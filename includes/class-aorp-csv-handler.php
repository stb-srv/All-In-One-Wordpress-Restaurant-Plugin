<?php
namespace AIO_Restaurant_Plugin;

/**
 * Handles import/export of menu data.
 */
class AORP_CSV_Handler {
    /**
     * Register actions for import/export.
     */
    public function register(): void {
        add_action( 'admin_post_aorp_export_csv', array( $this, 'export_csv' ) );
        add_action( 'admin_post_aorp_import_csv', array( $this, 'import_csv' ) );
    }

    /**
     * Export CSV.
     */
    public function export_csv(): void {
        check_admin_referer( 'aorp_export_csv' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Berechtigung fehlt.', 'aorp' ) );
        }

        $rows   = array();
        $rows[] = array( 'Nummer', 'Titel', 'Beschreibung', 'Preis', 'Kategorie', 'Inhaltsstoffe', 'Bild-ID' );

        $posts = get_posts(
            array(
                'post_type'   => 'aorp_menu_item',
                'numberposts' => -1,
                'meta_key'    => '_aorp_number',
                'orderby'     => 'meta_value_num',
                'order'       => 'ASC',
            )
        );

        foreach ( $posts as $post ) {
            $price  = get_post_meta( $post->ID, '_aorp_price', true );
            $number = get_post_meta( $post->ID, '_aorp_number', true );
            $ings   = get_post_meta( $post->ID, '_aorp_ingredients', true );
            $terms  = get_the_terms( $post->ID, 'aorp_menu_category' );
            $cat    = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
            $img_id = get_post_thumbnail_id( $post->ID );
            $rows[] = array(
                $number,
                $post->post_title,
                trim( wp_strip_all_tags( $post->post_content ) ),
                $price,
                $cat,
                $ings,
                $img_id,
            );
        }

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="aorp-export.csv"' );

        $output = fopen( 'php://output', 'w' );
        foreach ( $rows as $row ) {
            fputcsv( $output, $row );
        }
        fclose( $output );
        exit;
    }

    /**
     * Import CSV.
     */
    public function import_csv(): void {
        check_admin_referer( 'aorp_import_csv' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Berechtigung fehlt.', 'aorp' ) );
        }

        if ( empty( $_FILES['csv_file']['tmp_name'] ) ) {
            wp_safe_redirect( admin_url( 'admin.php?page=aio-settings-layouts&tab=import' ) );
            exit;
        }

        $handle = fopen( $_FILES['csv_file']['tmp_name'], 'r' );
        if ( ! $handle ) {
            wp_safe_redirect( admin_url( 'admin.php?page=aio-settings-layouts&tab=import' ) );
            exit;
        }

        $header = fgetcsv( $handle );
        while ( ( $data = fgetcsv( $handle ) ) ) {
            $row = array_map( 'trim', $data );
            list( $number, $title, $desc, $price, $category, $ingredients, $img_id ) = array_pad( $row, 7, '' );
            if ( '' === $title ) {
                continue;
            }
            $post_id = wp_insert_post(
                array(
                    'post_type'   => 'aorp_menu_item',
                    'post_status' => 'publish',
                    'post_title'  => $title,
                    'post_content'=> $desc,
                )
            );
            if ( $post_id ) {
                if ( '' !== $price ) {
                    update_post_meta( $post_id, '_aorp_price', $price );
                }
                if ( '' !== $number ) {
                    update_post_meta( $post_id, '_aorp_number', $number );
                }
                if ( '' !== $ingredients ) {
                    update_post_meta( $post_id, '_aorp_ingredients', $ingredients );
                }
                if ( '' !== $img_id ) {
                    set_post_thumbnail( $post_id, intval( $img_id ) );
                }
                if ( '' !== $category ) {
                    $term = term_exists( $category, 'aorp_menu_category' );
                    if ( 0 === $term || null === $term ) {
                        $term = wp_insert_term( $category, 'aorp_menu_category' );
                    }
                    if ( ! is_wp_error( $term ) ) {
                        wp_set_object_terms( $post_id, intval( $term['term_id'] ), 'aorp_menu_category' );
                    }
                }
            }
        }
        fclose( $handle );

        wp_safe_redirect( admin_url( 'admin.php?page=aio-settings-layouts&tab=import' ) );
        exit;
    }
}
