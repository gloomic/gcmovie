<?php
/*
 * Widget for GCMovie
 */

if ( ! defined( 'WPINC' ) ) {
    die( 'No direct access.' );
}

class GCMovie_Widget extends WP_Widget {

    function __construct() {
        parent::__construct(
			'gcmovie_widget',
			'GCMovie Widget',
			array( 'description' => 'Display movies' )
		);

		add_action( 'widgets_init', function() {
            register_widget( 'GCMovie_Widget' );
        } );
    }

    static function init() {
        new GCMovie_Widget;
    }

    /**
     * Handle for widget display
     * $args, set by the widget area
     * $instance, saved values
     */
    function widget( $args, $instance ) {
        echo $args['before_widget'];

        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        }

        echo GCMovie_Shortcode::parse_shortcode( $instance );

        echo $args['after_widget'];
	}

    /**
     * Handler for widget options settings
     */
    function form( $instance ) {
		$id = isset( $instance['id'] ) ? $instance['id'] : 0;
		$show_thumbnail = isset( $instance['show_thumbnail'] ) ? $instance['show_thumbnail'] : true ;

		?>
		<p>Movie Options</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_name( 'id' ) ); ?>">Movies</label>
			<select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'id' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'id' ) ); ?>" value="<?php echo $id; ?>">">
				<?php
				$args = array(
					'posts_per_page'	=> -1,
					'post_type'			=> 'gcmovie'
				);
				$posts = get_posts( $args );
				if ( $posts ) {
					foreach ( $posts as $p ) {
						if ( $p->ID == $id ) {
							echo '<option selected value="' . $p->ID . '">' . get_the_title( $p->ID ) . '</option>';
						} else {
							echo '<option value="' . $p->ID . '">' . get_the_title( $p->ID ) . '</option>';
						}
					}
				}
				?>
			</select>
		</p>
        <p>
            <input class="widefat" type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_thumbnail' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_thumbnail' ) ); ?>" value="show" checked="<?php echo $show_thumbnail ? 'true' : 'false'; ?>">
            <label for="<?php echo esc_attr( $this->get_field_id( 'show_thumbnail' ) ); ?>" >Show thumnail</label>
        </p>
		<?php
	}

    /**
     * Handler for widget options saving
     */
    function update( $new_instance, $old_instance ) {
		$instance = array();

		$instance['id'] = $new_instance['id'];
		$instance['show_thumbnail'] = isset( $new_instance['show_thumbnail'] ) ? true : false;

		return $instance;
	}
}

GCMovie_Widget::init();
