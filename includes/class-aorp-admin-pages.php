<?php
namespace AIO_Restaurant_Plugin;

use WP_List_Table;

/**
 * Handles admin pages.
 */
class AORP_Admin_Pages {
    /**
     * Register admin menu.
     */
    public function register(): void {
        add_action( 'admin_menu', array( $this, 'menu' ) );
    }

    /**
     * Add top level menu.
     */
    public function menu(): void {
        add_menu_page(
            'Speisekarte',
            'Speisekarte',
            'manage_options',
            'aorp_manage',
            array( $this, 'render_page' ),
            'dashicons-carrot',
            26
        );
    }

    /**
     * Render manage page.
     */
    public function render_page(): void {
        echo '<div class="wrap"><h1>Speisekarte</h1>';
        echo '<p>' . esc_html__( 'Hier werden künftig die Speisen verwaltet.', 'aorp' ) . '</p>';
        echo '</div>';
    }
}
