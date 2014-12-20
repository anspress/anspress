<?php
global $post;
$clearfix_class = array('question-summary clearfix');

if(!is_private_question() || (is_private_question() && ap_user_can_view_private_question(get_the_ID()))):
?>
<article id="question-<?php the_ID(); ?>" <?php post_class($clearfix_class); ?>>
	<?php if ( is_private_question()) : ?>
		<div class="private-question-label">
			<span><?php _e( 'Private Question', 'ap' ); ?></span>
		</div>
	<?php endif; ?>
	<div class="wrap-right">	
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
		<div class="ap-avatar">
			<a href="<?php echo ap_user_link(); ?>">
				<?php echo get_avatar( get_the_author_meta( 'ID' ), 45 ); ?>
			</a>
		</div>								
		<div class="summery wrap-left">
			<h3 class="question-title entry-title" itemprop="title">
				<a class="question-hyperlink" itemprop="url" href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_title(); ?></a>
			</h3>					
			<ul class="list-taxo ap-inline-list clearfix">
				<?php if($post->selected): ?>							
					<li class="ap-ansslable"><span class="bullet-point">&bull;</span><?php _e('answer accepted', 'ap'); ?></li>
				<?php endif; ?>
				<li class="list-meta">
					<span class="bullet-point">&bull;</span>
					<?php echo ap_get_latest_history_html(get_the_ID())	?>
				</li>						
			</ul>
			<?php if(ap_question_have_labels() || ap_question_have_category() || ap_question_have_tags()): ?>
			<ul class="ap-other-meta ap-inline-list clearfix">
				<?php if(ap_question_have_labels()): ?>
				<li>
					<span class="bullet-point">&bull;</span>
					<?php echo ap_get_question_label(null, true); ?>
				</li>
				<?php endif; ?>
				<?php if(ap_question_have_category()): ?>
					<li class="es-list-cat">
						<span class="bullet-point">&bull;</span>
						<?php ap_question_categories_html(false, false); ?>
					</li>
				<?php endif; ?>
				<?php if(ap_question_have_tags()): ?>
				<li class="es-list-tag">
					<span class="bullet-point">&bull;</span>
					<?php ap_question_tags_html(false, false); ?>
				</li>
				<?php endif; ?>
			</ul>
			<?php endif; ?>
		</div>				
	</div>
</article><!-- list item -->
<?php endif; ?>