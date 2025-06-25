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
        add_action( 'admin_post_wpgmo_save_shortcode', [ $this, 'save_shortcode' ] );
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
        add_submenu_page(
            'wpgmo_settings',
            __( 'Shortcodes', 'wpgmo' ),
            __( 'Shortcodes', 'wpgmo' ),
            'manage_options',
            'wpgmo_shortcodes',
            [ $this, 'shortcodes_page' ]
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

    public function shortcodes_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $shortcodes = get_option( 'wpgmo_custom_shortcodes', [] );

        if ( isset( $_GET['delete'] ) ) {
            $id = intval( $_GET['delete'] );
            check_admin_referer( 'wpgmo_delete_shortcode_' . $id );
            foreach ( $shortcodes as $k => $sc ) {
                if ( $sc['id'] == $id ) {
                    unset( $shortcodes[ $k ] );
                    break;
                }
            }
            update_option( 'wpgmo_custom_shortcodes', array_values( $shortcodes ) );
            wp_redirect( admin_url( 'admin.php?page=wpgmo_shortcodes' ) );
            exit;
        }

        $edit_id = isset( $_GET['edit'] ) ? intval( $_GET['edit'] ) : 0;
        $current = null;
        foreach ( $shortcodes as $sc ) {
            if ( $sc['id'] == $edit_id ) {
                $current = $sc;
                break;
            }
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Shortcodes', 'wpgmo' ); ?></h1>
            <?php if ( $shortcodes ) : ?>
            <table class="widefat">
                <thead><tr><th><?php esc_html_e( 'Name', 'wpgmo' ); ?></th><th><?php esc_html_e( 'Shortcode', 'wpgmo' ); ?></th><th><?php esc_html_e( 'Aktionen', 'wpgmo' ); ?></th></tr></thead>
                <tbody>
                <?php foreach ( $shortcodes as $sc ) : ?>
                    <tr>
                        <td><?php echo esc_html( $sc['name'] ); ?></td>
                        <td>[wp_grid_menu_overlay id="<?php echo esc_attr( $sc['id'] ); ?>"]</td>
                        <td>
                            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=wpgmo_shortcodes&edit=' . $sc['id'] ), 'wpgmo_edit_shortcode_' . $sc['id'] ) ); ?>"><?php esc_html_e( 'Bearbeiten', 'wpgmo' ); ?></a> |
                            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=wpgmo_shortcodes&delete=' . $sc['id'] ), 'wpgmo_delete_shortcode_' . $sc['id'] ) ); ?>" onclick="return confirm('Löschen?');"><?php esc_html_e( 'Löschen', 'wpgmo' ); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>

            <h2><?php echo $current ? esc_html__( 'Shortcode bearbeiten', 'wpgmo' ) : esc_html__( 'Neuen Shortcode anlegen', 'wpgmo' ); ?></h2>
            <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
                <input type="hidden" name="action" value="wpgmo_save_shortcode" />
                <?php wp_nonce_field( 'wpgmo_save_shortcode' ); ?>
                <?php if ( $current ) : ?>
                    <input type="hidden" name="id" value="<?php echo esc_attr( $current['id'] ); ?>" />
                <?php endif; ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="wpgmo_sc_name">Name</label></th>
                        <td><input type="text" id="wpgmo_sc_name" name="name" value="<?php echo esc_attr( $current['name'] ?? '' ); ?>" required /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wpgmo_sc_welcome">Willkommens-Titel</label></th>
                        <td><input type="text" id="wpgmo_sc_welcome" name="welcome_title" value="<?php echo esc_attr( $current['welcome_title'] ?? '' ); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wpgmo_sc_hours">Öffnungszeiten</label></th>
                        <td><input type="text" id="wpgmo_sc_hours" name="opening_hours" value="<?php echo esc_attr( $current['opening_hours'] ?? '' ); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wpgmo_sc_about">Über uns Text</label></th>
                        <td><textarea id="wpgmo_sc_about" name="about_text" rows="3" class="large-text"><?php echo esc_textarea( $current['about_text'] ?? '' ); ?></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wpgmo_sc_address">Kontakt-Adresse</label></th>
                        <td><textarea id="wpgmo_sc_address" name="contact_address" rows="2" class="large-text"><?php echo esc_textarea( $current['contact_address'] ?? '' ); ?></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wpgmo_sc_phone">Telefon</label></th>
                        <td><input type="text" id="wpgmo_sc_phone" name="contact_phone" value="<?php echo esc_attr( $current['contact_phone'] ?? '' ); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wpgmo_sc_email">E-Mail</label></th>
                        <td><input type="email" id="wpgmo_sc_email" name="contact_email" value="<?php echo esc_attr( $current['contact_email'] ?? '' ); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wpgmo_sc_form">Formular Shortcode</label></th>
                        <td><input type="text" id="wpgmo_sc_form" name="form_shortcode" value="<?php echo esc_attr( $current['form_shortcode'] ?? '' ); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wpgmo_sc_map">Karte einbetten</label></th>
                        <td><textarea id="wpgmo_sc_map" name="map_embed" rows="3" class="large-text"><?php echo esc_textarea( $current['map_embed'] ?? '' ); ?></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Grid Layout', 'wpgmo' ); ?></th>
                        <td>
                            <table class="widefat" id="wpgmo-layout-table">
                                <thead>
                                <tr><th><?php esc_html_e( 'Element', 'wpgmo' ); ?></th><th><?php esc_html_e( 'Größe', 'wpgmo' ); ?></th><th></th></tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <select id="wpgmo_types_options" style="display:none">
                                <option value="welcome"><?php esc_html_e( 'Willkommen', 'wpgmo' ); ?></option>
                                <option value="openings"><?php esc_html_e( 'Öffnungszeiten', 'wpgmo' ); ?></option>
                                <option value="about"><?php esc_html_e( 'Über uns', 'wpgmo' ); ?></option>
                                <option value="contact"><?php esc_html_e( 'Kontakt', 'wpgmo' ); ?></option>
                                <option value="form"><?php esc_html_e( 'Formular', 'wpgmo' ); ?></option>
                                <option value="map"><?php esc_html_e( 'Karte', 'wpgmo' ); ?></option>
                            </select>
                            <input type="hidden" id="wpgmo_layout" name="grid_layout" value="<?php echo esc_attr( $current['grid_layout'] ?? '' ); ?>" />
                            <p><button type="button" class="button" id="wpgmo_add_row"><?php esc_html_e( 'Element hinzufügen', 'wpgmo' ); ?></button></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button( $current ? esc_html__( 'Aktualisieren', 'wpgmo' ) : esc_html__( 'Anlegen', 'wpgmo' ) ); ?>
            </form>
        </div>
        <?php
    }

    public function save_shortcode() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nicht erlaubt' );
        }
        check_admin_referer( 'wpgmo_save_shortcode' );
        $shortcodes = get_option( 'wpgmo_custom_shortcodes', [] );
        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        $entry = [
            'id'             => $id ? $id : time(),
            'name'           => sanitize_text_field( $_POST['name'] ),
            'welcome_title'  => sanitize_text_field( $_POST['welcome_title'] ?? '' ),
            'opening_hours'  => sanitize_text_field( $_POST['opening_hours'] ?? '' ),
            'about_text'     => sanitize_textarea_field( $_POST['about_text'] ?? '' ),
            'contact_address'=> sanitize_textarea_field( $_POST['contact_address'] ?? '' ),
            'contact_phone'  => sanitize_text_field( $_POST['contact_phone'] ?? '' ),
            'contact_email'  => sanitize_email( $_POST['contact_email'] ?? '' ),
            'form_shortcode' => wp_kses_post( $_POST['form_shortcode'] ?? '' ),
            'map_embed'      => wp_kses_post( $_POST['map_embed'] ?? '' ),
            'grid_layout'    => '',
        ];
        if ( isset( $_POST['grid_layout'] ) ) {
            $raw    = wp_unslash( $_POST['grid_layout'] );
            $layout = json_decode( $raw, true );
            $allowed = [ 'welcome', 'openings', 'about', 'contact', 'form', 'map' ];
            $clean  = [];
            if ( is_array( $layout ) ) {
                foreach ( $layout as $cell ) {
                    if ( empty( $cell['type'] ) || ! in_array( $cell['type'], $allowed, true ) ) {
                        continue;
                    }
                    $clean[] = [
                        'type' => $cell['type'],
                        'size' => ( isset( $cell['size'] ) && 'large' === $cell['size'] ) ? 'large' : 'small',
                    ];
                }
            }
            $entry['grid_layout'] = wp_json_encode( $clean );
        }
        if ( $id ) {
            foreach ( $shortcodes as &$sc ) {
                if ( $sc['id'] == $id ) {
                    $sc = $entry;
                    break;
                }
            }
        } else {
            $shortcodes[] = $entry;
        }
        update_option( 'wpgmo_custom_shortcodes', array_values( $shortcodes ) );
        wp_redirect( admin_url( 'admin.php?page=wpgmo_shortcodes' ) );
        exit;
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
            'grid_layout'     => '',
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
        $output['grid_layout']      = wp_kses_post( $input['grid_layout'] ?? $defaults['grid_layout'] );

        return $output;
    }
}
