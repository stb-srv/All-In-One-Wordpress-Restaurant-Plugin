<?php if ( $items ) : ?>
    <h3><?php echo $mode === 'drink' ? 'Alle Getränke' : 'Alle Speisen'; ?></h3>
    <input type="text" id="aorp-item-filter" placeholder="Suche" />
    <p>
        <button class="button aorp-select-all" data-target="#aorp-items-table tbody input[type=checkbox]">Alle auswählen</button>
        <button class="button aorp-unselect-all" data-target="#aorp-items-table tbody input[type=checkbox]">Auswahl aufheben</button>
    </p>
    <table class="widefat" id="aorp-items-table">
        <thead>
            <tr>
                <th></th>
                <?php
                    $cols = $mode === 'drink'
                        ? array(
                            'title'       => 'Name',
                            'description' => 'Beschreibung',
                            'sizes'       => 'Größen/Preise',
                            'ingredients' => 'Inhaltsstoffe',
                            'category'    => 'Kategorie',
                            'actions'     => 'Aktionen',
                        )
                        : array(
                            'title'       => 'Name',
                            'description' => 'Beschreibung',
                            'price'       => 'Preis',
                            'number'      => 'Nummer',
                            'ingredients' => 'Inhaltsstoffe',
                            'category'    => 'Kategorie',
                            'actions'     => 'Aktionen',
                        );
                    foreach ( $cols as $key => $label ) {
                        if ( $key === 'actions' ) { echo '<th>' . esc_html( $label ) . '</th>'; continue; }
                        $dir = ( $orderby === $key && $order === 'ASC' ) ? 'DESC' : 'ASC';
                        $symbol = '';
                        if ( $orderby === $key ) $symbol = $order === 'ASC' ? ' \u2191' : ' \u2193';
                        $url = add_query_arg( array( 'orderby' => $key, 'order' => $dir ) );
                        echo '<th class="aorp-sort"><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . $symbol . '</a></th>';
                    }
                ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $items as $item ) :
                $terms = get_the_terms( $item->ID, $mode === 'drink' ? 'aorp_drink_category' : 'aorp_menu_category' );
                $cat   = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
                $data  = array(
                    'id'          => $item->ID,
                    'title'       => $item->post_title,
                    'description' => $item->post_content,
                    'ingredients' => get_post_meta( $item->ID, '_aorp_ingredients', true ),
                    'category'    => $cat ? $cat->term_id : 0,
                );
                if ( $mode === 'drink' ) {
                    $data['sizes'] = get_post_meta( $item->ID, '_aorp_drink_sizes', true );
                } else {
                    $data['price']  = get_post_meta( $item->ID, '_aorp_price', true );
                    $data['number'] = get_post_meta( $item->ID, '_aorp_number', true );
                }
            ?>
            <tr <?php foreach ( $data as $k => $v ) echo 'data-' . $k . '="' . esc_attr( $v ) . '" '; ?>>
                <td><input type="checkbox" name="item_ids[]" value="<?php echo esc_attr( $item->ID ); ?>" /></td>
                <td><?php echo esc_html( $item->post_title ); ?></td>
                <td><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $item->post_content ), 15 ) ); ?></td>
                <?php if ( $mode === 'drink' ) : ?>
                    <td><?php echo nl2br( esc_html( get_post_meta( $item->ID, '_aorp_drink_sizes', true ) ) ); ?></td>
                <?php else : ?>
                    <td><?php echo esc_html( \AIO_Restaurant_Plugin\format_price( get_post_meta( $item->ID, '_aorp_price', true ) ) ); ?></td>
                    <td><?php echo esc_html( get_post_meta( $item->ID, '_aorp_number', true ) ); ?></td>
                <?php endif; ?>
                <td><?php echo esc_html( \AIO_Restaurant_Plugin\ingredient_labels( get_post_meta( $item->ID, '_aorp_ingredients', true ) ) ); ?></td>
                <td><?php echo $cat ? esc_html( $cat->name ) : ''; ?></td>
                <td>
                    <?php if ( $mode === 'drink' ) : ?>
                        <a href="#" class="aorp-edit-drink" data-id="<?php echo esc_attr( $item->ID ); ?>">Bearbeiten</a> |
                        <a href="#" class="aorp-delete-drink" data-id="<?php echo esc_attr( $item->ID ); ?>" data-nonce="<?php echo wp_create_nonce( 'aorp_delete_drink_item_' . $item->ID ); ?>">Löschen</a>
                    <?php else : ?>
                        <a href="#" class="aorp-edit" data-id="<?php echo esc_attr( $item->ID ); ?>">Bearbeiten</a> |
                        <a href="#" class="aorp-delete" data-id="<?php echo esc_attr( $item->ID ); ?>" data-nonce="<?php echo wp_create_nonce( 'aorp_delete_item_' . $item->ID ); ?>">Löschen</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
