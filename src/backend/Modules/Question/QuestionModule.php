<?php
/**
 * Question module.
 *
 * @package AnsPress
 * @since   5.0.0
 */

namespace AnsPress\Modules\Question;

use AnsPress\Classes\AbstractModule;
use AnsPress\Classes\Plugin;
use AnsPress\Classes\RestRouteHandler;
use AnsPress\Modules\Answer\AnswerService;
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
		add_action( 'save_post_question', array( $this, 'afterCreatingQuestion' ), 99999, 3 );
		add_action( 'before_delete_post', array( $this, 'deleteQuestion' ), 999, 2 );
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

	/**
	 * Delete question.
	 *
	 * @param int     $postid Post ID.
	 * @param WP_Post $post Post object.
	 */
	public function deleteQuestion( $postid, $post ) {
		if ( 'question' !== $post->post_type ) {
			return;
		}

		// Delete all answers.
		$answers = get_posts(
			array(
				'post_type'      => 'answer',
				'post_parent'    => $postid,
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		foreach ( $answers as $answer ) {
			Plugin::get( AnswerService::class )->deleteAnswer( $answer );
		}

		ap_delete_qameta( $postid );
	}

	/**
	 * Count answers of a question and update the count.
	 *
	 * @param mixed $questionid Question id.
	 * @param mixed $question Post object.
	 * @param bool  $update Is updating post.
	 * @return void
	 */
	public function afterCreatingQuestion( $questionid, $question, $update ) {
		if ( wp_is_post_autosave( $question ) || wp_is_post_revision( $question ) ) {
			return;
		}

		// Add activity.
		$activity_type = $update ? 'edit_answer' : 'new_answer';

		$qameta = array(
			'last_updated' => current_time( 'mysql' ),
			'answers'      => 0,
		);

		ap_insert_qameta( $questionid, $qameta );

		ap_update_qameta_terms( $questionid );
	}
}
