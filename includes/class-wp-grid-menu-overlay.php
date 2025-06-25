<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Grid_Menu_Overlay {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', [ $this, 'register_shortcode' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
    }

    public function register_assets() {
        if ( is_admin() ) {
            return;
        }
        wp_register_style( 'wpgmo-styles', plugin_dir_url( __FILE__ ) . '../assets/css/wp-grid-menu-overlay.css', [], '1.0' );
    }

    public function register_shortcode() {
        add_shortcode( 'wp_grid_menu_overlay', [ $this, 'render_shortcode' ] );
    }

    private function enqueue_assets() {
        if ( is_admin() ) {
            return;
        }
        wp_enqueue_style( 'wpgmo-styles' );
    }

    public function render_shortcode( $atts ) {
        $this->enqueue_assets();

        $atts      = shortcode_atts( [ 'id' => get_option( 'wpgmo_default_template' ) ], $atts, 'wp_grid_menu_overlay' );
        $templates = get_option( 'wpgmo_templates', [] );
        if ( ! isset( $templates[ $atts['id'] ] ) ) {
            return '';
        }
        $layout = $templates[ $atts['id'] ]['layout'];
        global $post;
        $content_meta = get_post_meta( $post->ID, 'wpgmo_content_' . $atts['id'], true );
        if ( ! is_array( $content_meta ) ) {
            $content_meta = [];
        }

        ob_start();
        foreach ( $layout as $row ) {
            echo '<div class="wpgmo-row">';
            foreach ( $row as $cell ) {
                $size_class = 'wpgmo-cell-' . esc_attr( $cell['size'] );
                $cell_id    = $cell['id'];
                $inner      = isset( $content_meta[ $cell_id ] ) ? do_shortcode( wp_kses_post( $content_meta[ $cell_id ] ) ) : '';
                echo "<div class='wpgmo-cell {$size_class}'>" . $inner . '</div>';
            }
            echo '</div>';
        }
        return ob_get_clean();
    }
}
