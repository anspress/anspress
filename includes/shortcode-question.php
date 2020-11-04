<?php
/**
 * Class for AnsPress embed question shortcode
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-2.0+
 * @link      https://anspress.net
 * @copyright 2014 Rahul Aryan
 */

/**
 * Class for AnsPress base page shortcode
 */
class AnsPress_Question_Shortcode {

	protected static $instance = null;

	public static function get_instance() {

		// create an object
		null === self::$instance && self::$instance = new self();

		return self::$instance; // return the object
	}

	/**
	 * Control the output of [question] shortcode
	 *
	 * @param  string $content
	 * @return string
	 * @since 2.0.0-beta
	 */
	public function anspress_question_sc( $atts, $content = '' ) {

		ob_start();
		echo '<div id="anspress" class="ap-eq">';

		/**
		 * ACTION: ap_before_question_shortcode
		 * Action is fired before loading AnsPress body.
		 */
		do_action( 'ap_before_question_shortcode' );

		$questions = ap_get_question( $atts['id'] );

		if ( $questions->have_posts() ) {
			/**
			 * Set current question as global post
			 *
			 * @since 2.3.3
			 */

			while ( $questions->have_posts() ) :
				$questions->the_post();
				include ap_get_theme_location( 'shortcode/question.php' );
			endwhile;
		}

		echo '</div>';
		wp_reset_postdata();

		return ob_get_clean();
	}

}

