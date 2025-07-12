<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Outputs grid overlays on the frontend.
 *
 * @package AIO_Restaurant_Plugin
 */
class WP_Grid_Menu_Overlay {
    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }


/**
 * __construct
 *
 * @return void
 */
    private function __construct() {
        add_shortcode( 'wp_grid_menu_overlay', array( $this, 'shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
    }


/**
 * enqueue
 *
 * @return void
 */
    public function enqueue() {
        wp_enqueue_style( 'wp-grid-menu-overlay', plugin_dir_url( __FILE__ ) . '../assets/css/wp-grid-menu-overlay.css' );
    }


/**
 * get_templates
 *
 * @return void
 */
    private function get_templates() {
        if ( is_multisite() ) {
            return array_merge( get_site_option( 'wpgmo_templates_network', array() ), get_option( 'wpgmo_templates', array() ) );
        }
        return get_option( 'wpgmo_templates', array() );
    }


/**
 * shortcode
 *
 * @return void
 */
    public function shortcode( $atts ) {
        global $post;
        $default = get_option( 'wpgmo_default_template', is_multisite() ? get_site_option( 'wpgmo_default_template_network', '' ) : '' );
        $atts = shortcode_atts( array( 'id' => $default ), $atts );
        $templates = $this->get_templates();
        if ( empty( $templates[ $atts['id'] ] ) ) {
            return '';
        }
        $layout   = $templates[ $atts['id'] ]['layout'];
        $content  = get_post_meta( $post->ID, 'wpgmo_content_' . $atts['id'], true );
        $defaults = get_option( 'wpgmo_default_content', array() );
        $tpl_def  = isset( $defaults[ $atts['id'] ] ) ? $defaults[ $atts['id'] ] : array();
        $html = '<div class="wpgmo-grid">';
        foreach ( $layout as $row ) {
            $html .= '<div class="wpgmo-row">';
            foreach ( $row as $cell ) {
                $cid   = $cell['id'];
                if ( ! empty( $content[ $cid ] ) ) {
                    $raw   = $content[ $cid ];
                } elseif ( isset( $tpl_def[ $cid ] ) ) {
                    $raw   = $tpl_def[ $cid ];
                } else {
                    $raw   = '';
                }
                $inner = apply_filters( 'the_content', aorp_wp_kses_post_iframe( $raw ) );
                $inner = do_shortcode( $inner );
                $size  = isset( $cell['size'] ) ? $cell['size'] : 'large';
                $html .= "<div class='wpgmo-cell wpgmo-{$size}'>" . $inner . '</div>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';
        return $html;
    }
}
