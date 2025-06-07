<?php
// evy-cost-fifo/includes/class-evy-fifo-google-sheet-integrator.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Evy_FIFO_Google_Sheet_Integrator {

    protected $client;
    protected $service;

    public function __construct() {
        // Attempt to load Google Client library
        if ( ! class_exists( '\\Google\\Client' ) ) {
            $autoloader = EVY_FIFO_PLUGIN_DIR . 'vendor/autoload.php';
            if ( file_exists( $autoloader ) ) {
                require_once $autoloader;
            }
        }

        if ( class_exists( '\\Google\\Client' ) ) {
            $this->client = new Google\Client();
            $this->client->setApplicationName( 'Evy Cost FIFO' );
            $this->client->setScopes( array( Google\Service\Sheets::SPREADSHEETS ) );
            $credentials_path = EVY_FIFO_PLUGIN_DIR . 'credentials.json';
            if ( file_exists( $credentials_path ) ) {
                $this->client->setAuthConfig( $credentials_path );
                $this->client->setAccessType( 'offline' );
            }
        }
    }

    protected function get_service() {
        if ( ! $this->service && $this->client ) {
            $this->service = new Google\Service\Sheets( $this->client );
        }
        return $this->service;
    }

    public function ensure_headers( $spreadsheet_id, $sheet_name, $headers ) {
        $service = $this->get_service();
        if ( ! $service ) {
            return false;
        }
        try {
            $range     = $sheet_name . '!1:1';
            $response  = $service->spreadsheets_values->get( $spreadsheet_id, $range );
            $existing  = $response->getValues();
            if ( empty( $existing ) ) {
                $body = new Google\Service\Sheets\ValueRange( array( 'values' => array( $headers ) ) );
                $service->spreadsheets_values->update( $spreadsheet_id, $range, $body, array( 'valueInputOption' => 'RAW' ) );
            }
        } catch ( Exception $e ) {
            return false;
        }
        return true;
    }

    public function write_to_sheet( $spreadsheet_id, $sheet_name, $data_rows ) {
        $service = $this->get_service();
        if ( ! $service ) {
            return false;
        }
        try {
            $range  = $sheet_name;
            $body   = new Google\Service\Sheets\ValueRange( array( 'values' => $data_rows ) );
            $params = array( 'valueInputOption' => 'RAW', 'insertDataOption' => 'INSERT_ROWS' );
            $service->spreadsheets_values->append( $spreadsheet_id, $range, $body, $params );
            return true;
        } catch ( Exception $e ) {
            return false;
        }
    }

    public function sync_all_purchase_lots() {
        $spreadsheet_id = get_option( 'evy_fifo_sheet_id' );
        $sheet_name     = get_option( 'evy_fifo_purchase_sheet_name', 'Purchase Lots' );
        if ( ! $spreadsheet_id ) {
            return;
        }

        $headers = array( 'ID', 'Product ID', 'Purchase Date', 'Quantity', 'Remaining Quantity', 'Unit Cost', 'Total Cost', 'Supplier Name', 'Purchase Source', 'Credit Term (Days)', 'Due Date', 'Is Paid', 'Payment Date', 'Payment Ref', 'Reference Document', 'Created At', 'Updated At' );
        $this->ensure_headers( $spreadsheet_id, $sheet_name, $headers );

        $lots = Evy_FIFO_Database_Manager::get_all_purchase_lots();
        $rows = array();
        foreach ( $lots as $lot ) {
            $rows[] = array( $lot['id'], $lot['product_id'], $lot['purchase_date'], $lot['quantity'], $lot['remaining_quantity'], $lot['unit_cost'], $lot['total_cost'], $lot['supplier_name'], $lot['purchase_source'], $lot['credit_term_days'], $lot['due_date'], $lot['is_paid'], $lot['payment_date'], $lot['payment_ref'], $lot['notes'], $lot['created_at'], $lot['updated_at'] );
        }
        if ( ! empty( $rows ) ) {
            $this->write_to_sheet( $spreadsheet_id, $sheet_name, $rows );
        }
    }

    public function sync_purchase_lot_by_id( $lot_id ) {
        $spreadsheet_id = get_option( 'evy_fifo_sheet_id' );
        $sheet_name     = get_option( 'evy_fifo_purchase_sheet_name', 'Purchase Lots' );
        if ( ! $spreadsheet_id ) {
            return;
        }
        $lot = Evy_FIFO_Database_Manager::get_purchase_lot_by_id( $lot_id );
        if ( ! $lot ) {
            return;
        }
        $row = array( $lot['id'], $lot['product_id'], $lot['purchase_date'], $lot['quantity'], $lot['remaining_quantity'], $lot['unit_cost'], $lot['total_cost'], $lot['supplier_name'], $lot['purchase_source'], $lot['credit_term_days'], $lot['due_date'], $lot['is_paid'], $lot['payment_date'], $lot['payment_ref'], $lot['notes'], $lot['created_at'], $lot['updated_at'] );
        $this->write_to_sheet( $spreadsheet_id, $sheet_name, array( $row ) );
    }

    public function sync_purchase_lots_since( $datetime ) {
        $spreadsheet_id = get_option( 'evy_fifo_sheet_id' );
        $sheet_name     = get_option( 'evy_fifo_purchase_sheet_name', 'Purchase Lots' );
        if ( ! $spreadsheet_id ) {
            return;
        }
        $lots = Evy_FIFO_Database_Manager::get_purchase_lots_updated_since( $datetime );
        if ( empty( $lots ) ) {
            return;
        }
        $rows = array();
        foreach ( $lots as $lot ) {
            $rows[] = array( $lot['id'], $lot['product_id'], $lot['purchase_date'], $lot['quantity'], $lot['remaining_quantity'], $lot['unit_cost'], $lot['total_cost'], $lot['supplier_name'], $lot['purchase_source'], $lot['credit_term_days'], $lot['due_date'], $lot['is_paid'], $lot['payment_date'], $lot['payment_ref'], $lot['notes'], $lot['created_at'], $lot['updated_at'] );
        }
        $this->write_to_sheet( $spreadsheet_id, $sheet_name, $rows );
    }
}
