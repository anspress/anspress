<?php if(ap_user_can_answer(get_question_id())) : ?>
	<div id="answer-form-c">
		<div class="ap-avatar ap-pull-left">
			<a href="<?php echo ap_user_link(get_current_user_id()); ?>">
				<?php echo get_avatar(get_current_user_id(), ap_opt('avatar_size_qquestion')); ?>
			</a>		
		</div>
		<div class="ap-a-cells clearfix">
			<div class="ap-form-head">
				<a href="#" class="apicon-screen-full pull-right ap-btn-fullscreen" data-action="ap_fullscreen_toggle"><?php _e('Toggle fullscreen'); ?></a>
				<ul class="ap-form-head-tab ap-ul-inline clearfix ap-tab-nav">
					<li class="active"><a href="#ap-form-main"><?php _e('Write', 'ap'); ?></a></li>
					<?php if(ap_opt('answer_help_page') != '') : ?>
						<li><a href="#ap-form-help"><?php _e('How to answer', 'ap'); ?></a></li>
					<?php endif; ?>
				</ul>				
			</div>
			<div class="ap-tab-container">
				<div id="ap-form-main" class="active ap-tab-item">
					<?php ap_answer_form(get_question_id()); ?>
				</div>
				<div id="ap-form-help" class="ap-tab-item">
					<?php if(ap_opt('answer_help_page') != ''): ?>						
						<?php ap_how_to_answer(); ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
<?php elseif (is_user_logged_in()): ?>
	<div class="ap-no-permission">
		<?php _e('You dont have permission to answer this question.', 'ap'); ?>
	</div>
<?php else: ?>
	<div class="ap-please-login">
		<?php printf(__('Please %s or %s to answer this question.', 'ap'), '<a data-action="ap_modal" data-toggle="#ap-login-modal" href="'.wp_login_url(get_permalink()).'">'.__('Login', 'ap').'</a>', '<a href="'.wp_registration_url().'">'.__('Sign up', 'ap').'</a>') ?>
		<?php do_action( 'wordpress_social_login' ); ?>
	</div>
	<div id="ap-login-modal" class="ap-modal">
		<div class="ap-modal-backdrop"></div>
		<div class="ap-modal-inner">
			<div class="ap-modal-header">
				<i class="ap-modal-close" data-action="ap_modal_close">&times;</i>
				<h3 class="ap-modal-title"><?php _e('Login', 'ap'); ?></h3>
			</div>
			<div class="ap-modal-body">
				<?php wp_login_form(); ?>
			</div>
		</div>
	</div>
<?php endif; ?>




