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

/**
 * Count total menu items.
 */
function aio_get_total_speisen(): int {
    $count = wp_count_posts( 'aorp_menu_item' );
    return isset( $count->publish ) ? (int) $count->publish : 0;
}

/**
 * Count total menu categories.
 */
function aio_get_total_speisen_kategorien(): int {
    return (int) wp_count_terms( 'aorp_menu_category' );
}

/**
 * Count total drink items.
 */
function aio_get_total_getraenke(): int {
    $count = wp_count_posts( 'aorp_drink_item' );
    return isset( $count->publish ) ? (int) $count->publish : 0;
}

/**
 * Count total drink categories.
 */
function aio_get_total_getraenke_kategorien(): int {
    return (int) wp_count_terms( 'aorp_drink_category' );
}

/**
 * Count total ingredients.
 */
function aio_get_total_inhaltsstoffe(): int {
    $count = wp_count_posts( 'aorp_ingredient' );
    return isset( $count->publish ) ? (int) $count->publish : 0;
}

/**
 * Get most used food category name and count.
 *
 * @return array{name:string,count:int}
 */
function aio_get_meistgenutzte_kategorie(): array {
    $terms = get_terms( array(
        'taxonomy'   => 'aorp_menu_category',
        'hide_empty' => false,
        'orderby'    => 'count',
        'order'      => 'DESC',
        'number'     => 1,
    ) );

    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
        return array( 'name' => $terms[0]->name, 'count' => (int) $terms[0]->count );
    }

    return array( 'name' => '', 'count' => 0 );
}

/**
 * Get most popular ingredient name and count.
 *
 * @return array{name:string,count:int}
 */
function aio_get_beliebtester_inhaltsstoff(): array {
    $lookup  = array();
    $ings    = get_posts( array( 'post_type' => 'aorp_ingredient', 'numberposts' => -1 ) );
    foreach ( $ings as $ing ) {
        $code           = get_post_meta( $ing->ID, '_aorp_ing_code', true );
        $lookup[ $code ] = $ing->post_title;
    }

    $counts = array();
    $posts  = get_posts( array(
        'post_type'   => array( 'aorp_menu_item', 'aorp_drink_item' ),
        'numberposts' => -1,
    ) );
    foreach ( $posts as $post ) {
        $codes = get_post_meta( $post->ID, '_aorp_ingredients', true );
        foreach ( array_filter( array_map( 'trim', explode( ',', $codes ) ) ) as $code ) {
            if ( '' === $code ) {
                continue;
            }
            if ( ! isset( $counts[ $code ] ) ) {
                $counts[ $code ] = 0;
            }
            $counts[ $code ]++;
        }
    }

    if ( ! empty( $counts ) ) {
        arsort( $counts );
        $top_code  = key( $counts );
        $top_count = current( $counts );
        $name      = isset( $lookup[ $top_code ] ) ? $lookup[ $top_code ] : $top_code;
        return array( 'name' => $name, 'count' => (int) $top_count );
    }

    return array( 'name' => '', 'count' => 0 );
}

/**
 * Get last modification time across plugin post types.
 */
function aio_get_last_update(): string {
    $latest = get_posts( array(
        'post_type'   => array( 'aorp_menu_item', 'aorp_drink_item', 'aorp_ingredient' ),
        'numberposts' => 1,
        'orderby'     => 'modified',
        'order'       => 'DESC',
    ) );

    if ( ! empty( $latest ) ) {
        return get_date_from_gmt( $latest[0]->post_modified_gmt, 'd.m.Y H:i' );
    }

    return current_time( 'd.m.Y H:i' );
}

/**
 * Output the admin dashboard layout.
 */
