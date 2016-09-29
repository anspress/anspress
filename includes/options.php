<?php
/**
 * AnsPress options
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 * @since 2.0.1
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * To retrive AnsPress option
 * @param  string $key   Name of option to retrive,
 *                       Keep it blank to get all options of AnsPress
 * @param  string $value Enter value to update existing option
 * @return string
 * @since 0.1
 */
function ap_opt($key = false, $value = null) {
	$settings = wp_cache_get('anspress_opt', 'ap' );
	
	if ( false === $settings ) {
		$settings = get_option( 'anspress_opt' );

		if ( ! $settings ) {
			$settings = array();
		}

		wp_cache_set('anspress_opt', $settings, 'ap' );
	}

	$settings = $settings + ap_default_options();

	if ( ! is_null( $value ) ) {

		$settings[$key] = $value;
		update_option( 'anspress_opt', $settings );

		// Clear cache if option updated.
		wp_cache_delete( 'anspress_opt', 'ap' );

		return;
	}

	if ( false === $key ) {
		return $settings; 
	}

	if ( isset($settings[$key] ) ) {
		return $settings[$key]; 
	} else {
		return null; 
	}

	return false;
}

/**
 * Default options for AnsPress
 * @return array
 * @since 2.0.1
 */
function ap_default_options() {
	$cache = wp_cache_get( 'ap_default_options', 'ap' );

	if ( false !== $cache ) {
		return $cache;
	}

	$defaults = array(
		'show_login_signup' 		=> true,
		'show_login' 				=> true,
		'show_signup' 				=> true,
		'show_title_in_question'	=> false,
		'show_social_login'			=> false,
		'theme' 					=> 'default',
		'author_credits' 			=> false,
		'clear_database' 			=> false,
		'minimum_qtitle_length'		=> 10,
		'minimum_question_length'	=> 10,
		'multiple_answers' 			=> true,
		'disallow_op_to_answer' 	=> false,
		'minimum_ans_length' 		=> 5,
		'avatar_size_qquestion' 	=> 50,
		'allow_private_post'		=> true,
		'avatar_size_qanswer' 		=> 50,
		'avatar_size_qcomment' 		=> 25,
		'avatar_size_list' 			=> 45,
		'question_per_page' 		=> '20',
		'answers_per_page' 			=> '5',
		'answers_sort' 				=> 'active',
		'close_selected' 			=> true,
		'moderate_new_question'		=> 'no_mod',
		'mod_question_point'		=> 10,
		'categories_per_page'		=> 20,
		'question_prefix'			=> 'question',
		'min_point_new_tag'			=> 100,
		'allow_anonymous'			=> false,
		'only_admin_can_answer'		=> false,
		'logged_in_can_see_ans'		=> false,
		'logged_in_can_see_comment'	=> false,
		'question_text_editor'		=> false,
		'answer_text_editor'		=> false,
		'base_page_title'			=> 'Questions',
		'ask_page_title'			=> 'Ask question',
		'search_page_title'			=> 'Search "%s"',
		'disable_comments_on_question' => false,
		'disable_comments_on_answer' => false,
		'new_question_status'		=> 'publish',
		'new_answer_status'			=> 'publish',
		'edit_question_status'		=> 'publish',
		'edit_answer_status'		=> 'publish',
		'disable_delete_after'		=> 86400,
		'db_cleanup'				=> false,
		'disable_voting_on_question' => false,
		'disable_voting_on_answer' 	=> false,
		'enable_recaptcha' 			=> false,
		'recaptcha_site_key' 		=> '',
		'recaptcha_secret_key' 		=> '',
		'disable_reputation' 		=> false,
		'users_page_avatar_size'	=> 60,
		'users_per_page' 			=> 39,
		'enable_users_directory'	=> true,
		'question_permalink_follow' => true,
		'show_question_sidebar' 	=> true,
		'allow_upload_image' 		=> true,
		'question_help_page' 		=> '',
		'answer_help_page' 			=> '',
		'disable_answer_nav' 		=> false,
		'image_per_post' 			=> 3,
		'base_before_user_perma' 	=> false,
		'user_page_slug' 			=> 'user',
		'ask_page_slug' 			=> 'ask',
		'question_page_slug' 		=> 'question',
		'users_page_slug' 			=> 'users',
		'users_page_title' 			=> __('Users', 'anspress-question-answer' ),
		'users_page_title' 			=> false,
		'max_upload_size' 			=> 500000,
		'disable_down_vote_on_question' => false,
		'disable_down_vote_on_answer' => false,
		'show_solved_prefix'		=> true,
		'notification_sidebar'		=> false,
		'user_profile'				=> 'anspress',
		'check_bad_words'			=> false,
		'bad_word_post_action'		=> 'moderate',
		'bad_word_comment_action'	=> 'moderate',
		'akismet_validation'		=> false,
		'load_assets_in_anspress_only'		=> false,
		'only_logged_in'			=> false,
		'keep_stop_words'			=> true,
		'default_date_format'		=> false,
		'cover_height'				=> 250,
		'cover_width'				=> 950,
		'cover_width_small'			=> 320,
		'cover_height_small'		=> 95,
		'anonymous_post_status'		=> 'moderate',
		'disable_mentions'			=> false,
		'disable_profile'			=> false,
	);

	/**
	 * FILTER: ap_default_options
	 * Filter to be used by extensions for including their default options.
	 * @var array
	 * @since 0.1
	 */
	$defaults = apply_filters('ap_default_options', $defaults );

	wp_cache_set( 'ap_default_options', $defaults, 'ap' );

	return $defaults;
}
