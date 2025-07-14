<?php
namespace AIO_Restaurant_Plugin;

use WP_List_Table;

/**
 * Handles admin pages.
 */
use function AIO_Restaurant_Plugin\format_price;
use function AIO_Restaurant_Plugin\ingredient_labels;

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
            'AIO-Restaurant',
            'AIO-Restaurant',
            'manage_options',
            'aorp_manage',
            array( $this, 'render_page' ),
            'dashicons-carrot',
            26
        );

        add_submenu_page(
            'aorp_manage',
            'Speisekarte',
            'Speisekarte',
            'manage_options',
            'aorp_manage',
            array( $this, 'render_page' )
        );

        add_submenu_page(
            'aorp_manage',
            'Getränkekarte',
            'Getränkekarte',
            'manage_options',
            'aorp_manage_drinks',
            array( $this, 'render_drink_page' )
        );

    }

    public function render_drink_page(): void {
        $_GET['tab'] = 'drinks';
        $this->render_page();
    }

    /**
     * Render manage page.
     */
    public function render_page(): void {
        $tab = ( isset( $_GET['tab'] ) && 'drinks' === $_GET['tab'] ) ? 'drinks' : 'foods';
        $orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'title';
        $order   = isset( $_GET['order'] ) && 'DESC' === strtoupper( $_GET['order'] ) ? 'DESC' : 'ASC';

        $post_type = ( 'drinks' === $tab ) ? 'aorp_drink_item' : 'aorp_menu_item';
        $items = get_posts( array( 'post_type' => $post_type, 'numberposts' => -1, 'orderby' => $orderby, 'order' => $order ) );
        $cats  = get_terms( ( 'drinks' === $tab ) ? 'aorp_drink_category' : 'aorp_menu_category', array( 'hide_empty' => false ) );
        $ings  = get_posts( array( 'post_type' => 'aorp_ingredient', 'numberposts' => -1 ) );

        echo '<div class="wrap">';
        echo '<h1>Speisekarte</h1>';
        echo '<h2 class="nav-tab-wrapper">';
        echo '<a href="?page=aorp_manage&tab=foods" class="nav-tab' . ( 'foods' === $tab ? ' nav-tab-active' : '' ) . '">Speisen</a>';
        echo '<a href="?page=aorp_manage&tab=drinks" class="nav-tab' . ( 'drinks' === $tab ? ' nav-tab-active' : '' ) . '">Getränke</a>';
        echo '</h2>';

        // Add form
        $nonce = wp_create_nonce( 'aorp_add_' . ( 'drinks' === $tab ? 'drink_item' : 'item' ) );
        echo '<form class="aorp-add-form" data-action="aorp_add_' . ( 'drinks' === $tab ? 'drink_item' : 'item' ) . '">';
        echo '<input type="hidden" name="nonce" value="' . esc_attr( $nonce ) . '" />';
        echo '<p><input type="text" name="item_title" placeholder="Name" required /></p>';
        echo '<p><textarea name="item_description" placeholder="Beschreibung"></textarea></p>';
        if ( 'foods' === $tab ) {
            echo '<p><input type="text" name="item_price" placeholder="Preis" /></p>';
            echo '<p><input type="text" name="item_number" placeholder="Nummer" /></p>';
        } else {
            echo '<p><textarea name="item_sizes" placeholder="Größe=Preis pro Zeile"></textarea></p>';
        }
        if ( $cats ) {
            echo '<p><select name="item_category"><option value="">Kategorie</option>';
            foreach ( $cats as $cat ) {
                echo '<option value="' . esc_attr( $cat->term_id ) . '">' . esc_html( $cat->name ) . '</option>';
            }
            echo '</select></p>';
        }
        echo '<p><select class="aorp-ing-select"><option value="">Inhaltsstoff wählen</option>';
        foreach ( $ings as $ing ) {
            $code = get_post_meta( $ing->ID, '_aorp_ing_code', true );
            echo '<option value="' . esc_attr( $code ) . '">' . esc_html( $ing->post_title ) . '</option>';
        }
        echo '</select></p>';
        echo '<div class="aorp-selected"></div>';
        echo '<input type="hidden" name="item_ingredients" class="aorp-ing-text" />';
        echo '<p><button type="submit" class="button button-primary">Hinzufügen</button></p>';
        echo '</form>';

        $mode    = ( 'drinks' === $tab ) ? 'drink' : 'food';
        $orderby = $orderby;
        $order   = $order;
        include dirname( __DIR__ ) . '/admin/item-list.php';

        echo '</div>'; // wrap
    }
}
