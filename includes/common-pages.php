<?php
/**
 * Class for base page
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-3.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 */

/**
 * Handle output of all default pages of AnsPress
 */
class AnsPress_Common_Pages
{
	/**
	 * Register all pages of AnsPress
	 */
	public static function register_common_pages() {
		ap_register_page( 'base', ap_opt( 'base_page_title' ), array( __CLASS__, 'base_page' ) );
		ap_register_page( ap_opt( 'question_page_slug' ), __( 'Question', 'anspress-question-answer' ), array( __CLASS__, 'question_page' ), false );
		ap_register_page( ap_opt( 'ask_page_slug' ), __( 'Ask', 'anspress-question-answer' ), array( __CLASS__, 'ask_page' ) );
		ap_register_page( 'edit', __( 'Edit', 'anspress-question-answer' ), array( __CLASS__, 'edit_page' ), false );
		ap_register_page( 'search', __( 'Search', 'anspress-question-answer' ), array( __CLASS__, 'search_page' ), false );
		ap_register_page( 'activity', __( 'Activity Feed', 'anspress-question-answer' ), array( __CLASS__, 'activity_page' ) );
	}

	/**
	 * Layout of base page
	 */
	public static function base_page() {
		global $questions, $wp;
		$query = $wp->query_vars;

		$tax_relation = !empty( $wp->query_vars['ap_tax_relation'] ) ? $wp->query_vars['ap_tax_relation'] : 'OR';
		$args = array();
		$args['tax_query'] = array( 'relation' => $tax_relation );

		if( !empty( get_query_var( 'ap_sortby' ) ) ){
			$args['sortby'] = get_query_var( 'ap_sortby' );
		}

		/**
		 * FILTER: ap_main_questions_args
		 * Filter main question list args
		 * @var array
		 */
		$args = apply_filters( 'ap_main_questions_args', $args );

		$questions = ap_get_questions( $args );
		ap_get_template_part( 'base' );
	}

	/**
	 * Output single question page
	 * @return void
	 */
	public static function question_page() {

		// Set Header as 404 if question id is not set.
		if ( false === get_question_id() ) {
			SELF::set_404();
			return;
		}

		// Check if user is allowed to read this question.
		if ( ! ap_user_can_read_question( get_question_id() ) ) {
			printf(
				'<div class="ap-no-permission">%s</div>',
				__('Sorry! you are not allowed to read this question.', 'anspress-question-answer' )
			);

			return;
		}

		global $questions;

		$questions = ap_get_question( get_question_id() );

		if ( ap_have_questions() ) {
			/**
			 * Set current question as global post
			 * @since 2.3.3
			 */

			while ( ap_questions() ) : ap_the_question();
				global $post;
				setup_postdata( $post );
			endwhile;

			if ( 'future' == $post->post_status ) {
				echo '<div class="future-notice">';
				/**
				 * Filter to modify future post notice. If filter does not return false
				 * then retunrd string will be shown.
				 * @param  boolean $notice 		False by default.
				 * @param  object  $question   	Post object.
				 * @return boolean|string
				 * @since  2.4.7
				 */
				$notice = apply_filters( 'ap_future_post_notice', false, $post );
				if ( false === $notice ) {
					$time_to_publish = human_time_diff( strtotime( $post->post_date ), current_time( 'timestamp', true ) );
					echo '<strong>' .sprintf(__('Question will be publish in %s', 'anspress-question-answer' ), $time_to_publish ).'</strong>';
					echo '<p>' .__('This question is in waiting queue and is not accessible by anyone until it get published.', 'anspress-question-answer' ).'</p>';
				} else {
					echo $notice;
				}

				echo '</div>';
			}

			include( ap_get_theme_location( 'question.php' ) );
			wp_reset_postdata();

		} else {
			SELF::set_404();
		}

	}

	/**
	 * Output ask page template
	 */
	public static function ask_page() {
		include ap_get_theme_location( 'ask.php' );
	}

	/**
	 * Output edit page template
	 */
	public static function edit_page() {
		$post_id = (int) get_query_var( 'edit_post_id' );
		if ( ! ap_user_can_edit_question( $post_id ) ) {
				echo '<p>'.esc_attr__( 'You do not have permission to access this page.', 'anspress-question-answer' ).'</p>';
				return;
		} else {
			global $editing_post;
			$editing_post = get_post( $post_id );

			// Include theme file.
			include ap_get_theme_location( 'edit.php' );
		}
	}

	/**
	 * Load search page template
	 */
	public static function search_page() {
		global $questions;
		$keywords   = ap_sanitize_unslash( 'ap_s', 'query_var' );
		$type       = ap_sanitize_unslash( 'type', 'request' );

		if ( '' == $type ) {
			$questions = ap_get_questions( array( 's' => $keywords ) );
			include( ap_get_theme_location( 'search.php' ) );
		} elseif ( 'user' == $type && ap_opt( 'enable_users_directory' ) ) {
			global $ap_user_query;
			$ap_user_query = ap_has_users( array( 'search' => $keywords, 'search_columns' => array( 'user_login', 'user_email', 'user_nicename' ) ) );
			include( ap_get_theme_location( 'users/users.php' ) );
		}
	}

	/**
	 * Activity page template loading.
	 */
	public static function activity_page() {
		global $ap_activities;
	    $ap_activities = ap_get_activities( array( 'per_page' => 20 ) );

		include( ap_get_theme_location( 'activity/index.php' ) );
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

