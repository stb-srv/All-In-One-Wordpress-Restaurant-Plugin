<?php
namespace AIO_Restaurant_Plugin;

/**
 * REST API endpoints for menu items.
 */
class AORP_REST_API {
    /**
     * Register REST routes.
     */
    public function register(): void {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Register routes.
     */
    public function register_routes(): void {
        register_rest_route( 'aorp/v1', '/foods', array(
            'methods'  => 'GET',
            'callback' => array( $this, 'get_foods' ),
        ) );

        register_rest_route( 'aorp/v1', '/drinks', array(
            'methods'  => 'GET',
            'callback' => array( $this, 'get_drinks' ),
        ) );
    }

    /**
     * Return foods.
     */
    public function get_foods( \WP_REST_Request $request ) {
        $posts = get_posts( array( 'post_type' => 'aorp_menu_item', 'numberposts' => -1 ) );
        $data  = array();
        foreach ( $posts as $post ) {
            $data[] = array(
                'id'    => $post->ID,
                'title' => $post->post_title,
                'content' => $post->post_content,
            );
        }
        return rest_ensure_response( $data );
    }

    /**
     * Return drinks.
     */
    public function get_drinks( \WP_REST_Request $request ) {
        $posts = get_posts( array( 'post_type' => 'aorp_drink_item', 'numberposts' => -1 ) );
        $data  = array();
        foreach ( $posts as $post ) {
            $data[] = array(
                'id'    => $post->ID,
                'title' => $post->post_title,
                'content' => $post->post_content,
            );
        }
        return rest_ensure_response( $data );
    }
}
