<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPGMO_Template_Manager {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
        add_action( 'wp_ajax_wpgmo_save_template', [ $this, 'ajax_save_template' ] );
        add_action( 'wp_ajax_wpgmo_duplicate_template', [ $this, 'ajax_duplicate_template' ] );
        add_action( 'wp_ajax_wpgmo_delete_template', [ $this, 'ajax_delete_template' ] );
        add_action( 'wp_ajax_wpgmo_set_default_template', [ $this, 'ajax_set_default_template' ] );
        add_action( 'wp_ajax_wpgmo_get_template', [ $this, 'ajax_get_template' ] );
    }

    public function admin_menu() {
        add_menu_page(
            __( 'Grid Templates', 'wpgmo' ),
            __( 'Grid Templates', 'wpgmo' ),
            'manage_options',
            'wpgmo_templates',
            [ $this, 'render_page' ],
            'dashicons-grid-view',
            80
        );
        add_submenu_page(
            'wpgmo_templates',
            __( 'Grid Builder', 'wpgmo' ),
            __( 'Neu', 'wpgmo' ),
            'manage_options',
            'wpgmo_builder',
            [ $this, 'builder_page' ]
        );
    }

    public function enqueue( $hook ) {
        if ( in_array( $hook, [ 'toplevel_page_wpgmo_templates', 'wpgmo_page_wpgmo_builder', 'toplevel_page_wpgmo_builder' ], true ) ) {
            wp_enqueue_style( 'wpgmo-grid-builder', plugin_dir_url( __FILE__ ) . '../assets/css/wpgmo-grid-builder.css', [], '1.0' );
            wp_enqueue_script( 'wpgmo-grid-builder', plugin_dir_url( __FILE__ ) . '../assets/js/wpgmo-grid-builder.js', [ 'jquery' ], '1.0', true );
            wp_localize_script( 'wpgmo-grid-builder', 'WPGMO', [
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'wpgmo_save_template' ),
                'saved'   => __( 'Template gespeichert', 'wpgmo' ),
                'error'   => __( 'Fehler beim Speichern', 'wpgmo' ),
            ] );
        }
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $templates = get_option( 'wpgmo_templates', [] );
        $default   = get_option( 'wpgmo_default_template', '' );
        echo '<div class="wrap"><h1>' . esc_html__( 'Grid Templates', 'wpgmo' ) . '</h1>';
        echo '<p><a href="' . esc_url( admin_url( 'admin.php?page=wpgmo_builder' ) ) . '" class="button button-primary">' . esc_html__( 'Neues Template erstellen', 'wpgmo' ) . '</a></p>';
        if ( $templates ) {
            echo '<table class="widefat"><thead><tr><th>' . esc_html__( 'Slug', 'wpgmo' ) . '</th><th>' . esc_html__( 'Label', 'wpgmo' ) . '</th><th>' . esc_html__( 'Aktionen', 'wpgmo' ) . '</th></tr></thead><tbody>';
            foreach ( $templates as $slug => $tpl ) {
                echo '<tr><td>' . esc_html( $slug ) . ( $slug === $default ? ' (Default)' : '' ) . '</td><td>' . esc_html( $tpl['label'] ) . '</td><td>';
                $edit = admin_url( 'admin.php?page=wpgmo_builder&template=' . urlencode( $slug ) );
                echo '<a href="' . esc_url( $edit ) . '">' . esc_html__( 'Bearbeiten', 'wpgmo' ) . '</a> | ';
                echo '<a href="#" class="wpgmo-duplicate" data-slug="' . esc_attr( $slug ) . '">' . esc_html__( 'Duplizieren', 'wpgmo' ) . '</a> | ';
                echo '<a href="#" class="wpgmo-delete" data-slug="' . esc_attr( $slug ) . '">' . esc_html__( 'Löschen', 'wpgmo' ) . '</a> | ';
                if ( $slug !== $default ) {
                    echo '<a href="#" class="wpgmo-set-default" data-slug="' . esc_attr( $slug ) . '">' . esc_html__( 'Als Standard setzen', 'wpgmo' ) . '</a>';
                }
                echo '</td></tr>';
            }
            echo '</tbody></table>';
        }
        wp_nonce_field( 'wpgmo_templates', 'wpgmo_templates_nonce' );
        echo '</div>';
    }

    public function builder_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $slug = isset( $_GET['template'] ) ? sanitize_title( wp_unslash( $_GET['template'] ) ) : '';
        echo '<div class="wrap"><h1>' . esc_html__( 'Grid Builder', 'wpgmo' ) . '</h1>';
        echo '<form id="wpgmo-template-form" method="post" action="">';
        wp_nonce_field( 'wpgmo_save_template', 'wpgmo_save_template_nonce' );
        echo '<table class="form-table"><tr><th><label for="wpgmo_template_slug">' . esc_html__( 'Template-Slug', 'wpgmo' ) . '</label></th><td><input type="text" id="wpgmo_template_slug" value="' . esc_attr( $slug ) . '"' . ( $slug ? ' readonly' : '' ) . ' /></td></tr>';
        echo '<tr><th><label for="wpgmo_template_label">' . esc_html__( 'Template-Label', 'wpgmo' ) . '</label></th><td><input type="text" id="wpgmo_template_label" value="" /></td></tr></table>';
        echo '<div id="wpgmo-grid-editor"></div>';
        echo '<p><button type="button" class="button" id="wpgmo-add-row">' . esc_html__( '+ Row', 'wpgmo' ) . '</button></p>';
        echo '<input type="hidden" id="wpgmo_layout" value="" />';
        echo '<p><input type="submit" class="button button-primary" value="' . esc_attr__( 'Save Template', 'wpgmo' ) . '" /></p>';
        echo '</form></div>';
        ?>
        <script type="text/javascript">
        jQuery(function($){
            WPGMOBuilder.initTemplateEditor(<?php echo wp_json_encode( $slug ); ?>);
        });
        </script>
        <?php
    }

    public function ajax_save_template() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error();
        }
        check_ajax_referer( 'wpgmo_save_template', 'nonce' );
        $slug   = sanitize_title( wp_unslash( $_POST['slug'] ?? '' ) );
        $label  = sanitize_text_field( wp_unslash( $_POST['label'] ?? '' ) );
        $layout = json_decode( wp_unslash( $_POST['layout'] ?? '[]' ), true );
        if ( ! is_array( $layout ) ) {
            $layout = [];
        }
        $templates          = get_option( 'wpgmo_templates', [] );
        $templates[ $slug ] = [
            'slug'   => $slug,
            'label'  => $label,
            'layout' => $layout,
        ];
        update_option( 'wpgmo_templates', $templates );
        wp_send_json_success();
    }

    public function ajax_duplicate_template() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error();
        }
        check_ajax_referer( 'wpgmo_templates', 'nonce' );
        $slug = sanitize_title( wp_unslash( $_POST['slug'] ?? '' ) );
        $templates = get_option( 'wpgmo_templates', [] );
        if ( ! isset( $templates[ $slug ] ) ) {
            wp_send_json_error();
        }
        $base   = $slug . '-copy';
        $new    = $base;
        $i      = 1;
        while ( isset( $templates[ $new ] ) ) {
            $new = $base . '-' . $i;
            $i++;
        }
        $tpl              = $templates[ $slug ];
        $tpl['slug']      = $new;
        $tpl['label']     = $tpl['label'] . ' Copy';
        $templates[ $new ] = $tpl;
        update_option( 'wpgmo_templates', $templates );
        wp_send_json_success();
    }

    public function ajax_delete_template() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error();
        }
        check_ajax_referer( 'wpgmo_templates', 'nonce' );
        $slug = sanitize_title( wp_unslash( $_POST['slug'] ?? '' ) );
        $templates = get_option( 'wpgmo_templates', [] );
        if ( isset( $templates[ $slug ] ) ) {
            unset( $templates[ $slug ] );
            update_option( 'wpgmo_templates', $templates );
        }
        wp_send_json_success();
    }

    public function ajax_set_default_template() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error();
        }
        check_ajax_referer( 'wpgmo_templates', 'nonce' );
        $slug = sanitize_title( wp_unslash( $_POST['slug'] ?? '' ) );
        update_option( 'wpgmo_default_template', $slug );
        wp_send_json_success();
    }

    public function ajax_get_template() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error();
        }
        check_ajax_referer( 'wpgmo_save_template', 'nonce' );
        $slug = sanitize_title( wp_unslash( $_POST['slug'] ?? '' ) );
        $templates = get_option( 'wpgmo_templates', [] );
        if ( isset( $templates[ $slug ] ) ) {
            wp_send_json_success( $templates[ $slug ] );
        }
        wp_send_json_error();
    }
}
