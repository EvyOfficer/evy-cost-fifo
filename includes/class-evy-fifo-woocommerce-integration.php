<?php
// evy-cost-fifo/includes/class-evy-fifo-woocommerce-integration.php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Evy_FIFO_WooCommerce_Integration {

    public function __construct() {
        // Trigger when an order is marked completed
        add_action( 'woocommerce_order_status_completed', array( $this, 'handle_order_completed' ) );
    }

    /**
     * Handle WooCommerce order completion and trigger inventory out logic.
     *
     * @param int $order_id The WooCommerce order ID.
     */
    public function handle_order_completed( $order_id ) {
        if ( ! $order_id ) {
            return;
        }
        Evy_FIFO_Inventory_Manager::handle_inventory_out_from_order( $order_id );
    }
}
