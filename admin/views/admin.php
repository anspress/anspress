<?php
$options = get_option('anspress_opt');
$settings = $options + $this->default_options();


if ( ! isset( $_REQUEST['updated'] ) )
	$_REQUEST['updated'] = false; // This checks whether the form has just been submitted. ?>

<div class="wrap">
	<?php screen_icon(); echo '<h2>' . __( 'AnsPress Options' ) . '</h2>';
	// This shows the page's name and an icon if one has been provided ?>
			
	<?php if ( false !== $_REQUEST['updated'] ) : ?>
	<div class="updated fade"><p><strong><?php _e( 'Options saved' ); ?></strong></p></div>
	<?php endif; // If the form has just been submitted, this shows the notification ?>

	<form method="post" action="options.php">

	<?php settings_fields( 'anspress_options' );
		/* This function outputs some hidden fields required by the form,
		including a nonce, a unique number used to ensure the form has been submitted from the admin page
		and not somewhere else, very important for security */ ?>
		<ul id="ap_opt_nav" class="nav nav-tabs">
		  <li class="active"><a href="#ap-general" data-toggle="tab"><?php _e('General', 'ap'); ?></a></li>
		  <li><a href="#ap-answers" data-toggle="tab"><?php _e('Answers', 'ap'); ?></a></li>
		  <li><a href="#ap-theme" data-toggle="tab"><?php _e('Theme', 'ap'); ?></a></li>
		  <li><a href="#ap-pages" data-toggle="tab"><?php _e('Pages', 'ap'); ?></a></li>
		  <li><a href="#ap-misc" data-toggle="tab"><?php _e('Misc', 'ap'); ?></a></li>
		  <li><a href="#ap-maintenance" data-toggle="tab"><?php _e('Maintenance', 'ap'); ?></a></li>
		</ul>

		<!-- Tab panes -->
		<div class="tab-content">
		  <div class="tab-pane active" id="ap-general">		
			<table class="form-table">

				<tr valign="top">
					<th scope="row"><label for="base_page"><?php _e('Base Page', 'ap'); ?></label></th>
					<td>
						<?php wp_dropdown_pages( array('selected'=> $settings['base_page'],'name'=> 'anspress_opt[base_page]','post_type'=> 'page') ); ?>
						<p class="description"><?php _e('This page slug is use as base slug, if this page was selected for home page then no base slug will be added', 'ap'); ?></p>
					</td>
				</tr>			
				

				<tr valign="top">
					<th scope="row">Allow non loggedin to see question & answer form</th>
					<td>
						<fieldset>
							<input type="checkbox" id="allow_non_loggedin" name="anspress_opt[allow_non_loggedin]" value="1" <?php checked( true, $settings['allow_non_loggedin'] ); ?> />
							<label for="allow_non_loggedin">Allow non logged in (but still they need to signup or login)</label>
						</fieldset>
						<fieldset>
							<input type="checkbox" id="show_signup" name="anspress_opt[show_signup]" value="1" <?php checked( true, $settings['show_signup'] ); ?> />
							<label for="show_signup">Show signup form</label>
						</fieldset>
						<fieldset>
							<input type="checkbox" id="login_after_signup" name="anspress_opt[login_after_signup]" value="1" <?php checked( true, $settings['login_after_signup'] ); ?> />
							<label for="login_after_signup">Automatically login after signup</label>
						</fieldset>						
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Author Credits</th>
					<td>
						<input type="checkbox" id="author_credits" name="anspress_opt[author_credits]" value="1" <?php checked( true, $settings['author_credits'] ); ?> />
						<label for="author_credits">Hide Author Credits</label>
					</td>
				</tr>

			</table>
			</div>
			<div class="tab-pane" id="ap-answers">		
				<table class="form-table">

					<tr valign="top">
						<th scope="row"><label for="multiple_answers"><?php _e('Multiple Answers', 'ap'); ?></label></th>
						<td>
							<input type="checkbox" id="multiple_answers" name="anspress_opt[multiple_answers]" value="1" <?php checked( true, $settings['multiple_answers'] ); ?> />
							<label><?php _e('Allow an user to submit multiple answers on a single question', 'ap'); ?></label>
						</td>
					</tr>
				</table>
			</div>			
			<div class="tab-pane" id="ap-theme">		
				<table class="form-table">

					<tr valign="top">
						<th scope="row"><label for="theme"><?php _e('Theme', 'ap'); ?></label></th>
						<td>
							<select name="anspress_opt[theme]" id="theme">
								<?php 
									foreach (ap_theme_list() as $theme)
										echo '<option value="'.$theme.'">'.$theme.'</option>';
								?>									
							</select>
							<p class="description"><?php _e('Set the theme you want to use', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="theme"><?php _e('Include bootstrap', 'ap'); ?></label></th>
						<td>
							<input type="checkbox" id="bootstrap" name="anspress_opt[bootstrap]" value="1" <?php checked( true, $settings['bootstrap'] ); ?> />
							<p class="description"><?php _e('If your theme does not use bootstrap then include it.', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="theme"><?php _e('Avatar size in question page', 'ap'); ?></label></th>
						<td>
							<input type="text" name="anspress_opt[avatar_size_question]" id="avatar_size_question" value="<?php echo $settings['avatar_size_question'] ; ?>" />
							<p class="description"><?php _e('User avatar size for question and answer.', 'ap'); ?></p>
						</td>
					</tr>
				</table>
			</div>			
			
			<div class="tab-pane" id="ap-pages">
				<h3 class="ap-option-section"><?php _e('Tags Page', 'ap'); ?></h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="tags_per_page"><?php _e('Tags per page', 'ap'); ?></label></th>
						<td>
							<input type="text" name="anspress_opt[tags_per_page]" id="tags_per_page" value="<?php echo $settings['tags_per_page'] ; ?>" />								
							<p class="description"><?php _e('Tags to show per page', 'ap'); ?></p>
						</td>
					</tr>
				</table>
			</div>
						
			<div class="tab-pane" id="ap-misc">	
				<h3 class="title">Flag Notes</h3>
				<p class="description"><?php _e('Default notes when flagging the posts', 'ap'); ?></p>
				<?php if(isset($settings['flag_note']) && is_array($settings['flag_note'])) : ?>
				
				<?php 
					$i = 0;
					foreach($settings['flag_note'] as $k => $flag) : 
				?>	
					<table<?php echo $i == 0 ? ' id="first-note"' : ''; ?> class="form-table flag-note-item">
						<tr valign="top">
							<th scope="row"><label><?php _e('Title', 'ap'); ?></label></th>
							<td>							
								<input type="text" class="regular-text" name="anspress_opt[flag_note][<?php echo $k;?>][title]" value="<?php echo $flag['title'];?>" placeholder="Title of the note" />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label><?php _e('Description', 'ap'); ?></label></th>
							<td>							
								<textarea style="width: 500px;" name="anspress_opt[flag_note][<?php echo $k;?>][description]"><?php echo $flag['description'];?></textarea>
								
								<a class="delete-flag-note" href="#">Delete</a>
							</td>
						</tr>
					</table>
				<?php 
					$i++;
					endforeach; 
					else:
				?>				
				<table id="first-note" class="form-table flag-note-item">
					<tr valign="top">
						<th scope="row"><label><?php _e('Title', 'ap'); ?></label></th>
						<td>							
							<input type="text" class="regular-text" name="anspress_opt[flag_note][0][title]" value="" placeholder="Title of the note" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label><?php _e('Description', 'ap'); ?></label></th>
						<td>							
							<textarea style="width: 500px;" name="anspress_opt[flag_note][0][description]"></textarea>
							
							<a class="delete-flag-note" href="#">Delete</a>
						</td>
					</tr>
				</table>
				<?php endif; ?>
				<a id="add-flag-note" href="#">Add more notes</a>
			</div>
			<div class="tab-pane" id="ap-maintenance">
				<h3 class="ap-option-section"><?php _e('Recount Views', 'ap'); ?></h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="recount_views"><?php _e('Recount metas', 'ap'); ?></label></th>
						<td>
							<fieldset>
								<a href="#" data-action="recount-views" class="button-default"><?php _e('Recount views', 'ap'); ?></a>							
								<p class="description"><?php _e('This will recheck views count', 'ap'); ?></p>
							</fieldset>
							<fieldset>
								<a href="#" data-action="recount-votes" class="button-default"><?php _e('Recount votes', 'ap'); ?></a>							
								<p class="description"><?php _e('This will recheck vote count', 'ap'); ?></p>
							</fieldset>
							<fieldset>
								<a href="#" data-action="recount-fav" class="button-default"><?php _e('Recount favourites', 'ap'); ?></a>							
								<p class="description"><?php _e('This will recheck favourite count', 'ap'); ?></p>
							</fieldset>
							<fieldset>
								<a href="#" data-action="recount-flag" class="button-default"><?php _e('Recount flag', 'ap'); ?></a>							
								<p class="description"><?php _e('This will recheck flag count', 'ap'); ?></p>
							</fieldset>
							<fieldset>
								<a href="#" data-action="recount-flag" class="button-default"><?php _e('Recount close', 'ap'); ?></a>							
								<p class="description"><?php _e('This will recheck close count', 'ap'); ?></p>
							</fieldset>
						</td>
					</tr>

				</table>
			</div>
		</div>
		<p class="submit"><input type="submit" class="button-primary" value="Save Options" /></p>

	</form>
</div>