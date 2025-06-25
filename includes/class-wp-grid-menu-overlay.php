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
        $layout  = [];
        if ( ! empty( $opts['grid_layout'] ) ) {
            $layout = json_decode( $opts['grid_layout'], true );
        }
        if ( ! is_array( $layout ) || empty( $layout ) ) {
            $layout = [
                [ 'type' => 'welcome',  'size' => 'small' ],
                [ 'type' => 'openings', 'size' => 'small' ],
                [ 'type' => 'about',    'size' => 'small' ],
                [ 'type' => 'contact',  'size' => 'small' ],
                [ 'type' => 'form',     'size' => 'small' ],
                [ 'type' => 'map',      'size' => 'small' ],
            ];
        }

        ob_start();
        ?>
        <div class="wpgmo-grid">
        <?php foreach ( $layout as $cell ) :
            $type = $cell['type'];
            $size = ( isset( $cell['size'] ) && 'large' === $cell['size'] ) ? 'large' : 'small';
            $classes = 'wpgmo-item wpgmo-' . esc_attr( $type ) . ' size-' . $size;
            switch ( $type ) {
                case 'welcome':
                    ?>
                    <div class="<?php echo $classes; ?>">
                        <h2><?php echo esc_html( $welcome ); ?></h2>
                    </div>
                    <?php
                    break;
                case 'openings':
                    if ( $hours ) :
                    ?>
                    <div class="<?php echo $classes; ?>">
                        <h2><?php _e( 'Öffnungszeiten', 'wpgmo' ); ?></h2>
                        <p><?php echo esc_html( $hours ); ?></p>
                    </div>
                    <?php
                    endif;
                    break;
                case 'about':
                    if ( $about ) :
                    ?>
                    <div class="<?php echo $classes; ?>">
                        <h2><?php _e( 'Über uns', 'wpgmo' ); ?></h2>
                        <p><?php echo esc_html( $about ); ?></p>
                    </div>
                    <?php
                    endif;
                    break;
                case 'contact':
                    if ( $address || $phone || $email ) :
                    ?>
                    <div class="<?php echo $classes; ?>">
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
                    <?php
                    endif;
                    break;
                case 'form':
                    if ( $form ) :
                    ?>
                    <div class="<?php echo $classes; ?>">
                        <?php echo do_shortcode( $form ); ?>
                    </div>
                    <?php
                    endif;
                    break;
                case 'map':
                    if ( $map ) :
                    ?>
                    <div class="<?php echo $classes; ?>">
                        <?php echo $map; ?>
                    </div>
                    <?php
                    endif;
                    break;
            }
        endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
