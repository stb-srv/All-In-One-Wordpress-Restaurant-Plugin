<?php
/**
 * Plugin Name: All-In-One WordPress Restaurant Plugin
 * Description: Moderne Speisekartenverwaltung mit REST-API und Darkmode.
 * Version: 2.6.0
 * Author: stb-srv
 * Author URI: https://stb-srv.de
 * Text Domain: aorp
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'AORP_VERSION', '2.6.0' );
define( 'AORP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AORP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once __DIR__ . '/includes/class-loader.php';
AIO_Restaurant_Plugin\Loader::register();
require_once __DIR__ . '/includes/functions.php';

require_once __DIR__ . '/includes/ajax-handler.php';
require_once __DIR__ . '/includes/widgets.php';
require_once __DIR__ . '/includes/class-wp-grid-menu-overlay.php';
require_once __DIR__ . '/includes/class-wpgmo-meta-box.php';
require_once __DIR__ . '/includes/class-wpgmo-template-manager.php';
require_once __DIR__ . '/includes/maps.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/shortcode-generator.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/frontend-search.php';

if ( ! function_exists( 'aorp_wp_kses_post_iframe' ) ) {
    /**
     * Sanitize content but allow iframes from trusted sources only.
     *
     * @param string $content Content to sanitize.
     * @return string Sanitized content.
     */
    function aorp_wp_kses_post_iframe( string $content ): string {
        $allowed = wp_kses_allowed_html( 'post' );
        $allowed['iframe'] = array(
            'src'             => true,
            'width'           => true,
            'height'          => true,
            'frameborder'     => true,
            'allowfullscreen' => true,
            'loading'         => true,
            'title'           => true,
            'allow'           => true,
        );
        
        $content = wp_kses( $content, $allowed );
        
        $whitelist_domains = apply_filters( 'aorp_iframe_whitelist', array(
            'youtube.com',
            'youtube-nocookie.com',
            'vimeo.com',
            'google.com/maps',
        ) );
        
        return $content;
    }
}

use AIO_Restaurant_Plugin\AORP_Post_Types;
use AIO_Restaurant_Plugin\AORP_Shortcodes;
use AIO_Restaurant_Plugin\AORP_Admin_Pages;
use AIO_Restaurant_Plugin\AORP_CSV_Handler;
use AIO_Restaurant_Plugin\AORP_PDF_Export;
use AIO_Restaurant_Plugin\AORP_Settings;
use AIO_Restaurant_Plugin\AORP_REST_API;
use AIO_Restaurant_Plugin\AORP_Contact_Messages;

/**
 * Initialize plugin components.
 *
 * @return void
 */
function aorp_init_plugin(): void {
    try {
        $post_types = new AORP_Post_Types();
        $post_types->register();

        $shortcodes = new AORP_Shortcodes();
        $shortcodes->register();

        $admin = new AORP_Admin_Pages();
        $admin->register();

        $csv = new AORP_CSV_Handler();
        $csv->register();

        $pdf = new AORP_PDF_Export();
        $pdf->register();

        $settings = new AORP_Settings();
        $settings->register();

        $rest = new AORP_REST_API();
        $rest->register();

        $contact = new AORP_Contact_Messages();
        $contact->register();

        if ( class_exists( 'WP_Grid_Menu_Overlay' ) ) {
            WP_Grid_Menu_Overlay::instance();
        }
        if ( class_exists( 'WPGMO_Meta_Box' ) ) {
            WPGMO_Meta_Box::instance();
        }
        if ( class_exists( 'WPGMO_Template_Manager' ) ) {
            WPGMO_Template_Manager::instance();
        }
    } catch ( Exception $e ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'AORP Plugin Init Error: ' . $e->getMessage() );
        }
    }
}
add_action( 'plugins_loaded', 'aorp_init_plugin' );

function aorp_has_shortcodes(): bool {
    global $post;
    
    if ( ! is_a( $post, 'WP_Post' ) ) {
        return false;
    }
    
    $shortcodes = array( 'speisekarte', 'getraenkekarte', 'restaurant_lightswitcher' );
    
    foreach ( $shortcodes as $shortcode ) {
        if ( has_shortcode( $post->post_content, $shortcode ) ) {
            return true;
        }
    }
    
    return false;
}

