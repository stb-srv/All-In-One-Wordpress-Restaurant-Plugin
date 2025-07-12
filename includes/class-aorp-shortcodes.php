<?php
namespace AIO_Restaurant_Plugin;

/**
 * Registers and renders shortcodes.
 */
class AORP_Shortcodes {
    /**
     * Register shortcodes.
     */
    public function register(): void {
        add_shortcode( 'speisekarte', array( $this, 'render_foods' ) );
        add_shortcode( 'getraenkekarte', array( $this, 'render_drinks' ) );
        add_shortcode( 'restaurant_lightswitcher', array( $this, 'render_lightswitcher' ) );
    }

    /**
     * Render food list.
     */
    public function render_foods(): string {
        $foods = get_posts( array( 'post_type' => 'aorp_menu_item', 'numberposts' => -1 ) );
        ob_start();
        echo '<div class="aorp-food-list">';
        foreach ( $foods as $food ) {
            echo '<div class="aorp-food-item">';
            echo '<h3>' . esc_html( $food->post_title ) . '</h3>';
            echo wp_kses_post( wpautop( $food->post_content ) );
            echo '</div>';
        }
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * Render drink list.
     */
    public function render_drinks(): string {
        $drinks = get_posts( array( 'post_type' => 'aorp_drink_item', 'numberposts' => -1 ) );
        ob_start();
        echo '<div class="aorp-drink-list">';
        foreach ( $drinks as $drink ) {
            echo '<div class="aorp-drink-item">';
            echo '<h3>' . esc_html( $drink->post_title ) . '</h3>';
            echo wp_kses_post( wpautop( $drink->post_content ) );
            echo '</div>';
        }
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * Render lightswitcher.
     */
    public function render_lightswitcher(): string {
        return '<button class="aorp-dark-toggle" data-target="body">Dark Mode</button>';
    }
}
