<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function aio_frontend_search_filter( $output, $tag, $attr ) {
    // Enable search filter by default; option allows opt-out.
    if ( '1' !== get_option( 'aio_enable_search_filter', '1' ) ) {
        return $output;
    }

    if ( 'speisekarte' === $tag ) {
        $taxonomy = 'aorp_menu_category';
    } elseif ( 'getraenkekarte' === $tag ) {
        $taxonomy = 'aorp_drink_category';
    } else {
        return $output;
    }

    $terms = get_terms( $taxonomy, array( 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC' ) );
    $dropdown = '<select id="mini-category" class="aio-category-filter"><option value="">' . esc_html__( 'Alle Kategorien', 'aorp' ) . '</option>';
    foreach ( $terms as $term ) {
        $dropdown .= '<option value="' . esc_attr( $term->term_id ) . '">' . esc_html( $term->name ) . '</option>';
    }
    $dropdown .= '</select>';
    $search = '<input type="text" id="mini-search" class="aio-search-field" placeholder="' . esc_attr__( 'Suche', 'aorp' ) . '" />';
    $ui = '<div class="aio-search-filter-container">' . $search . ' ' . $dropdown . '</div>';

    return $ui . $output;
}
add_filter( 'do_shortcode_tag', 'aio_frontend_search_filter', 10, 3 );

function aio_frontend_search_filter_script() {
    // Skip script if feature explicitly disabled.
    if ( '1' !== get_option( 'aio_enable_search_filter', '1' ) ) {
        return;
    }
    ?>
    <script>
    jQuery(function($){
        function filterList(container){
            var search = container.find('.aio-search-field').val().toLowerCase();
            var cat = container.find('.aio-category-filter').val();
            var list = container.next('.aorp-menu');
            list.find('.aorp-category').each(function(){
                var cid = $(this).data('cat');
                var showCat = !cat || cat==cid;
                var items = $(this).next('.aorp-items').find('.aorp-item');
                items.each(function(){
                    var text = $(this).text().toLowerCase();
                    var match = text.indexOf(search) !== -1;
                    $(this).toggle(match && showCat);
                });
                var anyVisible = items.filter(':visible').length>0;
                $(this).toggle(anyVisible);
                $(this).next('.aorp-items').toggle(anyVisible);
            });
        }
        $(document).on('input change', '.aio-search-field, .aio-category-filter', function(){
            filterList($(this).closest('.aio-search-filter-container'));
        });
    });
    </script>
    <?php
}
add_action( 'wp_footer', 'aio_frontend_search_filter_script' );
