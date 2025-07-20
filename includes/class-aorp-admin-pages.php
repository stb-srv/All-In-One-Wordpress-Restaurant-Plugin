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
    private $settings_hook = '';

    /**
     * Register admin menu.
     */
    public function register(): void {
        // Run late so we can remove menus added by other components
        add_action( 'admin_menu', array( $this, 'menu' ), 50 );
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

        // Replace automatic submenu with a dedicated Dashboard entry
        remove_submenu_page( 'aio-restaurant', 'aio-restaurant' );
        add_submenu_page(
            'aio-restaurant',
            __( 'Dashboard', 'aorp' ),
            __( 'Dashboard', 'aorp' ),
            'manage_options',
            'aio-restaurant',
            array( $this, 'render_dashboard' )
        );

        add_submenu_page(
            'aio-restaurant',
            __( 'Speisenverwaltung', 'aorp' ),
            __( 'Speisenverwaltung', 'aorp' ),
            'manage_options',
            'aio-dishes',
            array( $this, 'render_food_page' )
        );

        add_submenu_page(
            'aio-restaurant',
            __( 'Getränkeverwaltung', 'aorp' ),
            __( 'Getränkeverwaltung', 'aorp' ),
            'manage_options',
            'aio-drinks',
            array( $this, 'render_drink_page' )
        );

        add_submenu_page(
            'aio-restaurant',
            __( 'Kategorien & Inhaltsstoffe', 'aorp' ),
            __( 'Kategorien & Inhaltsstoffe', 'aorp' ),
            'manage_options',
            'aio-categories',
            array( $this, 'render_categories_page' )
        );

        $this->settings_hook = add_submenu_page(
            'aio-restaurant',
            __( 'Einstellungen & Layouts', 'aorp' ),
            __( 'Einstellungen & Layouts', 'aorp' ),
            'manage_options',
            'aio-settings-layouts',
            array( $this, 'render_settings_layouts_page' )
        );

        // Remove old duplicate or unused submenus from legacy versions
        $old = array(
            'aio-add-dish',
            'aio-dish-categories',
            'aio-add-drink',
            'aio-drink-categories',
            'aio-grid-templates',
            'aio-overlay-builder',
            'aio-layout-preview',
            'aio-import-export',
            'aio-pdf-export',
            'aio-rest-api',
            'aio-settings',
            'aio-darkmode',
            'aio-features',
            'wpgmo-templates',
        );
        foreach ( $old as $slug ) {
            remove_submenu_page( 'aio-restaurant', $slug );
        }
        remove_submenu_page( 'wpgmo-templates', 'wpgmo-overview' );
        remove_menu_page( 'aio_leaflet_map' );
    }

    public function render_drink_page(): void {
        $this->render_manage_page( 'drinks' );
    }

    public function render_food_page(): void {
        $this->render_manage_page( 'foods' );
    }

    public function render_settings_layouts_page(): void {
        $tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'settings';

        if ( 'settings' === $tab ) {
            $settings = new AORP_Settings();
            $settings->render_settings_page();
            return;
        }

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'Einstellungen & Layouts', 'aorp' ) . '</h1>';
        echo '<h2 class="nav-tab-wrapper">';
        $tabs = array(
            'settings' => __( 'Allgemeine Einstellungen', 'aorp' ),
            'import'   => __( 'Import/Export', 'aorp' ),
            'layouts'  => __( 'AIO-Karten Layouts', 'aorp' ),
        );
        foreach ( $tabs as $key => $label ) {
            $url = add_query_arg( array( 'page' => 'aio-settings-layouts', 'tab' => $key ) );
            $active = ( $tab === $key ) ? ' nav-tab-active' : '';
            echo '<a href="' . esc_url( $url ) . '" class="nav-tab' . $active . '">' . esc_html( $label ) . '</a>';
        }
        echo '</h2>';

        if ( 'import' === $tab ) {
            $this->render_import_export_page();
        } else {
            // layouts
            wp_enqueue_style( 'wpgmo-gb-css', plugin_dir_url( __FILE__ ) . '../assets/css/wpgmo-grid-builder.css' );
            wp_enqueue_script( 'wpgmo-gb-js', plugin_dir_url( __FILE__ ) . '../assets/js/admin/wpgmo-grid-builder.js', array( 'jquery' ), false, true );
            if ( class_exists( 'WPGMO_Template_Manager' ) ) {
                ob_start();
                \WPGMO_Template_Manager::instance()->render_page();
                echo ob_get_clean();
            }
        }

        echo '</div>';
    }

    public function render_categories_page(): void {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'Kategorien & Inhaltsstoffe', 'aorp' ) . '</h1>';
        echo '<h2>' . esc_html__( 'Speisekategorien', 'aorp' ) . '</h2>';
        $this->render_categories( 'aorp_menu_category' );
        echo '<hr />';
        echo '<h2>' . esc_html__( 'Getränkekategorien', 'aorp' ) . '</h2>';
        $this->render_categories( 'aorp_drink_category' );
        echo '<hr />';
        echo '<h2>' . esc_html__( 'Inhaltsstoffe', 'aorp' ) . '</h2>';
        $this->render_ingredients();
        echo '</div>';
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
        $view    = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'list';
        $orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'title';
        $order   = isset( $_GET['order'] ) && 'DESC' === strtoupper( $_GET['order'] ) ? 'DESC' : 'ASC';

        $post_type = ( 'drinks' === $tab ) ? 'aorp_drink_item' : 'aorp_menu_item';
        $page_slug = ( 'drinks' === $tab ) ? 'aio-drinks' : 'aio-dishes';

        echo '<div class="wrap">';
        echo '<h1>' . ( 'drinks' === $tab ? __( 'Getränkekarte', 'aorp' ) : __( 'Speisekarte', 'aorp' ) ) . '</h1>';
        echo '<h2 class="nav-tab-wrapper">';
        $tabs = array(
            'list' => __( 'Liste', 'aorp' ),
            'add'  => __( 'Neu hinzufügen', 'aorp' ),
        );
        foreach ( $tabs as $key => $label ) {
            $url = add_query_arg( array( 'page' => $page_slug, 'tab' => $key ), admin_url( 'admin.php' ) );
            $active = ( $view === $key ) ? ' nav-tab-active' : '';
            echo '<a href="' . esc_url( $url ) . '" class="nav-tab' . $active . '">' . esc_html( $label ) . '</a>';
        }
        echo '</h2>';

        $cats = get_terms(
            ( 'drinks' === $tab ) ? 'aorp_drink_category' : 'aorp_menu_category',
            array(
                'hide_empty' => false,
                'orderby'    => 'name',
                'order'      => 'ASC',
            )
        );
        $ings = get_posts( array( 'post_type' => 'aorp_ingredient', 'numberposts' => -1 ) );

        if ( 'add' === $view ) {
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
            echo '<p><button type="submit" class="button button-primary">' . __( 'Hinzufügen', 'aorp' ) . '</button></p>';
            echo '</form>';
            echo '</div>';
            return;
        }

        $items = get_posts( array( 'post_type' => $post_type, 'numberposts' => -1, 'orderby' => $orderby, 'order' => $order ) );

        $mode = ( 'drinks' === $tab ) ? 'drink' : 'food';
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

