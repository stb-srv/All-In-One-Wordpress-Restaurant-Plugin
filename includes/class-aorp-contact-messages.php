<?php
namespace AIO_Restaurant_Plugin;

use function add_action;
use function admin_url;
use function delete_transient;
use function esc_url;
use function get_transient;
use function set_transient;
use function __;

/**
 * Handles contact message notifications.
 */
class AORP_Contact_Messages {
    /**
     * Register hooks.
     */
    public function register(): void {
        add_action( 'save_post_aorp_contact_message', array( $this, 'flag_new_message' ), 10, 3 );
        add_action( 'admin_notices', array( $this, 'admin_notice' ) );
    }

    /**
     * Flag a new message via transient.
     */
    public function flag_new_message( int $post_id, $post, bool $update ): void {
        if ( $update ) {
            return;
        }
        set_transient( 'aorp_new_contact_message', 1 );
    }

    /**
     * Display admin notice with link to messages page.
     */
    public function admin_notice(): void {
        if ( ! get_transient( 'aorp_new_contact_message' ) ) {
            return;
        }
        delete_transient( 'aorp_new_contact_message' );
        $url = admin_url( 'admin.php?page=aio-contact-messages' );
        echo '<div class="notice notice-info is-dismissible"><p>' . sprintf( __( 'Neue Kontaktnachricht erhalten. <a href="%s">Zur Nachrichtenseite</a>', 'aorp' ), esc_url( $url ) ) . '</p></div>';
    }
}

