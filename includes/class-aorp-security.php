<?php
namespace AIO_Restaurant_Plugin;

/**
 * Helper for nonce verification.
 */
class AORP_Security {
    /**
     * Verify a nonce and die on failure.
     */
    public function check_nonce( string $action, string $nonce_field = '_wpnonce' ): void {
        $nonce = isset( $_REQUEST[ $nonce_field ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $nonce_field ] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, $action ) ) {
            wp_die( __( 'Ungültige Anfrage.', 'aorp' ) );
        }
    }
}
