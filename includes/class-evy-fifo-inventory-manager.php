<?php
// evy-cost-fifo/includes/class-evy-fifo-inventory-manager.php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Evy_FIFO_Inventory_Manager {

    public function __construct() {
        // Hook สำหรับการจัดการการรับสินค้าเข้าสต็อก
        add_action( 'admin_post_evy_fifo_add_inventory_receipt', array( $this, 'handle_inventory_in_submission' ) );
        add_action( 'admin_post_nopriv_evy_fifo_add_inventory_receipt', array( $this, 'handle_inventory_in_submission' ) ); // สำหรับกรณีที่ไม่ได้ Login แต่อาจจะไม่จำเป็นสำหรับ admin form
    }

    /**
     * จัดการข้อมูลที่ส่งมาจากฟอร์มบันทึกการรับสินค้าเข้า
     */
    public function handle_inventory_in_submission() {
        $redirect_url = admin_url( 'admin.php?page=evy-fifo-inventory-in' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'evy-cost-fifo' ) );
        }

        if ( ! isset( $_POST['evy_fifo_add_inventory_receipt'] ) || ! wp_verify_nonce( $_POST['evy_fifo_add_inventory_receipt'], 'evy_fifo_add_inventory_receipt' ) ) {
            // nonce ไม่ถูกต้อง
            if ( function_exists( 'wc_add_notice' ) ) {
                wc_add_notice( __( 'Security check failed. Please try again.', 'evy-cost-fifo' ), 'error' );
            } else {
                error_log( 'Evy Cost FIFO: Nonce verification failed.' );
            }
            $redirect_url = add_query_arg( 'message', 'error', $redirect_url );
            wp_safe_redirect( $redirect_url );
            exit;
        }


        if ( isset( $_POST['evy_fifo_add_inventory_receipt'] ) ) {
            $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
            $quantity = isset( $_POST['quantity'] ) ? floatval( $_POST['quantity'] ) : 0;
            $unit_cost = isset( $_POST['unit_cost'] ) ? floatval( $_POST['unit_cost'] ) : 0;
            $purchase_date = isset( $_POST['purchase_date'] ) ? sanitize_text_field( $_POST['purchase_date'] ) : '';
            $supplier_name = isset( $_POST['supplier_name'] ) ? sanitize_text_field( $_POST['supplier_name'] ) : '';
            $purchase_source = isset( $_POST['purchase_source'] ) ? sanitize_text_field( $_POST['purchase_source'] ) : 'local';
            $credit_term_days = isset( $_POST['credit_term_days'] ) ? absint( $_POST['credit_term_days'] ) : 0;
            $notes = isset( $_POST['notes'] ) ? sanitize_text_field( $_POST['notes'] ) : '';

            // คำนวณต้นทุนรวมตามจำนวนที่รับเข้า
            $total_cost = $quantity * $unit_cost;

            // คำนวณ due_date และจัดการข้อมูลการชำระเงิน
            $current_date = date( 'Y-m-d', current_time( 'timestamp' ) );
            $due_date = '';
            if ( ! empty( $purchase_date ) && $credit_term_days > 0 ) {
                $due_date = date( 'Y-m-d', strtotime( $purchase_date . ' + ' . $credit_term_days . ' days' ) );
            } elseif ( ! empty( $purchase_date ) ) {
                $due_date = $purchase_date;
            } else {
                $due_date = $current_date;
            }


            $success = false;

            if ( $product_id > 0 && $quantity > 0 && $unit_cost >= 0 && ! empty( $purchase_date ) ) {
                $data = array(
                    'product_id'                 => $product_id,
                    'purchase_date'              => $purchase_date,
                    'quantity'                   => $quantity,
                    'remaining_quantity'         => $quantity, // เมื่อรับเข้า ปริมาณที่เหลือจะเท่ากับปริมาณที่รับ
                    'unit_cost'                  => $unit_cost,
                    'total_cost'                 => $total_cost,
                    'supplier_name'              => $supplier_name,
                    'purchase_source'            => $purchase_source,
                    'credit_term_days'           => $credit_term_days,
                    'due_date'                   => $due_date,
                    'notes'                      => $notes,
                );

                // เรียกใช้เมธอด insert_purchase_lot จาก Evy_FIFO_Database_Manager
                $inserted_id = Evy_FIFO_Database_Manager::insert_purchase_lot( $data );

                    if ( $inserted_id ) {
                        // อัปเดตสต็อกใน WooCommerce ด้วย
                        $product = wc_get_product( $product_id );
                        if ( $product ) {
                            $current_stock = $product->get_stock_quantity();
                            $new_stock = $current_stock + $quantity;
                            $product->set_stock_quantity( $new_stock );
                            $product->save();
                        }

                        // ส่งข้อมูลไปยัง Google Sheet แบบ Real-time
                        if ( isset( $GLOBALS['evy_fifo_google_integrator'] ) ) {
                            $GLOBALS['evy_fifo_google_integrator']->sync_purchase_lot_by_id( $inserted_id );
                        }

                    if ( function_exists( 'wc_add_notice' ) ) {
                        wc_add_notice( __( 'Inventory receipt saved successfully!', 'evy-cost-fifo' ), 'success' );
                    } else {
                        error_log( 'Evy Cost FIFO: Inventory receipt saved successfully for product ID ' . $product_id );
                    }
                    $success = true;
                } else {
                    if ( function_exists( 'wc_add_notice' ) ) {
                        wc_add_notice( __( 'Failed to save inventory receipt. Please try again.', 'evy-cost-fifo' ), 'error' );
                    } else {
                        error_log( 'Evy Cost FIFO: Failed to save inventory receipt for product ID ' . $product_id );
                    }
                }
            } else {
                if ( function_exists( 'wc_add_notice' ) ) {
                    wc_add_notice( __( 'Please fill in all required fields (Product, Quantity, Unit Cost, Purchase Date).', 'evy-cost-fifo' ), 'error' );
                } else {
                    error_log( 'Evy Cost FIFO: Missing required fields for inventory receipt.' );
                }
            }

            if ( $success ) {
                $redirect_url = add_query_arg( 'message', 'success', $redirect_url );
            } else {
                $redirect_url = add_query_arg( 'message', 'error', $redirect_url );
            }
        }
        wp_safe_redirect( $redirect_url );
        exit;
    }

    /**
     * Handle inventory deduction (Inventory Out) and COGS calculation for a WooCommerce order.
     *
     * @param int $order_id WooCommerce order ID.
     */
    public static function handle_inventory_out_from_order( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        foreach ( $order->get_items( array( 'line_item' ) ) as $item_id => $item ) {
            $product_id     = $item->get_product_id();
            $quantity_sold  = (float) $item->get_quantity();
            if ( $quantity_sold <= 0 || ! $product_id ) {
                continue;
            }

            $product       = wc_get_product( $product_id );
            if ( ! $product ) {
                continue;
            }
            $product_type  = $product->get_type();
            $update_lot_qt = self::is_physical_product( $product );

            $lots        = Evy_FIFO_Database_Manager::get_remaining_purchase_lots( $product_id );
            $qty_needed  = $quantity_sold;
            $total_cogs  = 0;
            $lot_ids     = array();

            foreach ( $lots as $lot ) {
                if ( $qty_needed <= 0 ) {
                    break;
                }

                $remaining_qty = (float) $lot['remaining_quantity'];
                if ( $remaining_qty <= 0 ) {
                    continue;
                }

                $deduct_qty   = min( $qty_needed, $remaining_qty );
                $qty_needed   -= $deduct_qty;

                if ( $update_lot_qt ) {
                    $new_remaining = $remaining_qty - $deduct_qty;
                    Evy_FIFO_Database_Manager::update_purchase_lot_remaining_quantity( $lot['id'], $new_remaining );
                }

                $total_cogs += $deduct_qty * (float) $lot['unit_cost'];
                $lot_ids[]  = $lot['id'];
            }

            if ( $qty_needed > 0 ) {
                error_log( 'Evy Cost FIFO: Insufficient purchase lots for product ID ' . $product_id . ' when processing order ' . $order_id );
            }

            $deducted_quantity = $quantity_sold - $qty_needed;
            $average_cost     = $deducted_quantity > 0 ? $total_cogs / $deducted_quantity : 0;

            Evy_FIFO_Database_Manager::insert_inventory_movement( array(
                'product_id'    => $product_id,
                'order_id'      => $order_id,
                'order_item_id' => $item_id,
                'movement_date' => current_time( 'mysql' ),
                'quantity'      => -1 * $deducted_quantity,
                'cost_per_unit' => $average_cost,
                'total_cost'    => $total_cogs,
                'type'          => 'sale',
                'notes'         => 'COGS for Order #' . $order_id,
            ) );

            Evy_FIFO_Database_Manager::insert_cogs_entry( array(
                'order_id'      => $order_id,
                'order_item_id' => $item_id,
                'product_id'    => $product_id,
                'variation_id'  => $item->get_variation_id(),
                'sold_quantity' => $deducted_quantity,
                'cogs_amount'   => $total_cogs,
                'sale_date'     => current_time( 'mysql' ),
                'lot_ids_used'  => implode( ',', $lot_ids ),
            ) );
        }
    }

    /**
     * Determine if a product should reduce physical stock in purchase lots.
     *
     * @param WC_Product $product The WooCommerce product object.
     * @return bool True if the product has physical stock and lots should be updated.
     */
    private static function is_physical_product( $product ) {
        $physical_types = array( 'simple', 'variable', 'variation' );
        return in_array( $product->get_type(), $physical_types, true ) && ! $product->is_virtual();
    }
}