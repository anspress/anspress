<?php
/**
 * The vote module.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Modules\Vote;

use AnsPress\Classes\AbstractModule;
use AnsPress\Classes\Plugin;

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
		$voteController = Plugin::get( VoteController::class );

		register_rest_route(
			'anspress/v1',
			'/vote',
			array(
				'methods'  => 'POST',
				'callback' => array( $voteController, 'createVote' ),
			)
		);
	}
}
