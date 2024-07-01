<?php
/**
 * Feature post button.
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

if ( ! Auth::currentUserCan( 'question:feature', array( 'question' => $post ) ) ) {
	return;
}

$href = Router::route(
	'v1.questions.actions',
	array(
		'question_id' => $post->ID,
		'action'      => 'toggle-featured',
	)
);
?>
<anspress-link
	data-href="<?php echo esc_attr( $href ); ?>"
	data-method="POST"
	class="anspress-apq-item-action anspress-apq-item-action-feature"><?php ap_is_featured_question( $post ) ? esc_html_e( 'Unfeature', 'anspress-question-answer' ) : esc_html_e( 'Feature', 'anspress-question-answer' ); ?></anspress-link>
