<?php
/**
 * AnsPress options.
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-2.0+
 * @link      https://anspress.net
 * @copyright 2014 Rahul Aryan
 * @since     2.0.1
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * To retrieve AnsPress option.
 *
 * @param  string $key   Name of option to retrieve or keep it blank to get all options of AnsPress.
 * @param  string $value Enter value to update existing option.
 * @return string
 * @since  0.1
 */
function ap_opt( $key = false, $value = null ) {
	$settings = wp_cache_get( 'anspress_opt', 'ap' );

	if ( false === $settings ) {
		$settings = get_option( 'anspress_opt' );

		if ( ! $settings ) {
			$settings = array();
		}

		wp_cache_set( 'anspress_opt', $settings, 'ap' );
	}

	$settings = $settings + ap_default_options();

	if ( ! is_null( $value ) ) {

		$settings[ $key ] = $value;
		update_option( 'anspress_opt', $settings );

		// Clear cache if option updated.
		wp_cache_delete( 'anspress_opt', 'ap' );

		return;
	}

	if ( false === $key ) {
		return $settings;
	}

	if ( isset( $settings[ $key ] ) ) {
		return $settings[ $key ];
	}

	return null;
}

/**
 * Default options for AnsPress
 *
 * @return array
 * @since 2.0.1
 */
function ap_default_options() {
	$cache = wp_cache_get( 'ap_default_options', 'ap' );

	if ( false !== $cache ) {
		return $cache;
	}

	$defaults = array(
		'show_login_signup'             => true,
		'show_login'                    => true,
		'show_signup'                   => true,
		'theme'                         => 'default',
		'author_credits'                => false,
		'clear_database'                => false,
		'minimum_qtitle_length'         => 10,
		'minimum_question_length'       => 10,
		'multiple_answers'              => true,
		'disallow_op_to_answer'         => false,
		'minimum_ans_length'            => 5,
		'avatar_size_qquestion'         => 50,
		'allow_private_post'            => true,
		'avatar_size_qanswer'           => 50,
		'avatar_size_qcomment'          => 25,
		'avatar_size_list'              => 45,
		'question_per_page'             => '20',
		'answers_per_page'              => '5',
		'question_order_by'             => 'active',
		'answers_sort'                  => 'active',
		'close_selected'                => true,
		'moderate_new_question'         => 'no_mod',
		'mod_question_point'            => 10,
		'question_prefix'               => 'question',
		'question_text_editor'          => false,
		'answer_text_editor'            => false,
		'base_page_title'               => __( 'Questions', 'anspress-question-answer' ),
		'search_page_title'             => __( 'Search "%s"', 'anspress-question-answer' ),
		'user_page_title'               => __( '%s', 'anspress-question-answer' ),
		'disable_comments_on_question'  => false,
		'disable_comments_on_answer'    => false,
		'new_question_status'           => 'publish',
		'new_answer_status'             => 'publish',
		'edit_question_status'          => 'publish',
		'edit_answer_status'            => 'publish',
		'disable_delete_after'          => 86400,
		'db_cleanup'                    => false,
		'disable_voting_on_question'    => false,
		'disable_voting_on_answer'      => false,
		'enable_recaptcha'              => false,
		'recaptcha_site_key'            => '',
		'recaptcha_secret_key'          => '',
		'show_question_sidebar'         => true,
		'allow_upload'                  => true,
		'uploads_per_post'              => 4,
		'question_page_slug'            => 'question',
		'question_page_permalink'       => 'question_perma_1',
		'max_upload_size'               => 500000,
		'disable_down_vote_on_question' => false,
		'disable_down_vote_on_answer'   => false,
		'show_solved_prefix'            => true,
		'load_assets_in_anspress_only'  => false,
		'keep_stop_words'               => true,
		'default_date_format'           => false,
		'anonymous_post_status'         => 'moderate',
		'bad_words'                     => '',
		'duplicate_check'               => true,
		'disable_q_suggestion'          => false,
		'comment_number'                => 5,
		'read_question_per'             => 'anyone',
		'read_answer_per'               => 'anyone',
		'read_comment_per'              => 'anyone',
		'post_question_per'             => 'anyone',
		'post_answer_per'               => 'logged_in',
		'post_comment_per'              => 'logged_in',
		'activity_exclude_roles'        => [],
		'create_account'                => true,
		'allow_private_posts'           => true,
	);

	/**
	 * Filter to be used by extensions for including their default options.
	 *
	 * @param array $defaults Default options.
	 * @since 0.1
	 */
	$defaults = apply_filters( 'ap_default_options', $defaults );

	wp_cache_set( 'ap_default_options', $defaults, 'ap' );

	return $defaults;
}

/**
 * Add default AnsPress options.
 *
 * @param array $defaults Default options to append.
 * @since 4.0.0
 */
function ap_add_default_options( $defaults ) {
	$old_default = ap_default_options();

	// Delete existing cache.
	wp_cache_delete( 'ap_default_options', 'ap' );
	wp_cache_delete( 'anspress_opt', 'ap' );

	$new_default = $old_default + (array) $defaults;
	wp_cache_set( 'ap_default_options', $new_default, 'ap' );
}
