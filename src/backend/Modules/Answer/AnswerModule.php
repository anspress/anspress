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
use AnsPress\Classes\Router;
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
	}
}
