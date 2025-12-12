<?php
namespace AIO_Restaurant_Plugin;

use function add_action;
use function register_post_type;
use function register_taxonomy;
use function term_exists;
use function wp_insert_term;

/**
 * Registers custom post types and taxonomies.
 *
 * @package AIO_Restaurant_Plugin
 * @since 1.0.0
 */
class AORP_Post_Types {
    /**
     * Default drink category slug.
     *
     * @var string
     */
    private string $default_drink_cat_slug = 'kategorielos';

    /**
     * Register actions.
     *
     * @return void
     */
    public function register(): void {
        add_action( 'init', array( $this, 'register_food_post_type' ) );
        add_action( 'init', array( $this, 'register_drink_post_type' ) );
        add_action( 'init', array( $this, 'register_ingredient_post_type' ) );
        add_action( 'init', array( $this, 'register_contact_post_type' ) );
        add_action( 'init', array( $this, 'register_taxonomies' ) );
    }

    /**
     * Food post type.
     *
     * @return void
     */
    public function register_food_post_type(): void {
        register_post_type(
            'aorp_menu_item',
            array(
                'labels'      => array(
                    'name'          => __( 'Speisen', 'aorp' ),
                    'singular_name' => __( 'Speise', 'aorp' ),
                ),
                'public'      => false,
                'show_ui'     => false,
                'has_archive' => false,
                'supports'    => array( 'title', 'editor', 'thumbnail' ),
                'description' => __( 'Speisen für die Speisekarte', 'aorp' ),
            )
        );
    }

    /**
     * Drink post type.
     *
     * @return void
     */
    public function register_drink_post_type(): void {
        register_post_type(
            'aorp_drink_item',
            array(
                'labels'      => array(
                    'name'          => __( 'Getränke', 'aorp' ),
                    'singular_name' => __( 'Getränk', 'aorp' ),
                    'menu_name'     => __( 'Getränke-Karte', 'aorp' ),
                ),
                'public'      => false,
                'show_ui'     => false,
                'has_archive' => false,
                'supports'    => array( 'title', 'editor', 'thumbnail' ),
                'description' => __( 'Getränke für die Getränkekarte', 'aorp' ),
            )
        );
    }

    /**
     * Ingredient post type.
     *
     * @return void
     */
    public function register_ingredient_post_type(): void {
        register_post_type(
            'aorp_ingredient',
            array(
                'labels'      => array(
                    'name'          => __( 'Inhaltsstoffe', 'aorp' ),
                    'singular_name' => __( 'Inhaltsstoff', 'aorp' ),
                ),
                'public'      => false,
                'show_ui'     => false,
                'supports'    => array( 'title' ),
                'description' => __( 'Inhaltsstoffe/Allergene für Speisen und Getränke', 'aorp' ),
            )
        );
    }

    /**
     * Contact message post type.
     *
     * @return void
     */
    public function register_contact_post_type(): void {
        register_post_type(
            'aorp_contact_message',
            array(
                'labels'   => array(
                    'name'          => __( 'Kontaktnachrichten', 'aorp' ),
                    'singular_name' => __( 'Kontaktnachricht', 'aorp' ),
                ),
                'public'   => false,
                'show_ui'  => false,
                'supports' => array( 'title', 'editor' ),
            )
        );
    }

    /**
     * Taxonomies for menu items and drinks.
     * WICHTIG: Separate Taxonomien für Speisen und Getränke!
     *
     * @return void
     */
    public function register_taxonomies(): void {
        // SPEISEN-KATEGORIEN (nur für Speisen)
        register_taxonomy(
            'aorp_menu_category',
            'aorp_menu_item',
            array(
                'labels'            => array(
                    'name'              => __( 'Speise-Kategorien', 'aorp' ),
                    'singular_name'     => __( 'Speise-Kategorie', 'aorp' ),
                    'add_new_item'      => __( 'Neue Speise-Kategorie hinzufügen', 'aorp' ),
                    'edit_item'         => __( 'Speise-Kategorie bearbeiten', 'aorp' ),
                    'menu_name'         => __( 'Speise-Kategorien', 'aorp' ),
                ),
                'hierarchical'      => true,
                'show_admin_column' => true,
                'show_ui'           => false,
                'description'       => __( 'Kategorien nur für die Speisekarte (z.B. Vorspeisen, Hauptgerichte, Desserts). Erscheinen nur im [speisekarte] Shortcode.', 'aorp' ),
            )
        );

        // GETRÄNKE-KATEGORIEN (nur für Getränke)
        register_taxonomy(
            'aorp_drink_category',
            'aorp_drink_item',
            array(
                'labels'            => array(
                    'name'              => __( 'Getränke-Kategorien', 'aorp' ),
                    'singular_name'     => __( 'Getränke-Kategorie', 'aorp' ),
                    'add_new_item'      => __( 'Neue Getränke-Kategorie hinzufügen', 'aorp' ),
                    'edit_item'         => __( 'Getränke-Kategorie bearbeiten', 'aorp' ),
                    'menu_name'         => __( 'Getränke-Kategorien', 'aorp' ),
                ),
                'hierarchical'      => true,
                'show_admin_column' => true,
                'show_ui'           => true,
                'description'       => __( 'Kategorien nur für die Getränkekarte (z.B. Softdrinks, Bier, Wein). Erscheinen nur im [getraenkekarte] Shortcode.', 'aorp' ),
            )
        );

        // Standard-Getränke-Kategorie erstellen falls nicht vorhanden
        if ( ! term_exists( $this->default_drink_cat_slug, 'aorp_drink_category' ) ) {
            wp_insert_term(
                'Kategorielos',
                'aorp_drink_category',
                array(
                    'slug'        => $this->default_drink_cat_slug,
                    'description' => __( 'Standard-Kategorie für Getränke ohne Zuordnung', 'aorp' ),
                )
            );
        }
    }
}
