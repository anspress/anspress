<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestPostTypes extends TestCase {

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_PostTypes', 'init' ) );
		$this->assertTrue( method_exists( 'AnsPress_PostTypes', 'question_perm_structure' ) );
		$this->assertTrue( method_exists( 'AnsPress_PostTypes', 'register_question_cpt' ) );
		$this->assertTrue( method_exists( 'AnsPress_PostTypes', 'register_answer_cpt' ) );
		$this->assertTrue( method_exists( 'AnsPress_PostTypes', 'post_type_link' ) );
		$this->assertTrue( method_exists( 'AnsPress_PostTypes', 'post_type_archive_link' ) );
	}

	public function testInit() {
		$this->assertEquals( 0, has_action( 'init', [ 'AnsPress_PostTypes', 'register_question_cpt' ] ) );
		$this->assertEquals( 0, has_action( 'init', [ 'AnsPress_PostTypes', 'register_answer_cpt' ] ) );
		$this->assertEquals( 10, has_action( 'post_type_link', [ 'AnsPress_PostTypes', 'post_type_link' ] ) );
		$this->assertEquals( 10, has_filter( 'post_type_archive_link', [ 'AnsPress_PostTypes', 'post_type_archive_link' ] ) );
	}

	/**
	 * @covers AnsPress_PostTypes::register_question_cpt
	 */
	public function testQuestionPostType() {
		global $wp_post_types;
		$this->assertArrayHasKey( 'question', $wp_post_types );
	}

	/**
	 * @covers AnsPress_PostTypes::register_question_cpt
	 */
	public function testQuestionPostTypeLabels() {
		global $wp_post_types;
		$this->assertEquals( 'Questions', $wp_post_types['question']->labels->name );
		$this->assertEquals( 'Question', $wp_post_types['question']->labels->singular_name );
		$this->assertEquals( 'Questions', $wp_post_types['question']->labels->menu_name );
		$this->assertEquals( 'Parent question:', $wp_post_types['question']->labels->parent_item_colon );
		$this->assertEquals( 'All questions', $wp_post_types['question']->labels->all_items );
		$this->assertEquals( 'View question', $wp_post_types['question']->labels->view_item );
		$this->assertEquals( 'Add new question', $wp_post_types['question']->labels->add_new_item );
		$this->assertEquals( 'New question', $wp_post_types['question']->labels->add_new );
		$this->assertEquals( 'Edit question', $wp_post_types['question']->labels->edit_item );
		$this->assertEquals( 'Update question', $wp_post_types['question']->labels->update_item );
		$this->assertEquals( 'Search questions', $wp_post_types['question']->labels->search_items );
		$this->assertEquals( 'No question found', $wp_post_types['question']->labels->not_found );
		$this->assertEquals( 'No questions found in trash', $wp_post_types['question']->labels->not_found_in_trash );
		$this->assertEquals( 'Question', $wp_post_types['question']->description );
		$this->assertEquals( 0, $wp_post_types['question']->hierarchical );
		$this->assertEquals( 1, $wp_post_types['question']->public );
		$this->assertEquals( 1, $wp_post_types['question']->show_ui );
		$this->assertEquals( 0, $wp_post_types['question']->show_in_menu );
		$this->assertEquals( 0, $wp_post_types['question']->show_in_nav_menus );
		$this->assertEquals( 1, $wp_post_types['question']->show_in_admin_bar );
		$this->assertEquals( 1, $wp_post_types['question']->can_export );
		$this->assertEquals( 1, $wp_post_types['question']->has_archive );
		$this->assertEquals( 1, $wp_post_types['question']->exclude_from_search );
		$this->assertEquals( 1, $wp_post_types['question']->publicly_queryable );
		$this->assertEquals( 'post', $wp_post_types['question']->capability_type );
		$this->assertEquals( 0, $wp_post_types['question']->rewrite );
		$this->assertEquals( 'question', $wp_post_types['question']->query_var );
		$this->assertEquals( 1, $wp_post_types['question']->delete_with_user );
	}

	/**
	 * @covers AnsPress_PostTypes::register_answer_cpt
	 */
	public function testAnswerPostType() {
		global $wp_post_types;
		$this->assertArrayHasKey( 'answer', $wp_post_types );
	}

	/**
	 * @covers AnsPress_PostTypes::register_answer_cpt
	 */
	public function testAnswerPostTypeLabels() {
		global $wp_post_types;
		$this->assertEquals( 'Answers', $wp_post_types['answer']->labels->name );
		$this->assertEquals( 'Answer', $wp_post_types['answer']->labels->singular_name );
		$this->assertEquals( 'Answers', $wp_post_types['answer']->labels->menu_name );
		$this->assertEquals( 'Parent answer:', $wp_post_types['answer']->labels->parent_item_colon );
		$this->assertEquals( 'All answers', $wp_post_types['answer']->labels->all_items );
		$this->assertEquals( 'View answer', $wp_post_types['answer']->labels->view_item );
		$this->assertEquals( 'Add new answer', $wp_post_types['answer']->labels->add_new_item );
		$this->assertEquals( 'New answer', $wp_post_types['answer']->labels->add_new );
		$this->assertEquals( 'Edit answer', $wp_post_types['answer']->labels->edit_item );
		$this->assertEquals( 'Update answer', $wp_post_types['answer']->labels->update_item );
		$this->assertEquals( 'Search answers', $wp_post_types['answer']->labels->search_items );
		$this->assertEquals( 'No answer found', $wp_post_types['answer']->labels->not_found );
		$this->assertEquals( 'No answer found in trash', $wp_post_types['answer']->labels->not_found_in_trash );
		$this->assertEquals( 'Answer', $wp_post_types['answer']->description );
		$this->assertEquals( 0, $wp_post_types['answer']->hierarchical );
		$this->assertEquals( 1, $wp_post_types['answer']->public );
		$this->assertEquals( 1, $wp_post_types['answer']->show_ui );
		$this->assertEquals( 0, $wp_post_types['answer']->show_in_menu );
		$this->assertEquals( 0, $wp_post_types['answer']->show_in_nav_menus );
		$this->assertEquals( 0, $wp_post_types['answer']->show_in_admin_bar );
		$this->assertEquals( 1, $wp_post_types['answer']->can_export );
		$this->assertEquals( 1, $wp_post_types['answer']->has_archive );
		$this->assertEquals( 1, $wp_post_types['answer']->exclude_from_search );
		$this->assertEquals( 1, $wp_post_types['answer']->publicly_queryable );
		$this->assertEquals( 'post', $wp_post_types['answer']->capability_type );
		$this->assertEquals( 0, $wp_post_types['answer']->rewrite );
		$this->assertEquals( 'answer', $wp_post_types['answer']->query_var );
	}

	/**
	 * @covers AnsPress_PostTypes::post_type_archive_link
	 */
	public function testPostTypeArchiveLink() {
		// Remove filter so that we can test the method directly.
		remove_filter( 'post_type_archive_link', [ 'AnsPress_PostTypes', 'post_type_archive_link' ], 10, 2 );

		// Setup base page.
		$base_page = $this->factory()->post->create( [ 'post_type' => 'page' ] );
		ap_opt( 'base_page', $base_page );

		// Test for other post type.
		$post_type_archive_link = get_post_type_archive_link( 'post' );
		$result = \AnsPress_PostTypes::post_type_archive_link( $post_type_archive_link, 'post' );
		$this->assertEquals( $post_type_archive_link, $result );
		$page_type_archive_link = get_post_type_archive_link( 'page' );
		$result = \AnsPress_PostTypes::post_type_archive_link( $page_type_archive_link, 'page' );
		$this->assertEquals( $page_type_archive_link, $result );

		// For the question post type.
		$question_type_archive_link = get_post_type_archive_link( 'question' );
		$result = \AnsPress_PostTypes::post_type_archive_link( $question_type_archive_link, 'question' );
		$this->assertEquals( get_permalink( $base_page ), $result );
		$this->assertNotEquals( $question_type_archive_link, $result );

		// For the answer post type.
		$answer_type_archive_link = get_post_type_archive_link( 'answer' );
		$result = \AnsPress_PostTypes::post_type_archive_link( $answer_type_archive_link, 'answer' );
		$this->assertEquals( $answer_type_archive_link, $result );

		// Re-add the filter.
		add_filter( 'post_type_archive_link', [ 'AnsPress_PostTypes', 'post_type_archive_link' ], 10, 2 );
	}

	/**
	 * @covers AnsPress_PostTypes::question_perm_structure
	 */
	public function testQuestionPermStructure() {
		// Test with default values.
		// Test 1.
		ap_opt( 'question_page_permalink', 'question_perma_1' );
		$result = \AnsPress_PostTypes::question_perm_structure();
		$this->assertEquals( 'questions/question/%question%', $result->rule );

		// Test 2.
		ap_opt( 'question_page_permalink', 'question_perma_2' );
		$result = \AnsPress_PostTypes::question_perm_structure();
		$this->assertEquals( 'question/%question%', $result->rule );

		// Test 3.
		ap_opt( 'question_page_permalink', 'question_perma_3' );
		$result = \AnsPress_PostTypes::question_perm_structure();
		$this->assertEquals( 'question/%question_id%', $result->rule );

		// Test 4.
		ap_opt( 'question_page_permalink', 'question_perma_4' );
		$result = \AnsPress_PostTypes::question_perm_structure();
		$this->assertEquals( 'question/%question_id%/%question%', $result->rule );

		// Test 5.
		ap_opt( 'question_page_permalink', 'question_perma_5' );
		$result = \AnsPress_PostTypes::question_perm_structure();
		$this->assertEquals( 'question/%question%/%question_id%', $result->rule );

		// Test 6.
		ap_opt( 'question_page_permalink', 'question_perma_6' );
		$result = \AnsPress_PostTypes::question_perm_structure();
		$this->assertEquals( 'question/%question_id%-%question%', $result->rule );

		// Test 7.
		ap_opt( 'question_page_permalink', 'question_perma_7' );
		$result = \AnsPress_PostTypes::question_perm_structure();
		$this->assertEquals( 'question/%question%-%question_id%', $result->rule );

		// Test with base page change.
		$base_page = $this->factory()->post->create( [ 'post_title' => 'base', 'post_type' => 'page' ] );
		ap_opt( 'base_page', $base_page );

		// Test 1.
		ap_opt( 'question_page_permalink', 'question_perma_1' );
		$result = \AnsPress_PostTypes::question_perm_structure();
		$this->assertEquals( 'base/question/%question%', $result->rule );

		// Test 2.
		ap_opt( 'question_page_permalink', 'question_perma_2' );
		$result = \AnsPress_PostTypes::question_perm_structure();
		$this->assertEquals( 'question/%question%', $result->rule );

		// Test 3.
		ap_opt( 'question_page_permalink', 'question_perma_3' );
		$result = \AnsPress_PostTypes::question_perm_structure();
		$this->assertEquals( 'question/%question_id%', $result->rule );

		// Test 4.
		ap_opt( 'question_page_permalink', 'question_perma_4' );
		$result = \AnsPress_PostTypes::question_perm_structure();
		$this->assertEquals( 'question/%question_id%/%question%', $result->rule );

		// Test 5.
		ap_opt( 'question_page_permalink', 'question_perma_5' );
		$result = \AnsPress_PostTypes::question_perm_structure();
		$this->assertEquals( 'question/%question%/%question_id%', $result->rule );

		// Test 6.
		ap_opt( 'question_page_permalink', 'question_perma_6' );
		$result = \AnsPress_PostTypes::question_perm_structure();
		$this->assertEquals( 'question/%question_id%-%question%', $result->rule );

		// Test 7.
		ap_opt( 'question_page_permalink', 'question_perma_7' );
		$result = \AnsPress_PostTypes::question_perm_structure();
		$this->assertEquals( 'question/%question%-%question_id%', $result->rule );

		// Test with question slug change.
		ap_opt( 'question_page_slug', 'test' );

		// Test 1.
		ap_opt( 'question_page_permalink', 'question_perma_1' );
		$result = \AnsPress_PostTypes::question_perm_structure();
		$this->assertEquals( 'base/test/%question%', $result->rule );

		// Test 2.
		ap_opt( 'question_page_permalink', 'question_perma_2' );
		$result = \AnsPress_PostTypes::question_perm_structure();
		$this->assertEquals( 'test/%question%', $result->rule );

		// Test 3.
		ap_opt( 'question_page_permalink', 'question_perma_3' );
		$result = \AnsPress_PostTypes::question_perm_structure();
		$this->assertEquals( 'test/%question_id%', $result->rule );

		// Test 4.
		ap_opt( 'question_page_permalink', 'question_perma_4' );
		$result = \AnsPress_PostTypes::question_perm_structure();
		$this->assertEquals( 'test/%question_id%/%question%', $result->rule );

		// Test 5.
		ap_opt( 'question_page_permalink', 'question_perma_5' );
		$result = \AnsPress_PostTypes::question_perm_structure();
		$this->assertEquals( 'test/%question%/%question_id%', $result->rule );

		// Test 6.
		ap_opt( 'question_page_permalink', 'question_perma_6' );
		$result = \AnsPress_PostTypes::question_perm_structure();
		$this->assertEquals( 'test/%question_id%-%question%', $result->rule );

		// Test 7.
		ap_opt( 'question_page_permalink', 'question_perma_7' );
		$result = \AnsPress_PostTypes::question_perm_structure();
		$this->assertEquals( 'test/%question%-%question_id%', $result->rule );
	}
}
