<?php
/**
 * Answer list item template
 *
 * @link http://wp3.in
 * @since 0.1
 * @license GPL 2+ 
 * @package AnsPress
 */

if(!ap_user_can_view_post(get_the_ID()))
	return;

global $post;
$clearfix_class = array('ap-questions-item clearfix');

?>
<div id="answer-<?php the_ID(); ?>" <?php post_class($clearfix_class); ?>>
	<?php if ( is_private_post()) : ?>
		<div class="private-question-label clearfix">
			<span><?php _e( 'Private Answer', 'ap' ); ?></span>
		</div>
	<?php endif; ?>		
	<div class="ap-questions-inner">
		<div class="ap-avatar ap-pull-left">
			<a href="<?php echo ap_user_link(); ?>">
				<!-- TODO: OPTION - avatar size in question list -->
				<?php echo get_avatar( get_the_author_meta( 'ID' ), 45 ); ?>
			</a>
		</div>
		<div class="pull-right">
			<?php if(!ap_opt('disable_voting_on_question')){ ?>
				<span class="ap-questions-count ap-questions-vcount">
					<span><?php echo ap_net_vote() ?></span>
					<?php  _e('votes', 'ap'); ?>
				</span>
			<?php } ?>
		</div>
		<div class="ap-questions-summery no-overflow">
			<span class="ap-questions-title entry-title" itemprop="title">
				<a class="ap-questions-hyperlink" itemprop="url" href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_title(); ?></a>
			</span>			
			<div class="ap-display-question-meta">
				<?php echo ap_display_answer_metas() ?>
			</div>
		</div>				
	</div>	
</div><!-- list item -->
