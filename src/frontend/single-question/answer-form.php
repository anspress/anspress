<?php
/**
 * Answer form for single question.
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

// Check if question is set or not.
if ( ! isset( $args['question'] ) ) {
	throw new GeneralException( 'Question is required.' );
}

$question = $args['question'];

$answer = isset( $args['answer'] ) ? $args['answer'] : null;

$answerFormArgs = array(
	'question_id'    => $question->ID,
	'form_loaded'    => $args['form_loaded'] ?? false,
	'load_form_path' => Router::route(
		'v1.questions.actions',
		array(
			'question_id' => $question->ID,
			'action'      => 'load-answer-form',
		)
	),
	'form_action'    => $answer ? Router::route(
		'v1.answers.update',
		array( 'answer_id' => $answer->ID )
	) : Router::route(
		'v1.answers.create',
		array(
			'question_id' => $question->ID,
		)
	),
	'load_tinymce'   => 'anspress-answer-content',
);
?>

<anspress-answer-form data-anspress-id="answer-form-c-<?php echo (int) $question->ID; ?>" class="anspress-apq-item anspress-answer-form-c" data-anspress="<?php echo esc_attr( wp_json_encode( $answerFormArgs ) ); ?>">
	<div class="anspress-apq-item-avatar">
		<a href="<?php ap_profile_link(); ?>">
			<?php ap_author_avatar( ap_opt( 'avatar_size_qquestion' ), $question->ID ); ?>
		</a>
	</div>
	<div class="anspress-apq-item-content">
		<div class="anspress-apq-item-qbody anspress-card">
			<form
				class="anspress-form anspress-answer-form"
				method="post"
				data-anspress-form="answer"
			>
				<?php if ( ! $answerFormArgs['form_loaded'] ) : ?>

					<div class="anspress-form-overlay" data-anspressel="load-form">
						<?php esc_html_e( 'Type your answer here...', 'anspress-question-answer' ); ?>
					</div>

				<?php else : ?>

					<div data-anspress-field="post_content" class="anspress-form-group">
						<textarea
							data-anspress-tinymce-field
							id="anspress-answer-content"
							name="post_content"
							class="anspress-form-control"
							placeholder="<?php esc_attr_e( 'Type your answer here.', 'anspress-question-answer' ); ?>"
						><?php echo wp_kses_post( apply_filters( 'the_content', $answer?->post_content ?? '' ) ); ?></textarea>
					</div>
					<div class="anspress-form-buttons">
						<?php
							$href = Router::route(
								'v1.questions.actions',
								array(
									'question_id' => $question->ID,
									'action'      => 'load-answer-form',
									'form_loaded' => true,
								)
							);
						?>
						<anspress-link data-href="<?php echo esc_attr( $href ); ?>" data-method="POST" data-anspress-id="button:answer:form" class="anspress-button anspress-button-secondary"><?php esc_attr_e( 'Cancel', 'anspress-question-answer' ); ?></anspress-link>
						<button
							data-anspressel="submit"
							data-anspress-button="submit"
							type="submit"
							class="anspress-button anspress-button-primary"
						>
							<?php $answer ? esc_attr_e( 'Update answer', 'anspress-question-answer' ) : esc_html_e( 'Post Answer', 'anspress-question-answer' ); ?>
						</button>
					</div>
				<?php endif; ?>
			</form>
		</div>
	</div>
</anspress-answer-form>
