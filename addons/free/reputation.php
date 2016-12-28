<?php
/**
 * Award reputation to user based on activities.
 *
 * @author    Rahul Aryan <support@anspress.io>
 * @copyright 2014 AnsPress.io & Rahul Aryan
 * @license   GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link      https://anspress.io
 * @package   WordPress/AnsPress/Email
 *
 * Addon Name:    Reputation
 * Addon URI:     https://anspress.io
 * Description:   Award reputation to user based on activities.
 * Author:        Rahul Aryan
 * Author URI:    https://anspress.io
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Reputation hooks.
 */
class AnsPress_Reputation_Hooks {

	/**
	 * Init class.
	 */
	public static function init() {
		SELF::register_default_events();
		anspress()->add_action( 'ap_option_groups', __CLASS__, 'load_options' );
		anspress()->add_action( 'wp_ajax_ap_save_events', __CLASS__, 'ap_save_events' );
		anspress()->add_action( 'ap_after_new_question', __CLASS__, 'new_question', 10, 2 );
		anspress()->add_action( 'ap_after_new_answer', __CLASS__, 'new_answer', 10, 2 );
		anspress()->add_action( 'ap_untrash_question', __CLASS__, 'new_question', 10, 2 );
		anspress()->add_action( 'ap_trash_question', __CLASS__, 'trash_question', 10, 2 );
		anspress()->add_action( 'ap_untrash_answer', __CLASS__, 'new_answer', 10, 2 );
		anspress()->add_action( 'ap_trash_answer', __CLASS__, 'trash_answer', 10, 2 );
		anspress()->add_action( 'ap_select_answer', __CLASS__, 'select_answer' );
		anspress()->add_action( 'ap_unselect_answer', __CLASS__, 'unselect_answer' );
		anspress()->add_action( 'ap_vote_up', __CLASS__, 'vote_up' );
		anspress()->add_action( 'ap_vote_down', __CLASS__, 'vote_down' );
		anspress()->add_action( 'ap_undo_vote_up', __CLASS__, 'undo_vote_up' );
		anspress()->add_action( 'ap_undo_vote_down', __CLASS__, 'undo_vote_down' );
		anspress()->add_action( 'ap_publish_comment', __CLASS__, 'new_comment' );
		anspress()->add_action( 'ap_unpublish_comment', __CLASS__, 'delete_comment' );
	}

	/**
	 * Register reputation options
	 */
	public static function load_options() {
		ap_register_option_group( 'reputation', __( 'Reputation', 'anspress-question-answer' ) );
		ap_register_option_section( 'reputation', 'events', __( 'Events', 'anspress-question-answer' ), 'ap_option_events_view' );
	}

	/**
	 * Register default reputation events.
	 */
	public static function register_default_events() {
		ap_register_reputation_event( 'ask', 2, __( 'Asking', 'anspress-question-answer' ), __( 'Points awarded when user asks a question', 'anspress-question-answer' ) );

		ap_register_reputation_event( 'answer', 5, __( 'Answering', 'anspress-question-answer' ), __( 'Points awarded when user answer a question', 'anspress-question-answer' ) );

		ap_register_reputation_event( 'comment', 2, __( 'Commenting', 'anspress-question-answer' ), __( 'Points awarded when user comment on question or answer', 'anspress-question-answer' ) );

		ap_register_reputation_event( 'select_answer', 1, __( 'Selecting an Answer', 'anspress-question-answer' ), __( 'Points awarded when user select an answer for thier question', 'anspress-question-answer' ) );

		ap_register_reputation_event( 'best_answer', 10, __( 'Answer selected as best', 'anspress-question-answer' ), __( 'Points awarded when user\'s answer selected as best', 'anspress-question-answer' ) );

		ap_register_reputation_event( 'received_vote_up', 1, __( 'Received up vote', 'anspress-question-answer' ), __( 'Points awarded when user receive an upvote', 'anspress-question-answer' ) );

		ap_register_reputation_event( 'received_vote_down', -2, __( 'Received down vote', 'anspress-question-answer' ), __( 'Points awarded when user receive a down vote', 'anspress-question-answer' ) );

		ap_register_reputation_event( 'given_vote_up', 0, __( 'Gives an up vote', 'anspress-question-answer' ), __( 'Points taken from user when they give a vote up', 'anspress-question-answer' ) );

		ap_register_reputation_event( 'given_vote_down', -1, __( 'Gives down vote', 'anspress-question-answer' ), __( 'Points taken from user when user give a down vote', 'anspress-question-answer' ) );
	}

