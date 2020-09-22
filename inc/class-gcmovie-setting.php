<?php
/*
 * GCMovie setting page
 */

if ( ! defined( 'WPINC' ) ) {
    die( 'No direct access.' );
}

class GCMovie_Setting {

    function __construct() {
        add_action( 'admin_init', array( $this, 'register_setting' ) );
    }

    static function init() {
        new GCMovie_Setting;
    }

    function register_pages() {
		$parent = 'edit.php?post_type=gcmovie';

        // Settings page
		add_submenu_page(
			$parent,
			esc_html__( 'Settings', 'gcmovie' ),
			esc_html__( 'Settings', 'gcmovie' ),
			'manage_options',
			'gcmovie_settings',
			array( $this, 'show_page' )
		);
	}

    function register_setting() {
        // Register a new setting 'gcmovie_options' for "gcmovie_settings" page
        register_setting( 'gcmovie_settings', 'gcmovie_options', array( $this, 'sanitize' ) );

        add_settings_section(
           'gcmovie_settings_section',
           __( 'After uninstall', 'gcmovie' ),
           array( $this, 'display_section' ),
           'gcmovie_settings'
        );

        add_settings_field(
            'gcmovie_settings_field',  // As of WP 4.6 this value is used only internally
                                       // Use $args' label_for to populate the id inside the callback
            __( 'Delete movies', 'gcmovie' ),
            array( $this, 'display_field' ),
            'gcmovie_settings',
            'gcmovie_settings_section',
            [
               'label_for' => 'delete_movies_after_uninstall',
            ]
        );
    }

    function display_section( $args ) {
    }

    function display_field( $args ) {
        // Get the value of the setting we've registered with register_setting()
        $options = get_option( 'gcmovie_options' );

        ?>
        <label for="<?php echo esc_attr( $args['label_for'] ); ?>">
            <input id="<?php echo esc_attr( $args['label_for'] ); ?>" name="gcmovie_options[<?php echo esc_attr( $args['label_for'] ); ?>]" type="checkbox" value="1" <?php echo ( isset( $options[$args['label_for']] ) && $options[$args['label_for']] == "1" )  ? 'checked' : ''; ?>>
            <?php _e( 'Delete moives after uninstall', 'gcmovie' ) ?>
        </label>
        <?php
    }

    function sanitize( $input ) {
        $new_input = array();

        $name = 'delete_movies_after_uninstall';
        $new_input[$name] = ( isset( $input[$name] ) && $input[$name] == '1' ) ? '1' : '0';
        return $new_input;
    }

    function show_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Add error/update messages

        // Check if the user have submitted the settings,
        // WordPress will add the "settings-updated" $_GET parameter to the url
        if ( isset( $_GET['settings-updated'] ) ) {
            // Add settings saved message with the class of "updated"
            add_settings_error( 'gcmovie_settings', 'gcmovie_message', __( 'Settings Saved', 'gcmovie' ), 'updated' );
        }

        // Show error/update messages
        settings_errors( 'gcmoive_settings' );
        ?>

        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="options.php" method="post">
        <?php
        settings_fields( 'gcmovie_settings' );              // Output security fields for the registered setting "gcmovie_settings".
        do_settings_sections( 'gcmovie_settings' );         // Output setting sections and their fields registed before.
        submit_button( __( 'Save Settings', 'gcmovie' ) );  // Output save settings button.
        ?>
            </form>
        </div>
        <?php
    }
}

GCMovie_Setting::init();
