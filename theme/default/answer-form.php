<?php
	$question_id =get_question_id() ;
	$current_user = get_userdata( get_current_user_id() );
	$validate = ap_validate_form();
	
	if(!empty($validate['has_error'])){
		echo '<div class="alert alert-danger" data-dismiss="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>'. __('Problem submitting form, please recheck form', 'ap') .'</div>';
	}
?>
<p class="ap-ans-form-label"><?php _e('Your answer', 'ap'); ?></p>
<div id="answer-form-c">
	<div class="ap-user">
		<?php 
			echo get_avatar( $current_user->user_email, ap_opt('avatar_size_question') ); 
		?>
	</div>					
	<?php if( ap_user_can_answer($question_id) ): ?>
		<form action="" id="answer_form" class="ap-content-inner" method="POST">					
			<?php 
				ap_editor_content(''); 
				
				if(ap_opt('show_signup'))
					ap_signup_fields(); 

				ap_answer_form_hidden_input($question_id);
			?>
		</form>
	<?php endif; ?>
</div>