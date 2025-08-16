<?php
namespace AIO_Restaurant_Plugin;

/**
 * Registers and renders shortcodes.
 */
use function AIO_Restaurant_Plugin\format_price;
use function AIO_Restaurant_Plugin\ingredient_labels;

class AORP_Shortcodes {
    /**
     * Track if the search UI has been printed.
     *
     * @var bool
     */
    private static $search_rendered = false;

    /**
     * Render search input and overlay once.
     */
    private function render_search_ui(): void {
        if ( self::$search_rendered ) {
            return;
        }
        self::$search_rendered = true;
        echo '<div class="main-search-wrap"><input type="text" id="main-search" placeholder="' . esc_attr__( 'Suche...', 'aorp' ) . '" /></div>';
        echo '<div id="search-dim" aria-hidden="true"></div>';
    }
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
        $options = get_option( 'aorp_options', array() );
        $col     = isset( $options['food_columns'] ) ? intval( $options['food_columns'] ) : 2;
        $columns_class = '';
        if ( in_array( $col, array( 2, 3 ), true ) ) {
            $columns_class = ' columns-' . $col;
        }

        $categories = get_terms(
            'aorp_menu_category',
            array(
                'hide_empty' => false,
                'orderby'    => 'name',
                'order'      => 'ASC',
            )
        );
        if ( empty( $categories ) ) {
            $categories = array();
        }
        ob_start();
        $this->render_search_ui();
        echo '<div class="aorp-menu' . esc_attr( $columns_class ) . '">';
        foreach ( $categories as $cat ) {
            echo '<div class="aorp-category" data-cat="' . esc_attr( $cat->term_id ) . '">' . esc_html( $cat->name ) . '</div>';
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
        $options = get_option( 'aorp_options', array() );
        $col     = isset( $options['drink_columns'] ) ? intval( $options['drink_columns'] ) : 2;
        $columns_class = '';
        if ( in_array( $col, array( 2, 3 ), true ) ) {
            $columns_class = ' columns-' . $col;
        }

        $categories = get_terms(
            'aorp_drink_category',
            array(
                'hide_empty' => false,
                'orderby'    => 'name',
                'order'      => 'ASC',
            )
        );
        if ( empty( $categories ) ) {
            $categories = array();
        }
        ob_start();
        $this->render_search_ui();
        echo '<div class="aorp-menu' . esc_attr( $columns_class ) . '">';
        foreach ( $categories as $cat ) {
            echo '<div class="aorp-category" data-cat="' . esc_attr( $cat->term_id ) . '">' . esc_html( $cat->name ) . '</div>';
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
