<?php
/*
 * GCMovie Admin
 */

if ( ! defined( 'WPINC' ) ) {
    die( 'No direct access.' );
}

class GCMovie_Admin {

    function __construct() {
        add_filter( 'manage_gcmovie_posts_columns', array( $this, 'custom_table_columns' ) );
        add_action( 'manage_gcmovie_posts_custom_column' , array( $this, 'custom_table_column' ), 10, 2 );

        require_once GCMOVIE_PLUGIN_DIR . 'inc/class-gcmovie-setting.php';
        $gcmovie_setting = new GCMovie_Setting;
        add_action( 'admin_menu', array( $gcmovie_setting, 'register_pages' ) );
    }

    static function init() {
        new GCMovie_Admin;
    }

    /**
     * Custom table columns, add "Director" and "Shortcode" columns.
     */
    function custom_table_columns( $columns ) {
        $date = $columns['date'];
        unset( $columns['date'] );

        $columns['director'] = __( 'Director', 'gcmoive' );
        $columns['shortcode'] = __( 'Shortcode', 'gcmoive' );
        $columns['date'] = $date;

        return $columns;
    }

    function custom_table_column( $column_name, $post_id ) {
        switch ( $column_name ) {
            case 'director' :
                echo esc_html( get_post_meta( $post_id, '_gcmovie_director', true ) );
                break;

            case 'shortcode' :
                echo "[gcmovie id=$post_id]";
                break;
        }
    }
}

GCMovie_Admin::init();
