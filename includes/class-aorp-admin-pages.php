<?php
namespace AIO_Restaurant_Plugin;

use WP_List_Table;
use AIO_Restaurant_Plugin\AORP_Settings;

/**
 * Handles admin pages.
 */
use function AIO_Restaurant_Plugin\format_price;
use function AIO_Restaurant_Plugin\ingredient_labels;

class AORP_Admin_Pages {
    /**
     * Register admin menu.
     */
    public function register(): void {
        add_action( 'admin_menu', array( $this, 'menu' ) );
    }

    /**
     * Add top level menu.
     */
    public function menu(): void {
        add_menu_page(
            'AIO-Restaurant',
            'AIO-Restaurant',
            'manage_options',
            'aio-restaurant',
            array( $this, 'render_dashboard' ),
            'dashicons-store',
            26
        );

        add_submenu_page(
            'aio-restaurant',
            'Speisen anzeigen',
            "\xF0\x9F\x8D\xBD Speisen anzeigen",
            'manage_options',
            'aio-dishes',
            array( $this, 'render_food_page' )
        );

        add_submenu_page(
            'aio-restaurant',
            'Neue Speise hinzufügen',
            "\xE2\x9E\x95 Neue Speise",
            'manage_options',
            'aio-add-dish',
            array( $this, 'render_food_page' )
        );

        add_submenu_page(
            'aio-restaurant',
            'Kategorien & Inhaltsstoffe',
            "\xF0\x9F\x8F\xB7\xEF\xB8\x8F Kategorien & Inhaltsstoffe",
            'manage_options',
            'aio-dish-categories',
            array( $this, 'render_food_page' )
        );

        add_submenu_page(
            'aio-restaurant',
            'Getränke anzeigen',
            "\xF0\x9F\xA5\xA4 Getränke anzeigen",
            'manage_options',
            'aio-drinks',
            array( $this, 'render_drink_page' )
        );

        add_submenu_page(
            'aio-restaurant',
            'Neues Getränk hinzufügen',
            "\xE2\x9E\x95 Neues Getränk",
            'manage_options',
            'aio-add-drink',
            array( $this, 'render_drink_page' )
        );

        add_submenu_page(
            'aio-restaurant',
            'Getränkekategorien',
            "\xF0\x9F\x8F\xB7\xEF\xB8\x8F Getränkekategorien",
            'manage_options',
            'aio-drink-categories',
            array( $this, 'render_drink_page' )
        );

        add_submenu_page(
            'aio-restaurant',
            'Grid Templates',
            "\xF0\x9F\x93\x90 Grid Templates",
            'manage_options',
            'aio-grid-templates',
            array( $this, 'render_grid_templates_page' )
        );

        add_submenu_page(
            'aio-restaurant',
            'Overlay Builder',
            "\xF0\x9F\xA7\xB1 Overlay Builder",
            'manage_options',
            'aio-overlay-builder',
            array( $this, 'render_overlay_builder_page' )
        );

        add_submenu_page(
            'aio-restaurant',
            'Layout Preview',
            "\xF0\x9F\x94\x8D Layout Preview",
            'manage_options',
            'aio-layout-preview',
            array( $this, 'render_layout_preview_page' )
        );

        add_submenu_page(
            'aio-restaurant',
            'CSV Import/Export',
            "\xE2\xAC\x86\xE2\xAC\x87 CSV Import/Export",
            'manage_options',
            'aio-import-export',
            array( $this, 'render_import_export_page' )
        );

        add_submenu_page(
            'aio-restaurant',
            'PDF Export',
            "\xF0\x9F\x93\x84 PDF Export",
            'manage_options',
            'aio-pdf-export',
            array( $this, 'render_pdf_export_page' )
        );

        add_submenu_page(
            'aio-restaurant',
            'REST API',
            "\xF0\x9F\x94\x97 REST API",
            'manage_options',
            'aio-rest-api',
            array( $this, 'render_rest_api_page' )
        );

        $settings = new AORP_Settings();
        add_submenu_page(
            'aio-restaurant',
            'Einstellungen',
            "\xE2\x9A\x99\xEF\xB8\x8F Einstellungen",
            'manage_options',
            'aio-settings',
            array( $settings, 'render_settings_page' )
        );

        add_submenu_page(
            'aio-restaurant',
            'Darkmode',
            "\xF0\x9F\x95\x83 Darkmode",
            'manage_options',
            'aio-darkmode',
            array( $this, 'render_darkmode_page' )
        );

        add_submenu_page(
            'aio-restaurant',
            'Enable/Disable Features',
            "\xE2\x9C\x85 Features",
            'manage_options',
            'aio-features',
            array( $this, 'render_features_page' )
        );
    }

