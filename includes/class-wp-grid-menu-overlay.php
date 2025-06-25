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

        $atts = shortcode_atts( [ 'id' => 0 ], $atts, 'wp_grid_menu_overlay' );
        $opts = [];
        if ( $atts['id'] ) {
            $shortcodes = get_option( 'wpgmo_custom_shortcodes', [] );
            foreach ( $shortcodes as $sc ) {
                if ( $sc['id'] == intval( $atts['id'] ) ) {
                    $opts = $sc;
                    break;
                }
            }
        }
        if ( ! $opts ) {
            $opts = get_option( 'wpgmo_settings', [] );
        }
        $welcome = sanitize_text_field( $opts['welcome_title'] ?? __( 'Willkommen', 'wpgmo' ) );
        $hours   = sanitize_text_field( $opts['opening_hours'] ?? '' );
        $about   = sanitize_textarea_field( $opts['about_text'] ?? '' );
        $address = sanitize_textarea_field( $opts['contact_address'] ?? '' );
        $phone   = sanitize_text_field( $opts['contact_phone'] ?? '' );
        $email   = sanitize_email( $opts['contact_email'] ?? '' );
        $form    = wp_kses_post( $opts['form_shortcode'] ?? '' );
        $map     = wp_kses_post( $opts['map_embed'] ?? '' );

        ob_start();
        ?>
        <div class="wpgmo-grid">
            <div class="wpgmo-item wpgmo-welcome">
                <h2><?php echo esc_html( $welcome ); ?></h2>
            </div>
            <?php if ( $hours ) : ?>
            <div class="wpgmo-item wpgmo-openings">
                <h2><?php _e( 'Öffnungszeiten', 'wpgmo' ); ?></h2>
                <p><?php echo esc_html( $hours ); ?></p>
            </div>
            <?php endif; ?>
            <?php if ( $about ) : ?>
            <div class="wpgmo-item wpgmo-about">
                <h2><?php _e( 'Über uns', 'wpgmo' ); ?></h2>
                <p><?php echo esc_html( $about ); ?></p>
            </div>
            <?php endif; ?>
            <?php if ( $address || $phone || $email ) : ?>
            <div class="wpgmo-item wpgmo-contact">
                <h2><?php _e( 'Kontakt', 'wpgmo' ); ?></h2>
                <?php if ( $address ) : ?>
                    <p><?php echo nl2br( esc_html( $address ) ); ?></p>
                <?php endif; ?>
                <?php if ( $phone ) : ?>
                    <p><?php echo esc_html( $phone ); ?></p>
                <?php endif; ?>
                <?php if ( $email ) : ?>
                    <p><a href="mailto:<?php echo antispambot( esc_attr( $email ) ); ?>"><?php echo antispambot( esc_html( $email ) ); ?></a></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php if ( $form ) : ?>
            <div class="wpgmo-item wpgmo-form">
                <?php echo do_shortcode( $form ); ?>
            </div>
            <?php endif; ?>
            <?php if ( $map ) : ?>
            <div class="wpgmo-item wpgmo-map">
                <?php echo $map; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