function aio_render_dashboard(): void {
    $total_speisen               = aio_get_total_speisen();
    $total_speisen_kategorien    = aio_get_total_speisen_kategorien();
    $total_getraenke             = aio_get_total_getraenke();
    $total_getraenke_kategorien  = aio_get_total_getraenke_kategorien();
    $total_inhaltsstoffe         = aio_get_total_inhaltsstoffe();

    $cat_info   = aio_get_meistgenutzte_kategorie();
    $ing_info   = aio_get_beliebtester_inhaltsstoff();
    $last_update = aio_get_last_update();

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__( 'AIO-Restaurant', 'aorp' ) . '</h1>';

    echo '<div class="aio-dashboard-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;margin-top:20px;">';

    echo '<div class="aio-card" style="background:#fff;border:1px solid #ddd;padding:20px;border-radius:8px;box-shadow:0 1px 2px rgba(0,0,0,0.05);">';
    echo '<h2>🍽 ' . esc_html__( 'Speisekarte', 'aorp' ) . '</h2>';
    echo '<p>' . esc_html( $total_speisen ) . ' ' . esc_html__( 'Speisen in', 'aorp' ) . ' ' . esc_html( $total_speisen_kategorien ) . ' ' . esc_html__( 'Kategorien', 'aorp' ) . '</p>';
    echo '<a href="' . esc_url( admin_url( 'admin.php?page=aio-dishes' ) ) . '" class="button button-primary">' . esc_html__( 'Anzeigen', 'aorp' ) . '</a> ';
    echo '<a href="' . esc_url( admin_url( 'admin.php?page=aio-dishes&tab=add' ) ) . '" class="button">' . esc_html__( 'Neue Speise', 'aorp' ) . '</a>';
    echo '</div>';

    echo '<div class="aio-card" style="background:#fff;border:1px solid #ddd;padding:20px;border-radius:8px;box-shadow:0 1px 2px rgba(0,0,0,0.05);">';
    echo '<h2>🍹 ' . esc_html__( 'Getränkekarte', 'aorp' ) . '</h2>';
    echo '<p>' . esc_html( $total_getraenke ) . ' ' . esc_html__( 'Getränke in', 'aorp' ) . ' ' . esc_html( $total_getraenke_kategorien ) . ' ' . esc_html__( 'Kategorien', 'aorp' ) . '</p>';
    echo '<a href="' . esc_url( admin_url( 'admin.php?page=aio-drinks' ) ) . '" class="button button-primary">' . esc_html__( 'Anzeigen', 'aorp' ) . '</a> ';
    echo '<a href="' . esc_url( admin_url( 'admin.php?page=aio-drinks&tab=add' ) ) . '" class="button">' . esc_html__( 'Neues Getränk', 'aorp' ) . '</a>';
    echo '</div>';

    echo '<div class="aio-card" style="background:#fff;border:1px solid #ddd;padding:20px;border-radius:8px;box-shadow:0 1px 2px rgba(0,0,0,0.05);">';
    echo '<h2>🧂 ' . esc_html__( 'Inhaltsstoffe', 'aorp' ) . '</h2>';
    echo '<p>' . esc_html( $total_inhaltsstoffe ) . ' ' . esc_html__( 'Einträge, z.B. Allergene', 'aorp' ) . '</p>';
    echo '<a href="' . esc_url( admin_url( 'admin.php?page=aio-categories' ) ) . '" class="button">' . esc_html__( 'Bearbeiten', 'aorp' ) . '</a>';
    echo '</div>';

    echo '</div>'; // grid

    echo '<div style="margin-top:30px;background:#fff;padding:20px;border:1px solid #ddd;border-radius:8px;">';
    echo '<h2>' . esc_html__( 'Schnellaktionen', 'aorp' ) . '</h2>';
    echo '<a href="' . esc_url( admin_url( 'admin.php?page=aio-dishes&tab=add' ) ) . '" class="button button-primary" style="margin-right:10px;">+ ' . esc_html__( 'Neue Speise hinzufügen', 'aorp' ) . '</a>';
    echo '<a href="' . esc_url( admin_url( 'admin.php?page=aio-drinks&tab=add' ) ) . '" class="button" style="margin-right:10px;">+ ' . esc_html__( 'Neues Getränk hinzufügen', 'aorp' ) . '</a>';
    echo '<a href="' . esc_url( admin_url( 'admin.php?page=aio-import-export' ) ) . '" class="button" style="margin-right:10px;">⬆ ' . esc_html__( 'CSV Import starten', 'aorp' ) . '</a>';
    echo '<a href="' . esc_url( admin_url( 'admin.php?page=aio-import-export' ) ) . '" class="button">⬇ ' . esc_html__( 'CSV Export aller Daten', 'aorp' ) . '</a>';
    echo '</div>';

    echo '<div style="margin-top:30px;background:#fff;padding:20px;border:1px solid #ddd;border-radius:8px;">';
    echo '<h2>' . esc_html__( 'Statistik', 'aorp' ) . '</h2>';
    if ( $cat_info['name'] ) {
        echo '<p><strong>' . esc_html__( 'Meistgenutzte Kategorie:', 'aorp' ) . '</strong> ' . esc_html( $cat_info['name'] ) . ' (' . esc_html( $cat_info['count'] ) . ' ' . esc_html__( 'Speisen', 'aorp' ) . ')</p>';
    }
    if ( $ing_info['name'] ) {
        echo '<p><strong>' . esc_html__( 'Beliebtester Inhaltsstoff:', 'aorp' ) . '</strong> ' . esc_html( $ing_info['name'] ) . ' (' . esc_html( $ing_info['count'] ) . 'x)</p>';
    }
    echo '<p><strong>' . esc_html__( 'Letztes Update:', 'aorp' ) . '</strong> ' . esc_html( $last_update ) . '</p>';
    echo '</div>';

    echo '</div>'; // wrap
}

