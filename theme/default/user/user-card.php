<?php
/**
 * Display user card
 *
 * @link http://wp3.in
 * @since unknown
 * @package AnsPress
 */
$cover = ap_get_cover_src(ap_user_get_the_ID());

?>
<div id="user_<?php ap_user_the_ID(); ?>_card" style="display:none">
	<div class="ap-user-cover"<?php if($cover): ?> style="background-image:url(<?php echo $cover; ?>)"<?php endif; ?>>
		<div class="ap-card-gr"></div>
	</div>
	<div class="ap-card-content">
	    <div class="ap-avatar">
			<a href="<?php ap_user_the_link() ; ?>">
				<?php ap_user_the_avatar(80); ?>
			</a>
	        <?php ap_follow_button(ap_user_get_the_ID()); ?>
	    </div>
	    <div class="no-overflow">
	    	<?php do_action('ap_hover_card_before_status', ap_user_get_the_ID()); ?>
	        <a href="<?php ap_user_the_link() ; ?>" class="ap-card-name"><?php ap_user_the_display_name(); ?></a>
	        <div class="ap-card-reputation"><span><?php ap_user_the_reputation(); ?></span><?php _e('Reputation', 'anspress-question-answer'); ?></div>
	        <div class="ap-card-stats">
	            <span><?php echo ap_icon('answer', true); ?><?php printf(__('%d answers, %d selected', 'anspress-question-answer'), ap_user_get_the_meta('__total_answers'), ap_user_get_the_meta('__best_answers')); ?></span>
	            <span><?php echo ap_icon('question', true); ?><?php printf(__('%d questions, %d solved', 'anspress-question-answer'), ap_user_get_the_meta('__total_questions'), ap_user_get_the_meta('__solved_answers')); ?></span>
	            <span><i class="apicon-comment-discussion"></i> <?php printf(__('%d Followers, %d following', 'anspress-question-answer'), ap_user_get_the_meta('__total_followers'), ap_user_get_the_meta('__total_following')); ?></span>
	        </div>
	        <?php do_action('ap_hover_card_after_status', ap_user_get_the_ID()); ?>
	    </div>
	</div>
</div>