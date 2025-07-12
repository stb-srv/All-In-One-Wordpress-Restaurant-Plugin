<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles AJAX requests for managing menu items.
 *
 * @package AIO_Restaurant_Plugin
 */
class AORP_Ajax_Handler {
    public static function init() {
        $handler = new self();
        add_action( 'wp_ajax_aorp_add_item', array( $handler, 'add_item' ) );
        add_action( 'wp_ajax_aorp_update_item', array( $handler, 'update_item' ) );
        add_action( 'wp_ajax_aorp_delete_item', array( $handler, 'delete_item' ) );
        add_action( 'wp_ajax_aorp_undo_delete_item', array( $handler, 'undo_delete_item' ) );

        add_action( 'wp_ajax_aorp_add_drink_item', array( $handler, 'add_drink_item' ) );
        add_action( 'wp_ajax_aorp_update_drink_item', array( $handler, 'update_drink_item' ) );
        add_action( 'wp_ajax_aorp_delete_drink_item', array( $handler, 'delete_drink_item' ) );
        add_action( 'wp_ajax_aorp_undo_delete_drink_item', array( $handler, 'undo_delete_drink_item' ) );
    }


/**
 * check_nonce
 *
 * @return void
 */
    private function check_nonce( $action ) {
        $nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';
        if ( ! wp_verify_nonce( $nonce, $action ) ) {
            wp_send_json_error( array( 'message' => 'Ungültige Anfrage.' ) );
        }
    }


/**
 * format_price
 *
 * @return void
 */
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


/**
 * get_ingredient_labels
 *
 * @return void
 */
    private function get_ingredient_labels( $codes ) {
        $codes = array_filter( array_map( 'trim', explode( ',', $codes ) ) );
        if ( empty( $codes ) ) {
            return '';
        }
        $lookup = array();
        $ings = get_posts( array( 'post_type' => 'aorp_ingredient', 'numberposts' => -1 ) );
        foreach ( $ings as $ing ) {
            $code = get_post_meta( $ing->ID, '_aorp_ing_code', true );
            $lookup[ $code ] = $ing->post_title;
        }
        $labels = array();
        foreach ( $codes as $code ) {
            if ( isset( $lookup[ $code ] ) ) {
                $labels[] = $lookup[ $code ] . ' (' . $code . ')';
            } else {
                $labels[] = $code;
            }
        }
        return implode( ', ', $labels );
    }


/**
 * food_row_html
 *
 * @return void
 */
    private function food_row_html( $id ) {
        $item = get_post( $id );
        if ( ! $item ) {
            return '';
        }
        $price = get_post_meta( $id, '_aorp_price', true );
        $number = get_post_meta( $id, '_aorp_number', true );
        $ingredients = get_post_meta( $id, '_aorp_ingredients', true );
        $terms = get_the_terms( $id, 'aorp_menu_category' );
        $cat = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
        $cat_id = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->term_id : 0;
        ob_start();
        ?>
        <tr data-id="<?php echo esc_attr( $id ); ?>" data-title="<?php echo esc_attr( $item->post_title ); ?>" data-description="<?php echo esc_attr( $item->post_content ); ?>" data-price="<?php echo esc_attr( $price ); ?>" data-number="<?php echo esc_attr( $number ); ?>" data-ingredients="<?php echo esc_attr( $ingredients ); ?>" data-category="<?php echo esc_attr( $cat_id ); ?>">
            <td><input type="checkbox" name="item_ids[]" value="<?php echo esc_attr( $id ); ?>" /></td>
            <td><?php echo esc_html( $item->post_title ); ?></td>
            <td><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $item->post_content ), 15 ) ); ?></td>
            <td><?php echo esc_html( $this->format_price( $price ) ); ?></td>
            <td><?php echo esc_html( $number ); ?></td>
            <td><?php echo esc_html( $this->get_ingredient_labels( $ingredients ) ); ?></td>
            <td><?php echo esc_html( $cat ); ?></td>
            <td>
                <a href="#" class="aorp-edit" data-id="<?php echo esc_attr( $id ); ?>"><?php _e( 'Bearbeiten', 'aorp' ); ?></a> |
                <a href="#" class="aorp-delete" data-id="<?php echo esc_attr( $id ); ?>" data-nonce="<?php echo wp_create_nonce( 'aorp_delete_item_' . $id ); ?>"><?php _e( 'Löschen', 'aorp' ); ?></a>
            </td>
        </tr>
        <?php
        return ob_get_clean();
    }


