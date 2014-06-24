<?php
while ( $question->have_posts() ) : $question->the_post(); 
?>
<div id="ap-single" class="ap-container clearfix">
	<div id="question" role="main" class="ap-content">	

			<h3 class="question-title">
				<a href="<?php get_permalink(); ?>" rel="bookmark"><?php echo the_title(); ?></a>
			</h3>	
			<div class="question-meta clearfix">
				<span class="question-status <?php echo ap_get_question_status(); ?>"><?php echo ap_get_question_status(); ?></span>
				<span class="views-count">
					<?php echo ap_get_qa_views(get_the_ID()) .' '. __('Views', 'ap'); ?>
				</span>
				<?php ap_question_categories(); ?>
				<?php ap_question_tags(); ?>
			</div>				
			<div class="vote-single pull-left">
				<?php ap_vote_html(); ?>					
			</div>				
			<div class="content-inner">
				<div class="user-header clearfix">
					<?php ap_favourite_html(); ?>
					<div class="ap-avatar">
						<?php echo get_avatar( get_the_author_meta( 'user_email' ), 30 ); ?>
					</div>					
					<div class="user-meta">
						<?php 
							printf( __( '%s <span class="when">asked about %s ago</span>	', 'ap' ), ap_user_display_name() , ap_human_time( get_the_time('U')));
						?>							
					</div>						
				</div>
				<div class="question-content">
					<?php the_content(); ?>									
				</div>					
				<ul class="user-actions clearfix">											
					<li>
						<a href="<?php echo ap_question_edit_link(get_the_ID()); ?>" class="btn edit-btn aicon-edit" title="<?php _e('Edit this question', 'ap'); ?>"><?php _e('Edit', 'ap') ; ?></a>
					</li>				
					<li><?php ap_comment_btn_html(); ?></li>					
					<li><?php ap_change_status_btn_html(); ?></li>					
					<li class="pull-right"><?php ap_close_vote_html(); ?></li>	
					<li class="pull-right"><?php ap_flag_btn_html(); ?></li>
				</ul>					
			</div>	
			<?php comments_template(); ?>				

	</div>	
	<?php if(ap_have_ans(get_the_ID())){ ?>
		<div id="answers">
			<h2 class="answer-count"><span><?php echo ap_count_ans(get_the_ID()); ?></span> <?php _e('Answers', 'ap'); ?></h2>
			<?php 

				$ans_args=array(
				  'post_type' => 'answer',
				  'post_status' => 'publish',
				  'post_parent' => get_the_ID(),
				  'showposts' => -1
				);
				$ans = new WP_Query($ans_args);

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