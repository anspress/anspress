<?php
 /**
  * Display answer item in question page
  *
  * @link http://wp3.in
  * @since 0.1
  * @package AnsPress
  */

if(!ap_user_can_view_post(get_the_ID()))
	return;

global $post;
$clearfix_class = array('list-question clearfix');

?>
<article id="answer-<?php the_ID(); ?>" <?php post_class($clearfix_class); ?>>
	<?php if ( is_private_post()) : ?>
		<div class="private-question-label clearfix">
			<span><?php _e( 'Private answer', 'ap' ); ?></span>
		</div>
	<?php endif; ?>		
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
			<ul class="ap-display-question-meta ap-ul-inline clearfix">
				<?php echo ap_display_answer_metas() ?>
			</ul>
			<div class="ap-history">
				<?php 
					if(ap_is_best_answer()){
						echo '<span class="ap-best-answer-label ap-tip" title="'.__('Selected as a best answer', 'ap').'">'.__('Selected', 'ap').'</span>';
					}
					echo ap_get_latest_history_html(get_the_ID());					
				?>
			</div>
		</div>				
	</div>	
</article><!-- list item -->
