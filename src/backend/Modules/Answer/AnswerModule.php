<?php
/**
 * Answer module.
 *
 * @package AnsPress
 * @since   5.0.0
 */

namespace AnsPress\Modules\Answer;

use AnsPress\Classes\AbstractModule;
use WP_REST_Server;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

/**
 * Answer module class.
 */
class AnswerModule extends AbstractModule {
	/**
	 * Register hooks.
	 */
	public function register_hooks() {
		add_action( 'save_post_answer', array( $this, 'updateQuestionAnswerCount' ), 99999, 3 );
	}

	/**
	 * Count answers of a question and update the count.
	 *
	 * @param mixed $answerid Answer id.
	 * @param mixed $answer Post object.
	 * @param bool  $update Is updating post.
	 * @return void
	 */
	public function updateQuestionAnswerCount( $answerid, $answer, bool $update ) {
		if ( wp_is_post_autosave( $answer ) || wp_is_post_revision( $answer ) ) {
			return;
		}

		ap_update_answers_count( $answer->post_parent );

		// Add activity.
		$activity_type = $update ? 'edit_answer' : 'new_answer';

		$qameta = array(
			'last_updated' => current_time( 'mysql' ),
			'activities'   => array(
				'type'    => $activity_type,
				'user_id' => $answer->post_author,
				'date'    => current_time( 'mysql' ),
			),
		);

		ap_insert_qameta( $answerid, $qameta );

		ap_update_qameta_terms( $answerid );
	}
}
