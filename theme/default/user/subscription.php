<div class="ap-user-posts">
	<?php ap_get_questions(array( 'ap_query' => 'ap_subscription_query', 'user_id' => get_current_user_id(), 'sortby' => 'newest')); ?>
	
	<?php if(ap_have_questions()): ?>

		<?php while ( ap_questions() ) : ap_the_question(); ?>
			<?php ap_get_template_part('user/list-item'); ?>
		<?php endwhile; ?>

		<?php ap_questions_the_pagination(); ?>
		
	<?php else: ?>

		<?php _e('No question subscribed yet!', 'ap'); ?>

	<?php endif; ?>
	
	<?php wp_reset_postdata(); ?>

</div>

