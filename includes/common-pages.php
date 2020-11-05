<?php
/**
 * Class for base page
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-3.0+
 * @link      https://anspress.net
 * @copyright 2014 Rahul Aryan
 */

/**
 * Handle output of all default pages of AnsPress
 */
class AnsPress_Common_Pages {
	/**
	 * Register all pages of AnsPress
	 */
	public static function register_common_pages() {
		ap_register_page( 'base', ap_opt( 'base_page_title' ), [ __CLASS__, 'base_page' ] );
		ap_register_page( 'question', __( 'Question', 'anspress-question-answer' ), [ __CLASS__, 'question_page' ], false );
		ap_register_page( 'ask', __( 'Ask a Question', 'anspress-question-answer' ), [ __CLASS__, 'ask_page' ] );
		ap_register_page( 'search', __( 'Search', 'anspress-question-answer' ), [ __CLASS__, 'search_page' ], false );
		ap_register_page( 'edit', __( 'Edit Answer', 'anspress-question-answer' ), [ __CLASS__, 'edit_page' ], false );
		ap_register_page( 'activities', __( 'Activities', 'anspress-question-answer' ), [ __CLASS__, 'activities_page' ], false );
	}

	/**
	 * Layout of base page.
	 */
	public static function base_page() {
		global $wp;

		$keywords          = get_search_query();
		$tax_relation      = ! empty( $wp->query_vars['ap_tax_relation'] ) ? $wp->query_vars['ap_tax_relation'] : 'OR';
		$args              = array();
		$args['tax_query'] = array( 'relation' => $tax_relation );

		if ( false !== $keywords ) {
			$args['s'] = $keywords;
		}

		if ( is_front_page() ) {
			$args['paged'] = get_query_var( 'page' );
		}

		// Set post parent.
		if ( get_query_var( 'post_parent', false ) ) {
			$args['post_parent'] = get_query_var( 'post_parent' );
		}

		if ( ap_sanitize_unslash( 'unpublished', 'r' ) && is_user_logged_in() ) {
			$args['ap_show_unpublished'] = true;
		}

		/**
		 * Filter main question list query arguments.
		 *
		 * @param array $args Wp_Query arguments.
		 */
		$args = apply_filters( 'ap_main_questions_args', $args );

		anspress()->questions = new Question_Query( $args );
		ap_get_template_part( 'archive' );
	}

	/**
	 * Render question permissions message.
	 *
	 * @param object $_post Post object.
	 * @return string
	 * @since 4.1.0
	 */
	private static function question_permission_msg( $_post ) {
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
	 * @since 0.0.1
	 * @since 4.1.0 Changed template file name to single-question.php to question.php.
	 * @since 4.1.3 Re-setup current post.
	 * @since 4.1.15 Add while loop.
	 */
	public static function question_page() {
		global $question_rendered, $post;

		$question_rendered = false;
		$msg               = self::question_permission_msg( $post );

		// Check if user have permission.
		if ( false !== $msg ) {
			status_header( 403 );
			echo '<div class="ap-no-permission">' . $msg . '</div>'; // WPCS: xss okay.
			$question_rendered = true;
			return;
		}

		if ( have_posts() ) {
			while ( have_posts() ) : the_post();
				include ap_get_theme_location( 'single-question.php' );
			endwhile;
		}

		/**
		 * An action triggered after rendering single question page.
		 *
		 * @since 0.0.1
		 */
		do_action( 'ap_after_question' );

		$question_rendered = true;
	}

	/**
	 * Output ask page template
	 */
	public static function ask_page() {
		$post_id = ap_sanitize_unslash( 'id', 'r', false );

		if ( $post_id && ! ap_verify_nonce( 'edit-post-' . $post_id ) ) {
			esc_attr_e( 'Something went wrong, please try again', 'anspress-question-answer' );
			return;
		}

		include ap_get_theme_location( 'ask.php' );

		/**
		 * Action called after ask page (shortcode) is rendered.
		 *
		 * @since 4.1.8
		 */
		do_action( 'ap_after_ask_page' );
	}

	/**
	 * Load search page template
	 */
	public static function search_page() {
		$keywords = ap_sanitize_unslash( 'ap_s', 'query_var', false );
		wp_safe_redirect( add_query_arg( [ 'ap_s' => $keywords ], ap_get_link_to( '/' ) ) );
	}

	/**
	 * Output edit page template
	 */
	public static function edit_page() {
		$post_id = (int) ap_sanitize_unslash( 'id', 'r' );

		if ( ! ap_verify_nonce( 'edit-post-' . $post_id ) || empty( $post_id ) || ! ap_user_can_edit_answer( $post_id ) ) {
				echo '<p>' . esc_attr__( 'Sorry, you cannot edit this answer.', 'anspress-question-answer' ) . '</p>';
				return;
		}

		global $editing_post;
		$editing_post = ap_get_post( $post_id );

		ap_answer_form( $editing_post->post_parent, true );
	}

	/**
	 * Render activities page.
	 *
	 * @return void
	 * @since 4.1.2
	 * @since 4.1.8 Added Exclude roles arguments.
	 */
	public static function activities_page() {
		$roles = array_keys( ap_opt( 'activity_exclude_roles' ) );
		$args  = [];

		if ( ! empty( $roles ) ) {
			$args['exclude_roles'] = $roles;
		}

		$activities = new AnsPress\Activity( $args );
		include ap_get_theme_location( 'activities/activities.php' );
	}

	/**
	 * If page is not found then set header as 404
	 */
	public static function set_404() {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		include ap_get_theme_location( 'not-found.php' );
	}
}

