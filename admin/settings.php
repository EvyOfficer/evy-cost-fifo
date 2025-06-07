<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <?php
    if ( isset( $_POST['evy_fifo_save_settings'] ) && check_admin_referer( 'evy_fifo_save_settings_nonce' ) ) {
        $sheet_id      = sanitize_text_field( $_POST['evy_fifo_sheet_id'] );
        $purchase_name = sanitize_text_field( $_POST['evy_fifo_purchase_sheet_name'] );
        $cogs_name     = sanitize_text_field( $_POST['evy_fifo_cogs_sheet_name'] );
        $enable_auto   = isset( $_POST['evy_fifo_enable_auto_sync'] ) ? 1 : 0;
        $sync_time     = sanitize_text_field( $_POST['evy_fifo_auto_sync_time'] );
        update_option( 'evy_fifo_sheet_id', $sheet_id );
        update_option( 'evy_fifo_purchase_sheet_name', $purchase_name );
        update_option( 'evy_fifo_cogs_sheet_name', $cogs_name );
        update_option( 'evy_fifo_enable_auto_sync', $enable_auto );
        update_option( 'evy_fifo_auto_sync_time', $sync_time );
        if ( function_exists( 'evy_fifo_schedule_daily_sync' ) ) {
            evy_fifo_schedule_daily_sync();
        }
        echo '<div class="updated"><p>' . esc_html__( 'Settings saved.', 'evy-cost-fifo' ) . '</p></div>';
    }

    if ( isset( $_POST['evy_fifo_test_sync'] ) && check_admin_referer( 'evy_fifo_test_sync_nonce' ) ) {
        if ( isset( $GLOBALS['evy_fifo_google_integrator'] ) ) {
            $GLOBALS['evy_fifo_google_integrator']->sync_all_purchase_lots();
            echo '<div class="updated"><p>' . esc_html__( 'Sync completed.', 'evy-cost-fifo' ) . '</p></div>';
        }
    }

    $sheet_id      = get_option( 'evy_fifo_sheet_id', '' );
    $purchase_name = get_option( 'evy_fifo_purchase_sheet_name', 'Purchase Lots' );
    $cogs_name     = get_option( 'evy_fifo_cogs_sheet_name', 'COGS Entries' );
    $enable_auto   = get_option( 'evy_fifo_enable_auto_sync', 0 );
    $sync_time     = get_option( 'evy_fifo_auto_sync_time', '23:30' );
    ?>
    <form method="post">
        <?php wp_nonce_field( 'evy_fifo_save_settings_nonce' ); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e( 'Google Spreadsheet ID', 'evy-cost-fifo' ); ?></th>
                <td><input type="text" name="evy_fifo_sheet_id" value="<?php echo esc_attr( $sheet_id ); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e( 'Sheet Name (Purchase Lots)', 'evy-cost-fifo' ); ?></th>
                <td><input type="text" name="evy_fifo_purchase_sheet_name" value="<?php echo esc_attr( $purchase_name ); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e( 'Sheet Name (COGS Entries)', 'evy-cost-fifo' ); ?></th>
                <td><input type="text" name="evy_fifo_cogs_sheet_name" value="<?php echo esc_attr( $cogs_name ); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e( 'Enable Daily Automated Sync', 'evy-cost-fifo' ); ?></th>
                <td><input type="checkbox" name="evy_fifo_enable_auto_sync" value="1" <?php checked( $enable_auto, 1 ); ?> /></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e( 'Automated Sync Time (HH:MM)', 'evy-cost-fifo' ); ?></th>
                <td><input type="time" name="evy_fifo_auto_sync_time" value="<?php echo esc_attr( $sync_time ); ?>" /></td>
            </tr>
        </table>
        <?php submit_button( __( 'Save Settings', 'evy-cost-fifo' ), 'primary', 'evy_fifo_save_settings' ); ?>
        <?php wp_nonce_field( 'evy_fifo_test_sync_nonce' ); ?>
        <?php submit_button( __( 'Test Connection & Initial Sync (Purchase Lots)', 'evy-cost-fifo' ), 'secondary', 'evy_fifo_test_sync' ); ?>
    </form>
</div>
