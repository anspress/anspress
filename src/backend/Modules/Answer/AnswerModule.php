<?php
/**
 * Answer module.
 *
 * @package AnsPress
 * @since   5.0.0
 */

namespace AnsPress\Modules\Answer;

use AnsPress\Classes\AbstractModule;
use AnsPress\Classes\RestRouteHandler;
use WP_REST_Server;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

/**
 * Answer module class.
 */
class AnswerModule extends AbstractModule {
	/**
	 * Register hooks.
	 */
	public function register_hooks() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route(
			'anspress/v1',
			'/post/(?P<post_id>\d+)/load-answer-form',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => fn( $req ) => RestRouteHandler::run( array( AnswerController::class, 'loadAnswerForm' ), $req ),
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
			'/post/(?P<post_id>\d+)/answers',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => fn( $req ) => RestRouteHandler::run( array( AnswerController::class, 'createAnswer' ), $req ),
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
			'/post/(?P<post_id>\d+)/answers',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => fn( $req ) => RestRouteHandler::run( array( AnswerController::class, 'showAnswers' ), $req ),
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
			'/post/(?P<post_id>\d+)/actions/select/(?P<answer_id>\d+)',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => fn( $req ) => RestRouteHandler::run( array( AnswerController::class, 'selectAnswer' ), $req ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'post_id'   => array(
						'required' => true,
						'type'     => 'integer',
					),
					'answer_id' => array(
						'required' => true,
						'type'     => 'integer',
					),
				),
			)
		);

		register_rest_route(
			'anspress/v1',
			'/post/(?P<post_id>\d+)/actions/unselect/(?P<answer_id>\d+)',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => fn( $req ) => RestRouteHandler::run( array( AnswerController::class, 'unselectAnswer' ), $req ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'post_id'   => array(
						'required' => true,
						'type'     => 'integer',
					),
					'answer_id' => array(
						'required' => true,
						'type'     => 'integer',
					),
				),
			)
		);

		register_rest_route(
			'anspress/v1',
			'/post/(?P<post_id>\d+)/answers/(?P<answer_id>\d+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => fn( $req ) => RestRouteHandler::run( array( AnswerController::class, 'deleteAnswer' ), $req ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'post_id'   => array(
						'required' => true,
						'type'     => 'integer',
					),
					'answer_id' => array(
						'required' => true,
						'type'     => 'integer',
					),
				),
			)
		);
	}
}
