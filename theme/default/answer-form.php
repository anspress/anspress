<?php
	$question_id =get_query_var('question_id') ;
	$current_user = get_userdata( get_current_user_id() );
	
	if(!empty($validate['has_error'])){
		echo '<div class="alert alert-danger" data-dismiss="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>'. __('Problem submitting form, please recheck form', 'ap') .'</div>';
	}
?>

<div id="answer-form-c">
	<h3 class="ap-widget-title"><?php _e('Your answer', 'ap') ?></h3>	
	<div class="ap-avatar ap-pull-left">
		<?php 
			echo get_avatar( $current_user->user_email, ap_opt('avatar_size_qquestion') ); 
		?>
	</div>
	<div class="no-overflow ap-editor">
		<?php ap_answer_form($question_id); ?>
	</div>
</div>