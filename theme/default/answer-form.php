<?php
	$current_user = get_userdata( get_current_user_id() );

?>

<div id="answer-form-c">
	<h3 class="ap-widget-title"><?php _e('Your answer', 'ap') ?></h3>	
	<div class="ap-avatar ap-pull-left">
		<?php 
			echo get_avatar( $current_user->user_email, ap_opt('avatar_size_qquestion') ); 
		?>
	</div>
	<div class="no-overflow ap-editor">
		<?php ap_answer_form(get_the_ID()); ?>
	</div>
</div>