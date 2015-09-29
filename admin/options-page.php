<?php
class AnsPress_Options_Page
{
	public function __construct()
	{
		add_action('init', array($this, 'add_option_groups'), 11 );
	}

	public function add_option_groups()
	{

		$settings = ap_opt();

		// Register general settings
		ap_register_option_group('general', __('General', 'ap') , array(
			array(
				'name' => 'anspress_opt[base_page]',
				'label' => __('Base page', 'ap') ,
				'desc' => __('Select page for displaying anspress.', 'ap') ,
				'type' => 'page_select',
				'value' => @$settings['base_page'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[question_help_page]',
				'label' => __('Question Help page', 'ap') ,
				'desc' => __('Direction for asking a question.', 'ap') ,
				'type' => 'page_select',
				'value' => @$settings['question_help_page'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[answer_help_page]',
				'label' => __('Answer Help page', 'ap') ,
				'desc' => __('Direction for answring a question.', 'ap') ,
				'type' => 'page_select',
				'value' => @$settings['answer_help_page'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[author_credits]',
				'label' => __('Hide author credits', 'ap') ,
				'desc' => __('Show your love by showing link to AnsPress project site.', 'ap') ,
				'type' => 'checkbox',
				'value' => @$settings['author_credits'],
				'order' => '1',
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[allow_private_posts]',
				'label' => __('Allow private posts', 'ap') ,
				'desc' => __('Allow users to create private question and answer.', 'ap') ,
				'type' => 'checkbox',
				'value' => @$settings['allow_private_posts'],
				'show_desc_tip' => false,
			),

			array(
				'name' => 'anspress_opt[db_cleanup]',
				'label' => __('Clean DB', 'ap') ,
				'desc' => __('check this to remove all anspress data including posts on deactivating plugin.', 'ap') ,
				'type' => 'checkbox',
				'value' => @$settings['db_cleanup'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[question_permalink_follow]',
				'label' => __('Base page slug before question permalink', 'ap') ,
				'desc' => __('i.e. '.home_url('/BASE_PAGE/question/QUESTION_TITLE'), 'ap') ,
				'type' => 'checkbox',
				'value' => @$settings['question_permalink_follow'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[base_before_user_perma]',
				'label' => __('Base page slug before user page permalink', 'ap') ,
				'desc' => __('i.e. '.home_url('/BASE_PAGE/user/USER_NAME'), 'ap') ,
				'type' => 'checkbox',
				'value' => @$settings['base_before_user_perma'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[disable_hover_card]',
				'label' => __('Disable hover card', 'ap') ,
				'desc' => __('Dont show user hover card on mouseover.', 'ap') ,
				'type' => 'checkbox',
				'value' => @$settings['disable_hover_card'],
				'show_desc_tip' => false,
			),

		));

		//Register layout settings
		ap_register_option_group('layout', __('Layout', 'ap') , array(
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __('Avatar') . '</span>',
			) ,
			array(
				'name' => 'anspress_opt[avatar_size_list]',
				'label' => __('List avatar size', 'ap') ,
				'desc' => __('User avatar size for questions list.', 'ap') ,
				'type' => 'number',
				'value' => $settings['avatar_size_list'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[avatar_size_qquestion]',
				'label' => __('Question avatar size', 'ap') ,
				'desc' => __('User avatar size for question.', 'ap') ,
				'type' => 'number',
				'value' => $settings['avatar_size_qquestion'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[avatar_size_qanswer]',
				'label' => __('Answer avatar size', 'ap') ,
				'desc' => __('User avatar size for answer.', 'ap') ,
				'type' => 'number',
				'value' => $settings['avatar_size_qanswer'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[avatar_size_qanswer]',
				'label' => __('Answer avatar size', 'ap') ,
				'desc' => __('User avatar size for answer.', 'ap') ,
				'type' => 'number',
				'value' => $settings['avatar_size_qanswer'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[avatar_size_qcomment]',
				'label' => __('Comment avatar size', 'ap') ,
				'desc' => __('User avatar size for comments.', 'ap') ,
				'type' => 'number',
				'value' => $settings['avatar_size_qcomment'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __('Items to show per page') . '</span>',
			) ,
			array(
				'name' => 'anspress_opt[question_per_page]',
				'label' => __('Questions per page', 'ap') ,
				'desc' => __('Questions to show per page.', 'ap') ,
				'type' => 'number',
				'value' => $settings['question_per_page'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[answers_per_page]',
				'label' => __('Answers per page', 'ap') ,
				'desc' => __('Answers to show per page.', 'ap') ,
				'type' => 'number',
				'value' => $settings['answers_per_page'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[users_per_page]',
				'label' => __('Users per page', 'ap') ,
				'desc' => __('Users to show per page.', 'ap') ,
				'type' => 'number',
				'value' => $settings['users_per_page'],
			) ,
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __('Sorting') . '</span>',
			) ,
			array(
				'name' => 'anspress_opt[answers_sort]',
				'label' => __('Default answer sort', 'ap') ,
				'desc' => __('Sort answers by default.', 'ap') ,
				'type' => 'select',
				'options' => array(
					'voted' => __('Voted', 'ap') ,
					'active' => __('Active', 'ap') ,
					'newest' => __('Newest', 'ap') ,
					'oldest' => __('Oldest', 'ap') ,
				) ,
				'value' => $settings['answers_sort'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __('Toggle') . '</span>',
			) ,
			array(
				'name' => 'anspress_opt[show_comments_by_default]',
				'label' => __('Show comments', 'ap') ,
				'desc' => __('Show comments by default.', 'ap') ,
				'type' => 'checkbox',
				'value' => $settings['show_comments_by_default'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[show_question_sidebar]',
				'label' => __('Show question sidebar', 'ap') ,
				'desc' => __('Subbscribe and and stats widget will shown in question page.', 'ap') ,
				'type' => 'checkbox',
				'value' => $settings['show_question_sidebar'],
				'show_desc_tip' => false,
			),
		));

		//Register pages settings
		ap_register_option_group('pages', __('Pages', 'ap') , array(
			array(
				'name' => '__sep',
				'type' => 'custom',
				'order' => 5,
				'html' => '<span class="ap-form-separator">' . __('Page slug') . '</span>',
			),
			array(
				'name' => 'anspress_opt[ask_page_slug]',
				'label' => __('Ask page slug', 'ap') ,
				'desc' => __('Enter slug for ask page.', 'ap') ,
				'type' => 'text',
				'value' => @$settings['ask_page_slug'],
				'show_desc_tip' => false,
				'order' => 5,
			),
			array(
				'name' => 'anspress_opt[question_page_slug]',
				'label' => __('Question page slug', 'ap') ,
				'desc' => __('Enter slug for question page.', 'ap') ,
				'type' => 'text',
				'value' => @$settings['question_page_slug'],
				'show_desc_tip' => false,
				'order' => 5,
			),
			array(
				'name' => 'anspress_opt[users_page_slug]',
				'label' => __('Users page slug', 'ap') ,
				'desc' => __('Enter slug for users page.', 'ap') ,
				'type' => 'text',
				'value' => @$settings['users_page_slug'],
				'show_desc_tip' => false,
				'order' => 5,
			),
			array(
				'name' => 'anspress_opt[user_page_slug]',
				'label' => __('User page slug', 'ap') ,
				'desc' => __('Enter slug for user page, make sure no page or post exists with same slug.', 'ap') ,
				'type' => 'text',
				'value' => @$settings['user_page_slug'],
				'show_desc_tip' => false,
				'order' => 5,
			),
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __('Page titles') . '</span>',
			) ,
			array(
				'name' => 'anspress_opt[show_title_in_question]',
				'label' => __('Show question title', 'ap') ,
				'desc' => __('Show question title in single question page.', 'ap') ,
				'type' => 'checkbox',
				'value' => $settings['show_title_in_question'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[show_solved_prefix]',
				'label' => __('Show solved prefix', 'ap') ,
				'desc' => __('If an answer is selected for question then [solved] prefix will be added in title.', 'ap') ,
				'type' => 'checkbox',
				'value' => $settings['show_solved_prefix'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[base_page_title]',
				'label' => __('Base page title', 'ap') ,
				'desc' => __('Main questions list page title', 'ap') ,
				'type' => 'text',
				'value' => $settings['base_page_title'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[ask_page_title]',
				'label' => __('Ask page title', 'ap') ,
				'desc' => __('Title of the ask page', 'ap') ,
				'type' => 'text',
				'value' => $settings['ask_page_title'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[users_page_title]',
				'label' => __('Users page title', 'ap') ,
				'desc' => __('Title of the users page', 'ap') ,
				'type' => 'text',
				'value' => $settings['users_page_title'],
				'show_desc_tip' => false,
			)
		));

		// Register question settings
		ap_register_option_group('question', __('Question', 'ap') , array(
			array(
				'name' => 'anspress_opt[minimum_qtitle_length]',
				'label' => __('Minimum title length', 'ap') ,
				'desc' => __('Set minimum letters for a question title.', 'ap') ,
				'type' => 'number',
				'value' => $settings['minimum_qtitle_length'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[minimum_question_length]',
				'label' => __('Minimum question content', 'ap') ,
				'desc' => __('Set minimum letters for a question contents.', 'ap') ,
				'type' => 'number',
				'value' => $settings['minimum_question_length'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[question_text_editor]',
				'label' => __('Use text editor ?', 'ap') ,
				'desc' => __('Text editor as default.', 'ap') ,
				'type' => 'checkbox',
				'value' => $settings['question_text_editor'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[disable_comments_on_question]',
				'label' => __('Disable comments', 'ap') ,
				'desc' => __('Disable comments on questions.', 'ap') ,
				'type' => 'checkbox',
				'value' => $settings['disable_comments_on_question'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[disable_voting_on_question]',
				'label' => __('Disable voting', 'ap') ,
				'desc' => __('Disable voting on questions.', 'ap') ,
				'type' => 'checkbox',
				'value' => $settings['disable_voting_on_question'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[disable_down_vote_on_question]',
				'label' => __('Disable down voting', 'ap') ,
				'desc' => __('Disable down voting on questions.', 'ap') ,
				'type' => 'checkbox',
				'value' => $settings['disable_down_vote_on_question'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[close_selected]',
				'label' => __('Close question after selecting answer', 'ap') ,
				'desc' => __('If enabled this will prevent user to submit answer on solved question.', 'ap') ,
				'type' => 'checkbox',
				'value' => $settings['close_selected'],
				'show_desc_tip' => false,
			) ,
		));

		// Register answer settings
		ap_register_option_group('answer', __('Answer', 'ap') , array(
			array(
				'name' => 'anspress_opt[multiple_answers]',
				'label' => __('Multiple Answers', 'ap') ,
				'desc' => __('Allow an user to submit multiple answers on a single question.', 'ap') ,
				'type' => 'checkbox',
				'value' => $settings['multiple_answers'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[minimum_ans_length]',
				'label' => __('Minimum question content', 'ap') ,
				'desc' => __('Set minimum letters for a answer contents.', 'ap') ,
				'type' => 'number',
				'value' => $settings['minimum_ans_length'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[answer_text_editor]',
				'label' => __('Use text editor ?', 'ap') ,
				'desc' => __('Text editor as default.', 'ap') ,
				'type' => 'checkbox',
				'value' => $settings['answer_text_editor'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[disable_comments_on_answer]',
				'label' => __('Disable comments', 'ap') ,
				'desc' => __('Disable comments on answer.', 'ap') ,
				'type' => 'checkbox',
				'value' => $settings['disable_comments_on_answer'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[disable_voting_on_answer]',
				'label' => __('Disable voting', 'ap') ,
				'desc' => __('Disable voting on answers.', 'ap') ,
				'type' => 'checkbox',
				'value' => $settings['disable_voting_on_answer'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[disable_down_vote_on_answer]',
				'label' => __('Disable down voting', 'ap') ,
				'desc' => __('Disable down voting on answers.', 'ap') ,
				'type' => 'checkbox',
				'value' => $settings['disable_down_vote_on_answer'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[disable_answer_nav]',
				'label' => __('Disable navigation', 'ap') ,
				'desc' => __('Disable answer navigation.', 'ap') ,
				'type' => 'checkbox',
				'value' => $settings['disable_answer_nav'],
				'show_desc_tip' => false,
			) ,
		));

		// register user settings
		ap_register_option_group('users', __('Users', 'ap') , array(
			array(
				'name' => 'anspress_opt[enable_users_directory]',
				'label' => __('Show users directory', 'ap') ,
				'desc' => __('When enabled public can see directory of users.', 'ap') ,
				'type' => 'checkbox',
				'value' => $settings['enable_users_directory'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __('Features', 'ap') . '</span>',
			) ,
			array(
				'name' => 'anspress_opt[disable_reputation]',
				'label' => __('Disable reputation', 'ap') ,
				'desc' => __('Disable reputation for user', 'ap') ,
				'type' => 'checkbox',
				'value' => $settings['disable_reputation'],
				'show_desc_tip' => false,
			) ,

			array(
				'name' => 'anspress_opt[users_page_avatar_size]',
				'label' => __('Users page avatar size', 'ap') ,
				'desc' => __('Set user avatar size for users page item.', 'ap') ,
				'type' => 'number',
				'value' => $settings['users_page_avatar_size'],
				'show_desc_tip' => false,
			) ,

			array(
				'name' => 'anspress_opt[cover_width]',
				'label' => __('Cover width', 'ap') ,
				'desc' => __('Set width of user cover photo.', 'ap') ,
				'type' => 'number',
				'value' => @$settings['cover_width'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[cover_height]',
				'label' => __('Cover height', 'ap') ,
				'desc' => __('Set height of user cover photo.', 'ap') ,
				'type' => 'number',
				'value' => @$settings['cover_height'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[cover_width_small]',
				'label' => __('Cover thumb width', 'ap') ,
				'desc' => __('Set width of user cover photo thumbnail.', 'ap') ,
				'type' => 'number',
				'value' => @$settings['cover_width_small'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[cover_height_small]',
				'label' => __('Small cover height', 'ap') ,
				'desc' => __('Set height of user cover photo thumbnail.', 'ap') ,
				'type' => 'number',
				'value' => @$settings['cover_height_small'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[default_rank]',
				'label' => __('Default rank', 'ap') ,
				'desc' => __('Assign a default rank for newly registered user.', 'ap') ,
				'type' => 'select',
				'options' => get_terms('rank', array(
					'hide_empty' => false,
					'orderby' => 'id'
				)) ,
				'value' => @$settings['default_rank'],
				'show_desc_tip' => false,
			) ,
		));

		// register permission settings
		ap_register_option_group('permission', __('Permission', 'ap') , array(
			array(
				'name' => 'anspress_opt[multiple_answers]',
				'label' => __('Multiple answers', 'ap') ,
				'desc' => __('Allow user to submit multiple answer per question.', 'ap') ,
				'type' => 'checkbox',
				'value' => $settings['multiple_answers'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[disallow_op_to_answer]',
				'label' => __('Asker can answer', 'ap') ,
				'desc' => __('Allow asker to answer his own question.', 'ap') ,
				'type' => 'checkbox',
				'value' => $settings['disallow_op_to_answer'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[allow_anonymous]',
				'label' => __('Allow anonymous', 'ap') ,
				'desc' => __('Non-loggedin user can post questions and answers.', 'ap') ,
				'type' => 'checkbox',
				'value' => $settings['allow_anonymous'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[only_admin_can_answer]',
				'label' => __('Only admin can answer', 'ap') ,
				'desc' => __('Only allow admin to answer question.', 'ap') ,
				'type' => 'checkbox',
				'value' => $settings['only_admin_can_answer'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[logged_in_can_see_ans]',
				'label' => __('Only logged in can see answers', 'ap') ,
				'desc' => __('non-loggedin user cannot see answers.', 'ap') ,
				'type' => 'checkbox',
				'value' => $settings['logged_in_can_see_ans'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[logged_in_can_see_comment]',
				'label' => __('Only logged in can see comment', 'ap') ,
				'desc' => __('non-loggedin user cannot see comments.', 'ap') ,
				'type' => 'checkbox',
				'value' => $settings['logged_in_can_see_comment'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[disable_delete_after]',
				'label' => __('Lock delete action', 'ap') ,
				'desc' => __('Lock comment or post after a period so that user cannot delete it. Set the time in epoch i.e. 86400 = 1 day.', 'ap') ,
				'type' => 'number',
				'value' => $settings['disable_delete_after'],
				'show_desc_tip' => false,
			),
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __('Upload', 'ap') . '</span>',
			),
			array(
				'name' => 'anspress_opt[allow_upload_image]',
				'label' => __('Allow image upload', 'ap') ,
				'desc' => __('Allow logged-in users to upload image.', 'ap') ,
				'type' => 'checkbox',
				'value' => $settings['allow_upload_image'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[image_per_post]',
				'label' => __('Max images per post', 'ap') ,
				'desc' => __('Set how many images user can upload for each post.', 'ap') ,
				'type' => 'number',
				'value' => $settings['image_per_post'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[max_upload_size]',
				'label' => __('Max upload size', 'ap') ,
				'desc' => __('Set maximum upload size.', 'ap') ,
				'type' => 'text',
				'value' => $settings['max_upload_size'],
				'show_desc_tip' => false,
			),
		));

		// register moderate settings
		ap_register_option_group('moderate', __('Moderate', 'ap') , array(
			array(
				'name' => 'anspress_opt[new_question_status]',
				'label' => __('Status of new question', 'ap') ,
				'desc' => __('Set post status of new question.', 'ap') ,
				'type' => 'select',
				'value' => $settings['new_question_status'],
				'options' => array(
					'publish' => __('Publish') ,
					'moderate' => __('Moderate', 'profile')
				) ,
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[edit_question_status]',
				'label' => __('Status of edited question', 'ap') ,
				'desc' => __('Set post status of edited question.', 'ap') ,
				'type' => 'select',
				'value' => $settings['edit_question_status'],
				'options' => array(
					'publish' => __('Publish') ,
					'moderate' => __('Moderate', 'profile')
				) ,
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[new_answer_status]',
				'label' => __('Status of new answer', 'ap') ,
				'desc' => __('Set post status of new answer.', 'ap') ,
				'type' => 'select',
				'value' => $settings['new_answer_status'],
				'options' => array(
					'publish' => __('Publish') ,
					'moderate' => __('Moderate', 'profile')
				) ,
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[edit_answer_status]',
				'label' => __('Status of edited answer', 'ap') ,
				'desc' => __('Set post status of edited answer.', 'ap') ,
				'type' => 'select',
				'value' => $settings['edit_answer_status'],
				'options' => array(
					'publish' => __('Publish') ,
					'moderate' => __('Moderate', 'profile')
				) ,
				'show_desc_tip' => false,
			) ,
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __('reCaptacha') . '</span>',
			) ,
			array(
				'name' => 'anspress_opt[enable_recaptcha]',
				'label' => __('Enable reCaptcha', 'ap') ,
				'desc' => __('Use this for preventing spam posts.', 'ap') ,
				'type' => 'checkbox',
				'value' => $settings['enable_recaptcha'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[recaptcha_site_key]',
				'label' => __('Recaptcha site key', 'ap') ,
				'desc' => __('Enter your site key, if you dont have it get it from here https://www.google.com/recaptcha/admin', 'ap') ,
				'type' => 'text',
				'value' => $settings['recaptcha_site_key'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[recaptcha_secret_key]',
				'label' => __('Recaptcha secret key', 'ap') ,
				'desc' => __('Enter your secret key', 'ap') ,
				'type' => 'text',
				'value' => $settings['recaptcha_secret_key'],
				'show_desc_tip' => false,
			) ,
		));

		ap_register_option_group('tools', __('Tools', 'ap') , array($this, 'tools_page'), false);
	}

	public function tools_page(){
		include 'views/tools.php';
	}
}
