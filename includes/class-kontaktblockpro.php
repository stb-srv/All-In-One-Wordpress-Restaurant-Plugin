<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * KontaktblockPro Modul
 *
 * Filter: "kbp_template_part"  - Pfad einzelner Template-Dateien anpassen
 * Action: "kbp_render_{slug}" - Eigene Ausgabe, falls kein Template vorhanden
 */
class AORP_KontaktblockPro {

    public function __construct() {
        add_action( 'init', array( $this, 'register_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
    }

    /**
     * Registriert den Shortcode.
     */
    public function register_shortcode() {
        add_shortcode( 'kontaktblockpro', array( $this, 'render_shortcode' ) );
    }

    /**
     * Meldet Styles an.
     */
    public function register_assets() {
        if ( is_admin() ) {
            return;
        }
        $url = plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/kontaktblockpro.css';
        wp_register_style( 'kontaktblockpro', $url, array(), '1.0' );
    }

    /**
     * Lädt die Styles im Frontend.
     */
    public function enqueue_assets() {
        if ( is_admin() ) {
            return;
        }
        wp_enqueue_style( 'kontaktblockpro' );
    }

    /**
     * Rendert den Kontaktblock.
     *
     * @param array $atts Shortcode-Attribute.
     * @return string HTML-Ausgabe
     */
    public function render_shortcode( $atts ) {
        $this->enqueue_assets();

        $parts = array(
            'welcome'  => 'kbp-welcome.php',
            'openings' => 'kbp-openings.php',
            'about'    => 'kbp-about.php',
            'contact'  => 'kbp-contact.php',
            'form'     => 'kbp-form.php',
            'map'      => 'kbp-map.php',
        );

        ob_start();
        echo '<div class="kbp-grid">';
        foreach ( $parts as $slug => $file ) {
            $path = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/' . $file;
            $path = apply_filters( 'kbp_template_part', $path, $slug );
            echo '<div class="kbp-item kbp-' . esc_attr( $slug ) . '">';
            if ( file_exists( $path ) ) {
                include $path;
            } else {
                do_action( 'kbp_render_' . $slug );
            }
            echo '</div>';
        }
        echo '</div>';

        return ob_get_clean();
    }
}

