<?php
/**
 * Plugin Name: All-In-One WordPress Restaurant Plugin
 * Description: Moderne Speisekartenverwaltung mit REST-API und Darkmode.
 * Version: 2.0
 * Author: stb-srv
 * Text Domain: aorp
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/includes/class-loader.php';
AIO_Restaurant_Plugin\Loader::register();

use AIO_Restaurant_Plugin\AORP_Post_Types;
use AIO_Restaurant_Plugin\AORP_Shortcodes;
use AIO_Restaurant_Plugin\AORP_Admin_Pages;
use AIO_Restaurant_Plugin\AORP_CSV_Handler;
use AIO_Restaurant_Plugin\AORP_Settings;
use AIO_Restaurant_Plugin\AORP_REST_API;

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

    $settings = new AORP_Settings();
    $settings->register();

    $rest = new AORP_REST_API();
    $rest->register();
}
add_action( 'plugins_loaded', 'aorp_init_plugin' );

/**
 * Enqueue assets.
 */
function aorp_enqueue_assets(): void {
    wp_enqueue_style( 'aorp-frontend', plugins_url( 'assets/css/frontend.css', __FILE__ ), array(), '1.0' );
    wp_enqueue_script( 'aorp-frontend', plugins_url( 'assets/js/frontend.js', __FILE__ ), array( 'jquery' ), '1.0', true );
}
add_action( 'wp_enqueue_scripts', 'aorp_enqueue_assets' );

function aorp_admin_assets(): void {
    wp_enqueue_style( 'aorp-admin', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), '1.0' );
    wp_enqueue_script( 'aorp-admin', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), '1.0', true );
}
add_action( 'admin_enqueue_scripts', 'aorp_admin_assets' );
