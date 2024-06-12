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
			'/vote/(?P<post_id>\d+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( VoteController::class, 'getPostVotes' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			'anspress/v1',
			'/vote/(?P<post_id>\d+)',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( VoteController::class, 'createVote' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			'anspress/v1',
			'/vote/(?P<post_id>\d+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( VoteController::class, 'undoVote' ),
				'permission_callback' => '__return_true',
			)
		);
	}
}
