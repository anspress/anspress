<?php
/**
 * AnsPresss admin class
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-3.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 */

$settings = ap_opt();

ap_register_option_group( 'general', __( 'General', 'anspress-question-answer' ) );
ap_register_option_group( 'addons', __( 'Add-ons', 'anspress-question-answer' ) );
ap_register_option_group( 'access', __( 'Access', 'anspress-question-answer' ) );
ap_register_option_group( 'tools', __( 'Tools', 'anspress-question-answer' ) );

// Register general section of general group.
ap_register_option_section( 'general', 'general', __( 'Pages and permalinks', 'anspress-question-answer' ), [
		array(
			'name'  => 'base_page',
			'label' => __( 'Questions page', 'anspress-question-answer' ),
			'desc'  => __( 'Select page for displaying anspress.', 'anspress-question-answer' ),
			'type'  => 'page_select',
		),
		array(
			'name'  => 'ask_page_slug',
			'label' => __( 'Ask question page slug', 'anspress-question-answer' ),
			'desc'  => __( 'Set a slug for ask question page.', 'anspress-question-answer' ),
			'type' => 'text',
		),
		array(
			'name'  => 'question_page_slug',
			'label' => __( 'Question slug', 'anspress-question-answer' ),
			'desc'  => __( 'Slug for single question page.', 'anspress-question-answer' ),
			'type' => 'text',
		),
		array(
			'name'  => 'question_page_permalink',
			'label' => __( 'Question permalink', 'anspress-question-answer' ),
			'desc'  => __( 'Select single question permalink structure.', 'anspress-question-answer' ),
			'type'  => 'radio',
			'options' => [
				'question_perma_1' => home_url( '/' . ap_base_page_slug() ) . '/<b class="ap-base-slug">' . ap_opt( 'question_page_slug' ). '</b><b>/question-name/</b>',
				'question_perma_2' => home_url( '/' ) . '<b class="ap-base-slug">' . ap_opt( 'question_page_slug' ). '</b><b>/question-name/</b>',
				'question_perma_3' => home_url( '/' ) . '<b class="ap-base-slug">' . ap_opt( 'question_page_slug' ). '</b><b>/213/</b>',
				'question_perma_4' => home_url( '/' ) . '<b class="ap-base-slug">' . ap_opt( 'question_page_slug' ). '</b><b>/213/question-name/</b>',
			],
		),
		array(
			'name'  => 'author_credits',
			'label' => __( 'Hide author credits', 'anspress-question-answer' ),
			'desc'  => __( 'Hide link to AnsPress project site.', 'anspress-question-answer' ),
			'type'  => 'checkbox',
			'order' => '1',
		),
		array(
			'type' => 'custom',
			'html' => '<span class="ap-form-separator">' . __( 'Page Titles', 'anspress-question-answer' ) . '</span>',
		) ,
		array(
			'name'  => 'base_page_title',
			'label' => __( 'Base page title', 'anspress-question-answer' ),
			'desc'  => __( 'Main questions list page title', 'anspress-question-answer' ),
		) ,
		array(
			'name'  => 'ask_page_title',
			'label' => __( 'Ask page title', 'anspress-question-answer' ),
			'desc'  => __( 'Title of the ask page', 'anspress-question-answer' ),
		) ,
		array(
			'name'  => 'search_page_title',
			'label' => __( 'Search page title', 'anspress-question-answer' ),
			'desc'  => __( 'Title of the search page', 'anspress-question-answer' ),
		),
		array(
			'name'  => 'author_page_title',
			'label' => __( 'Author page title', 'anspress-question-answer' ),
			'desc'  => __( 'Title of the author page', 'anspress-question-answer' ),
		),
		array(
			'name'  => 'show_solved_prefix',
			'label' => __( 'Show solved prefix', 'anspress-question-answer' ),
			'desc'  => __( 'If an answer is selected for question then [solved] prefix will be added in title.', 'anspress-question-answer' ),
			'type'  => 'checkbox',
		) ,
	] );

