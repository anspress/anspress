<?php
/**
 * Close post button.
 *
 * @package AnsPress
 * @since   5.0.0
 */

use AnsPress\Classes\Auth;
use AnsPress\Classes\PostHelper;
use AnsPress\Classes\Router;
use AnsPress\Modules\Question\QuestionModel;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

// Check that post argument is set.
if ( ! isset( $post ) ) {
	throw new InvalidArgumentException( 'Post argument is required.' );
}


if ( ! PostHelper::isQuestion( $post ) ) {
	return;
}

if ( ! Auth::currentUserCan( 'question:close', array( 'question' => $post ) ) ) {
	return;
}

$href = Router::route(
	'v1.questions.actions',
	array(
		'question_id' => $post->ID,
		'action'      => 'toggle-closed-state',
	)
);
?>
<anspress-link
	data-href="<?php echo esc_attr( $href ); ?>"
	data-method="POST"
	class="anspress-apq-item-action anspress-apq-item-action-close"><?php is_post_closed( $post ) ? esc_html_e( 'Open', 'anspress-question-answer' ) : esc_html_e( 'Close', 'anspress-question-answer' ); ?></anspress-link>
