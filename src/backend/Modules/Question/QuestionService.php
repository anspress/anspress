<?php
/**
 * Answer service.
 *
 * @package AnsPress
 * @since   5.0.0
 */

namespace AnsPress\Modules\Question;

use AnsPress\Classes\AbstractService;
use AnsPress\Classes\Validator;
use AnsPress\Exceptions\GeneralException;
use AnsPress\Exceptions\ValidationException;
use WP_Post;
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
	 * Create a question.
	 *
	 * @param array $data The data to create the question.
	 * @return WP_Post The created question.
	 * @throws ValidationException If validation fails.
	 */
	public function createQuestion( array $data ): WP_Post {
		$validationRules = array(
			'post_title'          => 'required|string',
			'post_content'        => 'required|string',
			'post_author'         => 'required|numeric|exists:users,ID',
			'private_question'    => 'nullable|bool',
			'question_tags'       => 'nullable|array',
			'question_tags.*'     => 'nullable|numeric|exists:terms,term_id',
			'question_category'   => 'nullable|array',
			'question_category.*' => 'nullable|numeric|exists:terms,term_id',
		);

		$validationRules = apply_filters( 'anspress/question/service/create/validation_rules', $validationRules );

		// Validate the data.
		$validator = new Validator( $data, $validationRules );

		if ( $validator->fails() ) {
			throw new ValidationException( $validator->errors() ); // @codingStandardsIgnoreLine
		}

		$data = $validator->validated();

		/**
		 * Filter the data before creating a question.
		 *
		 * @param array $data The data to create the question.
		 * @since 5.0.0
		 */
		$data = apply_filters( 'anspress/question/service/create/data', $data );

		if ( empty( $data ) ) {
			throw new ValidationException(
				array(),
				esc_attr__( 'Data is empty, failed to create question.', 'anspress-question-answer' )
			);
		}

		// Check all required fields are present.
		if ( ! isset( $data['post_title'], $data['post_content'], $data['post_author'] ) ) {
			throw new ValidationException(
				array(),
				esc_attr__( 'Required fields are missing.', 'anspress-question-answer' )
			);
		}

		$postStatus = $data['post_status'] ?? 'publish';

		if ( isset( $data['post_status'] ) ) {
			$postStatus = $data['post_status'];
		}

		if ( $data['private_question'] ) {
			$postStatus = 'private_post';
		}

		$postId = wp_insert_post(
			array(
				'post_title'   => $data['post_title'],
				'post_content' => $data['post_content'],
				'post_author'  => $data['post_author'],
				'post_status'  => $postStatus,
				'post_type'    => QuestionModel::postTypeSlug(),
				'tax_input'    => array(
					'question_tag'      => $data['question_tags'] ?? array(),
					'question_category' => $data['question_category'] ?? array(),
				),
			),
			true
		);

		if ( is_wp_error( $postId ) || ! $postId ) {
			/**
			 * Fires when question creation fails.
			 *
			 * @since 5.0.0
			 */
			do_action( 'anspress/question/service/create/error', $postId );

			throw new ValidationException(
				array(),
				esc_attr__( 'Failed to create answer.', 'anspress-question-answer' )
			);
		}

		/**
		 * Fires after a question is created.
		 *
		 * @since 5.0.0
		 */
		do_action( 'anspress/question/service/create/success', $postId );

		return get_post( $postId );
	}

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

	/**
	 * Update the status of a question to 'private_post'.
	 *
	 * @param int $postId The ID of the question.
	 * @return bool
	 * @throws ValidationException If an error occurs.
	 */
	public function updatePostStatusToPrivate( int $postId ): bool {
		$updateData = array(
			'ID'          => $postId,
			'post_status' => 'private_post',
		);

		$post = ap_get_post( $postId );

		if ( 'question' !== $post->post_type ) {
			throw new ValidationException( array( '*' => esc_attr__( 'Given post is not a question.', 'anspress-question-answer' ) ) );
		}

		// Check if already private.
		if ( 'private_post' === $post->post_status ) {
			return new ValidationException( array( '*' => esc_attr__( 'Question is already private.', 'anspress-question-answer' ) ) );
		}

		$updated = wp_update_post( $updateData, true );

		if ( is_wp_error( $updated ) ) {
			throw new ValidationException( array( '*' => esc_attr__( 'Failed to update post status.', 'anspress-question-answer' ) ) );
		}

		return true;
	}

	/**
	 * Update the status of a question to 'publish'.
	 *
	 * @param int $postId The ID of the question.
	 * @return bool
	 * @throws ValidationException If an error occurs.
	 */
	public function updatePostStatusToPublish( int $postId ): bool {
		$updateData = array(
			'ID'          => $postId,
			'post_status' => 'publish',
		);

		$post = ap_get_post( $postId );

		if ( 'question' !== $post->post_type ) {
			throw new ValidationException( array( '*' => esc_attr__( 'Given post is not a question.', 'anspress-question-answer' ) ) );
		}

		// Check if already published.
		if ( 'publish' === $post->post_status ) {
			return new ValidationException( array( '*' => esc_attr__( 'Question is already published.', 'anspress-question-answer' ) ) );
		}

		$updated = wp_update_post( $updateData, true );

		if ( is_wp_error( $updated ) ) {
			throw new ValidationException( array( '*' => esc_attr__( 'Failed to update question status.', 'anspress-question-answer' ) ) );
		}

		return true;
	}

	/**
	 * Update the status of a question to 'moderate'.
	 *
	 * @param int $questionId The ID of the question.
	 * @return bool
	 * @throws ValidationException If an error occurs.
	 */
	public function updatePostStatusToModerate( int $questionId ) {
		$updateData = array(
			'ID'          => $questionId,
			'post_status' => 'moderate',
		);

		$post = ap_get_post( $questionId );

		if ( 'question' !== $post->post_type ) {
			throw new ValidationException( array( '*' => esc_attr__( 'Given post is not a question.', 'anspress-question-answer' ) ) );
		}

		// Check if already moderate.
		if ( 'moderate' === $post->post_status ) {
			return new ValidationException( array( '*' => esc_attr__( 'Question is already moderate.', 'anspress-question-answer' ) ) );
		}

		$updated = wp_update_post( $updateData, true );

		if ( is_wp_error( $updated ) ) {
			throw new ValidationException( array( '*' => esc_attr__( 'Failed to update question status.', 'anspress-question-answer' ) ) );
		}

		return true;
	}
}
