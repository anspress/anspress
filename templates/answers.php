<?php
/**
 * Answers content
 * Control the output of answers.
 *
 * @link https://anspress.net/anspress
 * @since 2.0.1
 * @author Rahul Aryan <rah12@live.com>
 * @package AnsPress
 */

?>
<apanswersw style="<?php echo ! ap_have_answers() ? 'display:none' : ''; ?>">

	<div id="ap-answers-c">
		<div class="ap-sorting-tab clearfix">
			<h3 class="ap-answers-label ap-pull-left" ap="answers_count_t">
				<?php $count = ( '' !== get_query_var( 'answer_id' ) ? ap_get_answers_count() : ap_total_answers_found() ); ?>
				<span itemprop="answerCount"><?php echo (int) $count; ?></span>
				<?php echo _n( 'Answer', 'Answers', $count, 'anspress-question-answer' ); ?>
			</h3>

			<?php ap_answers_tab( get_the_permalink() ); ?>
		</div>

		<?php
		if ( '' === get_query_var( 'answer_id' ) && ap_have_answers() ) {
			ap_answers_the_pagination();
		}
		?>

		<div id="answers">
			<apanswers>
				<?php if ( ap_have_answers() ) : ?>

					<?php
					while ( ap_have_answers() ) :
						ap_the_answer();
?>
						<?php include ap_get_theme_location( 'answer.php' ); ?>
					<?php endwhile; ?>

				<?php endif; ?>
			</apanswers>

		</div>

		<?php if ( ap_have_answers() ) : ?>
			<?php ap_answers_the_pagination(); ?>
		<?php endif; ?>
	</div>
</apanswersw>
