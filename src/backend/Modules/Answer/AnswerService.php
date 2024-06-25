<?php
/**
 * Answer service.
 *
 * @package AnsPress
 * @since   5.0.0
 */

namespace AnsPress\Modules\Answer;

use AnsPress\Classes\AbstractService;
use AnsPress\Classes\Validator;
use AnsPress\Exceptions\ValidationException;

use function Patchwork\CallRerouting\validate;

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
		$havePages    = $query->max_num_pages > 1 && $currentPage < $totalPages;

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
}
