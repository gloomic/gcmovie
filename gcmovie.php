<?php
/*
 * Plugin Name: GCMovie
 * Plugin URI: https://www.gloomycorner.com/gcmovie/
 * Description: A plugin example with custom post type, shortcode, widget, etc.
 * Author: Gloomic
 * Version: 0.1
 * Author URI: https://gloomycorner.com
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: gcmovie
 * Domain Path: /languages
 */

if ( ! defined( 'WPINC' ) ) {
    die( 'No direct access.' );
}

define( 'GCMOVIE_MINIMUM_WP_VERSION', '5.3' );

define( 'GCMOVIE_VERSION',            '0.1' );
define( 'GCMOVIE_PLUGIN_DIR',         plugin_dir_path( __FILE__ ) );
define( 'GCMOVIE_PLUGIN_URL',         plugin_dir_url( __FILE__ ) );
define( 'GCMOVIE_PLUGIN_FILE',        __FILE__ );


class GCMovie {
    private static $instance = null;

    private function __construct() {
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

        // Custom post type: gcmovie

        add_action( 'init', array( $this, 'register_post_type' ) );

        // Custom content for gcmovie
        add_filter( 'the_content', array( $this, 'get_movie_html' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_scripts' ) ); // public scripts and styles

        // Edit for gcmovie
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post_gcmovie', array( $this, 'save_movie' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) ); // admin scripts and style

        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
        add_action( 'after_switch_theme', array( $this, 'activate' ) );
    }

    static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new GCMovie;
        }

        return self::$instance;
    }

    static function init() {
        self::get_instance();
    }

    // Make permalinks to work when this plugin is activated.
    function activate() {
        $this->register_post_type();

        // Flush permalinks
        flush_rewrite_rules();
    }

    function deactivate() {
        flush_rewrite_rules();
    }

