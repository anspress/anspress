<?php
/**
 * AnsPress options.
 *
 * @since 5.0.0
 * @package AnsPress
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'migration.installed_version'    => array(
		'type'  => 'integer',
		'value' => 0,
	),
	'migration.started'              => array( // Used to check if migration is started.
		'type'  => 'boolean',
		'value' => false,
	),
	'migration.current_file_running' => array( // For storing current running migration file.
		'type'  => 'string',
		'value' => '',
	),
	'show_login_signup'              => array(
		'type'  => 'boolean',
		'value' => true,
	),
	'show_login'                     => array(
		'type'  => 'boolean',
		'value' => true,
	),
	'show_signup'                    => array(
		'type'  => 'boolean',
		'value' => true,
	),
	'theme'                          => array(
		'type'  => 'string',
		'value' => 'default',
	),
	'author_credits'                 => array(
		'type'  => 'boolean',
		'value' => false,
	),
	'clear_database'                 => array(
		'type'  => 'boolean',
		'value' => false,
	),
	'minimum_qtitle_length'          => array(
		'type'  => 'integer',
		'value' => 10,
	),
	'minimum_question_length'        => array(
		'type'  => 'integer',
		'value' => 10,
	),
	'multiple_answers'               => array(
		'type'  => 'boolean',
		'value' => true,
	),
	'disallow_op_to_answer'          => array(
		'type'  => 'boolean',
		'value' => false,
	),
	'minimum_ans_length'             => array(
		'type'  => 'integer',
		'value' => 5,
	),
	'avatar_size_qquestion'          => array(
		'type'  => 'integer',
		'value' => 50,
	),
	'allow_private_post'             => array(
		'type'  => 'boolean',
		'value' => true,
	),
	'avatar_size_qanswer'            => array(
		'type'  => 'integer',
		'value' => 50,
	),
	'avatar_size_qcomment'           => array(
		'type'  => 'integer',
		'value' => 25,
	),
	'avatar_size_list'               => array(
		'type'  => 'integer',
		'value' => 45,
	),
	'question_per_page'              => array(
		'type'  => 'string',
		'value' => '20',
	),
	'answers_per_page'               => array(
		'type'  => 'string',
		'value' => '5',
	),
	'question_order_by'              => array(
		'type'  => 'string',
		'value' => 'active',
	),
	'answers_sort'                   => array(
		'type'  => 'string',
		'value' => 'active',
	),
	'close_selected'                 => array(
		'type'  => 'boolean',
		'value' => true,
	),
	'moderate_new_question'          => array(
		'type'  => 'string',
		'value' => 'no_mod',
	),
	'mod_question_point'             => array(
		'type'  => 'integer',
		'value' => 10,
	),
	'question_prefix'                => array(
		'type'  => 'string',
		'value' => 'question',
	),
	'question_text_editor'           => array(
		'type'  => 'boolean',
		'value' => false,
	),
	'answer_text_editor'             => array(
		'type'  => 'boolean',
		'value' => false,
	),
	'base_page_title'                => array(
		'type'  => 'string',
		'value' => __( 'Questions', 'anspress-question-answer' ),
	),
	'search_page_title'              => array(
		'type'  => 'string',
		// translators: %s is search query.
		'value' => __( 'Search "%s"', 'anspress-question-answer' ),
	),
	'user_page_title'                => array(
		'type'  => 'string',
		'value' => '%s',
	),
	'disable_comments_on_question'   => array(
		'type'  => 'boolean',
		'value' => false,
	),
	'disable_comments_on_answer'     => array(
		'type'  => 'boolean',
		'value' => false,
	),
	'new_question_status'            => array(
		'type'  => 'string',
		'value' => 'publish',
	),
	'new_answer_status'              => array(
		'type'  => 'string',
		'value' => 'publish',
	),
	'edit_question_status'           => array(
		'type'  => 'string',
		'value' => 'publish',
	),
	'edit_answer_status'             => array(
		'type'  => 'string',
		'value' => 'publish',
	),
	'disable_delete_after'           => array(
		'type'  => 'integer',
		'value' => 86400,
	),
	'db_cleanup'                     => array(
		'type'  => 'boolean',
		'value' => false,
	),
	'disable_voting_on_question'     => array(
		'type'  => 'boolean',
		'value' => false,
	),
	'disable_voting_on_answer'       => array(
		'type'  => 'boolean',
		'value' => false,
	),
	'enable_recaptcha'               => array(
		'type'  => 'boolean',
		'value' => false,
	),
	'recaptcha_site_key'             => array(
		'type'  => 'string',
		'value' => '',
	),
	'recaptcha_secret_key'           => array(
		'type'  => 'string',
		'value' => '',
	),
	'show_question_sidebar'          => array(
		'type'  => 'boolean',
		'value' => true,
	),
	'allow_upload'                   => array(
		'type'  => 'boolean',
		'value' => true,
	),
	'uploads_per_post'               => array(
		'type'  => 'integer',
		'value' => 4,
	),
	'question_page_slug'             => array(
		'type'  => 'string',
		'value' => 'question',
	),
	'question_page_permalink'        => array(
		'type'  => 'string',
		'value' => 'question_perma_1',
	),
	'max_upload_size'                => array(
		'type'  => 'integer',
		'value' => 500000,
	),
	'allowed_file_mime'              => array(
		'type'  => 'string',
		'value' => "jpeg|jpg=>image/jpeg\npng=>image/png\ngif=>image/gif",
	),
	'disable_down_vote_on_question'  => array(
		'type'  => 'boolean',
		'value' => false,
	),
	'disable_down_vote_on_answer'    => array(
		'type'  => 'boolean',
		'value' => false,
	),
	'show_solved_prefix'             => array(
		'type'  => 'boolean',
		'value' => true,
	),
	'load_assets_in_anspress_only'   => array(
		'type'  => 'boolean',
		'value' => false,
	),
	'keep_stop_words'                => array(
		'type'  => 'boolean',
		'value' => true,
	),
	'default_date_format'            => array(
		'type'  => 'boolean',
		'value' => false,
	),
	'anonymous_post_status'          => array(
		'type'  => 'string',
		'value' => 'moderate',
	),
	'bad_words'                      => array(
		'type'  => 'string',
		'value' => '',
	),
	'duplicate_check'                => array(
		'type'  => 'boolean',
		'value' => true,
	),
	'disable_q_suggestion'           => array(
		'type'  => 'boolean',
		'value' => false,
	),
	'comment_number'                 => array(
		'type'  => 'integer',
		'value' => 5,
	),
	'read_question_per'              => array(
		'type'  => 'string',
		'value' => 'anyone',
	),
	'read_answer_per'                => array(
		'type'  => 'string',
		'value' => 'anyone',
	),
	'read_comment_per'               => array(
		'type'  => 'string',
		'value' => 'anyone',
	),
	'post_question_per'              => array(
		'type'  => 'string',
		'value' => 'anyone',
	),
	'post_answer_per'                => array(
		'type'  => 'string',
		'value' => 'logged_in',
	),
	'post_comment_per'               => array(
		'type'  => 'string',
		'value' => 'logged_in',
	),
	'activity_exclude_roles'         => array(
		'type'  => 'string',
		'value' => 'administrator',
	),
	'create_account'                 => array(
		'type'  => 'boolean',
		'value' => true,
	),
	'allow_private_posts'            => array(
		'type'  => 'boolean',
		'value' => true,
	),
	'trashing_question_with_answer'  => array(
		'type'  => 'boolean',
		'value' => false,
	),
	'deleting_question_with_answer'  => array(
		'type'  => 'boolean',
		'value' => false,
	),
);
