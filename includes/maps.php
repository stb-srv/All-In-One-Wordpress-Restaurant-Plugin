<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AIO_Leaflet_Map {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ) );
        add_shortcode( 'aio_leaflet_map', array( $this, 'render_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_assets' ) );
    }

    public function register_settings() {
        register_setting( 'aio_leaflet_map', 'aio_leaflet_lat', array(
            'sanitize_callback' => 'floatval',
        ) );
        register_setting( 'aio_leaflet_map', 'aio_leaflet_lng', array(
            'sanitize_callback' => 'floatval',
        ) );
        register_setting( 'aio_leaflet_map', 'aio_leaflet_zoom', array(
            'sanitize_callback' => 'intval',
        ) );
        register_setting( 'aio_leaflet_map', 'aio_leaflet_popup', array(
            'sanitize_callback' => 'sanitize_text_field',
        ) );
    }

    public function admin_enqueue_assets( $hook ) {
        if ( 'toplevel_page_aio_leaflet_map' !== $hook ) {
            return;
        }
        $plugin_url = plugin_dir_url( __FILE__ );
        wp_enqueue_style( 'leaflet', $plugin_url . '../assets/leaflet/leaflet.css' );
        wp_enqueue_script( 'leaflet', $plugin_url . '../assets/leaflet/leaflet.js', array(), null, true );
        wp_enqueue_style( 'aio-leaflet-map', $plugin_url . '../assets/css/map.css' );
        wp_enqueue_script( 'aio-leaflet-map-admin', $plugin_url . '../assets/js/map-admin.js', array( 'leaflet' ), null, true );
    }

    public function admin_menu() {
        add_menu_page( 'Karten', 'Karten', 'manage_options', 'aio_leaflet_map', array( $this, 'settings_page' ), 'dashicons-location-alt' );
    }

    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>Karten Einstellungen</h1>
            <div id="aio-leaflet-map-admin"></div>
            <form method="post" action="options.php">
                <?php settings_fields( 'aio_leaflet_map' ); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="aio_leaflet_lat">Latitude</label></th>
                        <td><input type="text" name="aio_leaflet_lat" id="aio_leaflet_lat" value="<?php echo esc_attr( get_option( 'aio_leaflet_lat', '' ) ); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="aio_leaflet_lng">Longitude</label></th>
                        <td><input type="text" name="aio_leaflet_lng" id="aio_leaflet_lng" value="<?php echo esc_attr( get_option( 'aio_leaflet_lng', '' ) ); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="aio_leaflet_zoom">Zoom</label></th>
                        <td><input type="number" name="aio_leaflet_zoom" id="aio_leaflet_zoom" value="<?php echo esc_attr( get_option( 'aio_leaflet_zoom', 15 ) ); ?>" min="1" max="20" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="aio_leaflet_popup">Popup Text</label></th>
                        <td><input type="text" class="regular-text" name="aio_leaflet_popup" id="aio_leaflet_popup" value="<?php echo esc_attr( get_option( 'aio_leaflet_popup', '' ) ); ?>" /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function maybe_enqueue_assets() {
        if ( ! is_singular() ) {
            return;
        }
        global $post;
        $has_sc = has_shortcode( $post->post_content, 'aio_leaflet_map' );
        if ( ! $has_sc ) {
            $tpl = get_post_meta( $post->ID, 'wpgmo_template', true );
            if ( ! $tpl ) {
                $tpl = get_option( 'wpgmo_default_template', is_multisite() ? get_site_option( 'wpgmo_default_template_network', '' ) : '' );
            }
            $cells = get_post_meta( $post->ID, 'wpgmo_content_' . $tpl, true );
            if ( is_array( $cells ) ) {
                foreach ( $cells as $val ) {
                    if ( has_shortcode( $val, 'aio_leaflet_map' ) ) {
                        $has_sc = true;
                        break;
                    }
                }
            }
        }

        if ( $has_sc ) {
            $plugin_url = plugin_dir_url( __FILE__ );
            wp_enqueue_style( 'leaflet', $plugin_url . '../assets/leaflet/leaflet.css' );
            wp_enqueue_script( 'leaflet', $plugin_url . '../assets/leaflet/leaflet.js', array(), null, true );
            wp_enqueue_style( 'aio-leaflet-map', $plugin_url . '../assets/css/map.css' );
            wp_enqueue_script( 'aio-leaflet-map', $plugin_url . '../assets/js/map.js', array( 'leaflet' ), null, true );
            $data = array(
                'lat'   => get_option( 'aio_leaflet_lat', 0 ),
                'lng'   => get_option( 'aio_leaflet_lng', 0 ),
                'zoom'  => (int) get_option( 'aio_leaflet_zoom', 15 ),
                'popup' => get_option( 'aio_leaflet_popup', '' ),
            );
            wp_localize_script( 'aio-leaflet-map', 'aio_leaflet_map_settings', $data );
        }
    }

    public function render_shortcode() {
        $plugin_url = plugin_dir_url( __FILE__ );

        // Ensure Leaflet assets and the initialization script are loaded
        wp_enqueue_style( 'leaflet', $plugin_url . '../assets/leaflet/leaflet.css' );
        wp_enqueue_script( 'leaflet', $plugin_url . '../assets/leaflet/leaflet.js', array(), null, true );
        wp_enqueue_style( 'aio-leaflet-map', $plugin_url . '../assets/css/map.css' );
        wp_enqueue_script( 'aio-leaflet-map', $plugin_url . '../assets/js/map.js', array( 'leaflet' ), null, true );

        // Pass saved coordinates to the initialization script
        $data = array(
            'lat'   => get_option( 'aio_leaflet_lat', 0 ),
            'lng'   => get_option( 'aio_leaflet_lng', 0 ),
            'zoom'  => (int) get_option( 'aio_leaflet_zoom', 15 ),
            'popup' => get_option( 'aio_leaflet_popup', '' ),
        );
        wp_localize_script( 'aio-leaflet-map', 'aio_leaflet_map_settings', $data );

        return '<div id="aio-leaflet-map"></div>';
    }
}

new AIO_Leaflet_Map();
