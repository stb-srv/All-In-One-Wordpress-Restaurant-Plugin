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
        add_submenu_page( 'aorp_manage', 'Einstellungen', 'Einstellungen', 'manage_options', 'aorp_settings', array( $this, 'render_settings_page' ) );
    }

    /**
     * Register settings and sections.
     */
    public function settings_init(): void {
        register_setting( 'aorp_settings', 'aorp_options' );

        add_settings_section( 'aorp_general', __( 'Allgemein', 'aorp' ), '__return_false', 'aorp_settings' );
        add_settings_field( 'license_key', __( 'Lizenzschlüssel', 'aorp' ), array( $this, 'field_license_key' ), 'aorp_settings', 'aorp_general' );
        add_settings_field( 'food_columns', __( 'Spalten Speisekarte', 'aorp' ), array( $this, 'field_food_columns' ), 'aorp_settings', 'aorp_general' );
        add_settings_field( 'drink_columns', __( 'Spalten Getränkekarte', 'aorp' ), array( $this, 'field_drink_columns' ), 'aorp_settings', 'aorp_general' );
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
     * Render food column select.
     */
    public function field_food_columns(): void {
        $options = get_option( 'aorp_options', array() );
        $value   = isset( $options['food_columns'] ) ? (int) $options['food_columns'] : 2;
        echo '<select name="aorp_options[food_columns]">';
        foreach ( array( 2, 3 ) as $col ) {
            printf( '<option value="%1$d" %2$s>%1$d</option>', $col, selected( $value, $col, false ) );
        }
        echo '</select>';
    }

    /**
     * Render drink column select.
     */
    public function field_drink_columns(): void {
        $options = get_option( 'aorp_options', array() );
        $value   = isset( $options['drink_columns'] ) ? (int) $options['drink_columns'] : 2;
        echo '<select name="aorp_options[drink_columns]">';
        foreach ( array( 2, 3 ) as $col ) {
            printf( '<option value="%1$d" %2$s>%1$d</option>', $col, selected( $value, $col, false ) );
        }
        echo '</select>';
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
