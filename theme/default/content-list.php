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
				<a class="answer-count" href="<?php echo ap_answers_link(); ?>">
					<span><?php echo ap_count_ans(get_the_ID()); ?></span>
					<?php _e('Ans', 'ap');?>
				</a>
				<a class="views-count" href="#">
					<span><?php echo ap_get_qa_views(get_the_ID()); ?></span> 
					<?php  _e('Views', 'ap'); ?>
				</a>
			</div>
			<div class="ap-list-inner clearfix">
				<div class="ap-avatar">				
					<?php echo get_avatar( get_the_author_meta( 'user_email' ), 35 ); ?>
				</div>								
				<div class="summery wrap-left">
					<h3 class="question-title">
						<a class="question-hyperlink" href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
					</h3>					
					<div class="list-taxo">
						<div class="list-meta">	
							<span class="question-status <?php echo ap_get_question_status(); ?>"><?php echo ap_get_question_status(); ?></span>
							<?php 							
								printf(
									'<span class="when">%s ago</span>',
									ap_human_time(get_the_time('U'))
								); 
								ap_user_display_name();
							?>
						</div>						
						<?php ap_question_categories(true); ?>
						<?php ap_question_tags(); ?>
					</div>
					
				</div>				
			</div>	

	</article><!-- list item -->
