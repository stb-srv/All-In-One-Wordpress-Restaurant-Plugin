<?php
namespace AIO_Restaurant_Plugin;

/**
 * Handles import/export of menu data.
 */
class AORP_CSV_Handler {
    /**
     * Register actions for import/export.
     */
    public function register(): void {
        add_action( 'admin_post_aorp_export_csv', array( $this, 'export_csv' ) );
        add_action( 'admin_post_aorp_import_csv', array( $this, 'import_csv' ) );
    }

    /**
     * Export CSV.
     */
    public function export_csv(): void {
        check_admin_referer( 'aorp_export_csv' );
        // TODO: implement export logic.
        wp_safe_redirect( admin_url( 'admin.php?page=aio-restaurant' ) );
        exit;
    }

    /**
     * Import CSV.
     */
    public function import_csv(): void {
        check_admin_referer( 'aorp_import_csv' );
        // TODO: implement import logic.
        wp_safe_redirect( admin_url( 'admin.php?page=aio-restaurant' ) );
        exit;
    }
}
