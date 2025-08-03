<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if (
    isset( $_GET['delete_msg'], $_GET['_wpnonce'] ) &&
    wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'aorp_delete_msg_' . absint( $_GET['delete_msg'] ) )
) {
    wp_delete_post( absint( $_GET['delete_msg'] ), true );
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Nachricht gelöscht.', 'aorp' ) . '</p></div>';
}

$messages = get_posts( array(
    'post_type'   => 'aorp_contact_message',
    'numberposts' => -1,
    'orderby'     => 'date',
    'order'       => 'DESC',
) );

echo '<div class="wrap"><h1>' . esc_html__( 'Kontaktnachrichten', 'aorp' ) . '</h1>';
if ( $messages ) {
    echo '<table class="widefat"><thead><tr>';
    echo '<th>' . esc_html__( 'Datum', 'aorp' ) . '</th>';
    echo '<th>' . esc_html__( 'Name', 'aorp' ) . '</th>';
    echo '<th>' . esc_html__( 'E-Mail', 'aorp' ) . '</th>';
    echo '<th>' . esc_html__( 'Betreff', 'aorp' ) . '</th>';
    echo '<th>' . esc_html__( 'Nachricht', 'aorp' ) . '</th>';
    echo '<th>' . esc_html__( 'Aktionen', 'aorp' ) . '</th>';
    echo '</tr></thead><tbody>';
    foreach ( $messages as $msg ) {
        $name  = get_post_meta( $msg->ID, '_aorp_contact_name', true );
        $email = get_post_meta( $msg->ID, '_aorp_contact_email', true );
        $del_link = wp_nonce_url( add_query_arg( array( 'delete_msg' => $msg->ID ) ), 'aorp_delete_msg_' . $msg->ID );
        echo '<tr>';
        echo '<td>' . esc_html( get_date_from_gmt( $msg->post_date_gmt, 'd.m.Y H:i' ) ) . '</td>';
        echo '<td>' . esc_html( $name ) . '</td>';
        echo '<td><a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a></td>';
        echo '<td>' . esc_html( $msg->post_title ) . '</td>';
        echo '<td>' . esc_html( $msg->post_content ) . '</td>';
        echo '<td><a href="' . esc_url( $del_link ) . '">x</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
} else {
    echo '<p>' . esc_html__( 'Keine Nachrichten vorhanden.', 'aorp' ) . '</p>';
}
echo '</div>';

