<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPGMO_Template_Manager {
    private static $instance = null;
    private $page_hook = '';

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'network_admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
        add_action( 'wp_ajax_wpgmo_save_template', array( $this, 'save_template' ) );
        add_action( 'wp_ajax_wpgmo_delete_template', array( $this, 'delete_template' ) );
        add_action( 'wp_ajax_wpgmo_set_default_template', array( $this, 'set_default' ) );
        add_action( 'wp_ajax_wpgmo_duplicate_template', array( $this, 'duplicate_template' ) );
    }

    public function admin_menu() {
        if ( is_network_admin() ) {
            $this->page_hook = add_menu_page( __('Grid Templates','aorp'), __('Grid Templates','aorp'), 'manage_network_options', 'wpgmo-templates', array( $this, 'render_page' ) );
        } else {
            $this->page_hook = add_menu_page( __('Grid Templates','aorp'), __('Grid Templates','aorp'), 'manage_options', 'wpgmo-templates', array( $this, 'render_page' ) );
        }
    }

    public function enqueue( $hook ) {
        if ( $hook !== $this->page_hook ) {
            return;
        }
        wp_enqueue_style( 'wpgmo-gb-css', plugin_dir_url( __FILE__ ) . '../assets/css/wpgmo-grid-builder.css' );
        wp_enqueue_script( 'wpgmo-gb-js', plugin_dir_url( __FILE__ ) . '../assets/js/wpgmo-grid-builder.js', array( 'jquery' ), false, true );
        $network_templates = get_site_option( 'wpgmo_templates_network', array() );
        $templates = is_network_admin()
            ? $network_templates
            : array_merge( $network_templates, get_option( 'wpgmo_templates', array() ) );
        wp_localize_script( 'wpgmo-gb-js', 'WPGMO_GB', array(
            'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
            'nonce'          => wp_create_nonce( is_network_admin() ? 'wpgmo_gb_network' : 'wpgmo_gb' ),
            'templates'      => $templates,
            'networkSlugs'   => array_keys( $network_templates ),
            'setDefault'     => __( 'Set default', 'aorp' ),
            'duplicate'      => __( 'Duplicate', 'aorp' ),
            'isNetwork'      => is_network_admin() ? 1 : 0,
        ) );
    }

    public function render_page() {
        if ( ! current_user_can( is_network_admin() ? 'manage_network_options' : 'manage_options' ) ) {
            return;
        }
        $is_network = is_network_admin();
        $default    = $is_network ? get_site_option( 'wpgmo_default_template_network', '' ) : get_option( 'wpgmo_default_template', '' );
        ?>
        <div class="wrap">
            <h1><?php _e('Grid Templates','aorp'); ?></h1>
            <div id="wpgmo-template-manager" data-network="<?php echo $is_network ? 1 : 0; ?>" data-default="<?php echo esc_attr( $default ); ?>"></div>
        </div>
        <?php
    }

    private function sanitize_template( $data ) {
        $out = array();
        $out['label']  = sanitize_text_field( $data['label'] );
        $out['layout'] = isset( $data['layout'] ) && is_array( $data['layout'] ) ? $data['layout'] : array();
        return $out;
    }

    public function save_template() {
        check_ajax_referer( is_network_admin() ? 'wpgmo_gb_network' : 'wpgmo_gb', 'nonce' );
        if ( ! current_user_can( is_network_admin() ? 'manage_network_options' : 'manage_options' ) ) {
            wp_send_json_error();
        }
        $slug     = sanitize_key( $_POST['slug'] );
        $template = $this->sanitize_template( $_POST['template'] );
        if ( is_network_admin() ) {
            $templates = get_site_option( 'wpgmo_templates_network', array() );
            $templates[ $slug ] = $template;
            update_site_option( 'wpgmo_templates_network', $templates );
        } else {
            $templates = get_option( 'wpgmo_templates', array() );
            $templates[ $slug ] = $template;
            update_option( 'wpgmo_templates', $templates );
        }
        wp_send_json_success();
    }

    public function delete_template() {
        check_ajax_referer( is_network_admin() ? 'wpgmo_gb_network' : 'wpgmo_gb', 'nonce' );
        if ( ! current_user_can( is_network_admin() ? 'manage_network_options' : 'manage_options' ) ) {
            wp_send_json_error();
        }
        $slug = sanitize_key( $_POST['slug'] );
        if ( is_network_admin() ) {
            $templates = get_site_option( 'wpgmo_templates_network', array() );
            unset( $templates[ $slug ] );
            update_site_option( 'wpgmo_templates_network', $templates );
        } else {
            $templates = get_option( 'wpgmo_templates', array() );
            unset( $templates[ $slug ] );
            update_option( 'wpgmo_templates', $templates );
        }
        wp_send_json_success();
    }

    public function duplicate_template() {
        check_ajax_referer( is_network_admin() ? 'wpgmo_gb_network' : 'wpgmo_gb', 'nonce' );
        if ( ! current_user_can( is_network_admin() ? 'manage_network_options' : 'manage_options' ) ) {
            wp_send_json_error();
        }
        $slug     = sanitize_key( $_POST['slug'] );
        $new_slug = sanitize_key( $_POST['new_slug'] );
        if ( is_network_admin() ) {
            $templates = get_site_option( 'wpgmo_templates_network', array() );
        } else {
            $templates = get_option( 'wpgmo_templates', array() );
        }
        if ( ! isset( $templates[ $slug ] ) ) {
            wp_send_json_error();
        }
        $templates[ $new_slug ] = $templates[ $slug ];
        if ( is_network_admin() ) {
            update_site_option( 'wpgmo_templates_network', $templates );
        } else {
            update_option( 'wpgmo_templates', $templates );
        }
        wp_send_json_success();
    }

    public function set_default() {
        check_ajax_referer( is_network_admin() ? 'wpgmo_gb_network' : 'wpgmo_gb', 'nonce' );
        if ( ! current_user_can( is_network_admin() ? 'manage_network_options' : 'manage_options' ) ) {
            wp_send_json_error();
        }
        $slug = sanitize_key( $_POST['slug'] );
        if ( is_network_admin() ) {
            update_site_option( 'wpgmo_default_template_network', $slug );
        } else {
            update_option( 'wpgmo_default_template', $slug );
        }
        wp_send_json_success();
    }
}
