<?php
/*
Plugin Name: All-In-One WordPress Restaurant Plugin
Description: Umfangreiches Speisekarten-Plugin mit Dark‑Mode und Suchfunktion.
Version: 1.0.0
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
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action( 'wp_ajax_aorp_toggle_dark', array( $this, 'ajax_toggle_dark' ) );
        add_action( 'wp_ajax_nopriv_aorp_toggle_dark', array( $this, 'ajax_toggle_dark' ) );
        add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );
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
        // Term Meta for Styling
        add_action( 'aorp_menu_category_add_form_fields', array( $this, 'add_category_fields' ) );
        add_action( 'aorp_menu_category_edit_form_fields', array( $this, 'edit_category_fields' ), 10, 2 );
        add_action( 'created_aorp_menu_category', array( $this, 'save_category_fields' ) );
        add_action( 'edited_aorp_menu_category', array( $this, 'save_category_fields' ) );
    }

    public function add_category_fields() {
        ?>
        <div class="form-field">
            <label for="aorp_bg">Hintergrundfarbe</label>
            <input type="text" name="aorp_bg" id="aorp_bg" value="" class="aorp-color" />
        </div>
        <div class="form-field">
            <label for="aorp_color">Schriftfarbe</label>
            <input type="text" name="aorp_color" id="aorp_color" value="" class="aorp-color" />
        </div>
        <div class="form-field">
            <label for="aorp_font_size">Schriftgröße (z.B. 16px)</label>
            <input type="text" name="aorp_font_size" id="aorp_font_size" value="" />
        </div>
        <div class="form-field">
            <label for="aorp_width">Kachelbreite</label>
            <input type="text" name="aorp_width" id="aorp_width" value="" />
        </div>
        <div class="form-field">
            <label for="aorp_height">Kachelhöhe</label>
            <input type="text" name="aorp_height" id="aorp_height" value="" />
        </div>
        <?php
    }

    public function edit_category_fields( $term, $taxonomy ) {
        $bg = get_term_meta( $term->term_id, 'aorp_bg', true );
        $color = get_term_meta( $term->term_id, 'aorp_color', true );
        $size = get_term_meta( $term->term_id, 'aorp_font_size', true );
        $width = get_term_meta( $term->term_id, 'aorp_width', true );
        $height = get_term_meta( $term->term_id, 'aorp_height', true );
        ?>
        <tr class="form-field">
            <th scope="row"><label for="aorp_bg">Hintergrundfarbe</label></th>
            <td><input type="text" name="aorp_bg" id="aorp_bg" value="<?php echo esc_attr( $bg ); ?>" class="aorp-color" /></td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label for="aorp_color">Schriftfarbe</label></th>
            <td><input type="text" name="aorp_color" id="aorp_color" value="<?php echo esc_attr( $color ); ?>" class="aorp-color" /></td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label for="aorp_font_size">Schriftgröße (z.B. 16px)</label></th>
            <td><input type="text" name="aorp_font_size" id="aorp_font_size" value="<?php echo esc_attr( $size ); ?>" /></td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label for="aorp_width">Kachelbreite</label></th>
            <td><input type="text" name="aorp_width" id="aorp_width" value="<?php echo esc_attr( $width ); ?>" /></td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label for="aorp_height">Kachelhöhe</label></th>
            <td><input type="text" name="aorp_height" id="aorp_height" value="<?php echo esc_attr( $height ); ?>" /></td>
        </tr>
        <?php
    }

    public function save_category_fields( $term_id ) {
        $fields = array( 'aorp_bg', 'aorp_color', 'aorp_font_size', 'aorp_width', 'aorp_height' );
        foreach ( $fields as $field ) {
            if ( isset( $_POST[ $field ] ) ) {
                update_term_meta( $term_id, $field, sanitize_text_field( $_POST[ $field ] ) );
            }
        }
    }

    public function add_meta_boxes() {
        add_meta_box( 'aorp_meta', __( 'Speise Details', 'aorp' ), array( $this, 'render_meta_box' ), 'aorp_menu_item', 'normal', 'default' );
    }

    public function render_meta_box( $post ) {
        wp_nonce_field( basename( __FILE__ ), 'aorp_nonce' );
        $price = get_post_meta( $post->ID, '_aorp_price', true );
        $number = get_post_meta( $post->ID, '_aorp_number', true );
        $ingredients = get_post_meta( $post->ID, '_aorp_ingredients', true );
        ?>
        <p>
            <label for="aorp_number"><?php _e( 'Speisennummer', 'aorp' ); ?></label>
            <input type="text" name="aorp_number" id="aorp_number" value="<?php echo esc_attr( $number ); ?>" />
        </p>
        <p>
            <label for="aorp_price"><?php _e( 'Preis', 'aorp' ); ?></label>
            <input type="text" name="aorp_price" id="aorp_price" value="<?php echo esc_attr( $price ); ?>" />
        </p>
        <p>
            <label for="aorp_ingredients"><?php _e( 'Inhaltsstoffe', 'aorp' ); ?></label>
            <textarea name="aorp_ingredients" id="aorp_ingredients" style="width:100%;" rows="3"><?php echo esc_textarea( $ingredients ); ?></textarea>
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
        if ( isset( $_POST['aorp_ingredients'] ) ) {
            update_post_meta( $post_id, '_aorp_ingredients', sanitize_textarea_field( $_POST['aorp_ingredients'] ) );
        }
    }

    public function speisekarte_shortcode( $atts ) {
        $atts = shortcode_atts( array( 'columns' => 1, 'kategorien' => '' ), $atts, 'speisekarte' );
        $columns = max( 1, min( 3, intval( $atts['columns'] ) ) );

        ob_start();
        echo '<p class="aorp-note">🔽 Klicke auf eine Kategorie, um die Speisen einzublenden.</p>';
        echo '<input type="text" id="aorp-search" placeholder="Suche nach Speisen …" />';

        echo '<div class="aorp-menu columns-' . $columns . '">';

        $args = array( 'taxonomy' => 'aorp_menu_category', 'hide_empty' => false );
        if ( ! empty( $atts['kategorien'] ) ) {
            $args['slug'] = array_map( 'trim', explode( ',', $atts['kategorien'] ) );
        }
        $terms = get_terms( $args );

        if ( empty( $terms ) ) {
            $query = new WP_Query( array( 'post_type' => 'aorp_menu_item', 'posts_per_page' => -1 ) );
            if ( $query->have_posts() ) {
                echo '<h3 class="aorp-category">Alle Speisen</h3>';
                echo '<div class="aorp-items">';
                while ( $query->have_posts() ) {
                    $query->the_post();
                    $this->render_menu_item();
                }
                echo '</div>';
                wp_reset_postdata();
            }
        } else {
            foreach ( $terms as $term ) {
                $query = new WP_Query( array(
                    'post_type' => 'aorp_menu_item',
                    'tax_query' => array( array( 'taxonomy' => 'aorp_menu_category', 'field' => 'term_id', 'terms' => $term->term_id ) )
                ) );
                if ( $query->have_posts() ) {
                    $bg = get_term_meta( $term->term_id, 'aorp_bg', true );
                    $color = get_term_meta( $term->term_id, 'aorp_color', true );
                    $size = get_term_meta( $term->term_id, 'aorp_font_size', true );
                    $width = get_term_meta( $term->term_id, 'aorp_width', true );
                    $height = get_term_meta( $term->term_id, 'aorp_height', true );
                    $style = '';
                    if ( $bg ) $style .= 'background:' . esc_attr( $bg ) . ';';
                    if ( $color ) $style .= 'color:' . esc_attr( $color ) . ';';
                    if ( $size ) $style .= 'font-size:' . esc_attr( $size ) . ';';
                    if ( $width ) $style .= 'width:' . esc_attr( $width ) . ';';
                    if ( $height ) $style .= 'height:' . esc_attr( $height ) . ';';

                    echo '<h3 class="aorp-category" style="' . esc_attr( $style ) . '">' . esc_html( $term->name ) . '</h3>';
                    echo '<div class="aorp-items">';
                    while ( $query->have_posts() ) {
                        $query->the_post();
                        $this->render_menu_item();
                    }
                    echo '</div>';
                    wp_reset_postdata();
                }
            }
        }
        echo '</div>';
        return ob_get_clean();
    }

    private function render_menu_item() {
        $price = get_post_meta( get_the_ID(), '_aorp_price', true );
        $number = get_post_meta( get_the_ID(), '_aorp_number', true );
        $ingredients = get_post_meta( get_the_ID(), '_aorp_ingredients', true );
        $img = get_the_post_thumbnail( get_the_ID(), 'thumbnail', array( 'class' => 'aorp-img' ) );

        echo '<div class="aorp-item">';
        if ( $img ) {
            echo $img;
        }
        echo '<div class="aorp-text">';
        echo '<span class="aorp-number">' . esc_html( $number ) . '</span> ';
        echo '<span class="aorp-title">' . get_the_title() . '</span> ';
        echo '<span class="aorp-price">' . esc_html( $price ) . '</span>';
        echo '<div class="aorp-desc">' . get_the_content() . '</div>';
        if ( $ingredients ) {
            echo '<div class="aorp-ingredients"><em>' . esc_html( $ingredients ) . '</em></div>';
        }
        echo '</div>';
        echo '</div>';
    }

    public function enqueue_assets() {
        if ( ! is_admin() ) {
            wp_enqueue_style( 'aorp-style', plugin_dir_url( __FILE__ ) . 'assets/style.css' );
            wp_enqueue_script( 'aorp-script', plugin_dir_url( __FILE__ ) . 'assets/script.js', array('jquery'), false, true );
            wp_localize_script( 'aorp-script', 'aorp_ajax', array( 'url' => admin_url( 'admin-ajax.php' ) ) );
        }
    }

    public function admin_assets() {
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
    }

    public function load_textdomain() {
        load_plugin_textdomain( 'aorp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    public function ajax_toggle_dark() {
        $count = (int) get_option( 'aorp_dark_count', 0 );
        update_option( 'aorp_dark_count', $count + 1 );
        wp_send_json_success();
    }

    public function add_dashboard_widgets() {
        wp_add_dashboard_widget( 'aorp_dashboard', 'Dark-Mode Umschaltungen', array( $this, 'dashboard_widget_output' ) );
    }

    public function dashboard_widget_output() {
        $count = (int) get_option( 'aorp_dark_count', 0 );
        echo '<p>Gesamtanzahl: <strong>' . $count . '</strong></p>';
    }
}

new AIO_Restaurant_Plugin();
