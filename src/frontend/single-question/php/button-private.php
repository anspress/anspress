<?php
/**
 * Private post button.
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

// Check if already moderate.
if ( ! PostHelper::isPublishedStatus( $post ) || PostHelper::isPrivateStatus( $post ) ) {
	return;
}

$context = array( $post->post_type => $post );

if ( ! Auth::currentUserCan( $post->post_type . ':update', $context ) ) {
	return;
}

if ( PostHelper::isQuestion( $post ) ) {
	$href = Router::route(
		'v1.questions.actions',
		array(
			'question_id' => $post->ID,
			'action'      => 'make-private',
		)
	);
} else {
	$href = Router::route(
		'v1.answers.actions',
		array(
			'answer_id' => $post->ID,
			'action'    => 'make-private',
		)
	);
}
?>
<anspress-link data-href="<?php echo esc_attr( $href ); ?>" data-method="POST" class="anspress-apq-item-action anspress-apq-item-action-view"><?php esc_html_e( 'Make Private', 'anspress-question-answer' ); ?></anspress-link>
