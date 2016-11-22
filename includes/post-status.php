<?php

/**
 * Post status related codes
 *
 * @link https://anspress.io
 * @since 2.0.1
 * @license GPL2+
 * @package AnsPress
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class AnsPress_Post_Status {

	/**
	 * Register post status for question and answer CPT
	 */
	public static function register_post_status() {
		 register_post_status( 'moderate', array(
			  'label'                     => __( 'Moderate', 'anspress-question-answer' ),
			  'public'                    => true,
			  'show_in_admin_all_list'    => false,
			  'show_in_admin_status_list' => true,
			  'label_count'               => _n_noop( 'Moderate <span class="count">(%s)</span>', 'Moderate <span class="count">(%s)</span>', 'anspress-question-answer' ),
		 ) );

		 register_post_status( 'private_post', array(
			  'label'                     => __( 'Private', 'anspress-question-answer' ),
			  'public'                    => true,
			  'show_in_admin_all_list'    => false,
			  'show_in_admin_status_list' => true,
			  'label_count'               => _n_noop( 'Private Post <span class="count">(%s)</span>', 'Private Post <span class="count">(%s)</span>', 'anspress-question-answer' ),
		 ) );
	}

	/**
	 * Handle change post status ajax request.
	 *
	 * @since 2.1
	 */
	public static function change_post_status() {
		$args = ap_sanitize_unslash( 'args', 'request' );

		if ( empty( $args ) ) {
			ap_ajax_json('something_wrong' );
		}

		$post_id = (int) $args[0];
		$status = $args[1];

		// Die if not a defined post status.
	   	if ( ! in_array( $status, [ 'publish', 'moderate', 'private_post'], true ) ) {
	   		ap_ajax_json( 'something_wrong' );
	   	}

		// Check if user has permission else die.
		if ( ! is_user_logged_in() || ! ap_verify_nonce( 'change_post_status_'.$post_id ) || ! ap_user_can_change_status( $post_id ) ) {
			ap_ajax_json('no_permission' );
		}

		$post = ap_get_post( $post_id );

	   	// Check if post is question or answer and new post status is not same as old.
	   	if ( ! in_array( $post->post_type, [ 'question', 'answer' ] ) || $post->post_status == $status ) {
			ap_ajax_json('something_wrong' );
		}

	   	$update_data = array();
	   	$update_data['post_status'] = $status;
		$update_data['ID'] = $post->ID;
		wp_update_post( $update_data );

		// Unselect as best answer if moderate.
		if ( 'answer' == $post->post_type && 'moderate' == $status && ap_have_answer_selected( $post->post_parent ) ) {
			ap_unselect_answer( $post->ID );
		}

		do_action( 'ap_post_status_updated', $post->ID );

		$activity_type = 'moderate' === $post->post_status ? 'approved_' . $post->post_type : 'changed_status';
		ap_update_post_activity_meta( $post_id, $activity_type, get_current_user_id() );

		ob_start();
		ap_post_status_description( $post->ID );
		$html = ob_get_clean();

		ap_ajax_json( array(
			'action' 		=> 'status_updated',
			'message' 		=> 'status_updated',
			'do' 			=> array(
				'remove_if_exists' => '#ap_post_status_desc_'.$post->ID,
				'toggle_active_class' => array( '#ap_post_status_toggle_'.$post->ID, '.'.$status ),
				'append_before' => '#ap_post_actions_'.$post->ID,
			),
			'html' 			=> $html,
		));
	}
}

/**
 * Post status message.
 *
 * @param mixed $post_id Post.
 * @return string
 * @since 4.0.0
 */
function ap_get_post_status_message( $post_id = false ) {
	$post = ap_get_post( $post_id );
	$post_type = 'question' === $post->post_type ? __( 'Question', 'anspress-question-answer' ) : __( 'Answer', 'anspress-question-answer' );

	$ret = '';

	if ( is_private_post( $post_id ) ) {
		$ret = sprintf( __( '%s is marked as a private, only admin and post author can see.', 'anspress-question-answer' ), $post->post_type );
	} elseif ( is_post_waiting_moderation( $post_id ) ) {
		$ret = sprintf( __( '%s is awaiting for approval by moderator.', 'anspress-question-answer' ), $post->post_type );
	} elseif ( is_post_closed( $post_id ) ) {
		$ret = __( 'Question is closed for new answers', 'anspress-question-answer' );
	} elseif ( 'trash' === $post->post_status ) {
		$ret = sprintf( __( '%s has been trashed, you can delete it permanently from wp-admin.', 'anspress-question-answer' ), $post->post_type );
	}

	return apply_filters( 'ap_get_post_status_message',  $ret, $post_id );
}

/**
 * Return description of a post status.
 *
 * @param  boolean|integer $post_id Post ID.
 */
function ap_post_status_message( $post_id = false ) {
	$ret = '<post-message>';
	$msg = ap_get_post_status_message( $post_id );

	if ( ! empty( $msg ) ) {
		$ret .= '<div class="ap-pmsg">' . $msg . '</div>';
	}

	$ret .= '</post-message>';

	return $ret;
}
