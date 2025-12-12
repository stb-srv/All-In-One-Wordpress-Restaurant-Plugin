<?php
/**
 * Modern Add Item Form Template
 * v2.8.0 - Beautiful card-based forms
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$is_drink = ( $mode === 'drink' );
$post_type = $is_drink ? 'aorp_drink_item' : 'aorp_menu_item';
$taxonomy = $is_drink ? 'aorp_drink_category' : 'aorp_menu_category';
$nonce = wp_create_nonce( 'aorp_add_' . ( $is_drink ? 'drink_item' : 'item' ) );
$action = 'aorp_add_' . ( $is_drink ? 'drink_item' : 'item' );

$cats = get_terms( $taxonomy, array( 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC' ) );
$ings = get_posts( array( 'post_type' => 'aorp_ingredient', 'numberposts' => -1 ) );
?>

<div class="aorp-add-form-wrapper">
    <div class="aorp-add-form-card">
        <div class="aorp-card-header-modern">
            <h2 class="aorp-form-title">
                <?php if ( $is_drink ) : ?>
                    <span class="aorp-form-icon">🍹</span> Neues Getränk hinzufügen
                <?php else : ?>
                    <span class="aorp-form-icon">🍽️</span> Neue Speise hinzufügen
                <?php endif; ?>
            </h2>
            <p class="aorp-form-subtitle">
                <?php echo $is_drink 
                    ? 'Fülle das Formular aus, um ein neues Getränk zur Karte hinzuzufügen.' 
                    : 'Fülle das Formular aus, um eine neue Speise zur Karte hinzuzufügen.'; ?>
            </p>
        </div>
        
        <form class="aorp-add-form aorp-modern-form" 
              data-action="<?php echo esc_attr( $action ); ?>" 
              data-type="<?php echo esc_attr( $mode ); ?>">
            
            <input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>" />
            
            <!-- Main Fields -->
            <div class="aorp-form-section">
                <h3 class="aorp-section-title">
                    <span class="aorp-section-icon">📝</span> Grundinformationen
                </h3>
                
                <div class="aorp-form-grid">
                    <?php if ( ! $is_drink ) : ?>
                        <div class="aorp-form-group">
                            <label for="item_number">
                                <span class="aorp-label-icon">#️⃣</span> Nummer
                                <span class="aorp-optional">(optional)</span>
                            </label>
                            <input type="text" 
                                   id="item_number"
                                   name="item_number" 
                                   class="aorp-input"
                                   placeholder="z.B. 42" />
                        </div>
                    <?php endif; ?>
                    
                    <div class="aorp-form-group <?php echo $is_drink ? 'aorp-full-width' : ''; ?>">
                        <label for="item_title">
                            <span class="aorp-label-icon">✨</span> Name
                            <span class="aorp-required">*</span>
                        </label>
                        <input type="text" 
                               id="item_title"
                               name="item_title" 
                               class="aorp-input"
                               placeholder="<?php echo $is_drink ? 'z.B. Cola' : 'z.B. Spaghetti Carbonara'; ?>" 
                               required />
                    </div>
                </div>
                
                <div class="aorp-form-group">
                    <label for="item_description">
                        <span class="aorp-label-icon">📄</span> Beschreibung
                        <span class="aorp-optional">(optional)</span>
                    </label>
                    <textarea id="item_description"
                              name="item_description" 
                              class="aorp-textarea"
                              rows="3"
                              placeholder="<?php echo $is_drink ? 'Beschreibe das Getränk...' : 'Beschreibe das Gericht...'; ?>"></textarea>
                </div>
            </div>
            
            <!-- Price / Sizes Section -->
            <div class="aorp-form-section">
                <h3 class="aorp-section-title">
                    <span class="aorp-section-icon">💰</span> 
                    <?php echo $is_drink ? 'Größen & Preise' : 'Preis & Kategorie'; ?>
                </h3>
                
                <?php if ( $is_drink ) : ?>
                    <div class="aorp-form-group">
                        <label>
                            <span class="aorp-label-icon">🥤</span> Größen & Preise
                            <span class="aorp-help-text">Format: 0.3L = 2.50</span>
                        </label>
                        <div class="aorp-drink-sizes-list">
                            <div class="aorp-size-row">
                                <input type="text" 
                                       class="aorp-size-vol aorp-input" 
                                       placeholder="0,3L" />
                                <span class="aorp-size-separator">=</span>
                                <input type="text" 
                                       class="aorp-size-price aorp-input" 
                                       placeholder="2.50" />
                                <button type="button" 
                                        class="aorp-btn aorp-btn-icon aorp-remove-size"
                                        title="Entfernen">
                                    ×
                                </button>
                            </div>
                        </div>
                        <button type="button" 
                                class="aorp-btn aorp-btn-secondary aorp-add-drink-size">
                            <span class="aorp-btn-icon">+</span> Größe hinzufügen
                        </button>
                        <input type="hidden" name="drink_sizes" value="" />
                    </div>
                <?php else : ?>
                    <div class="aorp-form-grid">
                        <div class="aorp-form-group">
                            <label for="item_price">
                                <span class="aorp-label-icon">💶</span> Preis
                            </label>
                            <input type="text" 
                                   id="item_price"
                                   name="item_price" 
                                   class="aorp-input"
                                   placeholder="12.50" />
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ( $cats && ! empty( $cats ) ) : ?>
                    <div class="aorp-form-group">
                        <label for="item_category">
                            <span class="aorp-label-icon">🏷️</span> Kategorie
                        </label>
                        <select id="item_category"
                                name="item_category" 
                                class="aorp-select">
                            <option value="">-- Kategorie wählen --</option>
                            <?php foreach ( $cats as $cat ) : ?>
                                <option value="<?php echo esc_attr( $cat->term_id ); ?>">
                                    <?php echo esc_html( $cat->name ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Ingredients Section -->
            <div class="aorp-form-section">
                <h3 class="aorp-section-title">
                    <span class="aorp-section-icon">⚠️</span> Inhaltsstoffe & Allergene
                </h3>
                
                <div class="aorp-form-group">
                    <label for="ingredient_select">
                        <span class="aorp-label-icon">🔍</span> Inhaltsstoffe auswählen
                    </label>
                    <select id="ingredient_select"
                            class="aorp-ing-select aorp-select">
                        <option value="">-- Inhaltsstoff auswählen --</option>
                        <?php foreach ( $ings as $ing ) : 
                            $code = get_post_meta( $ing->ID, '_aorp_ing_code', true );
                        ?>
                            <option value="<?php echo esc_attr( $code ); ?>">
                                <?php echo esc_html( $ing->post_title ); ?>
                                <?php if ( $code ) : ?>
                                    (<?php echo esc_html( $code ); ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <div class="aorp-selected aorp-chip-container"></div>
                    <input type="hidden" name="item_ingredients" class="aorp-ing-text" />
                </div>
            </div>
            
            <!-- Image Section -->
            <div class="aorp-form-section">
                <h3 class="aorp-section-title">
                    <span class="aorp-section-icon">🖼️</span> Bild
                </h3>
                
                <div class="aorp-form-group">
                    <label>
                        <span class="aorp-label-icon">📷</span> Produktbild
                        <span class="aorp-optional">(optional)</span>
                    </label>
                    <div class="aorp-image-upload-wrapper">
                        <button type="button" class="aorp-btn aorp-btn-secondary aorp-upload-image">
                            <span class="aorp-btn-icon">📷</span> Bild auswählen
                        </button>
                        <div class="aorp-image-preview aorp-image-preview-large"></div>
                    </div>
                    <input type="hidden" name="item_image_id" class="aorp-image-id" />
                </div>
            </div>
            
            <!-- Submit Section -->
            <div class="aorp-form-actions aorp-form-actions-sticky">
                <button type="submit" class="aorp-btn aorp-btn-primary aorp-btn-large">
                    <span class="aorp-btn-icon">✨</span> 
                    <?php echo $is_drink ? 'Getränk hinzufügen' : 'Speise hinzufügen'; ?>
                </button>
            </div>
        </form>
    </div>
    
    <!-- Help Card -->
    <div class="aorp-help-card">
        <h3 class="aorp-help-title">
            <span class="aorp-help-icon">💡</span> Tipps
        </h3>
        <ul class="aorp-help-list">
            <?php if ( $is_drink ) : ?>
                <li>✓ Gib mehrere Größen an (z.B. 0.3L, 0.5L, 1L)</li>
                <li>✓ Preise mit Punkt angeben (z.B. 2.50)</li>
                <li>✓ Füge Allergene und Inhaltsstoffe hinzu</li>
            <?php else : ?>
                <li>✓ Eine aussagekräftige Beschreibung hilft Kunden</li>
                <li>✓ Preise mit Punkt angeben (z.B. 12.50)</li>
                <li>✓ Nummern helfen bei der Bestellung</li>
            <?php endif; ?>
            <li>✓ Hochwertige Bilder steigern den Appetit</li>
            <li>✓ Alle Felder können später bearbeitet werden</li>
        </ul>
    </div>
</div>
