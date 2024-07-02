<?php
/**
 * Publish post button.
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

// Check if already moderate or author.
if ( PostHelper::isPublishedStatus( $post ) ) {
	return;
}

$context = array( $post->post_type => $post );

if ( ! Auth::currentUserCan( $post->post_type . ':set_status_to_publish', $context ) ) {
	return;
}

if ( PostHelper::isQuestion( $post ) ) {
	$href = Router::route(
		'v1.questions.actions',
		array(
			'question_id' => $post->ID,
			'action'      => 'set-publish',
		)
	);
} else {
	$href = Router::route(
		'v1.answers.actions',
		array(
			'answer_id' => $post->ID,
			'action'    => 'set-publish',
		)
	);
}
?>
<anspress-link data-href="<?php echo esc_attr( $href ); ?>" data-method="POST" class="anspress-apq-item-action anspress-apq-item-action-view"><?php esc_html_e( 'Public', 'anspress-question-answer' ); ?></anspress-link>
