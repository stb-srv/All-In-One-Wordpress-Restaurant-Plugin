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
        add_shortcode( 'wp_grid_menu_overlay', array( $this, 'shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
    }

    public function enqueue() {
        wp_enqueue_style( 'wp-grid-menu-overlay', plugin_dir_url( __FILE__ ) . '../assets/css/wp-grid-menu-overlay.css' );
    }

    private function get_templates() {
        if ( is_multisite() ) {
            return array_merge( get_site_option( 'wpgmo_templates_network', array() ), get_option( 'wpgmo_templates', array() ) );
        }
        return get_option( 'wpgmo_templates', array() );
    }

    public function shortcode( $atts ) {
        global $post;
        $atts = shortcode_atts( array( 'id' => get_option( 'wpgmo_default_template' ) ), $atts );
        $templates = $this->get_templates();
        if ( empty( $templates[ $atts['id'] ] ) ) {
            return '';
        }
        $layout  = $templates[ $atts['id'] ]['layout'];
        $content = get_post_meta( $post->ID, 'wpgmo_content_' . $atts['id'], true );
        $html = '<div class="wpgmo-grid">';
        foreach ( $layout as $row ) {
            $html .= '<div class="wpgmo-row">';
            foreach ( $row as $cell ) {
                $cid   = $cell['id'];
                $inner = isset( $content[ $cid ] ) ? do_shortcode( wp_kses_post( $content[ $cid ] ) ) : '';
                $size  = isset( $cell['size'] ) ? $cell['size'] : 'large';
                $html .= "<div class='wpgmo-cell wpgmo-{$size}'>" . $inner . '</div>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';
        return $html;
    }
}
