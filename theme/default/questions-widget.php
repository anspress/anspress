<div class="ap-qw clearfix">
	<?php if ( $question->have_posts() ) :
		/* Start the Loop */
		while ( $question->have_posts() ) : $question->the_post();
			global $post;
			?>
			<article>
				<?php if(!empty($avatar)) : ?>
					<div class="ap-avatar">
						<a href="<?php echo ap_user_link(); ?>">
							<?php echo get_avatar( get_the_author_meta( 'user_email' ), $avatar ); ?>
						</a>
					</div>
				<?php endif; ?>
				<div class="summery wrap-left">
						<a class="question-hyperlink" href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
						<?php //echo ap_get_question_label(null, true); ?>				
					<ul class="list-taxo ap-inline-list clearfix">
						
						<?php if($post->selected): ?>
							<li class="ap-tip ap-ansslable" title="<?php _e('Answer is accepted', 'ap'); ?>">
								<i class="ap-icon-answer"></i>
							</li>
						<?php endif; ?>
						
						<?php if($show_activity) : ?>
						<li class="list-meta ap-tip" title="<?php _e('Last activity', 'ap'); ?>">	
							<i class="ap-icon-clock ap-meta-icon"></i>
							<?php 							
								printf(
									'<span class="when">%s ago</span>',
									ap_human_time(get_the_time('U'))
								); 
								ap_user_display_name();
							?>
						</li>
						<?php endif; ?>
						
						<?php if($show_vote) : ?>
						<li class="ap-tip">
							<i class="ap-icon-thumbsup ap-meta-icon"></i>
							<?php echo ap_net_vote(); ?>
							<?php  _e('Votes', 'ap'); ?>
						</li>
						<?php endif; ?>
						
						<?php if($show_answers) : ?>
						<li class="ap-tip">
							<i class="ap-icon-comment ap-meta-icon"></i>
							<?php echo ap_count_ans_meta(); ?>
							<?php _e('Ans', 'ap');?>
						</li>
						<?php endif; ?>
						
						<?php if($show_views) : ?>						
							<li class="ap-tip" title="<?php _e('Question was viewed by', 'ap'); ?>"><i class="ap-icon-hit ap-meta-icon"></i><?php  printf(__('%d Views', 'ap'), ap_get_qa_views()); ?></li>
						<?php endif; ?>
						
						<?php if($show_category) : ?>
							<li class="ap-tip" title="<?php _e('Question category', 'ap'); ?>"><?php ap_question_categories_html(false, false); ?></li>
						<?php endif; ?>
						
						<?php if($show_tags) : ?>
							<li class="ap-tip" title="<?php _e('Question tagged', 'ap'); ?>"><?php ap_question_tags_html(false, false); ?></li>
						<?php endif; ?>
					</ul>
					
				</div>
			</article><!-- list item -->
			<?php
		endwhile;

		else : 
			include(ap_get_theme_location('content-none.php'));
		endif; 
	?>	
</div>


