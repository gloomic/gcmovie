<?php
/*
 * Shortcode for GCMovie
 */

if ( ! defined( 'WPINC' ) ) {
    die( 'No direct access.' );
}

class GCMovie_Shortcode {
    static function init() {
        $gcmovie_shortcode = new GCMovie_Shortcode;
        add_shortcode( 'gcmovie', array( 'GCMovie_Shortcode', 'parse_shortcode' ) );
    }

    /**
     * Shortcode, support the following attributes:
     * - id, (Required) The ID of the GCMovie post.
     * - show_thumbnail (Optional) Whether to show the thumbnail, the default value is true.
     */
    static function parse_shortcode( $atts ) {
        if ( ! isset( $atts['id'] ) ) {
            return '';
        }

        $shortcode_atts = shortcode_atts(
			array(
                'id'             => 0,
				'show_thumbnail' => true,
			),
			$atts,
            'gcmovie'
		);

        $post_id = intval( $shortcode_atts['id'] );
        $post = get_post( $post_id );
        $app = GCMovie::get_instance();

        ob_start();
        ?>
        <h4><?php echo $post->post_title; ?></h4>
        <div class="gcmovie-box">
        <?php
        if ( $shortcode_atts['show_thumbnail'] ) {
        ?>
            <div class="post-thumbnail top">
                <a href="<?php echo get_post_permalink( $post ); ?>">
                    <?php echo get_the_post_thumbnail( $post ); ?>
                </a>
            </div>
            <div class="bottom">
               <?php $app->display_movie_information( $post_id ); ?>
            </div>

        <?php
        } else {
            $app->display_movie_information( $post_id );
        }
        ?>
        </div>
        <?php

        return ob_get_clean();
    }
}

GCMovie_Shortcode::init();
