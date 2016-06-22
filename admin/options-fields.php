<?php
class AnsPress_Options_Fields
{
	public function __construct() {
		$this->add_option_groups();
	}

	public function add_option_groups() {

		$settings = ap_opt();

		// Register general settings
		ap_register_option_group('general', __( 'General', 'anspress-question-answer' ) , array(
			array(
				'name' 			=> 'base_page',
				'label' 		=> __( 'Base page', 'anspress-question-answer' ),
				'desc' 			=> __( 'Select page for displaying anspress.', 'anspress-question-answer' ),
				'type' 			=> 'page_select',
				'show_desc_tip' => false,
			),
			array(
				'name' => 'question_help_page',
				'label' => __( 'Question Help page', 'anspress-question-answer' ),
				'desc' => __( 'Direction for asking a question.', 'anspress-question-answer' ),
				'type' => 'page_select',
				'show_desc_tip' => false,
			),
			array(
				'name' => 'answer_help_page',
				'label' => __( 'Answer Help page', 'anspress-question-answer' ),
				'desc' => __( 'Direction for answring a question.', 'anspress-question-answer' ),
				'type' => 'page_select',
				'show_desc_tip' => false,
			),
			array(
				'name' => 'author_credits',
				'label' => __( 'Hide author credits', 'anspress-question-answer' ),
				'desc' => __( 'Hide link to AnsPress project site.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'order' => '1',
				'show_desc_tip' => false,
			),
			array(
				'name' => 'allow_private_posts',
				'label' => __( 'Allow private posts', 'anspress-question-answer' ),
				'desc' => __( 'Allow users to create private question and answer.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			),

			array(
				'name' => 'db_cleanup',
				'label' => __( 'Clean DB', 'anspress-question-answer' ),
				'desc' => __( 'Check this to remove all anspress data including posts on deactivating plugin.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			),
			array(
				'name' => 'question_permalink_follow',
				'label' => __( 'Base page slug before question permalink', 'anspress-question-answer' ),
				'desc' => __( 'i.e. '.home_url( '/BASE_PAGE/question/QUESTION_TITLE' ), 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			),
			array(
				'name' => 'base_before_user_perma',
				'label' => __( 'Base page slug before user page permalink', 'anspress-question-answer' ),
				'desc' => __( 'i.e. '.home_url( '/BASE_PAGE/user/USER_NAME' ), 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			),
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'Toggle Features', 'anspress-question-answer' ) . '</span>',
			) ,
			array(
				'name' => 'disable_hover_card',
				'label' => __( 'Disable hover card', 'anspress-question-answer' ),
				'desc' => __( 'Dont show user hover card on mouseover.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			),
			array(
				'name' => 'disable_mentions',
				'label' => __( 'Disable mentions', 'anspress-question-answer' ),
				'desc' => __( 'Disable mentions and suggestions', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			),
			array(
				'name' => 'disable_reputation',
				'label' => __( 'Disable reputation', 'anspress-question-answer' ),
				'desc' => __( 'Disable reputation', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			)

		));

		// Register layout settings
		ap_register_option_group('layout', __( 'Layout', 'anspress-question-answer' ) , array(
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'Avatar', 'anspress-question-answer' ) . '</span>',
			) ,
			array(
				'name' => 'load_assets_in_anspress_only',
				'label' => __( 'Load assets in AnsPress page only?', 'anspress-question-answer' ),
				'desc' => __( 'Check this to load AnsPress JS and CSS on the AnsPress page only. Be careful, this might break layout.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'avatar_size_list',
				'label' => __( 'List avatar size', 'anspress-question-answer' ),
				'desc' => __( 'User avatar size for questions list.', 'anspress-question-answer' ),
				'type' => 'number',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'avatar_size_qquestion',
				'label' => __( 'Question avatar size', 'anspress-question-answer' ),
				'desc' => __( 'User avatar size for question.', 'anspress-question-answer' ),
				'type' => 'number',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'avatar_size_qanswer',
				'label' => __( 'Answer avatar size', 'anspress-question-answer' ),
				'desc' => __( 'User avatar size for answer.', 'anspress-question-answer' ),
				'type' => 'number',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'avatar_size_qanswer',
				'label' => __( 'Answer avatar size', 'anspress-question-answer' ),
				'desc' => __( 'User avatar size for answer.', 'anspress-question-answer' ),
				'type' => 'number',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'avatar_size_qcomment',
				'label' => __( 'Comment avatar size', 'anspress-question-answer' ),
				'desc' => __( 'User avatar size for comments.', 'anspress-question-answer' ),
				'type' => 'number',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'Items to show per page', 'anspress-question-answer' ) . '</span>',
			) ,
			array(
				'name' => 'question_per_page',
				'label' => __( 'Questions per page', 'anspress-question-answer' ),
				'desc' => __( 'Questions to show per page.', 'anspress-question-answer' ),
				'type' => 'number',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'answers_per_page',
				'label' => __( 'Answers per page', 'anspress-question-answer' ),
				'desc' => __( 'Answers to show per page.', 'anspress-question-answer' ),
				'type' => 'number',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'users_per_page',
				'label' => __( 'Users per page', 'anspress-question-answer' ),
				'desc' => __( 'Users to show per page.', 'anspress-question-answer' ),
				'type' => 'number',
			) ,
			array(
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'Sorting', 'anspress-question-answer' ) . '</span>',
			) ,
			array(
				'name' => 'answers_sort',
				'label' => __( 'Default answer sort', 'anspress-question-answer' ),
				'desc' => __( 'Sort answers by default.', 'anspress-question-answer' ),
				'type' => 'select',
				'options' => array(
					'voted' => __( 'Voted', 'anspress-question-answer' ),
					'active' => __( 'Active', 'anspress-question-answer' ),
					'newest' => __( 'Newest', 'anspress-question-answer' ),
					'oldest' => __( 'Oldest', 'anspress-question-answer' ),
				),
				'show_desc_tip' => false,
			) ,
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'Toggle', 'anspress-question-answer' ) . '</span>',
			) ,
			array(
				'name' => 'show_question_sidebar',
				'label' => __( 'Show question sidebar', 'anspress-question-answer' ),
				'desc' => __( 'Subscribe and Stats widgets will be shown on question page.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			),
			array(
				'name' => 'notification_sidebar',
				'label' => __( 'Show notification sidebar', 'anspress-question-answer' ),
				'desc' => __( 'Show dropdown notification as sidebar', 'anspress-question-answer' ),
				'type' => 'checkbox',
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
				'name' => 'ask_page_slug',
				'label' => __( 'Ask page slug', 'anspress-question-answer' ),
				'desc' => __( 'Enter slug for ask page.', 'anspress-question-answer' ),
				'type' => 'text',
				'show_desc_tip' => false,
				'order' => 5,
			),
			array(
				'name' => 'question_page_slug',
				'label' => __( 'Question page slug', 'anspress-question-answer' ),
				'desc' => __( 'Enter slug for question page.', 'anspress-question-answer' ),
				'type' => 'text',
				'show_desc_tip' => false,
				'order' => 5,
			),
			array(
				'name' => 'users_page_slug',
				'label' => __( 'Users page slug', 'anspress-question-answer' ),
				'desc' => __( 'Enter slug for users page.', 'anspress-question-answer' ),
				'type' => 'text',
				'show_desc_tip' => false,
				'order' => 5,
			),
			array(
				'name' => 'user_page_slug',
				'label' => __( 'User page slug', 'anspress-question-answer' ),
				'desc' => __( 'Enter slug for user page, make sure no page or post exists with same slug.', 'anspress-question-answer' ),
				'type' => 'text',
				'show_desc_tip' => false,
				'order' => 5,
			),
			array(
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'Page titles', 'anspress-question-answer' ) . '</span>',
			) ,
			array(
				'name' => 'show_title_in_question',
				'label' => __( 'Show question title', 'anspress-question-answer' ),
				'desc' => __( 'Show question title in single question page.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'show_solved_prefix',
				'label' => __( 'Show solved prefix', 'anspress-question-answer' ),
				'desc' => __( 'If an answer is selected for question then [solved] prefix will be added in title.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'base_page_title',
				'label' => __( 'Base page title', 'anspress-question-answer' ),
				'desc' => __( 'Main questions list page title', 'anspress-question-answer' ),
				'type' => 'text',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'ask_page_title',
				'label' => __( 'Ask page title', 'anspress-question-answer' ),
				'desc' => __( 'Title of the ask page', 'anspress-question-answer' ),
				'type' => 'text',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'users_page_title',
				'label' => __( 'Users page title', 'anspress-question-answer' ),
				'desc' => __( 'Title of the users page', 'anspress-question-answer' ),
				'type' => 'text',
				'show_desc_tip' => false,
			),array(
				'name' => 'search_page_title',
				'label' => __( 'Search page title', 'anspress-question-answer' ),
				'desc' => __( 'Title of the search page', 'anspress-question-answer' ),
				'type' => 'text',
				'show_desc_tip' => false,
			)
		));

		// Register question settings
		ap_register_option_group('question', __( 'Q&A', 'anspress-question-answer' ) , array(
			array(
				'name' => 'default_date_format',
				'label' => __( 'Show default date format', 'anspress-question-answer' ),
				'desc' => __( 'Instead of showing time passed i.e. 1 Hour ago, show default format date.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'Question', 'anspress-question-answer' ) . '</span>',
			) ,
			array(
				'name' => 'keep_stop_words',
				'label' => __( 'Keep stop words in question slug', 'anspress-question-answer' ),
				'desc' => __( 'AnsPress will not strip stop words in question slug.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'minimum_qtitle_length',
				'label' => __( 'Minimum title length', 'anspress-question-answer' ),
				'desc' => __( 'Set minimum letters for a question title.', 'anspress-question-answer' ),
				'type' => 'number',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'minimum_question_length',
				'label' => __( 'Minimum question content', 'anspress-question-answer' ),
				'desc' => __( 'Set minimum letters for a question contents.', 'anspress-question-answer' ),
				'type' => 'number',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'question_text_editor',
				'label' => __( 'Use text editor ?', 'anspress-question-answer' ),
				'desc' => __( 'Text editor as default.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'disable_comments_on_question',
				'label' => __( 'Disable comments', 'anspress-question-answer' ),
				'desc' => __( 'Disable comments on questions.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'disable_voting_on_question',
				'label' => __( 'Disable voting', 'anspress-question-answer' ),
				'desc' => __( 'Disable voting on questions.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'disable_down_vote_on_question',
				'label' => __( 'Disable down voting', 'anspress-question-answer' ),
				'desc' => __( 'Disable down voting on questions.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'close_selected',
				'label' => __( 'Close question after selecting answer', 'anspress-question-answer' ),
				'desc' => __( 'If enabled this will prevent user to submit answer on solved question.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			) ,
			array(
				'type' => 'custom',
				'html' => '<br /><span class="ap-form-separator">' . __( 'Answer', 'anspress-question-answer' ) . '</span>',
			) ,
			array(
				'name' => 'multiple_answers',
				'label' => __( 'Multiple Answers', 'anspress-question-answer' ),
				'desc' => __( 'Allow an user to submit multiple answers on a single question.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'minimum_ans_length',
				'label' => __( 'Minimum question content', 'anspress-question-answer' ),
				'desc' => __( 'Set minimum letters for a answer contents.', 'anspress-question-answer' ),
				'type' => 'number',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'answer_text_editor',
				'label' => __( 'Use text editor ?', 'anspress-question-answer' ),
				'desc' => __( 'Text editor as default.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'disable_comments_on_answer',
				'label' => __( 'Disable comments', 'anspress-question-answer' ),
				'desc' => __( 'Disable comments on answer.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'disable_voting_on_answer',
				'label' => __( 'Disable voting', 'anspress-question-answer' ),
				'desc' => __( 'Disable voting on answers.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'disable_down_vote_on_answer',
				'label' => __( 'Disable down voting', 'anspress-question-answer' ),
				'desc' => __( 'Disable down voting on answers.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'disable_answer_nav',
				'label' => __( 'Disable navigation', 'anspress-question-answer' ),
				'desc' => __( 'Disable answer navigation.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			) ,
		));

		// register user settings
		ap_register_option_group('users', __( 'Users', 'anspress-question-answer' ) , array(
			array(
				'name' => 'user_profile',
				'label' => __( 'User profile', 'anspress-question-answer' ),
				'desc' => __( 'Select which user profile you\'d like to use.', 'anspress-question-answer' ),
				'type' => 'select',
				'show_desc_tip' => false,
				'options' => array( 'none' => 'None','anspress' => 'AnsPress', 'buddypress' => 'BuddyPress', 'userpro' => 'User Pro' ),
			) ,
			array(
				'name' => 'enable_users_directory',
				'label' => __( 'Show users directory', 'anspress-question-answer' ),
				'desc' => __( 'When enabled public can see directory of users.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			) ,
			array(
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'Features', 'anspress-question-answer' ) . '</span>',
			) ,

			array(
				'name' => 'users_page_avatar_size',
				'label' => __( 'Users page avatar size', 'anspress-question-answer' ),
				'desc' => __( 'Set user avatar size for users page item.', 'anspress-question-answer' ),
				'type' => 'number',
				'show_desc_tip' => false,
			) ,

			array(
				'name' => 'cover_width',
				'label' => __( 'Cover width', 'anspress-question-answer' ),
				'desc' => __( 'Set width of user cover photo.', 'anspress-question-answer' ),
				'type' => 'number',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'cover_height',
				'label' => __( 'Cover height', 'anspress-question-answer' ),
				'desc' => __( 'Set height of user cover photo.', 'anspress-question-answer' ),
				'type' => 'number',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'cover_width_small',
				'label' => __( 'Cover thumb width', 'anspress-question-answer' ),
				'desc' => __( 'Set width of user cover photo thumbnail.', 'anspress-question-answer' ),
				'type' => 'number',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'cover_height_small',
				'label' => __( 'Small cover height', 'anspress-question-answer' ),
				'desc' => __( 'Set height of user cover photo thumbnail.', 'anspress-question-answer' ),
				'type' => 'number',
				'show_desc_tip' => false,
			) ,
		));

		// register permission settings
		ap_register_option_group('permission', __( 'Permission', 'anspress-question-answer' ) , array(
			array(
				'name' => 'only_logged_in',
				'label' => __( 'Only logged in can see questions, answer and comments?', 'anspress-question-answer' ),
				'desc' => __( 'Require user to login to see AnsPress contents?.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			),
			array(
				'name' => 'multiple_answers',
				'label' => __( 'Multiple answers', 'anspress-question-answer' ),
				'desc' => __( 'Allow user to submit multiple answer per question.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			),
			array(
				'name' => 'disallow_op_to_answer',
				'label' => __( 'Asker can answer', 'anspress-question-answer' ),
				'desc' => __( 'Allow asker to answer his own question.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			),
			array(
				'name' => 'allow_anonymous',
				'label' => __( 'Allow anonymous', 'anspress-question-answer' ),
				'desc' => __( 'Non-loggedin user can post questions and answers.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			),
			array(
				'name' => 'only_admin_can_answer',
				'label' => __( 'Only admin can answer', 'anspress-question-answer' ),
				'desc' => __( 'Only allow admin to answer question.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			),
			array(
				'name' => 'logged_in_can_see_ans',
				'label' => __( 'Only logged in can see answers', 'anspress-question-answer' ),
				'desc' => __( 'non-loggedin user cannot see answers.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			),
			
			array(
				'name' => 'logged_in_can_see_comment',
				'label' => __( 'Only logged in can see comment', 'anspress-question-answer' ),
				'desc' => __( 'non-loggedin user cannot see comments.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			),
			array(
				'name' => 'disable_delete_after',
				'label' => __( 'Lock delete action', 'anspress-question-answer' ),
				'desc' => __( 'Lock comment or post after a period so that user cannot delete it. Set the time in epoch i.e. 86400 = 1 day.', 'anspress-question-answer' ),
				'type' => 'number',
				'show_desc_tip' => false,
			),
			array(
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'Upload', 'anspress-question-answer' ) . '</span>',
			),
			array(
				'name' => 'allow_upload_image',
				'label' => __( 'Allow image upload', 'anspress-question-answer' ),
				'desc' => __( 'Allow logged-in users to upload image.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			),
			array(
				'name' => 'image_per_post',
				'label' => __( 'Max images per post', 'anspress-question-answer' ),
				'desc' => __( 'Set how many images user can upload for each post.', 'anspress-question-answer' ),
				'type' => 'number',
				'show_desc_tip' => false,
			),
			array(
				'name' => 'max_upload_size',
				'label' => __( 'Max upload size', 'anspress-question-answer' ),
				'desc' => __( 'Set maximum upload size.', 'anspress-question-answer' ),
				'type' => 'text',
				'show_desc_tip' => false,
			),
		));

		// register moderate settings
		ap_register_option_group('moderate', __( 'Moderate', 'anspress-question-answer' ) , array(
			array(
				'name' => 'new_question_status',
				'label' => __( 'Status of new question', 'anspress-question-answer' ),
				'desc' => __( 'Set post status of new question.', 'anspress-question-answer' ),
				'type' => 'select',
				'options' => array(
					'publish' => __( 'Publish', 'anspress-question-answer' ),
					'moderate' => __( 'Moderate', 'profile', 'anspress-question-answer' ),
				),
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'edit_question_status',
				'label' => __( 'Status of edited question', 'anspress-question-answer' ),
				'desc' => __( 'Set post status of edited question.', 'anspress-question-answer' ),
				'type' => 'select',
				'options' => array(
					'publish' => __( 'Publish', 'anspress-question-answer' ),
					'moderate' => __( 'Moderate', 'profile', 'anspress-question-answer' ),
				),
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'new_answer_status',
				'label' => __( 'Status of new answer', 'anspress-question-answer' ),
				'desc' => __( 'Set post status of new answer.', 'anspress-question-answer' ),
				'type' => 'select',
				'options' => array(
					'publish' => __( 'Publish', 'anspress-question-answer' ),
					'moderate' => __( 'Moderate', 'profile', 'anspress-question-answer' ),
				),
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'edit_answer_status',
				'label' => __( 'Status of edited answer', 'anspress-question-answer' ),
				'desc' => __( 'Set post status of edited answer.', 'anspress-question-answer' ),
				'type' => 'select',
				'options' => array(
					'publish' => __( 'Publish', 'anspress-question-answer' ),
					'moderate' => __( 'Moderate', 'profile', 'anspress-question-answer' ),
				),
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'anonymous_post_status',
				'label' => __( 'Status of anonymous post', 'anspress-question-answer' ),
				'desc' => __( 'Set post status post submitted by anonymous user.', 'anspress-question-answer' ),
				'type' => 'select',
				'options' => array(
					'publish' => __( 'Publish', 'anspress-question-answer' ),
					'moderate' => __( 'Moderate', 'profile', 'anspress-question-answer' ),
				),
				'show_desc_tip' => false,
			) ,
			array(
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'Bad Words', 'anspress-question-answer' ) . '</span>',
			),
			array(
				'name' => 'check_bad_words',
				'label' => __( 'Check bad words', 'anspress-question-answer' ),
				'desc' => __( 'Enable this to check for bad words in posts and comments. You can find bad words here ', 'anspress-question-answer' ). '<a href="https://raw.githubusercontent.com/anspress/anspress/master/theme/default/badwords.txt">badwords.txt</a>',
				'type' => 'checkbox',
				'show_desc_tip' => false,
			),
			array(
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'Akismet check', 'anspress-question-answer' ) . '</span>',
			) ,
			array(
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'reCaptacha', 'anspress-question-answer' ) . '</span>',
			) ,
			array(
				'name' => 'enable_recaptcha',
				'label' => __( 'Enable reCaptcha', 'anspress-question-answer' ),
				'desc' => __( 'Use this for preventing spam posts.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'recaptcha_site_key',
				'label' => __( 'Recaptcha site key', 'anspress-question-answer' ),
				'desc' => __( 'Enter your site key, if you dont have it get it from here https://www.google.com/recaptcha/admin', 'anspress-question-answer' ),
				'type' => 'text',
				'show_desc_tip' => false,
			) ,
			array(
				'name' => 'recaptcha_secret_key',
				'label' => __( 'Recaptcha secret key', 'anspress-question-answer' ),
				'desc' => __( 'Enter your secret key', 'anspress-question-answer' ),
				'type' => 'text',
				'show_desc_tip' => false,
			) ,

		));
		ap_register_option_group( 'roles', __( 'User roles', 'anspress-question-answer' ) , array( 'callback' => [ __CLASS__, 'permissions_page' ] ), false );
	}
	public static function permissions_page() {
		include 'views/permissions.php';
	}
}
