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
        add_submenu_page( 'aorp_manage', 'Karteninformationen/Einstellungen', 'Karteninformationen/Einstellungen', 'manage_options', 'aorp_settings', array( $this, 'render_settings_page' ) );
    }

    /**
     * Register settings and sections.
     */
    public function settings_init(): void {
        register_setting( 'aorp_settings', 'aorp_options' );

        add_settings_section( 'aorp_general', __( 'Allgemein', 'aorp' ), '__return_false', 'aorp_settings' );
        add_settings_field( 'food_columns', __( 'Spalten Speisekarte', 'aorp' ), array( $this, 'field_food_columns' ), 'aorp_settings', 'aorp_general' );
        add_settings_field( 'drink_columns', __( 'Spalten Getränkekarte', 'aorp' ), array( $this, 'field_drink_columns' ), 'aorp_settings', 'aorp_general' );
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
        $tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general';
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Restaurant Einstellungen', 'aorp' ); ?></h1>
            <h2 class="nav-tab-wrapper">
                <a href="?page=aorp_settings&tab=general" class="nav-tab<?php echo ( 'general' === $tab ) ? ' nav-tab-active' : ''; ?>"><?php esc_html_e( 'Allgemein', 'aorp' ); ?></a>
                <a href="?page=aorp_settings&tab=importexport" class="nav-tab<?php echo ( 'importexport' === $tab ) ? ' nav-tab-active' : ''; ?>"><?php esc_html_e( 'Import/Export', 'aorp' ); ?></a>
            </h2>
            <?php if ( 'importexport' === $tab ) : ?>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
                    <?php wp_nonce_field( 'aorp_import_csv' ); ?>
                    <input type="hidden" name="action" value="aorp_import_csv" />
                    <p><input type="file" name="csv_file" accept=".csv" /></p>
                    <?php submit_button( __( 'Importieren', 'aorp' ) ); ?>
                </form>
                <hr />
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <?php wp_nonce_field( 'aorp_export_csv' ); ?>
                    <input type="hidden" name="action" value="aorp_export_csv" />
                    <?php submit_button( __( 'Exportieren', 'aorp' ) ); ?>
                </form>
            <?php else : ?>
                <form action="options.php" method="post">
                    <?php
                    settings_fields( 'aorp_settings' );
                    do_settings_sections( 'aorp_settings' );
                    submit_button();
                    ?>
                </form>
            <?php endif; ?>
        </div>
        <?php
    }
}
