<?php $active = isset($_GET['tab']) ? $_GET['tab'] : 'answers'; ?>
<div class="ap-user-posts">
	<h3>
		<?php ap_user_top_posts_tab(); ?>
	</h3>

	<?php if($active == 'answers'): ?>

		<?php ap_get_answers(array('author' => ap_get_displayed_user_id(), 'showposts' => 10, 'sortby' => 'voted')); ?>
		
		<?php if(ap_have_answers()): ?>

			<?php while ( ap_answers() ) : ap_the_answer(); ?>
				<div class="ap-user-posts-item clearfix">
					<a class="ap-tip ap-user-posts-vcount <?php ap_answer_the_vote_class(); ?>" href="<?php ap_answer_the_permalink(); ?>" title="<?php _e('Votes'); ?>"><?php echo ap_icon('vote_up', true); ?><?php echo ap_answer_get_the_net_vote(); ?></a>
					<span class="ap-user-posts-active"><?php ap_answer_the_active_ago(); ?></span>
					<a class="ap-user-posts-ccount ap-tip" href="<?php ap_answer_the_permalink(); ?>" title="<?php _e('Comments', 'ap'); ?>"><?php echo ap_icon('comment', true); ?><?php echo get_comments_number(); ?></a>
					<div class="no-overflow"><a href="<?php ap_answer_the_permalink(); ?>" class="ap-user-posts-title"><?php the_title(); ?></a></div>				
				</div>
			<?php endwhile; ?>
			<div class="ap-user-posts-footer">
				<?php printf(__('More answers by %s', 'ap'), ap_user_get_the_display_name()); ?>
				<a href="<?php echo ap_user_link(ap_get_displayed_user_id(), 'answers'); ?>"><?php _e('view', 'ap'); ?>&rarr;</a>
			</div>
		<?php else: ?>

			<?php _e('No answer posted yet!', 'ap'); ?>

		<?php endif; ?>
		
		<?php wp_reset_postdata(); ?>

	<?php elseif($active == 'questions'): ?>

		<?php ap_get_questions(array('author' => ap_get_displayed_user_id(), 'showposts' => 10, 'sortby' => 'voted')); ?>
		
		<?php if(ap_have_questions()): ?>

			<?php while ( ap_questions() ) : ap_the_question(); ?>
				<div class="ap-user-posts-item clearfix">
					<a class="ap-user-posts-vcount ap-tip<?php echo ap_question_best_answer_selected() ? ' answer-selected' :''; ?>" href="<?php ap_question_the_permalink(); ?>" title="<?php _e('Answers'); ?>"><?php echo ap_icon('answer', true); ?><?php echo ap_question_get_the_answer_count(); ?></a>
					<span class="ap-user-posts-active"><?php ap_question_the_active_ago(); ?></span>
					<a class="ap-user-posts-ccount ap-tip" href="<?php ap_question_the_permalink(); ?>" title="<?php _e('Comments', 'ap'); ?>"><?php echo ap_icon('comment', true); ?><?php echo get_comments_number(); ?></a>
					<div class="no-overflow"><a href="<?php ap_question_the_permalink(); ?>" class="ap-user-posts-title"><?php the_title(); ?></a></div>				
				</div>
			<?php endwhile; ?>
			<div class="ap-user-posts-footer">
				<?php printf(__('More questions by %s', 'ap'), ap_user_get_the_display_name()); ?>
				<a href="<?php echo ap_user_link(ap_get_displayed_user_id(), 'questions'); ?>"><?php _e('view', 'ap'); ?>&rarr;</a>
			</div>
		<?php else: ?>

			<?php _e('No question asked yet!', 'ap'); ?>

		<?php endif; ?>
		
		<?php wp_reset_postdata(); ?>

	<?php endif; ?>

</div>

