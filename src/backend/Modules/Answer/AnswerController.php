<?php
/**
 * Answer controller.
 *
 * @package AnsPress
 * @since   5.0.0
 */

namespace AnsPress\Modules\Answer;

use AnsPress\Classes\AbstractController;
use AnsPress\Classes\Auth;
use AnsPress\Classes\Plugin;
use AnsPress\Exceptions\HTTPException;
use AnsPress\Exceptions\ValidationException;
use InvalidArgumentException;
use WP_Post;
use WP_Query;
use WP_REST_Response;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

/**
 * Answer controller class.
 */
class AnswerController extends AbstractController {
	/**
	 * Answer service.
	 *
	 * @param AnswerService $answerService Answer service.
	 * @return void
	 */
	public function __construct( public AnswerService $answerService ) {
	}

	/**
	 * Load answer form.
	 *
	 * @return WP_REST_Response Response.
	 * @throws ValidationException If validation fails.
	 */
	public function loadAnswerForm(): WP_REST_Response {
		$this->assureLoggedIn();

		$data = $this->validate(
			array(
				'post_id'     => 'required|numeric|exists:posts,id|post_type:question',
				'form_loaded' => 'nullable|bool',
			),
		);

		$post = get_post( $data['post_id'] );

		$this->checkPermission( 'answer:create', array( 'question' => $post ) );

		$this->replaceHtml(
			'[data-anspress-id="answer-form-c-' . $post->ID . '"]',
			Plugin::loadView(
				'src/frontend/single-question/answer-form.php',
				array(
					'question'    => $post,
					'form_loaded' => (bool) $this->getParam( 'form_loaded', false ),
				),
				false
			)
		);

		return $this->response(
			array(
				'load_tinymce' => 'anspress-answer-content',
			)
		);
	}

	/**
	 * Create answer.
	 *
	 * @return WP_REST_Response Response object.
	 * @throws ValidationException If validation fails.
	 */
	public function createAnswer(): WP_REST_Response {
		$this->assureLoggedIn();

		$data = $this->validate(
			array(
				'post_content' => 'required|min:1|max:5000',
				'post_id'      => 'required|numeric|exists:posts,ID|post_type:question',
			)
		);

		$question = get_post( $data['post_id'] );

		$this->checkPermission( 'answer:create', array( 'question' => $question ) );

		$answer = $this->answerService->createAnswer(
			array(
				'post_content' => $data['post_content'],
				'question_id'  => $question->ID,
				'post_author'  => get_current_user_id(),
			)
		);

		$query = new WP_Query(
			array(
				'post_type'   => 'answer',
				'post_status' => 'publish',
				'p'           => $answer->ID,
			)
		);

		$html = '';
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$html = Plugin::loadView( 'src/frontend/single-question/item.php', array(), false );
			}
		}

		wp_reset_postdata();

		$this->addMessage(
			'success',
			esc_attr__( 'Answer posted successfully.', 'anspress-question-answer' )
		);

		$this->addEvent( 'anspress:answer:added:' . $question->ID, array( 'html' => $html ) );

		return $this->response();
	}

	/**
	 * Fetch answers.
	 *
	 * @return WP_REST_Response Response object.
	 */
	public function showAnswers(): WP_REST_Response {
		$data = $this->validate(
			array(
				'post_id' => 'required|numeric|exists:posts,ID|post_type:question',
			)
		);

		$question = get_post( $data['post_id'] );

		$currentPage = max( 1, $this->getParam( 'page', 1 ) );

		$query = new WP_Query(
			array(
				'post_type'      => 'answer',
				'post_parent'    => $question->ID,
				'posts_per_page' => ap_opt( 'answers_per_page' ),
				'paged'          => $currentPage,
				'order'          => 'ASC',
				'orderby'        => 'date',
			)
		);

		$answersArgs = $this->answerService->getAnswersData( $query, $question, $this->getParam( 'page', 1 ) );

		$this->addEvent(
			'anspress:answer:added:' . $question->ID,
			array(
				'html' => Plugin::loadView(
					'src/frontend/single-question/answers.php',
					array(
						'question'     => $question,
						'query'        => $query,
						'answers_args' => $answersArgs,
					),
					false
				),
			)
		);

		$this->setData(
			'answers-' . $question->ID,
			$answersArgs
		);

		return $this->response();
	}
}
