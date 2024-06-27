<?php
/**
 * Answer service.
 *
 * @package AnsPress
 * @since   5.0.0
 */

namespace AnsPress\Modules\Question;

use AnsPress\Classes\AbstractService;
use WP_Query;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

/**
 * Question service class.
 */
class QuestionService extends AbstractService {

	/**
	 * Delete question.
	 *
	 * @param int $question_id Id of the question.
	 * @return bool
	 */
	public function deleteQuestion( int $question_id ) {
		$deleted = wp_delete_post( $question_id, true );

		if ( ! $deleted ) {
			return false;
		}

		return true;
	}
}
