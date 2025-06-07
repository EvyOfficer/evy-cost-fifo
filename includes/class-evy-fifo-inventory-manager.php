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
            wp_safe_redirect( admin_url( 'admin.php?page=evy-fifo-inventory-in' ) );
            exit;
        }


        if ( isset( $_POST['evy_fifo_add_inventory_receipt'] ) ) {
            $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
            $quantity = isset( $_POST['quantity'] ) ? floatval( $_POST['quantity'] ) : 0;
            $unit_cost = isset( $_POST['unit_cost'] ) ? floatval( $_POST['unit_cost'] ) : 0;
            $purchase_date = isset( $_POST['purchase_date'] ) ? sanitize_text_field( $_POST['purchase_date'] ) : '';
            $supplier_name = isset( $_POST['supplier_name'] ) ? sanitize_text_field( $_POST['supplier_name'] ) : '';
            $shipping_cost_per_unit = isset( $_POST['shipping_cost_per_unit'] ) ? floatval( $_POST['shipping_cost_per_unit'] ) : 0;
            $credit_term_days = isset( $_POST['credit_term_days'] ) ? absint( $_POST['credit_term_days'] ) : 0;
            $is_paid = isset( $_POST['is_paid'] ) ? 1 : 0;
            $payment_date = isset( $_POST['payment_date'] ) ? sanitize_text_field( $_POST['payment_date'] ) : '';
            $payment_ref  = isset( $_POST['payment_ref'] ) ? sanitize_text_field( $_POST['payment_ref'] ) : '';
            $notes = isset( $_POST['notes'] ) ? sanitize_textarea_field( $_POST['notes'] ) : '';

            // คำนวณ cost_per_unit_with_shipping และ total_cost
            $cost_per_unit_with_shipping = $unit_cost + $shipping_cost_per_unit;
            $total_cost = $quantity * $cost_per_unit_with_shipping;

            // คำนวณ due_date และจัดการข้อมูลการชำระเงิน
            $current_date = date( 'Y-m-d', current_time( 'timestamp' ) );
            $due_date = '';
            if ( $is_paid ) {
                // หากมีการชำระเงินแล้ว ใช้วันที่ชำระเป็น due_date (หรือวันนี้หากไม่ได้ระบุ)
                $payment_date = $payment_date ? $payment_date : $current_date;
                $due_date     = $payment_date;
            } else {
                if ( ! empty( $purchase_date ) && $credit_term_days > 0 ) {
                    $due_date = date( 'Y-m-d', strtotime( $purchase_date . ' + ' . $credit_term_days . ' days' ) );
                } elseif ( ! empty( $purchase_date ) ) {
                    // หากไม่มีเครดิตเทอม กำหนดวันครบกำหนดเป็นวันซื้อสินค้า
                    $due_date = $purchase_date;
                } else {
                    $due_date = $current_date;
                }
                $payment_date = '';
                $payment_ref  = '';
            }


            if ( $product_id > 0 && $quantity > 0 && $unit_cost >= 0 && ! empty( $purchase_date ) ) {
                $data = array(
                    'product_id'                 => $product_id,
                    'purchase_date'              => $purchase_date,
                    'quantity'                   => $quantity,
                    'remaining_quantity'         => $quantity, // เมื่อรับเข้า ปริมาณที่เหลือจะเท่ากับปริมาณที่รับ
                    'unit_cost'                  => $unit_cost,
                    'shipping_cost_per_unit'     => $shipping_cost_per_unit,
                    'cost_per_unit_with_shipping' => $cost_per_unit_with_shipping,
                    'total_cost'                 => $total_cost,
                    'supplier_name'              => $supplier_name,
                    'credit_term_days'           => $credit_term_days,
                    'due_date'                   => $due_date,
                    'is_paid'                    => $is_paid,
                    'payment_date'               => $payment_date ? $payment_date : null,
                    'payment_ref'                => $payment_ref,
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

                    if ( function_exists( 'wc_add_notice' ) ) {
                        wc_add_notice( __( 'Inventory receipt saved successfully!', 'evy-cost-fifo' ), 'success' );
                    } else {
                        error_log( 'Evy Cost FIFO: Inventory receipt saved successfully for product ID ' . $product_id );
                    }
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
        }
        wp_safe_redirect( admin_url( 'admin.php?page=evy-fifo-inventory-in' ) );
        exit;
    }
}