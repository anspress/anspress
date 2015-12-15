<?php
class AnsPress_Options_Page
{
	public function __construct() {

		add_action( 'init', array( $this, 'add_option_groups' ), 11 );
	}

	public function add_option_groups() {

		$settings = ap_opt();

		// Register general settings
		ap_register_option_group('general', __( 'General', 'anspress-question-answer' ) , array(
			array(
				'name' 			=> 'anspress_opt[base_page]',
				'label' 		=> __( 'Base page', 'anspress-question-answer' ),
				'desc' 			=> __( 'Select page for displaying anspress.', 'anspress-question-answer' ),
				'type' 			=> 'page_select',
				'value' 		=> @$settings['base_page'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[question_help_page]',
				'label' => __( 'Question Help page', 'anspress-question-answer' ),
				'desc' => __( 'Direction for asking a question.', 'anspress-question-answer' ),
				'type' => 'page_select',
				'value' => @$settings['question_help_page'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[answer_help_page]',
				'label' => __( 'Answer Help page', 'anspress-question-answer' ),
				'desc' => __( 'Direction for answring a question.', 'anspress-question-answer' ),
				'type' => 'page_select',
				'value' => @$settings['answer_help_page'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[author_credits]',
				'label' => __( 'Hide author credits', 'anspress-question-answer' ),
				'desc' => __( 'Hide link to AnsPress project site.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => @$settings['author_credits'],
				'order' => '1',
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[allow_private_posts]',
				'label' => __( 'Allow private posts', 'anspress-question-answer' ),
				'desc' => __( 'Allow users to create private question and answer.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => @$settings['allow_private_posts'],
				'show_desc_tip' => false,
			),

			array(
				'name' => 'anspress_opt[db_cleanup]',
				'label' => __( 'Clean DB', 'anspress-question-answer' ),
				'desc' => __( 'check this to remove all anspress data including posts on deactivating plugin.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => @$settings['db_cleanup'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[question_permalink_follow]',
				'label' => __( 'Base page slug before question permalink', 'anspress-question-answer' ),
				'desc' => __( 'i.e. '.home_url( '/BASE_PAGE/question/QUESTION_TITLE' ), 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => @$settings['question_permalink_follow'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[base_before_user_perma]',
				'label' => __( 'Base page slug before user page permalink', 'anspress-question-answer' ),
				'desc' => __( 'i.e. '.home_url( '/BASE_PAGE/user/USER_NAME' ), 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => @$settings['base_before_user_perma'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[disable_hover_card]',
				'label' => __( 'Disable hover card', 'anspress-question-answer' ),
				'desc' => __( 'Dont show user hover card on mouseover.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => @$settings['disable_hover_card'],
				'show_desc_tip' => false,
			),

		));

		// Register layout settings
		ap_register_option_group('layout', __( 'Layout', 'anspress-question-answer' ) , array(
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'Avatar', 'anspress-question-answer' ) . '</span>',
			) ,
			array(
				'name' => 'anspress_opt[avatar_size_list]',
				'label' => __( 'List avatar size', 'anspress-question-answer' ),
				'desc' => __( 'User avatar size for questions list.', 'anspress-question-answer' ),
				'type' => 'number',
				'value' => $settings['avatar_size_list'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[avatar_size_qquestion]',
				'label' => __( 'Question avatar size', 'anspress-question-answer' ),
				'desc' => __( 'User avatar size for question.', 'anspress-question-answer' ),
				'type' => 'number',
				'value' => $settings['avatar_size_qquestion'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[avatar_size_qanswer]',
				'label' => __( 'Answer avatar size', 'anspress-question-answer' ),
				'desc' => __( 'User avatar size for answer.', 'anspress-question-answer' ),
				'type' => 'number',
				'value' => $settings['avatar_size_qanswer'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[avatar_size_qanswer]',
				'label' => __( 'Answer avatar size', 'anspress-question-answer' ),
				'desc' => __( 'User avatar size for answer.', 'anspress-question-answer' ),
				'type' => 'number',
				'value' => $settings['avatar_size_qanswer'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[avatar_size_qcomment]',
				'label' => __( 'Comment avatar size', 'anspress-question-answer' ),
				'desc' => __( 'User avatar size for comments.', 'anspress-question-answer' ),
				'type' => 'number',
				'value' => $settings['avatar_size_qcomment'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'Items to show per page', 'anspress-question-answer' ) . '</span>',
			) ,
			array(
				'name' => 'anspress_opt[question_per_page]',
				'label' => __( 'Questions per page', 'anspress-question-answer' ),
				'desc' => __( 'Questions to show per page.', 'anspress-question-answer' ),
				'type' => 'number',
				'value' => $settings['question_per_page'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[answers_per_page]',
				'label' => __( 'Answers per page', 'anspress-question-answer' ),
				'desc' => __( 'Answers to show per page.', 'anspress-question-answer' ),
				'type' => 'number',
				'value' => $settings['answers_per_page'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[users_per_page]',
				'label' => __( 'Users per page', 'anspress-question-answer' ),
				'desc' => __( 'Users to show per page.', 'anspress-question-answer' ),
				'type' => 'number',
				'value' => $settings['users_per_page'],
			) ,
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'Sorting', 'anspress-question-answer' ) . '</span>',
			) ,
			array(
				'name' => 'anspress_opt[answers_sort]',
				'label' => __( 'Default answer sort', 'anspress-question-answer' ),
				'desc' => __( 'Sort answers by default.', 'anspress-question-answer' ),
				'type' => 'select',
				'options' => array(
					'voted' => __( 'Voted', 'anspress-question-answer' ),
					'active' => __( 'Active', 'anspress-question-answer' ),
					'newest' => __( 'Newest', 'anspress-question-answer' ),
					'oldest' => __( 'Oldest', 'anspress-question-answer' ),
				),
				'value' => $settings['answers_sort'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'Toggle', 'anspress-question-answer' ) . '</span>',
			) ,
			array(
				'name' => 'anspress_opt[show_comments_by_default]',
				'label' => __( 'Show comments', 'anspress-question-answer' ),
				'desc' => __( 'Show comments by default.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => $settings['show_comments_by_default'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[show_question_sidebar]',
				'label' => __( 'Show question sidebar', 'anspress-question-answer' ),
				'desc' => __( 'Subscribe and Stats widgets will be shown on question page.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => $settings['show_question_sidebar'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[notification_sidebar]',
				'label' => __( 'Show notification sidebar', 'anspress-question-answer' ),
				'desc' => __( 'Show dropdown notification as sidebar', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => $settings['notification_sidebar'],
				'show_desc_tip' => false,
			),
		));

		// Register pages settings
		ap_register_option_group('pages', __( 'Pages', 'anspress-question-answer' ) , array(
			array(
				'name' => '__sep',
				'type' => 'custom',
				'order' => 5,
				'html' => '<span class="ap-form-separator">' . __( 'Page slug', 'anspress-question-answer' ) . '</span>',
			),
			array(
				'name' => 'anspress_opt[ask_page_slug]',
				'label' => __( 'Ask page slug', 'anspress-question-answer' ),
				'desc' => __( 'Enter slug for ask page.', 'anspress-question-answer' ),
				'type' => 'text',
				'value' => @$settings['ask_page_slug'],
				'show_desc_tip' => false,
				'order' => 5,
			),
			array(
				'name' => 'anspress_opt[question_page_slug]',
				'label' => __( 'Question page slug', 'anspress-question-answer' ),
				'desc' => __( 'Enter slug for question page.', 'anspress-question-answer' ),
				'type' => 'text',
				'value' => @$settings['question_page_slug'],
				'show_desc_tip' => false,
				'order' => 5,
			),
			array(
				'name' => 'anspress_opt[users_page_slug]',
				'label' => __( 'Users page slug', 'anspress-question-answer' ),
				'desc' => __( 'Enter slug for users page.', 'anspress-question-answer' ),
				'type' => 'text',
				'value' => @$settings['users_page_slug'],
				'show_desc_tip' => false,
				'order' => 5,
			),
			array(
				'name' => 'anspress_opt[user_page_slug]',
				'label' => __( 'User page slug', 'anspress-question-answer' ),
				'desc' => __( 'Enter slug for user page, make sure no page or post exists with same slug.', 'anspress-question-answer' ),
				'type' => 'text',
				'value' => @$settings['user_page_slug'],
				'show_desc_tip' => false,
				'order' => 5,
			),
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'Page titles', 'anspress-question-answer' ) . '</span>',
			) ,
			array(
				'name' => 'anspress_opt[show_title_in_question]',
				'label' => __( 'Show question title', 'anspress-question-answer' ),
				'desc' => __( 'Show question title in single question page.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => $settings['show_title_in_question'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[show_solved_prefix]',
				'label' => __( 'Show solved prefix', 'anspress-question-answer' ),
				'desc' => __( 'If an answer is selected for question then [solved] prefix will be added in title.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => $settings['show_solved_prefix'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[base_page_title]',
				'label' => __( 'Base page title', 'anspress-question-answer' ),
				'desc' => __( 'Main questions list page title', 'anspress-question-answer' ),
				'type' => 'text',
				'value' => $settings['base_page_title'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[ask_page_title]',
				'label' => __( 'Ask page title', 'anspress-question-answer' ),
				'desc' => __( 'Title of the ask page', 'anspress-question-answer' ),
				'type' => 'text',
				'value' => $settings['ask_page_title'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[users_page_title]',
				'label' => __( 'Users page title', 'anspress-question-answer' ),
				'desc' => __( 'Title of the users page', 'anspress-question-answer' ),
				'type' => 'text',
				'value' => $settings['users_page_title'],
				'show_desc_tip' => false,
			),
		));

		// Register question settings
		ap_register_option_group('question', __( 'Question', 'anspress-question-answer' ) , array(
			array(
				'name' => 'anspress_opt[minimum_qtitle_length]',
				'label' => __( 'Minimum title length', 'anspress-question-answer' ),
				'desc' => __( 'Set minimum letters for a question title.', 'anspress-question-answer' ),
				'type' => 'number',
				'value' => $settings['minimum_qtitle_length'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[minimum_question_length]',
				'label' => __( 'Minimum question content', 'anspress-question-answer' ),
				'desc' => __( 'Set minimum letters for a question contents.', 'anspress-question-answer' ),
				'type' => 'number',
				'value' => $settings['minimum_question_length'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[question_text_editor]',
				'label' => __( 'Use text editor ?', 'anspress-question-answer' ),
				'desc' => __( 'Text editor as default.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => $settings['question_text_editor'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[disable_comments_on_question]',
				'label' => __( 'Disable comments', 'anspress-question-answer' ),
				'desc' => __( 'Disable comments on questions.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => $settings['disable_comments_on_question'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[disable_voting_on_question]',
				'label' => __( 'Disable voting', 'anspress-question-answer' ),
				'desc' => __( 'Disable voting on questions.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => $settings['disable_voting_on_question'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[disable_down_vote_on_question]',
				'label' => __( 'Disable down voting', 'anspress-question-answer' ),
				'desc' => __( 'Disable down voting on questions.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => $settings['disable_down_vote_on_question'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[close_selected]',
				'label' => __( 'Close question after selecting answer', 'anspress-question-answer' ),
				'desc' => __( 'If enabled this will prevent user to submit answer on solved question.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => $settings['close_selected'],
				'show_desc_tip' => false,
			) ,
		));

		// Register answer settings
		ap_register_option_group('answer', __( 'Answer', 'anspress-question-answer' ) , array(
			array(
				'name' => 'anspress_opt[multiple_answers]',
				'label' => __( 'Multiple Answers', 'anspress-question-answer' ),
				'desc' => __( 'Allow an user to submit multiple answers on a single question.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => $settings['multiple_answers'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[minimum_ans_length]',
				'label' => __( 'Minimum question content', 'anspress-question-answer' ),
				'desc' => __( 'Set minimum letters for a answer contents.', 'anspress-question-answer' ),
				'type' => 'number',
				'value' => $settings['minimum_ans_length'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[answer_text_editor]',
				'label' => __( 'Use text editor ?', 'anspress-question-answer' ),
				'desc' => __( 'Text editor as default.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => $settings['answer_text_editor'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[disable_comments_on_answer]',
				'label' => __( 'Disable comments', 'anspress-question-answer' ),
				'desc' => __( 'Disable comments on answer.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => $settings['disable_comments_on_answer'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[disable_voting_on_answer]',
				'label' => __( 'Disable voting', 'anspress-question-answer' ),
				'desc' => __( 'Disable voting on answers.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => $settings['disable_voting_on_answer'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[disable_down_vote_on_answer]',
				'label' => __( 'Disable down voting', 'anspress-question-answer' ),
				'desc' => __( 'Disable down voting on answers.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => $settings['disable_down_vote_on_answer'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[disable_answer_nav]',
				'label' => __( 'Disable navigation', 'anspress-question-answer' ),
				'desc' => __( 'Disable answer navigation.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => $settings['disable_answer_nav'],
				'show_desc_tip' => false,
			) ,
		));

		// register user settings
		ap_register_option_group('users', __( 'Users', 'anspress-question-answer' ) , array(
			array(
				'name' => 'anspress_opt[user_profile]',
				'label' => __( 'User profile', 'anspress-question-answer' ),
				'desc' => __( 'Select which user profile you\'d like to use.', 'anspress-question-answer' ),
				'type' => 'select',
				'value' => $settings['user_profile'],
				'show_desc_tip' => false,
				'options' => array( 'anspress' => 'AnsPress', 'buddypress' => 'BuddyPress', 'userpro' => 'User Pro' ),
			) ,
			array(
				'name' => 'anspress_opt[enable_users_directory]',
				'label' => __( 'Show users directory', 'anspress-question-answer' ),
				'desc' => __( 'When enabled public can see directory of users.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => $settings['enable_users_directory'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'Features', 'anspress-question-answer' ) . '</span>',
			) ,
			array(
				'name' => 'anspress_opt[disable_reputation]',
				'label' => __( 'Disable reputation', 'anspress-question-answer' ),
				'desc' => __( 'Disable reputation for user', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => $settings['disable_reputation'],
				'show_desc_tip' => false,
			) ,

			array(
				'name' => 'anspress_opt[users_page_avatar_size]',
				'label' => __( 'Users page avatar size', 'anspress-question-answer' ),
				'desc' => __( 'Set user avatar size for users page item.', 'anspress-question-answer' ),
				'type' => 'number',
				'value' => $settings['users_page_avatar_size'],
				'show_desc_tip' => false,
			) ,

			array(
				'name' => 'anspress_opt[cover_width]',
				'label' => __( 'Cover width', 'anspress-question-answer' ),
				'desc' => __( 'Set width of user cover photo.', 'anspress-question-answer' ),
				'type' => 'number',
				'value' => @$settings['cover_width'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[cover_height]',
				'label' => __( 'Cover height', 'anspress-question-answer' ),
				'desc' => __( 'Set height of user cover photo.', 'anspress-question-answer' ),
				'type' => 'number',
				'value' => @$settings['cover_height'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[cover_width_small]',
				'label' => __( 'Cover thumb width', 'anspress-question-answer' ),
				'desc' => __( 'Set width of user cover photo thumbnail.', 'anspress-question-answer' ),
				'type' => 'number',
				'value' => @$settings['cover_width_small'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[cover_height_small]',
				'label' => __( 'Small cover height', 'anspress-question-answer' ),
				'desc' => __( 'Set height of user cover photo thumbnail.', 'anspress-question-answer' ),
				'type' => 'number',
				'value' => @$settings['cover_height_small'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[default_rank]',
				'label' => __( 'Default rank', 'anspress-question-answer' ),
				'desc' => __( 'Assign a default rank for newly registered user.', 'anspress-question-answer' ),
				'type' => 'select',
				'options' => get_terms('rank', array(
					'hide_empty' => false,
					'orderby' => 'id',
				)),
				'value' => @$settings['default_rank'],
				'show_desc_tip' => false,
			) ,
		));

		// register permission settings
		ap_register_option_group('permission', __( 'Permission', 'anspress-question-answer' ) , array(
			array(
				'name' => 'anspress_opt[multiple_answers]',
				'label' => __( 'Multiple answers', 'anspress-question-answer' ),
				'desc' => __( 'Allow user to submit multiple answer per question.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => $settings['multiple_answers'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[disallow_op_to_answer]',
				'label' => __( 'Asker can answer', 'anspress-question-answer' ),
				'desc' => __( 'Allow asker to answer his own question.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => $settings['disallow_op_to_answer'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[allow_anonymous]',
				'label' => __( 'Allow anonymous', 'anspress-question-answer' ),
				'desc' => __( 'Non-loggedin user can post questions and answers.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => $settings['allow_anonymous'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[only_admin_can_answer]',
				'label' => __( 'Only admin can answer', 'anspress-question-answer' ),
				'desc' => __( 'Only allow admin to answer question.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => $settings['only_admin_can_answer'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[logged_in_can_see_ans]',
				'label' => __( 'Only logged in can see answers', 'anspress-question-answer' ),
				'desc' => __( 'non-loggedin user cannot see answers.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => $settings['logged_in_can_see_ans'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[logged_in_can_see_comment]',
				'label' => __( 'Only logged in can see comment', 'anspress-question-answer' ),
				'desc' => __( 'non-loggedin user cannot see comments.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => $settings['logged_in_can_see_comment'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[disable_delete_after]',
				'label' => __( 'Lock delete action', 'anspress-question-answer' ),
				'desc' => __( 'Lock comment or post after a period so that user cannot delete it. Set the time in epoch i.e. 86400 = 1 day.', 'anspress-question-answer' ),
				'type' => 'number',
				'value' => $settings['disable_delete_after'],
				'show_desc_tip' => false,
			),
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'Upload', 'anspress-question-answer' ) . '</span>',
			),
			array(
				'name' => 'anspress_opt[allow_upload_image]',
				'label' => __( 'Allow image upload', 'anspress-question-answer' ),
				'desc' => __( 'Allow logged-in users to upload image.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => $settings['allow_upload_image'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[image_per_post]',
				'label' => __( 'Max images per post', 'anspress-question-answer' ),
				'desc' => __( 'Set how many images user can upload for each post.', 'anspress-question-answer' ),
				'type' => 'number',
				'value' => $settings['image_per_post'],
				'show_desc_tip' => false,
			),
			array(
				'name' => 'anspress_opt[max_upload_size]',
				'label' => __( 'Max upload size', 'anspress-question-answer' ),
				'desc' => __( 'Set maximum upload size.', 'anspress-question-answer' ),
				'type' => 'text',
				'value' => $settings['max_upload_size'],
				'show_desc_tip' => false,
			),
		));

		// register moderate settings
		ap_register_option_group('moderate', __( 'Moderate', 'anspress-question-answer' ) , array(
			array(
				'name' => 'anspress_opt[new_question_status]',
				'label' => __( 'Status of new question', 'anspress-question-answer' ),
				'desc' => __( 'Set post status of new question.', 'anspress-question-answer' ),
				'type' => 'select',
				'value' => $settings['new_question_status'],
				'options' => array(
					'publish' => __( 'Publish', 'anspress-question-answer' ),
					'moderate' => __( 'Moderate', 'profile', 'anspress-question-answer' ),
				),
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[edit_question_status]',
				'label' => __( 'Status of edited question', 'anspress-question-answer' ),
				'desc' => __( 'Set post status of edited question.', 'anspress-question-answer' ),
				'type' => 'select',
				'value' => $settings['edit_question_status'],
				'options' => array(
					'publish' => __( 'Publish', 'anspress-question-answer' ),
					'moderate' => __( 'Moderate', 'profile', 'anspress-question-answer' ),
				),
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[new_answer_status]',
				'label' => __( 'Status of new answer', 'anspress-question-answer' ),
				'desc' => __( 'Set post status of new answer.', 'anspress-question-answer' ),
				'type' => 'select',
				'value' => $settings['new_answer_status'],
				'options' => array(
					'publish' => __( 'Publish', 'anspress-question-answer' ),
					'moderate' => __( 'Moderate', 'profile', 'anspress-question-answer' ),
				),
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[edit_answer_status]',
				'label' => __( 'Status of edited answer', 'anspress-question-answer' ),
				'desc' => __( 'Set post status of edited answer.', 'anspress-question-answer' ),
				'type' => 'select',
				'value' => $settings['edit_answer_status'],
				'options' => array(
					'publish' => __( 'Publish', 'anspress-question-answer' ),
					'moderate' => __( 'Moderate', 'profile', 'anspress-question-answer' ),
				),
				'show_desc_tip' => false,
			) ,
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'reCaptacha', 'anspress-question-answer' ) . '</span>',
			) ,
			array(
				'name' => 'anspress_opt[enable_recaptcha]',
				'label' => __( 'Enable reCaptcha', 'anspress-question-answer' ),
				'desc' => __( 'Use this for preventing spam posts.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'value' => $settings['enable_recaptcha'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[recaptcha_site_key]',
				'label' => __( 'Recaptcha site key', 'anspress-question-answer' ),
				'desc' => __( 'Enter your site key, if you dont have it get it from here https://www.google.com/recaptcha/admin', 'anspress-question-answer' ),
				'type' => 'text',
				'value' => $settings['recaptcha_site_key'],
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anspress_opt[recaptcha_secret_key]',
				'label' => __( 'Recaptcha secret key', 'anspress-question-answer' ),
				'desc' => __( 'Enter your secret key', 'anspress-question-answer' ),
				'type' => 'text',
				'value' => $settings['recaptcha_secret_key'],
				'show_desc_tip' => false,
			) ,
		));

		ap_register_option_group( 'tools', __( 'Tools', 'anspress-question-answer' ) , array( $this, 'tools_page' ), false );
	}

	public function tools_page() {
		include 'views/tools.php';
	}
}
