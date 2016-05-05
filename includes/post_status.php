<?php

/**
 * Post status related codes
 *
 * @link http://anspress.io
 * @since 2.0.1
 * @license GPL2+
 * @package AnsPress
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class AnsPress_Post_Status
{

	/**
	 * Register post status for question and answer CPT
	 */
	public static function register_post_status() {

		register_post_status( 'closed', array(
			  'label'                     => __( 'Closed', 'anspress-question-answer' ),
			  'public'                    => true,
			  'show_in_admin_all_list'    => false,
			  'show_in_admin_status_list' => true,
			  'label_count'               => _n_noop( 'Closed <span class="count">(%s)</span>', 'Moderate <span class="count">(%s)</span>', 'anspress-question-answer' ),
		 ) );

		 register_post_status( 'moderate', array(
			  'label'                     => __( 'Moderate', 'anspress-question-answer' ),
			  'public'                    => true,
			  'show_in_admin_all_list'    => false,
			  'show_in_admin_status_list' => true,
			  'label_count'               => _n_noop( 'Moderate <span class="count">(%s)</span>', 'Moderate <span class="count">(%s)</span>', 'anspress-question-answer' ),
		 ) );

		 register_post_status( 'private_post', array(
			  'label'                     => __( 'Private Post', 'anspress-question-answer' ),
			  'public'                    => true,
			  'show_in_admin_all_list'    => false,
			  'show_in_admin_status_list' => true,
			  'label_count'               => _n_noop( 'Private Post <span class="count">(%s)</span>', 'Private Post <span class="count">(%s)</span>', 'anspress-question-answer' ),
		 ) );
	}

	/**
	 * Handle change post status ajax request.
	 * @since 2.1
	 */
	public static function change_post_status() {
		$args = ap_sanitize_unslash( 'args', 'request' );
		var_dump($args );
		if ( empty( $args ) ) {
			ap_ajax_json('something_wrong' );
		}

	    $post_id = (int) $args[0];
	    $status = $args[1];

	    // Die if not a defined post status.
	   	if ( ! in_array( $status, [ 'publish', 'moderate', 'private_post', 'closed' ] ) ) {
	   		ap_ajax_json('something_wrong' );
	   	}

	    // Check if user has permission else die.
	    if ( ! is_user_logged_in() || ! ap_verify_nonce( 'change_post_status_'.$post_id ) || ! ap_user_can_change_status( $post_id ) ) {
	        ap_ajax_json('no_permission' );
	    }

		$post = get_post( $post_id );

	   	// Check if post is question or answer and new post status is not same as old.
	   	if ( ! in_array( $post->post_type, [ 'question', 'answer' ] ) || $post->post_status == $status ) {
			ap_ajax_json('something_wrong' );
		}

	   	$update_data = array();

	   	$update_data['post_status'] = $status;

		// Unregister history action for edit.
		remove_action( 'ap_after_new_answer', array( 'AP_History', 'new_answer' ) );
		remove_action( 'ap_after_new_question', array( 'AP_History', 'new_question' ) );

		$update_data['ID'] = $post->ID;
		wp_update_post( $update_data );

		// ap_add_history( get_current_user_id(), $post_id, '', 'status_updated' );
		add_action( 'ap_post_status_updated', $post->ID );

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