// Register layout section.
ap_register_option_section( 'general', 'layout', __( 'Layout', 'anspress-question-answer' ), [
		array(
			'name'  => 'load_assets_in_anspress_only',
			'label' => __( 'Load assets in AnsPress page only?', 'anspress-question-answer' ),
			'desc'  => __( 'Check this to load AnsPress JS and CSS on the AnsPress page only. Be careful, this might break layout.', 'anspress-question-answer' ),
			'type'  => 'checkbox',
		) ,
		array(
			'name' => '__sep',
			'type' => 'custom',
			'html' => '<span class="ap-form-separator">' . __( 'Avatar', 'anspress-question-answer' ) . '</span>',
		) ,

		array(
			'name'  => 'avatar_size_list',
			'label' => __( 'List avatar size', 'anspress-question-answer' ),
			'desc'  => __( 'User avatar size for questions list.', 'anspress-question-answer' ),
			'type'  => 'number',
		) ,
		array(
			'name'  => 'avatar_size_qquestion',
			'label' => __( 'Question avatar size', 'anspress-question-answer' ),
			'desc'  => __( 'User avatar size for question.', 'anspress-question-answer' ),
			'type'  => 'number',
		) ,
		array(
			'name'  => 'avatar_size_qanswer',
			'label' => __( 'Answer avatar size', 'anspress-question-answer' ),
			'desc'  => __( 'User avatar size for answer.', 'anspress-question-answer' ),
			'type'  => 'number',
		) ,
		array(
			'name'  => 'avatar_size_qanswer',
			'label' => __( 'Answer avatar size', 'anspress-question-answer' ),
			'desc'  => __( 'User avatar size for answer.', 'anspress-question-answer' ),
			'type'  => 'number',
		) ,
		array(
			'name'  => 'avatar_size_qcomment',
			'label' => __( 'Comment avatar size', 'anspress-question-answer' ),
			'desc'  => __( 'User avatar size for comments.', 'anspress-question-answer' ),
			'type'  => 'number',
		) ,
		array(
			'name' => '__sep',
			'type' => 'custom',
			'html' => '<span class="ap-form-separator">' . __( 'Items to show per page', 'anspress-question-answer' ) . '</span>',
		) ,
		array(
			'name'  => 'question_per_page',
			'label' => __( 'Questions per page', 'anspress-question-answer' ),
			'desc'  => __( 'Questions to show per page.', 'anspress-question-answer' ),
			'type'  => 'number',
		) ,
		array(
			'name'  => 'answers_per_page',
			'label' => __( 'Answers per page', 'anspress-question-answer' ),
			'desc'  => __( 'Answers to show per page.', 'anspress-question-answer' ),
			'type'  => 'number',
		)
	] );

