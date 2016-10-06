<?php
/**
 * Question loop item for user question list in about page.
 *
 * @link https://anspress.io
 * @since unknown
 * @package AnsPress
 */
?>

<div class="ap-user-posts-item clearfix">
	<a class="ap-user-posts-vcount ap-tip<?php echo ap_have_answer_selected() ? ' answer-selected' :''; ?>" href="<?php the_permalink(); ?>" title="<?php esc_attr_e('Votes', 'anspress-question-answer' ); ?>">
		<?php echo ap_icon('thumb-up', true ); ?>
		<?php ap_votes_net(); ?>
    </a>
    <span class="ap-user-posts-active">
		<?php ap_last_active(); ?>
    </span>
	<a class="ap-user-posts-ccount ap-tip" href="<?php the_permalink(); ?>" title="<?php esc_attr_e('Answers', 'anspress-question-answer' ); ?>">
		<?php printf(__('%d answers', 'anspress-question-answer' ), ap_get_answers_count() ); ?>
    </a>
    <div class="no-overflow">
		<a href="<?php the_permalink(); ?>" class="ap-user-posts-title"><?php the_title(); ?></a>
    </div>              
</div>
