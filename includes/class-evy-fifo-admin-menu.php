<?php
// evy-cost-fifo/includes/class-evy-fifo-admin-menu.php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Evy_FIFO_Admin_Menu {

    public function __construct() {
        // Hook ที่ใช้เพิ่มเมนูหลักของปลั๊กอินใน Admin
        add_action( 'admin_menu', array( $this, 'add_plugin_menu' ) );

        // Hook สำหรับการโหลด Scripts/Styles ใน Admin
        add_action( 'admin_enqueue_scripts', array( $this, 'evy_fifo_enqueue_admin_scripts' ) );

        // AJAX search for products (for Select2 field)
        add_action( 'wp_ajax_evy_fifo_search_products', array( $this, 'ajax_search_products' ) );
        add_action( 'wp_ajax_nopriv_evy_fifo_search_products', array( $this, 'ajax_search_products' ) );
    }

    /**
     * เพิ่มเมนูหลักและเมนูย่อยของปลั๊กอินในหน้า Admin
     */
    public function add_plugin_menu() {
        // เพิ่มเมนูหลัก "Evy Cost FIFO"
        add_menu_page(
            __( 'Evy Cost FIFO', 'evy-cost-fifo' ),
            __( 'Evy Cost FIFO', 'evy-cost-fifo' ),
            'manage_options',
            'evy-fifo-inventory-in',
            array( $this, 'display_inventory_in_page' ),
            'dashicons-chart-bar',
            30
        );

        // เพิ่มเมนูย่อย "Inventory In" (slug เดียวกับเมนูหลัก)
        add_submenu_page(
            'evy-fifo-inventory-in',
            __( 'Inventory In', 'evy-cost-fifo' ),
            __( 'Inventory In', 'evy-cost-fifo' ),
            'manage_options',
            'evy-fifo-inventory-in',
            array( $this, 'display_inventory_in_page' )
        );

        // เพิ่มเมนูย่อย "Settings" ใต้เมนูหลัก Settings ของ WordPress
        add_submenu_page(
            'options-general.php',
            __( 'Evy Cost FIFO Settings', 'evy-cost-fifo' ),
            __( 'Evy Cost FIFO', 'evy-cost-fifo' ),
            'manage_options',
            'evy-cost-fifo',
            array( $this, 'display_settings_page' )
        );
    }

    /**
     * Enqueue scripts and styles for admin pages.
     *
     * @param string $hook_suffix The current admin page hook suffix.
     */
    public function evy_fifo_enqueue_admin_scripts( $hook_suffix ) {
        // ตรวจสอบว่าอยู่บนหน้า Inventory In ของเราเท่านั้น
        $pages_to_load_scripts = array(
            'evy-fifo-inventory-in_page_evy-fifo-inventory-in', // สำหรับหน้า Inventory In
            'toplevel_page_evy-fifo-inventory-in', // สำหรับเมนูหลักที่ลิงก์ไป Inventory In
            'evy-fifo-inventory-in_page_evy-cost-fifo' // สำหรับหน้า Settings (เมนูย่อย)
        );

        if ( ! in_array( $hook_suffix, $pages_to_load_scripts ) ) {
            return;
        }

        // Enqueue WooCommerce admin styles and scripts which include Select2
        if ( function_exists( 'WC' ) ) {
            // Select2 script handle ใน WooCommerce คือ 'selectWoo' และ 'wc-enhanced-select'
            if ( ! wp_script_is( 'selectWoo', 'enqueued' ) ) {
                wp_enqueue_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full.min.js', array( 'jquery' ), WC_VERSION, true );
            }
            if ( ! wp_script_is( 'wc-enhanced-select', 'enqueued' ) ) {
                wp_enqueue_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select.min.js', array( 'jquery', 'selectWoo' ), WC_VERSION, true );
            }

            // สไตล์สำหรับ Select2 (มักจะมี handle ชื่อ 'select2' หรือเป็นส่วนหนึ่งของ woocommerce_admin_styles)
            if ( ! wp_style_is( 'select2', 'enqueued' ) ) {
                wp_enqueue_style( 'select2', WC()->plugin_url() . '/assets/css/select2.css', array(), WC_VERSION );
            }
            if ( ! wp_style_is( 'woocommerce_admin_styles', 'enqueued' ) ) {
                wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
            }
        }


        // ส่งค่า nonce และ ajaxurl ไปยัง JavaScript ของเรา
        // เราใช้ 'wc-enhanced-select' เป็น script handle ที่จะแนบข้อมูลไป เพราะเป็น script ที่เราใช้งาน Select2
        wp_localize_script(
            'wc-enhanced-select',
            'evy_fifo_vars',
            array(
                'ajaxurl'                    => admin_url( 'admin-ajax.php' ),
                'search_products_nonce'      => wp_create_nonce( 'evy_fifo_search_products_nonce' ), // ส่ง Nonce สำหรับ AJAX Search
            )
        );
    }

    /**
     * AJAX handler to search WooCommerce products by term.
     *
     * @return void Outputs JSON suitable for Select2.
     */
    public function ajax_search_products() {
        check_ajax_referer( 'evy_fifo_search_products_nonce', 'security' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json( array() );
        }

        $term = isset( $_GET['term'] ) ? wc_clean( wp_unslash( $_GET['term'] ) ) : '';

        $query_args = array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => 30,
            's'              => $term,
        );

        $posts    = get_posts( $query_args );
        $results  = array();

        foreach ( $posts as $post ) {
            $product      = wc_get_product( $post->ID );
            $results[] = array(
                'id'   => $product->get_id(),
                'text' => $product->get_formatted_name(),
            );
        }

        wp_send_json( $results );
    }

    /**
     * แสดงผลหน้า Settings
     * จะรวมไฟล์ settings.php เข้ามาแสดง
     */
    public function display_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        require_once EVY_FIFO_PLUGIN_DIR . 'admin/settings.php';
    }

    /**
     * แสดงผลหน้า Inventory In
     * จะรวมไฟล์ inventory-in.php เข้ามาแสดง
     */
    public function display_inventory_in_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        require_once EVY_FIFO_PLUGIN_DIR . 'admin/inventory-in.php';
    }
}