	/**
	 * Save reputation events.
	 */
	public static function ap_save_events() {
		check_ajax_referer( 'ap-save-events', '__nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		$events_point = ap_isset_post_value( 'events', 'r' );
		$points = [];

		foreach ( ap_get_reputation_events() as $slug => $event ) {
			if ( isset( $events_point[ $slug ] ) && (int) $events_point[ $slug ] !== (int) $event['points'] ) {
				$points[ sanitize_text_field( $slug ) ] = (int) $events_point[ $slug ];
			}
		}

		update_option( 'anspress_reputation_events', $points );

		echo '<div class="notice notice-success is-dismissible"><p>' . esc_attr__( 'Successfully updated reputation points!', 'anspress-question-answer' ) . '</p></div>';

		wp_die();
	}

	/**
	 * Add reputation for user for new question.
	 *
	 * @param integer $post_id Post ID.
	 */
	public static function new_question( $post_id, $_post ) {
		ap_insert_reputation( 'ask', $post_id, $_post->post_author );
	}

	/**
	 * Add reputation for new answer.
	 *
	 * @param integer $post_id Post ID.
	 */
	public static function new_answer( $post_id, $_post ) {
		ap_insert_reputation( 'answer', $post_id, $_post->post_author );
	}

	/**
	 * Update reputation when a question is deleted.
	 *
	 * @param integer $post_id Post ID.
	 * @param object  $_post Post object.
	 */
	public static function trash_question( $post_id, $_post ) {
		ap_delete_reputation( 'ask', $post_id, $_post->post_author );
	}

	/**
	 * Update reputation when a answer is deleted.
	 *
	 * @param integer $post_id Post ID.
	 * @param object  $_post Post object.
	 */
	public static function trash_answer( $post_id, $_post ) {
		ap_delete_reputation( 'answer', $post_id, $_post->post_author );
	}

	/**
	 * Award reputation when best answer selected.
	 *
	 * @param object $_post Post object.
	 */
	public static function select_answer( $_post ) {
		ap_insert_reputation( 'best_answer', $_post->ID, $_post->post_author );
		$question = get_post( $_post->post_parent );

		// Award select answer points to question author only.
		if ( get_current_user_id() === $question->post_author ) {
			ap_insert_reputation( 'select_answer', $_post->ID );
		}
	}

	/**
	 * Award reputation when user get an upvote.
	 *
	 * @param object $_post Post object.
	 */
	public static function unselect_answer( $_post ) {
		ap_delete_reputation( 'best_answer', $_post->ID, $_post->post_author );
		$question = get_post( $_post->post_parent );
		ap_delete_reputation( 'select_answer', $_post->ID, $question->post_author );
	}

	/**
	 * Award reputation when user recive an up vote.
	 *
	 * @param integer $post_id Post ID.
	 */
	public static function vote_up( $post_id ) {
		$_post = get_post( $post_id );
		ap_insert_reputation( 'received_vote_up', $_post->ID, $_post->post_author );
		ap_insert_reputation( 'given_vote_up', $_post->ID );
	}

	/**
	 * Award reputation when user recive an down vote.
	 *
	 * @param integer $post_id Post ID.
	 */
	public static function vote_down( $post_id ) {
		$_post = get_post( $post_id );
		ap_insert_reputation( 'received_vote_down', $_post->ID, $_post->post_author );
		ap_insert_reputation( 'given_vote_down', $_post->ID );
	}

	/**
	 * Award reputation when user recive an up vote.
	 *
	 * @param integer $post_id Post ID.
	 */
	public static function undo_vote_up( $post_id ) {
		$_post = get_post( $post_id );
		ap_delete_reputation( 'received_vote_up', $_post->ID, $_post->post_author );
		ap_delete_reputation( 'given_vote_up', $_post->ID );
	}

	/**
	 * Award reputation when user recive an down vote.
	 *
	 * @param integer $post_id Post ID.
	 */
	public static function undo_vote_down( $post_id ) {
		$_post = get_post( $post_id );
		ap_delete_reputation( 'received_vote_down', $_post->ID, $_post->post_author );
		ap_delete_reputation( 'given_vote_down', $_post->ID );
	}

	/**
	 * Award reputation on new comment.
	 *
	 * @param  object $comment WordPress comment object
	 * @return void
	 */
	public static function new_comment( $comment ) {
		ap_insert_reputation( 'comment', $comment->comment_ID, $comment->user_id );
	}

	/**
	 * Undo reputation on deleting comment.
	 *
	 * @param  object $comment
	 * @return void
	 */
	public static function delete_comment( $comment ) {
		ap_delete_reputation( 'comment', $comment->comment_ID, $comment->user_id );
	}
}

AnsPress_Reputation_Hooks::init();

/**
 * Option event view.
 */
function ap_option_events_view() {
	include ANSPRESS_DIR . '/admin/views/reputation-events.php';
}

