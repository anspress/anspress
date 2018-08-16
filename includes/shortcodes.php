<?php
/**
 * AnsPress Shortcodes.
 *
 * @author Rahul Aryan <rah12@live.com>
 * @package AnsPress
 * @subpackage Shortcodes
 * @since 4.2.0
 */

namespace AnsPress;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AnsPress Shortcode Class.
 *
 * @since 4.2.0
 */
class Shortcodes {
	/**
	 * The shortcodes list.
	 *
	 * @var array
	 */
	public $codes = array();

	/**
	 * Refers to a single instance of this class.
	 *
	 * @var null|object
	 */
	private static $instance = null;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @return \AnsPress\Shortcodes A single instance of this class.
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * CLass constructor.
	 *
	 * @since 4.2.0
	 */
	private function __construct() {
		$this->codes = array(
			'anspress_question' => [ $this, 'display_question' ],
		);
	}

	/**
	 * Register the AnsPress shortcodes
	 *
	 * @since 4.2.0
	 */
	public function add_shortcodes() {
		foreach ( (array) $this->codes as $code => $function ) {
			add_shortcode( $code, $function );
		}
	}

	/**
	 * Unset some globals in the $bbp object that hold query related info.
	 */
	private function unset_globals() {
		$ap = anspress();

		// Unset global queries
		$ap->questions_query = new \WP_Query();

		// Unset global ID's
		$ap->current_question_id = 0;
		$ap->current_answer_id   = 0;

		// Reset the post data
		wp_reset_postdata();
	}

	/**
	 * Start an output buffer.
	 *
	 * This is used to put the contents of the shortcode into a variable rather
	 * than outputting the HTML at run-time. This allows shortcodes to appear
	 * in the correct location in the_content() instead of when it's created.
	 */
	private function start( $query_name = '' ) {
		// Set query name
		set_query_var( '_ap_query_name', $query_name );

		// Start output buffer
		ob_start();
		echo '<div id="anspress" class="anspress">';
	}

	/**
	 * Return the contents of the output buffer and flush its contents.
	 */
	private function end() {

		// Unset globals
		$this->unset_globals();

		// Reset the query name
		set_query_var( '_ap_query_name', '' );
		echo '</div>';

		// Return and flush the output buffer
		return ob_get_clean();
	}

	/**
	 * Display an index of all visible root level forums in an output buffer
	 * and return to ensure that post/page contents are displayed first.
	 *
	 * @since bbPress (r3031)
	 *
	 * @param array $attr
	 * @param string $content
	 * @uses bbp_has_forums()
	 * @uses get_template_part()
	 * @return string
	 */
	public function display_archive() {
		// Unset globals
		$this->unset_globals();

		// Start output buffer
		$this->start( 'ap_archive' );

		ap_get_template_part( 'content-archive-question' );

		// Return contents of output buffer
		return $this->end();
	}

	/**
	 * Render question permissions message.
	 *
	 * @param object $_post Post object.
	 * @return string
	 * @since 4.1.0
	 */
	private function question_permission_msg( $_post ) {
		$msg = false;

		// Check if user is allowed to read this question.
		if ( ! ap_user_can_read_question( $_post->ID ) ) {
			if ( 'moderate' === $_post->post_status ) {
				$msg = __( 'This question is awaiting moderation and cannot be viewed. Please check back later.', 'anspress-question-answer' );
			} else {
				$msg = __( 'Sorry! you are not allowed to read this question.', 'anspress-question-answer' );
			}
		} elseif ( 'future' === $_post->post_status && ! ap_user_can_view_future_post( $_post ) ) {
			$time_to_publish = human_time_diff( strtotime( $_post->post_date ), current_time( 'timestamp', true ) );

			$msg = '<strong>' . sprintf(
				// Translators: %s contain time to publish.
				__( 'Question will be published in %s', 'anspress-question-answer' ),
				$time_to_publish
			) . '</strong>';

			$msg .= '<p>' . esc_attr__( 'This question is not published yet and is not accessible to anyone until it get published.', 'anspress-question-answer' ) . '</p>';
		}

		/**
		 * Filter single question page permission message.
		 *
		 * @param string $msg Message.
		 * @since 4.1.0
		 */
		$msg = apply_filters( 'ap_question_page_permission_msg', $msg );

		return $msg;
	}

	/**
	 * Output single question page.
	 *
	 * @since 4.2.0
	 */
	public function display_question( $attr = [], $content = '' ) {
		$ap = anspress();

		$attr = wp_parse_args( $attr, array(
			'id' => get_question_id(),
		) );

		// Unset globals
		$this->unset_globals();
		$question_id = $ap->current_question_id = $attr['id'];

		// Reset the queries if not in theme compat
		if ( ! ap_is_theme_compat_active() ) {
			// Reset necessary question_query.
			$ap->question_query->query_vars['post_type'] = 'question';
			$ap->question_query->in_the_loop             = true;
			$ap->question_query->post                    = get_post( $question_id );
		}

		// Start output buffer
		$this->start( 'single-question' );

		include ap_get_theme_location( 'content-single-question.php' );

		/**
		 * An action triggered after rendering single question page.
		 *
		 * @since 0.0.1
		 */
		do_action( 'ap_after_question' );

		// Return contents of output buffer
		return $this->end();
	}
}
