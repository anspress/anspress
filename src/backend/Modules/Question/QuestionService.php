<?php
/**
 * Answer service.
 *
 * @package AnsPress
 * @since   5.0.0
 */

namespace AnsPress\Modules\Question;

use AnsPress\Classes\AbstractService;
use AnsPress\Exceptions\GeneralException;
use WP_Query;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

/**
 * Question service class.
 */
class QuestionService extends AbstractService {

	/**
	 * Delete question.
	 *
	 * @param int $questionId Id of the question.
	 * @return bool
	 */
	public function deleteQuestion( int $questionId ) {
		$deleted = wp_delete_post( $questionId, true );

		if ( ! $deleted ) {
			return false;
		}

		return true;
	}

	/**
	 * Toggle the closed state of a question.
	 *
	 * @param int $questionId The ID of the question.
	 * @return string 'closed' or 'open'.
	 */
	public function toggleQuestionClosedState( int $questionId ): string {
		// Retrieve the question by its ID.
		$question = ap_get_post( $questionId );

		// Get the metadata for the question.
		$questionMeta = ap_get_qameta( $questionId );

		// Determine the new state by toggling the current closed state.
		$newState = ! $questionMeta->closed;

		// Update the question metadata with the new state.
		ap_insert_qameta( $questionId, array( 'closed' => $newState ) );

		// Log the state change in the activity table.
		ap_activity_add(
			array(
				'q_id'   => $question->ID,
				'action' => $newState ? 'closed_q' : 'open_q',
			)
		);

		// Return the new state as a string.
		return $newState ? 'closed' : 'open';
	}

	/**
	 * Toggle the featured state of a question.
	 *
	 * @param int $questionId The ID of the question.
	 * @return string 'featured' or 'unfeatured'.
	 * @throws GeneralException If an error occurs.
	 */
	public function toggleQuestionFeaturedState( int $questionId ): string {
		// Get the metadata for the question.
		$questionMeta = ap_get_qameta( $questionId );

		// Determine the new state by toggling the current featured state.
		$newState = ! $questionMeta->featured;

		// Update the question metadata with the new state.
		$inserted = ap_insert_qameta( $questionId, array( 'featured' => $newState ), true );

		if ( is_wp_error( $inserted ) ) {
			throw new GeneralException( esc_html( $inserted->get_error_message() ) );
		}

		ap_activity_add(
			array(
				'q_id'   => $questionId,
				'action' => 'featured',
			)
		);

		// Return the new state as a string.
		return $newState ? 'featured' : 'unfeatured';
	}
}
