<?php
/*
Plugin Name: All-In-One Restaurant Plugin
Description: Speisekarte management with Dark Mode toggle.
Version: 0.1.0
Author: stb-srv
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class AIO_Restaurant_Plugin {

    public function __construct() {
        add_action( 'init', array( $this, 'register_post_type' ) );
        add_action( 'init', array( $this, 'register_taxonomy' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_meta_boxes' ) );
        add_shortcode( 'speisekarte', array( $this, 'speisekarte_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    public function register_post_type() {
        $labels = array(
            'name' => __( 'Speisen', 'aorp' ),
            'singular_name' => __( 'Speise', 'aorp' )
        );
        register_post_type( 'aorp_menu_item', array(
            'labels' => $labels,
            'public' => true,
            'has_archive' => false,
            'supports' => array( 'title', 'editor', 'thumbnail' )
        ) );
    }

    public function register_taxonomy() {
        $labels = array(
            'name' => __( 'Kategorien', 'aorp' ),
            'singular_name' => __( 'Kategorie', 'aorp' )
        );
        register_taxonomy( 'aorp_menu_category', 'aorp_menu_item', array(
            'labels' => $labels,
            'hierarchical' => true,
            'show_admin_column' => true,
        ) );
    }

    public function add_meta_boxes() {
        add_meta_box( 'aorp_meta', __( 'Speise Details', 'aorp' ), array( $this, 'render_meta_box' ), 'aorp_menu_item', 'normal', 'default' );
    }

    public function render_meta_box( $post ) {
        wp_nonce_field( basename( __FILE__ ), 'aorp_nonce' );
        $price = get_post_meta( $post->ID, '_aorp_price', true );
        $number = get_post_meta( $post->ID, '_aorp_number', true );
        ?>
        <p>
            <label for="aorp_number"><?php _e( 'Speisennummer', 'aorp' ); ?></label>
            <input type="text" name="aorp_number" id="aorp_number" value="<?php echo esc_attr( $number ); ?>" />
        </p>
        <p>
            <label for="aorp_price"><?php _e( 'Preis', 'aorp' ); ?></label>
            <input type="text" name="aorp_price" id="aorp_price" value="<?php echo esc_attr( $price ); ?>" />
        </p>
        <?php
    }

    public function save_meta_boxes( $post_id ) {
        if ( ! isset( $_POST['aorp_nonce'] ) || ! wp_verify_nonce( $_POST['aorp_nonce'], basename( __FILE__ ) ) ) {
            return $post_id;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }
        if ( isset( $_POST['aorp_price'] ) ) {
            update_post_meta( $post_id, '_aorp_price', sanitize_text_field( $_POST['aorp_price'] ) );
        }
        if ( isset( $_POST['aorp_number'] ) ) {
            update_post_meta( $post_id, '_aorp_number', sanitize_text_field( $_POST['aorp_number'] ) );
        }
    }

    public function speisekarte_shortcode( $atts ) {
        $atts = shortcode_atts( array( 'columns' => 1 ), $atts, 'speisekarte' );
        ob_start();
        $terms = get_terms( array( 'taxonomy' => 'aorp_menu_category', 'hide_empty' => false ) );
        echo '<div class="aorp-menu">';
        foreach ( $terms as $term ) {
            $query = new WP_Query( array( 'post_type' => 'aorp_menu_item', 'tax_query' => array( array( 'taxonomy' => 'aorp_menu_category', 'field' => 'term_id', 'terms' => $term->term_id ) ) ) );
            if ( $query->have_posts() ) {
                echo '<h3 class="aorp-category">' . esc_html( $term->name ) . '</h3>';
                echo '<div class="aorp-items">';
                while ( $query->have_posts() ) {
                    $query->the_post();
                    $price = get_post_meta( get_the_ID(), '_aorp_price', true );
                    echo '<div class="aorp-item">';
                    echo '<span class="aorp-title">' . get_the_title() . '</span> - ';
                    echo '<span class="aorp-price">' . esc_html( $price ) . '</span>';
                    echo '</div>';
                }
                echo '</div>';
                wp_reset_postdata();
            }
        }
        echo '</div>';
        return ob_get_clean();
    }

    public function enqueue_assets() {
        if ( ! is_admin() ) {
            wp_enqueue_style( 'aorp-style', plugin_dir_url( __FILE__ ) . 'assets/style.css' );
            wp_enqueue_script( 'aorp-script', plugin_dir_url( __FILE__ ) . 'assets/script.js', array('jquery'), false, true );
        }
    }
}

new AIO_Restaurant_Plugin();
