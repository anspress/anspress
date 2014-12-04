<?php
$options = get_option('anspress_opt');
$settings = $options  + ap_default_options();

if ( ! isset( $_REQUEST['settings-updated'] ) )
	$_REQUEST['settings-updated'] = false; // This checks whether the form has just been submitted. ?>

<div class="wrap">
	<?php screen_icon(); echo '<h2>' . __( 'AnsPress Options' ) . '</h2>';
	// This shows the page's name and an icon if one has been provided ?>
			
	<?php if ( false !== $_REQUEST['settings-updated'] ) : ?>
	<div class="updated fade"><p><strong><?php _e( 'Options saved' ); ?></strong></p></div>
	<?php endif; // If the form has just been submitted, this shows the notification ?>
	
	<div class="get-support">
		<strong>Need more help ? feel free to ask for support. </strong>
		<a href="http://open-wp.com">Support Forum</a>
	</div>
	<div class="doante-to-anspress">
		<h3>Help us keep AnsPress open source, free and full functional without any limitations</h3>
		<a href="https://www.paypal.com/cgi-bin/webscr?business=rah12@live.com&cmd=_xclick&item_name=Donation%20to%20AnsPress%20development" target="_blank"><img src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" alt="" /></a>
	</div>
	
	<form method="post" action="" id="ap-options">
		<ul id="ap_opt_nav" class="nav nav-tabs">
		  <li class="active"><a href="#ap-general" data-toggle="tab"><?php _e('General', 'ap'); ?></a></li>
		  <li><a href="#ap-question" data-toggle="tab"><?php _e('Question', 'ap'); ?></a></li>
		  <li><a href="#ap-answers" data-toggle="tab"><?php _e('Answers', 'ap'); ?></a></li>
		  <li><a href="#ap-theme" data-toggle="tab"><?php _e('Theme', 'ap'); ?></a></li>
		  <li><a href="#ap-categories" data-toggle="tab"><?php _e('Categories', 'ap'); ?></a></li>
		  <li><a href="#ap-tags" data-toggle="tab"><?php _e('Tags', 'ap'); ?></a></li>
		  <li><a href="#ap-user" data-toggle="tab"><?php _e('User', 'ap'); ?></a></li>
		  <li><a href="#ap-permission" data-toggle="tab"><?php _e('Permission', 'ap'); ?></a></li>
		  <li><a href="#ap-pages" data-toggle="tab"><?php _e('Pages', 'ap'); ?></a></li>
		  <li><a href="#ap-permalink" data-toggle="tab"><?php _e('Permalink', 'ap'); ?></a></li>
		  <li><a href="#ap-labels" data-toggle="tab"><?php _e('Labels', 'ap'); ?></a></li>
		  <li><a href="#ap-misc" data-toggle="tab"><?php _e('Spam', 'ap'); ?></a></li>
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
							<input type="checkbox" id="show_login_signup" name="anspress_opt[show_login_signup]" value="1" <?php checked( true, $settings['show_login_signup'] ); ?> />
							<label for="show_login_signup">Show login and signup</label>
						</fieldset>
						<fieldset>
							<input type="checkbox" id="show_signup" name="anspress_opt[show_signup]" value="1" <?php checked( true, $settings['show_signup'] ); ?> />
							<label for="show_signup">Show signup form</label>
						</fieldset>
						<fieldset>
							<input type="checkbox" id="show_login" name="anspress_opt[show_login]" value="1" <?php checked( true, $settings['show_login'] ); ?> />
							<label for="show_login">Show login form</label>
						</fieldset>
						<fieldset>
							<input type="checkbox" id="show_social_login" name="anspress_opt[show_social_login]" value="1" <?php checked( true, $settings['show_social_login'] ); ?> />
							<label for="show_social_login">Show social login form</label>
						</fieldset>
						<fieldset>
							<p>Type down your own custom signup url or leave it blank for the default AnsPress modal signup</p>
							<input type="url" name="anspress_opt[custom_signup_url]" placeholder="http://yorsite.com/signup" id="custom_signup_url" value="<?php echo $settings['custom_signup_url'] ; ?>" />
						</fieldset>
						<fieldset>
							<p>Type down your own custom login url or leave it blank for the default AnsPress modal login</p>
							<input type="url" name="anspress_opt[custom_login_url]" placeholder="http://yorsite.com/login" id="custom_login_url" value="<?php echo $settings['custom_login_url'] ; ?>" />
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
				<tr valign="top">
					<th scope="row">Double titles</th>
					<td>
						<input type="checkbox" id="double_titles" name="anspress_opt[double_titles]" value="1" <?php checked( true, $settings['double_titles'] ); ?> />
						<label for="double_titles">If you see double titles on your pages enable this checkbox </label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Disable private question</th>
					<td>
						<input type="checkbox" id="can_private_question" name="anspress_opt[can_private_question]" value="1" <?php checked( true, $settings['can_private_question'] ); ?> />
						<label for="can_private_question">Disable the ability to ask a question as private</label>
					</td>
				</tr>
			</table>
			</div>
			<div class="tab-pane" id="ap-question">		
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="minimum_qtitle_length"><?php _e('Minimum words in title', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[minimum_qtitle_length]" id="minimum_qtitle_length" value="<?php echo $settings['minimum_qtitle_length'] ; ?>" />
							<p class="description"><?php _e('Minimum words for question title.', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="minimum_question_length"><?php _e('Minimum words in question', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[minimum_question_length]" id="minimum_question_length" value="<?php echo $settings['minimum_question_length'] ; ?>" />
							<p class="description"><?php _e('Set minimum question word limit.', 'ap'); ?></p>
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
					<tr valign="top">
						<th scope="row"><label for="minimum_ans_length"><?php _e('Minimum words in answer', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[minimum_ans_length]" id="minimum_ans_length" value="<?php echo $settings['minimum_ans_length'] ; ?>" />
							<p class="description"><?php _e('Set minimum answer word limit.', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="close_selected"><?php _e('Close after selecting answer', 'ap'); ?></label></th>
						<td>
							<input type="checkbox" id="close_selected" name="anspress_opt[close_selected]" value="1" <?php checked( true, $settings['close_selected'] ); ?> />
							<p class="description"><?php _e('Do not allow new answer after selecting answer.', 'ap'); ?></p>
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
						<th scope="row"><label for="avatar_size_qquestion"><?php _e('Avatar size in question page', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[avatar_size_qquestion]" id="avatar_size_qquestion" value="<?php echo $settings['avatar_size_qquestion'] ; ?>" />
							<p class="description"><?php _e('User avatar size for question.', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="avatar_size_qanswer"><?php _e('Avatar size in answer', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[avatar_size_qanswer]" id="avatar_size_qanswer" value="<?php echo $settings['avatar_size_qanswer'] ; ?>" />
							<p class="description"><?php _e('User avatar in question page answers.', 'ap'); ?></p>
						</td>
					</tr>
				</table>
			</div>			
			
			<div class="tab-pane" id="ap-tags">		
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="enable_tags"><?php _e('Enable tags', 'ap'); ?></label></th>
						<td>
							<input type="checkbox" id="enable_tags" name="anspress_opt[enable_tags]" value="1" <?php checked( true, $settings['enable_tags'] ); ?> />
							<p class="description"><?php _e('Enable or disable tags system', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="max_tags"><?php _e('Maximum tags', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" id="max_tags" name="anspress_opt[max_tags]" value="<?php echo $settings['max_tags']; ?>" />
							<p class="description"><?php _e('Maximum numbers of tags that user can add when asking.', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="min_tags"><?php _e('Minimum tags', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" id="min_tags" name="anspress_opt[min_tags]" value="<?php echo $settings['min_tags']; ?>" />
							<p class="description"><?php _e('Minimum numbers of tags user need to add when asking.', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="min_point_new_tag"><?php _e('Minimum points to create new tag', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" id="min_point_new_tag" name="anspress_opt[min_point_new_tag]" value="<?php echo $settings['min_point_new_tag']; ?>" />
							<p class="description"><?php _e('User must have more or equal to those points to create a new tag.', 'ap'); ?></p>
						</td>
					</tr>
				</table>
			</div>
			
			<div class="tab-pane" id="ap-categories">		
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="enable_categories"><?php _e('Enable categories', 'ap'); ?></label></th>
						<td>
							<input type="checkbox" id="enable_categories" name="anspress_opt[enable_categories]" value="1" <?php checked( true, $settings['enable_categories'] ); ?> />
							<p class="description"><?php _e('Enable or disable categories system', 'ap'); ?></p>
						</td>
					</tr>
					
				</table>
			</div>
			
			<div class="tab-pane" id="ap-user">
				<table class="form-table">
					<!--<tr valign="top">
						<th scope="row"><label for="default_avatar"><?php _e('Default avatar', 'ap'); ?></label></th>
						<td>
							<div class="uploader">
								<input id="default_avatar" name="anspress_opt[default_avatar]" type="text" value="<?php echo $settings['default_avatar'] ; ?>" />
								<button id="default_avatar_upload" class="button" name="default_avatar_upload">Upload</button>
							</div>					
							<p class="description"><?php _e('Default avatar.', 'ap'); ?></p>
						</td>
					</tr>-->
					<tr valign="top">
						<th scope="row"><label for="cover_width"><?php _e('Cover width', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[cover_width]" id="cover_width" value="<?php echo $settings['cover_width'] ; ?>" placeholder="800" />								
							<p class="description"><?php _e('Width of of the cover image.', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="cover_height"><?php _e('Cover height', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[cover_height]" id="cover_height" value="<?php echo $settings['cover_height'] ; ?>" placeholder="200" />								
							<p class="description"><?php _e('Height of the cover image.', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="cover_width_small"><?php _e('Small cover width', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[cover_width_small]" id="cover_width_small" value="<?php echo $settings['cover_width_small'] ; ?>" placeholder="800" />								
							<p class="description"><?php _e('Width of of the small cover image.', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="cover_height_small"><?php _e('Small cover height', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[cover_height_small]" id="cover_height_small" value="<?php echo $settings['cover_height_small'] ; ?>" placeholder="200" />								
							<p class="description"><?php _e('Height of the small cover image.', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="default_rank"><?php _e('Default rank', 'ap'); ?></label></th>
						<td>
							<?php
								$terms = get_terms( 'rank', array( 'hide_empty' => false, 'orderby' => 'id' ) );
								if ( !empty( $terms ) ) {
									echo '<select name="anspress_opt[default_rank]">';
									foreach ( $terms as $term ) { ?>
										<option value="<?php echo esc_attr( $term->term_id ); ?>" <?php selected(  $settings['default_rank'], $term->term_id ); ?>><?php echo esc_attr( $term->name ); ?></option>
									<?php }
									echo '</select>';
								}

								/* If there are no rank terms, display a message. */
								else {
									_e( 'There are no ranks available.', 'ap' );
								}
							?>
							<p class="description"><?php _e('Assign a default rank for newly registered user', 'ap'); ?></p>
						</td>
					</tr>
				</table>
			</div>
			
			<div class="tab-pane" id="ap-permission">
				<h3 class="ap-option-section"><?php _e('Permission', 'ap'); ?></h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e('Post questions', 'ap') ?></th>
						<td>
							<fieldset>
								<input type="checkbox" id="allow_anonymous" name="anspress_opt[allow_anonymous]" value="1" <?php checked( true, $settings['allow_anonymous'] ); ?> />
								<label for="allow_anonymous"><?php _e('Allow anonymous', 'ap') ?></label>
							</fieldset>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Post answers', 'ap') ?></th>
						<td>
							<fieldset>
								<input type="checkbox" id="only_admin_can_answer" name="anspress_opt[only_admin_can_answer]" value="1" <?php checked( true, $settings['only_admin_can_answer'] ); ?> />
								<label for="only_admin_can_answer"><?php _e('Only admin can answer', 'ap') ?></label>
							</fieldset>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Show answers', 'ap') ?></th>
						<td>
							<fieldset>
								<input type="checkbox" id="logged_in_can_see_ans" name="anspress_opt[logged_in_can_see_ans]" value="1" <?php checked( true, $settings['logged_in_can_see_ans'] ); ?> />
								<label for="logged_in_can_see_ans"><?php _e('Only logged in can see answers', 'ap') ?></label>
							</fieldset>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Show comments', 'ap') ?></th>
						<td>
							<fieldset>
								<input type="checkbox" id="logged_in_can_see_comment" name="anspress_opt[logged_in_can_see_comment]" value="1" <?php checked( true, $settings['logged_in_can_see_comment'] ); ?> />
								<label for="logged_in_can_see_comment"><?php _e('Only logged in can see comment', 'ap') ?></label>
							</fieldset>
						</td>
					</tr>
				</table>
			</div>
				
			<div class="tab-pane" id="ap-pages">
				<h3 class="ap-option-section"><?php _e('Item per page', 'ap'); ?></h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="question_per_page"><?php _e('Question per page', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[question_per_page]" id="question_per_page" value="<?php echo $settings['question_per_page'] ; ?>" />								
							<p class="description"><?php _e('Question to show per page', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="answers_per_page"><?php _e('Answers per page', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[answers_per_page]" id="answers_per_page" value="<?php echo $settings['answers_per_page'] ; ?>" />								
							<p class="description"><?php _e('Answers to show per page in question page', 'ap'); ?></p>
						</td>
					</tr>					
					<tr valign="top">
						<th scope="row"><label for="tags_per_page"><?php _e('Tags per page', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[tags_per_page]" id="tags_per_page" value="<?php echo $settings['tags_per_page'] ; ?>" />								
							<p class="description"><?php _e('Tags to show per page', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="users_per_page"><?php _e('Users per page', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[users_per_page]" id="users_per_page" value="<?php echo $settings['users_per_page'] ; ?>" />								
							<p class="description"><?php _e('Users to show per page on users page', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="followers_limit"><?php _e('Followers per page', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[followers_limit]" id="followers_limit" value="<?php echo $settings['followers_limit'] ; ?>" placeholder="10" />								
							<p class="description"><?php _e('How many followers to display on user profile?', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="following_limit"><?php _e('Following users per page', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[following_limit]" id="following_limit" value="<?php echo $settings['following_limit'] ; ?>" placeholder="10" />								
							<p class="description"><?php _e('How many following users to display on user profile?', 'ap'); ?></p>
						</td>
					</tr>
				</table>
				<h3 class="ap-option-section"><?php _e('Sorting & Ordering', 'ap'); ?></h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="answers_sort"><?php _e('Default sorting of answers', 'ap'); ?></label></th>
						<td>
							<select name="anspress_opt[answers_sort]" id="answers_sort">
								<option value="voted"<?php echo $settings['answers_sort']=='voted' ? ' selected="selected"' : '' ?>>Voted</option>
								<option value="oldest"<?php echo $settings['answers_sort']=='oldest' ? ' selected="selected"' : '' ?>>Oldest</option>
								<option value="newest"<?php echo $settings['answers_sort']=='newest' ? ' selected="selected"' : '' ?>>Newest</option>
							</select>
							<p class="description"><?php _e('Default active tab for answers list', 'ap'); ?></p>
						</td>
					</tr>
				</table>
				<h3 class="ap-option-section"><?php _e('Page titles', 'ap'); ?></h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="base_page_title"><?php _e('Base page title', 'ap'); ?></label></th>
						<td>
							<input type="text" name="anspress_opt[base_page_title]" id="base_page_title" value="<?php echo $settings['base_page_title'] ; ?>" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="ask_page_title"><?php _e('Ask page title', 'ap'); ?></label></th>
						<td>
							<input type="text" name="anspress_opt[ask_page_title]" id="ask_page_title" value="<?php echo $settings['ask_page_title'] ; ?>" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="categories_page_title"><?php _e('Categories page title', 'ap'); ?></label></th>
						<td>
							<input type="text" name="anspress_opt[categories_page_title]" id="categories_page_title" value="<?php echo $settings['categories_page_title'] ; ?>" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="tags_page_title"><?php _e('Tags page title', 'ap'); ?></label></th>
						<td>
							<input type="text" name="anspress_opt[tags_page_title]" id="tags_page_title" value="<?php echo $settings['tags_page_title'] ; ?>" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="users_page_title"><?php _e('Users page title', 'ap'); ?></label></th>
						<td>
							<input type="text" name="anspress_opt[users_page_title]" id="users_page_title" value="<?php echo $settings['users_page_title'] ; ?>" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="search_page_title"><?php _e('Search page title', 'ap'); ?></label></th>
						<td>
							<input type="text" name="anspress_opt[search_page_title]" id="search_page_title" value="<?php echo $settings['search_page_title'] ; ?>" />
						</td>
					</tr>
				</table>
			</div>
			
			<div class="tab-pane" id="ap-permalink">		
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="question_prefix"><?php _e('Question prefix', 'ap'); ?></label></th>
						<td>
							<input type="text" id="question_prefix" name="anspress_opt[question_prefix]" value="<?php echo $settings['question_prefix'] ; ?>" <?php checked( true, $settings['question_prefix'] ); ?> />
							<p class="description"><?php _e('Question prefix', 'ap'); ?></p>
						</td>
					</tr>					
				</table>
			</div>
			
			<div class="tab-pane" id="ap-labels">
				<table class="form-table">					
					<tr valign="top">
						<th scope="row"><label for="default_label"><?php _e('Default label', 'ap'); ?></label></th>
						<td>
							<?php

								$terms = get_terms( 'question_label', array( 'hide_empty' => false, 'orderby' => 'id' ) );
								if ( !empty( $terms ) ) {
									echo '<select name="anspress_opt[default_label]">';
									foreach ( $terms as $term ) { ?>
										<option value="<?php echo esc_attr( $term->term_id ); ?>" <?php selected(  $settings['default_label'], $term->term_id ); ?>><?php echo esc_attr( $term->name ); ?></option>
									<?php }
									echo '</select>';
								}

								/* If there are no rank terms, display a message. */
								else {
									_e( 'There are no labels available.', 'ap' );
								}
							?>
							<p class="description"><?php _e('Assign a default label for new questions', 'ap'); ?></p>
						</td>
					</tr>
				</table>
			</div>			
			<div class="tab-pane" id="ap-misc">	
				<h3 class="title"><?php _e('Spam', 'ap'); ?></h3>
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
				<h3 class="ap-option-section"><?php _e('Moderation', 'ap'); ?></h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="moderate_new_question"><?php _e('New question', 'ap'); ?></label></th>
						<td>
							<select name="anspress_opt[moderate_new_question]" id="moderate_new_question">
								<option value="no_mod" <?php selected($settings['moderate_new_question'], 'no_mod') ; ?>><?php _e('No moderation', 'ap'); ?></option>
								<option value="pending" <?php selected($settings['moderate_new_question'], 'pending') ; ?>><?php _e('Hold for review', 'ap'); ?></option>
								<option value="point" <?php selected($settings['moderate_new_question'], 'point') ; ?>><?php _e('Point required', 'ap'); ?></option>
							</select>
							<p class="description"><?php _e('Hold new question for moderation. If you select "Point required" then you can must enter point below.', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="mod_question_point"><?php _e('Point required for question', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" class="regular-text" name="anspress_opt[mod_question_point]" value="<?php echo $settings['mod_question_point']; ?>" />
							<p class="description"><?php _e('Point required for directly publish new question.', 'ap'); ?></p>
						</td>
					</tr>
				</table>
				<h3 class="ap-option-section"><?php _e('reCaptcha', 'ap'); ?></h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="recaptcha_public_key"><?php _e('Public Key', 'ap'); ?></label></th>
						<td>
							<input type="text" name="anspress_opt[recaptcha_public_key]" id="recaptcha_public_key" value="<?php echo $settings['recaptcha_public_key'] ; ?>" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="recaptcha_private_key"><?php _e('Private Key', 'ap'); ?></label></th>
						<td>
							<input type="text" name="anspress_opt[recaptcha_private_key]" id="recaptcha_private_key" value="<?php echo $settings['recaptcha_private_key'] ; ?>" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="captcha_ask"><?php _e('Enable in ask form', 'ap'); ?></label></th>
						<td>
							<input type="checkbox" name="anspress_opt[captcha_ask]" id="captcha_ask" value="1" <?php checked(true, $settings['captcha_ask']); ?> />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="captcha_answer"><?php _e('Enable in answer form', 'ap'); ?></label></th>
						<td>
							<input type="checkbox" name="anspress_opt[captcha_answer]" id="captcha_answer" value="1" <?php checked(true, $settings['captcha_answer']); ?> />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="enable_captcha_skip"><?php _e('Enable reCaptcha skip based on user points', 'ap'); ?></label></th>
						<td>
							<input type="checkbox" name="anspress_opt[enable_captcha_skip]" id="enable_captcha_skip" value="1" <?php checked(true, $settings['enable_captcha_skip']); ?> />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="captcha_skip_rpoints"><?php _e('Minimum points to skip reCaptcha', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[captcha_skip_rpoints]" id="captcha_skip_rpoints" value="<?php echo $settings['captcha_skip_rpoints'] ; ?>" />
						</td>
					</tr>
				</table>
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
								<a href="#" data-action="recount-fav" class="button-default"><?php _e('Recount favorites', 'ap'); ?></a>							
								<p class="description"><?php _e('This will recheck favorite count', 'ap'); ?></p>
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
			<p class="submit"><input type="submit" class="button-primary" value="Save Options" /></p>
		</div>		
		<input type="hidden" value="ap_save_options" name="action"/>
	</form>
</div>