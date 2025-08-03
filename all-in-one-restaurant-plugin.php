<?php
/**
 * Plugin Name: All-In-One WordPress Restaurant Plugin
 * Description: Moderne Speisekartenverwaltung mit REST-API und Darkmode.
 * Version: 2.1.0
 * Author: stb-srv
 * Text Domain: aorp
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/includes/class-loader.php';
AIO_Restaurant_Plugin\Loader::register();
require_once __DIR__ . '/includes/functions.php';

require_once __DIR__ . '/includes/ajax-handler.php';
require_once __DIR__ . '/includes/widgets.php';
require_once __DIR__ . '/includes/class-wp-grid-menu-overlay.php';
require_once __DIR__ . '/includes/class-wpgmo-meta-box.php';
require_once __DIR__ . '/includes/class-wpgmo-template-manager.php';
require_once __DIR__ . '/includes/maps.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/settings.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/import-export.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/shortcode-generator.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/frontend-search.php';

if ( ! function_exists( 'aorp_wp_kses_post_iframe' ) ) {
    /**
     * Sanitize content but allow iframes.
     */
    function aorp_wp_kses_post_iframe( $content ) {
        $allowed = wp_kses_allowed_html( 'post' );
        $allowed['iframe'] = array(
            'src'             => true,
            'width'           => true,
            'height'          => true,
            'frameborder'     => true,
            'allowfullscreen' => true,
            'loading'         => true,
        );
        return wp_kses( $content, $allowed );
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
 */
function aorp_init_plugin(): void {
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
}
add_action( 'plugins_loaded', 'aorp_init_plugin' );

/**
 * Enqueue assets.
 */
function aorp_enqueue_assets(): void {
    wp_enqueue_style( 'aorp-frontend', plugins_url( 'assets/style.css', __FILE__ ), array(), '1.0' );
    wp_enqueue_script( 'aorp-frontend', plugins_url( 'assets/js/frontend/script.js', __FILE__ ), array( 'jquery' ), '1.0', true );
    wp_localize_script( 'aorp-frontend', 'aorp_ajax', array(
        'url'        => admin_url( 'admin-ajax.php' ),
        'icon_light' => '☀️',
        'icon_dark'  => '🌙',
    ) );
}
add_action( 'wp_enqueue_scripts', 'aorp_enqueue_assets' );

function aorp_admin_assets(): void {
    wp_enqueue_style( 'aorp-admin', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), '1.0' );
    wp_enqueue_script( 'aorp-admin-filters', plugins_url( 'assets/js/admin/filters.js', __FILE__ ), array( 'jquery' ), '1.0', true );
    wp_enqueue_script( 'aorp-admin-items', plugins_url( 'assets/js/admin/item-management.js', __FILE__ ), array( 'jquery' ), '1.0', true );
    wp_enqueue_media();
    wp_localize_script( 'aorp-admin-items', 'aorp_admin', array(
        'ajax_url'   => admin_url( 'admin-ajax.php' ),
        'nonce_edit' => wp_create_nonce( 'aorp_edit_item' ),
    ) );
}
add_action( 'admin_enqueue_scripts', 'aorp_admin_assets' );

function aorp_toggle_dark_callback() {
    $mode = ( isset( $_POST['mode'] ) && 'on' === $_POST['mode'] ) ? 'on' : 'off';
    setcookie( 'aorp_dark_mode', $mode, time() + YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
    wp_die();
}
add_action( 'wp_ajax_aorp_toggle_dark', 'aorp_toggle_dark_callback' );
add_action( 'wp_ajax_nopriv_aorp_toggle_dark', 'aorp_toggle_dark_callback' );
