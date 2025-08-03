<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function aio_import_export_menu_page() {
    add_submenu_page(
        'aio-restaurant',
        __( 'Import/Export', 'aorp' ),
        __( 'Import/Export', 'aorp' ),
        'manage_options',
        'aio-import-export-settings',
        'aio_render_import_export_page'
    );
}
add_action( 'admin_menu', 'aio_import_export_menu_page' );

function aio_render_import_export_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Import/Export', 'aorp' ); ?></h1>
        <h2><?php esc_html_e( 'Export', 'aorp' ); ?></h2>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'aio_export_settings' ); ?>
            <input type="hidden" name="action" value="aio_export_settings" />
            <?php submit_button( __( 'Einstellungen exportieren', 'aorp' ) ); ?>
        </form>
        <hr />
        <h2><?php esc_html_e( 'Import', 'aorp' ); ?></h2>
        <form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'aio_import_settings' ); ?>
            <input type="hidden" name="action" value="aio_import_settings" />
            <input type="file" name="aio_settings_file" accept=".json" required />
            <?php submit_button( __( 'Einstellungen importieren', 'aorp' ) ); ?>
        </form>
    </div>
    <?php
}

function aio_handle_export_settings() {
    if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'aio_export_settings' ) ) {
        wp_die( esc_html__( 'Nicht erlaubt', 'aorp' ) );
    }

    $data = array(
        'aio_enable_search_filter' => get_option( 'aio_enable_search_filter' ),
        'aorp_options'             => get_option( 'aorp_options' ),
    );

    $json = wp_json_encode( $data );
    header( 'Content-Type: application/json' );
    header( 'Content-Disposition: attachment; filename="aio-settings.json"' );
    echo $json;
    exit;
}
add_action( 'admin_post_aio_export_settings', 'aio_handle_export_settings' );

function aio_handle_import_settings() {
    if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'aio_import_settings' ) ) {
        wp_die( esc_html__( 'Nicht erlaubt', 'aorp' ) );
    }

    if ( ! empty( $_FILES['aio_settings_file']['tmp_name'] ) ) {
        $json = file_get_contents( $_FILES['aio_settings_file']['tmp_name'] );
        $data = json_decode( $json, true );
        if ( is_array( $data ) ) {
            if ( isset( $data['aio_enable_search_filter'] ) ) {
                update_option( 'aio_enable_search_filter', $data['aio_enable_search_filter'] );
            }
            if ( isset( $data['aorp_options'] ) ) {
                update_option( 'aorp_options', $data['aorp_options'] );
            }
        }
    }

    wp_redirect( admin_url( 'admin.php?page=aio-import-export-settings&import=1' ) );
    exit;
}
add_action( 'admin_post_aio_import_settings', 'aio_handle_import_settings' );
