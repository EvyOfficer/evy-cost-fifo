<?php
/**
 * Plugin Name: Evy Cost FIFO – WooCommerce Inventory Costing & AP Plugin
 * Plugin URI: https://yourwebsite.com/
 * Description: Manages FIFO inventory costing and Accounts Payable for WooCommerce, with Google Sheets integration.
 * Version: 1.0.0
 * Author: Evy
 * Author URI: https://yourwebsite.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: evy-cost-fifo
 * Domain Path: /languages
 */

// ป้องกันการเข้าถึงไฟล์โดยตรง (ความปลอดภัยพื้นฐานของ WordPress)
if ( ! defined( 'ABSPATH' ) ) {
    exit; // ออกจากการทำงานทันที
}

// --- กำหนดค่าคงที่สำหรับปลั๊กอิน ---
if ( ! defined( 'EVY_FIFO_VERSION' ) ) {
    define( 'EVY_FIFO_VERSION', '1.0.0' ); // กำหนดเวอร์ชันของปลั๊กอิน
}
if ( ! defined( 'EVY_FIFO_PLUGIN_DIR' ) ) {
    define( 'EVY_FIFO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) ); // พาธของโฟลเดอร์ปลั๊กอิน (มี trailing slash)
}
if ( ! defined( 'EVY_FIFO_PLUGIN_URL' ) ) {
    define( 'EVY_FIFO_PLUGIN_URL', plugin_dir_url( __FILE__ ) ); // URL ของโฟลเดอร์ปลั๊กอิน (มี trailing slash)
}
if ( ! defined( 'EVY_FIFO_PLUGIN_BASENAME' ) ) {
    define( 'EVY_FIFO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

add_action( 'plugins_loaded', function() {
    load_plugin_textdomain( 'evy-cost-fifo', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
} );



// --- ตรวจสอบว่า WooCommerce ติดตั้งและเปิดใช้งานอยู่หรือไม่ ---
// เพื่อให้แน่ใจว่าฟังก์ชัน WooCommerce (wc_get_product, wc_add_notice) พร้อมใช้งาน
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    /**
     * แสดงข้อความเตือนเมื่อ WooCommerce ไม่ได้เปิดใช้งาน
     */
    function evy_fifo_admin_notice_woocommerce_missing() {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><strong><?php esc_html_e( 'Evy Cost FIFO: WooCommerce is not active!', 'evy-cost-fifo' ); ?></strong></p>
            <p><?php esc_html_e( 'The Evy Cost FIFO plugin requires WooCommerce to be installed and active. Please install and activate WooCommerce to use this plugin.', 'evy-cost-fifo' ); ?></p>
        </div>
        <?php
    }
    add_action( 'admin_notices', 'evy_fifo_admin_notice_woocommerce_missing' );

    /**
     * ปิดการทำงานของปลั๊กอิน Evy Cost FIFO หาก WooCommerce ไม่อยู่
     */
    function evy_fifo_deactivate_self() {
        deactivate_plugins( EVY_FIFO_PLUGIN_BASENAME ); // ใช้ค่าคงที่ที่กำหนดไว้
        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
    add_action( 'admin_init', 'evy_fifo_deactivate_self' );
    return; // หยุดการทำงานของปลั๊กอิน
}

// โหลด Composer dependencies (ถ้ามี)
if ( file_exists( EVY_FIFO_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
    require_once EVY_FIFO_PLUGIN_DIR . 'vendor/autoload.php';
}

// --- ส่วนสำหรับรวมไฟล์คลาสหลัก (Include core classes) ---
require_once EVY_FIFO_PLUGIN_DIR . 'includes/class-evy-fifo-database-manager.php';
require_once EVY_FIFO_PLUGIN_DIR . 'includes/class-evy-fifo-admin-menu.php';
require_once EVY_FIFO_PLUGIN_DIR . 'includes/class-evy-fifo-inventory-manager.php';
// require_once EVY_FIFO_PLUGIN_DIR . 'includes/class-evy-fifo-google-sync.php';
// require_once EVY_FIFO_PLUGIN_DIR . 'includes/class-evy-fifo-woocommerce-integration.php';
// require_once EVY_FIFO_PLUGIN_DIR . 'includes/helpers.php'; // ถ้ามี helper functions

/**
 * ฟังก์ชันที่จะถูกเรียกเมื่อปลั๊กอินถูกเปิดใช้งาน (Activation Hook)
 * มีหน้าที่หลักคือ สร้างตารางฐานข้อมูลและตั้งเวลา Cron Job เริ่มต้น
 */
function evy_fifo_activate() {
    Evy_FIFO_Database_Manager::create_tables();
    if ( ! wp_next_scheduled( 'evy_fifo_daily_sync' ) ) {
        wp_schedule_event( time(), 'daily', 'evy_fifo_daily_sync' );
    }
}
register_activation_hook( __FILE__, 'evy_fifo_activate' );

/**
 * ฟังก์ชันที่จะถูกเรียกเมื่อปลั๊กอินถูกปิดใช้งาน (Deactivation Hook)
 * มีหน้าที่หลักคือ เคลียร์ Cron Job ที่ตั้งไว้
 */
function evy_fifo_deactivate() {
    wp_clear_scheduled_hook( 'evy_fifo_daily_sync' );
}
register_deactivation_hook( __FILE__, 'evy_fifo_deactivate' );

// เพิ่มฟังก์ชัน Callback สำหรับ Cron Job (จะถูกนิยามในคลาส Evy_FIFO_Google_Sync ในอนาคต)
// add_action( 'evy_fifo_daily_sync', 'evy_fifo_run_daily_sync' );

// ส่วนสำหรับเริ่มต้นคลาสอื่นๆ ของปลั๊กอิน
// Initialize plugin classes
new Evy_FIFO_Admin_Menu();
new Evy_FIFO_Inventory_Manager();
// new Evy_FIFO_WooCommerce_Integration();
// new Evy_FIFO_Google_Sync();