<?php
/**
 * Delete post button.
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
if ( ! isset( $args ['post'] ) ) {
	throw new InvalidArgumentException( 'Post argument is required.' );
}

if ( ! PostHelper::isValidPostType( $args['post']->post_type ) ) {
	return;
}

$postType = $args['post']->post_type;

$context = PostHelper::isQuestion( $post ) ? array( 'question' => $args['post'] ) : array( 'answer' => $args['post'] );

if ( ! Auth::currentUserCan( $postType . ':delete', $context ) ) {
	return;
}

if ( 'question' === $args['post']->post_type ) {
	$href = Router::route(
		'v1.questions.actions',
		array(
			'question_id' => $args['post']->ID,
			'action'      => 'delete-question',
		)
	);
} else {
	$href = Router::route(
		'v1.answers.actions',
		array(
			'answer_id' => $args['post']->ID,
			'action'    => 'delete-answer',
		)
	);
}
?>
<anspress-link data-href="<?php echo esc_attr( $href ); ?>" data-method="POST" class="anspress-apq-item-action anspress-apq-item-action-view"><?php esc_html_e( 'Delete', 'anspress-question-answer' ); ?></anspress-link>
