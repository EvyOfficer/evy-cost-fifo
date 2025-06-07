<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <?php
    // แสดง WooCommerce notices (สำหรับ wc_add_notice)
    // ต้องเรียกใช้ฟังก์ชันนี้เพื่อให้ข้อความแจ้งเตือนจาก handle_inventory_in_submission แสดงขึ้น
    if ( function_exists( 'wc_print_notices' ) ) {
        wc_print_notices();
    } else {
        // Fallback สำหรับกรณีที่ wc_print_notices ไม่มี (ไม่น่าจะเกิดขึ้นถ้า WooCommerce ทำงานอยู่)
        if ( isset( $_GET['message'] ) ) {
            $message_type = '';
            $message_text = '';
            switch ( $_GET['message'] ) {
                case 'success':
                    $message_type = 'notice notice-success';
                    $message_text = __( 'Inventory In entry added successfully!', 'evy-cost-fifo' );
                    break;
                case 'error': // ใช้ 'error' แทน 'missing_fields' หรือ 'db_error' เพื่อให้สอดคล้องกับ wc_add_notice
                    $message_type = 'notice notice-error';
                    $message_text = __( 'An error occurred. Please check the data and try again.', 'evy-cost-fifo' );
                    break;
            }
            if ( $message_text ) {
                printf( '<div class="%1$s is-dismissible"><p>%2$s</p></div>', esc_attr( $message_type ), esc_html( $message_text ) );
            }
        }
    }
    ?>

    <p><?php esc_html_e( 'Use this page to record inventory receipts using the FIFO principle.', 'evy-cost-fifo' ); ?></p>

    <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
        <?php wp_nonce_field( 'evy_fifo_add_inventory_receipt', 'evy_fifo_add_inventory_receipt' ); ?>
        <input type="hidden" name="action" value="evy_fifo_add_inventory_receipt">

        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><label for="purchase_date"><?php esc_html_e( 'Purchase Date:', 'evy-cost-fifo' ); ?></label></th>
                    <td><input type="date" name="purchase_date" id="purchase_date" class="regular-text" value="<?php echo date('Y-m-d'); ?>" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="product_id"><?php esc_html_e( 'Product:', 'evy-cost-fifo' ); ?></label></th>
                    <td>
                        <select class="wc-product-search" style="width: 50%;" name="product_id" id="product_id" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>" data-allow_clear="true" required></select>
                        <span class="description"><?php esc_html_e( 'Select a product from WooCommerce.', 'evy-cost-fifo' ); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="quantity"><?php esc_html_e( 'Quantity:', 'evy-cost-fifo' ); ?></label></th>
                    <td>
                        <input type="number" name="quantity" id="quantity" class="regular-text" min="1" step="1" required>
                        <span class="description"><?php esc_html_e( 'Enter whole numbers only (e.g., 1, 2, 3).', 'evy-cost-fifo' ); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="unit_cost"><?php esc_html_e( 'Unit Cost:', 'evy-cost-fifo' ); ?></label></th>
                    <td>
                        <input type="number" name="unit_cost" id="unit_cost" class="regular-text" step="0.01" min="0" required>
                        <span class="description"><?php esc_html_e( 'Enter cost with up to 2 decimal places.', 'evy-cost-fifo' ); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="supplier_name"><?php esc_html_e( 'Supplier Name:', 'evy-cost-fifo' ); ?></label></th>
                    <td>
                        <input type="text" name="supplier_name" id="supplier_name" class="regular-text" placeholder="<?php esc_attr_e( 'Enter supplier name (e.g., Company ABC)', 'evy-cost-fifo' ); ?>">
                        <span class="description"><?php esc_html_e( 'Please ensure consistent naming for suppliers.', 'evy-cost-fifo' ); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="credit_term_days"><?php esc_html_e( 'Credit Term (days):', 'evy-cost-fifo' ); ?></label></th>
                    <td><input type="number" name="credit_term_days" id="credit_term_days" class="regular-text" min="0" value="0"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="purchase_source"><?php esc_html_e( 'Purchase Source:', 'evy-cost-fifo' ); ?></label></th>
                    <td>
                        <select name="purchase_source" id="purchase_source">
                            <option value="local"><?php esc_html_e( 'Local', 'evy-cost-fifo' ); ?></option>
                            <option value="imported"><?php esc_html_e( 'Imported', 'evy-cost-fifo' ); ?></option>
                        </select>
                        <span class="description"><?php esc_html_e( 'Specify whether the goods are sourced locally or imported.', 'evy-cost-fifo' ); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="notes"><?php esc_html_e( 'Reference Document:', 'evy-cost-fifo' ); ?></label></th>
                    <td><input type="text" name="notes" id="notes" class="regular-text"></td>
                </tr>
            </tbody>
        </table>
        <?php submit_button( __( 'Save Inventory Receipt', 'evy-cost-fifo' ) ); ?>
    </form>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Initialize Select2 for product search
        if ($.fn.select2) {
            $('.wc-product-search').select2({
                ajax: {
                    url: evy_fifo_vars.ajaxurl, // ใช้ ajaxurl ที่ส่งมาจาก wp_localize_script
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            term: params.term,
                            action: 'evy_fifo_search_products',
                            security: evy_fifo_vars.search_products_nonce // ใช้ nonce ที่ส่งมาจาก wp_localize_script
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                },
                minimumInputLength: 3,
                templateResult: function(product) {
                    if (product.loading) return product.text;
                    return product.text;
                },
                templateSelection: function(product) {
                    return product.text || product.id;
                }
            });
        } else {
            console.warn('Select2 is not loaded. Product search will not work.');
        }

        // Optional: Auto-fill purchase_date to today
        if (!$('#purchase_date').val()) {
            $('#purchase_date').val('<?php echo date('Y-m-d'); ?>');
        }
    });
</script>