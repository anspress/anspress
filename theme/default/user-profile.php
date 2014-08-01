<div class="ap-profile-box">
	<h3 class="ap-box-title"><?php _e('About Me', 'ap'); ?></h3>
	<p class="about-me">
		<?php echo $user_meta->description; ?>
	</p>
</div>
<div class="ap-profile-box ap-half-col">
	<h3 class="ap-box-title"><?php _e('Answers', 'ap'); ?></h3>
	<?php echo ap_get_user_answers_list($userid); ?>
</div>
<div class="ap-profile-box ap-half-col">
	<h3 class="ap-box-title"><?php _e('Questions', 'ap'); ?></h3>
	<?php echo ap_get_user_question_list($userid); ?>
</div>
