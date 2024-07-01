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
		add_action( 'init', array( $this, 'registerQuestionStatus' ) );
	}

	/**
	 * Register question post status.
	 */
	public function registerQuestionStatus() {
		register_post_status(
			'moderate',
			array(
				'label'                     => __( 'Moderate', 'anspress-question-answer' ),
				'public'                    => true,
				'show_in_admin_all_list'    => false,
				'show_in_admin_status_list' => true,
				// translators: %s is count of post awaiting moderation.
				'label_count'               => _n_noop(
					'Moderate <span class="count">(%s)</span>',
					'Moderate <span class="count">(%s)</span>',
					'anspress-question-answer'
				),
			)
		);

		register_post_status(
			'private_post',
			array(
				'label'                     => __( 'Private', 'anspress-question-answer' ),
				'public'                    => true,
				'show_in_admin_all_list'    => false,
				'show_in_admin_status_list' => true,
				// translators: %s is count of private post.
				'label_count'               => _n_noop( 'Private Post <span class="count">(%s)</span>', 'Private Post <span class="count">(%s)</span>', 'anspress-question-answer' ),
			)
		);
	}
}
