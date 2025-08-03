<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function aio_register_general_settings() {
    register_setting( 'aio_settings_group', 'aio_enable_search_filter' );
}
add_action( 'admin_init', 'aio_register_general_settings' );

function aio_settings_menu_page() {
    add_submenu_page(
        'aio-restaurant',
        __( 'Einstellungen', 'aorp' ),
        __( 'Einstellungen', 'aorp' ),
        'manage_options',
        'aio-restaurant-settings',
        'aio_render_settings_page'
    );
}
add_action( 'admin_menu', 'aio_settings_menu_page' );

function aio_render_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Plugin Einstellungen', 'aorp' ); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'aio_settings_group' );
            ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="aio_enable_search_filter"><?php esc_html_e( 'Such- & Filterfunktion im Frontend aktivieren', 'aorp' ); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="aio_enable_search_filter" name="aio_enable_search_filter" value="1" <?php checked( get_option( 'aio_enable_search_filter' ), '1' ); ?> />
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
