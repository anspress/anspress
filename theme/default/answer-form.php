<?php
	$question_id =get_question_id() ;
	global $current_user;
	$validate = ap_validate_form();
	
	if(isset($validate['has_error']) && $validate['has_error']){
		echo '<div class="alert alert-danger">'. implode(', ', $validate['message']) .'</div>';
	}
?>

<h3 class="your-answer-label"><?php _e('Your Answer', 'ap'); ?></h3>
<div id="answer-form-c">
	<div class="user-header clearfix">
		<div class="ap-avatar">
			<?php 
				$current_user = wp_get_current_user();	
				echo get_avatar( $current_user->user_email, 20 ); 
			?>
		</div>					
		<div class="user-meta">
			<?php echo ap_user_display_name($current_user->ID); ?>								
		</div>						
	</div>
	
	<?php if( ap_user_can_answer($question_id) ): ?>
		<form action="" id="answer_form" method="POST">
			<fieldset>						
				<?php ap_editor_content(''); ?>
			</fieldset>
			<?php
				do_action('ap_answer_form');
				ap_answer_form_hidden_input($question_id);
			?>
		</form>
	<?php endif; ?>
</div>