<?php
/**
 * Answer form for single question.
 *
 * @since 5.0.0
 * @package AnsPress
 */

use AnsPress\Exceptions\GeneralException;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

// Check if question is set or not.
if ( ! isset( $args['question'] ) ) {
	throw new GeneralException( 'Question is required.' );
}

$question = $args['question'];

$answerFormArgs = array(
	'question_id'    => $question->ID,
	'form_loaded'    => $args['form_loaded'] ?? false,
	'load_form_path' => '/anspress/v1/post/' . $question->ID . '/load-answer-form',
	'form_action'    => '/anspress/v1/post/' . $question->ID . '/answers',
);
?>
<div class="anspress-apq-item anspress-answer-form-c" data-anspressel="answer-form" data-anspress="<?php echo esc_attr( wp_json_encode( $answerFormArgs ) ); ?>">
	<div class="anspress-apq-item-avatar">
		<a href="<?php ap_profile_link(); ?>">
			<?php ap_author_avatar( ap_opt( 'avatar_size_qquestion' ), $question->ID ); ?>
		</a>
	</div>
	<div class="anspress-apq-item-content">
		<div class="anspress-apq-item-qbody">
			<form
				data-anspressel
				class="anspress-form anspress-answer-form"
				method="post"
				data-anspress-form="answer"
				@submit.prevent="submitForm"
			>
				<?php if ( ! $answerFormArgs['form_loaded'] ) : ?>

					<div data-anspressel class="anspress-form-overlay" @click.prevent="loadForm">
						<?php esc_html_e( 'Type your answer here...', 'anspress-question-answer' ); ?>
					</div>

				<?php else : ?>

					<div data-anspress-field="post_content" class="anspress-form-group">
						<textarea
							id="anspress-answer-content"
							name="post_content"
							class="anspress-form-control"
							placeholder="<?php esc_attr_e( 'Type your answer here.', 'anspress-question-answer' ); ?>"
						></textarea>
					</div>
					<div class="anspress-form-buttons">
						<button
							data-anspressel="submit"
							data-anspress-button="submit"
							type="submit"
							class="anspress-button anspress-button-primary"
						>
							<?php esc_html_e( 'Post Answer', 'anspress-question-answer' ); ?>
						</button>
					</div>
				<?php endif; ?>
			</form>
		</div>
	</div>
</div>
