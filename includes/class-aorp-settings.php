<?php
namespace AIO_Restaurant_Plugin;

/**
 * Plugin settings page.
 */
class AORP_Settings {
    /**
     * Register settings hooks.
     */
    public function register(): void {
        add_action( 'admin_init', array( $this, 'settings_init' ) );
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
    }

    /**
     * Add settings page.
     */
    public function add_settings_page(): void {
        add_options_page( 'Restaurant Einstellungen', 'Restaurant', 'manage_options', 'aorp_settings', array( $this, 'render_settings_page' ) );
    }

    /**
     * Register settings and sections.
     */
    public function settings_init(): void {
        register_setting( 'aorp_settings', 'aorp_options' );

        add_settings_section( 'aorp_general', __( 'Allgemein', 'aorp' ), '__return_false', 'aorp_settings' );
        add_settings_field( 'license_key', __( 'Lizenzschlüssel', 'aorp' ), array( $this, 'field_license_key' ), 'aorp_settings', 'aorp_general' );
    }

    /**
     * Render license field.
     */
    public function field_license_key(): void {
        $options = get_option( 'aorp_options', array() );
        $value   = isset( $options['license_key'] ) ? esc_attr( $options['license_key'] ) : '';
        echo '<input type="text" name="aorp_options[license_key]" value="' . $value . '" class="regular-text" />';
    }

    /**
     * Output settings page.
     */
    public function render_settings_page(): void {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Restaurant Einstellungen', 'aorp' ); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( 'aorp_settings' );
                do_settings_sections( 'aorp_settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}
