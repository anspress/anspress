<?php
/**
 * The vote module.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Modules\Vote;

use AnsPress\Classes\AbstractModule;
use AnsPress\Classes\RestRouteHandler;
use WP_REST_Server;

/**
 * Category module class.
 *
 * @since 5.0.0
 */
class VoteModule extends AbstractModule {
	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'rest_api_init', array( $this, 'registerRoutes' ) );
	}

	/**
	 * Register REST API routes.
	 *
	 * @return void
	 */
	public function registerRoutes() {
		register_rest_route(
			'anspress/v1',
			'/post/(?P<post_id>\d+)/meta/votes',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => fn( $req ) => RestRouteHandler::run( array( VoteController::class, 'getPostVotes' ), $req ),
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
			'/post/(?P<post_id>\d+)/actions/vote/(?P<vote_type>\w+)',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => fn( $req ) => RestRouteHandler::run( array( VoteController::class, 'createVote' ), $req ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'post_id'   => array(
						'required' => true,
						'type'     => 'integer',
					),
					'vote_type' => array(
						'required' => true,
						'type'     => 'string',
					),
				),
			)
		);

		register_rest_route(
			'anspress/v1',
			'/post/(?P<post_id>\d+)/actions/undo-vote',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => fn( $req ) => RestRouteHandler::run( array( VoteController::class, 'undoVote' ), $req ),
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
