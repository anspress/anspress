<?php
/**
 * Answer loop item for user answers list in about page.
 *
 * @link https://anspress.io
 * @since unknown
 * @package AnsPress
 */
?>
<div class="ap-user-posts-item clearfix">
	<a class="ap-user-posts-vcount ap-tip<?php echo ap_question_best_answer_selected() ? ' answer-selected' :''; ?>" href="<?php ap_answer_the_permalink(); ?>" title="<?php _e('Votes', 'anspress-question-answer'); ?>">
		<?php echo ap_icon('thumb-up', true); ?>
		<?php echo ap_answer_get_the_net_vote(); ?>
	</a>
	<span class="ap-user-posts-active">
		<?php ap_answer_the_active_ago(); ?>
	</span>
	<div class="no-overflow">
		<a href="<?php ap_answer_the_permalink(); ?>" class="ap-user-posts-title"><?php the_title(); ?></a>
	</div>				
</div>