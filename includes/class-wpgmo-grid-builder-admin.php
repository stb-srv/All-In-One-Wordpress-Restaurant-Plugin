<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPGMO_Grid_Builder_Admin {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu', [ $this, 'maybe_add_page' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
    }

    public static function render_page() {
        self::instance()->builder_page();
    }

    public function maybe_add_page() {
        // page already added via Template_Manager
    }

    public function enqueue() {
        $screen = get_current_screen();
        if ( $screen && 'toplevel_page_wpgmo_builder' === $screen->id ) {
            wp_enqueue_script( 'wpgmo-grid-builder', plugin_dir_url( __FILE__ ) . '../assets/js/wpgmo-grid-builder.js', [ 'jquery' ], '1.0', true );
            wp_localize_script( 'wpgmo-grid-builder', 'WPGMO', [
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'wpgmo_save_template' ),
                'saved'   => __( 'Template gespeichert', 'wpgmo' ),
                'error'   => __( 'Fehler beim Speichern', 'wpgmo' ),
            ] );
        }
    }

    public function builder_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $slug      = isset( $_GET['template'] ) ? sanitize_title( wp_unslash( $_GET['template'] ) ) : '';
        $templates = get_option( 'wpgmo_templates', [] );
        $label     = '';
        $layout    = [];
        if ( $slug && isset( $templates[ $slug ] ) ) {
            $label  = $templates[ $slug ]['label'];
            $layout = $templates[ $slug ]['layout'];
        }
        echo '<div class="wrap"><h1>' . esc_html__( 'Grid Builder', 'wpgmo' ) . '</h1>';
        echo '<form id="wpgmo-template-form" method="post" action="">';
        wp_nonce_field( 'wpgmo_save_template', 'wpgmo_save_template_nonce' );
        echo '<table class="form-table"><tr><th><label for="wpgmo_template_slug">' . esc_html__( 'Template-Slug', 'wpgmo' ) . '</label></th><td><input type="text" id="wpgmo_template_slug" value="' . esc_attr( $slug ) . '"' . ( $slug ? ' readonly' : '' ) . ' /></td></tr>';
        echo '<tr><th><label for="wpgmo_template_label">' . esc_html__( 'Template-Label', 'wpgmo' ) . '</label></th><td><input type="text" id="wpgmo_template_label" value="' . esc_attr( $label ) . '" /></td></tr></table>';
        echo '<div id="wpgmo-grid-editor"></div>';
        echo '<p><button type="button" class="button" id="wpgmo-add-row">' . esc_html__( 'Zeile hinzufügen', 'wpgmo' ) . '</button></p>';
        echo '<input type="hidden" id="wpgmo_layout" value="" />';
        echo '<p><input type="submit" class="button button-primary" value="' . esc_attr__( 'Speichern', 'wpgmo' ) . '" /></p>';
        echo '</form></div>';
        ?>
        <script type="text/javascript">
        jQuery(function($){
            WPGMOBuilder.initTemplateEditor(<?php echo wp_json_encode( $slug ); ?>, <?php echo wp_json_encode( $label ); ?>, <?php echo wp_json_encode( $layout ); ?>);
        });
        </script>
        <?php
    }
}
