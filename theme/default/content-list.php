<?php
global $post;
$clearfix_class = array('list-question clearfix');

if(!is_private_post() || (is_private_post() && ap_user_can_view_private_post(get_the_ID()))):
?>
<article id="question-<?php the_ID(); ?>" <?php post_class($clearfix_class); ?>>
	<?php if ( is_private_post()) : ?>
		<div class="private-question-label">
			<span><?php _e( 'Private Question', 'ap' ); ?></span>
		</div>
	<?php endif; ?>
	<!-- TODO: ADD OPTION - add option to toggle each count -->
	<div class="ap-count ap-pull-right">	
		<a class="ap-answer-count ap-tip" href="<?php echo ap_answers_link(); ?>" title="<?php _e('Total answers', 'ap'); ?>">
			<span><?php echo ap_count_ans_meta(); ?></span>
			<?php _e('Ans', 'ap');?>
		</a>						
		<a class="ap-vote-count ap-tip" href="#" title="<?php _e('Total views', 'ap'); ?>">
			<span><?php echo ap_get_qa_views(); ?></span> 
			<?php  _e('Views', 'ap'); ?>
		</a>
		<a class="ap-vote-count ap-tip" href="#" title="<?php _e('Total votes', 'ap'); ?>">
			<span><?php echo ap_net_vote(); ?></span> 
			<?php  _e('Votes', 'ap'); ?>
		</a>		
	</div>	
	<div class="ap-list-inner">
		<div class="ap-avatar ap-pull-left">
			<a href="<?php echo ap_user_link(); ?>">
				<!-- TODO: OPTION - avatar size in question list -->
				<?php echo get_avatar( get_the_author_meta( 'ID' ), 45 ); ?>
			</a>
		</div>								
		<div class="summery no-overflow">
			<span class="question-title entry-title" itemprop="title">
				<a class="question-hyperlink" itemprop="url" href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_title(); ?></a>
			</span>
			<ul class="ap-display-question-meta ap-ul-inline">
				<?php echo ap_display_question_metas() ?>
				<!-- TODOD: hook question labels ap_get_question_label(null, true);  -->
				<!-- TODO: hook tags ap_question_tags_html(false, false) -->
			</ul>
		</div>				
	</div>
</article><!-- list item -->

<?php endif; ?>