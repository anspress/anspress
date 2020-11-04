<?php
/**
 * Answers loop template.
 *
 * @link    https://anspress.net/anspress
 * @since   4.2.0
 * @author  Rahul Aryan <rah12@live.com>
 * @package AnsPress
 * @package Templates
 */

namespace AnsPress\Post;
?>
<apanswersw style="<?php echo ! ap_have_answers() ? 'display:none' : ''; ?>">

	<div id="ap-answers-c">
		<div class="ap-sorting-tab clearfix">
			<h3 class="ap-answers-label ap-pull-left" ap="answers_count_t">
				<span itemprop="answerCount"><?php answer_count(); ?></span>
				<?php echo _n( 'Answer', 'Answers', get_answer_count(), 'anspress-question-answer' ); ?>
			</h3>

			<?php ap_get_template_part( 'tab-answers' ); ?>
		</div>

		<div id="answers">
			<apanswers>
				<?php if ( ap_have_answers() ) : ?>

					<?php while ( ap_have_answers() ) : ap_the_answer(); ?>
						<?php ap_get_template_part( 'loop-answer' ); ?>
					<?php endwhile; ?>

				<?php endif; ?>
			</apanswers>

		</div>

		<?php ap_get_template_part( 'pagination-answers' ); ?>

	</div>
</apanswersw>