    function load_textdomain() {
        load_plugin_textdomain( 'gcmovie', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    function register_post_type() {
        $labels = array(
            'name'               => __( 'GCMovies', 'gcmovie' ),// General name for the post type.
            'menu_name'          => __( 'GCMovie', 'gcmovie' ),
            'singular_name'      => __( 'Movie', 'gcmovie' ),
            'all_items'          => __( 'Movies', 'gcmovie' ),
            'search_items'       => __( 'Search Movies', 'gcmovie' ),
            'add_new'            => __( 'Add New', 'gcmovie' ),
            'add_new_item'       => __( 'Add New Movie', 'gcmovie' ),
            'new_item'           => __( 'New Movie', 'gcmovie' ),
            'view_item'          => __( 'View Movie', 'gcmovie' ),
            'edit_item'          => __( 'Edit Movie', 'gcmovie' ),
            'not_found'          => __( 'No Movies Found.', 'gcmovie' ),
            'not_found_in_trash' => __( 'Movie not found in Trash.', 'gcmovie' ),
            'parent_item_colon'  => __( 'Parent Movie', 'gcmovie' ),
        );

        $args = array(
            'labels'             => $labels,
            'description'        => 'Movie',
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-media-video',
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_admin_bar'  => true,
            'query_var'          => true,
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'supports'           => array( 'title', 'thumbnail', 'editor' ),
        );

        register_post_type( 'gcmovie', $args );
    }

    function add_meta_boxes() {
        add_meta_box(
            'gcmovie_meta_box_information',       // id
            __( 'Information', 'gcmovie' ),       // name
            array( $this, 'display_meta_box_information' ),  // display function
            'gcmovie'                             // post type
        );
    }

    function display_meta_box_information( $post ) {
        wp_nonce_field( 'gcmovie_my_nonce', 'gcmovie_nonce_field' );

        $director = get_post_meta( $post->ID, '_gcmovie_director', true );
        $location = get_post_meta( $post->ID, '_gcmovie_location', true );
        $language = get_post_meta( $post->ID, '_gcmovie_language', true );

        do_action( 'gcmovie_edit_start' );
        ?>

        <table class="gcmovie-edit-table" role="presentation">
            <tbody>
                <tr>
                    <td class="left"><label for="gcmovie_director"><?php _e( 'Director', 'gcmovie' ); ?></label></td>
                    <td><input type="text" name="gcmovie_director" id="gcmovie_director" value="<?php echo $director; ?>"></td>
                </tr>
                <tr>
                    <td><label for="gcmovie_location"><?php _e( 'Location', 'gcmovie' ); ?></label></td>
                    <td><input type="text" name="gcmovie_location" id="gcmovie_location" value="<?php echo $location; ?>"></td>
                </div>
                <tr>
                    <td><label for="gcmovie_language"><?php _e( 'Language', 'gcmovie' ); ?></label></td>
                    <td><input type="text" name="gcmovie_language" id="gcmovie_language" value="<?php echo $language; ?>"></td>
                </tr>
            </tbody>
        </table>

        <?php
        do_action( 'gcmovie_edit_end' );
    }

    function save_movie( $post_id ) {
        if( ! isset( $_POST['gcmovie_nonce_field'] ) ) {
            return $post_id;
        }

        if( ! wp_verify_nonce( $_POST['gcmovie_nonce_field'], 'gcmovie_my_nonce' ) ) {
            return $post_id;
        }

        // Check for autosave
        if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        $director = isset( $_POST['gcmovie_director'] ) ? sanitize_text_field( $_POST['gcmovie_director'] ) : '';
        update_post_meta( $post_id, '_gcmovie_director', $director );

        $location = isset( $_POST['gcmovie_location']) ? sanitize_text_field( $_POST['gcmovie_location'] ) : '';
        update_post_meta( $post_id, '_gcmovie_location', $location );

        $language = isset( $_POST['gcmovie_language'] ) ? sanitize_text_field( $_POST['gcmovie_language'] ) : '';
        update_post_meta( $post_id, '_gcmovie_language', $language );

        do_action( 'gcmovie_save', $post_id );
    }

    function enqueue_admin_scripts() {
        wp_enqueue_style( 'gcmovie-admin.css', GCMOVIE_PLUGIN_URL . 'assets/css/gcmovie-admin.css', '', GCMOVIE_VERSION );
    }

    function enqueue_public_scripts() {
        wp_enqueue_style( 'gcmovie-admin.css', GCMOVIE_PLUGIN_URL . 'assets/css/gcmovie-public.css', '', GCMOVIE_VERSION );
    }

    function get_movie_html( $content ) {
        global $post, $post_type;

        if( $post_type != 'gcmovie' || ! is_singular() ) {
            return $content;
        }

        ob_start();
        $this->display_movie( $post );
        return ob_get_clean();
    }

    private function display_movie( $post ) {
        $post_id = $post->ID;

        do_action( 'gcmovie_content_start', $post );
        ?>
        <div class="gcmovie-information">
            <?php $this->display_movie_information( $post_id ); ?>
        </div>
        <h4><?php _e( 'Description', 'gcmovie' ); ?></h4>
        <?php
        echo $post->post_content;

        do_action( 'gcmovie_content_end', $post );
    }

    function display_movie_information( $post_id ) {
        $director = get_post_meta( $post_id, '_gcmovie_director', true );
        $location = get_post_meta( $post_id, '_gcmovie_location', true );
        $language = get_post_meta( $post_id, '_gcmovie_language', true );
        ?>
        <b><?php _e( 'Director', 'gcmovie' ); ?>: </b><?php echo $director; ?></br>
        <b><?php _e( 'Location', 'gcmovie' ); ?>: </b><?php echo $location; ?></br>
        <b><?php _e( 'Language', 'gcmovie' ); ?>: </b><?php echo $language; ?></br>
        <?php
    }

}

GCMovie::init();
require_once GCMOVIE_PLUGIN_DIR . 'inc/class-gcmovie-shortcode.php';
require_once GCMOVIE_PLUGIN_DIR . 'inc/class-gcmovie-widget.php';
require_once GCMOVIE_PLUGIN_DIR . 'inc/class-gcmovie-admin.php';
