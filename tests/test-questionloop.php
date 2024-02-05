<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestQustionLoop extends TestCase {

	use Testcases\Common;

	/**
	 * @covers ::ap_questions_the_pagination
	 */
	public function testAPQuestionsThePagination() {
		// Test for front page.
		$page_id = $this->factory->post->create( [ 'post_type' => 'page' ] );
		update_option( 'page_on_front', $page_id );
		update_option( 'show_on_front', 'page' );

		// Test 1.
		$this->go_to( home_url() );
		set_query_var( 'page', 1 );
		anspress()->questions = (object) [ 'max_num_pages' => 3 ];
		ob_start();
		ap_questions_the_pagination();
		$pagination = ob_get_clean();
		$this->assertNotEmpty( $pagination );
		$this->assertStringContainsString( '<div class="ap-pagination clearfix">', $pagination );
		$this->assertStringContainsString( '<span aria-current="page" class="page-numbers current">1</span>', $pagination );
		$this->assertStringContainsString( 'class="next page-numbers" rel="next"', $pagination );
		$this->assertStringNotContainsString( 'class="prev page-numbers" rel="prev"', $pagination );

		// Test 2.
		$this->go_to( home_url() );
		set_query_var( 'page', 2 );
		anspress()->questions = (object) [ 'max_num_pages' => 3 ];
		ob_start();
		ap_questions_the_pagination();
		$pagination = ob_get_clean();
		$this->assertNotEmpty( $pagination );
		$this->assertStringContainsString( '<div class="ap-pagination clearfix">', $pagination );
		$this->assertStringContainsString( '<span aria-current="page" class="page-numbers current">2</span>', $pagination );
		$this->assertStringContainsString( 'class="next page-numbers" rel="next"', $pagination );
		$this->assertStringContainsString( 'class="prev page-numbers" rel="prev"', $pagination );

		// Test 3.
		$this->go_to( home_url() );
		set_query_var( 'page', 3 );
		anspress()->questions = (object) [ 'max_num_pages' => 3 ];
		ob_start();
		ap_questions_the_pagination();
		$pagination = ob_get_clean();
		$this->assertNotEmpty( $pagination );
		$this->assertStringContainsString( '<div class="ap-pagination clearfix">', $pagination );
		$this->assertStringContainsString( '<span aria-current="page" class="page-numbers current">3</span>', $pagination );
		$this->assertStringNotContainsString( 'class="next page-numbers" rel="next"', $pagination );
		$this->assertStringContainsString( 'class="prev page-numbers" rel="prev"', $pagination );

		// Reset the front page.
		update_option( 'page_on_front', 0 );
		update_option( 'show_on_front', 'posts' );

		// Test for other pages.
		$base_page_id = $this->factory->post->create( [ 'post_type' => 'page' ] );
		ap_opt( 'categories_page', $base_page_id );

		// Test on ap_paged query var.
		// Test 1.
		$this->go_to( '?post_type=page&p=' . $base_page_id );
		set_query_var( 'ap_paged', 1 );
		anspress()->questions = (object) [ 'max_num_pages' => 3 ];
		ob_start();
		ap_questions_the_pagination();
		$pagination = ob_get_clean();
		$this->assertNotEmpty( $pagination );
		$this->assertStringContainsString( '<div class="ap-pagination clearfix">', $pagination );
		$this->assertStringContainsString( '<span aria-current="page" class="page-numbers current">1</span>', $pagination );
		$this->assertStringContainsString( 'class="next page-numbers" rel="next"', $pagination );
		$this->assertStringNotContainsString( 'class="prev page-numbers" rel="prev"', $pagination );

		// Test 2.
		$this->go_to( '?post_type=page&p=' . $base_page_id );
		set_query_var( 'ap_paged', 2 );
		anspress()->questions = (object) [ 'max_num_pages' => 3 ];
		ob_start();
		ap_questions_the_pagination();
		$pagination = ob_get_clean();
		$this->assertNotEmpty( $pagination );
		$this->assertStringContainsString( '<div class="ap-pagination clearfix">', $pagination );
		$this->assertStringContainsString( '<span aria-current="page" class="page-numbers current">2</span>', $pagination );
		$this->assertStringContainsString( 'class="next page-numbers" rel="next"', $pagination );
		$this->assertStringContainsString( 'class="prev page-numbers" rel="prev"', $pagination );

		// Test 3.
		$this->go_to( '?post_type=page&p=' . $base_page_id );
		set_query_var( 'ap_paged', 3 );
		anspress()->questions = (object) [ 'max_num_pages' => 3 ];
		ob_start();
		ap_questions_the_pagination();
		$pagination = ob_get_clean();
		$this->assertNotEmpty( $pagination );
		$this->assertStringContainsString( '<div class="ap-pagination clearfix">', $pagination );
		$this->assertStringContainsString( '<span aria-current="page" class="page-numbers current">3</span>', $pagination );
		$this->assertStringNotContainsString( 'class="next page-numbers" rel="next"', $pagination );
		$this->assertStringContainsString( 'class="prev page-numbers" rel="prev"', $pagination );

		// Test on paged query var.
		// Test 1.
		$this->go_to( '?post_type=page&p=' . $base_page_id );
		set_query_var( 'paged', 1 );
		anspress()->questions = (object) [ 'max_num_pages' => 3 ];
		ob_start();
		ap_questions_the_pagination();
		$pagination = ob_get_clean();
		$this->assertNotEmpty( $pagination );
		$this->assertStringContainsString( '<div class="ap-pagination clearfix">', $pagination );
		$this->assertStringContainsString( '<span aria-current="page" class="page-numbers current">1</span>', $pagination );
		$this->assertStringContainsString( 'class="next page-numbers" rel="next"', $pagination );
		$this->assertStringNotContainsString( 'class="prev page-numbers" rel="prev"', $pagination );

		// Test 2.
		$this->go_to( '?post_type=page&p=' . $base_page_id );
		set_query_var( 'paged', 2 );
		anspress()->questions = (object) [ 'max_num_pages' => 3 ];
		ob_start();
		ap_questions_the_pagination();
		$pagination = ob_get_clean();
		$this->assertNotEmpty( $pagination );
		$this->assertStringContainsString( '<div class="ap-pagination clearfix">', $pagination );
		$this->assertStringContainsString( '<span aria-current="page" class="page-numbers current">2</span>', $pagination );
		$this->assertStringContainsString( 'class="next page-numbers" rel="next"', $pagination );
		$this->assertStringContainsString( 'class="prev page-numbers" rel="prev"', $pagination );

		// Test 3.
		$this->go_to( '?post_type=page&p=' . $base_page_id );
		set_query_var( 'paged', 3 );
		anspress()->questions = (object) [ 'max_num_pages' => 3 ];
		ob_start();
		ap_questions_the_pagination();
		$pagination = ob_get_clean();
		$this->assertNotEmpty( $pagination );
		$this->assertStringContainsString( '<div class="ap-pagination clearfix">', $pagination );
		$this->assertStringContainsString( '<span aria-current="page" class="page-numbers current">3</span>', $pagination );
		$this->assertStringNotContainsString( 'class="next page-numbers" rel="next"', $pagination );
		$this->assertStringContainsString( 'class="prev page-numbers" rel="prev"', $pagination );
	}

	/**
	 * @covers ::ap_get_question
	 */
	public function testAPGetQuestion() {
		// Test for publish question.
		$question_id = $this->factory->post->create( [ 'post_type' => 'question', 'post_status' => 'publish' ] );

		// Test 1.
		$question = ap_get_question( $question_id );
		$this->assertInstanceOf( 'Question_Query', $question );
		$this->assertNotNull( $question->post );
		$this->assertEquals( $question_id, $question->post->ID );

		// Test for future question.
		$future_question_id = $this->factory->post->create( [ 'post_type' => 'question', 'post_status' => 'future', 'post_date' => '9999-12-31 23:59:59', ] );

		// Test 1.
		$question = ap_get_question( $future_question_id );
		$this->assertInstanceOf( 'Question_Query', $question );
		$this->assertNull( $question->post );

		// Test 2.
		$this->setRole( 'subscriber' );
		$question = ap_get_question( $future_question_id );
		$this->assertInstanceOf( 'Question_Query', $question );
		$this->assertNull( $question->post );

		// Test 3.
		$this->setRole( 'administrator' );
		$question = ap_get_question( $future_question_id );
		$this->assertInstanceOf( 'Question_Query', $question );
		$this->assertNotNull( $question->post );
		$this->assertEquals( $future_question_id, $question->post->ID );

		// Test for private question.
		$this->logout();
		$private_question_id = $this->factory->post->create( [ 'post_type' => 'question', 'post_status' => 'private_post' ] );

		// Test 1.
		$question = ap_get_question( $private_question_id );
		$this->assertInstanceOf( 'Question_Query', $question );
		$this->assertNull( $question->post );

		// Test 2.
		$this->setRole( 'subscriber' );
		$question = ap_get_question( $private_question_id );
		$this->assertInstanceOf( 'Question_Query', $question );
		$this->assertNull( $question->post );

		// Test 3.
		$this->setRole( 'administrator' );
		$question = ap_get_question( $private_question_id );
		$this->assertInstanceOf( 'Question_Query', $question );
		$this->assertNotNull( $question->post );

		// Test 4.
		$this->setRole( 'subscriber' );
		$question_id = $this->factory->post->create( [ 'post_type' => 'question', 'post_status' => 'private_post' ] );
		$question = ap_get_question( $question_id );
		$this->assertInstanceOf( 'Question_Query', $question );
		$this->assertNotNull( $question->post );
		$this->assertEquals( $question_id, $question->post->ID );

		// Test for moderate question.
		$this->logout();
		$moderate_question_id = $this->factory->post->create( [ 'post_type' => 'question', 'post_status' => 'moderate' ] );

		// Test 1.
		$question = ap_get_question( $moderate_question_id );
		$this->assertInstanceOf( 'Question_Query', $question );
		$this->assertNull( $question->post );

		// Test 2.
		$this->setRole( 'subscriber' );
		$question = ap_get_question( $moderate_question_id );
		$this->assertInstanceOf( 'Question_Query', $question );
		$this->assertNull( $question->post );

		// Test 3.
		$this->setRole( 'administrator' );
		$question = ap_get_question( $moderate_question_id );
		$this->assertInstanceOf( 'Question_Query', $question );
		$this->assertNotNull( $question->post );

		// Test 4.
		$this->setRole( 'subscriber' );
		$question_id = $this->factory->post->create( [ 'post_type' => 'question', 'post_status' => 'moderate' ] );
		$question = ap_get_question( $question_id );
		$this->assertInstanceOf( 'Question_Query', $question );
		$this->assertNotNull( $question->post );
		$this->assertEquals( $question_id, $question->post->ID );
		$this->logout();
	}

	/**
	 * @covers ::ap_get_questions
	 */
	public function testAPGetQuestions() {
		// Test on other pages.
		// Test 1.
		$questions = ap_get_questions();
		$this->assertInstanceOf( 'Question_Query', $questions );
		$this->assertEquals( ap_opt( 'question_per_page' ), $questions->query_vars['showposts'] );
		$this->assertEquals( 1, $questions->query_vars['paged'] );
		$this->assertEquals( 'featured_post', $questions->query_vars['ap_query'] );
		$this->assertEmpty( $questions->query_vars['post_parent'] );

		// Test 2.
		$question_id = $this->factory->post->create( [ 'post_type' => 'question', 'post_status' => 'publish' ] );
		$this->go_to( '?post_type=question&p=' . $question_id );
		set_query_var( 'paged', 3 );
		$questions = ap_get_questions();
		$this->assertInstanceOf( 'Question_Query', $questions );
		$this->assertEquals( ap_opt( 'question_per_page' ), $questions->query_vars['showposts'] );
		$this->assertEquals( 3, $questions->query_vars['paged'] );
		$this->assertEquals( 'featured_post', $questions->query_vars['ap_query'] );
		$this->assertEmpty( $questions->query_vars['post_parent'] );

		// Test 3.
		$args = [
			'showposts'   => 11,
			'paged'       => 3,
			'ap_query'    => 'single_question',
			'post_parent' => 11,
			'ap_order_by' => 'newest',
		];
		$questions = ap_get_questions( $args );
		$this->assertInstanceOf( 'Question_Query', $questions );
		$this->assertEquals( 11, $questions->query_vars['showposts'] );
		$this->assertEquals( 3, $questions->query_vars['paged'] );
		$this->assertEquals( 'single_question', $questions->query_vars['ap_query'] );
		$this->assertEquals( 11, $questions->query_vars['post_parent'] );
		$this->assertEquals( 'newest', $questions->query_vars['ap_order_by'] );

		// Test 4.
		$this->go_to( '?post_type=question&p=' . $question_id );
		$answer_id = $this->factory->post->create( [ 'post_type' => 'answer', 'post_status' => 'publish', 'post_parent' => $question_id ] );
		ap_opt( 'question_per_page', 3 );
		set_query_var( 'paged', 5 );
		$args = [
			'ap_query'    => 'single_question',
			'post_parent' => $question_id,
			'ap_order_by' => 'oldest',
			'p'           => $answer_id,
		];
		$questions = ap_get_questions( $args );
		$this->assertInstanceOf( 'Question_Query', $questions );
		$this->assertEquals( 3, $questions->query_vars['showposts'] );
		$this->assertEquals( 5, $questions->query_vars['paged'] );
		$this->assertEquals( 'single_question', $questions->query_vars['ap_query'] );
		$this->assertEquals( $question_id, $questions->query_vars['post_parent'] );
		$this->assertEquals( 'oldest', $questions->query_vars['ap_order_by'] );
		$this->assertEquals( $answer_id, $questions->query_vars['p'] );

		// Test for front page.
		$page_id = $this->factory->post->create( [ 'post_type' => 'page' ] );
		update_option( 'page_on_front', $page_id );
		update_option( 'show_on_front', 'page' );
		$question_id = $this->factory->post->create( [ 'post_type' => 'question', 'post_status' => 'publish' ] );

		// Test 1.
		$this->go_to( home_url() );
		$questions = ap_get_questions();
		$this->assertInstanceOf( 'Question_Query', $questions );
		$this->assertEquals( ap_opt( 'question_per_page' ), $questions->query_vars['showposts'] );
		$this->assertEquals( 1, $questions->query_vars['paged'] );
		$this->assertEquals( 'featured_post', $questions->query_vars['ap_query'] );
		$this->assertEmpty( $questions->query_vars['post_parent'] );

		// Test 2.
		$this->go_to( home_url() );
		ap_opt( 'question_per_page', 5 );
		$_REQUEST['ap_paged'] = 3;
		$questions = ap_get_questions();
		$this->assertInstanceOf( 'Question_Query', $questions );
		$this->assertEquals( 5, $questions->query_vars['showposts'] );
		$this->assertEquals( 3, $questions->query_vars['paged'] );
		$this->assertEquals( 'featured_post', $questions->query_vars['ap_query'] );
		$this->assertEmpty( $questions->query_vars['post_parent'] );

		// Test 3.
		$this->go_to( home_url() );
		unset( $_REQUEST['ap_paged'] );
		ap_opt( 'question_per_page', 5 );
		$args = [
			'ap_query'    => 'single_question',
			'post_parent' => $question_id,
			'ap_order_by' => 'newest',
		];
		$questions = ap_get_questions( $args );
		$this->assertInstanceOf( 'Question_Query', $questions );
		$this->assertEquals( 5, $questions->query_vars['showposts'] );
		$this->assertEquals( 1, $questions->query_vars['paged'] );
		$this->assertEquals( 'single_question', $questions->query_vars['ap_query'] );
		$this->assertEquals( $question_id, $questions->query_vars['post_parent'] );
		$this->assertEquals( 'newest', $questions->query_vars['ap_order_by'] );

		// Test 4.
		$this->go_to( home_url() );
		$answer_id = $this->factory->post->create( [ 'post_type' => 'answer', 'post_status' => 'publish', 'post_parent' => $question_id ] );
		$_REQUEST['ap_paged'] = 3;
		$args = [
			'ap_query'    => 'single_question',
			'post_parent' => $question_id,
			'p'           => $answer_id,
		];
		$questions = ap_get_questions( $args );
		$this->assertInstanceOf( 'Question_Query', $questions );
		$this->assertEquals( ap_opt( 'question_per_page' ), $questions->query_vars['showposts'] );
		$this->assertEquals( 3, $questions->query_vars['paged'] );
		$this->assertEquals( 'single_question', $questions->query_vars['ap_query'] );
		$this->assertEquals( $question_id, $questions->query_vars['post_parent'] );
		$this->assertEquals( $answer_id, $questions->query_vars['p'] );

		// Reset the front page.
		update_option( 'page_on_front', 0 );
		update_option( 'show_on_front', 'posts' );

		// Reset the question_per_page option.
		ap_opt( 'question_per_page', 20 );
	}
}
