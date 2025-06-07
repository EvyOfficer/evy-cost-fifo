<?php
// evy-cost-fifo/includes/class-evy-fifo-database-manager.php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Evy_FIFO_Database_Manager {

    private static $table_name_prefix; // สำหรับเก็บ prefix ของชื่อตาราง

    /**
     * กำหนดชื่อตารางที่ใช้ในปลั๊กอิน
     */
    private static function get_table_names() {
        global $wpdb;
        self::$table_name_prefix = $wpdb->prefix . 'evy_fifo_'; // กำหนด prefix ของตาราง

        return array(
            'purchase_lots'    => self::$table_name_prefix . 'purchase_lots',
            'inventory_movements' => self::$table_name_prefix . 'inventory_movements',
            'cogs_entries'     => self::$table_name_prefix . 'cogs_entries',
            // 'product_cost_map' => self::$table_name_prefix . 'product_cost_map', // อาจจะมีในอนาคต
        );
    }

    /**
     * สร้างตารางที่จำเป็นเมื่อปลั๊กอินถูกเปิดใช้งาน
     */
    public static function create_tables() {
        global $wpdb;
        $tables = self::get_table_names();
        $charset_collate = $wpdb->get_charset_collate();

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        // ตาราง: purchase_lots (สำหรับบันทึกการรับสินค้าเข้าสต็อกตาม Lot)
        $sql_purchase_lots = "CREATE TABLE {$tables['purchase_lots']} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            product_id BIGINT(20) UNSIGNED NOT NULL,
            purchase_date DATE NOT NULL,
            quantity DECIMAL(10,4) NOT NULL,
            remaining_quantity DECIMAL(10,4) NOT NULL,
            unit_cost DECIMAL(10,4) NOT NULL,
            total_cost DECIMAL(10,4) NOT NULL,
            supplier_name VARCHAR(255) NULL,
            purchase_source VARCHAR(50) NOT NULL DEFAULT 'local',
            credit_term_days INT(11) NOT NULL DEFAULT 0,
            due_date DATE NULL,
            is_paid TINYINT(1) NOT NULL DEFAULT 0,
            payment_date DATE NULL,
            payment_ref VARCHAR(255) NULL,
            notes TEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY purchase_date (purchase_date)
        ) $charset_collate;";
        dbDelta( $sql_purchase_lots );

        // ตาราง: inventory_movements (สำหรับบันทึกการเคลื่อนไหวของสินค้า เช่น ขายออก)
        $sql_inventory_movements = "CREATE TABLE {$tables['inventory_movements']} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            product_id BIGINT(20) UNSIGNED NOT NULL,
            order_id BIGINT(20) UNSIGNED NULL,
            order_item_id BIGINT(20) UNSIGNED NULL,
            movement_date DATETIME NOT NULL,
            quantity DECIMAL(10,4) NOT NULL,
            cost_per_unit DECIMAL(10,4) NOT NULL,
            total_cost DECIMAL(10,4) NOT NULL,
            type VARCHAR(50) NOT NULL,
            notes TEXT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY order_id (order_id)
        ) $charset_collate;";
        dbDelta( $sql_inventory_movements );

        // ตาราง: cogs_entries (บันทึกต้นทุนขายตามคำสั่งซื้อ)
        $sql_cogs_entries = "CREATE TABLE {$tables['cogs_entries']} (
            cogs_entry_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            order_id BIGINT(20) UNSIGNED NOT NULL,
            order_item_id BIGINT(20) UNSIGNED NOT NULL,
            product_id BIGINT(20) UNSIGNED NOT NULL,
            variation_id BIGINT(20) UNSIGNED DEFAULT NULL,
            sold_quantity DECIMAL(10,4) NOT NULL,
            cogs_amount DECIMAL(20,4) NOT NULL,
            sale_date DATETIME NOT NULL,
            lot_ids_used TEXT NOT NULL,
            date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (cogs_entry_id),
            KEY order_id (order_id),
            KEY product_id (product_id)
        ) $charset_collate;";
        dbDelta( $sql_cogs_entries );

    }

    /**
     * เพิ่มข้อมูลการรับสินค้าเข้าสต็อก (Purchase Lot)
     *
     * @param array $data ข้อมูล Lot สินค้าที่ต้องการบันทึก
     * @return int|bool ID ของแถวที่เพิ่มเข้าไป หรือ false หากเกิดข้อผิดพลาด
     */
    public static function insert_purchase_lot( $data ) {
        global $wpdb;
        $tables = self::get_table_names();
        $table_name = $tables['purchase_lots'];

        // ตรวจสอบและเตรียมข้อมูล
        $insert_data = array(
            'product_id'        => isset( $data['product_id'] ) ? $data['product_id'] : 0,
            'purchase_date'     => isset( $data['purchase_date'] ) ? $data['purchase_date'] : current_time( 'mysql', true ),
            'quantity'          => isset( $data['quantity'] ) ? $data['quantity'] : 0,
            'remaining_quantity' => isset( $data['remaining_quantity'] ) ? $data['remaining_quantity'] : 0,
            'unit_cost'         => isset( $data['unit_cost'] ) ? $data['unit_cost'] : 0,
            'total_cost'        => isset( $data['total_cost'] ) ? $data['total_cost'] : 0,
            'supplier_name'     => isset( $data['supplier_name'] ) ? $data['supplier_name'] : '',
            'purchase_source'   => isset( $data['purchase_source'] ) ? $data['purchase_source'] : 'local',
            'credit_term_days'  => isset( $data['credit_term_days'] ) ? $data['credit_term_days'] : 0,
            'due_date'          => isset( $data['due_date'] ) ? $data['due_date'] : null,
            'notes'             => isset( $data['notes'] ) ? $data['notes'] : '',
            'created_at'        => current_time( 'mysql' ),
            'updated_at'        => null,
        );

        // กำหนด Format ของข้อมูล
        $format = array(
            '%d', // product_id
            '%s', // purchase_date
            '%f', // quantity
            '%f', // remaining_quantity
            '%f', // unit_cost
            '%f', // total_cost
            '%s', // supplier_name
            '%s', // purchase_source
            '%d', // credit_term_days
            '%s', // due_date
            '%s', // notes
            '%s', // created_at
            '%s', // updated_at
        );

        // ทำการ Insert
        $inserted = $wpdb->insert( $table_name, $insert_data, $format );

        if ( $inserted ) {
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * ดึง Lot สินค้าที่ยังเหลือ
     *
     * @param int $product_id ID ของสินค้า
     * @return array รายการ Lot สินค้าที่เหลือ โดยเรียงตามวันที่รับเข้า (FIFO)
     */
    public static function get_remaining_purchase_lots( $product_id ) {
        global $wpdb;
        $tables = self::get_table_names();
        $table_name = $tables['purchase_lots'];

        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE product_id = %d AND remaining_quantity > 0 ORDER BY purchase_date ASC, id ASC",
            $product_id
        ), ARRAY_A );

        return $results;
    }

    /**
     * อัปเดตปริมาณที่เหลือของ Lot สินค้า
     *
     * @param int $lot_id ID ของ Lot สินค้า
     * @param float $new_remaining_quantity ปริมาณที่เหลือใหม่
     * @return bool true หากอัปเดตสำเร็จ, false หากไม่สำเร็จ
     */
    public static function update_purchase_lot_remaining_quantity( $lot_id, $new_remaining_quantity ) {
        global $wpdb;
        $tables = self::get_table_names();
        $table_name = $tables['purchase_lots'];

        return $wpdb->update(
            $table_name,
            array( 'remaining_quantity' => $new_remaining_quantity ),
            array( 'id' => $lot_id ),
            array( '%f' ),
            array( '%d' )
        );
    }

    /**
     * เพิ่มข้อมูลการเคลื่อนไหวของสินค้า (Inventory Movement)
     *
     * @param array $data ข้อมูลการเคลื่อนไหวที่ต้องการบันทึก
     * @return int|bool ID ของแถวที่เพิ่มเข้าไป หรือ false หากเกิดข้อผิดพลาด
     */
    public static function insert_inventory_movement( $data ) {
        global $wpdb;
        $tables = self::get_table_names();
        $table_name = $tables['inventory_movements'];

        $insert_data = array(
            'product_id'    => isset( $data['product_id'] ) ? $data['product_id'] : 0,
            'order_id'      => isset( $data['order_id'] ) ? $data['order_id'] : null,
            'order_item_id' => isset( $data['order_item_id'] ) ? $data['order_item_id'] : null,
            'movement_date' => isset( $data['movement_date'] ) ? $data['movement_date'] : current_time( 'mysql' ),
            'quantity'      => isset( $data['quantity'] ) ? $data['quantity'] : 0,
            'cost_per_unit' => isset( $data['cost_per_unit'] ) ? $data['cost_per_unit'] : 0,
            'total_cost'    => isset( $data['total_cost'] ) ? $data['total_cost'] : 0,
            'type'          => isset( $data['type'] ) ? $data['type'] : 'sale',
            'notes'         => isset( $data['notes'] ) ? $data['notes'] : '',
            'created_at'    => current_time( 'mysql' ),
        );

        $format = array(
            '%d', // product_id
            '%d', // order_id
            '%d', // order_item_id
            '%s', // movement_date
            '%f', // quantity
            '%f', // cost_per_unit
            '%f', // total_cost
            '%s', // type
            '%s', // notes
            '%s', // created_at
        );

        $inserted = $wpdb->insert( $table_name, $insert_data, $format );

        return $inserted ? $wpdb->insert_id : false;
    }

    /**
     * เพิ่มข้อมูล COGS สำหรับแต่ละรายการสั่งซื้อ
     *
     * @param array $data ข้อมูล COGS ที่ต้องการบันทึก
     * @return int|bool ID ของแถวที่เพิ่มเข้าไป หรือ false หากเกิดข้อผิดพลาด
     */
    public static function insert_cogs_entry( $data ) {
        global $wpdb;
        $tables     = self::get_table_names();
        $table_name = $tables['cogs_entries'];

        $insert_data = array(
            'order_id'      => isset( $data['order_id'] ) ? $data['order_id'] : 0,
            'order_item_id' => isset( $data['order_item_id'] ) ? $data['order_item_id'] : 0,
            'product_id'    => isset( $data['product_id'] ) ? $data['product_id'] : 0,
            'variation_id'  => isset( $data['variation_id'] ) ? $data['variation_id'] : null,
            'sold_quantity' => isset( $data['sold_quantity'] ) ? $data['sold_quantity'] : 0,
            'cogs_amount'   => isset( $data['cogs_amount'] ) ? $data['cogs_amount'] : 0,
            'sale_date'     => isset( $data['sale_date'] ) ? $data['sale_date'] : current_time( 'mysql' ),
            'lot_ids_used'  => isset( $data['lot_ids_used'] ) ? $data['lot_ids_used'] : '',
            'date_created'  => current_time( 'mysql' ),
        );

        $format = array(
            '%d', // order_id
            '%d', // order_item_id
            '%d', // product_id
            '%d', // variation_id
            '%f', // sold_quantity
            '%f', // cogs_amount
            '%s', // sale_date
            '%s', // lot_ids_used
            '%s', // date_created
        );

        $inserted = $wpdb->insert( $table_name, $insert_data, $format );

        return $inserted ? $wpdb->insert_id : false;
    }
}