/**
 * drink_row_html
 *
 * @return void
 */
    private function drink_row_html( $id ) {
        $item = get_post( $id );
        if ( ! $item ) {
            return '';
        }
        $sizes = nl2br( esc_html( get_post_meta( $id, '_aorp_drink_sizes', true ) ) );
        $ingredients = get_post_meta( $id, '_aorp_ingredients', true );
        $terms = get_the_terms( $id, 'aorp_drink_category' );
        $cat = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
        $cat_id = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->term_id : 0;
        ob_start();
        ?>
        <tr data-id="<?php echo esc_attr( $id ); ?>" data-title="<?php echo esc_attr( $item->post_title ); ?>" data-description="<?php echo esc_attr( $item->post_content ); ?>" data-sizes="<?php echo esc_attr( get_post_meta( $id, '_aorp_drink_sizes', true ) ); ?>" data-ingredients="<?php echo esc_attr( $ingredients ); ?>" data-category="<?php echo esc_attr( $cat_id ); ?>">
            <td><input type="checkbox" name="item_ids[]" value="<?php echo esc_attr( $id ); ?>" /></td>
            <td><?php echo esc_html( $item->post_title ); ?></td>
            <td><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $item->post_content ), 15 ) ); ?></td>
            <td><?php echo $sizes; ?></td>
            <td><?php echo esc_html( $this->get_ingredient_labels( $ingredients ) ); ?></td>
            <td><?php echo esc_html( $cat ); ?></td>
            <td>
                <a href="#" class="aorp-edit-drink" data-id="<?php echo esc_attr( $id ); ?>"><?php _e( 'Bearbeiten', 'aorp' ); ?></a> |
                <a href="#" class="aorp-delete-drink" data-id="<?php echo esc_attr( $id ); ?>" data-nonce="<?php echo wp_create_nonce( 'aorp_delete_drink_item_' . $id ); ?>"><?php _e( 'Löschen', 'aorp' ); ?></a>
            </td>
        </tr>
        <?php
        return ob_get_clean();
    }


/**
 * add_item
 *
 * @return void
 */
    public function add_item() {
        $this->check_nonce( 'aorp_add_item' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error();
        }
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
                $ings = array_filter( array_map( 'sanitize_text_field', explode( ',', $_POST['item_ingredients'] ) ) );
                update_post_meta( $post_id, '_aorp_ingredients', implode( ', ', $ings ) );
            }
            wp_send_json_success( array( 'row' => $this->food_row_html( $post_id ) ) );
        }
        wp_send_json_error();
    }


/**
 * update_item
 *
 * @return void
 */
    public function update_item() {
        $this->check_nonce( 'aorp_edit_item' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error();
        }
        $post_id = intval( $_POST['item_id'] );
        wp_update_post( array(
            'ID'           => $post_id,
            'post_title'   => sanitize_text_field( $_POST['item_title'] ),
            'post_content' => sanitize_textarea_field( $_POST['item_description'] )
        // Update item post with sanitized data
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
            $ings = array_filter( array_map( 'sanitize_text_field', explode( ',', $_POST['item_ingredients'] ) ) );
            update_post_meta( $post_id, '_aorp_ingredients', implode( ', ', $ings ) );
        }
        wp_send_json_success( array( 'row' => $this->food_row_html( $post_id ) ) );
    }


/**
 * delete_item
 *
 * @return void
 */
    public function delete_item() {
        $id = intval( $_POST['item_id'] );
        $this->check_nonce( 'aorp_delete_item_' . $id );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error();
        }
        wp_trash_post( $id );
        wp_send_json_success( array( 'undo_nonce' => wp_create_nonce( 'aorp_undo_delete_item_' . $id ) ) );
    }


/**
 * undo_delete_item
 *
 * @return void
 */
    public function undo_delete_item() {
        $id = intval( $_POST['item_id'] );
        $this->check_nonce( 'aorp_undo_delete_item_' . $id );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error();
        }
        wp_untrash_post( $id );
        wp_send_json_success( array( 'row' => $this->food_row_html( $id ) ) );
    }


