<?php
namespace AIO_Restaurant_Plugin;

/**
 * Simple PDF export for menu items.
 */
class AORP_PDF_Export {
    /**
     * Register export action.
     */
    public function register(): void {
        add_action( 'admin_post_aorp_export_pdf', array( $this, 'export_pdf' ) );
    }

    /**
     * Handle PDF export request.
     */
    public function export_pdf(): void {
        check_admin_referer( 'aorp_export_pdf' );
        $foods  = get_posts(
            array(
                'post_type'   => 'aorp_menu_item',
                'numberposts' => -1,
                'meta_key'    => '_aorp_number',
                'orderby'     => 'meta_value_num',
                'order'       => 'ASC',
                'post_status' => 'publish',
            )
        );
        $drinks = get_posts(
            array(
                'post_type'   => 'aorp_drink_item',
                'numberposts' => -1,
                'orderby'     => 'title',
                'order'       => 'ASC',
                'post_status' => 'publish',
            )
        );

        $lines   = array();
        $lines[] = 'Speisekarte';
        foreach ( $foods as $food ) {
            $price = get_post_meta( $food->ID, '_aorp_price', true );
            $number = get_post_meta( $food->ID, '_aorp_number', true );
            $line = ( $number ? $number . ' ' : '' ) . $food->post_title;
            if ( $price ) {
                $line .= ' - ' . format_price( $price );
            }
            $lines[] = $line;
        }

        $lines[] = '';
        $lines[] = 'Getränkekarte';
        foreach ( $drinks as $drink ) {
            $sizes = get_post_meta( $drink->ID, '_aorp_drink_sizes', true );
            if ( $sizes ) {
                foreach ( explode( "\n", $sizes ) as $row ) {
                    list( $vol, $price ) = array_map( 'trim', explode( '=', $row ) );
                    $lines[] = $drink->post_title . ' ' . $vol . ' - ' . format_price( $price );
                }
            } else {
                $lines[] = $drink->post_title;
            }
        }

        $pdf = $this->generate_pdf( $lines );
        header( 'Content-Type: application/pdf' );
        header( 'Content-Disposition: attachment; filename="menu.pdf"' );
        header( 'Content-Length: ' . strlen( $pdf ) );
        echo $pdf;
        exit;
    }

    /**
     * Create a very small PDF document from text lines.
     */
    private function generate_pdf( array $lines ): string {
        $objects = array();
        $pdf     = "%PDF-1.4\n";

        $objects[] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
        $objects[] = "<< /Type /Page /Parent 2 0 R /Resources << /Font << /F1 4 0 R >> >> /MediaBox [0 0 595 842] /Contents 5 0 R >>";
        $objects[] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";

        $text = "BT\n/F1 12 Tf\n";
        $y    = 800;
        foreach ( $lines as $line ) {
            $safe  = str_replace( array( '(', ')' ), array( '\\(', '\\)' ), $line );
            $text .= "72 $y Td ($safe) Tj\n";
            $y    -= 14;
        }
        $text .= "ET";
        $objects[] = "<< /Length " . strlen( $text ) . " >>\nstream\n" . $text . "\nendstream";

        $offsets = array();
        foreach ( $objects as $i => $obj ) {
            $offsets[ $i + 1 ] = strlen( $pdf );
            $pdf .= ( $i + 1 ) . " 0 obj\n" . $obj . "\nendobj\n";
        }

        $xref = strlen( $pdf );
        $pdf .= "xref\n0 " . ( count( $objects ) + 1 ) . "\n0000000000 65535 f \n";
        foreach ( $offsets as $off ) {
            $pdf .= sprintf( "%010d 00000 n \n", $off );
        }
        $pdf .= "trailer << /Size " . ( count( $objects ) + 1 ) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xref . "\n%%EOF";

        return $pdf;
    }
}
