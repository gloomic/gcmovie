<?php
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) || ! WP_UNINSTALL_PLUGIN ) {
    exit;
}

global $wpdb;

// delete options

$options = get_option( 'gcmovie_options' );
delete_option( 'gcmovie_options' );

if ( isset( $options['delete_movies_after_uninstall'] && $options['delete_movies_after_uninstall'] === '1' ) ) {

    // delete posts

    $args = array(
        'post_type' => 'gcmovie'
    );

    $query = new WP_Query( $args );
    while ( $query->have_posts() ) {
        $query->the_post();
        if ( get_post_type() === 'gcmovie' ) {
            $post_id = get_the_ID();
            wp_delete_post( $post_id, true );
        }
    }
    wp_reset_postdata();
}
