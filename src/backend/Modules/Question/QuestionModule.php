<?php
/**
 * Question module.
 *
 * @package AnsPress
 * @since   5.0.0
 */

namespace AnsPress\Modules\Question;

use AnsPress\Classes\AbstractModule;
use AnsPress\Classes\RestRouteHandler;
use WP_REST_Server;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

/**
 * Question module class.
 */
class QuestionModule extends AbstractModule {
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
			'/post/(?P<post_id>\d+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => fn( $req ) => RestRouteHandler::run( array( QuestionController::class, 'deleteQuestion' ), $req ),
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
			'/post/(?P<post_id>\d+)/load-edit-question-form',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => fn( $req ) => RestRouteHandler::run( array( QuestionController::class, 'loadEditQuestion' ), $req ),
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
			'/post/(?P<post_id>\d+)/actions/(?P<action>[a-z-]+)',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => fn( $req ) => RestRouteHandler::run( array( QuestionController::class, 'loadEditQuestion' ), $req ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'post_id' => array(
						'required' => true,
						'type'     => 'integer',
					),
				),
			)
		);
	}
}
