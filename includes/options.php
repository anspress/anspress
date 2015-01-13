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
function ap_opt($key = false, $value = false){
	$settings = wp_cache_get('ap_opt', 'options');
	
	if($settings === false){
		$settings = get_option( 'anspress_opt');
		if(!$settings)
			$settings = array();
		$settings = $settings + ap_default_options();
		
		wp_cache_set('ap_opt', $settings, 'options');
	}	
	if($value){

		$settings[$key] = $value;		
		update_option( 'anspress_opt', $settings);

		// clear cache if option updated
		wp_cache_delete( 'ap_opt', 'options' );

		return;
	}

	if(!$key)
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
		'custom_signup_url'		=> '',
		'custom_login_url'		=> '',
		'show_login_signup' 	=> true,
		'show_login' 			=> true,
		'show_signup' 			=> true,
		'show_title_in_question'=> false,
		'show_social_login'		=> false,
		'theme' 				=> 'default',
		'author_credits' 		=> false,
		'clear_database' 		=> false,
		'minimum_qtitle_length'	=> 3,
		'minimum_question_length'=> 5,
		'multiple_answers' 		=> false,
		'minimum_ans_length' 	=> 5,
		'avatar_size_qquestion' => 50,
		'allow_private_post'	=> true,
		'avatar_size_qanswer' 	=> 30,
		'avatar_size_qcomment' 	=> 25,
		'down_vote_points' 		=> -1,
		'flag_note' 			=> array(0 => array('title' => 'it is spam', 'description' => 'This question is effectively an advertisement with no disclosure. It is not useful or relevant, but promotional.')),			
		'question_per_page' 	=> '20',
		'answers_per_page' 		=> '5',
		'answers_sort' 			=> 'active',
		'close_selected' 		=> true,
		'cover_width'			=> '878',
		'cover_height'			=> '200',
		'default_rank'			=> '0',
		'users_per_page'		=> 15,
		'cover_width_small'		=> 275,
		'cover_height_small'	=> 80,
		'followers_limit'		=> 10,
		'following_limit'		=> 10,
		'captcha_ask'			=> true,
		'captcha_answer'		=> true,
		'moderate_new_question'	=> 'no_mod',
		'mod_question_point'	=> 10,
		'categories_per_page'	=> 20,
		'question_prefix'		=> 'question',
		'min_point_new_tag'		=> 100,
		'allow_anonymous'		=> false,
		'enable_captcha_skip'	=> false,
		'captcha_skip_rpoints'	=> 40,
		'only_admin_can_answer'	=> false,
		'logged_in_can_see_ans'	=> false,
		'logged_in_can_see_comment'	=> false
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
