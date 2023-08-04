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
 * Class for AnsPress base page shortcode.
 *
 * @since unknown
 * @since 4.2.0 Fixed: CS bugs.
 */
class AnsPress_Question_Shortcode {
	/**
	 * Instance of this class.
	 *
	 * @var AnsPress_Question|null
	 */
	protected static $instance = null;

	/**
	 * Return singleton instance of this class.
	 *
	 * @return AnsPress_Question
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Control the output of [question] shortcode
	 *
	 * @param  array  $atts Attributes.
	 * @param  string $content Content.
	 * @return string
	 * @since 2.0.0
	 */
	public function anspress_question_sc( $atts, $content = '' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		ob_start();
		echo '<div id="anspress" class="ap-eq">';

		/**
		 * Action is fired before loading AnsPress body.
		 */
		do_action( 'ap_before_question_shortcode' );

		$id = ! empty( $atts['ID'] ) ? absint( $atts['ID'] ) : absint( $atts['id'] );

		$questions = ap_get_question( $id );

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
		} else {
			esc_attr_e( 'Invalid or non existing question id.', 'anspress-question-answer' );
		}

		echo '</div>';
		wp_reset_postdata();

		return ob_get_clean();
	}
}
