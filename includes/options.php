<?php
/**
 * AnsPress options
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://rahularyan.com
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
function ap_opt($key = false, $value = null){
	$settings = wp_cache_get('ap_opt', 'options');

	if($settings === false){
		$settings = get_option( 'anspress_opt');
		if(!$settings)
			$settings = array();
		$settings = $settings + ap_default_options();
		
		wp_cache_set('ap_opt', $settings, 'options');
	}	
	if(!is_null($value)){

		$settings[$key] = $value;		
		update_option( 'anspress_opt', $settings);

		// clear cache if option updated
		wp_cache_delete( 'ap_opt', 'options' );

		return;
	}

	if(false === $key)
		return $settings;
		
	if(isset($settings[$key]))
		return $settings[$key];
	else
		return NULL;
	
	return false;
}

/**
 * Default options for AnsPress
 * @return array
 * @since 2.0.1
 */
function ap_default_options(){
	$defaults =  array(
		'show_login_signup' 	=> true,
		'show_login' 			=> true,
		'show_signup' 			=> true,
		'show_title_in_question'=> false,
		'show_social_login'		=> false,
		'theme' 				=> 'default',
		'author_credits' 		=> false,
		'clear_database' 		=> false,
		'minimum_qtitle_length'	=> 10,
		'minimum_question_length'=> 10,
		'multiple_answers' 		=> false,
		'disallow_op_to_answer' => false,
		'minimum_ans_length' 	=> 5,
		'avatar_size_qquestion' => 50,
		'allow_private_post'	=> true,
		'avatar_size_qanswer' 	=> 50,
		'avatar_size_qcomment' 	=> 25,
		'avatar_size_list' 		=> 45,
		'question_per_page' 	=> '20',
		'answers_per_page' 		=> '5',
		'answers_sort' 			=> 'active',
		'close_selected' 		=> true,
		'moderate_new_question'	=> 'no_mod',
		'mod_question_point'	=> 10,
		'categories_per_page'	=> 20,
		'question_prefix'		=> 'question',
		'min_point_new_tag'		=> 100,
		'allow_anonymous'		=> false,
		'only_admin_can_answer'	=> false,
		'logged_in_can_see_ans'	=> false,
		'logged_in_can_see_comment'	=> false,
		'show_comments_by_default'	=> false,
		'question_text_editor'	=> false,
		'answer_text_editor'	=> false,
		'base_page_title'		=> 'Questions',
		'ask_page_title'		=> 'Ask question',
		'search_page_title'		=> 'Search "%s"',
		'disable_comments_on_question'=> false,
		'disable_comments_on_answer'=> false,
		'new_question_status'	=> 'publish',
		'new_answer_status'		=> 'publish',
		'edit_question_status'	=> 'publish',
		'edit_answer_status'	=> 'publish',
		'disable_delete_after'	=> 86400,
		'db_cleanup'			=> false,
		'disable_voting_on_question' => false,
		'disable_voting_on_answer' => false,
		'enable_recaptcha' 		=> false,
		'recaptcha_site_key' 	=> '',
		'recaptcha_secret_key' 	=> '',
		'disable_reputation' 	=> false,
		'users_page_avatar_size' 	=> 80,
		'users_per_page' 	=> 20,		
		'enable_users_directory' 	=> true,		
	);
	
	/**
	 * FILTER: ap_default_options
	 * Filter to be used by extensions for including their default options.
	 * @var array
	 * @since 0.1
	 */
	$defaults = apply_filters('ap_default_options', $defaults );

	return $defaults;
}
