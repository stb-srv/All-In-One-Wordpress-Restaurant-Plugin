<?php
/**
 * Widget to display the menu via shortcode.
 */
class AORP_Menu_Widget extends WP_Widget {

/**
 * __construct
 *
 * @return void
 */
    public function __construct() {
        parent::__construct('aorp_menu_widget',__('Speisekarte','aorp'),array('description'=>__('Zeigt die Speisekarte an','aorp')));
    }

/**
 * widget
 *
 * @return void
 */
    public function widget($args,$instance){
        echo $args['before_widget'];
        echo do_shortcode('[speisekarte]');
        echo $args['after_widget'];
    }
}
/**
 * Widget providing a dark mode switcher.
 */
class AORP_Lightswitcher_Widget extends WP_Widget {

/**
 * __construct
 *
 * @return void
 */
    public function __construct(){
        parent::__construct('aorp_lightswitcher_widget',__('Lightswitcher','aorp'),array('description'=>__('Dark Mode Schalter','aorp')));
    }

/**
 * widget
 *
 * @return void
 */
    public function widget($args,$instance){
        echo $args['before_widget'];
        echo do_shortcode('[restaurant_lightswitcher]');
        echo $args['after_widget'];
    }
}

add_action('widgets_init',function(){
    register_widget('AORP_Menu_Widget');
    register_widget('AORP_Lightswitcher_Widget');
});
