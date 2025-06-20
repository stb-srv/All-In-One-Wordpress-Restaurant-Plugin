<?php
/*
Plugin Name: All-In-One WordPress Restaurant Plugin
Description: Umfangreiches Speisekarten-Plugin mit Dark‑Mode, Suchfunktion und Import/Export.
Version: 1.1.4
Author: stb-srv
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class AIO_Restaurant_Plugin {

    public function __construct() {
        add_action( 'init', array( $this, 'register_post_type' ) );
        add_action( 'init', array( $this, 'register_taxonomy' ) );
        add_action( 'init', array( $this, 'register_legend_type' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_legend_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_legend_meta' ) );
        add_shortcode( 'speisekarte', array( $this, 'speisekarte_shortcode' ) );
        add_shortcode( 'speisekarte_legende', array( $this, 'legend_shortcode' ) );
        add_shortcode( 'restaurant_lightswitcher', array( $this, 'lightswitcher_shortcode' ) );
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_post_aorp_export_csv', array( $this, 'export_csv' ) );
        add_action( 'admin_post_aorp_import_csv', array( $this, 'import_csv' ) );
        add_action( 'admin_post_aorp_undo_import', array( $this, 'undo_import' ) );
        add_action( 'admin_post_aorp_add_category', array( $this, 'add_category' ) );
        add_action( 'admin_post_aorp_update_category', array( $this, 'update_category' ) );
        add_action( 'admin_post_aorp_delete_category', array( $this, 'delete_category' ) );
        add_action( 'admin_post_aorp_add_item', array( $this, 'add_item' ) );
        add_action( 'admin_post_aorp_add_legend', array( $this, 'add_legend' ) );
        add_action( 'admin_post_aorp_update_item', array( $this, 'update_item' ) );
        add_action( 'admin_post_aorp_delete_item', array( $this, 'delete_item' ) );
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
            'public' => false,
            'show_ui' => false,
            'has_archive' => false,
            'supports' => array( 'title', 'editor', 'thumbnail' )
        ) );
    }

    public function register_legend_type() {
        $labels = array(
            'name' => __( 'Legende', 'aorp' ),
            'singular_name' => __( 'Legenden-Item', 'aorp' )
        );
        register_post_type( 'aorp_legend', array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => false,
            'supports' => array( 'title', 'page-attributes' )
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
            'show_ui' => false,
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
            <label for="aorp_code">Code</label>
            <input type="text" name="aorp_code" id="aorp_code" value="" />
        </div>
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
        $bg    = get_term_meta( $term->term_id, 'aorp_bg', true );
        $color = get_term_meta( $term->term_id, 'aorp_color', true );
        $size  = get_term_meta( $term->term_id, 'aorp_font_size', true );
        $width = get_term_meta( $term->term_id, 'aorp_width', true );
        $height = get_term_meta( $term->term_id, 'aorp_height', true );
        $code  = get_term_meta( $term->term_id, 'aorp_code', true );
        ?>
        <tr class="form-field">
            <th scope="row"><label for="aorp_code">Code</label></th>
            <td><input type="text" name="aorp_code" id="aorp_code" value="<?php echo esc_attr( $code ); ?>" /></td>
        </tr>
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
        $fields = array( 'aorp_code', 'aorp_bg', 'aorp_color', 'aorp_font_size', 'aorp_width', 'aorp_height' );
        foreach ( $fields as $field ) {
            if ( isset( $_POST[ $field ] ) ) {
                update_term_meta( $term_id, $field, sanitize_text_field( $_POST[ $field ] ) );
            }
        }
    }

    public function add_meta_boxes() {
        add_meta_box( 'aorp_meta', __( 'Speise Details', 'aorp' ), array( $this, 'render_meta_box' ), 'aorp_menu_item', 'normal', 'default' );
    }

    public function add_legend_meta_box() {
        add_meta_box( 'aorp_legend_meta', __( 'Legenden-Details', 'aorp' ), array( $this, 'render_legend_meta' ), 'aorp_legend', 'normal', 'default' );
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

    public function render_legend_meta( $post ) {
        wp_nonce_field( 'aorp_legend_meta', 'aorp_legend_nonce' );
        $symbol = get_post_meta( $post->ID, '_aorp_symbol', true );
        $color  = get_post_meta( $post->ID, '_aorp_color', true );
        ?>
        <p>
            <label for="aorp_symbol"><?php _e( 'Symbol', 'aorp' ); ?></label>
            <input type="text" name="aorp_symbol" id="aorp_symbol" value="<?php echo esc_attr( $symbol ); ?>" style="width:100%;" />
        </p>
        <p>
            <label for="aorp_color"><?php _e( 'Farbe', 'aorp' ); ?></label>
            <input type="text" name="aorp_color" id="aorp_color" value="<?php echo esc_attr( $color ); ?>" class="aorp-color" />
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

    public function save_legend_meta( $post_id ) {
        if ( ! isset( $_POST['aorp_legend_nonce'] ) || ! wp_verify_nonce( $_POST['aorp_legend_nonce'], 'aorp_legend_meta' ) ) {
            return $post_id;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }
        if ( isset( $_POST['aorp_symbol'] ) ) {
            update_post_meta( $post_id, '_aorp_symbol', sanitize_text_field( $_POST['aorp_symbol'] ) );
        }
        if ( isset( $_POST['aorp_color'] ) ) {
            update_post_meta( $post_id, '_aorp_color', sanitize_text_field( $_POST['aorp_color'] ) );
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

    public function legend_shortcode() {
        $items = get_posts( array( 'post_type' => 'aorp_legend', 'numberposts' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ) );
        if ( ! $items ) {
            return '';
        }
        ob_start();
        echo '<div class="aorp-legende">';
        foreach ( $items as $item ) {
            $symbol = get_post_meta( $item->ID, '_aorp_symbol', true );
            $color  = get_post_meta( $item->ID, '_aorp_color', true );
            echo '<div class="aorp-legende-item" style="color:' . esc_attr( $color ) . '">';
            echo '<span class="aorp-legend-symbol">' . esc_html( $symbol ) . '</span> ';
            echo '<span class="aorp-legend-desc">' . esc_html( $item->post_title ) . '</span>';
            echo '</div>';
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

    public function lightswitcher_shortcode() {
        return '<div id="aorp-toggle" aria-label="Dark Mode umschalten" role="button" tabindex="0">🌓</div>';
    }

    public function admin_menu() {
        add_menu_page( 'Speisekarte', 'Speisekarte', 'manage_options', 'aorp_manage', array( $this, 'manage_page' ), 'dashicons-list-view' );
        add_submenu_page( 'aorp_manage', 'Import/Export', 'Import/Export', 'manage_options', 'aorp_export', array( $this, 'export_page' ) );
        add_submenu_page( 'aorp_manage', 'Historie', 'Historie', 'manage_options', 'aorp_history', array( $this, 'history_page' ) );
    }

    public function export_page() {
        ?>
        <div class="wrap">
            <h1>Import/Export</h1>
            <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="aorp_import_csv" />
                <select name="import_format">
                    <option value="csv">CSV</option>
                    <option value="json">JSON</option>
                    <option value="yaml">YAML</option>
                </select>
                <input type="file" name="import_file" accept=".csv,.json,.yml,.yaml" />
                <?php submit_button( 'Importieren' ); ?>
            </form>
            <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
                <input type="hidden" name="action" value="aorp_export_csv" />
                <select name="export_format">
                    <option value="csv">CSV</option>
                    <option value="json">JSON</option>
                    <option value="yaml">YAML</option>
                </select>
                <?php submit_button( 'Exportieren' ); ?>
            </form>
        </div>
        <?php
    }

    public function manage_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $categories = get_terms( array( 'taxonomy' => 'aorp_menu_category', 'hide_empty' => false ) );
        $items      = get_posts( array( 'post_type' => 'aorp_menu_item', 'numberposts' => -1 ) );
        $legend     = get_posts( array( 'post_type' => 'aorp_legend', 'numberposts' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ) );

        $ingredients_list = array();
        foreach ( $items as $itm ) {
            $ing = get_post_meta( $itm->ID, '_aorp_ingredients', true );
            if ( '' !== $ing ) {
                $ingredients_list[] = $ing;
            }
        }
        $ingredients_list = array_unique( $ingredients_list );
        sort( $ingredients_list );

        $edit_item = isset( $_GET['edit'] ) ? intval( $_GET['edit'] ) : 0;
        $current   = $edit_item ? get_post( $edit_item ) : null;
        $edit_cat     = isset( $_GET['edit_cat'] ) ? intval( $_GET['edit_cat'] ) : 0;
        $current_cat  = $edit_cat ? get_term( $edit_cat, 'aorp_menu_category' ) : null;
        ?>
        <div class="wrap">
            <h1>Speisekarte Verwaltung</h1>

            <h2>Kategorien</h2>
            <?php if ( $current_cat ) : ?>
            <h3>Kategorie bearbeiten</h3>
            <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
                <input type="hidden" name="action" value="aorp_update_category" />
                <?php wp_nonce_field( 'aorp_edit_category_' . $current_cat->term_id ); ?>
                <input type="hidden" name="cat_id" value="<?php echo esc_attr( $current_cat->term_id ); ?>" />
                <p><input type="text" name="cat_code" value="<?php echo esc_attr( get_term_meta( $current_cat->term_id, 'aorp_code', true ) ); ?>" placeholder="Code" required /></p>
                <p><input type="text" name="cat_name" value="<?php echo esc_attr( $current_cat->name ); ?>" placeholder="Bezeichnung" required /></p>
                <?php submit_button( 'Kategorie speichern' ); ?>
                <a href="<?php echo admin_url( 'admin.php?page=aorp_manage' ); ?>">Abbrechen</a>
            </form>
            <?php else : ?>
            <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
                <input type="hidden" name="action" value="aorp_add_category" />
                <?php wp_nonce_field( 'aorp_add_category' ); ?>
                <p><input type="text" name="cat_code" placeholder="Code" required /></p>
                <p><input type="text" name="cat_name" placeholder="Bezeichnung" required /></p>
                <?php submit_button( 'Anlegen' ); ?>
            </form>
            <?php endif; ?>
            <?php if ( $categories ) : ?>
                <input type="text" id="aorp-cat-filter" placeholder="Suche" />
                <table class="widefat" id="aorp-cat-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Bezeichnung</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $categories as $cat ) : ?>
                            <tr>
                                <td><?php echo esc_html( get_term_meta( $cat->term_id, 'aorp_code', true ) ); ?></td>
                                <td><?php echo esc_html( $cat->name ); ?></td>
                                <td>
                                    <a href="<?php echo admin_url( 'admin.php?page=aorp_manage&edit_cat=' . $cat->term_id ); ?>">Bearbeiten</a> |
                                    <a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=aorp_delete_category&cat_id=' . $cat->term_id ), 'aorp_delete_category_' . $cat->term_id ); ?>" onclick="return confirm('Kategorie löschen?');">Löschen</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <h2>Speisen</h2>
            <?php if ( $current ) : ?>
            <h3>Speise bearbeiten</h3>
            <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
                <input type="hidden" name="action" value="aorp_update_item" />
                <?php wp_nonce_field( 'aorp_edit_item' ); ?>
                <input type="hidden" name="item_id" value="<?php echo esc_attr( $current->ID ); ?>" />
                <p><input type="text" name="item_title" value="<?php echo esc_attr( $current->post_title ); ?>" placeholder="Name" required /></p>
                <p><textarea name="item_description" placeholder="Beschreibung" rows="3" class="aorp-ing-text"><?php echo esc_textarea( $current->post_content ); ?></textarea></p>
                <p><input type="text" name="item_price" value="<?php echo esc_attr( get_post_meta( $current->ID, '_aorp_price', true ) ); ?>" placeholder="Preis" /></p>
                <p><input type="text" name="item_number" value="<?php echo esc_attr( get_post_meta( $current->ID, '_aorp_number', true ) ); ?>" placeholder="Nummer" /></p>
                <p>
                    <input type="hidden" id="aorp_image_id" name="item_image_id" value="<?php echo esc_attr( get_post_thumbnail_id( $current->ID ) ); ?>" />
                    <button type="button" class="button aorp-image-upload">Bild auswählen</button>
                    <span class="aorp-image-preview"><?php echo get_the_post_thumbnail( $current->ID, array(80,80) ); ?></span>
                </p>
                <p>
                    <select name="item_category">
                        <option value="">Kategorie wählen</option>
                        <?php foreach ( $categories as $cat ) : ?>
                            <option value="<?php echo esc_attr( $cat->term_id ); ?>" <?php selected( has_term( $cat->term_id, 'aorp_menu_category', $current->ID ) ); ?>><?php echo esc_html( $cat->name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </p>
                <p>
                    <select class="aorp-ing-select">
                        <option value="">Inhaltsstoff wählen</option>
                        <?php foreach ( $ingredients_list as $ing ) : ?>
                            <option value="<?php echo esc_attr( $ing ); ?>"><?php echo esc_html( $ing ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </p>
                <p><textarea name="item_ingredients" id="aorp_ingredients" class="aorp-ing-text" rows="2"><?php echo esc_textarea( get_post_meta( $current->ID, '_aorp_ingredients', true ) ); ?></textarea></p>
                <?php submit_button( 'Speise speichern' ); ?>
                <a href="<?php echo admin_url( 'admin.php?page=aorp_manage' ); ?>">Abbrechen</a>
            </form>
            <?php else : ?>
            <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
                <input type="hidden" name="action" value="aorp_add_item" />
                <?php wp_nonce_field( 'aorp_add_item' ); ?>
                <p><input type="text" name="item_title" placeholder="Name" required /></p>
                <p><textarea name="item_description" placeholder="Beschreibung" rows="3" class="aorp-ing-text"></textarea></p>
                <p><input type="text" name="item_price" placeholder="Preis" /></p>
                <p><input type="text" name="item_number" placeholder="Nummer" /></p>
                <p>
                    <input type="hidden" id="aorp_image_id" name="item_image_id" value="" />
                    <button type="button" class="button aorp-image-upload">Bild auswählen</button>
                    <span class="aorp-image-preview"></span>
                </p>
                <p>
                    <select name="item_category">
                        <option value="">Kategorie wählen</option>
                        <?php foreach ( $categories as $cat ) : ?>
                            <option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </p>
                <p>
                    <select class="aorp-ing-select">
                        <option value="">Inhaltsstoff wählen</option>
                        <?php foreach ( $ingredients_list as $ing ) : ?>
                            <option value="<?php echo esc_attr( $ing ); ?>"><?php echo esc_html( $ing ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </p>
                <p><textarea name="item_ingredients" id="aorp_ingredients" class="aorp-ing-text" rows="2"></textarea></p>
                <?php submit_button( 'Speise anlegen' ); ?>
            </form>
            <?php endif; ?>

            <?php if ( $items ) : ?>
                <h3>Alle Speisen</h3>
                <input type="text" id="aorp-item-filter" placeholder="Suche" />
                <table class="widefat" id="aorp-items-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Preis</th>
                            <th>Nummer</th>
                            <th>Kategorie</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $items as $item ) : ?>
                            <tr>
                                <td><?php echo esc_html( $item->post_title ); ?></td>
                                <td><?php echo esc_html( get_post_meta( $item->ID, '_aorp_price', true ) ); ?></td>
                                <td><?php echo esc_html( get_post_meta( $item->ID, '_aorp_number', true ) ); ?></td>
                                <td>
                                    <?php
                                        $terms = get_the_terms( $item->ID, 'aorp_menu_category' );
                                        if ( $terms && ! is_wp_error( $terms ) ) {
                                            echo esc_html( $terms[0]->name );
                                        }
                                    ?>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url( 'admin.php?page=aorp_manage&edit=' . $item->ID ); ?>">Bearbeiten</a> |
                                    <a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=aorp_delete_item&item_id=' . $item->ID ), 'aorp_delete_item_' . $item->ID ); ?>" onclick="return confirm('Speise löschen?');">Löschen</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <h2>Legende</h2>
            <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
                <input type="hidden" name="action" value="aorp_add_legend" />
                <?php wp_nonce_field( 'aorp_add_legend' ); ?>
                <p><input type="text" name="legend_title" placeholder="Beschreibung" required /></p>
                <p><input type="text" name="legend_symbol" placeholder="Symbol" /></p>
                <p><input type="text" name="legend_color" placeholder="Farbe" class="aorp-color" /></p>
                <?php submit_button( 'Item anlegen' ); ?>
            </form>
            <?php if ( $legend ) : ?>
                <ul>
                    <?php foreach ( $legend as $l ) : ?>
                        <li><?php echo esc_html( $l->post_title ); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php
    }

    public function export_csv() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nicht erlaubt' );
        }
        $format = isset( $_POST['export_format'] ) ? sanitize_text_field( $_POST['export_format'] ) : 'csv';
        $data   = array();
        $items  = get_posts( array( 'post_type' => 'aorp_menu_item', 'numberposts' => -1 ) );
        foreach ( $items as $item ) {
            $data[] = array(
                'number'      => get_post_meta( $item->ID, '_aorp_number', true ),
                'title'       => $item->post_title,
                'description' => $item->post_content,
                'price'       => get_post_meta( $item->ID, '_aorp_price', true ),
            );
        }
        $history = get_option( 'aorp_history', array() );
        $history[] = array( 'action' => 'export', 'time' => current_time( 'mysql' ), 'user' => get_current_user_id(), 'format' => $format );
        update_option( 'aorp_history', $history );

        if ( 'json' === $format ) {
            header( 'Content-Type: application/json' );
            header( 'Content-Disposition: attachment; filename="speisekarte.json"' );
            echo wp_json_encode( $data );
        } elseif ( 'yaml' === $format ) {
            header( 'Content-Type: text/yaml' );
            header( 'Content-Disposition: attachment; filename="speisekarte.yaml"' );
            echo $this->array_to_yaml( $data );
        } else {
            header( 'Content-Type: text/csv' );
            header( 'Content-Disposition: attachment; filename="speisekarte.csv"' );
            $out = fopen( 'php://output', 'w' );
            fputcsv( $out, array( 'Nummer', 'Titel', 'Beschreibung', 'Preis' ) );
            foreach ( $data as $row ) {
                fputcsv( $out, $row );
            }
            fclose( $out );
        }
        exit;
    }

    public function import_csv() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nicht erlaubt' );
        }
        if ( ! empty( $_FILES['import_file']['tmp_name'] ) ) {
            $format = isset( $_POST['import_format'] ) ? sanitize_text_field( $_POST['import_format'] ) : 'csv';
            $rows   = array();
            if ( 'json' === $format ) {
                $rows = json_decode( file_get_contents( $_FILES['import_file']['tmp_name'] ), true );
            } elseif ( 'yaml' === $format ) {
                $rows = $this->yaml_to_array( file_get_contents( $_FILES['import_file']['tmp_name'] ) );
            } else {
                $handle = fopen( $_FILES['import_file']['tmp_name'], 'r' );
                if ( $handle ) {
                    fgetcsv( $handle );
                    while ( ( $data = fgetcsv( $handle ) ) !== false ) {
                        $rows[] = array( 'number' => $data[0], 'title' => $data[1], 'description' => $data[2], 'price' => $data[3] );
                    }
                    fclose( $handle );
                }
            }
            $ids = array();
            if ( is_array( $rows ) ) {
                foreach ( $rows as $row ) {
                    $post_id = wp_insert_post( array( 'post_type' => 'aorp_menu_item', 'post_title' => sanitize_text_field( $row['title'] ), 'post_content' => sanitize_textarea_field( $row['description'] ), 'post_status' => 'publish' ) );
                    if ( $post_id ) {
                        update_post_meta( $post_id, '_aorp_number', sanitize_text_field( $row['number'] ) );
                        update_post_meta( $post_id, '_aorp_price', sanitize_text_field( $row['price'] ) );
                        $ids[] = $post_id;
                    }
                }
            }
            $history = get_option( 'aorp_history', array() );
            $history[] = array( 'action' => 'import', 'time' => current_time( 'mysql' ), 'user' => get_current_user_id(), 'ids' => $ids, 'format' => $format );
            update_option( 'aorp_history', $history );
        }
        wp_redirect( admin_url( 'admin.php?page=aorp_export' ) );
        exit;
    }

    public function enqueue_assets() {
        if ( ! is_admin() ) {
            wp_enqueue_style( 'aorp-style', plugin_dir_url( __FILE__ ) . 'assets/style.css' );
            wp_enqueue_script( 'aorp-script', plugin_dir_url( __FILE__ ) . 'assets/script.js', array('jquery'), false, true );
            wp_localize_script( 'aorp-script', 'aorp_ajax', array( 'url' => admin_url( 'admin-ajax.php' ) ) );
        }
    }

    public function admin_assets( $hook ) {
        if ( strpos( $hook, 'aorp' ) !== false ) {
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_script( 'wp-color-picker' );
            wp_enqueue_media();
            wp_enqueue_script( 'aorp-admin', plugin_dir_url( __FILE__ ) . 'assets/admin.js', array( 'jquery' ), false, true );
        }
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

    private function array_to_yaml( $data ) {
        if ( function_exists( 'yaml_emit' ) ) {
            return yaml_emit( $data );
        }
        $yaml = '';
        foreach ( $data as $row ) {
            $yaml .= "-\n";
            foreach ( $row as $k => $v ) {
                $yaml .= '  ' . $k . ': ' . str_replace( "\n", '\\n', $v ) . "\n";
            }
        }
        return $yaml;
    }

    private function yaml_to_array( $text ) {
        if ( function_exists( 'yaml_parse' ) ) {
            return yaml_parse( $text );
        }
        $rows    = array();
        $current = array();
        foreach ( preg_split( '/\r?\n/', $text ) as $line ) {
            if ( '' === trim( $line ) ) {
                continue;
            }
            if ( '-' === $line[0] ) {
                if ( $current ) {
                    $rows[] = $current;
                }
                $current = array();
            } else {
                list( $k, $v ) = array_map( 'trim', explode( ':', $line, 2 ) );
                $current[ $k ] = str_replace( '\\n', "\n", $v );
            }
        }
        if ( $current ) {
            $rows[] = $current;
        }
        return $rows;
    }

    public function add_category() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nicht erlaubt' );
        }
        check_admin_referer( 'aorp_add_category' );
        if ( ! empty( $_POST['cat_name'] ) ) {
            $term = wp_insert_term( sanitize_text_field( $_POST['cat_name'] ), 'aorp_menu_category' );
            if ( ! is_wp_error( $term ) && isset( $_POST['cat_code'] ) ) {
                update_term_meta( $term['term_id'], 'aorp_code', sanitize_text_field( $_POST['cat_code'] ) );
            }
        }
        wp_redirect( admin_url( 'admin.php?page=aorp_manage' ) );
        exit;
    }

    public function update_category() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nicht erlaubt' );
        }
        $term_id = intval( $_POST['cat_id'] );
        check_admin_referer( 'aorp_edit_category_' . $term_id );
        if ( $term_id && ! empty( $_POST['cat_name'] ) ) {
            wp_update_term( $term_id, 'aorp_menu_category', array( 'name' => sanitize_text_field( $_POST['cat_name'] ) ) );
        }
        if ( isset( $_POST['cat_code'] ) ) {
            update_term_meta( $term_id, 'aorp_code', sanitize_text_field( $_POST['cat_code'] ) );
        }
        wp_redirect( admin_url( 'admin.php?page=aorp_manage' ) );
        exit;
    }

    public function delete_category() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nicht erlaubt' );
        }
        $term_id = isset( $_GET['cat_id'] ) ? intval( $_GET['cat_id'] ) : 0;
        check_admin_referer( 'aorp_delete_category_' . $term_id );
        if ( $term_id ) {
            wp_delete_term( $term_id, 'aorp_menu_category' );
        }
        wp_redirect( admin_url( 'admin.php?page=aorp_manage' ) );
        exit;
    }

    public function add_item() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nicht erlaubt' );
        }
        check_admin_referer( 'aorp_add_item' );
        $post_id = wp_insert_post( array(
            'post_type'   => 'aorp_menu_item',
            'post_status' => 'publish',
            'post_title'  => sanitize_text_field( $_POST['item_title'] ),
            'post_content'=> sanitize_textarea_field( $_POST['item_description'] )
        ) );
        if ( $post_id ) {
            if ( ! empty( $_POST['item_category'] ) ) {
                wp_set_object_terms( $post_id, intval( $_POST['item_category'] ), 'aorp_menu_category' );
            }
            if ( isset( $_POST['item_price'] ) ) {
                update_post_meta( $post_id, '_aorp_price', sanitize_text_field( $_POST['item_price'] ) );
            }
            if ( isset( $_POST['item_number'] ) ) {
                update_post_meta( $post_id, '_aorp_number', sanitize_text_field( $_POST['item_number'] ) );
            }
            if ( ! empty( $_POST['item_image_id'] ) ) {
                set_post_thumbnail( $post_id, intval( $_POST['item_image_id'] ) );
            }
            if ( isset( $_POST['item_ingredients'] ) ) {
                update_post_meta( $post_id, '_aorp_ingredients', sanitize_textarea_field( $_POST['item_ingredients'] ) );
            }
        }
        wp_redirect( admin_url( 'admin.php?page=aorp_manage' ) );
        exit;
    }

    public function update_item() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nicht erlaubt' );
        }
        check_admin_referer( 'aorp_edit_item' );
        $post_id = intval( $_POST['item_id'] );
        wp_update_post( array(
            'ID'           => $post_id,
            'post_title'   => sanitize_text_field( $_POST['item_title'] ),
            'post_content' => sanitize_textarea_field( $_POST['item_description'] )
        ) );
        if ( ! empty( $_POST['item_category'] ) ) {
            wp_set_object_terms( $post_id, intval( $_POST['item_category'] ), 'aorp_menu_category' );
        } else {
            wp_set_object_terms( $post_id, array(), 'aorp_menu_category' );
        }
        if ( isset( $_POST['item_price'] ) ) {
            update_post_meta( $post_id, '_aorp_price', sanitize_text_field( $_POST['item_price'] ) );
        }
        if ( isset( $_POST['item_number'] ) ) {
            update_post_meta( $post_id, '_aorp_number', sanitize_text_field( $_POST['item_number'] ) );
        }
        if ( ! empty( $_POST['item_image_id'] ) ) {
            set_post_thumbnail( $post_id, intval( $_POST['item_image_id'] ) );
        }
        if ( isset( $_POST['item_ingredients'] ) ) {
            update_post_meta( $post_id, '_aorp_ingredients', sanitize_textarea_field( $_POST['item_ingredients'] ) );
        }
        wp_redirect( admin_url( 'admin.php?page=aorp_manage' ) );
        exit;
    }

    public function delete_item() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nicht erlaubt' );
        }
        $post_id = isset( $_GET['item_id'] ) ? intval( $_GET['item_id'] ) : 0;
        check_admin_referer( 'aorp_delete_item_' . $post_id );
        if ( $post_id ) {
            wp_delete_post( $post_id, true );
        }
        wp_redirect( admin_url( 'admin.php?page=aorp_manage' ) );
        exit;
    }

    public function add_legend() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nicht erlaubt' );
        }
        check_admin_referer( 'aorp_add_legend' );
        $post_id = wp_insert_post( array(
            'post_type'   => 'aorp_legend',
            'post_status' => 'publish',
            'post_title'  => sanitize_text_field( $_POST['legend_title'] )
        ) );
        if ( $post_id ) {
            if ( isset( $_POST['legend_symbol'] ) ) {
                update_post_meta( $post_id, '_aorp_symbol', sanitize_text_field( $_POST['legend_symbol'] ) );
            }
            if ( isset( $_POST['legend_color'] ) ) {
                update_post_meta( $post_id, '_aorp_color', sanitize_text_field( $_POST['legend_color'] ) );
            }
        }
        wp_redirect( admin_url( 'admin.php?page=aorp_manage' ) );
        exit;
    }

    public function history_page() {
        echo '<div class="wrap"><h1>Import/Export Historie</h1><table class="widefat"><thead><tr><th>Aktion</th><th>Zeit</th><th>Benutzer</th><th>Format</th><th>Undo</th></tr></thead><tbody>';
        $history = array_reverse( get_option( 'aorp_history', array() ) );
        foreach ( $history as $index => $row ) {
            $user = get_userdata( $row['user'] );
            echo '<tr><td>' . esc_html( $row['action'] ) . '</td><td>' . esc_html( $row['time'] ) . '</td><td>' . esc_html( $user ? $user->display_name : $row['user'] ) . '</td><td>' . esc_html( isset( $row['format'] ) ? $row['format'] : '' ) . '</td><td>';
            if ( 'import' === $row['action'] && ! empty( $row['ids'] ) ) {
                $url = wp_nonce_url( admin_url( 'admin-post.php?action=aorp_undo_import&index=' . $index ), 'aorp_undo_' . $index );
                echo '<a href="' . esc_url( $url ) . '">Rückgängig</a>';
            }
            echo '</td></tr>';
        }
        echo '</tbody></table></div>';
    }

    public function undo_import() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nicht erlaubt' );
        }
        $index = isset( $_GET['index'] ) ? intval( $_GET['index'] ) : -1;
        check_admin_referer( 'aorp_undo_' . $index );
        $history = get_option( 'aorp_history', array() );
        if ( isset( $history[ $index ] ) && 'import' === $history[ $index ]['action'] && ! empty( $history[ $index ]['ids'] ) ) {
            foreach ( $history[ $index ]['ids'] as $id ) {
                wp_delete_post( $id, true );
            }
            $history[] = array( 'action' => 'undo', 'time' => current_time( 'mysql' ), 'user' => get_current_user_id(), 'format' => $history[ $index ]['format'] );
            update_option( 'aorp_history', $history );
        }
        wp_redirect( admin_url( 'admin.php?page=aorp_history' ) );
        exit;
    }
}

require_once plugin_dir_path( __FILE__ ) . 'includes/widgets.php';

new AIO_Restaurant_Plugin();
