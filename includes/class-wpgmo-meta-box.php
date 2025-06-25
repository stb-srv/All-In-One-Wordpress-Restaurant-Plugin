<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPGMO_Meta_Box {
    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_box' ) );
        add_action( 'save_post', array( $this, 'save' ) );
    }

    public function add_box() {
        add_meta_box( 'wpgmo_box', __('Grid-Overlay Inhalt','aorp'), array( $this, 'render_box' ), ['post','page'], 'normal', 'high' );
    }

    public function render_box( $post ) {
        $templates_net  = get_site_option( 'wpgmo_templates_network', array() );
        $templates_site = get_option( 'wpgmo_templates', array() );
        $default        = get_option( 'wpgmo_default_template', get_site_option( 'wpgmo_default_template_network', '' ) );
        $selected       = get_post_meta( $post->ID, 'wpgmo_template', true );
        if ( ! $selected ) {
            $selected = $default;
        }
        wp_nonce_field( 'wpgmo_save_meta', 'wpgmo_nonce' );
        echo '<p><label for="wpgmo_template">'.__('Vorlage wählen','aorp').'</label> ';
        echo '<select name="wpgmo_template" id="wpgmo_template">';
        foreach ( $templates_net as $slug => $tpl ) {
            printf( '<option value="%s" %s>%s</option>', esc_attr( $slug ), selected( $selected, $slug, false ), esc_html( $tpl['label'] ) );
        }
        foreach ( $templates_site as $slug => $tpl ) {
            printf( '<option value="%s" %s>%s</option>', esc_attr( $slug ), selected( $selected, $slug, false ), esc_html( $tpl['label'] ) );
        }
        echo '</select></p>';
        $template = isset( $templates_site[ $selected ] ) ? $templates_site[ $selected ] : ( isset( $templates_net[ $selected ] ) ? $templates_net[ $selected ] : null );
        $content  = get_post_meta( $post->ID, 'wpgmo_content_' . $selected, true );
        if ( $template ) {
            foreach ( $template['layout'] as $row ) {
                foreach ( $row as $cell ) {
                    $cid = esc_attr( $cell['id'] );
                    $val = isset( $content[ $cid ] ) ? wp_kses_post( $content[ $cid ] ) : '';
                    echo '<p><label>'.esc_html( $cid ).'</label></p>';
                    wp_editor( $val, 'wpgmo_cell_' . $cid, array(
                        'textarea_name' => 'wpgmo_cells['.$cid.']',
                        'textarea_rows' => 5,
                        'media_buttons' => true,
                        'teeny'         => true,
                    ) );
                }
            }
        }
    }

    public function save( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! isset( $_POST['wpgmo_nonce'] ) || ! wp_verify_nonce( $_POST['wpgmo_nonce'], 'wpgmo_save_meta' ) ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        $template = isset( $_POST['wpgmo_template'] ) ? sanitize_key( $_POST['wpgmo_template'] ) : '';
        update_post_meta( $post_id, 'wpgmo_template', $template );
        $cells = isset( $_POST['wpgmo_cells'] ) && is_array( $_POST['wpgmo_cells'] ) ? wp_unslash( $_POST['wpgmo_cells'] ) : array();
        $clean = array();
        foreach ( $cells as $k => $v ) {
            $clean[ sanitize_key( $k ) ] = wp_kses_post( $v );
        }
        update_post_meta( $post_id, 'wpgmo_content_' . $template, $clean );
    }
}
