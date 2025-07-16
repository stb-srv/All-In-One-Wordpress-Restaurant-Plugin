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
        // Menu registration handled in AORP_Admin_Pages for consistent order.
    }

    /**
     * Add settings page.
     */
    public function add_settings_page(): void {
        add_submenu_page( 'aio-restaurant', 'Einstellungen', 'Einstellungen', 'manage_options', 'aio-settings', array( $this, 'render_settings_page' ) );
    }

    /**
     * Register settings and sections.
     */
    public function settings_init(): void {
        register_setting( 'aorp_settings_general', 'aorp_options' );
        register_setting( 'aorp_settings_design', 'aorp_options' );
        register_setting( 'aorp_settings_license', 'aorp_options' );

        add_settings_section( 'aorp_general', __( 'Allgemein', 'aorp' ), '__return_false', 'aorp_settings_general' );
        add_settings_field( 'food_columns', __( 'Spalten Speisekarte', 'aorp' ), array( $this, 'field_food_columns' ), 'aorp_settings_general', 'aorp_general' );
        add_settings_field( 'drink_columns', __( 'Spalten Getränkekarte', 'aorp' ), array( $this, 'field_drink_columns' ), 'aorp_settings_general', 'aorp_general' );

        add_settings_section( 'aorp_design', __( 'Design', 'aorp' ), '__return_false', 'aorp_settings_design' );
        add_settings_field( 'dark_mode', __( 'Darkmode Standard', 'aorp' ), array( $this, 'field_dark_mode' ), 'aorp_settings_design', 'aorp_design' );
        add_settings_field( 'layout_columns', __( 'Spaltenlayout', 'aorp' ), array( $this, 'field_layout_columns' ), 'aorp_settings_design', 'aorp_design' );

        add_settings_section( 'aorp_license', __( 'Lizenz', 'aorp' ), '__return_false', 'aorp_settings_license' );
        add_settings_field( 'license_key', __( 'Lizenzschlüssel', 'aorp' ), array( $this, 'field_license_key' ), 'aorp_settings_license', 'aorp_license' );
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
     * Render dark mode checkbox.
     */
    public function field_dark_mode(): void {
        $options = get_option( 'aorp_options', array() );
        $value   = ! empty( $options['dark_mode'] );
        echo '<label><input type="checkbox" name="aorp_options[dark_mode]" value="1" ' . checked( $value, true, false ) . ' /> ' . __( 'Darkmode standardmäßig aktivieren', 'aorp' ) . '</label>';
    }

    /**
     * Render layout column select.
     */
    public function field_layout_columns(): void {
        $options = get_option( 'aorp_options', array() );
        $value   = isset( $options['layout_columns'] ) ? (int) $options['layout_columns'] : 2;
        echo '<select name="aorp_options[layout_columns]">';
        foreach ( array( 1, 2, 3 ) as $col ) {
            printf( '<option value="%1$d" %2$s>%1$d</option>', $col, selected( $value, $col, false ) );
        }
        echo '</select>';
    }

    /**
     * Render license key field.
     */
    public function field_license_key(): void {
        $options = get_option( 'aorp_options', array() );
        $value   = isset( $options['license_key'] ) ? $options['license_key'] : '';
        echo '<input type="text" name="aorp_options[license_key]" value="' . esc_attr( $value ) . '" class="regular-text" />';
    }

    /**
     * Output settings page.
     */
    public function render_settings_page(): void {
        $tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general';
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Restaurant Einstellungen', 'aorp' ); ?></h1>
            <h2 class="nav-tab-wrapper">
                <a href="?page=aio-settings&tab=general" class="nav-tab<?php echo ( 'general' === $tab ) ? ' nav-tab-active' : ''; ?>"><?php esc_html_e( 'Allgemein', 'aorp' ); ?></a>
                <a href="?page=aio-settings&tab=design" class="nav-tab<?php echo ( 'design' === $tab ) ? ' nav-tab-active' : ''; ?>"><?php esc_html_e( 'Design', 'aorp' ); ?></a>
                <a href="?page=aio-settings&tab=license" class="nav-tab<?php echo ( 'license' === $tab ) ? ' nav-tab-active' : ''; ?>"><?php esc_html_e( 'Lizenz', 'aorp' ); ?></a>
            </h2>
            <form action="options.php" method="post">
                <?php
                if ( 'design' === $tab ) {
                    settings_fields( 'aorp_settings_design' );
                    do_settings_sections( 'aorp_settings_design' );
                } elseif ( 'license' === $tab ) {
                    settings_fields( 'aorp_settings_license' );
                    do_settings_sections( 'aorp_settings_license' );
                } else {
                    settings_fields( 'aorp_settings_general' );
                    do_settings_sections( 'aorp_settings_general' );
                }
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}

