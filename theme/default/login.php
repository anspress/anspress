<div id="login">
	<div class="panel panel-primary">
		<div class="panel-heading"><?php _e('Login', 'ap'); ?></div>
		<div class="panel-body">
			<form id="askme-login-form" method="post" action="<?php bloginfo('url') ?>/wp-login.php" class="form-horizontal">
				<div class="form-group">	
					<label for="inputEmail3" class="col-sm-2 control-label"><?php _e('Username', 'ap'); ?></label>
					<div class="col-sm-10">		
						<input type="text" name="log" value="<?php echo esc_attr(stripslashes($user_login)); ?>" size="20" id="user_login" class="form-control" placeholder="<?php _e('Username', 'ap'); ?>" />
					</div>
				</div>
				<div class="form-group">
					<label for="inputEmail3" class="col-sm-2 control-label"><?php _e('Password', 'ap'); ?></label>
					<div class="col-sm-10">
						<input type="password" name="pwd" value="" id="user_pass" class="form-control" placeholder="<?php _e('Password', 'ap'); ?>" />
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
					  <div class="checkbox">
						<label>
						  <input type="checkbox" name="rememberme" id="rememberme" /> <?php _e('Remember me', 'ap' );?>
						</label>
					  </div>
					</div>
				</div>
				
				<?php do_action('login_form'); ?>
				
				<div class="form-group">	
					<div class="col-sm-offset-2 col-sm-10">
						<input type="submit" name="user-submit" value="<?php _e('Login', 'ap'); ?>" class="btn btn-primary" />
						<input type="hidden" name="redirect_to" value="<?php echo $_SERVER['REQUEST_URI']; ?>" />
						<input type="hidden" name="user-cookie" value="1" />
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="or-sep"><?php _e('Or', 'ap'); ?></div>
<div id="register">
	<div class="panel panel-success">
		<div class="panel-heading"><?php _e('Register', 'ap'); ?></div>
		<div class="panel-body">
			<form id="askme-register-form" method="post" action="<?php echo site_url('wp-login.php?action=register', 'login_post') ?>" class="form-horizontal">
				<div class="form-group">	
					<label for="inputEmail3" class="col-sm-2 control-label"><?php _e('Username', 'ap'); ?></label>
					<div class="col-sm-10">
						<input type="text" name="user_login" value="<?php echo esc_attr(stripslashes($user_login)); ?>" size="20" id="user_login" class="form-control" placeholder="<?php _e('Username', 'ap'); ?>" />
					</div>
				</div>
				<div class="form-group">
					<label for="email" class="col-sm-2 control-label"><?php _e('email', 'ap'); ?></label>
					<div class="col-sm-10">
						<input id="email" type="text" name="user_email" value="<?php echo esc_attr(stripslashes($user_email)); ?>"  id="user_email" class="form-control" placeholder="<?php _e('Your Email', 'ap'); ?>" />
					</div>
				</div>
				<?php do_action('register_form'); ?>
				<div class="form-group">	
					<div class="col-sm-offset-2 col-sm-10">
						<input type="submit" name="user-submit" value="<?php _e('Sign up!', 'ap'); ?>" class="btn btn-success" />
						<?php $register = $_GET['register']; if($register == true) { echo '<p>'.__('Check your email for the password!', 'ap').'</p>'; } ?>
						<input type="hidden" name="redirect_to" value="<?php echo $_SERVER['REQUEST_URI']; ?>?register=true" />
						<input type="hidden" name="user-cookie" value="1" />
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