    public function render_drink_page(): void {
        $this->render_manage_page( 'drinks' );
    }

    public function render_food_page(): void {
        $this->render_manage_page( 'foods' );
    }

    public function render_dashboard(): void {
        $foods  = wp_count_posts( 'aorp_menu_item' );
        $drinks = wp_count_posts( 'aorp_drink_item' );
        $ings   = wp_count_posts( 'aorp_ingredient' );
        echo '<div class="wrap">';
        echo '<h1>AIO-Restaurant</h1>';
        echo '<p>' . sprintf( __( 'Es gibt %1$d Speisen, %2$d Getränke und %3$d Inhaltsstoffe.', 'aorp' ), $foods->publish, $drinks->publish, $ings->publish ) . '</p>';
        echo '</div>';
    }

    public function render_import_export_page(): void {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Import/Export', 'aorp' ); ?></h1>
            <h2><?php esc_html_e( 'Import', 'aorp' ); ?></h2>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
                <?php wp_nonce_field( 'aorp_import_csv' ); ?>
                <input type="hidden" name="action" value="aorp_import_csv" />
                <p><input type="file" name="csv_file" accept=".csv" /></p>
                <?php submit_button( __( 'CSV Import', 'aorp' ) ); ?>
            </form>
            <p><button class="button button-primary" disabled>YAML Import</button> <button class="button button-primary" disabled>JSON Import</button></p>
            <hr />
            <h2><?php esc_html_e( 'Export', 'aorp' ); ?></h2>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'aorp_export_csv' ); ?>
                <input type="hidden" name="action" value="aorp_export_csv" />
                <?php submit_button( __( 'CSV Export', 'aorp' ) ); ?>
            </form>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'aorp_export_pdf' ); ?>
                <input type="hidden" name="action" value="aorp_export_pdf" />
                <?php submit_button( __( 'PDF Export', 'aorp' ) ); ?>
            </form>
            <p><button class="button button-primary" disabled>YAML Export</button> <button class="button button-primary" disabled>JSON Export</button></p>
        </div>
        <?php
    }

    public function render_grid_templates_page(): void {
        echo '<div class="wrap"><h1>Grid Templates</h1><p>Coming soon</p></div>';
    }

    public function render_overlay_builder_page(): void {
        echo '<div class="wrap"><h1>Overlay Builder</h1><p>Coming soon</p></div>';
    }

    public function render_layout_preview_page(): void {
        echo '<div class="wrap"><h1>Layout Preview</h1><p>Coming soon</p></div>';
    }

    public function render_pdf_export_page(): void {
        echo '<div class="wrap"><h1>PDF Export</h1><p>Coming soon</p></div>';
    }

    public function render_rest_api_page(): void {
        echo '<div class="wrap"><h1>REST API</h1><p>Coming soon</p></div>';
    }

    public function render_darkmode_page(): void {
        echo '<div class="wrap"><h1>Darkmode</h1><p>Coming soon</p></div>';
    }

    public function render_features_page(): void {
        echo '<div class="wrap"><h1>Features</h1><p>Coming soon</p></div>';
    }

    /**
     * Render manage page.
     */
    public function render_page(): void {
        $this->render_manage_page( 'foods' );
    }

    private function render_manage_page( string $tab ): void {
        $section = isset( $_GET['section'] ) ? sanitize_key( wp_unslash( $_GET['section'] ) ) : 'entries';
        $orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'title';
        $order   = isset( $_GET['order'] ) && 'DESC' === strtoupper( $_GET['order'] ) ? 'DESC' : 'ASC';

        $post_type = ( 'drinks' === $tab ) ? 'aorp_drink_item' : 'aorp_menu_item';
        $page_slug = ( 'drinks' === $tab ) ? 'aio-drinks' : 'aio-dishes';

        echo '<div class="wrap">';
        echo '<h1>' . ( 'drinks' === $tab ? __( 'Getränkekarte', 'aorp' ) : __( 'Speisekarte', 'aorp' ) ) . '</h1>';
        echo '<h2 class="nav-tab-wrapper">';
        $sections = array(
            'entries'     => __( 'Einträge', 'aorp' ),
            'categories'  => __( 'Kategorien', 'aorp' ),
            'ingredients' => __( 'Inhaltsstoffe', 'aorp' ),
        );
        foreach ( $sections as $key => $label ) {
            $url = add_query_arg( array( 'page' => $page_slug, 'section' => $key ), admin_url( 'admin.php' ) );
            $active = ( $section === $key ) ? ' nav-tab-active' : '';
            echo '<a href="' . esc_url( $url ) . '" class="nav-tab' . $active . '">' . esc_html( $label ) . '</a>';
        }
        echo '</h2>';

        if ( 'categories' === $section ) {
            $taxonomy = ( 'drinks' === $tab ) ? 'aorp_drink_category' : 'aorp_menu_category';
            $this->render_categories( $taxonomy );
            echo '</div>';
            return;
        }

        if ( 'ingredients' === $section ) {
            $this->render_ingredients();
            echo '</div>';
            return;
        }

        $items = get_posts( array( 'post_type' => $post_type, 'numberposts' => -1, 'orderby' => $orderby, 'order' => $order ) );
        $cats  = get_terms(
            ( 'drinks' === $tab ) ? 'aorp_drink_category' : 'aorp_menu_category',
            array(
                'hide_empty' => false,
                'orderby'    => 'name',
                'order'      => 'ASC',
            )
        );
        $ings  = get_posts( array( 'post_type' => 'aorp_ingredient', 'numberposts' => -1 ) );

        $nonce = wp_create_nonce( 'aorp_add_' . ( 'drinks' === $tab ? 'drink_item' : 'item' ) );
        echo '<form class="aorp-add-form" data-action="aorp_add_' . ( 'drinks' === $tab ? 'drink_item' : 'item' ) . '">';
        echo '<input type="hidden" name="nonce" value="' . esc_attr( $nonce ) . '" />';
        echo '<p><input type="text" name="item_title" placeholder="Name" required /></p>';
        echo '<p><textarea name="item_description" placeholder="Beschreibung"></textarea></p>';
        if ( 'foods' === $tab ) {
            echo '<p><input type="text" name="item_price" placeholder="Preis" /></p>';
            echo '<p><input type="text" name="item_number" placeholder="Nummer" /></p>';
        } else {
            echo '<p><textarea name="item_sizes" placeholder="Größe=Preis pro Zeile"></textarea></p>';
        }
        if ( $cats ) {
            echo '<p><select name="item_category"><option value="">Kategorie</option>';
            foreach ( $cats as $cat ) {
                echo '<option value="' . esc_attr( $cat->term_id ) . '">' . esc_html( $cat->name ) . '</option>';
            }
            echo '</select></p>';
        }
        echo '<p><select class="aorp-ing-select"><option value="">Inhaltsstoff wählen</option>';
        foreach ( $ings as $ing ) {
            $code = get_post_meta( $ing->ID, '_aorp_ing_code', true );
            echo '<option value="' . esc_attr( $code ) . '">' . esc_html( $ing->post_title ) . '</option>';
        }
        echo '</select></p>';
        echo '<div class="aorp-selected"></div>';
        echo '<input type="hidden" name="item_ingredients" class="aorp-ing-text" />';
        echo '<p><button class="button aorp-upload-image">' . __( 'Bild auswählen', 'aorp' ) . '</button> ';
        echo '<input type="hidden" name="item_image_id" class="aorp-image-id" />';
        echo '<span class="aorp-image-preview"></span></p>';
        echo '<p><button type="submit" class="button button-primary">Hinzufügen</button></p>';
        echo '</form>';

        $mode    = ( 'drinks' === $tab ) ? 'drink' : 'food';
        include dirname( __DIR__ ) . '/admin/item-list.php';

        echo '</div>';
    }

    private function render_categories( string $taxonomy ): void {
        if ( isset( $_POST['new_cat'] ) && check_admin_referer( 'aorp_add_cat' ) ) {
            wp_insert_term( sanitize_text_field( wp_unslash( $_POST['new_cat'] ) ), $taxonomy );
        }

        if ( isset( $_GET['delete_cat'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'aorp_delete_cat_' . intval( $_GET['delete_cat'] ) ) ) {
            wp_delete_term( intval( $_GET['delete_cat'] ), $taxonomy );
        }

        $terms = get_terms(
            $taxonomy,
            array(
                'hide_empty' => false,
                'orderby'    => 'name',
                'order'      => 'ASC',
            )
        );
        echo '<form method="post">';
        wp_nonce_field( 'aorp_add_cat' );
        echo '<p><input type="text" name="new_cat" /> ';
        submit_button( __( 'Hinzufügen', 'aorp' ), 'primary', 'submit', false );
        echo '</p></form>';
        if ( $terms ) {
            echo '<ul>';
            foreach ( $terms as $term ) {
                $del = '';
                if ( 'aorp_drink_category' !== $taxonomy || 'kategorielos' !== $term->slug ) {
                    $del = ' <a href="' . esc_url( wp_nonce_url( add_query_arg( array( 'delete_cat' => $term->term_id ) ), 'aorp_delete_cat_' . $term->term_id ) ) . '">x</a>';
                }
                echo '<li>' . esc_html( $term->name ) . $del . '</li>';
            }
            echo '</ul>';
        }
    }

    private function render_ingredients(): void {
        if ( isset( $_POST['new_ing'] ) && check_admin_referer( 'aorp_add_ing' ) ) {
            $id = wp_insert_post( array(
                'post_type'   => 'aorp_ingredient',
                'post_status' => 'publish',
                'post_title'  => sanitize_text_field( wp_unslash( $_POST['new_ing'] ) ),
            ) );
            if ( $id && ! empty( $_POST['new_code'] ) ) {
                update_post_meta( $id, '_aorp_ing_code', sanitize_text_field( wp_unslash( $_POST['new_code'] ) ) );
            }
        }

        if ( isset( $_GET['delete_ing'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'aorp_delete_ing_' . intval( $_GET['delete_ing'] ) ) ) {
            wp_delete_post( intval( $_GET['delete_ing'] ), true );
        }

        $ings = get_posts( array( 'post_type' => 'aorp_ingredient', 'numberposts' => -1 ) );
        echo '<form method="post">';
        wp_nonce_field( 'aorp_add_ing' );
        echo '<p><input type="text" name="new_ing" placeholder="Name" /> <input type="text" name="new_code" placeholder="Code" /> ';
        submit_button( __( 'Hinzufügen', 'aorp' ), 'primary', 'submit', false );
        echo '</p></form>';
        if ( $ings ) {
            echo '<ul>';
            foreach ( $ings as $ing ) {
                $code     = get_post_meta( $ing->ID, '_aorp_ing_code', true );
                $del_link = wp_nonce_url( add_query_arg( array( 'delete_ing' => $ing->ID ) ), 'aorp_delete_ing_' . $ing->ID );
                echo '<li>' . esc_html( $ing->post_title . ( $code ? ' (' . $code . ')' : '' ) ) . ' <a href="' . esc_url( $del_link ) . '">x</a></li>';
            }
            echo '</ul>';
        }
    }
}

