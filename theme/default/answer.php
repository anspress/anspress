<?php
/**
 * Answer content
 * 	
 * @author Rahul Aryan <rah12@live.com>
 * @link http://wp3.in/anspress
 * @since 0.1
 *
 * @package AnsPress
 */

if(!ap_user_can_view_post(get_the_ID())){
	include ap_get_theme_location('no-permission-post.php');
	return;
}

$class = ap_is_best_answer(get_the_ID()) ? ' selected' : '';
?>

<!-- TODO: add post_class() -->
<div id="answer_<?php echo get_the_ID(); ?>" <?php post_class($class) ?> data-id="<?php echo get_the_ID(); ?>" itemprop="suggestedAnswer<?php echo ap_is_best_answer(get_the_ID()) ? ' acceptedAnswer' : ''; ?>" itemtype="http://schema.org/Answer" itemscope="">
	<div class="ap-content clearfix">		
		<div class="ap-avatar ap-pull-left">
			<a href="<?php echo ap_user_link(get_the_author_meta('ID')); ?>">
				<?php echo get_avatar( get_the_author_meta( 'user_email' ), ap_opt('avatar_size_qanswer') ); ?>
			</a>
			<div class="ap-single-vote"><?php ap_vote_btn(); ?></div>			
		</div>
		<div class="ap-content-inner no-overflow">
			<?php echo ap_select_answer_btn_html(get_the_ID()); ?>
			<div class="ap-answer-metas clearfix">
				<?php ap_user_display_meta(true, false, true); ?>

				<ul class="ap-display-question-meta ap-ul-inline">
					<?php echo ap_display_answer_metas() ?>
				</ul>
				
			</div>
			<div class="ap-answer-content ap-post-content" itemprop="text">
				<?php
					the_content();
				?>
			</div>
			<?php ap_post_actions_buttons() ?>
			<?php comments_template(); ?>
		</div>			
	</div>	
</div>