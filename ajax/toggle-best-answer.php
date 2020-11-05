<?php
/**
 * Class used for ajax callback `ap_toggle_best_answer`.
 * This class is auto loaded by AnsPress loader on demand.
 *
 * @author Rahul Aryan <rah12@live.com>
 * @package AnsPress
 * @subpackage Ajax
 * @since 4.1.8
 */

namespace AnsPress\Ajax;

// Die if called directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The `ap_toggle_best_answer` ajax callback.
 *
 * @since 4.1.8
 */
class Toggle_Best_Answer extends \AnsPress\Classes\Ajax {
	/**
	 * Instance of this class.
	 */
	static $instance;

	/**
	 * The class constructor.
	 *
	 * Set requests and nonce key.
	 */
	protected function __construct() {
		$this->req( 'answer_id', ap_sanitize_unslash( 'answer_id', 'r' ) );
		$this->nonce_key = 'select-answer-' . $this->req( 'answer_id' );

		// Call parent.
		parent::__construct();
	}

	/**
	 * Verify user permission.
	 *
	 * @return void
	 */
	protected function verify_permission() {
		$answer_id = $this->req( 'answer_id' );

		if ( empty( $answer_id ) || ! ap_user_can_select_answer( $answer_id ) ) {
			parent::verify_permission();
		}
	}

	/**
	 * Handle ajax for logged in users.
	 *
	 * @return void
	 */
	public function logged_in() {
		$_post = ap_get_post( $this->req( 'answer_id' ) );

		// Unselect best answer if already selected.
		if ( ap_have_answer_selected( $_post->post_parent ) ) {
			ap_unset_selected_answer( $_post->post_parent );
			$this->set_success();
			$this->add_res( 'selected', false );
			$this->add_res( 'label', __( 'Select', 'anspress-question-answer' ) );
			$this->snackbar( __( 'Best answer is unselected for your question.', 'anspress-question-answer' ) );

			$this->send();
		}

		// Do not allow answer to be selected as best if status is moderate.
		if ( in_array( $_post->post_status, [ 'moderate', 'trash', 'private' ], true ) ) {
			$this->set_fail();
			$this->snackbar( __( 'This answer cannot be selected as best, update status to select as best answer.', 'anspress-question-answer' ) );

			$this->send();
		}

		// Update question qameta.
		ap_set_selected_answer( $_post->post_parent, $_post->ID );

		$this->set_success();
		$this->add_res( 'selected', true );
		$this->add_res( 'label', __( 'Unselect', 'anspress-question-answer' ) );
		$this->snackbar( __( 'Best answer is selected for your question.', 'anspress-question-answer' ) );
	}
}
