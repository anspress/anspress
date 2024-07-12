<?php
/**
 * Answer service.
 *
 * @package AnsPress
 * @since   5.0.0
 */

namespace AnsPress\Modules\Answer;

use AnsPress\Classes\AbstractService;
use AnsPress\Classes\PostHelper;
use AnsPress\Classes\Validator;
use AnsPress\Exceptions\ValidationException;
use WP_Post;
use WP_Query;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

/**
 * Answer service class.
 */
class AnswerService extends AbstractService {
	/**
	 * Get answers data.
	 *
	 * @param WP_Query $query Query.
	 * @param WP_Post  $question Question.
	 * @param int      $currentPage Current page.
	 * @param int|null $perPage Per page.
	 * @return array
	 */
	public function getAnswersData( $query, $question, $currentPage, ?int $perPage = null ) {
		if ( ! $perPage ) {
			$perPage = ap_opt( 'answers_per_page' );
		}

		$totalAnswers = $query->found_posts;
		$totalPages   = ceil( $totalAnswers / $perPage );

		return array(
			'question_id'     => $question->ID,
			'per_page'        => $perPage,
			'total_pages'     => $totalPages,
			'current_page'    => $currentPage,
			'have_pages'      => max( 0, $query->max_num_pages - $currentPage ),
			'remaining_items' => max( 0, $totalAnswers - ( $currentPage * $perPage ) ),
			'load_more_path'  => 'anspress/v1/post/' . $question->ID . '/answers',
		);
	}

	/**
	 * Create answer.
	 *
	 * @param array $data Data.
	 * @return \WP_Post
	 * @throws ValidationException If validation fails.
	 */
	public function createAnswer( $data ) {
		$data = ( new Validator(
			$data,
			array(
				'post_content' => 'required|min:1|max:5000',
				'question_id'  => 'required|numeric|exists:posts,ID|post_type:question',
				'post_author'  => 'required|numeric|exists:users,ID',
			)
		) )->validated();

		$question = get_post( $data['question_id'] );

		$answer = wp_insert_post(
			array(
				'post_content' => $data['post_content'],
				'post_title'   => '',
				'post_status'  => 'publish',
				'post_type'    => 'answer',
				'post_parent'  => $question->ID,
				'post_author'  => $data['post_author'],
			),
			true
		);

		if ( is_wp_error( $answer ) ) {
			throw new ValidationException(
				array(),
				esc_attr__( 'Failed to create answer.', 'anspress-question-answer' )
			);
		}

		return get_post( $answer );
	}

	/**
	 * Delete answer.
	 *
	 * @param int $answer_id Id of the answer.
	 * @return bool
	 */
	public function deleteAnswer( int $answer_id ) {
		$deleted = wp_trash_post( $answer_id, true );

		if ( ! $deleted ) {
			return false;
		}

		return true;
	}

	/**
	 * Update answer.
	 *
	 * @param array $data Data.
	 * @return WP_Post
	 * @throws ValidationException If validation fails.
	 */
	public function updateAnswer( $data ): WP_Post {
		$data = ( new Validator(
			$data,
			array(
				'post_content' => 'required|min:1|max:5000',
				'post_id'      => 'required|numeric|exists:posts,ID|post_type:answer',
			)
		) )->validated();

		$answer = wp_update_post(
			array(
				'ID'           => $data['post_id'],
				'post_content' => $data['post_content'],
			),
			true
		);

		if ( is_wp_error( $answer ) ) {
			throw new ValidationException(
				array(),
				esc_attr__( 'Failed to update answer.', 'anspress-question-answer' )
			);
		}

		return get_post( $answer );
	}

	/**
	 * Get answers query.
	 *
	 * @param array $args Args.
	 * @return WP_Query
	 */
	public function getAnswersQuery( array $args = array() ): WP_Query {
		$args = wp_parse_args(
			$args,
			array(
				'post_type'        => 'answer',
				'posts_per_page'   => ap_opt( 'answers_per_page' ),
				'paged'            => 0,
				'ap_order_by'      => 'oldest',
				'ap_answers_query' => true,
				'ap_query'         => true,
			)
		);

		// If only one answer is requested then we need to set posts_per_page to 1.
		if ( ! empty( $args['answer_id'] ) ) {
			$args['posts_per_page'] = 1;
			$args['p']              = $args['answer_id'];
		}

		return new WP_Query( $args );
	}