/**
 * add_drink_item
 *
 * @return void
 */
    public function add_drink_item() {
        $this->check_nonce( 'aorp_add_drink_item' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error();
        }
        $title = sanitize_text_field( $_POST['item_title'] );
        $existing = get_page_by_title( $title, OBJECT, 'aorp_drink_item' );
        if ( $existing ) {
            wp_send_json_error( array( 'message' => 'Getränk existiert bereits.' ) );
        }
        $post_id = wp_insert_post( array(
            'post_type'   => 'aorp_drink_item',
            'post_status' => 'publish',
            'post_title'  => $title,
            'post_content'=> sanitize_textarea_field( $_POST['item_description'] )
        ) );
        if ( $post_id ) {
                // Loop through volume fields and build meta string
            if ( ! empty( $_POST['item_category'] ) ) {
                wp_set_object_terms( $post_id, intval( $_POST['item_category'] ), 'aorp_drink_category' );
            }
            if ( isset( $_POST['item_sizes'] ) && is_array( $_POST['item_sizes'] ) ) {
                $lines = array();
                foreach ( $_POST['item_sizes'] as $vol => $price ) {
                    $price = trim( $price );
                    if ( $price !== '' ) {
                        $lines[] = $vol . '=' . $price;
                    }
                }
                update_post_meta( $post_id, '_aorp_drink_sizes', implode( "\n", $lines ) );
            }
            if ( isset( $_POST['item_ingredients'] ) ) {
                $ings = array_filter( array_map( 'sanitize_text_field', explode( ',', $_POST['item_ingredients'] ) ) );
                update_post_meta( $post_id, '_aorp_ingredients', implode( ', ', $ings ) );
            }
            wp_send_json_success( array( 'row' => $this->drink_row_html( $post_id ) ) );
        }
        wp_send_json_error();
    }


/**
 * update_drink_item
 *
 * @return void
 */
    public function update_drink_item() {
        $this->check_nonce( 'aorp_edit_drink_item' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error();
        }
        $post_id = intval( $_POST['item_id'] );
        $title   = sanitize_text_field( $_POST['item_title'] );
        $existing = get_page_by_title( $title, OBJECT, 'aorp_drink_item' );
        if ( $existing && intval( $existing->ID ) !== $post_id ) {
            wp_send_json_error( array( 'message' => 'Getränk existiert bereits.' ) );
        }
        wp_update_post( array(
            'ID'           => $post_id,
            'post_title'   => $title,
            'post_content' => sanitize_textarea_field( $_POST['item_description'] )
        ) );
        if ( ! empty( $_POST['item_category'] ) ) {
            wp_set_object_terms( $post_id, intval( $_POST['item_category'] ), 'aorp_drink_category' );
        }
        if ( isset( $_POST['item_sizes'] ) && is_array( $_POST['item_sizes'] ) ) {
            $lines = array();
            foreach ( $_POST['item_sizes'] as $vol => $price ) {
                $price = trim( $price );
                if ( $price !== '' ) {
                    $lines[] = $vol . '=' . $price;
                }
            }
            update_post_meta( $post_id, '_aorp_drink_sizes', implode( "\n", $lines ) );
        }
        if ( isset( $_POST['item_ingredients'] ) ) {
            $ings = array_filter( array_map( 'sanitize_text_field', explode( ',', $_POST['item_ingredients'] ) ) );
            update_post_meta( $post_id, '_aorp_ingredients', implode( ', ', $ings ) );
        }
        wp_send_json_success( array( 'row' => $this->drink_row_html( $post_id ) ) );
    }


/**
 * delete_drink_item
 *
 * @return void
 */
    public function delete_drink_item() {
        $id = intval( $_POST['item_id'] );
        $this->check_nonce( 'aorp_delete_drink_item_' . $id );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error();
        }
        wp_trash_post( $id );
        wp_send_json_success( array( 'undo_nonce' => wp_create_nonce( 'aorp_undo_delete_drink_item_' . $id ) ) );
    }


/**
 * undo_delete_drink_item
 *
 * @return void
 */
    public function undo_delete_drink_item() {
        $id = intval( $_POST['item_id'] );
        $this->check_nonce( 'aorp_undo_delete_drink_item_' . $id );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error();
        }
        wp_untrash_post( $id );
        wp_send_json_success( array( 'row' => $this->drink_row_html( $id ) ) );
    }
}
AORP_Ajax_Handler::init();
