<?php
namespace AIO_Restaurant_Plugin;

/**
 * Registers and renders shortcodes.
 */
use function AIO_Restaurant_Plugin\format_price;
use function AIO_Restaurant_Plugin\ingredient_labels;

class AORP_Shortcodes {
    /**
     * Register shortcodes.
     */
    public function register(): void {
        add_shortcode( 'speisekarte', array( $this, 'render_foods' ) );
        add_shortcode( 'getraenkekarte', array( $this, 'render_drinks' ) );
        add_shortcode( 'restaurant_lightswitcher', array( $this, 'render_lightswitcher' ) );
    }

    /**
     * Render food list.
     */
    public function render_foods( array $atts = array() ): string {
        $atts = shortcode_atts( array(
            'columns' => 2,
        ), $atts, 'speisekarte' );

        $columns_class = '';
        $col = intval( $atts['columns'] );
        if ( in_array( $col, array( 2, 3 ), true ) ) {
            $columns_class = ' columns-' . $col;
        }

        $categories = get_terms( 'aorp_menu_category', array( 'hide_empty' => false ) );
        if ( empty( $categories ) ) {
            $categories = array();
        }
        ob_start();
        echo '<div class="aorp-search-wrapper"><input type="text" id="aorp-search-input" placeholder="Suche..." /><button id="aorp-close-cats" class="aorp-close-cats">Kategorien schließen</button><div id="aorp-search-results"></div></div>';
        echo '<p class="aorp-note">Bitte klicken Sie auf die Kategorien, um die Speisen zu sehen.</p>';
        echo '<div class="aorp-menu' . esc_attr( $columns_class ) . '">';
        foreach ( $categories as $cat ) {
            echo '<div class="aorp-category">' . esc_html( $cat->name ) . '</div>';
            echo '<div class="aorp-items">';
            $items = get_posts( array( 'post_type' => 'aorp_menu_item', 'numberposts' => -1, 'tax_query' => array( array( 'taxonomy' => 'aorp_menu_category', 'terms' => $cat->term_id ) ) ) );
            foreach ( $items as $food ) {
                $price = get_post_meta( $food->ID, '_aorp_price', true );
                $number = get_post_meta( $food->ID, '_aorp_number', true );
                $ings = get_post_meta( $food->ID, '_aorp_ingredients', true );
                echo '<div class="aorp-item">';
                if ( has_post_thumbnail( $food ) ) {
                    echo get_the_post_thumbnail( $food, 'thumbnail' );
                }
                echo '<div class="aorp-text">';
                echo '<div class="aorp-header">';
                if ( $number ) {
                    echo '<span class="aorp-number">' . esc_html( $number ) . '</span>';
                }
                echo '<span class="aorp-title">' . esc_html( $food->post_title ) . '</span>';
                if ( $price ) {
                    echo '<span class="aorp-price">' . esc_html( format_price( $price ) ) . '</span>';
                }
                echo '</div>';
                if ( $food->post_content ) {
                    echo '<div class="aorp-desc">' . wp_kses_post( wpautop( $food->post_content ) ) . '</div>';
                }
                if ( $ings ) {
                    echo '<div class="aorp-ingredients">' . esc_html( ingredient_labels( $ings ) ) . '</div>';
                }
                echo '</div></div>';
            }
            echo '</div>';
        }
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * Render drink list.
     */
    public function render_drinks( array $atts = array() ): string {
        $atts = shortcode_atts( array(
            'columns' => 2,
        ), $atts, 'getraenkekarte' );

        $columns_class = '';
        $col = intval( $atts['columns'] );
        if ( in_array( $col, array( 2, 3 ), true ) ) {
            $columns_class = ' columns-' . $col;
        }

        $categories = get_terms( 'aorp_drink_category', array( 'hide_empty' => false ) );
        if ( empty( $categories ) ) {
            $categories = array();
        }
        ob_start();
        echo '<div class="aorp-search-wrapper"><input type="text" id="aorp-search-input" placeholder="Suche..." /><button id="aorp-close-cats" class="aorp-close-cats">Kategorien schließen</button><div id="aorp-search-results"></div></div>';
        echo '<p class="aorp-note">Bitte klicken Sie auf die Kategorien, um die Speisen zu sehen.</p>';
        echo '<div class="aorp-menu' . esc_attr( $columns_class ) . '">';
        foreach ( $categories as $cat ) {
            echo '<div class="aorp-category">' . esc_html( $cat->name ) . '</div>';
            echo '<div class="aorp-items">';
            $items = get_posts( array( 'post_type' => 'aorp_drink_item', 'numberposts' => -1, 'tax_query' => array( array( 'taxonomy' => 'aorp_drink_category', 'terms' => $cat->term_id ) ) ) );
            foreach ( $items as $drink ) {
                $sizes = get_post_meta( $drink->ID, '_aorp_drink_sizes', true );
                $ings  = get_post_meta( $drink->ID, '_aorp_ingredients', true );
                echo '<div class="aorp-item">';
                echo '<div class="aorp-text">';
                echo '<div class="aorp-header">';
                echo '<span class="aorp-title">' . esc_html( $drink->post_title ) . '</span>';
                echo '</div>';
                if ( $drink->post_content ) {
                    echo '<div class="aorp-desc">' . wp_kses_post( wpautop( $drink->post_content ) ) . '</div>';
                }
                if ( $sizes ) {
                    echo '<ul class="aorp-drink-sizes">';
                    foreach ( explode( "\n", $sizes ) as $line ) {
                        list( $vol, $price ) = array_map( 'trim', explode( '=', $line ) );
                        echo '<li><span>' . esc_html( $vol ) . '</span><span>' . esc_html( format_price( $price ) ) . '</span></li>';
                    }
                    echo '</ul>';
                }
                if ( $ings ) {
                    echo '<div class="aorp-ingredients">' . esc_html( ingredient_labels( $ings ) ) . '</div>';
                }
                echo '</div></div>';
            }
            echo '</div>';
        }
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * Render lightswitcher.
     */
    public function render_lightswitcher(): string {
        return '<button class="aorp-dark-toggle" data-target="body">Dark Mode</button>';
    }
}
