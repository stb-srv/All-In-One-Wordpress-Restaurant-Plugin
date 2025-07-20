<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin UI for creating and editing grid templates.
 *
 * @package AIO_Restaurant_Plugin
 */
class WPGMO_Template_Manager {
    private static $instance = null;
    private $page_hook = '';
    private $overview_hook = '';

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }


/**
 * __construct
 *
 * @return void
 */
    private function __construct() {
        // Run late so grid templates appear at the end of the submenu
        add_action( 'admin_menu', array( $this, 'admin_menu' ), 60 );
        add_action( 'network_admin_menu', array( $this, 'admin_menu' ), 60 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
        add_action( 'wp_ajax_wpgmo_save_template', array( $this, 'save_template' ) );
        add_action( 'wp_ajax_wpgmo_delete_template', array( $this, 'delete_template' ) );
        add_action( 'wp_ajax_wpgmo_set_default_template', array( $this, 'set_default' ) );
        add_action( 'wp_ajax_wpgmo_duplicate_template', array( $this, 'duplicate_template' ) );
    }


/**
 * admin_menu
 *
 * @return void
 */
    public function admin_menu() {
        if ( is_network_admin() ) {
            $this->page_hook = add_menu_page( __('AIO-Grid-Vorlagen','aorp'), __('AIO-Grid-Vorlagen','aorp'), 'manage_network_options', 'wpgmo-templates', array( $this, 'render_page' ) );
            $this->overview_hook = add_submenu_page( 'wpgmo-templates', __('AIO-Grid-Inhalte','aorp'), __('AIO-Grid-Inhalte','aorp'), 'manage_network_options', 'wpgmo-overview', array( $this, 'render_overview_page' ) );
        } else {
            $this->page_hook = add_submenu_page( 'aio-restaurant', __('Grid-Vorlagen','aorp'), __('Grid-Vorlagen','aorp'), 'manage_options', 'wpgmo-templates', array( $this, 'render_page' ) );
            $this->overview_hook = add_submenu_page( 'wpgmo-templates', __('AIO-Grid-Inhalte','aorp'), __('AIO-Grid-Inhalte','aorp'), 'manage_options', 'wpgmo-overview', array( $this, 'render_overview_page' ) );
        }
    }


/**
 * enqueue
 *
 * @return void
 */
    public function enqueue( $hook ) {
        if ( $hook === $this->page_hook ) {
            $tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'templates';
            if ( 'contents' === $tab ) {
                wp_enqueue_style( 'wp-grid-menu-overlay', plugin_dir_url( __FILE__ ) . '../assets/css/wp-grid-menu-overlay.css' );
                wp_enqueue_editor();
            } else {
                wp_enqueue_style( 'wpgmo-gb-css', plugin_dir_url( __FILE__ ) . '../assets/css/wpgmo-grid-builder.css' );
                wp_enqueue_script( 'wpgmo-gb-js', plugin_dir_url( __FILE__ ) . '../assets/js/admin/wpgmo-grid-builder.js', array( 'jquery' ), false, true );
                $network_templates = get_site_option( 'wpgmo_templates_network', array() );
                $templates = is_network_admin()
                    ? $network_templates
                    : array_merge( $network_templates, get_option( 'wpgmo_templates', array() ) );
                $default = is_network_admin() ? get_site_option( 'wpgmo_default_template_network', '' ) : get_option( 'wpgmo_default_template', '' );
                wp_localize_script( 'wpgmo-gb-js', 'WPGMO_GB', array(
                    'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
                    'nonce'        => wp_create_nonce( is_network_admin() ? 'wpgmo_gb_network' : 'wpgmo_gb' ),
                    'templates'    => $templates,
                    'networkSlugs' => array_keys( $network_templates ),
                    'isNetwork'    => is_network_admin() ? 1 : 0,
                    'setDefault'   => __( 'Als Standard setzen', 'aorp' ),
                    'duplicate'    => __( 'Duplizieren', 'aorp' ),
                    'edit'         => __( 'Bearbeiten', 'aorp' ),
                    'del'          => __( 'Löschen', 'aorp' ),
                    'new'          => __( 'Neue Vorlage', 'aorp' ),
                    'save'         => __( 'Vorlage speichern', 'aorp' ),
                    'cancel'       => __( 'Abbrechen', 'aorp' ),
                    'slug'         => __( 'Slug', 'aorp' ),
                    'label'        => __( 'Bezeichnung', 'aorp' ),
                    'actions'      => __( 'Aktionen', 'aorp' ),
                    'description'  => __( 'Beschreibung', 'aorp' ),
                    'shortcode'    => __( 'Shortcode', 'aorp' ),
                    'confirm'      => __( 'Vorlage löschen?', 'aorp' ),
                    'removeRow'    => __( 'Zeile entfernen', 'aorp' ),
                    'default'      => $default,
                ) );
            }
        } elseif ( $hook === $this->overview_hook ) {
            wp_enqueue_style( 'wp-grid-menu-overlay', plugin_dir_url( __FILE__ ) . '../assets/css/wp-grid-menu-overlay.css' );
            wp_enqueue_editor();
        } else {
            return;
        }
    }


/**
 * render_page
 *
 * @return void
 */
    public function render_page() {
        if ( ! current_user_can( is_network_admin() ? 'manage_network_options' : 'manage_options' ) ) {
            return;
        }
        $tab        = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'templates';
        $is_network = is_network_admin();
        $default    = $is_network ? get_site_option( 'wpgmo_default_template_network', '' ) : get_option( 'wpgmo_default_template', '' );
        ?>
        <div class="wrap">
            <h1><?php _e('AIO-Grid Templates','aorp'); ?></h1>
            <h2 class="nav-tab-wrapper">
                <?php
                $url_templates = add_query_arg( array( 'page' => 'wpgmo-templates', 'tab' => 'templates' ), menu_page_url( 'wpgmo-templates', false ) );
                $url_contents  = add_query_arg( array( 'page' => 'wpgmo-templates', 'tab' => 'contents' ), menu_page_url( 'wpgmo-templates', false ) );
                ?>
                <a href="<?php echo esc_url( $url_templates ); ?>" class="nav-tab<?php echo ( 'templates' === $tab ) ? ' nav-tab-active' : ''; ?>"><?php esc_html_e( 'Vorlagen', 'aorp' ); ?></a>
                <a href="<?php echo esc_url( $url_contents ); ?>" class="nav-tab<?php echo ( 'contents' === $tab ) ? ' nav-tab-active' : ''; ?>"><?php esc_html_e( 'Inhalte', 'aorp' ); ?></a>
            </h2>
            <?php if ( 'contents' === $tab ) : ?>
                <?php $this->render_overview_page( true ); ?>
            <?php else : ?>
                <div id="wpgmo-template-manager" data-network="<?php echo $is_network ? 1 : 0; ?>" data-default="<?php echo esc_attr( $default ); ?>"></div>
            <?php endif; ?>
        </div>
        <?php
    }


/**
 * render_overview_page
 *
 * @return void
 */
    public function render_overview_page( $embedded = false ) {
        if ( ! current_user_can( is_network_admin() ? 'manage_network_options' : 'manage_options' ) ) {
            return;
        }
        $templates = is_network_admin()
            ? get_site_option( 'wpgmo_templates_network', array() )
            : array_merge( get_site_option( 'wpgmo_templates_network', array() ), get_option( 'wpgmo_templates', array() ) );
        $contents  = get_option( 'wpgmo_default_content', array() );
        if ( isset( $_POST['wpgmo_save_content'] ) && check_admin_referer( 'wpgmo_save_content' ) ) {
            foreach ( $templates as $slug => $tpl ) {
                if ( isset( $_POST['content'][ $slug ] ) && is_array( $_POST['content'][ $slug ] ) ) {
                    foreach ( $_POST['content'][ $slug ] as $cid => $val ) {
                        $contents[ $slug ][ sanitize_key( $cid ) ] = aorp_wp_kses_post_iframe( $val );
                    }
                }
            }
            update_option( 'wpgmo_default_content', $contents );
            echo '<div class="updated"><p>' . esc_html__( 'Inhalte gespeichert', 'aorp' ) . '</p></div>';
        }
        ?>
        <?php if ( ! $embedded ) : ?>
        <div class="wrap">
            <h1><?php _e('AIO-Grid-Inhalte','aorp'); ?></h1>
        <?php endif; ?>
            <form method="post">
                <?php wp_nonce_field( 'wpgmo_save_content' ); ?>
                <?php foreach ( $templates as $slug => $tpl ) : ?>
                    <h2><?php echo esc_html( $tpl['label'] ); ?> (<?php echo esc_html( $slug ); ?>)</h2>
                    <?php foreach ( $tpl['layout'] as $row ) : ?>
                        <div class="wpgmo-row">
                            <?php foreach ( $row as $cell ) : ?>
                                <?php $cid = $cell['id'];
                                $val = isset( $contents[ $slug ][ $cid ] ) ? $contents[ $slug ][ $cid ] : ''; ?>
                                <div class="wpgmo-cell wpgmo-<?php echo esc_attr( $cell['size'] ); ?>">
                                    <?php wp_editor( $val, 'wpgmo_' . $slug . '_' . $cid, array(
                                        'textarea_name' => 'content[' . $slug . '][' . $cid . ']',
                                        'textarea_rows' => 5,
                                        'teeny'         => true,
                                        'media_buttons' => true,
                                    ) ); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                <p><input type="submit" name="wpgmo_save_content" class="button button-primary" value="<?php esc_attr_e( 'Inhalte speichern', 'aorp' ); ?>" /></p>
            </form>
        <?php if ( ! $embedded ) : ?>
        </div>
        <?php endif; ?>
        <?php
    }


/**
 * sanitize_template
 *
 * @return void
 */
    private function sanitize_template( $data ) {
        $out = array();
        $out['label']  = sanitize_text_field( $data['label'] );
        $out['desc']   = isset( $data['desc'] ) ? sanitize_text_field( $data['desc'] ) : '';
        $out['layout'] = isset( $data['layout'] ) && is_array( $data['layout'] ) ? $data['layout'] : array();
        return $out;
    }


/**
 * save_template
 *
 * @return void
 */
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


/**
 * delete_template
 *
 * @return void
 */
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


/**
 * duplicate_template
 *
 * @return void
 */
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


/**
 * set_default
 *
 * @return void
 */
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