// Register question and answers section.
ap_register_option_section( 'general', 'qa', __( 'Question & Answer', 'anspress-question-answer' ), [
		array(
			'name'  => 'allow_private_posts',
			'label' => __( 'Allow private posts', 'anspress-question-answer' ),
			'desc'  => __( 'Allow users to create private question and answer.', 'anspress-question-answer' ),
			'type'  => 'checkbox',
		),
		array(
			'name'  => 'show_comments_default',
			'label' => __( 'Load comments', 'anspress-question-answer' ),
			'desc'  => __( 'Show question and answer comments by default', 'anspress-question-answer' ),
			'type'  => 'checkbox',
		),
		array(
			'name'  => 'comment_number',
			'label' => __( 'Numbers of comments to show', 'anspress-question-answer' ),
			'desc'  => __( 'Numbers of comments to load in each query?', 'anspress-question-answer' ),
		),
		array(
			'name'  => 'duplicate_check',
			'label' => __( 'Check duplicate', 'anspress-question-answer' ),
			'desc'  => __( 'Check for duplicate posts before posting', 'anspress-question-answer' ),
			'type'  => 'checkbox',
		),
		array(
			'name'  => 'disable_q_suggestion',
			'label' => __( 'Disable question suggestion', 'anspress-question-answer' ),
			'desc'  => __( 'Checking this will disable question suggestion in ask form', 'anspress-question-answer' ),
			'type'  => 'checkbox',
		),
		array(
			'name'  => 'default_date_format',
			'label' => __( 'Show default date format', 'anspress-question-answer' ),
			'desc'  => __( 'Instead of showing time passed i.e. 1 Hour ago, show default format date.', 'anspress-question-answer' ),
			'type'  => 'checkbox',
		) ,
		array(
			'name' => '__sep',
			'type' => 'custom',
			'html' => '<span class="ap-form-separator">' . __( 'Question', 'anspress-question-answer' ) . '</span>',
		) ,
		array(
			'name'    => 'question_order_by',
			'label'   => __( 'Default question order', 'anspress-question-answer' ),
			'desc'    => __( 'Order question list by default using selected', 'anspress-question-answer' ),
			'type'    => 'select',
			'options' => array(
				'voted'  => __( 'Voted', 'anspress-question-answer' ),
				'active' => __( 'Active', 'anspress-question-answer' ),
				'newest' => __( 'Newest', 'anspress-question-answer' ),
				'oldest' => __( 'Oldest', 'anspress-question-answer' ),
			),
		),
		array(
			'name'  => 'keep_stop_words',
			'label' => __( 'Keep stop words in question slug', 'anspress-question-answer' ),
			'desc'  => __( 'AnsPress will not strip stop words in question slug.', 'anspress-question-answer' ),
			'type'  => 'checkbox',
		) ,
		array(
			'name'  => 'minimum_qtitle_length',
			'label' => __( 'Minimum title length', 'anspress-question-answer' ),
			'desc'  => __( 'Set minimum letters for a question title.', 'anspress-question-answer' ),
			'type'  => 'number',
		) ,
		array(
			'name'  => 'minimum_question_length',
			'label' => __( 'Minimum question content', 'anspress-question-answer' ),
			'desc'  => __( 'Set minimum letters for a question contents.', 'anspress-question-answer' ),
			'type'  => 'number',
		) ,
		array(
			'name'  => 'question_text_editor',
			'label' => __( 'Use text editor ?', 'anspress-question-answer' ),
			'desc'  => __( 'Text editor as default.', 'anspress-question-answer' ),
			'type'  => 'checkbox',
		) ,
		array(
			'name'  => 'disable_comments_on_question',
			'label' => __( 'Disable comments', 'anspress-question-answer' ),
			'desc'  => __( 'Disable comments on questions.', 'anspress-question-answer' ),
			'type'  => 'checkbox',
		) ,
		array(
			'name'  => 'disable_voting_on_question',
			'label' => __( 'Disable voting', 'anspress-question-answer' ),
			'desc'  => __( 'Disable voting on questions.', 'anspress-question-answer' ),
			'type'  => 'checkbox',
		) ,
		array(
			'name'  => 'disable_down_vote_on_question',
			'label' => __( 'Disable down voting', 'anspress-question-answer' ),
			'desc'  => __( 'Disable down voting on questions.', 'anspress-question-answer' ),
			'type'  => 'checkbox',
		) ,
		array(
			'name'  => 'close_selected',
			'label' => __( 'Close question after selecting answer', 'anspress-question-answer' ),
			'desc'  => __( 'If enabled this will prevent user to submit answer on solved question.', 'anspress-question-answer' ),
			'type'  => 'checkbox',
		) ,
		array(
			'type' => 'custom',
			'html' => '<br /><span class="ap-form-separator">' . __( 'Answer', 'anspress-question-answer' ) . '</span>',
		) ,
		array(
			'name'    => 'answers_sort',
			'label'   => __( 'Default answers order', 'anspress-question-answer' ),
			'desc'    => __( 'Order answers by by default using selected', 'anspress-question-answer' ),
			'type'    => 'select',
			'options' => array(
				'voted'  => __( 'Voted', 'anspress-question-answer' ),
				'active' => __( 'Active', 'anspress-question-answer' ),
				'newest' => __( 'Newest', 'anspress-question-answer' ),
				'oldest' => __( 'Oldest', 'anspress-question-answer' ),
			),
		),
		array(
			'name'  => 'multiple_answers',
			'label' => __( 'Multiple Answers', 'anspress-question-answer' ),
			'desc'  => __( 'Allow an user to submit multiple answers on a single question.', 'anspress-question-answer' ),
			'type'  => 'checkbox',
		) ,
		array(
			'name'  => 'minimum_ans_length',
			'label' => __( 'Minimum question content', 'anspress-question-answer' ),
			'desc'  => __( 'Set minimum letters for a answer contents.', 'anspress-question-answer' ),
			'type'  => 'number',
		) ,
		array(
			'name'  => 'answer_text_editor',
			'label' => __( 'Use text editor ?', 'anspress-question-answer' ),
			'desc'  => __( 'Text editor as default.', 'anspress-question-answer' ),
			'type'  => 'checkbox',
		) ,
		array(
			'name'  => 'disable_comments_on_answer',
			'label' => __( 'Disable comments', 'anspress-question-answer' ),
			'desc'  => __( 'Disable comments on answer.', 'anspress-question-answer' ),
			'type'  => 'checkbox',
		) ,
		array(
			'name'  => 'disable_voting_on_answer',
			'label' => __( 'Disable voting', 'anspress-question-answer' ),
			'desc'  => __( 'Disable voting on answers.', 'anspress-question-answer' ),
			'type'  => 'checkbox',
		) ,
		array(
			'name'  => 'disable_down_vote_on_answer',
			'label' => __( 'Disable down voting', 'anspress-question-answer' ),
			'desc'  => __( 'Disable down voting on answers.', 'anspress-question-answer' ),
			'type'  => 'checkbox',
		) ,
	]);

