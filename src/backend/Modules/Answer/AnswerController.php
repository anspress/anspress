<?php
/**
 * Answer controller.
 *
 * @package AnsPress
 * @since   5.0.0
 */

namespace AnsPress\Modules\Answer;

use AnsPress\Classes\AbstractPostController;
use AnsPress\Classes\Auth;
use AnsPress\Classes\Plugin;
use AnsPress\Classes\PostHelper;
use AnsPress\Classes\Str;
use AnsPress\Classes\TemplateHelper;
use AnsPress\Exceptions\ValidationException;
use AnsPress\Modules\Vote\VoteService;
use WP_Query;
use WP_REST_Response;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

/**
 * Answer controller class.
 */
class AnswerController extends AbstractPostController {
	/**
	 * Answer service.
	 *
	 * @param AnswerService $answerService Answer service.
	 * @return void
	 */
	public function __construct( public AnswerService $answerService ) {
	}

	/**
	 * Answer action handler.
	 *
	 * @return WP_REST_Response Response.
	 */
	public function actions(): WP_REST_Response {
		$data = $this->validate(
			array(
				'answer_id' => 'required|numeric|exists:posts,id|post_type:answer',
				'action'    => 'required|string',
			)
		);

		$action = Str::toCamelCase( 'action' . $data['action'] );

		if ( method_exists( $this, $action ) ) {
			return $this->$action( (int) $data['answer_id'] );
		}

		return $this->notFound( __( 'Invalid action.', 'anspress-question-answer' ) );
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
				'question_id'  => 'required|numeric|exists:posts,ID|post_type:question',
			)
		);

		$question = get_post( $data['question_id'] );

		$this->checkPermission( 'answer:create', array( 'question' => $question ) );

		$answer = $this->answerService->createAnswer(
			array(
				'post_content' => $data['post_content'],
				'question_id'  => $question->ID,
				'post_author'  => get_current_user_id(),
			)
		);

		$query = $this->answerService->getAnswersQuery(
			array(
				'p' => $answer->ID,
			)
		);

		$html = '';
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$html = TemplateHelper::loadRestBlockPart(
					$this->request,
					'src/frontend/single-question/php/item.php',
					array(
						'post' => $answer,
					)
				);
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
				'question_id' => 'required|numeric|exists:posts,ID|post_type:question',
			)
		);

		$question = get_post( $data['question_id'] );

		$currentPage = max( 1, $this->getParam( 'page', 1 ) );

		$query = Plugin::get( AnswerService::class )->getAnswersQuery(
			array(
				'post_parent' => $question->ID,
				'paged'       => $currentPage,
			)
		);

		$answersArgs = $this->answerService->getAnswersData( $query, $question, $this->getParam( 'page', 1 ) );

		$this->addEvent(
			'anspress:answer:added:' . $question->ID,
			array(
				'html' => TemplateHelper::loadRestBlockPart(
					$this->request,
					'src/frontend/single-question/php/answers.php',
					array(
						'question'     => $question,
						'query'        => $query,
						'answers_args' => $answersArgs,
					)
				),
			)
		);

		$this->setData(
			'button:answers:loadmore:' . $question->ID,
			array(
				'page' => $currentPage + 1,
			)
		);

		$this->setData(
			'answers-' . $question->ID,
			$answersArgs
		);

		return $this->response();
	}

	/**
	 * Select answer.
	 *
	 * @param int $answerId Answer ID.
	 * @return WP_REST_Response Response object.
	 */
	public function actionSelect( int $answerId ): WP_REST_Response {
		$this->assureLoggedIn();

		$answer = get_post( $answerId );

		$this->checkPermission( 'answer:select', array( 'answer' => $answer ) );

		ap_set_selected_answer( $answer->post_parent, $answerId );

		$this->addMessage(
			'success',
			esc_attr__( 'Answer selected successfully.', 'anspress-question-answer' )
		);

		return $this->response(
			array(
				'reload' => true,
			)
		);
	}

	/**
	 * Unselect answer.
	 *
	 * @param int $answerId Answer ID.
	 * @return WP_REST_Response Response object.
	 */
	public function actionUnselect( int $answerId ): WP_REST_Response {
		$this->assureLoggedIn();

		$answer   = get_post( $answerId );
		$question = get_post( $answer->post_parent );

		$this->checkPermission( 'answer:unselect', array( 'answer' => $answer ) );

		// Check if answer is selected.
		if ( ! ap_is_selected( $answer->ID ) ) {
			return $this->badRequest( __( 'Answer is not selected for given question', 'anspress-question-answer' ) );
		}

		ap_unset_selected_answer( $question->ID );

		$this->addMessage(
			'success',
			esc_attr__( 'Answer unselected successfully.', 'anspress-question-answer' )
		);

		return $this->response(
			array(
				'reload' => true,
			)
		);
	}

	/**
	 * Delete answer.
	 *
	 * @param int $answerId Answer ID.
	 * @return WP_REST_Response Response object.
	 */
	public function actionDeleteAnswer( int $answerId ): WP_REST_Response {
		$this->assureLoggedIn();

		$answer = get_post( $answerId );

		$this->checkPermission( 'answer:delete', array( 'answer' => $answer ) );

		$deleted = $this->answerService->deleteAnswer( $answer->ID );

		if ( ! $deleted ) {
			return $this->badRequest(
				__( 'Failed to delete answer.', 'anspress-question-answer' )
			);
		}

		$this->addMessage(
			'success',
			esc_attr__( 'Answer deleted successfully.', 'anspress-question-answer' )
		);

		$this->addEvent( 'anspress:answer:deleted:' . $answer->post_parent, array( 'answer_id' => $answer->ID ) );

		$query = Plugin::get( AnswerService::class )->getAnswersQuery(
			array(
				'post_parent' => $answer->post_parent,
				'paged'       => 1,
			)
		);

		$this->setData( 'answers-' . $answer->post_parent, $this->answerService->getAnswersData( $query, ap_get_post( $answer->post_parent ), 1 ) );

		return $this->response();
	}

	/**
	 * Load edit answer form.
	 *
	 * @return WP_REST_Response Response object.
	 */
	public function actionLoadAnswerEditForm() {
		$this->assureLoggedIn();

		$this->validate(
			array(
				'answer_id' => 'required|numeric|exists:posts,ID|post_type:answer',
			)
		);

		$answer = get_post( $this->getParam( 'answer_id' ) );

		$this->checkPermission( 'answer:edit', array( 'answer' => $answer ) );

		$this->replaceHtml(
			'[data-anspress-id="answer-form-c-' . $answer->post_parent . '"]',
			TemplateHelper::loadRestBlockPart(
				$this->request,
				'src/frontend/single-question/php/answer-form.php',
				array(
					'question'     => ap_get_post( $answer->post_parent ),
					'answer'       => $answer,
					'form_loaded'  => true,
					'load_tinymce' => 'anspress-answer-content',
				)
			)
		);

		return $this->response();
	}

	/**
	 * Update answer.
	 *
	 * @return WP_REST_Response Response object.
	 */
	public function updateAnswer() {
		$this->assureLoggedIn();

		$data = $this->validate(
			array(
				'post_content' => 'required|min:1|max:5000',
				'answer_id'    => 'required|numeric|exists:posts,ID|post_type:answer',
			)
		);

		$answer = get_post( $data['answer_id'] );

		$this->checkPermission( 'answer:edit', array( 'answer' => $answer ) );

		$updated = $this->answerService->updateAnswer(
			array(
				'post_content' => $data['post_content'],
				'post_id'      => $answer->ID,
			)
		);

		if ( ! $updated ) {
			return $this->badRequest(
				__( 'Failed to update answer.', 'anspress-question-answer' )
			);
		}

		$this->addMessage(
			'success',
			esc_attr__( 'Answer updated successfully.', 'anspress-question-answer' )
		);

		$query = $this->answerService->getAnswersQuery(
			array(
				'p' => $answer->ID,
			)
		);

		$html = '';
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$html = TemplateHelper::loadRestBlockPart(
					$this->request,
					'src/frontend/single-question/php/item.php',
					array(
						'post' => $answer,
					)
				);
			}
		}

		wp_reset_postdata();

		$elm = '[data-anspress-id="answer:' . $answer->ID . '"]';

		$this->replaceHtml(
			$elm,
			$html
		);

		$this->addEvent( 'scrollTo', array( 'element' => $elm ) );

		return $this->response();
	}

	/**
	 * Report an answer.
	 *
	 * @param int $answerId The ID of the answer.
	 * @return WP_REST_Response Response.
	 */
	public function actionReport( int $answerId ) {
		$this->assureLoggedIn();

		$post = ap_get_post( $answerId );

		if ( Plugin::get( VoteService::class )->hasUserFlaggedPost( $post->ID ) ) {
			return $this->badRequest( __( 'You have already reported this answer.', 'anspress-question-answer' ) );
		}

		$voted = Plugin::get( VoteService::class )->addPostFlag( $post->ID, Auth::getID() );

		if ( ! $voted ) {
			return $this->serverError( __( 'Failed to report this answer.', 'anspress-question-answer' ) );
		}

		$this->addMessage(
			'success',
			esc_attr__( 'Thank you for reporting this answer.', 'anspress-question-answer' )
		);

		return $this->response();
	}

	/**
	 * Set answer as moderate.
	 *
	 * @param int $answerId The ID of the anser.
	 * @return WP_REST_Response Response.
	 * @throws ValidationException If validation fails.
	 */
	public function actionSetModerate( int $answerId ): WP_REST_Response {
		$this->assureLoggedIn();

		$post = ap_get_post( $answerId );

		$this->checkPermission( 'answer:update', array( 'answer' => $post ) );

		if ( PostHelper::isAuthor( $post ) ) {
			return $this->badRequest( __( 'You can not moderate your own answer.', 'anspress-question-answer' ) );
		}

		$this->answerService->updatePostStatusToModerate( $post->ID );

		$this->addMessage(
			'success',
			__( 'Question status updated to moderate and is only visible to admin and moderators.', 'anspress-question-answer' )
		);

		$query = new WP_Query(
			array(
				'p'         => $post->ID,
				'post_type' => AnswerModel::postTypeSlug(),
			)
		);

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$this->replaceHtml(
					'[data-anspress-id="answer:' . $post->ID . '"]',
					TemplateHelper::loadRestBlockPart(
						$this->request,
						'src/frontend/single-question/php/item.php',
						array(
							'post' => $post,
						)
					)
				);
			}
		}

		return $this->response();
	}

	/**
	 * Set answer as private.
	 *
	 * @param int $answerId The ID of the answer.
	 * @return WP_REST_Response Response.
	 * @throws ValidationException If validation fails.
	 */
	public function actionMakePrivate( int $answerId ): WP_REST_Response {
		$this->assureLoggedIn();

		$post = ap_get_post( $answerId );

		$this->checkPermission( 'answer:update', array( 'answer' => $post ) );

		$this->answerService->updatePostStatusToPrivate( $post->ID );

		$this->addMessage(
			'success',
			__( 'Answer status updated to private and is only visible to author, admin and moderators.', 'anspress-question-answer' )
		);

		$query = new WP_Query(
			array(
				'p'         => $post->ID,
				'post_type' => AnswerModel::postTypeSlug(),
			)
		);

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$this->replaceHtml(
					'[data-anspress-id="answer:' . $post->ID . '"]',
					TemplateHelper::loadRestBlockPart(
						$this->request,
						'src/frontend/single-question/php/item.php',
						array(
							'post' => get_post(),
						)
					)
				);
			}
		}

		return $this->response();
	}
}
