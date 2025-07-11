<?php
/*
Plugin Name: All-In-One WordPress Restaurant Plugin
Description: Umfangreiches Speisekarten-Plugin mit Dark‑Mode, Suchfunktion und Import/Export.
Version: 1.4.1
Author: stb-srv
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function aorp_wp_kses_post_iframe( $content ) {
    $allowed = wp_kses_allowed_html( 'post' );
    $allowed['iframe'] = array(
        'src'             => true,
        'width'           => true,
        'height'          => true,
        'frameborder'     => true,
        'allowfullscreen' => true,
        'title'           => true,
        'loading'         => true,
        'style'           => true,
        'class'           => true,
        'id'              => true,
    );
    return wp_kses( $content, $allowed );
}

class AIO_Restaurant_Plugin {

    public function __construct() {
        add_action( 'init', array( $this, 'register_post_type' ) );
        add_action( 'init', array( $this, 'register_drink_post_type' ) );
        add_action( 'init', array( $this, 'register_taxonomy' ) );
        add_action( 'init', array( $this, 'register_ingredient_type' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_meta_boxes' ) );
        add_shortcode( 'speisekarte', array( $this, 'speisekarte_shortcode' ) );
        add_shortcode( 'restaurant_lightswitcher', array( $this, 'lightswitcher_shortcode' ) );
        add_shortcode( 'getraenkekarte', array( $this, 'getraenkekarte_shortcode' ) );
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_post_aorp_export_csv', array( $this, 'export_csv' ) );
        add_action( 'admin_post_aorp_import_csv', array( $this, 'import_csv' ) );
        add_action( 'admin_post_aorp_undo_import', array( $this, 'undo_import' ) );
        add_action( 'admin_post_aorp_add_category', array( $this, 'add_category' ) );
        add_action( 'admin_post_aorp_update_category', array( $this, 'update_category' ) );
        add_action( 'admin_post_aorp_delete_category', array( $this, 'delete_category' ) );
        add_action( 'admin_post_aorp_bulk_delete_category', array( $this, 'bulk_delete_category' ) );
        add_action( 'admin_post_aorp_add_item', array( $this, 'add_item' ) );
        add_action( 'admin_post_aorp_add_ingredient', array( $this, 'add_ingredient' ) );
        add_action( 'admin_post_aorp_update_ingredient', array( $this, 'update_ingredient' ) );
        add_action( 'admin_post_aorp_delete_ingredient', array( $this, 'delete_ingredient' ) );
        add_action( 'admin_post_aorp_bulk_delete_ingredient', array( $this, 'bulk_delete_ingredient' ) );
        add_action( 'admin_post_aorp_update_item', array( $this, 'update_item' ) );
        add_action( 'admin_post_aorp_delete_item', array( $this, 'delete_item' ) );
        add_action( 'admin_post_aorp_bulk_delete_item', array( $this, 'bulk_delete_item' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_head', array( $this, 'output_custom_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action( 'wp_ajax_aorp_toggle_dark', array( $this, 'ajax_toggle_dark' ) );
        add_action( 'wp_ajax_nopriv_aorp_toggle_dark', array( $this, 'ajax_toggle_dark' ) );
        add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
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

    public function register_drink_post_type() {
        $labels = array(
            'name' => __( 'Getränke', 'aorp' ),
            'singular_name' => __( 'Getränk', 'aorp' )
        );
        register_post_type( 'aorp_drink_item', array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'has_archive' => false,
            'supports' => array( 'title', 'editor', 'thumbnail' )
        ) );
    }

    public function register_ingredient_type() {
        $labels = array(
            'name' => __( 'Inhaltsstoffe', 'aorp' ),
            'singular_name' => __( 'Inhaltsstoff', 'aorp' )
        );
        register_post_type( 'aorp_ingredient', array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => false,
            'supports' => array( 'title' )
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
            <label for="aorp_bg">Hintergrundfarbe</label>
            <input type="text" name="aorp_bg" id="aorp_bg" value="" class="aorp-color" />
        </div>
        <div class="form-field">
            <label for="aorp_color">Schriftfarbe</label>
            <input type="text" name="aorp_color" id="aorp_color" value="" class="aorp-color" />
        </div>
        <div class="form-field">
            <label for="aorp_font_size">Schriftgröße</label>
            <select name="aorp_font_size" id="aorp_font_size">
                <?php foreach ( array( '', '0.8em', '0.9em', '1em', '1.1em', '1.2em', '1.3em', '1.4em', '1.5em' ) as $fs ) : ?>
                    <option value="<?php echo esc_attr( $fs ); ?>"><?php echo $fs ? esc_html( $fs ) : '--'; ?></option>
                <?php endforeach; ?>
            </select>
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

    private function render_category_form( $categories, $current_cat ) {
        ?>
        <div class="aorp-section">
        <h2>Kategorien</h2>
        <?php if ( $current_cat ) : ?>
        <h3>Kategorie bearbeiten</h3>
        <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
            <input type="hidden" name="action" value="aorp_update_category" />
            <?php wp_nonce_field( 'aorp_edit_category_' . $current_cat->term_id ); ?>
            <input type="hidden" name="cat_id" value="<?php echo esc_attr( $current_cat->term_id ); ?>" />
            <p><input type="text" name="cat_name" value="<?php echo esc_attr( $current_cat->name ); ?>" placeholder="Bezeichnung" required /></p>
            <?php submit_button( 'Kategorie speichern' ); ?>
            <a href="<?php echo admin_url( 'admin.php?page=aorp_manage' ); ?>">Abbrechen</a>
        </form>
        <?php else : ?>
        <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
            <input type="hidden" name="action" value="aorp_add_category" />
            <?php wp_nonce_field( 'aorp_add_category' ); ?>
            <p><input type="text" name="cat_name" placeholder="Bezeichnung" required /></p>
            <?php submit_button( 'Anlegen' ); ?>
        </form>
        <?php endif; ?>
        <?php if ( $categories ) : ?>
            <input type="text" id="aorp-cat-filter" placeholder="Suche" />
            <p>
                <button class="button aorp-select-all" data-target="#aorp-cat-table tbody input[type=checkbox]">Alle auswählen</button>
                <button class="button aorp-unselect-all" data-target="#aorp-cat-table tbody input[type=checkbox]">Auswahl aufheben</button>
            </p>
            <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
                <input type="hidden" name="action" value="aorp_bulk_delete_category" />
                <?php wp_nonce_field( 'aorp_bulk_delete_category' ); ?>
                <table class="widefat" id="aorp-cat-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Bezeichnung</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $categories as $cat ) : ?>
                            <tr>
                                <td><input type="checkbox" name="cat_ids[]" value="<?php echo esc_attr( $cat->term_id ); ?>" /></td>
                                <td><?php echo esc_html( $cat->name ); ?></td>
                                <td>
                                    <a href="<?php echo admin_url( 'admin.php?page=aorp_manage&edit_cat=' . $cat->term_id ); ?>">Bearbeiten</a> |
                                    <a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=aorp_delete_category&cat_id=' . $cat->term_id ), 'aorp_delete_category_' . $cat->term_id ); ?>" onclick="return confirm('Kategorie löschen?');">Löschen</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php submit_button( 'Ausgewählte löschen', 'delete' ); ?>
            </form>
        <?php endif; ?>
        </div>
        <?php
    }

    private function render_ingredient_form( $ingredients_posts, $current_ing ) {
        ?>
        <div class="aorp-section">
        <h2>Inhaltsstoffe</h2>
        <?php if ( $current_ing ) : ?>
        <h3>Inhaltsstoff bearbeiten</h3>
        <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
        <input type="hidden" name="action" value="aorp_update_ingredient" />
        <?php wp_nonce_field( 'aorp_edit_ingredient_' . $current_ing->ID ); ?>
        <input type="hidden" name="ing_id" value="<?php echo esc_attr( $current_ing->ID ); ?>" />
        <p><input type="text" name="ing_code" value="<?php echo esc_attr( get_post_meta( $current_ing->ID, '_aorp_ing_code', true ) ); ?>" placeholder="Code" required /></p>
        <p><input type="text" name="ing_name" value="<?php echo esc_attr( $current_ing->post_title ); ?>" placeholder="Bezeichnung" required /></p>
        <?php submit_button( 'Inhaltsstoff speichern' ); ?>
        <a href="<?php echo admin_url( 'admin.php?page=aorp_manage' ); ?>">Abbrechen</a>
        </form>
        <?php else : ?>
        <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
        <input type="hidden" name="action" value="aorp_add_ingredient" />
        <?php wp_nonce_field( 'aorp_add_ingredient' ); ?>
        <p><input type="text" name="ing_code" placeholder="Code" required /></p>
        <p><input type="text" name="ing_name" placeholder="Bezeichnung" required /></p>
        <?php submit_button( 'Anlegen' ); ?>
        </form>
        <?php endif; ?>
        <?php if ( $ingredients_posts ) : ?>
        <input type="text" id="aorp-ing-filter" placeholder="Suche" />
        <p>
            <button class="button aorp-select-all" data-target="#aorp-ing-table tbody input[type=checkbox]">Alle auswählen</button>
            <button class="button aorp-unselect-all" data-target="#aorp-ing-table tbody input[type=checkbox]">Auswahl aufheben</button>
        </p>
        <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
        <input type="hidden" name="action" value="aorp_bulk_delete_ingredient" />
        <?php wp_nonce_field( 'aorp_bulk_delete_ingredient' ); ?>
        <table class="widefat" id="aorp-ing-table">
        <thead>
        <tr>
        <th></th>
        <th>Code</th>
        <th>Bezeichnung</th>
        <th>Aktionen</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ( $ingredients_posts as $ing ) : ?>
        <tr>
        <td><input type="checkbox" name="ing_ids[]" value="<?php echo esc_attr( $ing->ID ); ?>" /></td>
        <td><?php echo esc_html( get_post_meta( $ing->ID, '_aorp_ing_code', true ) ); ?></td>
        <td><?php echo esc_html( $ing->post_title ); ?></td>
        <td>
        <a href="<?php echo admin_url( 'admin.php?page=aorp_manage&edit_ing=' . $ing->ID ); ?>">Bearbeiten</a> |
        <a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=aorp_delete_ingredient&ing_id=' . $ing->ID ), 'aorp_delete_ingredient_' . $ing->ID ); ?>" onclick="return confirm('Inhaltsstoff löschen?');">Löschen</a>
        </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        </table>
        <?php submit_button( 'Ausgewählte löschen', 'delete' ); ?>
        </form>
        <?php endif; ?>
        </div>
        <?php
    }

    private function render_item_form( $items, $categories, $ingredients_list, $current ) {
        ?>
        <div class="aorp-section">
        <h2>Speisen</h2>
        <?php if ( $current ) : ?>
        <h3>Speise bearbeiten</h3>
        <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
            <input type="hidden" name="action" value="aorp_update_item" />
            <?php wp_nonce_field( 'aorp_edit_item' ); ?>
            <input type="hidden" name="item_id" value="<?php echo esc_attr( $current->ID ); ?>" />
            <p><input type="text" name="item_number" value="<?php echo esc_attr( get_post_meta( $current->ID, '_aorp_number', true ) ); ?>" placeholder="Nummer" /></p>
            <p><input type="text" name="item_title" value="<?php echo esc_attr( $current->post_title ); ?>" placeholder="Name" required /></p>
            <p><textarea name="item_description" placeholder="Beschreibung" rows="3"><?php echo esc_textarea( $current->post_content ); ?></textarea></p>
            <p><input type="text" name="item_price" value="<?php echo esc_attr( get_post_meta( $current->ID, '_aorp_price', true ) ); ?>" placeholder="Preis (€)" /></p>
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
                        <option value="<?php echo esc_attr( $ing['code'] ); ?>"><?php echo esc_html( $ing['name'] . ' (' . $ing['code'] . ')' ); ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <div class="aorp-selected"></div>
            <input type="hidden" name="item_ingredients" id="aorp_ingredients" class="aorp-ing-text" value="<?php echo esc_attr( get_post_meta( $current->ID, '_aorp_ingredients', true ) ); ?>" />
            <?php submit_button( 'Speise speichern' ); ?>
            <a href="<?php echo admin_url( 'admin.php?page=aorp_manage' ); ?>">Abbrechen</a>
        </form>
        <?php else : ?>
        <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
            <input type="hidden" name="action" value="aorp_add_item" />
            <?php wp_nonce_field( 'aorp_add_item' ); ?>
            <p><input type="text" name="item_number" placeholder="Nummer" /></p>
            <p><input type="text" name="item_title" placeholder="Name" required /></p>
            <p><textarea name="item_description" placeholder="Beschreibung" rows="3"></textarea></p>
            <p><input type="text" name="item_price" placeholder="Preis (€)" /></p>
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
                        <option value="<?php echo esc_attr( $ing['code'] ); ?>"><?php echo esc_html( $ing['name'] . ' (' . $ing['code'] . ')' ); ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <div class="aorp-selected"></div>
            <input type="hidden" name="item_ingredients" id="aorp_ingredients" class="aorp-ing-text" value="" />
            <?php submit_button( 'Speise anlegen' ); ?>
        </form>
        <?php endif; ?>

        <?php if ( $items ) : ?>
            <h3>Alle Speisen</h3>
            <input type="text" id="aorp-item-filter" placeholder="Suche" />
            <p>
                <button class="button aorp-select-all" data-target="#aorp-items-table tbody input[type=checkbox]">Alle auswählen</button>
                <button class="button aorp-unselect-all" data-target="#aorp-items-table tbody input[type=checkbox]">Auswahl aufheben</button>
            </p>
            <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
                <input type="hidden" name="action" value="aorp_bulk_delete_item" />
                <?php wp_nonce_field( 'aorp_bulk_delete_item' ); ?>
                <table class="widefat" id="aorp-items-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Name</th>
                            <th>Beschreibung</th>
                            <th>Preis</th>
                            <th id="aorp-number-sort" class="sortable">Nummer</th>
                            <th>Inhaltsstoffe</th>
                            <th>Kategorie</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $items as $item ) : ?>
                            <tr>
                                <td><input type="checkbox" name="item_ids[]" value="<?php echo esc_attr( $item->ID ); ?>" /></td>
                                <td><?php echo esc_html( $item->post_title ); ?></td>
                                <td><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $item->post_content ), 15 ) ); ?></td>
                                <td><?php echo esc_html( $this->format_price( get_post_meta( $item->ID, '_aorp_price', true ) ) ); ?></td>
                                <td><?php echo esc_html( get_post_meta( $item->ID, '_aorp_number', true ) ); ?></td>
                                <td><?php echo esc_html( $this->get_ingredient_labels( get_post_meta( $item->ID, '_aorp_ingredients', true ) ) ); ?></td>
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
                <?php submit_button( 'Ausgewählte löschen', 'delete' ); ?>
            </form>
        <?php endif; ?>
        </div>
        <?php
    }

    public function edit_category_fields( $term, $taxonomy ) {
        $bg    = get_term_meta( $term->term_id, 'aorp_bg', true );
        $color = get_term_meta( $term->term_id, 'aorp_color', true );
        $size  = get_term_meta( $term->term_id, 'aorp_font_size', true );
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
            <th scope="row"><label for="aorp_font_size">Schriftgröße</label></th>
            <td>
                <select name="aorp_font_size" id="aorp_font_size">
                    <?php foreach ( array( '', '0.8em', '0.9em', '1em', '1.1em', '1.2em', '1.3em', '1.4em', '1.5em' ) as $fs ) : ?>
                        <option value="<?php echo esc_attr( $fs ); ?>" <?php selected( $size, $fs ); ?>><?php echo $fs ? esc_html( $fs ) : '--'; ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
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
        add_meta_box( 'aorp_drink_meta', __( 'Getränk Details', 'aorp' ), array( $this, 'render_drink_meta_box' ), 'aorp_drink_item', 'normal', 'default' );
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
            <label for="aorp_price"><?php _e( 'Preis (€)', 'aorp' ); ?></label>
            <input type="text" name="aorp_price" id="aorp_price" value="<?php echo esc_attr( $price ); ?>" />
        </p>
        <p>
            <label for="aorp_ingredients"><?php _e( 'Inhaltsstoffe', 'aorp' ); ?></label>
            <textarea name="aorp_ingredients" id="aorp_ingredients" style="width:100%;" rows="3"><?php echo esc_textarea( $ingredients ); ?></textarea>
        </p>
        <?php
    }

    public function render_drink_meta_box( $post ) {
        wp_nonce_field( basename( __FILE__ ), 'aorp_nonce' );
        $sizes = get_post_meta( $post->ID, '_aorp_drink_sizes', true );
        ?>
        <p>
            <label for="aorp_drink_sizes"><?php _e( 'Größen und Preise (je Zeile Größe=Preis)', 'aorp' ); ?></label>
            <textarea name="aorp_drink_sizes" id="aorp_drink_sizes" style="width:100%;" rows="3"><?php echo esc_textarea( $sizes ); ?></textarea>
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
        if ( isset( $_POST['aorp_drink_sizes'] ) ) {
            update_post_meta( $post_id, '_aorp_drink_sizes', sanitize_textarea_field( $_POST['aorp_drink_sizes'] ) );
        }
    }


    public function speisekarte_shortcode( $atts ) {
        $default = (int) get_option( 'aorp_menu_columns', 1 );
        $atts = shortcode_atts( array( 'columns' => $default, 'kategorien' => '' ), $atts, 'speisekarte' );
        $columns = max( 1, min( 3, intval( $atts['columns'] ) ) );

        ob_start();
        echo '<p class="aorp-note">🔽 Klicke auf eine Kategorie, um die Speisen einzublenden.</p>';
        echo '<div class="aorp-search-wrapper"><div id="aorp-search-overlay"><input type="text" id="aorp-search-input" placeholder="Suche nach Speisen …" /><div id="aorp-search-results"></div></div><button type="button" id="aorp-close-cats" class="aorp-close-cats">Alle Kategorien schließen</button></div>';

        echo '<div class="aorp-menu columns-' . $columns . '">';

        $args = array( 'taxonomy' => 'aorp_menu_category', 'hide_empty' => false );
        if ( ! empty( $atts['kategorien'] ) ) {
            $args['slug'] = array_map( 'trim', explode( ',', $atts['kategorien'] ) );
        }
        $terms = get_terms( $args );

        if ( empty( $terms ) ) {
            $query = new WP_Query( array(
                'post_type'      => 'aorp_menu_item',
                'posts_per_page' => -1,
                'meta_key'       => '_aorp_number',
                'orderby'        => 'meta_value_num',
                'order'          => 'ASC',
            ) );
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
                    'post_type'      => 'aorp_menu_item',
                    'tax_query'      => array( array( 'taxonomy' => 'aorp_menu_category', 'field' => 'term_id', 'terms' => $term->term_id ) ),
                    'meta_key'       => '_aorp_number',
                    'orderby'        => 'meta_value_num',
                    'order'          => 'ASC',
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
        $ingredient_names = $this->get_ingredient_names( $ingredients );
        $img = get_the_post_thumbnail( get_the_ID(), 'thumbnail', array( 'class' => 'aorp-img' ) );

        echo '<div class="aorp-item">';
        if ( $img ) {
            echo $img;
        }
        echo '<div class="aorp-text">';
        echo '<div class="aorp-header">';
        echo '<span class="aorp-number">' . esc_html( $number ) . '</span>';
        echo '<span class="aorp-title">' . get_the_title() . '</span>';
        echo '<span class="aorp-price">' . esc_html( $this->format_price( $price ) ) . '</span>';
        echo '</div>';
        $content = apply_filters( 'the_content', get_the_content() );
        echo '<div class="aorp-desc">' . $content . '</div>';
        if ( $ingredient_names ) {
            echo '<div class="aorp-ingredients"><em>' . esc_html( $ingredient_names ) . '</em></div>';
        }
        echo '</div>';
        echo '</div>';
    }

    private function render_drink_item() {
        $number = get_post_meta( get_the_ID(), '_aorp_number', true );
        $sizes_raw = get_post_meta( get_the_ID(), '_aorp_drink_sizes', true );
        $sizes = array();
        foreach ( explode( "\n", $sizes_raw ) as $line ) {
            $parts = array_map( 'trim', explode( '=', $line ) );
            if ( count( $parts ) === 2 ) {
                $sizes[] = esc_html( $parts[0] ) . ' - ' . esc_html( $this->format_price( $parts[1] ) );
            }
        }
        echo '<div class="aorp-item">';
        echo '<div class="aorp-text">';
        echo '<div class="aorp-header">';
        echo '<span class="aorp-number">' . esc_html( $number ) . '</span>';
        echo '<span class="aorp-title">' . get_the_title() . '</span>';
        echo '</div>';
        if ( $sizes ) {
            echo '<ul class="aorp-drink-sizes">';
            foreach ( $sizes as $s ) {
                echo '<li>' . $s . '</li>';
            }
            echo '</ul>';
        }
        $content = apply_filters( 'the_content', get_the_content() );
        if ( $content ) {
            echo '<div class="aorp-desc">' . $content . '</div>';
        }
        echo '</div>';
        echo '</div>';
    }

    public function lightswitcher_shortcode() {
        $light = $this->get_icon_html( 'light' );
        return '<div id="aorp-toggle" aria-label="Dark Mode umschalten" role="button" tabindex="0">' . $light . '</div>';
    }

    public function getraenkekarte_shortcode( $atts ) {
        $query = new WP_Query( array(
            'post_type'      => 'aorp_drink_item',
            'posts_per_page' => -1,
            'meta_key'       => '_aorp_number',
            'orderby'        => 'meta_value_num',
            'order'          => 'ASC',
        ) );
        ob_start();
        if ( $query->have_posts() ) {
            echo '<div class="aorp-menu">';
            while ( $query->have_posts() ) {
                $query->the_post();
                $this->render_drink_item();
            }
            echo '</div>';
            wp_reset_postdata();
        }
        return ob_get_clean();
    }

    private function get_icon_html( $type = 'light' ) {
        $set = get_option( 'aorp_icon_set', 'default' );
        if ( 'custom' === $set ) {
            $img_id = intval( get_option( 'aorp_icon_' . $type . '_img', 0 ) );
            if ( $img_id ) {
                return wp_get_attachment_image( $img_id, array( 24, 24 ) );
            }
            $char = get_option( 'aorp_icon_' . $type, $type === 'light' ? '☀️' : '🌙' );
            return esc_html( $char );
        } else {
            $sets = array(
                'default'  => array( '☀️', '🌙' ),
                'alt'      => array( '🌞', '🌜' ),
                'minimal'  => array( '🔆', '🌑' ),
                'eclipse'  => array( '🌞', '🌚' ),
                'sunset'   => array( '🌇', '🌃' ),
                'cloudy'   => array( '⛅', '🌙' ),
                'simple'   => array( '☼', '☾' ),
                'twilight' => array( '🌄', '🌌' ),
                'starry'   => array( '⭐', '🌜' ),
                'morning'  => array( '🌅', '🌠' ),
                'bright'   => array( '🔆', '🔅' ),
                'flower'   => array( '🌻', '🌑' ),
                'smiley'   => array( '😀', '😴' ),
            );
            if ( isset( $sets[ $set ] ) ) {
                $index = $type === 'light' ? 0 : 1;
                return esc_html( $sets[ $set ][ $index ] );
            }
            $char = $type === 'light' ? '☀️' : '🌙';
            return esc_html( $char );
        }
    }

    public function admin_menu() {
        add_menu_page( 'Speisekarte', 'Speisekarte', 'manage_options', 'aorp_manage', array( $this, 'manage_page' ), 'dashicons-list-view' );
        add_submenu_page( 'aorp_manage', 'Import/Export', 'Import/Export', 'manage_options', 'aorp_export', array( $this, 'export_page' ) );
        add_submenu_page( 'aorp_manage', 'Einstellungen', 'Einstellungen', 'manage_options', 'aorp_settings', array( $this, 'settings_page' ) );
        add_menu_page( 'Dark Mode', 'Dark Mode', 'manage_options', 'aorp_dark', array( $this, 'dark_page' ), 'dashicons-lightbulb' );
        // Historie wird direkt auf der Import/Export Seite angezeigt
    }

    public function export_page() {
        ?>
        <div class="wrap">
            <h1>Import/Export</h1>
            <h2>Mustervorlagen</h2>
            <ul>
                <li><a href="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'samples/import-template.csv' ); ?>">CSV Vorlage</a></li>
                <li><a href="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'samples/import-template.json' ); ?>">JSON Vorlage</a></li>
                <li><a href="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'samples/import-template.yaml' ); ?>">YAML Vorlage</a></li>
            </ul>
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
            <?php $this->render_history_table(); ?>
        </div>
        <?php
    }

    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>Einstellungen</h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'aorp_settings' ); ?>
                <?php
                    $cols  = (int) get_option( 'aorp_menu_columns', 1 );
                    $num   = get_option( 'aorp_size_number', '' );
                    $title = get_option( 'aorp_size_title', '' );
                    $desc  = get_option( 'aorp_size_desc', '' );
                    $price = get_option( 'aorp_size_price', '' );
                    $sizes = array( '', '0.8em', '0.9em', '1em', '1.1em', '1.2em', '1.3em', '1.4em', '1.5em' );
                ?>
                <h2>Allgemein</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Spaltenanzahl</th>
                        <td>
                            <select name="aorp_menu_columns">
                                <?php for ( $i = 1; $i <= 3; $i++ ) : ?>
                                    <option value="<?php echo $i; ?>" <?php selected( $cols, $i ); ?>><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </td>
                    </tr>
                </table>

                <h2>Darstellung</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="aorp_size_number">Schriftgröße Nummer (1em)</label></th>
                        <td>
                            <select name="aorp_size_number" id="aorp_size_number">
                                <?php foreach ( $sizes as $s ) : ?>
                                    <option value="<?php echo esc_attr( $s ); ?>" <?php selected( $num, $s ); ?>><?php echo $s ? esc_html( $s ) : '--'; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="aorp_size_title">Schriftgröße Titel (1em)</label></th>
                        <td>
                            <select name="aorp_size_title" id="aorp_size_title">
                                <?php foreach ( $sizes as $s ) : ?>
                                    <option value="<?php echo esc_attr( $s ); ?>" <?php selected( $title, $s ); ?>><?php echo $s ? esc_html( $s ) : '--'; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="aorp_size_desc">Schriftgröße Beschreibung (0.9em)</label></th>
                        <td>
                            <select name="aorp_size_desc" id="aorp_size_desc">
                                <?php foreach ( $sizes as $s ) : ?>
                                    <option value="<?php echo esc_attr( $s ); ?>" <?php selected( $desc, $s ); ?>><?php echo $s ? esc_html( $s ) : '--'; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                <tr>
                    <th scope="row"><label for="aorp_size_price">Schriftgröße Preis (1em)</label></th>
                    <td>
                        <select name="aorp_size_price" id="aorp_size_price">
                            <?php foreach ( $sizes as $s ) : ?>
                                <option value="<?php echo esc_attr( $s ); ?>" <?php selected( $price, $s ); ?>><?php echo $s ? esc_html( $s ) : '--'; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                </table>

                <?php submit_button(); ?>
            </form>
            <?php $this->output_custom_styles(); ?>
            <h2>Vorschau</h2>
            <div class="aorp-menu columns-<?php echo (int) $cols; ?>">
                <h3 class="aorp-category">Beispiel Kategorie</h3>
                <div class="aorp-items" style="display:block">
                    <div class="aorp-item">
                        <img src="data:image/gif;base64,R0lGODlhAQABAIAAAP////8AAAALAAAAAABAAEAAAICRAEAOw==" alt="" />
                        <div class="aorp-text">
                            <div class="aorp-header">
                                <span class="aorp-number">1</span>
                                <span class="aorp-title">Beispielgericht</span>
                                <span class="aorp-price">9,90 €</span>
                            </div>
                            <div class="aorp-desc">Leckere Beschreibung</div>
                            <div class="aorp-ingredients"><em>Zutat A, Zutat B</em></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function dark_page() {
        ?>
        <div class="wrap">
            <h1>Dark Mode</h1>
            <p class="description">Wähle zunächst ein Icon-Set oder lade eigene Icons hoch.
            Nach deinen Anpassungen klicke auf „Änderungen speichern“.</p>
            <form method="post" action="options.php">
                <?php settings_fields( 'aorp_dark' ); ?>
                <?php
                    $set        = get_option( 'aorp_icon_set', 'default' );
                    $light_img  = intval( get_option( 'aorp_icon_light_img', 0 ) );
                    $dark_img   = intval( get_option( 'aorp_icon_dark_img', 0 ) );
                    $icon_sets  = array(
                        'default'  => array('☀️','🌙'),
                        'alt'      => array('🌞','🌜'),
                        'minimal'  => array('🔆','🌑'),
                        'eclipse'  => array('🌞','🌚'),
                        'sunset'   => array('🌇','🌃'),
                        'cloudy'   => array('⛅','🌙'),
                        'simple'   => array('☼','☾'),
                        'twilight' => array('🌄','🌌'),
                        'starry'   => array('⭐','🌜'),
                        'morning'  => array('🌅','🌠'),
                        'bright'   => array('🔆','🔅'),
                        'flower'   => array('🌻','🌑'),
                        'smiley'   => array('😀','😴'),
                        'custom'   => array('', '')
                    );
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="aorp_icon_set">Icon Set</label></th>
                        <td>
                            <select name="aorp_icon_set" id="aorp_icon_set">
                                <?php foreach ( $icon_sets as $key => $icons ) : ?>
                                    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $set, $key ); ?>><?php echo $key === 'custom' ? 'Eigene Icons' : esc_html( $icons[0] . ' / ' . $icons[1] ); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span id="aorp_icon_preview" class="aorp-icon-preview"><?php echo $set === 'custom' ? 'Eigenes Icon-Set' : esc_html( $icon_sets[ $set ][0] . ' / ' . $icon_sets[ $set ][1] ); ?></span>
                        </td>
                    </tr>
                    <tr style="display:none;">
                        <th></th>
                        <td>
                            <input type="hidden" name="aorp_icon_light" id="aorp_icon_light" value="<?php echo esc_attr( get_option( 'aorp_icon_light', '☀️' ) ); ?>" />
                            <input type="hidden" name="aorp_icon_dark" id="aorp_icon_dark" value="<?php echo esc_attr( get_option( 'aorp_icon_dark', '🌙' ) ); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Eigenes Icon hell</th>
                        <td>
                            <input type="hidden" name="aorp_icon_light_img" id="aorp_icon_light_img" value="<?php echo esc_attr( $light_img ); ?>" />
                            <button type="button" class="button aorp-image-upload">Bild auswählen</button>
                            <span class="aorp-image-preview"><?php echo $light_img ? wp_get_attachment_image( $light_img, array(32,32) ) : ''; ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Eigenes Icon dunkel</th>
                        <td>
                            <input type="hidden" name="aorp_icon_dark_img" id="aorp_icon_dark_img" value="<?php echo esc_attr( $dark_img ); ?>" />
                            <button type="button" class="button aorp-image-upload">Bild auswählen</button>
                            <span class="aorp-image-preview"><?php echo $dark_img ? wp_get_attachment_image( $dark_img, array(32,32) ) : ''; ?></span>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            <p>Empfohlene Größe: 32x32 PNG mit transparentem Hintergrund. Geeignete Icons gibt es z.B. auf <a href="https://www.flaticon.com" target="_blank">flaticon.com</a>.</p>
        </div>
        <?php
    }


    public function register_settings() {
        register_setting( 'aorp_settings', 'aorp_menu_columns', array( 'type' => 'integer', 'sanitize_callback' => 'absint', 'default' => 1 ) );
        register_setting( 'aorp_settings', 'aorp_size_number', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ) );
        register_setting( 'aorp_settings', 'aorp_size_title', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ) );
        register_setting( 'aorp_settings', 'aorp_size_desc', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ) );
        register_setting( 'aorp_settings', 'aorp_size_price', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ) );

        register_setting( 'aorp_dark', 'aorp_icon_set', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => 'default' ) );
        register_setting( 'aorp_dark', 'aorp_icon_light', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '☀️' ) );
        register_setting( 'aorp_dark', 'aorp_icon_dark', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '🌙' ) );
        register_setting( 'aorp_dark', 'aorp_icon_light_img', array( 'type' => 'integer', 'sanitize_callback' => 'absint', 'default' => 0 ) );
        register_setting( 'aorp_dark', 'aorp_icon_dark_img', array( 'type' => 'integer', 'sanitize_callback' => 'absint', 'default' => 0 ) );
    }

    private function render_history_table() {
        echo '<h2>Historie</h2><table class="widefat"><thead><tr><th>Aktion</th><th>Zeit</th><th>Benutzer</th><th>Format</th><th>Undo</th></tr></thead><tbody>';
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
        echo '</tbody></table>';
    }

    public function manage_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $categories = get_terms( array( 'taxonomy' => 'aorp_menu_category', 'hide_empty' => false ) );
        $items      = get_posts( array( 'post_type' => 'aorp_menu_item', 'numberposts' => -1 ) );
        $ingredients_posts = get_posts( array( 'post_type' => 'aorp_ingredient', 'numberposts' => -1 ) );

        $ingredients_list = array();
        foreach ( $ingredients_posts as $ing ) {
            $ingredients_list[] = array(
                'code' => get_post_meta( $ing->ID, '_aorp_ing_code', true ),
                'name' => $ing->post_title,
            );
        }
        usort( $ingredients_list, function ( $a, $b ) { return strcasecmp( $a['name'], $b['name'] ); } );

        $edit_item = isset( $_GET['edit'] ) ? intval( $_GET['edit'] ) : 0;
        $current   = $edit_item ? get_post( $edit_item ) : null;
        $edit_cat     = isset( $_GET['edit_cat'] ) ? intval( $_GET['edit_cat'] ) : 0;
        $current_cat  = $edit_cat ? get_term( $edit_cat, 'aorp_menu_category' ) : null;
        $edit_ing     = isset( $_GET['edit_ing'] ) ? intval( $_GET['edit_ing'] ) : 0;
        $current_ing  = $edit_ing ? get_post( $edit_ing ) : null;
        ?>
        <div class="wrap">
            <h1>Speisekarte Verwaltung</h1>
            <?php $this->render_category_form( $categories, $current_cat ); ?>
            <?php $this->render_ingredient_form( $ingredients_posts, $current_ing ); ?>
            <?php $this->render_item_form( $items, $categories, $ingredients_list, $current ); ?>
        </div>
        <?php
    }

    public function export_csv() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nicht erlaubt' );
        }
        $format = isset( $_POST['export_format'] ) ? sanitize_text_field( $_POST['export_format'] ) : 'csv';
        $data   = $this->get_export_data();
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
            fputcsv( $out, array( 'Nummer', 'Titel', 'Beschreibung', 'Preis', 'Kategorie', 'Inhaltsstoffe', 'Bild-ID' ) );
            foreach ( $data['items'] as $row ) {
                fputcsv( $out, array( $row['number'], $row['title'], $row['description'], $row['price'], $row['category'], $row['ingredients'], $row['image_id'] ) );
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
            $data   = array();
            if ( 'json' === $format ) {
                $json = file_get_contents( $_FILES['import_file']['tmp_name'] );
                $data = json_decode( $json, true );
                if ( json_last_error() !== JSON_ERROR_NONE ) {
                    set_transient( 'aorp_import_msg', array( 'type' => 'notice-error', 'text' => 'Ungültiges JSON: ' . json_last_error_msg() ), 30 );
                    wp_redirect( admin_url( 'admin.php?page=aorp_export' ) );
                    exit;
                }
            } elseif ( 'yaml' === $format ) {
                $yaml = file_get_contents( $_FILES['import_file']['tmp_name'] );
                $data = $this->yaml_to_array( $yaml );
                if ( ! is_array( $data ) || empty( $data ) ) {
                    set_transient( 'aorp_import_msg', array( 'type' => 'notice-error', 'text' => 'YAML konnte nicht gelesen werden.' ), 30 );
                    wp_redirect( admin_url( 'admin.php?page=aorp_export' ) );
                    exit;
                }
            } else {
                $handle = fopen( $_FILES['import_file']['tmp_name'], 'r' );
                if ( $handle ) {
                    $header = fgetcsv( $handle );
                    while ( ( $row = fgetcsv( $handle ) ) !== false ) {
                        $data[] = array(
                            'number'      => $row[0],
                            'title'       => $row[1],
                            'description' => $row[2],
                            'price'       => $row[3],
                            'category'    => isset( $row[4] ) ? $row[4] : '',
                            'ingredients' => isset( $row[5] ) ? $row[5] : '',
                            'image_id'    => isset( $row[6] ) ? $row[6] : '',
                        );
                    }
                    fclose( $handle );
                }
            }
            $ids = array();
            $items = array();
            $categories = array();
            $ingredients = array();
            if ( $format === 'json' || $format === 'yaml' ) {
                $categories  = isset( $data['categories'] ) ? $data['categories'] : array();
                $ingredients = isset( $data['ingredients'] ) ? $data['ingredients'] : array();
                $items       = isset( $data['items'] ) ? $data['items'] : (array) $data;
            } else {
                $items = $data;
            }

            $cat_map = array();
            foreach ( $categories as $cat ) {
                if ( empty( $cat['name'] ) ) {
                    continue;
                }
                $existing = get_terms( array(
                    'taxonomy'   => 'aorp_menu_category',
                    'hide_empty' => false,
                    'name'       => $cat['name'],
                ) );
                if ( $existing && ! is_wp_error( $existing ) ) {
                    $term_id = $existing[0]->term_id;
                } else {
                    $term = wp_insert_term( sanitize_text_field( $cat['name'] ), 'aorp_menu_category' );
                    $term_id = is_wp_error( $term ) ? 0 : $term['term_id'];
                }
                if ( $term_id ) {
                    $fields = array(
                        'aorp_bg'        => 'bg',
                        'aorp_color'     => 'color',
                        'aorp_font_size' => 'font_size',
                        'aorp_width'     => 'width',
                        'aorp_height'    => 'height',
                    );
                    foreach ( $fields as $meta => $src ) {
                        if ( isset( $cat[ $src ] ) ) {
                            update_term_meta( $term_id, $meta, sanitize_text_field( $cat[ $src ] ) );
                        }
                    }
                    $cat_map[ $cat['name'] ] = $term_id;
                }
            }

            foreach ( $ingredients as $ing ) {
                if ( empty( $ing['code'] ) ) {
                    continue;
                }
                $existing = get_posts( array(
                    'post_type'  => 'aorp_ingredient',
                    'meta_key'   => '_aorp_ing_code',
                    'meta_value' => $ing['code'],
                    'numberposts'=> 1,
                ) );
                if ( $existing ) {
                    $ing_id = $existing[0]->ID;
                    wp_update_post( array( 'ID' => $ing_id, 'post_title' => sanitize_text_field( $ing['name'] ) ) );
                } else {
                    $ing_id = wp_insert_post( array(
                        'post_type'   => 'aorp_ingredient',
                        'post_status' => 'publish',
                        'post_title'  => sanitize_text_field( $ing['name'] ),
                    ) );
                }
                if ( $ing_id && ! is_wp_error( $ing_id ) ) {
                    update_post_meta( $ing_id, '_aorp_ing_code', sanitize_text_field( $ing['code'] ) );
                }
            }

            if ( is_array( $items ) ) {
                foreach ( $items as $row ) {
                    $post_id = wp_insert_post( array(
                        'post_type'   => 'aorp_menu_item',
                        'post_title'  => sanitize_text_field( $row['title'] ),
                        'post_content'=> sanitize_textarea_field( $row['description'] ),
                        'post_status' => 'publish',
                    ) );
                    if ( $post_id ) {
                        if ( isset( $row['category'] ) && isset( $cat_map[ $row['category'] ] ) ) {
                            wp_set_object_terms( $post_id, intval( $cat_map[ $row['category'] ] ), 'aorp_menu_category' );
                        }
                        update_post_meta( $post_id, '_aorp_number', sanitize_text_field( $row['number'] ) );
                        update_post_meta( $post_id, '_aorp_price', sanitize_text_field( $row['price'] ) );
                        if ( isset( $row['ingredients'] ) ) {
                            update_post_meta( $post_id, '_aorp_ingredients', sanitize_textarea_field( $row['ingredients'] ) );
                        }
                        if ( ! empty( $row['image_id'] ) ) {
                            set_post_thumbnail( $post_id, intval( $row['image_id'] ) );
                        }
                        $ids[] = $post_id;
                    }
                }
            }
            $history = get_option( 'aorp_history', array() );
            $history[] = array( 'action' => 'import', 'time' => current_time( 'mysql' ), 'user' => get_current_user_id(), 'ids' => $ids, 'format' => $format );
            update_option( 'aorp_history', $history );
            set_transient( 'aorp_import_msg', array( 'type' => 'notice-success', 'text' => 'Import abgeschlossen: ' . count( $ids ) . ' Einträge' ), 30 );
        } else {
            set_transient( 'aorp_import_msg', array( 'type' => 'notice-error', 'text' => 'Keine Datei übermittelt.' ), 30 );
        }
        wp_redirect( admin_url( 'admin.php?page=aorp_export' ) );
        exit;
    }

    public function enqueue_assets() {
        if ( ! is_admin() ) {
            wp_enqueue_style( 'aorp-style', plugin_dir_url( __FILE__ ) . 'assets/style.css' );
            wp_enqueue_script( 'aorp-script', plugin_dir_url( __FILE__ ) . 'assets/script.js', array('jquery'), false, true );
            wp_localize_script( 'aorp-script', 'aorp_ajax', array(
                'url'        => admin_url( 'admin-ajax.php' ),
                'icon_light' => $this->get_icon_html( 'light' ),
                'icon_dark'  => $this->get_icon_html( 'dark' ),
            ) );
        }
    }

    public function output_custom_styles() {
        $num   = trim( get_option( 'aorp_size_number', '' ) );
        $title = trim( get_option( 'aorp_size_title', '' ) );
        $desc  = trim( get_option( 'aorp_size_desc', '' ) );
        $price = trim( get_option( 'aorp_size_price', '' ) );
        if ( $num || $title || $desc || $price ) {
            echo '<style type="text/css">';
            if ( $num ) {
                echo '.aorp-number{font-size:' . esc_attr( $num ) . ';}';
            }
            if ( $title ) {
                echo '.aorp-title{font-size:' . esc_attr( $title ) . ';}';
            }
            if ( $desc ) {
                echo '.aorp-desc{font-size:' . esc_attr( $desc ) . ';}';
            }
            if ( $price ) {
                echo '.aorp-price{font-size:' . esc_attr( $price ) . ';}';
            }
            echo '</style>';
        }
    }

    public function admin_assets( $hook ) {
        if ( strpos( $hook, 'aorp' ) !== false ) {
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_script( 'wp-color-picker' );
            wp_enqueue_media();
            wp_enqueue_style( 'aorp-admin-style', plugin_dir_url( __FILE__ ) . 'assets/admin.css' );
            wp_enqueue_script( 'aorp-admin', plugin_dir_url( __FILE__ ) . 'assets/admin.js', array( 'jquery' ), false, true );
            if ( isset( $_GET['page'] ) && $_GET['page'] === 'aorp_settings' ) {
                wp_enqueue_style( 'aorp-style', plugin_dir_url( __FILE__ ) . 'assets/style.css' );
            }
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

    public function admin_notices() {
        $msg = get_transient( 'aorp_import_msg' );
        if ( $msg ) {
            $type = isset( $msg['type'] ) ? $msg['type'] : 'notice-success';
            printf( '<div class="%s notice is-dismissible"><p>%s</p></div>', esc_attr( $type ), esc_html( $msg['text'] ) );
            delete_transient( 'aorp_import_msg' );
        }
    }

    private function get_export_data() {
        $data = array(
            'categories'  => array(),
            'ingredients' => array(),
            'items'       => array(),
        );

        $cats = get_terms( array( 'taxonomy' => 'aorp_menu_category', 'hide_empty' => false ) );
        foreach ( $cats as $cat ) {
            $data['categories'][] = array(
                'name'       => $cat->name,
                'bg'         => get_term_meta( $cat->term_id, 'aorp_bg', true ),
                'color'      => get_term_meta( $cat->term_id, 'aorp_color', true ),
                'font_size'  => get_term_meta( $cat->term_id, 'aorp_font_size', true ),
                'width'      => get_term_meta( $cat->term_id, 'aorp_width', true ),
                'height'     => get_term_meta( $cat->term_id, 'aorp_height', true ),
            );
        }

        $ings = get_posts( array( 'post_type' => 'aorp_ingredient', 'numberposts' => -1 ) );
        foreach ( $ings as $ing ) {
            $data['ingredients'][] = array(
                'code' => get_post_meta( $ing->ID, '_aorp_ing_code', true ),
                'name' => $ing->post_title,
            );
        }

        $items = get_posts( array( 'post_type' => 'aorp_menu_item', 'numberposts' => -1 ) );
        foreach ( $items as $item ) {
            $term  = get_the_terms( $item->ID, 'aorp_menu_category' );
            $cat   = '';
            if ( $term && ! is_wp_error( $term ) ) {
                $cat = $term[0]->name;
            }
            $data['items'][] = array(
                'number'      => get_post_meta( $item->ID, '_aorp_number', true ),
                'title'       => $item->post_title,
                'description' => $item->post_content,
                'price'       => get_post_meta( $item->ID, '_aorp_price', true ),
                'category'    => $cat,
                'ingredients' => get_post_meta( $item->ID, '_aorp_ingredients', true ),
                'image_id'    => get_post_thumbnail_id( $item->ID ),
            );
        }

        return $data;
    }

    private function array_to_yaml( $data, $indent = 0 ) {
        if ( function_exists( 'yaml_emit' ) ) {
            return yaml_emit( $data );
        }

        $yaml   = '';
        $prefix = str_repeat( '  ', $indent );
        $is_assoc = array_keys( $data ) !== range( 0, count( $data ) - 1 );

        if ( $is_assoc ) {
            foreach ( $data as $key => $value ) {
                if ( is_array( $value ) ) {
                    $yaml .= $prefix . $key . ":\n" . $this->array_to_yaml( $value, $indent + 1 );
                } else {
                    $yaml .= $prefix . $key . ': ' . str_replace( "\n", '\\n', $value ) . "\n";
                }
            }
        } else {
            foreach ( $data as $value ) {
                if ( is_array( $value ) ) {
                    $yaml .= $prefix . "-\n" . $this->array_to_yaml( $value, $indent + 1 );
                } else {
                    $yaml .= $prefix . '- ' . str_replace( "\n", '\\n', $value ) . "\n";
                }
            }
        }

        return $yaml;
    }

    private function yaml_to_array( $text ) {
        if ( function_exists( 'yaml_parse' ) ) {
            return yaml_parse( $text );
        }

        $data    = array();
        $section = null;
        $current = array();

        foreach ( preg_split( '/\r?\n/', $text ) as $line ) {
            if ( '' === trim( $line ) ) {
                continue;
            }

            if ( ':' === substr( rtrim( $line ), -1 ) && ' ' !== $line[0] ) {
                if ( $current && $section ) {
                    $data[ $section ][] = $current;
                    $current           = array();
                }
                $section       = rtrim( $line, ':' );
                $data[ $section ] = array();
            } elseif ( preg_match( '/^\s*-\s*(.*)/', $line, $m ) ) {
                if ( $current && $section ) {
                    $data[ $section ][] = $current;
                }
                $current = array();
                $inline  = trim( $m[1] );
                if ( '' !== $inline ) {
                    list( $k, $v ) = array_map( 'trim', explode( ':', $inline, 2 ) );
                    $v = str_replace( '\\n', "\n", $v );
                    $v = $this->trim_quotes( $v );
                    $current[ $k ] = $v;
                }
            } else {
                list( $k, $v ) = array_map( 'trim', explode( ':', $line, 2 ) );
                $v = str_replace( '\\n', "\n", $v );
                $v = $this->trim_quotes( $v );
                $current[ $k ] = $v;
            }
        }

        if ( $current && $section ) {
            $data[ $section ][] = $current;
        }

        return $data;
    }

    private function trim_quotes( $value ) {
        if ( is_string( $value ) && strlen( $value ) > 1 ) {
            if ( ( $value[0] === '"' && substr( $value, -1 ) === '"' ) || ( $value[0] === "'" && substr( $value, -1 ) === "'" ) ) {
                return substr( $value, 1, -1 );
            }
        }
        return $value;
    }

    private $ingredient_lookup = null;

    private function get_ingredient_labels( $codes ) {
        $codes = array_filter( array_map( 'trim', explode( ',', $codes ) ) );
        if ( empty( $codes ) ) {
            return '';
        }
        if ( null === $this->ingredient_lookup ) {
            $this->ingredient_lookup = array();
            $ings = get_posts( array( 'post_type' => 'aorp_ingredient', 'numberposts' => -1 ) );
            foreach ( $ings as $ing ) {
                $code = get_post_meta( $ing->ID, '_aorp_ing_code', true );
                $this->ingredient_lookup[ $code ] = $ing->post_title;
            }
        }
        $labels = array();
        foreach ( $codes as $code ) {
            if ( isset( $this->ingredient_lookup[ $code ] ) ) {
                $labels[] = $this->ingredient_lookup[ $code ] . ' (' . $code . ')';
            } else {
                $labels[] = $code;
            }
        }
        return implode( ', ', $labels );
    }

    private function get_ingredient_names( $codes ) {
        $codes = array_filter( array_map( 'trim', explode( ',', $codes ) ) );
        if ( empty( $codes ) ) {
            return '';
        }
        if ( null === $this->ingredient_lookup ) {
            $this->ingredient_lookup = array();
            $ings = get_posts( array( 'post_type' => 'aorp_ingredient', 'numberposts' => -1 ) );
            foreach ( $ings as $ing ) {
                $code = get_post_meta( $ing->ID, '_aorp_ing_code', true );
                $this->ingredient_lookup[ $code ] = $ing->post_title;
            }
        }
        $labels = array();
        foreach ( $codes as $code ) {
            if ( isset( $this->ingredient_lookup[ $code ] ) ) {
                $labels[] = $this->ingredient_lookup[ $code ];
            } else {
                $labels[] = $code;
            }
        }
        return implode( ', ', $labels );
    }

    private function format_price( $price ) {
        $price = trim( (string) $price );
        if ( '' === $price ) {
            return '';
        }
        if ( strpos( $price, '€' ) === false ) {
            $price .= ' €';
        }
        return $price;
    }

    public function add_category() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nicht erlaubt' );
        }
        check_admin_referer( 'aorp_add_category' );
        if ( ! empty( $_POST['cat_name'] ) ) {
            wp_insert_term( sanitize_text_field( $_POST['cat_name'] ), 'aorp_menu_category' );
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

    public function bulk_delete_category() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nicht erlaubt' );
        }
        check_admin_referer( 'aorp_bulk_delete_category' );
        if ( ! empty( $_POST['cat_ids'] ) && is_array( $_POST['cat_ids'] ) ) {
            foreach ( $_POST['cat_ids'] as $id ) {
                wp_delete_term( intval( $id ), 'aorp_menu_category' );
            }
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

    public function bulk_delete_item() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nicht erlaubt' );
        }
        check_admin_referer( 'aorp_bulk_delete_item' );
        if ( ! empty( $_POST['item_ids'] ) && is_array( $_POST['item_ids'] ) ) {
            foreach ( $_POST['item_ids'] as $id ) {
                wp_delete_post( intval( $id ), true );
            }
        }
        wp_redirect( admin_url( 'admin.php?page=aorp_manage' ) );
        exit;
    }

    public function add_ingredient() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nicht erlaubt' );
        }
        check_admin_referer( 'aorp_add_ingredient' );
        $post_id = wp_insert_post( array(
            'post_type'   => 'aorp_ingredient',
            'post_status' => 'publish',
            'post_title'  => sanitize_text_field( $_POST['ing_name'] )
        ) );
        if ( $post_id && isset( $_POST['ing_code'] ) ) {
            update_post_meta( $post_id, '_aorp_ing_code', sanitize_text_field( $_POST['ing_code'] ) );
        }
        wp_redirect( admin_url( 'admin.php?page=aorp_manage' ) );
        exit;
    }

    public function update_ingredient() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nicht erlaubt' );
        }
        $ing_id = intval( $_POST['ing_id'] );
        check_admin_referer( 'aorp_edit_ingredient_' . $ing_id );
        if ( $ing_id && ! empty( $_POST['ing_name'] ) ) {
            wp_update_post( array( 'ID' => $ing_id, 'post_title' => sanitize_text_field( $_POST['ing_name'] ) ) );
            if ( isset( $_POST['ing_code'] ) ) {
                update_post_meta( $ing_id, '_aorp_ing_code', sanitize_text_field( $_POST['ing_code'] ) );
            }
        }
        wp_redirect( admin_url( 'admin.php?page=aorp_manage' ) );
        exit;
    }

    public function delete_ingredient() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nicht erlaubt' );
        }
        $ing_id = isset( $_GET['ing_id'] ) ? intval( $_GET['ing_id'] ) : 0;
        check_admin_referer( 'aorp_delete_ingredient_' . $ing_id );
        if ( $ing_id ) {
            wp_delete_post( $ing_id, true );
        }
        wp_redirect( admin_url( 'admin.php?page=aorp_manage' ) );
        exit;
    }

    public function bulk_delete_ingredient() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nicht erlaubt' );
        }
        check_admin_referer( 'aorp_bulk_delete_ingredient' );
        if ( ! empty( $_POST['ing_ids'] ) && is_array( $_POST['ing_ids'] ) ) {
            foreach ( $_POST['ing_ids'] as $id ) {
                wp_delete_post( intval( $id ), true );
            }
        }
        wp_redirect( admin_url( 'admin.php?page=aorp_manage' ) );
        exit;
    }


    public function history_page() {
        echo '<div class="wrap"><h1>Import/Export Historie</h1>';
        $this->render_history_table();
        echo '</div>';
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
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpgmo-template-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpgmo-meta-box.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-grid-menu-overlay.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/maps.php';

function aio_restaurant_activate() {
    $templates = is_multisite()
        ? get_site_option( 'wpgmo_templates_network', array() )
        : get_option( 'wpgmo_templates', array() );
    if ( empty( $templates ) ) {
        $templates['beispiel'] = array(
            'label'  => 'Beispiel Grid',
            'layout' => array(
                array(
                    array( 'id' => 'cell1', 'size' => 'large' ),
                    array( 'id' => 'cell2', 'size' => 'large' ),
                ),
            ),
        );
        if ( is_multisite() ) {
            update_site_option( 'wpgmo_templates_network', $templates );
            update_site_option( 'wpgmo_default_template_network', 'beispiel' );
        } else {
            update_option( 'wpgmo_templates', $templates );
            update_option( 'wpgmo_default_template', 'beispiel' );
        }
    }
}

register_activation_hook( __FILE__, 'aio_restaurant_activate' );

WPGMO_Template_Manager::instance();
WPGMO_Meta_Box::instance();
WP_Grid_Menu_Overlay::instance();

new AIO_Restaurant_Plugin();
