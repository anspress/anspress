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