// Register addons toggle seaction
ap_register_option_section( 'addons', 'toggle_addons', __( 'Add-ons', 'anspress-question-answer' ), 'ap_admin_addons_page' );

// Register permission section.
ap_register_option_section( 'access', 'permissions',  __( 'Permissions', 'anspress-question-answer' ), [
	array(
		'name'  => 'only_logged_in',
		'label' => __( 'Only logged in can see questions, answer and comments?', 'anspress-question-answer' ),
		'desc'  => __( 'Require user to login to see AnsPress contents?.', 'anspress-question-answer' ),
		'type'  => 'checkbox',
	),
	array(
		'name'  => 'multiple_answers',
		'label' => __( 'Multiple answers', 'anspress-question-answer' ),
		'desc'  => __( 'Allow user to submit multiple answer per question.', 'anspress-question-answer' ),
		'type'  => 'checkbox',
	),
	array(
		'name'  => 'disallow_op_to_answer',
		'label' => __( 'Asker can answer', 'anspress-question-answer' ),
		'desc'  => __( 'Allow asker to answer his own question.', 'anspress-question-answer' ),
		'type'  => 'checkbox',
	),
	array(
		'name'  => 'allow_anonymous',
		'label' => __( 'Allow anonymous', 'anspress-question-answer' ),
		'desc'  => __( 'Non-loggedin user can post questions and answers.', 'anspress-question-answer' ),
		'type'  => 'checkbox',
	),
	array(
		'name'  => 'only_admin_can_answer',
		'label' => __( 'Only admin can answer', 'anspress-question-answer' ),
		'desc'  => __( 'Only allow admin to answer question.', 'anspress-question-answer' ),
		'type'  => 'checkbox',
	),
	array(
		'name'  => 'logged_in_can_see_ans',
		'label' => __( 'Only logged in can see answers', 'anspress-question-answer' ),
		'desc'  => __( 'non-loggedin user cannot see answers.', 'anspress-question-answer' ),
		'type'  => 'checkbox',
	),
	array(
		'name'  => 'logged_in_can_see_comment',
		'label' => __( 'Only logged in can see comment', 'anspress-question-answer' ),
		'desc'  => __( 'non-loggedin user cannot see comments.', 'anspress-question-answer' ),
		'type'  => 'checkbox',
	),
	array(
		'name'  => 'disable_delete_after',
		'label' => __( 'Lock delete action', 'anspress-question-answer' ),
		'desc'  => __( 'Lock comment or post after a period so that user cannot delete it. Set the time in epoch i.e. 86400 = 1 day.', 'anspress-question-answer' ),
		'type'  => 'number',
	),
	array(
		'type' => 'custom',
		'html' => '<span class="ap-form-separator">' . __( 'Upload', 'anspress-question-answer' ) . '</span>',
	),
	array(
		'name'  => 'allow_upload',
		'label' => __( 'Allow image upload', 'anspress-question-answer' ),
		'desc'  => __( 'Allow logged-in users to upload image.', 'anspress-question-answer' ),
		'type'  => 'checkbox',
	),
	array(
		'name'  => 'uploads_per_post',
		'label' => __( 'Max uploads per post', 'anspress-question-answer' ),
		'desc'  => __( 'Set numbers of media user can upload for each post.', 'anspress-question-answer' ),
		'type'  => 'number',
	),
	array(
		'name'  => 'max_upload_size',
		'label' => __( 'Max upload size', 'anspress-question-answer' ),
		'desc'  => __( 'Set maximum upload size.', 'anspress-question-answer' ),
	),
]);

