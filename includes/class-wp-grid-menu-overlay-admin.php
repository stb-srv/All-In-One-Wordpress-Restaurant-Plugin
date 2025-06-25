<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Grid_Menu_Overlay_Admin {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
        add_action( 'admin_init', [ $this, 'admin_init' ] );
    }

    public function admin_menu() {
        add_menu_page(
            __( 'WP Grid Menu Overlay', 'wpgmo' ),
            __( 'WP Grid Menu', 'wpgmo' ),
            'manage_options',
            'wpgmo_settings',
            [ $this, 'render_settings_page' ],
            'dashicons-screenoptions',
            80
        );
    }

    public function admin_init() {
        register_setting( 'wpgmo_settings_group', 'wpgmo_settings', [ $this, 'sanitize_settings' ] );

        add_settings_section( 'wpgmo_main', __( 'Allgemeine Einstellungen', 'wpgmo' ), null, 'wpgmo_settings' );

        add_settings_field( 'wpgmo_welcome_title', __( 'Willkommens-Titel', 'wpgmo' ), [ $this, 'field_welcome_title' ], 'wpgmo_settings', 'wpgmo_main' );
        add_settings_field( 'wpgmo_opening_hours', __( 'Öffnungszeiten', 'wpgmo' ), [ $this, 'field_opening_hours' ], 'wpgmo_settings', 'wpgmo_main' );
        add_settings_field( 'wpgmo_about_text', __( 'Über uns Text', 'wpgmo' ), [ $this, 'field_about_text' ], 'wpgmo_settings', 'wpgmo_main' );
        add_settings_field( 'wpgmo_contact_address', __( 'Kontakt-Adresse', 'wpgmo' ), [ $this, 'field_contact_address' ], 'wpgmo_settings', 'wpgmo_main' );
        add_settings_field( 'wpgmo_contact_phone', __( 'Telefon', 'wpgmo' ), [ $this, 'field_contact_phone' ], 'wpgmo_settings', 'wpgmo_main' );
        add_settings_field( 'wpgmo_contact_email', __( 'E-Mail', 'wpgmo' ), [ $this, 'field_contact_email' ], 'wpgmo_settings', 'wpgmo_main' );
        add_settings_field( 'wpgmo_form_shortcode', __( 'Formular Shortcode', 'wpgmo' ), [ $this, 'field_form_shortcode' ], 'wpgmo_settings', 'wpgmo_main' );
        add_settings_field( 'wpgmo_map_embed', __( 'Karte einbetten', 'wpgmo' ), [ $this, 'field_map_embed' ], 'wpgmo_settings', 'wpgmo_main' );
    }

    public function render_settings_page() {
        echo '<div class="wrap"><h1>' . esc_html__( 'WP Grid Menu Overlay', 'wpgmo' ) . '</h1>';
        ?>
        <form method="post" action="options.php">
            <?php settings_fields( 'wpgmo_settings_group' ); ?>
            <?php do_settings_sections( 'wpgmo_settings' ); ?>
            <?php submit_button(); ?>
        </form>
        <?php
        echo '</div>';
    }

    public function field_welcome_title() {
        $opts  = get_option( 'wpgmo_settings', [] );
        $value = esc_attr( $opts['welcome_title'] ?? '' );
        echo '<input type="text" name="wpgmo_settings[welcome_title]" value="' . $value . '" class="regular-text" />';
    }

    public function field_opening_hours() {
        $opts  = get_option( 'wpgmo_settings', [] );
        $value = esc_attr( $opts['opening_hours'] ?? '' );
        echo '<input type="text" name="wpgmo_settings[opening_hours]" value="' . $value . '" class="regular-text" />';
    }

    public function field_about_text() {
        $opts  = get_option( 'wpgmo_settings', [] );
        $value = esc_textarea( $opts['about_text'] ?? '' );
        echo '<textarea name="wpgmo_settings[about_text]" rows="5" class="large-text">' . $value . '</textarea>';
    }

    public function field_contact_address() {
        $opts  = get_option( 'wpgmo_settings', [] );
        $value = esc_textarea( $opts['contact_address'] ?? '' );
        echo '<textarea name="wpgmo_settings[contact_address]" rows="3" class="large-text">' . $value . '</textarea>';
    }

    public function field_contact_phone() {
        $opts  = get_option( 'wpgmo_settings', [] );
        $value = esc_attr( $opts['contact_phone'] ?? '' );
        echo '<input type="text" name="wpgmo_settings[contact_phone]" value="' . $value . '" class="regular-text" />';
    }

    public function field_contact_email() {
        $opts  = get_option( 'wpgmo_settings', [] );
        $value = esc_attr( $opts['contact_email'] ?? '' );
        echo '<input type="email" name="wpgmo_settings[contact_email]" value="' . $value . '" class="regular-text" />';
    }

    public function field_form_shortcode() {
        $opts  = get_option( 'wpgmo_settings', [] );
        $value = esc_attr( $opts['form_shortcode'] ?? '' );
        echo '<input type="text" name="wpgmo_settings[form_shortcode]" value="' . $value . '" class="regular-text" />';
    }

    public function field_map_embed() {
        $opts  = get_option( 'wpgmo_settings', [] );
        $value = esc_textarea( $opts['map_embed'] ?? '' );
        echo '<textarea name="wpgmo_settings[map_embed]" rows="5" class="large-text">' . $value . '</textarea>';
    }

    public function sanitize_settings( $input ) {
        $defaults = [
            'welcome_title'   => 'Willkommen',
            'opening_hours'   => '',
            'about_text'      => '',
            'contact_address' => '',
            'contact_phone'   => '',
            'contact_email'   => '',
            'form_shortcode'  => '',
            'map_embed'       => '',
        ];

        $output                     = [];
        $output['welcome_title']    = sanitize_text_field( $input['welcome_title'] ?? $defaults['welcome_title'] );
        $output['opening_hours']    = sanitize_text_field( $input['opening_hours'] ?? $defaults['opening_hours'] );
        $output['about_text']       = sanitize_textarea_field( $input['about_text'] ?? $defaults['about_text'] );
        $output['contact_address']  = sanitize_textarea_field( $input['contact_address'] ?? $defaults['contact_address'] );
        $output['contact_phone']    = sanitize_text_field( $input['contact_phone'] ?? $defaults['contact_phone'] );
        $output['contact_email']    = sanitize_email( $input['contact_email'] ?? $defaults['contact_email'] );
        $output['form_shortcode']   = wp_kses_post( $input['form_shortcode'] ?? $defaults['form_shortcode'] );
        $output['map_embed']        = wp_kses_post( $input['map_embed'] ?? $defaults['map_embed'] );

        return $output;
    }
}
