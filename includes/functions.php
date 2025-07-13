<?php
namespace AIO_Restaurant_Plugin;

/**
 * Format a price string with Euro symbol.
 */
function format_price( string $price ): string {
    $price = trim( $price );
    if ( '' === $price ) {
        return '';
    }
    if ( false === strpos( $price, '€' ) ) {
        $price .= ' €';
    }
    return $price;
}

/**
 * Convert ingredient codes to readable labels.
 */
function ingredient_labels( string $codes ): string {
    $codes = array_filter( array_map( 'trim', explode( ',', $codes ) ) );
    if ( empty( $codes ) ) {
        return '';
    }
    $lookup = array();
    $ings   = get_posts( array( 'post_type' => 'aorp_ingredient', 'numberposts' => -1 ) );
    foreach ( $ings as $ing ) {
        $code           = get_post_meta( $ing->ID, '_aorp_ing_code', true );
        $lookup[ $code ] = $ing->post_title;
    }
    $labels = array();
    foreach ( $codes as $code ) {
        $labels[] = isset( $lookup[ $code ] ) ? $lookup[ $code ] . ' (' . $code . ')' : $code;
    }
    return implode( ', ', $labels );
}

