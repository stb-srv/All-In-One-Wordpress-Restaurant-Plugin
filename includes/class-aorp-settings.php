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
        /* Design and license settings removed */

        add_settings_section( 'aorp_general', __( 'Allgemein', 'aorp' ), '__return_false', 'aorp_settings_general' );
        add_settings_field( 'food_columns', __( 'Spalten Speisekarte', 'aorp' ), array( $this, 'field_food_columns' ), 'aorp_settings_general', 'aorp_general' );
        add_settings_field( 'drink_columns', __( 'Spalten Getränkekarte', 'aorp' ), array( $this, 'field_drink_columns' ), 'aorp_settings_general', 'aorp_general' );

        /* Design and license sections removed */
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

    /* Design and license field renderers removed */

    /**
     * Output settings page.
     */
    public function render_settings_page(): void {
        $tab = 'general';
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Restaurant Einstellungen', 'aorp' ); ?></h1>
            <h2 class="nav-tab-wrapper">
                <a href="?page=aio-settings&tab=general" class="nav-tab nav-tab-active"><?php esc_html_e( 'Allgemein', 'aorp' ); ?></a>
            </h2>
            <form action="options.php" method="post">
                <?php
                settings_fields( 'aorp_settings_general' );
                do_settings_sections( 'aorp_settings_general' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}

