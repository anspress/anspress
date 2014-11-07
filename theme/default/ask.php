<!-- #primary BEGIN -->
<h1 class="ap-main-title">
	<?php if (!ap_opt('double_titles'))
	the_title(); 
	?>
</h1>
<div id="ap-ask-page" class="clearfix">	
	<?php if (ap_user_can_ask()): ?>
		<?php ap_ask_form(); ?>
	<?php else: ?>
		<h2>Please login or register to ask question</h2>
	<?php endif; ?>	
</div>
