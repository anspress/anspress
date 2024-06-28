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
	}
}
