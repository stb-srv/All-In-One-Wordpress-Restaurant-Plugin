<?php
/**
 * Plugin Name:       All-In-One-WordPress-Restaurant-Plugin
 * Plugin URI:        https://example.com/aiwrp
 * Description:       Komplettlösung für digitale Speisekarte, Lightswitcher (Dark Mode) und Verwaltung – selbsterklärend und vollständig auf Deutsch.
 * Version:           1.0.0
 * Author:            Dein Name
 * Text Domain:       aiwrp
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) exit;

spl_autoload_register( function( $class ) {
    if ( 0 !== strpos( $class, 'AIWRP\\' ) ) return;
    $file = __DIR__ . '/includes/' . str_replace( '\\', '/', substr( $class, 6 ) ) . '.php';
    if ( file_exists( $file ) ) require_once $file;
} );

define( 'AIWRP_VERSION', '1.0.0' );
define( 'AIWRP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AIWRP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

add_action( 'plugins_loaded', function() {
    load_plugin_textdomain( 'aiwrp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
} );

add_action( 'init', array( 'AIWRP\\Admin\\Post_Types', 'register_post_types' ) );
add_action( 'admin_menu', array( 'AIWRP\\Admin\\Menu_Admin', 'add_menu_pages' ) );

add_action( 'init', array( 'AIWRP\\Frontend\\Shortcode_Menu', 'register' ) );
add_action( 'init', array( 'AIWRP\\Frontend\\Shortcode_Legende', 'register' ) );
add_action( 'wp_enqueue_scripts', array( 'AIWRP\\Lightswitcher', 'enqueue_assets' ) );

AIWRP\\Lightswitcher::init();
?>