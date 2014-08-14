<!-- #primary BEGIN -->
<div id="ap-ask-page" class="clearfix">	
	<?php if (ap_user_can_ask()): ?>
		<?php ap_ask_form(); ?>
	<?php else: ?>
		<h2>Please login or register to ask question</h2>
	<?php endif; ?>	
</div>
