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
        add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
        add_action( 'save_post', [ $this, 'save' ] );
    }

    public function add_meta_boxes() {
        add_meta_box( 'wpgmo_meta', __( 'Grid Overlay Content', 'wpgmo' ), [ $this, 'render' ], [ 'page', 'post' ] );
    }

    private function get_templates() {
        return get_option( 'wpgmo_templates', [] );
    }

    public function render( $post ) {
        wp_nonce_field( 'wpgmo_meta', 'wpgmo_meta_nonce' );
        $templates = $this->get_templates();
        $selected  = get_post_meta( $post->ID, 'wpgmo_template_slug', true );
        if ( ! $selected ) {
            $selected = get_option( 'wpgmo_default_template', '' );
        }
        echo '<p><label for="wpgmo_template_slug">' . esc_html__( 'Template wählen', 'wpgmo' ) . '</label> ';
        echo '<select name="wpgmo_template_slug" id="wpgmo_template_slug">';
        foreach ( $templates as $slug => $tpl ) {
            echo '<option value="' . esc_attr( $slug ) . '"' . selected( $selected, $slug, false ) . '>' . esc_html( $tpl['label'] ) . '</option>';
        }
        echo '</select></p>';
        $layout = isset( $templates[ $selected ] ) ? $templates[ $selected ]['layout'] : [];
        $content = get_post_meta( $post->ID, 'wpgmo_content_' . $selected, true );
        if ( ! is_array( $content ) ) {
            $content = [];
        }
        echo '<div class="wpgmo-layout-preview">';
        foreach ( $layout as $row ) {
            echo '<div class="wpgmo-row">';
            foreach ( $row as $cell ) {
                $id   = $cell['id'];
                $size = $cell['size'];
                $val  = $content[ $id ] ?? '';
                echo '<div class="wpgmo-cell-preview wpgmo-cell-' . esc_attr( $size ) . '">';
                echo '<p>' . esc_html( $id ) . '</p>';
                wp_editor( $val, 'wpgmo_cell_' . esc_attr( $id ), [ 'textarea_name' => 'wpgmo_content[' . esc_attr( $id ) . ']' ] );
                echo '</div>';
            }
            echo '</div>';
        }
        echo '</div>';
    }

    public function save( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! isset( $_POST['wpgmo_meta_nonce'] ) || ! wp_verify_nonce( $_POST['wpgmo_meta_nonce'], 'wpgmo_meta' ) ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        $slug = isset( $_POST['wpgmo_template_slug'] ) ? sanitize_title( wp_unslash( $_POST['wpgmo_template_slug'] ) ) : '';
        update_post_meta( $post_id, 'wpgmo_template_slug', $slug );
        $content = [];
        if ( isset( $_POST['wpgmo_content'] ) && is_array( $_POST['wpgmo_content'] ) ) {
            foreach ( $_POST['wpgmo_content'] as $id => $val ) {
                $content[ sanitize_key( $id ) ] = wp_kses_post( $val );
            }
        }
        update_post_meta( $post_id, 'wpgmo_content_' . $slug, $content );
    }
}