	/**
	 * Method to guess the page number of an answer in the pagination.
	 *
	 * @param int $question_id Question ID.
	 * @param int $answer_id Answer ID.
	 * @param int $per_page Number of answers per page.
	 * @return int
	 */
	public function guessAnswerPageInPagination( $question_id, $answer_id, $per_page ): int {
		$answer = get_post( $answer_id );

		if ( ! $answer ) {
			return 1;
		}

		$question = get_post( $question_id );

		$answers = get_posts(
			array(
				'post_type'      => 'answer',
				'post_parent'    => $question->ID,
				'posts_per_page' => $per_page,
				'fields'         => 'ids',
			)
		);

		$answer_index = array_search( $answer_id, $answers, true );

		if ( false === $answer_index ) {
			return 1;
		}

		return ceil( ( $answer_index + 1 ) / $per_page );
	}

	/**
	 * Update the status of a answer to 'moderate'.
	 *
	 * @param int $answerId The ID of the question.
	 * @return bool
	 * @throws ValidationException If an error occurs.
	 */
	public function updatePostStatusToModerate( int $answerId ) {
		$updateData = array(
			'ID'          => $answerId,
			'post_status' => 'moderate',
		);

		$post = ap_get_post( $answerId );

		if ( ! PostHelper::isAnswer( $post ) ) {
			throw new ValidationException( array( '*' => esc_attr__( 'Given post is not an answer.', 'anspress-question-answer' ) ) );
		}

		// Check if already moderate.
		if ( PostHelper::isModerateStatus( $post ) ) {
			return new ValidationException( array( '*' => esc_attr__( 'Answer is already moderate.', 'anspress-question-answer' ) ) );
		}

		$updated = wp_update_post( $updateData, true );

		if ( is_wp_error( $updated ) ) {
			throw new ValidationException( array( '*' => esc_attr__( 'Failed to update answer status.', 'anspress-question-answer' ) ) );
		}

		return true;
	}

	/**
	 * Update the status of a answer to 'private_post'.
	 *
	 * @param int $answerId The ID of the question.
	 * @return bool
	 * @throws ValidationException If an error occurs.
	 */
	public function updatePostStatusToPrivate( int $answerId ) {
		$updateData = array(
			'ID'          => $answerId,
			'post_status' => 'private_post',
		);

		$post = ap_get_post( $answerId );

		if ( ! PostHelper::isAnswer( $post ) ) {
			throw new ValidationException( array( '*' => esc_attr__( 'Given post is not an answer.', 'anspress-question-answer' ) ) );
		}

		// Check if already private.
		if ( 'private' === $post->post_status ) {
			return new ValidationException( array( '*' => esc_attr__( 'Answer is already private.', 'anspress-question-answer' ) ) );
		}

		$updated = wp_update_post( $updateData, true );

		if ( is_wp_error( $updated ) ) {
			throw new ValidationException( array( '*' => esc_attr__( 'Failed to update answer status.', 'anspress-question-answer' ) ) );
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

		if ( ! PostHelper::isAnswer( $post ) ) {
			throw new ValidationException( array( '*' => esc_attr__( 'Given post is not a answer.', 'anspress-question-answer' ) ) );
		}

		// Check if already published.
		if ( 'publish' === $post->post_status ) {
			return new ValidationException( array( '*' => esc_attr__( 'Answer is already published.', 'anspress-question-answer' ) ) );
		}

		$updated = wp_update_post( $updateData, true );

		if ( is_wp_error( $updated ) ) {
			throw new ValidationException( array( '*' => esc_attr__( 'Failed to update answer status.', 'anspress-question-answer' ) ) );
		}

		return true;
	}
}
