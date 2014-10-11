<?php
	$question_id =get_question_id() ;
	$current_user = get_userdata( get_current_user_id() );
	$validate = ap_validate_form();
	
	if(!empty($validate['has_error'])){
		echo '<div class="alert alert-danger" data-dismiss="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>'. __('Problem submitting form, please recheck form', 'ap') .'</div>';
	}
?>

<div id="answer-form-c">	
	<div class="ap-avatar">
		<?php 
			echo get_avatar( $current_user->user_email, ap_opt('avatar_size_qquestion') ); 
		?>
	</div>
	<?php ap_answer_form($question_id); ?>
</div>