<?php
	$current_user = get_userdata( get_current_user_id() );

?>

<div id="answer-form-c">
	<h3 class="ap-widget-title"><?php _e('Your answer', 'ap') ?></h3>	
	<div class="no-overflow ap-editor">
		<?php ap_answer_form(get_the_ID()); ?>
	</div>
</div>