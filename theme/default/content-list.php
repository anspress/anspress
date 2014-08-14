<?php

 $clearfix_class = array('question-summary clearfix');

?>

	<article id="question-<?php the_ID(); ?>" <?php post_class($clearfix_class); ?>>
		<?php if ( is_sticky() && is_home() && ! is_paged() ) : ?>
		<div class="featured-post">
			<?php _e( 'Featured post', 'ap' ); ?>
		</div>
		<?php endif; ?>
			<div class="wrap-right">
				<a class="ap-answer-count" href="<?php echo ap_answers_link(); ?>">
					<span><?php echo ap_count_ans_meta(); ?></span>
					<?php _e('Ans', 'ap');?>
				</a>
				<a class="ap-vote-count" href="#">
					<span><?php echo ap_net_vote(); ?></span> 
					<?php  _e('Votes', 'ap'); ?>
				</a>
			</div>
			<div class="ap-list-inner">
				<div class="ap-avatar">
					<a href="<?php echo ap_user_link(); ?>">
						<?php echo get_avatar( get_the_author_meta( 'user_email' ), 35 ); ?>
					</a>
				</div>								
				<div class="summery wrap-left">
					<h3 class="question-title">
						<a class="question-hyperlink" href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
					</h3>					
					<ul class="list-taxo ap-inline-list clearfix">
						<li>
							<?php echo ap_get_question_label(); ?>
						</li>
						<li class="list-meta">	
							
							<?php 							
								printf(
									'<span class="when">%s ago</span>',
									ap_human_time(get_the_time('U'))
								); 
								ap_user_display_name();
							?>
						</li>
						<li><?php  printf(__('%d Views', 'ap'), ap_get_qa_views()); ?></li>
						<li><?php ap_question_categories_html(false, false); ?></li>
						<li><?php ap_question_tags_html(false, false); ?></li>
					</ul>
					
				</div>				
			</div>	

	</article><!-- list item -->
