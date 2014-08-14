<!-- #primary BEGIN -->
<div id="ap-ask-page" class="clearfix">	
	<?php if (ap_user_can_ask()): ?>
		<h2><?php _e('Ask a new question', 'ap'); ?></h2>
		<?php ap_ask_form(); ?>
	<?php else: ?>
		<h2>Please login or register to ask question</h2>
	<?php endif; ?>	
</div>
