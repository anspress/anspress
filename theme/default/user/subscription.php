<?php $active = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'question'; ?>
<div class="ap-user-posts">
	<h3>
		<?php ap_user_subscription_tab(); ?>
	</h3>
	<?php if($active == 'question'): ?>
		<?php if(ap_have_questions()): ?>

			<?php while ( ap_questions() ) : ap_the_question(); ?>
				<?php ap_get_template_part('user/list-question'); ?>
			<?php endwhile; ?>

			<?php ap_questions_the_pagination(); ?>

		<?php else: ?>

			<?php _e('No question subscribed yet!', 'ap'); ?>

		<?php endif; ?>
		
		<?php wp_reset_postdata(); ?>

	<?php endif; ?>
	<?php do_action('ap_user_subscription_page'); ?>
</div>