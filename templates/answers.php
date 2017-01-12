<?php
/**
 * Answers content
 * Control the output of answers.
 *
 * @link https://anspress.io/anspress
 * @since 2.0.1
 * @author Rahul Aryan <support@anspress.io>
 * @package AnsPress
 */

?>
<ap-answers-w style="<?php echo ! ap_have_answers() ? 'display:none' : ''; ?>">
	<div id="ap-answers-c">
		<div class="ap-sorting-tab clearfix">
			<h3 class="ap-answers-label ap-pull-left" ap-answerscount-text>
				<?php
					$count = ( '' !== get_query_var( 'answer_id' ) ? ap_get_answers_count() : ap_total_answers_found() );
					printf(
						_n( '%d Answer', '%d Answers', $count, 'anspress-question-answer' ),
						$count
					);
				?>
			</h3>
			<?php ap_answers_tab( get_the_permalink() ); ?>
		</div>

		<?php
		if ( '' === get_query_var( 'answer_id' ) ) {
			ap_answers_the_pagination();
		}
		?>

		<div id="answers">

				<ap-answers>
					<?php while ( ap_have_answers() ) : ap_the_answer(); ?>
						<?php include( ap_get_theme_location( 'answer.php' ) ); ?>
					<?php endwhile ; ?>
				</ap-answers>
		</div>

		<?php ap_answers_the_pagination(); ?>

	</div>
</ap-answers-w>

