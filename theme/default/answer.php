<?php
/**
 * Answer content
 * 	
 * @author Rahul Aryan <support@anspress.io>
 * @link http://anspress.io/anspress
 * @since 0.1
 *
 * @package AnsPress
 */

if(!ap_answer_user_can_view()){
	include ap_get_theme_location('no-permission-post.php');
	return;
}
?>
<div id="answer_<?php echo get_the_ID(); ?>" <?php post_class() ?> data-id="<?php echo get_the_ID(); ?>" itemprop="suggestedAnswer<?php echo ap_answer_is_best() ? ' acceptedAnswer' : ''; ?>" itemtype="http://schema.org/Answer" itemscope="">
	<div class="ap-avatar ap-pull-left">
		<a href="<?php ap_answer_the_author_link(); ?>">
			<?php ap_answer_the_author_avatar(); ?>
		</a>		
	</div>
	<div class="ap-q-cells ap-content clearfix">
		<div class="ap-q-metas clearfix">
			<div class="ap-single-vote ap-pull-right"><?php ap_answer_the_vote_button(); ?></div>
			<?php ap_user_display_meta(true, false, true); ?>

			<ul class="ap-display-question-meta ap-ul-inline">
				<?php echo ap_display_answer_metas() ?>
			</ul>
		</div>
		<div class="ap-q-inner">			
			<div class="ap-answer-content ap-q-content" itemprop="text">
				<?php the_content(); ?>
			</div>
			<?php ap_post_actions_buttons() ?>			
		</div>
		<?php if ( is_private_post()) : ?>
			<div class="ap-notice black clearfix">
				<i class="apicon-lock"></i><span><?php _e( 'Answer is marked as a private, only admin and post author can see.', 'ap' ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( is_post_waiting_moderation()) : ?>
			<div class="ap-notice yellow clearfix">
				<i class="apicon-info"></i><span><?php _e( 'Answer is waiting for approval by moderator.', 'ap' ); ?></span>
			</div>
		<?php endif; ?>
		<?php ap_answer_the_comments(); ?>
	</div>	
</div>