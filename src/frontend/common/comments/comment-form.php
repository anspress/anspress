<?php
/**
 * Comment form for single question.
 *
 * @since 5.0.0
 * @package AnsPress
 */

use AnsPress\Classes\Router;
use AnsPress\Exceptions\GeneralException;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

// Check if post is set or not.
if ( ! isset( $post ) ) {
	throw new GeneralException( 'Post is required.' );
}

$formLoaded  = $args['form_loaded'] ?? false;
$postComment = $args['comment'] ?? null;

$answerFormArgs = array(
	'question_id' => $post->ID,
	'form_loaded' => $postComment ? true : $formLoaded,
	'form_action' => Router::route( 'v1.posts.createComment', array( 'post_id' => $post->ID ) ),
);
?>
<anspress-comment-form data-anspress-id="comment-form-c-<?php echo (int) $post->ID; ?>" class="anspress-comment-form-c anspress-card" data-anspress="<?php echo esc_attr( wp_json_encode( $answerFormArgs ) ); ?>" id="anspress-comment-form<?php echo $postComment ? '-' . (int) $postComment->comment_ID : ''; ?>">
	<?php if ( ! $answerFormArgs['form_loaded'] ) : ?>
		<div data-anspressel="load-form" class="anspress-form-overlay">
			<?php esc_html_e( 'Type your comment here...', 'anspress-question-answer' ); ?>
		</div>
	<?php else : ?>
		<form class="anspress-form anspress-comment-form" data-anspressel="comment-form" data-anspress-form="comment-form" @submit.prevent="submitForm">
			<div data-anspress-field="comment_content" class="anspress-form-group">
				<textarea name="comment_content" class="anspress-form-control" placeholder="Write your comment..."><?php echo esc_textarea( $postComment ? $postComment->comment_content : '' ); ?></textarea>
			</div>
			<div data-anspress-field="comment_content" class="anspress-comments-form-buttons">

				<button data-anspress-id="comment:button:cancel" class="anspress-comments-form-cancel anspress-button" type="button"><?php esc_attr_e( 'Cancel', 'anspress-question-answer' ); ?></button>

				<button class="anspress-comments-form-submit anspress-button" type="submit"><?php esc_attr_e( 'Submit', 'anspress-question-answer' ); ?></button>
			</div>
		</form>
	<?php endif; ?>
</anspress-comment-form>
