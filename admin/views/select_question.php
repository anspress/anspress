<?php
/**
 * Control the output of question select
 *
 * @link https://anspress.net
 * @since 2.0.0
 * @author Rahul Aryan <rah12@live.com>
 * @package AnsPress
 * @since 4.2.0 Fixed: CS bugs.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

?>
<div id="ap-admin-dashboard" class="wrap">
	<?php do_action( 'ap_before_admin_page_title' ); ?>

	<h2><?php esc_attr_e( 'Select a question for new answer', 'anspress-question-answer' ); ?></h2>
	<p><?php esc_attr_e( 'Slowly type for question suggestion and then click select button right to question title.', 'anspress-question-answer' ); ?></p>

	<?php do_action( 'ap_after_admin_page_title' ); ?>

	<div class="ap-admin-container">
		<form class="question-selection">
			<input type="text" name="question_id" class="ap-select-question" id="select-question-for-answer" />
			<input type="hidden" name="is_admin" value="true" />
		</form>
		<div id="similar_suggestions">
			<?php
				$questions = new Question_Query(
					array(
						'post_status'         => array( 'publish', 'private_post' ),
						'post_status__not_in' => array( 'trash' ),
					)
				);
				?>
			<?php if ( $questions->have_questions() ) : ?>
				<h3><?php esc_attr_e( 'Recently active questions', 'anspress-question-answer' ); ?></h3>
				<div class="ap-similar-questions">
					<?php
					while ( $questions->have_questions() ) :
						$questions->the_question();

						$url = add_query_arg(
							array(
								'post_type'   => 'answer',
								'post_parent' => get_the_ID(),
							),
							admin_url( 'post-new.php' )
						);
						?>
						<div class="ap-q-suggestion-item clearfix">
							<a class="select-question-button button button-primary button-small" href="<?php echo esc_url( $url ); ?>"><?php esc_attr_e( 'Select', 'anspress-question-answer' ); ?></a>

							<span class="question-title"><?php echo esc_html( get_the_title() ); ?></span>
							<span class="acount">
								<?php
									echo esc_attr(
										sprintf(
											// translators: %d contain total answer of the question.
											_n( '%d Answer', '%d Answers', ap_get_answers_count(), 'anspress-question-answer' ),
											ap_get_answers_count()
										)
									);
								?>

							</span>
						</div>
					<?php endwhile; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
