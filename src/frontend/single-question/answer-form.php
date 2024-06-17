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

?>
<div data-anspressel="answer-form" class="anspress-answer-form-c">
	<div class="anspress-apq-item">
		<div class="anspress-apq-item-avatar">
			<a href="<?php ap_profile_link(); ?>">
				<?php ap_author_avatar( ap_opt( 'avatar_size_qquestion' ) ); ?>
			</a>
		</div>
		<div class="anspress-apq-item-content">
			<div class="anspress-apq-item-qbody">
				<form
					class="anspress-form anspress-answer-form"
					method="post"
				>
					<div class="anspress-form-group">
						<textarea
							id="anspress-quicktag-editor"
							name="content"
							class="anspress-form-control"
							rows="5"
							required
						></textarea>
					</div>
					<?php wp_nonce_field( 'anspress_answer', 'anspress_answer_nonce' ); ?>
					<input type="hidden" name="action" value="anspress_answer" />
					<input type="hidden" name="post_id" value="<?php echo esc_attr( $args['question']->ID ); ?>" />
					<input type="hidden" name="ap_form" value="answer" />
					<button type="submit" class="anspress-button anspress-btn-primary">
						<?php esc_html_e( 'Submit Answer', 'anspress-question-answer' ); ?>
					</button>
				</form>
			</div>
		</div>
	</div>
</div>
