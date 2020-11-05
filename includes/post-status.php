<?php
/**
 * Post status related codes
 *
 * @link     https://anspress.net
 * @since    2.0.1
 * @license  GPL3+
 * @package  AnsPress
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
		register_post_status(
			'moderate', array(
				'label'                     => __( 'Moderate', 'anspress-question-answer' ),
				'public'                    => true,
				'show_in_admin_all_list'    => false,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Moderate <span class="count">(%s)</span>', 'Moderate <span class="count">(%s)</span>', 'anspress-question-answer' ),
			)
		);

		register_post_status(
			'private_post', array(
				'label'                     => __( 'Private', 'anspress-question-answer' ),
				'public'                    => true,
				'show_in_admin_all_list'    => false,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Private Post <span class="count">(%s)</span>', 'Private Post <span class="count">(%s)</span>', 'anspress-question-answer' ),
			)
		);
	}

	/**
	 * Handle change post status ajax request.
	 *
	 * @since 2.1
	 */
	public static function change_post_status() {
		$post_id = (int) ap_sanitize_unslash( 'post_id', 'request' );
		$status  = ap_sanitize_unslash( 'status', 'request' );

		// Check if user has permission else die.
		if ( ! is_user_logged_in() || ! in_array( $status, [ 'publish', 'moderate', 'private_post', 'trash' ], true ) || ! ap_verify_nonce( 'change-status-' . $status . '-' . $post_id ) || ! ap_user_can_change_status( $post_id ) ) {
			ap_ajax_json(
				array(
					'success'  => false,
					'snackbar' => [ 'message' => __( 'You are not allowed to change post status', 'anspress-question-answer' ) ],
				)
			);
		}

		$post                       = ap_get_post( $post_id );
		$update_data                = array();
		$update_data['post_status'] = $status;
		$update_data['ID']          = $post->ID;

		wp_update_post( $update_data );

		// Unselect as best answer if moderate.
		if ( 'answer' === $post->post_type && 'moderate' === $status && ap_have_answer_selected( $post->post_parent ) ) {
			ap_unset_selected_answer( $post->ID );
		}

		do_action( 'ap_post_status_updated', $post->ID );

		$activity_type = 'moderate' === $post->post_status ? 'approved_' . $post->post_type : 'changed_status';
		ap_update_post_activity_meta( $post_id, $activity_type, get_current_user_id() );

		ap_ajax_json(
			array(
				'success'     => true,
				'snackbar'    => [ 'message' => __( 'Post status updated successfully', 'anspress-question-answer' ) ],
				'action'      => [ 'active' => true ],
				'postmessage' => ap_get_post_status_message( $post->ID ),
				'newStatus'   => $status,
			)
		);
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
	$post      = ap_get_post( $post_id );
	$post_type = 'question' === $post->post_type ? __( 'Question', 'anspress-question-answer' ) : __( 'Answer', 'anspress-question-answer' );

	$ret = '';
	$msg = '';
	if ( is_private_post( $post_id ) ) {
		$ret = '<i class="apicon-lock"></i><span>' . sprintf( __( 'This %s is marked as a private, only admin and post author can see.', 'anspress-question-answer' ), $post_type ) . '</span>';
	} elseif ( is_post_waiting_moderation( $post_id ) ) {
		$ret = '<i class="apicon-alert"></i><span>' . sprintf( __( 'This %s is waiting for the approval by the moderator.', 'anspress-question-answer' ), $post_type ) . '</span>';
	} elseif ( is_post_closed( $post_id ) ) {
		$ret = '<i class="apicon-x"></i><span>' . __( 'Question is closed for new answers.', 'anspress-question-answer' ) . '</span>';
	} elseif ( 'trash' === $post->post_status ) {
		$ret = '<i class="apicon-trashcan"></i><span>' . sprintf( __( 'This %s has been trashed, you can delete it permanently from wp-admin.', 'anspress-question-answer' ), $post_type ) . '</span>';
	} elseif ( 'future' === $post->post_status ) {
		$ret = '<i class="apicon-clock"></i><span>' . sprintf( __( 'This %s is not published yet and is not accessible to anyone until it get published.', 'anspress-question-answer' ), $post_type ) . '</span>';
	}

	if ( ! empty( $ret ) ) {
		$msg = '<div class="ap-notice status-' . $post->post_status . ( is_post_closed( $post_id ) ? ' closed' : '' ) . '">' . $ret . '</div>';
	}

	return apply_filters( 'ap_get_post_status_message', $msg, $post_id );
}

/**
 * Return description of a post status.
 *
 * @param  boolean|integer $post_id Post ID.
 */
function ap_post_status_badge( $post_id = false ) {
	$ret = '<postmessage>';
	$msg = ap_get_post_status_message( $post_id );

	if ( ! empty( $msg ) ) {
		$ret .= $msg;
	}

	$ret .= '</postmessage>';

	return $ret;
}
