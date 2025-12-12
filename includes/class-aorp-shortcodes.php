<?php
namespace AIO_Restaurant_Plugin;

/**
 * Registers and renders shortcodes.
 *
 * @package AIO_Restaurant_Plugin
 * @since 1.0.0
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
     * Cache duration in seconds (1 hour).
     *
     * @var int
     */
    private const CACHE_DURATION = 3600;

    /**
     * Render search input and overlay once.
     *
     * @return void
     */
    private function render_search_ui(): void {
        if ( self::$search_rendered ) {
            return;
        }
        self::$search_rendered = true;
        echo '<div class="main-search-wrap" role="search">';
        echo '<input type="text" id="main-search" placeholder="' . esc_attr__( 'Suche...', 'aorp' ) . '" aria-label="' . esc_attr__( 'Menü durchsuchen', 'aorp' ) . '" />';
        echo '</div>';
        echo '<div id="search-dim" aria-hidden="true"></div>';
    }

    /**
     * Register shortcodes.
     *
     * @return void
     */
    public function register(): void {
        add_shortcode( 'speisekarte', array( $this, 'render_foods' ) );
        add_shortcode( 'getraenkekarte', array( $this, 'render_drinks' ) );
        add_shortcode( 'restaurant_lightswitcher', array( $this, 'render_lightswitcher' ) );
    }

    /**
     * Get cached menu items or fetch from database.
     *
     * @param string $post_type Post type to fetch.
     * @param string $taxonomy Taxonomy to filter by.
     * @param int    $term_id Term ID to filter by.
     * @return array Array of post objects.
     */
    private function get_cached_items( string $post_type, string $taxonomy, int $term_id ): array {
        $cache_key = sprintf( 'aorp_items_%s_%s_%d', $post_type, $taxonomy, $term_id );
        $items = get_transient( $cache_key );

        if ( false === $items ) {
            $items = get_posts( array(
                'post_type'      => $post_type,
                'numberposts'    => -1,
                'posts_per_page' => -1,
                'orderby'        => 'menu_order title',
                'order'          => 'ASC',
                'tax_query'      => array(
                    array(
                        'taxonomy' => $taxonomy,
                        'terms'    => $term_id,
                    ),
                ),
            ) );

            set_transient( $cache_key, $items, self::CACHE_DURATION );
        }

        return is_array( $items ) ? $items : array();
    }

    /**
     * Clear menu cache when items are updated.
     *
     * @param int $post_id Post ID being saved.
     * @return void
     */
    public static function clear_cache_on_save( int $post_id ): void {
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

        $post_type = get_post_type( $post_id );
        if ( in_array( $post_type, array( 'aorp_menu_item', 'aorp_drink_item' ), true ) ) {
            global $wpdb;
            $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aorp_items_%'" );
            $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_aorp_items_%'" );
        }
    }

    /**
     * Render food list.
     *
     * @param array $atts Shortcode attributes.
     * @return string Rendered HTML.
     */
    public function render_foods( array $atts = array() ): string {
        $options = get_option( 'aorp_options', array() );
        $col     = isset( $options['food_columns'] ) ? intval( $options['food_columns'] ) : 2;
        $columns_class = '';
        
        if ( in_array( $col, array( 2, 3 ), true ) ) {
            $columns_class = ' columns-' . $col;
        }

        $categories = get_terms( array(
            'taxonomy'   => 'aorp_menu_category',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ) );

        if ( is_wp_error( $categories ) || empty( $categories ) ) {
            return '<p>' . esc_html__( 'Keine Speisekategorien gefunden.', 'aorp' ) . '</p>';
        }

        ob_start();
        $this->render_search_ui();
        
        echo '<div class="aorp-menu' . esc_attr( $columns_class ) . '" role="region" aria-label="' . esc_attr__( 'Speisekarte', 'aorp' ) . '">';
        
        foreach ( $categories as $cat ) {
            echo '<div class="aorp-category" data-cat="' . esc_attr( $cat->term_id ) . '" role="heading" aria-level="2">';
            echo esc_html( $cat->name );
            echo '</div>';
            
            echo '<div class="aorp-items" role="list">';
            
            $items = $this->get_cached_items( 'aorp_menu_item', 'aorp_menu_category', $cat->term_id );
            
            foreach ( $items as $food ) {
                $price = get_post_meta( $food->ID, '_aorp_price', true );
                $number = get_post_meta( $food->ID, '_aorp_number', true );
                $ings = get_post_meta( $food->ID, '_aorp_ingredients', true );
                
                echo '<div class="aorp-item" role="listitem">';
                
                if ( has_post_thumbnail( $food ) ) {
                    echo '<figure class="aorp-image">';
                    echo get_the_post_thumbnail( $food, 'thumbnail', array(
                        'alt'     => esc_attr( $food->post_title ),
                        'loading' => 'lazy',
                    ) );
                    echo '</figure>';
                }
                
                echo '<div class="aorp-text">';
                echo '<div class="aorp-header">';
                
                if ( $number ) {
                    echo '<span class="aorp-number" aria-label="' . esc_attr__( 'Nummer', 'aorp' ) . '">' . esc_html( $number ) . '</span>';
                }
                
                echo '<span class="aorp-title">' . esc_html( $food->post_title ) . '</span>';
                
                if ( $price ) {
                    echo '<span class="aorp-price" aria-label="' . esc_attr__( 'Preis', 'aorp' ) . '">' . esc_html( format_price( $price ) ) . '</span>';
                }
                
                echo '</div>';
                
                if ( ! empty( $food->post_content ) ) {
                    echo '<div class="aorp-desc">' . wp_kses_post( wpautop( $food->post_content ) ) . '</div>';
                }
                
                if ( $ings ) {
                    echo '<div class="aorp-ingredients" aria-label="' . esc_attr__( 'Inhaltsstoffe', 'aorp' ) . '">';
                    echo esc_html( ingredient_labels( $ings ) );
                    echo '</div>';
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
     *
     * @param array $atts Shortcode attributes.
     * @return string Rendered HTML.
     */
    public function render_drinks( array $atts = array() ): string {
        $options = get_option( 'aorp_options', array() );
        $col     = isset( $options['drink_columns'] ) ? intval( $options['drink_columns'] ) : 2;
        $columns_class = '';
        
        if ( in_array( $col, array( 2, 3 ), true ) ) {
            $columns_class = ' columns-' . $col;
        }

        $categories = get_terms( array(
            'taxonomy'   => 'aorp_drink_category',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ) );

        if ( is_wp_error( $categories ) || empty( $categories ) ) {
            return '<p>' . esc_html__( 'Keine Getränkekategorien gefunden.', 'aorp' ) . '</p>';
        }

        ob_start();
        $this->render_search_ui();
        
        echo '<div class="aorp-menu' . esc_attr( $columns_class ) . '" role="region" aria-label="' . esc_attr__( 'Getränkekarte', 'aorp' ) . '">';
        
        foreach ( $categories as $cat ) {
            echo '<div class="aorp-category" data-cat="' . esc_attr( $cat->term_id ) . '" role="heading" aria-level="2">';
            echo esc_html( $cat->name );
            echo '</div>';
            
            echo '<div class="aorp-items" role="list">';
            
            $items = $this->get_cached_items( 'aorp_drink_item', 'aorp_drink_category', $cat->term_id );
            
            foreach ( $items as $drink ) {
                $sizes = get_post_meta( $drink->ID, '_aorp_drink_sizes', true );
                $ings  = get_post_meta( $drink->ID, '_aorp_ingredients', true );
                
                echo '<div class="aorp-item" role="listitem">';
                echo '<div class="aorp-text">';
                echo '<div class="aorp-header">';
                echo '<span class="aorp-title">' . esc_html( $drink->post_title ) . '</span>';
                echo '</div>';
                
                if ( ! empty( $drink->post_content ) ) {
                    echo '<div class="aorp-desc">' . wp_kses_post( wpautop( $drink->post_content ) ) . '</div>';
                }
                
                if ( $sizes ) {
                    echo '<ul class="aorp-drink-sizes">';
                    
                    $size_lines = explode( "\n", $sizes );
                    foreach ( $size_lines as $line ) {
                        if ( empty( trim( $line ) ) ) {
                            continue;
                        }
                        
                        $parts = array_map( 'trim', explode( '=', $line, 2 ) );
                        if ( count( $parts ) === 2 ) {
                            list( $vol, $price ) = $parts;
                            echo '<li>';
                            echo '<span class="aorp-volume">' . esc_html( $vol ) . '</span>';
                            echo '<span class="aorp-price">' . esc_html( format_price( $price ) ) . '</span>';
                            echo '</li>';
                        }
                    }
                    
                    echo '</ul>';
                }
                
                if ( $ings ) {
                    echo '<div class="aorp-ingredients" aria-label="' . esc_attr__( 'Inhaltsstoffe', 'aorp' ) . '">';
                    echo esc_html( ingredient_labels( $ings ) );
                    echo '</div>';
                }
                
                echo '</div></div>';
            }
            
            echo '</div>';
        }
        
        echo '</div>';
        
        return ob_get_clean();
    }

    /**
     * Render lightswitcher button.
     *
     * @param array $atts Shortcode attributes.
     * @return string Rendered HTML.
     */
    public function render_lightswitcher( array $atts = array() ): string {
        $is_dark = isset( $_COOKIE['aorp_dark_mode'] ) && 'on' === $_COOKIE['aorp_dark_mode'];
        $label = $is_dark ? __( 'Light Mode', 'aorp' ) : __( 'Dark Mode', 'aorp' );
        
        return sprintf(
            '<button class="aorp-dark-toggle" data-target="body" data-nonce="%s" aria-pressed="%s" aria-label="%s">%s</button>',
            esc_attr( wp_create_nonce( 'aorp_toggle_dark' ) ),
            $is_dark ? 'true' : 'false',
            esc_attr( $label ),
            esc_html( $label )
        );
    }
}

// Hook to clear cache when posts are saved
add_action( 'save_post', array( 'AIO_Restaurant_Plugin\\AORP_Shortcodes', 'clear_cache_on_save' ) );