// Register moderate section.
ap_register_option_section( 'access', 'moderate',  __( 'Moderate', 'anspress-question-answer' ), [
	array(
		'name'    => 'new_question_status',
		'label'   => __( 'Status of new question', 'anspress-question-answer' ),
		'desc'    => __( 'Set post status of new question.', 'anspress-question-answer' ),
		'type'    => 'select',
		'options' => array(
			'publish'  => __( 'Publish', 'anspress-question-answer' ),
			'moderate' => __( 'Moderate', 'anspress-question-answer' ),
		),
	) ,
	array(
		'name'    => 'edit_question_status',
		'label'   => __( 'Status of edited question', 'anspress-question-answer' ),
		'desc'    => __( 'Set post status of edited question.', 'anspress-question-answer' ),
		'type'    => 'select',
		'options' => array(
			'publish'  => __( 'Publish', 'anspress-question-answer' ),
			'moderate' => __( 'Moderate', 'anspress-question-answer' ),
		),
	) ,
	array(
		'name'    => 'new_answer_status',
		'label'   => __( 'Status of new answer', 'anspress-question-answer' ),
		'desc'    => __( 'Set post status of new answer.', 'anspress-question-answer' ),
		'type'    => 'select',
		'options' => array(
			'publish'  => __( 'Publish', 'anspress-question-answer' ),
			'moderate' => __( 'Moderate', 'anspress-question-answer' ),
		),
	) ,
	array(
		'name'    => 'edit_answer_status',
		'label'   => __( 'Status of edited answer', 'anspress-question-answer' ),
		'desc'    => __( 'Set post status of edited answer.', 'anspress-question-answer' ),
		'type'    => 'select',
		'options' => array(
			'publish'  => __( 'Publish', 'anspress-question-answer' ),
			'moderate' => __( 'Moderate', 'anspress-question-answer' ),
		),
	) ,
	array(
		'name'    => 'anonymous_post_status',
		'label'   => __( 'Status of anonymous post', 'anspress-question-answer' ),
		'desc'    => __( 'Set post status post submitted by anonymous user.', 'anspress-question-answer' ),
		'type'    => 'select',
		'options' => array(
			'publish'  => __( 'Publish', 'anspress-question-answer' ),
			'moderate' => __( 'Moderate', 'anspress-question-answer' ),
		),
	) ,
]);

ap_register_option_section( 'tools', 'recount', __( 'Re-count', 'anspress-question-answer' ) , 'ap_admin_recount_page' );
ap_register_option_section( 'tools', 'user_roles', __( 'User roles', 'anspress-question-answer' ) , 'ap_admin_roles_page' );
ap_register_option_section( 'tools', 'uninstall', __( 'Uninstall - clear all AnsPress data', 'anspress-question-answer' ) , 'ap_admin_uninstall_page' );

/**
 * Load roles option.
 */
function ap_admin_recount_page() {
	include 'views/recount.php';
}

/**
 * Load roles option.
 */
function ap_admin_roles_page() {
	include 'views/roles.php';
}

/**
 * Loads uninstall page.
 */
function ap_admin_uninstall_page() {
	include 'views/uninstall.php';
}

/**
 * Loads uninstall page.
 */
function ap_admin_addons_page() {
	include 'views/addons.php';
}
