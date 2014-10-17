<?php if($user_meta->description) : ?>
	<div class="ap-profile-box">
		<h3 class="ap-box-title"><?php _e('About Me', 'ap'); ?></h3>
		<p class="about-me">
			<?php echo $user_meta->description; ?>
		</p>
	</div>
<?php else: ?>
	<div class="ap-bo-about-me ap-icon-user ap-soft-warning"><?php _e('User has not written an "About Me" statement.'); ?></div>
<?php endif; ?>

<?php if(ap_user_answer_count($userid) > 0) : ?>
	<div class="ap-profile-box">
		<h3 class="ap-box-title"><?php _e('Answers', 'ap'); ?></h3>
		<?php echo ap_get_user_answers_list($userid); ?>
	</div>
<?php else: ?>
	<div class="ap-bo-about-me ap-icon-chat ap-soft-warning"><?php _e('No answers posted by the user.'); ?></div>
<?php endif; ?>

<?php if(ap_user_question_count($userid) > 0) : ?>
	<div class="ap-profile-box">
		<h3 class="ap-box-title"><?php _e('Questions', 'ap'); ?></h3>
		<?php echo ap_get_user_question_list($userid); ?>
	</div>
<?php else: ?>
	<div class="ap-bo-about-me ap-icon-comment ap-soft-warning"><?php _e('No question posted by the user.'); ?></div>
<?php endif; ?>
