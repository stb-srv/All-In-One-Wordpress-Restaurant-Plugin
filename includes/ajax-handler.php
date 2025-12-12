<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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

    private function check_nonce( $action ) {
        $nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';
        if ( ! wp_verify_nonce( $nonce, $action ) ) {
            wp_send_json_error( array( 'message' => 'Ungültige Anfrage.' ) );
        }
    }

    private function format_price( $price ) {
        return \AIO_Restaurant_Plugin\format_price( (string) $price );
    }

    private function get_ingredient_labels( $codes ) {
        return \AIO_Restaurant_Plugin\ingredient_labels( (string) $codes );
    }

    private function food_row_html( $id ) {
        $item = get_post( $id );
        if ( ! $item ) return '';
        $price = get_post_meta( $id, '_aorp_price', true );
        $number = get_post_meta( $id, '_aorp_number', true );
        $ingredients = get_post_meta( $id, '_aorp_ingredients', true );
        $terms = get_the_terms( $id, 'aorp_menu_category' );
        $cat = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
        $cat_id = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->term_id : 0;
        $img_id = get_post_thumbnail_id( $id );
        $img_url = $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : '';
        ob_start();
        ?>
        <tr data-id="<?php echo esc_attr( $id ); ?>" data-title="<?php echo esc_attr( $item->post_title ); ?>" data-description="<?php echo esc_attr( $item->post_content ); ?>" data-price="<?php echo esc_attr( $price ); ?>" data-number="<?php echo esc_attr( $number ); ?>" data-ingredients="<?php echo esc_attr( $ingredients ); ?>" data-category="<?php echo esc_attr( $cat_id ); ?>" data-imageid="<?php echo esc_attr( $img_id ); ?>" data-imageurl="<?php echo esc_attr( $img_url ); ?>">
            <td><input type="checkbox" name="item_ids[]" value="<?php echo esc_attr( $id ); ?>" /></td>
            <td class="aorp-item-title">
                <?php if ( $img_url ) : ?><img src="<?php echo esc_url( $img_url ); ?>" class="aorp-item-image" alt="" /><?php endif; ?>
                <strong><?php echo esc_html( $item->post_title ); ?></strong>
            </td>
            <td><span class="aorp-item-description"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $item->post_content ), 15 ) ); ?></span></td>
            <td><span class="aorp-item-price"><?php echo esc_html( $this->format_price( $price ) ); ?></span></td>
            <td><?php echo esc_html( $number ); ?></td>
            <td><?php echo esc_html( $this->get_ingredient_labels( $ingredients ) ); ?></td>
            <td><?php echo $cat ? '<span class="aorp-item-category">' . esc_html( $cat ) . '</span>' : ''; ?></td>
            <td class="aorp-actions">
                <a href="#" class="aorp-edit" data-id="<?php echo esc_attr( $id ); ?>">✏️ Bearbeiten</a>
                <a href="#" class="aorp-delete" data-id="<?php echo esc_attr( $id ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'aorp_delete_item_' . $id ) ); ?>">🗑️ Löschen</a>
            </td>
        </tr>
        <?php
        return ob_get_clean();
    }

    private function drink_row_html( $id ) {
        $item = get_post( $id );
        if ( ! $item ) return '';
        $sizes = get_post_meta( $id, '_aorp_drink_sizes', true );
        $ingredients = get_post_meta( $id, '_aorp_ingredients', true );
        $terms = get_the_terms( $id, 'aorp_drink_category' );
        $cat = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
        $cat_id = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->term_id : 0;
        $img_id = get_post_thumbnail_id( $id );
        $img_url = $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : '';
        ob_start();
        ?>
        <tr data-id="<?php echo esc_attr( $id ); ?>" data-title="<?php echo esc_attr( $item->post_title ); ?>" data-description="<?php echo esc_attr( $item->post_content ); ?>" data-sizes="<?php echo esc_attr( $sizes ); ?>" data-ingredients="<?php echo esc_attr( $ingredients ); ?>" data-category="<?php echo esc_attr( $cat_id ); ?>" data-imageid="<?php echo esc_attr( $img_id ); ?>" data-imageurl="<?php echo esc_attr( $img_url ); ?>">
            <td><input type="checkbox" name="item_ids[]" value="<?php echo esc_attr( $id ); ?>" /></td>
            <td class="aorp-item-title">
                <?php if ( $img_url ) : ?><img src="<?php echo esc_url( $img_url ); ?>" class="aorp-item-image" alt="" /><?php endif; ?>
                <strong><?php echo esc_html( $item->post_title ); ?></strong>
            </td>
            <td><span class="aorp-item-description"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $item->post_content ), 15 ) ); ?></span></td>
            <td>
                <?php 
                if ( $sizes ) {
                    $lines = explode( "\n", $sizes );
                    echo '<ul class="aorp-drink-sizes">';
                    foreach ( $lines as $line ) {
                        if ( trim( $line ) ) echo '<li>' . esc_html( $line ) . '</li>';
                    }
                    echo '</ul>';
                }
                ?>
            </td>
            <td><?php echo esc_html( $this->get_ingredient_labels( $ingredients ) ); ?></td>
            <td><?php echo $cat ? '<span class="aorp-item-category">' . esc_html( $cat ) . '</span>' : ''; ?></td>
            <td class="aorp-actions">
                <a href="#" class="aorp-edit aorp-edit-drink" data-id="<?php echo esc_attr( $id ); ?>">✏️ Bearbeiten</a>
                <a href="#" class="aorp-delete aorp-delete-drink" data-id="<?php echo esc_attr( $id ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'aorp_delete_drink_item_' . $id ) ); ?>">🗑️ Löschen</a>
            </td>
        </tr>
        <?php
        return ob_get_clean();
    }

    public function add_item() {
        $this->check_nonce( 'aorp_add_item' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
        $post_id = wp_insert_post( array(
            'post_type'   => 'aorp_menu_item',
            'post_status' => 'publish',
            'post_title'  => sanitize_text_field( $_POST['item_title'] ),
            'post_content'=> sanitize_textarea_field( $_POST['item_description'] )
        ) );
        if ( $post_id ) {
            if ( ! empty( $_POST['item_category'] ) ) wp_set_object_terms( $post_id, intval( $_POST['item_category'] ), 'aorp_menu_category' );
            if ( isset( $_POST['item_price'] ) ) update_post_meta( $post_id, '_aorp_price', sanitize_text_field( $_POST['item_price'] ) );
            if ( isset( $_POST['item_number'] ) ) update_post_meta( $post_id, '_aorp_number', sanitize_text_field( $_POST['item_number'] ) );
            if ( ! empty( $_POST['item_image_id'] ) ) set_post_thumbnail( $post_id, intval( $_POST['item_image_id'] ) );
            if ( isset( $_POST['item_ingredients'] ) ) {
                $ings = array_filter( array_map( 'sanitize_text_field', explode( ',', $_POST['item_ingredients'] ) ) );
                update_post_meta( $post_id, '_aorp_ingredients', implode( ', ', $ings ) );
            }
            wp_send_json_success( array( 'row' => $this->food_row_html( $post_id ), 'message' => 'Speise hinzugefügt!' ) );
        }
        wp_send_json_error( array( 'message' => 'Fehler beim Hinzufügen' ) );
    }

    public function update_item() {
        $this->check_nonce( 'aorp_edit_item' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
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
        if ( isset( $_POST['item_price'] ) ) update_post_meta( $post_id, '_aorp_price', sanitize_text_field( $_POST['item_price'] ) );
        if ( isset( $_POST['item_number'] ) ) update_post_meta( $post_id, '_aorp_number', sanitize_text_field( $_POST['item_number'] ) );
        if ( ! empty( $_POST['item_image_id'] ) ) set_post_thumbnail( $post_id, intval( $_POST['item_image_id'] ) );
        if ( isset( $_POST['item_ingredients'] ) ) {
            $ings = array_filter( array_map( 'sanitize_text_field', explode( ',', $_POST['item_ingredients'] ) ) );
            update_post_meta( $post_id, '_aorp_ingredients', implode( ', ', $ings ) );
        }
        wp_send_json_success( array( 'row' => $this->food_row_html( $post_id ), 'message' => 'Speise aktualisiert!' ) );
    }

    public function delete_item() {
        $id = intval( $_POST['item_id'] );
        $this->check_nonce( 'aorp_delete_item_' . $id );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
        wp_trash_post( $id );
        wp_send_json_success( array( 'message' => 'Speise gelöscht!', 'undo_nonce' => wp_create_nonce( 'aorp_undo_delete_item_' . $id ) ) );
    }

    public function undo_delete_item() {
        $id = intval( $_POST['item_id'] );
        $this->check_nonce( 'aorp_undo_delete_item_' . $id );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
        wp_untrash_post( $id );
        wp_send_json_success( array( 'row' => $this->food_row_html( $id ) ) );
    }

    public function add_drink_item() {
        $this->check_nonce( 'aorp_add_drink_item' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
        $post_id = wp_insert_post( array(
            'post_type'   => 'aorp_drink_item',
            'post_status' => 'publish',
            'post_title'  => sanitize_text_field( $_POST['item_title'] ),
            'post_content'=> sanitize_textarea_field( $_POST['item_description'] )
        ) );
        if ( $post_id ) {
            if ( ! empty( $_POST['item_category'] ) ) wp_set_object_terms( $post_id, intval( $_POST['item_category'] ), 'aorp_drink_category' );
            if ( isset( $_POST['item_sizes'] ) ) update_post_meta( $post_id, '_aorp_drink_sizes', sanitize_textarea_field( $_POST['item_sizes'] ) );
            if ( ! empty( $_POST['item_image_id'] ) ) set_post_thumbnail( $post_id, intval( $_POST['item_image_id'] ) );
            if ( isset( $_POST['item_ingredients'] ) ) {
                $ings = array_filter( array_map( 'sanitize_text_field', explode( ',', $_POST['item_ingredients'] ) ) );
                update_post_meta( $post_id, '_aorp_ingredients', implode( ', ', $ings ) );
            }
            wp_send_json_success( array( 'row' => $this->drink_row_html( $post_id ), 'message' => 'Getränk hinzugefügt!' ) );
        }
        wp_send_json_error( array( 'message' => 'Fehler beim Hinzufügen' ) );
    }

    public function update_drink_item() {
        $this->check_nonce( 'aorp_edit_drink_item' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
        $post_id = intval( $_POST['item_id'] );
        wp_update_post( array(
            'ID'           => $post_id,
            'post_title'   => sanitize_text_field( $_POST['item_title'] ),
            'post_content' => sanitize_textarea_field( $_POST['item_description'] )
        ) );
        if ( ! empty( $_POST['item_category'] ) ) {
            wp_set_object_terms( $post_id, intval( $_POST['item_category'] ), 'aorp_drink_category' );
        } else {
            wp_set_object_terms( $post_id, array(), 'aorp_drink_category' );
        }
        if ( isset( $_POST['item_sizes'] ) ) update_post_meta( $post_id, '_aorp_drink_sizes', sanitize_textarea_field( $_POST['item_sizes'] ) );
        if ( ! empty( $_POST['item_image_id'] ) ) set_post_thumbnail( $post_id, intval( $_POST['item_image_id'] ) );
        if ( isset( $_POST['item_ingredients'] ) ) {
            $ings = array_filter( array_map( 'sanitize_text_field', explode( ',', $_POST['item_ingredients'] ) ) );
            update_post_meta( $post_id, '_aorp_ingredients', implode( ', ', $ings ) );
        }
        wp_send_json_success( array( 'row' => $this->drink_row_html( $post_id ), 'message' => 'Getränk aktualisiert!' ) );
    }

    public function delete_drink_item() {
        $id = intval( $_POST['item_id'] );
        $this->check_nonce( 'aorp_delete_drink_item_' . $id );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
        wp_trash_post( $id );
        wp_send_json_success( array( 'message' => 'Getränk gelöscht!' ) );
    }

    public function undo_delete_drink_item() {
        $id = intval( $_POST['item_id'] );
        $this->check_nonce( 'aorp_undo_delete_drink_item_' . $id );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
        wp_untrash_post( $id );
        wp_send_json_success( array( 'row' => $this->drink_row_html( $id ) ) );
    }
}
AORP_Ajax_Handler::init();
