<?php
/**
 * Report post button.
 *
 * @package AnsPress
 * @since   5.0.0
 */

use AnsPress\Classes\Auth;
use AnsPress\Classes\PostHelper;
use AnsPress\Classes\Router;
use AnsPress\Modules\Answer\AnswerModel;
use AnsPress\Modules\Question\QuestionModel;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

// Check that post argument is set.
if ( ! isset( $post ) ) {
	throw new InvalidArgumentException( 'Post argument is required.' );
}

if ( ! Auth::isLoggedIn() || PostHelper::isAuthor( $post ) ) {
	return;
}

if ( PostHelper::isQuestion( $post ) ) {
	$href = Router::route(
		'v1.questions.actions',
		array(
			'question_id' => $post->ID,
			'action'      => 'report',
		)
	);
} else {
	$href = Router::route(
		'v1.answers.actions',
		array(
			'answer_id' => $post->ID,
			'action'    => 'report',
		)
	);
}
?>
<anspress-link
	data-href="<?php echo esc_attr( $href ); ?>"
	data-method="POST"
	class="anspress-apq-item-action anspress-apq-item-action-vote"><?php esc_html_e( 'Report', 'anspress-question-answer' ); ?></anspress-link>
