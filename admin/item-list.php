<?php
/**
 * Modern Item List Template
 * v2.7.1 - Card-based layout with improved UX
 */
?>

<?php if ( $items ) : ?>
    <div class="aorp-list-header">
        <h2 class="aorp-list-title">
            <?php echo $mode === 'drink' ? '🍹 Alle Getränke' : '🍽️ Alle Speisen'; ?>
            <span class="aorp-count-badge"><?php echo count( $items ); ?></span>
        </h2>
        
        <div class="aorp-list-controls">
            <div class="aorp-search-box">
                <span class="aorp-search-icon">🔍</span>
                <input type="text" 
                       id="aorp-item-filter" 
                       class="aorp-search-input"
                       placeholder="<?php echo $mode === 'drink' ? 'Getränk suchen...' : 'Speise suchen...'; ?>" />
            </div>
            <div class="aorp-bulk-actions">
                <button class="aorp-btn aorp-btn-secondary aorp-select-all" 
                        data-target="#aorp-items-table tbody input[type=checkbox]">
                    ☑️ Alle auswählen
                </button>
                <button class="aorp-btn aorp-btn-secondary aorp-unselect-all" 
                        data-target="#aorp-items-table tbody input[type=checkbox]">
                    ☐ Auswahl aufheben
                </button>
            </div>
        </div>
    </div>

    <div class="aorp-items-grid">
        <?php foreach ( $items as $item ) :
            $terms = get_the_terms( $item->ID, $mode === 'drink' ? 'aorp_drink_category' : 'aorp_menu_category' );
            $cat   = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
            $thumbnail_url = get_post_thumbnail_id( $item->ID ) 
                ? wp_get_attachment_image_url( get_post_thumbnail_id( $item->ID ), 'medium' ) 
                : '';
            
            $data  = array(
                'id'          => $item->ID,
                'title'       => $item->post_title,
                'description' => $item->post_content,
                'ingredients' => get_post_meta( $item->ID, '_aorp_ingredients', true ),
                'category'    => $cat ? $cat->term_id : 0,
                'imageid'     => get_post_thumbnail_id( $item->ID ),
                'imageurl'    => $thumbnail_url,
                'type'        => $mode,
            );
            
            if ( $mode === 'drink' ) {
                $data['sizes'] = get_post_meta( $item->ID, '_aorp_drink_sizes', true );
            } else {
                $data['price']  = get_post_meta( $item->ID, '_aorp_price', true );
                $data['number'] = get_post_meta( $item->ID, '_aorp_number', true );
            }
        ?>
        <div class="aorp-item-card" <?php foreach ( $data as $k => $v ) echo 'data-' . esc_attr($k) . '="' . esc_attr( $v ) . '" '; ?>>
            <div class="aorp-card-checkbox">
                <input type="checkbox" 
                       name="item_ids[]" 
                       value="<?php echo esc_attr( $item->ID ); ?>" 
                       id="item-<?php echo esc_attr( $item->ID ); ?>" />
                <label for="item-<?php echo esc_attr( $item->ID ); ?>"></label>
            </div>
            
            <?php if ( $thumbnail_url ) : ?>
                <div class="aorp-card-image" style="background-image: url('<?php echo esc_url( $thumbnail_url ); ?>')"></div>
            <?php else : ?>
                <div class="aorp-card-image aorp-no-image">
                    <span class="aorp-placeholder-icon"><?php echo $mode === 'drink' ? '🍹' : '🍽️'; ?></span>
                </div>
            <?php endif; ?>
            
            <div class="aorp-card-body">
                <div class="aorp-card-header">
                    <h3 class="aorp-card-title">
                        <?php if ( $mode === 'food' && get_post_meta( $item->ID, '_aorp_number', true ) ) : ?>
                            <span class="aorp-item-number">#<?php echo esc_html( get_post_meta( $item->ID, '_aorp_number', true ) ); ?></span>
                        <?php endif; ?>
                        <?php echo esc_html( $item->post_title ); ?>
                    </h3>
                    
                    <?php if ( $cat ) : ?>
                        <span class="aorp-category-badge">
                            <span class="aorp-category-icon">🏷️</span>
                            <?php echo esc_html( $cat->name ); ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <?php if ( $item->post_content ) : ?>
                    <p class="aorp-card-description">
                        <?php echo esc_html( wp_trim_words( wp_strip_all_tags( $item->post_content ), 20 ) ); ?>
                    </p>
                <?php endif; ?>
                
                <div class="aorp-card-meta">
                    <?php if ( $mode === 'drink' ) : ?>
                        <?php $sizes = get_post_meta( $item->ID, '_aorp_drink_sizes', true ); ?>
                        <?php if ( $sizes ) : ?>
                            <div class="aorp-sizes-list">
                                <strong>🥤 Größen:</strong>
                                <?php 
                                $size_lines = array_filter( explode( "\n", $sizes ) );
                                foreach ( $size_lines as $idx => $line ) {
                                    echo '<span class="aorp-size-item">' . esc_html( trim( $line ) ) . '</span>';
                                    if ( $idx < count($size_lines) - 1 ) echo ' • ';
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                    <?php else : ?>
                        <div class="aorp-price-tag">
                            <strong>💶 Preis:</strong>
                            <span class="aorp-price"><?php echo esc_html( \AIO_Restaurant_Plugin\format_price( get_post_meta( $item->ID, '_aorp_price', true ) ) ); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php $ingredients = get_post_meta( $item->ID, '_aorp_ingredients', true ); ?>
                    <?php if ( $ingredients ) : ?>
                        <div class="aorp-ingredients">
                            <strong>⚠️ Inhaltsstoffe:</strong>
                            <span><?php echo esc_html( \AIO_Restaurant_Plugin\ingredient_labels( $ingredients ) ); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="aorp-card-actions">
                <?php if ( $mode === 'drink' ) : ?>
                    <button class="aorp-btn aorp-btn-primary aorp-btn-sm aorp-edit" data-id="<?php echo esc_attr( $item->ID ); ?>">
                        ✏️ Bearbeiten
                    </button>
                    <button class="aorp-btn aorp-btn-danger aorp-btn-sm aorp-delete" 
                            data-id="<?php echo esc_attr( $item->ID ); ?>" 
                            data-nonce="<?php echo esc_attr( wp_create_nonce( 'aorp_delete_drink_item_' . $item->ID ) ); ?>">
                        🗑️ Löschen
                    </button>
                <?php else : ?>
                    <button class="aorp-btn aorp-btn-primary aorp-btn-sm aorp-edit" data-id="<?php echo esc_attr( $item->ID ); ?>">
                        ✏️ Bearbeiten
                    </button>
                    <button class="aorp-btn aorp-btn-danger aorp-btn-sm aorp-delete" 
                            data-id="<?php echo esc_attr( $item->ID ); ?>" 
                            data-nonce="<?php echo esc_attr( wp_create_nonce( 'aorp_delete_item_' . $item->ID ) ); ?>">
                        🗑️ Löschen
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
<?php else : ?>
    <div class="aorp-empty-state">
        <div class="aorp-empty-icon"><?php echo $mode === 'drink' ? '🍹' : '🍽️'; ?></div>
        <h3><?php echo $mode === 'drink' ? 'Noch keine Getränke' : 'Noch keine Speisen'; ?></h3>
        <p>Füge dein erstes <?php echo $mode === 'drink' ? 'Getränk' : 'Gericht'; ?> über das Formular oben hinzu.</p>
    </div>
<?php endif; ?>
