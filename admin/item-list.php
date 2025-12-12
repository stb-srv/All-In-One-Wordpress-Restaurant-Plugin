<?php if ( $items ) : ?>
    <div class="aorp-list-header">
        <h3><?php echo $mode === 'drink' ? '🍺 Alle Getränke' : '🍽️ Alle Speisen'; ?></h3>
        <div class="aorp-list-controls">
            <input type="text" id="aorp-item-filter" placeholder="🔍 Suche..." class="aorp-search-input" />
        </div>
    </div>
    <table class="widefat" id="aorp-items-table">
        <thead>
            <tr>
                <th style="width: 40px;"></th>
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
                        if ( $key === 'actions' ) { 
                            echo '<th style="width: 180px;">' . esc_html( $label ) . '</th>'; 
                            continue; 
                        }
                        $dir = ( $orderby === $key && $order === 'ASC' ) ? 'DESC' : 'ASC';
                        $symbol = '';
                        if ( $orderby === $key ) $symbol = $order === 'ASC' ? ' ↑' : ' ↓';
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
                    'imageid'     => get_post_thumbnail_id( $item->ID ),
                    'imageurl'    => get_post_thumbnail_id( $item->ID ) ? wp_get_attachment_image_url( get_post_thumbnail_id( $item->ID ), 'thumbnail' ) : '',
                );
                if ( $mode === 'drink' ) {
                    $data['sizes'] = get_post_meta( $item->ID, '_aorp_drink_sizes', true );
                } else {
                    $data['price']  = get_post_meta( $item->ID, '_aorp_price', true );
                    $data['number'] = get_post_meta( $item->ID, '_aorp_number', true );
                }
            ?>
            <tr <?php foreach ( $data as $k => $v ) echo 'data-' . esc_attr( $k ) . '="' . esc_attr( $v ) . '" '; ?>>
                <td><input type="checkbox" name="item_ids[]" value="<?php echo esc_attr( $item->ID ); ?>" /></td>
                <td class="aorp-item-title">
                    <?php if ( has_post_thumbnail( $item->ID ) ) : ?>
                        <img src="<?php echo esc_url( get_the_post_thumbnail_url( $item->ID, 'thumbnail' ) ); ?>" class="aorp-item-image" alt="" />
                    <?php endif; ?>
                    <strong><?php echo esc_html( $item->post_title ); ?></strong>
                </td>
                <td><span class="aorp-item-description"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $item->post_content ), 15 ) ); ?></span></td>
                <?php if ( $mode === 'drink' ) : ?>
                    <td>
                        <?php 
                        $sizes = get_post_meta( $item->ID, '_aorp_drink_sizes', true );
                        if ( $sizes ) {
                            $lines = explode( "\n", $sizes );
                            echo '<ul class="aorp-drink-sizes">';
                            foreach ( $lines as $line ) {
                                if ( trim( $line ) ) {
                                    echo '<li>' . esc_html( $line ) . '</li>';
                                }
                            }
                            echo '</ul>';
                        }
                        ?>
                    </td>
                <?php else : ?>
                    <td><span class="aorp-item-price"><?php echo esc_html( \AIO_Restaurant_Plugin\format_price( get_post_meta( $item->ID, '_aorp_price', true ) ) ); ?></span></td>
                    <td><?php echo esc_html( get_post_meta( $item->ID, '_aorp_number', true ) ); ?></td>
                <?php endif; ?>
                <td><?php echo esc_html( \AIO_Restaurant_Plugin\ingredient_labels( get_post_meta( $item->ID, '_aorp_ingredients', true ) ) ); ?></td>
                <td><?php echo $cat ? '<span class="aorp-item-category">' . esc_html( $cat->name ) . '</span>' : ''; ?></td>
                <td class="aorp-actions">
                    <?php if ( $mode === 'drink' ) : ?>
                        <a href="#" class="aorp-edit aorp-edit-drink" data-id="<?php echo esc_attr( $item->ID ); ?>">✏️ Bearbeiten</a>
                        <a href="#" class="aorp-delete aorp-delete-drink" data-id="<?php echo esc_attr( $item->ID ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'aorp_delete_drink_item_' . $item->ID ) ); ?>">🗑️ Löschen</a>
                    <?php else : ?>
                        <a href="#" class="aorp-edit" data-id="<?php echo esc_attr( $item->ID ); ?>">✏️ Bearbeiten</a>
                        <a href="#" class="aorp-delete" data-id="<?php echo esc_attr( $item->ID ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'aorp_delete_item_' . $item->ID ) ); ?>">🗑️ Löschen</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else : ?>
    <div class="aorp-empty-state">
        <div class="aorp-empty-state-icon"><?php echo $mode === 'drink' ? '🍺' : '🍽️'; ?></div>
        <div class="aorp-empty-state-text">
            <?php echo $mode === 'drink' ? 'Noch keine Getränke vorhanden.' : 'Noch keine Speisen vorhanden.'; ?><br>
            Füge dein erstes Element über den Tab "Neu hinzufügen" hinzu.
        </div>
    </div>
<?php endif; ?>
