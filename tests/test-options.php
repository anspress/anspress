<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestOptions extends TestCase {

	/**
	 * @covers ::ap_opt
	 */
	public function testAPOpt() {
		// Test for getting the option value.
		$this->assertEquals( false, ap_opt( 'author_credits' ) );

		// Test for setting the option value and after that,
		// getting it's respective value.
		ap_opt( 'author_credits', true );
		$this->assertEquals( true, ap_opt( 'author_credits' ) );

		// Test for adding new option.
		ap_opt( 'questionblahblahblah', 'question' );
		$this->assertEquals( 'question', ap_opt( 'questionblahblahblah' ) );
		ap_opt( 'answerblahblahblah', 'answer' );
		$this->assertEquals( 'answer', ap_opt( 'answerblahblahblah' ) );

		// Test for getting all of the option values.
		$options_arr = array(
			'show_login_signup',
			'show_login',
			'show_signup',
			'theme',
			'author_credits',
			'clear_database',
			'minimum_qtitle_length',
			'minimum_question_length',
			'multiple_answers',
			'disallow_op_to_answer',
			'minimum_ans_length',
			'avatar_size_qquestion',
			'allow_private_post',
			'avatar_size_qanswer',
			'avatar_size_qcomment',
			'avatar_size_list',
			'question_per_page',
			'answers_per_page',
			'question_order_by',
			'answers_sort',
			'close_selected',
			'moderate_new_question',
			'mod_question_point',
			'question_prefix',
			'question_text_editor',
			'answer_text_editor',
			'base_page_title',
			'search_page_title',
			'user_page_title',
			'disable_comments_on_question',
			'disable_comments_on_answer',
			'new_question_status',
			'new_answer_status',
			'edit_question_status',
			'edit_answer_status',
			'disable_delete_after',
			'db_cleanup',
			'disable_voting_on_question',
			'disable_voting_on_answer',
			'enable_recaptcha',
			'recaptcha_site_key',
			'recaptcha_secret_key',
			'show_question_sidebar',
			'allow_upload',
			'uploads_per_post',
			'question_page_slug',
			'question_page_permalink',
			'max_upload_size',
			'disable_down_vote_on_question',
			'disable_down_vote_on_answer',
			'show_solved_prefix',
			'load_assets_in_anspress_only',
			'keep_stop_words',
			'default_date_format',
			'anonymous_post_status',
			'bad_words',
			'duplicate_check',
			'disable_q_suggestion',
			'comment_number',
			'read_question_per',
			'read_answer_per',
			'read_comment_per',
			'post_question_per',
			'post_answer_per',
			'post_comment_per',
			'activity_exclude_roles',
			'create_account',
			'allow_private_posts',
		);
		foreach ( $options_arr as $option_id ) {
			$this->assertArrayHasKey( $option_id, ap_opt() );
		}
	}

	/**
	 * @covers ::ap_default_options
	 */
	public function testAPDefaultOptions() {
		$default_options = ap_default_options();
		$this->assertEquals( true, $default_options['show_login_signup'] );
		$this->assertEquals( true, $default_options['show_login'] );
		$this->assertEquals( true, $default_options['show_signup'] );
		$this->assertEquals( 'default', $default_options['theme'] );
		$this->assertEquals( false, $default_options['author_credits'] );
		$this->assertEquals( false, $default_options['clear_database'] );
		$this->assertEquals( 10, $default_options['minimum_qtitle_length'] );
		$this->assertEquals( 10, $default_options['minimum_question_length'] );
		$this->assertEquals( true, $default_options['multiple_answers'] );
		$this->assertEquals( false, $default_options['disallow_op_to_answer'] );
		$this->assertEquals( 5, $default_options['minimum_ans_length'] );
		$this->assertEquals( 50, $default_options['avatar_size_qquestion'] );
		$this->assertEquals( true, $default_options['allow_private_post'] );
		$this->assertEquals( 50, $default_options['avatar_size_qanswer'] );
		$this->assertEquals( 25, $default_options['avatar_size_qcomment'] );
		$this->assertEquals( 45, $default_options['avatar_size_list'] );
		$this->assertEquals( '20', $default_options['question_per_page'] );
		$this->assertEquals( '5', $default_options['answers_per_page'] );
		$this->assertEquals( 'active', $default_options['question_order_by'] );
		$this->assertEquals( 'active', $default_options['answers_sort'] );
		$this->assertEquals( true, $default_options['close_selected'] );
		$this->assertEquals( 'no_mod', $default_options['moderate_new_question'] );
		$this->assertEquals( 10, $default_options['mod_question_point'] );
		$this->assertEquals( 'question', $default_options['question_prefix'] );
		$this->assertEquals( false, $default_options['question_text_editor'] );
		$this->assertEquals( false, $default_options['answer_text_editor'] );
		$this->assertEquals( 'Questions', $default_options['base_page_title'] );
		$this->assertEquals( 'Search "%s"', $default_options['search_page_title'] );
		$this->assertEquals( '%s', $default_options['user_page_title'] );
		$this->assertEquals( false, $default_options['disable_comments_on_question'] );
		$this->assertEquals( false, $default_options['disable_comments_on_answer'] );
		$this->assertEquals( 'publish', $default_options['new_question_status'] );
		$this->assertEquals( 'publish', $default_options['new_answer_status'] );
		$this->assertEquals( 'publish', $default_options['edit_question_status'] );
		$this->assertEquals( 'publish', $default_options['edit_answer_status'] );
		$this->assertEquals( 86400, $default_options['disable_delete_after'] );
		$this->assertEquals( false, $default_options['db_cleanup'] );
		$this->assertEquals( false, $default_options['disable_voting_on_question'] );
		$this->assertEquals( false, $default_options['disable_voting_on_answer'] );
		$this->assertEquals( false, $default_options['enable_recaptcha'] );
		$this->assertEquals( '', $default_options['recaptcha_site_key'] );
		$this->assertEquals( '', $default_options['recaptcha_secret_key'] );
		$this->assertEquals( true, $default_options['show_question_sidebar'] );
		$this->assertEquals( true, $default_options['allow_upload'] );
		$this->assertEquals( 4, $default_options['uploads_per_post'] );
		$this->assertEquals( 'question', $default_options['question_page_slug'] );
		$this->assertEquals( 'question_perma_1', $default_options['question_page_permalink'] );
		$this->assertEquals( 500000, $default_options['max_upload_size'] );
		$this->assertEquals( false, $default_options['disable_down_vote_on_question'] );
		$this->assertEquals( false, $default_options['disable_down_vote_on_answer'] );
		$this->assertEquals( true, $default_options['show_solved_prefix'] );
		$this->assertEquals( false, $default_options['load_assets_in_anspress_only'] );
		$this->assertEquals( true, $default_options['keep_stop_words'] );
		$this->assertEquals( false, $default_options['default_date_format'] );
		$this->assertEquals( 'moderate', $default_options['anonymous_post_status'] );
		$this->assertEquals( '', $default_options['bad_words'] );
		$this->assertEquals( true, $default_options['duplicate_check'] );
		$this->assertEquals( false, $default_options['disable_q_suggestion'] );
		$this->assertEquals( 5, $default_options['comment_number'] );
		$this->assertEquals( 'anyone', $default_options['read_question_per'] );
		$this->assertEquals( 'anyone', $default_options['read_answer_per'] );
		$this->assertEquals( 'anyone', $default_options['read_comment_per'] );
		$this->assertEquals( 'anyone', $default_options['post_question_per'] );
		$this->assertEquals( 'logged_in', $default_options['post_answer_per'] );
		$this->assertEquals( 'logged_in', $default_options['post_comment_per'] );
		$this->assertEquals( array(), $default_options['activity_exclude_roles'] );
		$this->assertEquals( true, $default_options['create_account'] );
		$this->assertEquals( true, $default_options['allow_private_posts'] );
	}

	/**
	 * @covers ::ap_add_default_options
	 */
	public function testAPAddDefaultOptions() {
		$default = [
			'question_id' => 100,
			'answer_id'   => 110,
			'comment_id'  => 120,
		];
		ap_add_default_options( $default );
		$default_cache = wp_cache_get( 'ap_default_options', 'ap' );
		$this->assertArrayHasKey( 'question_id', $default_cache );
		$this->assertArrayHasKey( 'answer_id', $default_cache );
		$this->assertArrayHasKey( 'comment_id', $default_cache );
		$this->assertEquals( 100, $default_cache['question_id'] );
		$this->assertEquals( 110, $default_cache['answer_id'] );
		$this->assertEquals( 120, $default_cache['comment_id'] );
	}
}
