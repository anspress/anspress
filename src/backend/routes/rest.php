<?php
/**
 * Register REST API routes.
 *
 * @package AnsPress
 * @since 5.0.0
 */

use AnsPress\Classes\Router;
use AnsPress\Modules\Answer\AnswerController;
use AnsPress\Modules\Category\CategoryController;
use AnsPress\Modules\Comment\CommentController;
use AnsPress\Modules\Question\QuestionController;
use AnsPress\Modules\Tag\TagController;

Router::group(
	array(
		'namespace' => 'anspress/v1',
		'name'      => 'v1',
	),
	function () {

		Router::group(
			array(
				'prefix'     => 'questions',
				'name'       => 'questions',
				'controller' => QuestionController::class,
			),
			function () {
				Router::post(
					'(?P<question_id>\d+)/actions/(?P<action>[a-z-]+)',
					'actions',
					array(
						'name' => 'actions',
						'args' => array(
							'question_id' => array(
								'required' => true,
								'type'     => 'integer',
							),
							'action'      => array(
								'required' => true,
								'type'     => 'string',
							),
						),
					)
				);

				Router::post(
					'',
					'create',
					array(
						'name' => 'create',
					)
				);

				Router::post(
					'(?P<question_id>\d+)/update',
					'update',
					array(
						'name' => 'update',
						'args' => array(
							'question_id' => array(
								'required' => true,
								'type'     => 'integer',
							),
						),
					)
				);

				Router::get(
					'(?P<question_id>\d+)/answers',
					array( AnswerController::class, 'showAnswers' ),
					array(
						'name' => 'answers',
						'args' => array(
							'question_id' => array(
								'required' => true,
								'type'     => 'integer',
							),
						),
					)
				);
			}
		);

		Router::group(
			array(
				'prefix'     => 'answers',
				'name'       => 'answers',
				'controller' => AnswerController::class,
			),
			function () {
				Router::post(
					'(?P<answer_id>\d+)/actions/(?P<action>[a-z-]+)',
					'actions',
					array(
						'name' => 'actions',
						'args' => array(
							'answer_id' => array(
								'required' => true,
								'type'     => 'integer',
							),
							'action'    => array(
								'required' => true,
								'type'     => 'string',
							),
						),
					)
				);

				Router::post(
					'(?P<question_id>\d+)',
					'createAnswer',
					array(
						'name' => 'create',
						'args' => array(
							'question_id' => array(
								'required' => true,
								'type'     => 'integer',
							),
						),
					)
				);

				Router::post(
					'(?P<answer_id>\d+)/update',
					'updateAnswer',
					array(
						'name' => 'update',
						'args' => array(
							'answer_id' => array(
								'required' => true,
								'type'     => 'integer',
							),
						),
					)
				);
			}
		);

		Router::group(
			array(
				'prefix'     => 'posts',
				'name'       => 'posts',
				'controller' => CommentController::class,
			),
			function () {
				Router::post(
					'(?P<post_id>\d+)/load-comment-form',
					'loadCommentForm',
					array(
						'name' => 'loadCommentForm',
						'args' => array(
							'post_id' => array(
								'required' => true,
								'type'     => 'integer',
							),
						),
					)
				);

				Router::post(
					'(?P<post_id>\d+)/load-comment-edit-form/(?P<comment_id>\d+)',
					'loadCommentEditForm',
					array(
						'name' => 'loadCommentEditForm',
						'args' => array(
							'post_id'    => array(
								'required' => true,
								'type'     => 'integer',
							),
							'comment_id' => array(
								'required' => true,
								'type'     => 'integer',
							),
						),
					)
				);

				Router::post(
					'(?P<post_id>\d+)/comments',
					'createComment',
					array(
						'name' => 'createComment',
						'args' => array(
							'post_id' => array(
								'required' => true,
								'type'     => 'integer',
							),
						),
					)
				);

				Router::get(
					'(?P<post_id>\d+)/comments',
					'showComments',
					array(
						'name' => 'showComments',
						'args' => array(
							'post_id' => array(
								'required' => true,
								'type'     => 'integer',
							),
						),
					)
				);
			}
		);

		Router::post(
			'comments/(?P<comment_id>\d+)/actions/(?P<action>[a-z-]+)',
			array( CommentController::class, 'actions' ),
			array(
				'name' => 'comments.actions',
				'args' => array(
					'comment_id' => array(
						'required' => true,
						'type'     => 'integer',
					),
					'action'     => array(
						'required' => true,
						'type'     => 'string',
					),
				),
			)
		);

		Router::post(
			'comments/(?P<comment_id>\d+)/update',
			array( CommentController::class, 'updateComment' ),
			array(
				'name' => 'comments.updateComment',
				'args' => array(
					'comment_id' => array(
						'required' => true,
						'type'     => 'integer',
					),
				),
			)
		);

		Router::group(
			array(
				'prefix'     => 'categories',
				'name'       => 'categories',
				'controller' => CategoryController::class,
			),
			function () {
				Router::get(
					'',
					'index',
					array(
						'name' => 'index',
					)
				);
			}
		);

		Router::group(
			array(
				'prefix'     => 'tags',
				'name'       => 'tags',
				'controller' => TagController::class,
			),
			function () {
				Router::get(
					'',
					'index',
					array(
						'name' => 'index',
					)
				);
			}
		);
	}
);
