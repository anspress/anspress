<?php
/**
 * Edit post button.
 *
 * @package AnsPress
 * @since   5.0.0
 */

use AnsPress\Classes\Auth;
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

$postType = QuestionModel::POST_TYPE === $args['post']->post_type ? QuestionModel::POST_TYPE : AnswerModel::POST_TYPE;

$context = QuestionModel::POST_TYPE === $postType ? array( 'question' => $args['post'] ) : array( 'answer' => $args['post'] );

if ( ! Auth::currentUserCan( $postType . ':update', $context ) ) {
	return;
}

?>
<?php if ( 'question' === $args['post']->post_type ) : ?>
	<a href="<?php echo esc_url( ap_post_edit_link( $args['post'] ) ); ?>"class="anspress-apq-item-action anspress-apq-item-action-delete"><?php esc_html_e( 'Edit', 'anspress-question-answer' ); ?></a>
<?php else : ?>
	<?php
		$href = Router::route(
			'v1.answers.actions',
			array(
				'answer_id' => $args['post']->ID,
				'action'    => 'load-answer-edit-form',
			)
		);
	?>
	<anspress-link data-href="<?php echo esc_attr( $href ); ?>" data-method="POST" class="anspress-apq-item-action anspress-apq-item-action-edit"><?php esc_html_e( 'Edit', 'anspress-question-answer' ); ?></anspress-link>
<?php endif; ?>
