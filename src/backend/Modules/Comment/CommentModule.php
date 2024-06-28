<?php
/**
 * Answer module.
 *
 * @package AnsPress
 * @since   5.0.0
 */

namespace AnsPress\Modules\Comment;

use AnsPress\Classes\AbstractModule;
use AnsPress\Classes\RestRouteHandler;
use WP_REST_Server;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

/**
 * Comment module class.
 */
class CommentModule extends AbstractModule {
	/**
	 * Register hooks.
	 */
	public function register_hooks() {
	}

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route(
			'anspress/v1',
			'/post/(?P<post_id>\d+)/comments',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => fn( $req ) => RestRouteHandler::run( array( CommentController::class, 'showComments' ), $req ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'post_id' => array(
						'required' => true,
						'type'     => 'integer',
					),
				),
			)
		);

		register_rest_route(
			'anspress/v1',
			'/post/(?P<post_id>\d+)/comments',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => fn( $req ) => RestRouteHandler::run( array( CommentController::class, 'createComment' ), $req ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'post_id' => array(
						'required' => true,
						'type'     => 'integer',
					),
				),
			)
		);

		register_rest_route(
			'anspress/v1',
			'/post/(?P<post_id>\d+)/load-comment-form',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => fn( $req ) => RestRouteHandler::run( array( CommentController::class, 'loadCommentForm' ), $req ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'post_id' => array(
						'required' => true,
						'type'     => 'integer',
					),
				),
			)
		);

		register_rest_route(
			'anspress/v1',
			'/post/(?P<post_id>\d+)/load-edit-comment-form/(?P<comment_id>\d+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => fn( $req ) => RestRouteHandler::run( array( CommentController::class, 'loadEditCommentForm' ), $req ),
				'permission_callback' => '__return_true',
				'args'                => array(
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

		register_rest_route(
			'anspress/v1',
			'/post/(?P<post_id>\d+)/comments/(?P<comment_id>\d+)',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => fn( $req ) => RestRouteHandler::run( array( CommentController::class, 'updateComment' ), $req ),
				'permission_callback' => '__return_true',
				'args'                => array(
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

		register_rest_route(
			'anspress/v1',
			'/post/(?P<post_id>\d+)/comments/(?P<comment_id>\d+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => fn( $req ) => RestRouteHandler::run( array( CommentController::class, 'deleteComment' ), $req ),
				'permission_callback' => '__return_true',
				'args'                => array(
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
	}
}
