<?php if ( $items ) : ?>
    <h3>Alle Getränke</h3>
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
                    $cols = array(
                        'title'       => 'Name',
                        'description' => 'Beschreibung',
                        'sizes'       => 'Größen/Preise',
                        'ingredients' => 'Inhaltsstoffe',
                        'category'    => 'Kategorie',
                        'actions'     => 'Aktionen'
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
                $terms = get_the_terms( $item->ID, 'aorp_drink_category' );
                $cat   = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
            ?>
            <tr data-id="<?php echo esc_attr( $item->ID ); ?>" data-title="<?php echo esc_attr( $item->post_title ); ?>" data-description="<?php echo esc_attr( $item->post_content ); ?>" data-sizes="<?php echo esc_attr( get_post_meta( $item->ID, '_aorp_drink_sizes', true ) ); ?>" data-ingredients="<?php echo esc_attr( get_post_meta( $item->ID, '_aorp_ingredients', true ) ); ?>" data-category="<?php echo esc_attr( $cat ? $cat->term_id : 0 ); ?>">
                <td><input type="checkbox" name="item_ids[]" value="<?php echo esc_attr( $item->ID ); ?>" /></td>
                <td><?php echo esc_html( $item->post_title ); ?></td>
                <td><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $item->post_content ), 15 ) ); ?></td>
                <td><?php echo nl2br( esc_html( get_post_meta( $item->ID, '_aorp_drink_sizes', true ) ) ); ?></td>
                <td><?php echo esc_html( $this->get_ingredient_labels( get_post_meta( $item->ID, '_aorp_ingredients', true ) ) ); ?></td>
                <td><?php echo $cat ? esc_html( $cat->name ) : ''; ?></td>
                <td>
                    <a href="#" class="aorp-edit-drink" data-id="<?php echo esc_attr( $item->ID ); ?>">Bearbeiten</a> |
                    <a href="#" class="aorp-delete-drink" data-id="<?php echo esc_attr( $item->ID ); ?>" data-nonce="<?php echo wp_create_nonce( 'aorp_delete_drink_item_' . $item->ID ); ?>">Löschen</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