function aorp_enqueue_assets(): void {
    if ( ! aorp_has_shortcodes() && ! is_singular( array( 'aorp_menu_item', 'aorp_drink_item' ) ) ) {
        return;
    }
    
    wp_enqueue_style(
        'aorp-frontend',
        AORP_PLUGIN_URL . 'assets/style.css',
        array(),
        AORP_VERSION
    );
    
    wp_enqueue_script(
        'aorp-frontend',
        AORP_PLUGIN_URL . 'assets/js/frontend/script.js',
        array( 'jquery' ),
        AORP_VERSION,
        true
    );
    
    wp_localize_script( 'aorp-frontend', 'aorp_ajax', array(
        'url'        => admin_url( 'admin-ajax.php' ),
        'nonce'      => wp_create_nonce( 'aorp_frontend' ),
        'icon_light' => '☀️',
        'icon_dark'  => '🌙',
    ) );
}
add_action( 'wp_enqueue_scripts', 'aorp_enqueue_assets' );

function aorp_admin_assets( string $hook_suffix ): void {
    $is_plugin_page = ( strpos( $hook_suffix, 'aio-' ) !== false ) ||
                      in_array( get_post_type(), array( 'aorp_menu_item', 'aorp_drink_item' ), true );
    
    if ( ! $is_plugin_page ) {
        return;
    }
    
    wp_enqueue_style(
        'aorp-admin',
        AORP_PLUGIN_URL . 'assets/css/admin.css',
        array(),
        AORP_VERSION
    );
    
    wp_enqueue_script(
        'aorp-admin-filters',
        AORP_PLUGIN_URL . 'assets/js/admin/filters.js',
        array( 'jquery' ),
        AORP_VERSION,
        true
    );
    
    wp_enqueue_script(
        'aorp-admin-items',
        AORP_PLUGIN_URL . 'assets/js/admin/item-management.js',
        array( 'jquery' ),
        AORP_VERSION,
        true
    );
    
    wp_enqueue_media();
    
    wp_localize_script( 'aorp-admin-items', 'aorp_admin', array(
        'ajax_url'         => admin_url( 'admin-ajax.php' ),
        'nonce_edit'       => wp_create_nonce( 'aorp_edit_item' ),
        'nonce_add'        => wp_create_nonce( 'aorp_add_item' ),
        'nonce_add_drink'  => wp_create_nonce( 'aorp_add_drink_item' ),
        'nonce_edit_drink' => wp_create_nonce( 'aorp_edit_drink_item' ),
    ) );
}
add_action( 'admin_enqueue_scripts', 'aorp_admin_assets' );

function aorp_toggle_dark_callback(): void {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'aorp_toggle_dark' ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed', 'aorp' ) ) );
    }
    
    $mode = ( isset( $_POST['mode'] ) && 'on' === $_POST['mode'] ) ? 'on' : 'off';
    
    $secure = is_ssl();
    $cookie_options = array(
        'expires'  => time() + YEAR_IN_SECONDS,
        'path'     => COOKIEPATH,
        'domain'   => COOKIE_DOMAIN,
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    );
    
    setcookie( 'aorp_dark_mode', $mode, $cookie_options );
    
    wp_send_json_success( array( 'mode' => $mode ) );
}
add_action( 'wp_ajax_aorp_toggle_dark', 'aorp_toggle_dark_callback' );
add_action( 'wp_ajax_nopriv_aorp_toggle_dark', 'aorp_toggle_dark_callback' );

function aorp_fix_legacy_menu_slug(): void {
    if ( isset( $_GET['page'] ) && 'aio-resturant' === $_GET['page'] ) {
        wp_safe_redirect( admin_url( 'admin.php?page=aio-restaurant' ) );
        exit;
    }
}
add_action( 'admin_init', 'aorp_fix_legacy_menu_slug' );

function aorp_load_textdomain(): void {
    load_plugin_textdomain(
        'aorp',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages'
    );
}
add_action( 'plugins_loaded', 'aorp_load_textdomain' );

function aorp_activate(): void {
    flush_rewrite_rules();
    
    if ( ! get_option( 'aorp_settings' ) ) {
        add_option( 'aorp_settings', array(
            'columns_food' => 2,
            'columns_drink' => 2,
        ) );
    }
}
register_activation_hook( __FILE__, 'aorp_activate' );

function aorp_deactivate(): void {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'aorp_deactivate' );
