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
	<a class="ap-user-posts-vcount ap-tip<?php echo ap_have_answer_selected() ? ' answer-selected' :''; ?>" href="<?php the_permalink(); ?>" title="<?php _e('Votes', 'anspress-question-answer'); ?>">
		<?php echo ap_icon('thumb-up', true); ?>
		<?php ap_votes_net(); ?>
	</a>
	<span class="ap-user-posts-active">
		<?php ap_last_active(); ?>
	</span>
	<div class="no-overflow">
		<a href="<?php the_permalink(); ?>" class="ap-user-posts-title"><?php the_title(); ?></a>
	</div>				
</div>