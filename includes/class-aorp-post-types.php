<?php
namespace AIO_Restaurant_Plugin;

use function add_action;
use function register_post_type;
use function register_taxonomy;
use function term_exists;
use function wp_insert_term;

/**
 * Registers custom post types and taxonomies.
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
            )
        );
    }

    /**
     * Drink post type.
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
            )
        );
    }

    /**
     * Ingredient post type.
     */
    public function register_ingredient_post_type(): void {
        register_post_type(
            'aorp_ingredient',
            array(
                'labels'   => array(
                    'name'          => __( 'Inhaltsstoffe', 'aorp' ),
                    'singular_name' => __( 'Inhaltsstoff', 'aorp' ),
                ),
                'public'   => false,
                'show_ui'  => false,
                'supports' => array( 'title' ),
            )
        );
    }

    /**
     * Contact message post type.
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
     */
    public function register_taxonomies(): void {
        register_taxonomy(
            'aorp_menu_category',
            'aorp_menu_item',
            array(
                'labels'            => array(
                    'name'          => __( 'Kategorien', 'aorp' ),
                    'singular_name' => __( 'Kategorie', 'aorp' ),
                ),
                'hierarchical'      => true,
                'show_admin_column' => true,
                'show_ui'           => false,
            )
        );

        register_taxonomy(
            'aorp_drink_category',
            'aorp_drink_item',
            array(
                'labels'            => array(
                    'name'          => __( 'Getränke Kategorien', 'aorp' ),
                    'singular_name' => __( 'Getränke Kategorie', 'aorp' ),
                ),
                'hierarchical'      => true,
                'show_admin_column' => true,
                'show_ui'           => true,
            )
        );

        if ( ! term_exists( $this->default_drink_cat_slug, 'aorp_drink_category' ) ) {
            wp_insert_term( 'Kategorielos', 'aorp_drink_category', array( 'slug' => $this->default_drink_cat_slug ) );
        }
    }
}
