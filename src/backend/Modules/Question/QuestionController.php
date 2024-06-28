<?php
/**
 * Question controller.
 *
 * @package AnsPress
 * @since   5.0.0
 */

namespace AnsPress\Modules\Question;

use AnsPress\Classes\AbstractController;
use AnsPress\Classes\Auth;
use AnsPress\Classes\Plugin;
use AnsPress\Classes\Str;
use AnsPress\Modules\Vote\VoteService;
use WP_REST_Response;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

/**
 * Question controller class.
 */
class QuestionController extends AbstractController {
	/**
	 * Question service.
	 *
	 * @param QuestionService $questionService Question service.
	 * @return void
	 */
	public function __construct( public QuestionService $questionService ) {
	}

	/**
	 * Question action handler.
	 *
	 * @return WP_REST_Response Response.
	 */
	public function actions(): WP_REST_Response {
		$data = $this->validate(
			array(
				'question_id' => 'required|numeric|exists:posts,id|post_type:question',
				'action'      => 'required|string',
			)
		);

		$action = Str::toCamelCase( 'action' . $data['action'] );

		if ( method_exists( $this, $action ) ) {
			return $this->$action( (int) $data['question_id'] );
		}

		return $this->notFound( __( 'Invalid action.', 'anspress-question-answer' ) );
	}

	/**
	 * Delete question.
	 *
	 * @param int $questionId The ID of the question.
	 * @return WP_REST_Response Response.
	 */
	public function actionDeleteQuestion( int $questionId ): WP_REST_Response {
		$this->assureLoggedIn();

		$post = get_post( $questionId );

		$this->checkPermission( 'question:delete', array( 'question' => $post ) );

		$this->questionService->deleteQuestion( $post->ID );

		$this->addMessage( 'success', __( 'Question deleted successfully.', 'anspress-question-answer' ) );

		return $this->response(
			array(
				'redirect' => ap_base_page_link(),
			)
		);
	}

	/**
	 * Toggle the closed state of a question.
	 *
	 * @param int $questionId The ID of the question.
	 * @return WP_REST_Response Response.
	 */
	public function actionToggleClosedState( int $questionId ) {
		$this->assureLoggedIn();

		$post = ap_get_post( $questionId );

		$this->checkPermission( 'question:close', array( 'question' => $post ) );

		$newState = $this->questionService->toggleQuestionClosedState( $post->ID );

		$this->addMessage(
			'success',
			'closed' === $newState ? __( 'Question closed successfully.', 'anspress-question-answer' ) :
			__( 'Question opened successfully.', 'anspress-question-answer' )
		);

		return $this->response(
			array(
				'reload' => true,
			)
		);
	}

	/**
	 * Toggle the featured state of a question.
	 *
	 * @param int $questionId The ID of the question.
	 * @return WP_REST_Response Response.
	 */
	public function actionToggleFeatured( int $questionId ) {
		$this->assureLoggedIn();

		$post = ap_get_post( $questionId );

		$this->checkPermission( 'question:feature', array( 'question' => $post ) );

		$newState = $this->questionService->toggleQuestionFeaturedState( $post->ID );

		$this->addMessage(
			'success',
			'featured' === $newState ? __( 'Question featured successfully.', 'anspress-question-answer' ) :
			__( 'Question unfeatured successfully.', 'anspress-question-answer' )
		);

		return $this->response(
			array(
				'reload' => true,
			)
		);
	}

	/**
	 * Report a question.
	 *
	 * @param int $questionId The ID of the question.
	 * @return WP_REST_Response Response.
	 */
	public function actionReport( int $questionId ) {
		$this->assureLoggedIn();

		$post = ap_get_post( $questionId );

		if ( Plugin::get( VoteService::class )->hasUserFlaggedPost( $post->ID ) ) {
			return $this->badRequest( __( 'You have already reported this question.', 'anspress-question-answer' ) );
		}

		$voted = Plugin::get( VoteService::class )->addPostFlag( $post->ID, Auth::getID() );

		if ( ! $voted ) {
			return $this->serverError( __( 'Failed to report this question.', 'anspress-question-answer' ) );
		}

		$this->addMessage(
			'success',
			esc_attr__( 'Thank you for reporting this question.', 'anspress-question-answer' )
		);

		return $this->response();
	}

	/**
	 * Load answer form.
	 *
	 * @param int $questionId The ID of the question.
	 * @return WP_REST_Response Response.
	 * @throws ValidationException If validation fails.
	 */
	public function actionLoadAnswerForm( int $questionId ): WP_REST_Response {
		$this->assureLoggedIn();

		$this->validate(
			array(
				'form_loaded' => 'nullable|bool',
			),
		);

		$post = get_post( $questionId );

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
}
