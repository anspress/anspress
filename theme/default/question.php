<?php
while ( $question->have_posts() ) : $question->the_post(); 
?>
<div id="ap-single" class="ap-container clearfix">
	<h2 class="question-title">
		<a href="<?php get_permalink(); ?>" rel="bookmark"><?php echo the_title(); ?></a>
	</h2>
	<?php ap_favourite_html(); ?>
	<div id="question" role="main" class="ap-content question">	
		<ul class="ap-question-meta clearfix">
			<li><span class="question-status <?php echo ap_get_question_status(); ?>"><?php echo ap_get_question_status(); ?></span></li>
			<li>
				<span class="views-count">
					<?php echo ap_get_qa_views(get_the_ID()) .' '. __('Views', 'ap'); ?>
				</span>
			</li>
			<li><?php ap_question_categories(); ?></li>
			<li class="pull-right"><?php ap_question_tags(); ?></li>
		</ul>
		<div class="ap-question-cells clearfix">
			<div class="ap-single-vote"><?php ap_vote_html(); ?></div>
						
			<div class="ap-content-inner">				
				<div class="ap-user-meta">
					<div class="ap-avatar">
						<?php echo get_avatar( get_the_author_meta( 'user_email' ), ap_opt('avatar_size_question') ); ?>
					</div>
					<div class="ap-meta">
						<?php 
							printf( __( '%s <span class="when">asked about %s ago</span>', 'ap' ), ap_user_display_name() , ap_human_time( get_the_time('U')));
						?>							
					</div>			
				</div>			
				<div class="question-content">
					<?php the_content(); ?>									
				</div>				
				<ul class="ap-user-actions clearfix">

					<li>
						<a href="<?php echo ap_question_edit_link(get_the_ID()); ?>" title="<?php _e('Edit this question', 'ap'); ?>"><?php _e('Edit', 'ap') ; ?></a>
					</li>				
					<li><?php ap_comment_btn_html(); ?></li>					
					<li><?php ap_change_status_btn_html(); ?></li>					
					<li><?php ap_close_vote_html(); ?></li>	
					<li><?php ap_flag_btn_html(); ?></li>
				</ul>
				<?php comments_template(); ?>
			</div>	
			
		</div>
	</div>	
	<?php if(ap_have_ans(get_the_ID())){ ?>
		<div id="answers">
			<?php 
				// get answer sorting tab
				ap_ans_tab(); 

				$ans = ap_ans_query(get_the_ID());

				while ( $ans->have_posts() ) : $ans->the_post(); 
					include(ap_get_theme_location('answer.php'));
				endwhile ;
				wp_reset_query();
			?>
		</div>	
	<?php } ?>
		
	<?php 
		if(ap_user_can_answer(get_question_id()))
			include(ap_get_theme_location('answer-form.php')); 
	?>

</div>
<?php 
	endwhile ;
	wp_reset_query(); 
?>