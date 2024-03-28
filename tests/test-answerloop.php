<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnswerLoop extends TestCase {

	use TestCases\Common;

	public function testClassProperties() {
		$class = new \ReflectionClass( 'Answers_Query' );
		$this->assertTrue( $class->hasProperty( 'args' ) && $class->getProperty( 'args' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'Answers_Query', '__construct' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'get_answers' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'next_answer' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'reset_next' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'the_answer' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'have_answers' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'rewind_answers' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'is_main_query' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'reset_answers_data' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'get_ids' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'pre_fetch' ) );
	}

	/**
	 * @covers ::ap_answer_status
	 */
	public function testAPAnswerStatus() {
		// Test on publish post status.
		$id = $this->insert_answer();
		$this->assertNull( ap_answer_status( $id->a ) );

		// Test on other post statuses.
		$q_id = $this->insert_question();

		// Moderate post status.
		$a1_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'moderate',
				'post_parent'  => $q_id,
			)
		);
		ob_start();
		ap_answer_status( $a1_id );
		$moderate_post_status = ob_get_clean();
		$this->assertEquals( '<span class="ap-post-status moderate">Moderate</span>', $moderate_post_status );

		// Private post post status.
		$a2_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'private_post',
				'post_parent'  => $q_id,
			)
		);
		ob_start();
		ap_answer_status( $a2_id );
		$private_post_status = ob_get_clean();
		$this->assertEquals( '<span class="ap-post-status private_post">Private</span>', $private_post_status );

		// Future post status.
		$a3_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'future',
				'post_parent'  => $q_id,
				'post_date'    => '9999-12-31 23:59:59',
			)
		);
		ob_start();
		ap_answer_status( $a3_id );
		$future_status = ob_get_clean();
		$this->assertEquals( '<span class="ap-post-status future">Scheduled</span>', $future_status );

		// Draft post status.
		$a4_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'draft',
				'post_parent'  => $q_id,
			)
		);
		ob_start();
		ap_answer_status( $a4_id );
		$draft_status = ob_get_clean();
		$this->assertEquals( '<span class="ap-post-status draft">Draft</span>', $draft_status );

		// Pending review post status.
		$a5_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'pending',
				'post_parent'  => $q_id,
			)
		);
		ob_start();
		ap_answer_status( $a5_id );
		$pending_status = ob_get_clean();
		$this->assertEquals( '<span class="ap-post-status pending">Pending</span>', $pending_status );
	}

	/**
	 * @covers ::ap_count_published_answers
	 */
	public function testAPCountPublishedAnswers() {
		// Test for empty answers.
		$id = $this->insert_question();
		$this->assertEquals( 0, ap_count_published_answers( $id ) );

		// Test for only 1 answer published.
		$id = $this->insert_answer();
		$this->assertEquals( 1, ap_count_published_answers( $id->q ) );

		// Test for many answers published.
		$id = $this->insert_answers( [], [], 5 );
		$this->assertEquals( 5, ap_count_published_answers( $id['question'] ) );

		// Test for additional answers published.
		$a1_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $id['question'],
			)
		);
		$this->assertEquals( 6, ap_count_published_answers( $id['question'] ) );
		$a2_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $id['question'],
			)
		);
		$this->assertEquals( 7, ap_count_published_answers( $id['question'] ) );

		// Test on all post status.
		$q_id = $this->insert_question();
		$this->assertEquals( 0, ap_count_published_answers( $q_id ) );
		$a1_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
			)
		);
		$this->assertEquals( 1, ap_count_published_answers( $q_id ) );
		$a2_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
				'post_status'  => 'private_post',
			)
		);
		$this->assertEquals( 1, ap_count_published_answers( $q_id ) );
		$a3_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
				'post_status'  => 'trash',
			)
		);
		$this->assertEquals( 1, ap_count_published_answers( $q_id ) );
		$a4_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
				'post_status'  => 'draft',
			)
		);
		$this->assertEquals( 1, ap_count_published_answers( $q_id ) );
		$a5_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
				'post_status'  => 'publish',
			)
		);
		$this->assertEquals( 2, ap_count_published_answers( $q_id ) );
		$a6_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
				'post_status'  => 'moderate',
			)
		);
		$this->assertEquals( 2, ap_count_published_answers( $q_id ) );
		$a7_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
				'post_status'  => 'pending',
			)
		);
		$this->assertEquals( 2, ap_count_published_answers( $q_id ) );
		$a8_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
				'post_status'  => 'future',
				'post_date'    => '9999-12-31 23:59:59',
			)
		);
		$this->assertEquals( 2, ap_count_published_answers( $q_id ) );
		$a9_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
				'post_status'  => 'publish',
			)
		);
		$this->assertEquals( 3, ap_count_published_answers( $q_id ) );
		$a10_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
			)
		);
		$this->assertEquals( 4, ap_count_published_answers( $q_id ) );
	}

	/**
	 * @covers ::ap_count_other_answer
	 */
	public function testAPCountOtherAnswer() {
		// Test for empty answers.
		$id = $this->insert_question();
		$this->assertEquals( 0, ap_count_other_answer( $id ) );

		// Test for only 1 answer on the question.
		$id = $this->insert_answer();
		$this->assertEquals( 1, ap_count_other_answer( $id->q ) );

		// Test for many answers.
		$id = $this->insert_answers( [], [], 8 );
		$this->assertEquals( 8, ap_count_other_answer( $id['question'] ) );

		// Test for additional answers.
		$a1_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $id['question'],
			)
		);
		$this->assertEquals( 9, ap_count_other_answer( $id['question'] ) );
		$a2_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $id['question'],
			)
		);
		$this->assertEquals( 10, ap_count_other_answer( $id['question'] ) );

		// Test on all post status.
		$q_id = $this->insert_question();
		$this->assertEquals( 0, ap_count_other_answer( $q_id ) );
		$a1_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
			)
		);
		$this->assertEquals( 1, ap_count_other_answer( $q_id ) );
		$a2_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
				'post_status'  => 'private_post',
			)
		);
		$this->assertEquals( 1, ap_count_other_answer( $q_id ) );
		$a3_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
				'post_status'  => 'moderate',
			)
		);
		$this->assertEquals( 1, ap_count_other_answer( $q_id ) );
		$a4_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
				'post_status'  => 'publish',
			)
		);
		$this->assertEquals( 2, ap_count_other_answer( $q_id ) );

		// Test after the question have a selected answer.
		$id = $this->insert_answer();
		$this->assertEquals( 1, ap_count_other_answer( $id->q ) );
		$a1_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $id->q,
			)
		);
		$a2_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $id->q,
			)
		);
		$this->assertEquals( 3, ap_count_other_answer( $id->q ) );
		ap_set_selected_answer( $id->q, $a1_id );
		$this->assertNotEquals( 3, ap_count_other_answer( $id->q ) );
		$this->assertEquals( 2, ap_count_other_answer( $q_id ) );
	}

	/**
	 * @covers ::ap_answers_the_pagination
	 */
	public function testAPAnswersThePagination() {
		$id = $this->insert_answers( [], [], 11 );

		// Test 1.
		$this->go_to( '?post_type=question&p=' . $id['question'] );
		set_query_var( 'answer_id', $id['answers'][0] );
		ob_start();
		ap_answers_the_pagination();
		$pagination = ob_get_clean();
		$this->assertNotEmpty( $pagination );
		$this->assertStringContainsString( 'ap-all-answers', $pagination );
		$this->assertStringContainsString( 'You are viewing 1 out of 11 answers, click here to view all answers.', $pagination );
		$this->assertStringContainsString( esc_url( get_permalink( $id['question'] ) ), $pagination );
		$this->assertEquals( '<a class="ap-all-answers" href="' . esc_url( get_permalink( $id['question'] ) ) . '">You are viewing 1 out of 11 answers, click here to view all answers.</a>', $pagination );

		// Test 2.
		$this->go_to( '?post_type=question&p=' . $id['question'] );
		set_query_var( 'answer_id', $id['answers'][5] );
		ob_start();
		ap_answers_the_pagination();
		$pagination = ob_get_clean();
		$this->assertNotEmpty( $pagination );
		$this->assertStringContainsString( 'ap-all-answers', $pagination );
		$this->assertStringContainsString( 'You are viewing 1 out of 11 answers, click here to view all answers.', $pagination );
		$this->assertStringContainsString( esc_url( get_permalink( $id['question'] ) ), $pagination );
		$this->assertEquals( '<a class="ap-all-answers" href="' . esc_url( get_permalink( $id['question'] ) ) . '">You are viewing 1 out of 11 answers, click here to view all answers.</a>', $pagination );

		// Test 3.
		$this->go_to( '?post_type=question&p=' . $id['question'] );
		global $answers;
		$answers = (object) [ 'max_num_pages' => 3 ];
		ob_start();
		ap_answers_the_pagination();
		$pagination = ob_get_clean();
		$this->assertNotEmpty( $pagination );
		$this->assertStringContainsString( '<div class="ap-pagination clearfix">', $pagination );
		$this->assertStringContainsString( '<span aria-current="page" class="page-numbers current">1</span>', $pagination );
		$this->assertStringContainsString( 'class="next page-numbers" rel="next"', $pagination );
		$this->assertStringNotContainsString( 'class="prev page-numbers" rel="prev"', $pagination );
		$this->assertStringNotContainsString( 'You are viewing 1 out of 11 answers, click here to view all answers.', $pagination );
		$answers = null;

		// Test 4.
		$this->go_to( '?post_type=question&p=' . $id['question'] );
		set_query_var( 'ap_paged', 2 );
		global $answers;
		$answers = (object) [ 'max_num_pages' => 3 ];
		ob_start();
		ap_answers_the_pagination();
		$pagination = ob_get_clean();
		$this->assertNotEmpty( $pagination );
		$this->assertStringContainsString( '<div class="ap-pagination clearfix">', $pagination );
		$this->assertStringContainsString( '<span aria-current="page" class="page-numbers current">2</span>', $pagination );
		$this->assertStringContainsString( 'class="next page-numbers" rel="next"', $pagination );
		$this->assertStringContainsString( 'class="prev page-numbers" rel="prev"', $pagination );
		$this->assertStringNotContainsString( 'You are viewing 1 out of 11 answers, click here to view all answers.', $pagination );
		$answers = null;

		// Test 5.
		$this->go_to( '?post_type=question&p=' . $id['question'] );
		set_query_var( 'ap_paged', 3 );
		global $answers;
		$answers = (object) [ 'max_num_pages' => 3 ];
		ob_start();
		ap_answers_the_pagination();
		$pagination = ob_get_clean();
		$this->assertNotEmpty( $pagination );
		$this->assertStringContainsString( '<div class="ap-pagination clearfix">', $pagination );
		$this->assertStringContainsString( '<span aria-current="page" class="page-numbers current">3</span>', $pagination );
		$this->assertStringNotContainsString( 'class="next page-numbers" rel="next"', $pagination );
		$this->assertStringContainsString( 'class="prev page-numbers" rel="prev"', $pagination );
		$this->assertStringNotContainsString( 'You are viewing 1 out of 11 answers, click here to view all answers.', $pagination );
		$answers = null;
	}

	/**
	 * @covers ::ap_answer_the_object
	 */
	public function testAPAnswerTheObject() {
		global $answers;

		// Test 1.
		$result = ap_answer_the_object();
		$this->assertNull( $result );

		// Test 2.
		$mock_answer = (object) [
			'ID'           => 5,
			'post_title'   => 'Answer title',
			'post_content' => 'Answer content',
			'post_type'    => 'answer',
		];
		$answers = (object) [ 'post' => $mock_answer ];
		$result = ap_answer_the_object();
		$this->assertSame( $mock_answer, $result );

		// Test 3.
		$mock_answer = (object) [
			'ID' => 11,
		];
		$answers = (object) [ 'post' => $mock_answer ];
		$result = ap_answer_the_object();
		$this->assertSame( $mock_answer, $result );

		// Reset global $answers.
		$answers = null;
	}

	/**
	 * @covers ::ap_total_answers_found
	 */
	public function testAPTotalAnswersFound() {
		global $answers;

		// Test 1.
		$answers = (object) [ 'found_posts' => 0 ];
		$result = ap_total_answers_found();
		$this->assertEquals( 0, $result );

		// Test 2.
		$answers = (object) [ 'found_posts' => 11 ];
		$result = ap_total_answers_found();
		$this->assertEquals( 11, $result );

		// Test 3.
		$ids = $this->insert_answers( [], [], 0 );
		$answers = new \WP_Query( [ 'post_type' => 'answer' ] );
		$result = ap_total_answers_found();
		$this->assertEquals( $answers->found_posts, $result );

		// Test 4.
		$ids = $this->insert_answers( [], [], 11 );
		$answers = new \WP_Query( [ 'post_type' => 'answer', 'posts_per_page' => -1 ] );
		$result = ap_total_answers_found();
		$this->assertEquals( $answers->found_posts, $result );

		// Reset global $answers.
		$answers = null;
	}

	/**
	 * @covers ::ap_have_answers
	 */
	public function testAPHaveAnswers() {
		global $answers;

		// Test 1.
		$question_id = $this->insert_question();
		$answers     = new \WP_Query( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$result      = ap_have_answers();
		$this->assertFalse( $result );

		// Test 2.
		$ids     = $this->insert_answers( [], [], 11 );
		$answers = new \WP_Query( [ 'post_type' => 'answer', 'post_parent' => $ids['question'] ] );
		$result  = ap_have_answers();
		$this->assertTrue( $result );

		// Test 3.
		$ids     = $this->insert_answers( [], [], 0 );
		$answers = new \WP_Query( [ 'post_type' => 'answer', 'post_parent' => $ids['question'] ] );
		$result  = ap_have_answers();
		$this->assertFalse( $result );

		// Test 4.
		$answers = null;
		$result  = ap_have_answers();
		$this->assertFalse( $result );

		// Reset global $answers.
		$answers = null;
	}

	/**
	 * @covers ::ap_get_answer
	 */
	public function testAPGetAnswer() {
		$id = $this->insert_answer();

		// Test 1.
		$result = ap_get_answer( $id->a );
		$this->assertInstanceOf( 'Answers_Query', $result );
		$this->assertNotNull( $result->post );
		$this->assertEquals( $id->a, $result->post->ID );

		// Test 2.
		$result = ap_get_answer( 0 );
		$this->assertNull( $result->post );
	}

	/**
	 * @covers ::ap_get_answers
	 */
	public function testAPGetAnswers() {
		$ids = $this->insert_answers( [], [], 5 );

		// Test 1.
		$result = ap_get_answers();
		$this->assertInstanceOf( 'Answers_Query', $result );
		$this->assertNull( $result->post );

		// Test 2.
		$this->go_to( '?post_type=question&p=' . $ids['question'] );
		$result = ap_get_answers();
		$this->assertInstanceOf( 'Answers_Query', $result );
		$this->assertNotNull( $result->post );
		$this->assertEquals( 5, $result->found_posts );
		$this->go_to( home_url() );

		// Test 3.
		$result = ap_get_answers( [ 'p' => $ids['answers'][0] ] );
		$this->assertInstanceOf( 'Answers_Query', $result );
		$this->assertNotNull( $result->post );
		$this->assertEquals( $ids['answers'][0], $result->post->ID );
		$this->assertEquals( 1, $result->found_posts );

		// Test 4.
		$this->go_to( '?post_type=question&p=' . $ids['question'] );
		$result = ap_get_answers();
		$this->assertInstanceOf( 'Answers_Query', $result );
		$this->assertNotNull( $result->post );
		$this->assertEquals( $ids['question'], $result->args['question_id'] );
		$this->assertEquals( ap_opt( 'answers_sort' ), $result->args['ap_order_by'] );

		// Test 5.
		$_REQUEST['order_by'] = 'newest';
		$result = ap_get_answers();
		$this->assertInstanceOf( 'Answers_Query', $result );
		$this->assertNotNull( $result->post );
		$this->assertEquals( $ids['question'], $result->args['question_id'] );
		$this->assertEquals( 'newest', $result->args['ap_order_by'] );

		// Test 6.
		$_REQUEST['order_by'] = 'oldest';
		$args = [ 'p' => $ids['answers'][0] ];
		$result = ap_get_answers( $args );
		$this->assertInstanceOf( 'Answers_Query', $result );
		$this->assertNotNull( $result->post );
		$this->assertEquals( $ids['answers'][0], $result->post->ID );
		$this->assertEquals( 1, $result->found_posts );
		$this->assertEquals( 'oldest', $result->args['ap_order_by'] );
		$this->assertEquals( $ids['answers'][0], $result->args['p'] );

		// Test 7.
		unset( $_REQUEST['order_by'] );
		$args = [ 'test_arg' => 'test_value' ];
		$result = ap_get_answers( $args );
		$this->assertInstanceOf( 'Answers_Query', $result );
		$this->assertNotNull( $result->post );
		$this->assertEquals( $ids['question'], $result->args['question_id'] );
		$this->assertEquals( ap_opt( 'answers_sort' ), $result->args['ap_order_by'] );
		$this->assertEquals( 'test_value', $result->args['test_arg'] );
	}

	/**
	 * @covers ::ap_get_best_answer
	 */
	public function testAPGetBestAnswer() {
		// Test 1.
		$question_id = $this->insert_question();
		$answer_id   = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$result      = ap_get_best_answer( $question_id );
		$this->assertInstanceof( 'Answers_Query', $result );
		$this->assertEquals( 1, $result->found_posts );
		$this->assertEquals( $answer_id, $result->post->ID );
		$this->assertEquals( $answer_id, $result->posts[0]->ID );

		// Test 2.
		$question_id = $this->insert_question();
		$answer_id_1  = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title 1',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$answer_id_2  = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title 2',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$answer_id_3  = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title 3',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		ap_set_selected_answer( $question_id, $answer_id_3 );
		$result      = ap_get_best_answer( $question_id );
		$this->assertInstanceof( 'Answers_Query', $result );
		$this->assertEquals( 3, $result->found_posts );
		$this->assertEquals( $answer_id_3, $result->post->ID );
		$this->assertEquals( $answer_id_3, $result->posts[0]->ID );
	}

	/**
	 * @covers ::ap_get_best_answer
	 */
	public function testAPGetBestAnswerNoOrFalseArgumentPassed() {
		// Test 1.
		$question_id = $this->insert_question();
		$answer_id   = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$this->go_to( '?post_type=question&p=' . $question_id );
		$result = ap_get_best_answer();
		$this->assertInstanceof( 'Answers_Query', $result );
		$this->assertEquals( 1, $result->found_posts );
		$this->assertEquals( $answer_id, $result->post->ID );
		$this->assertEquals( $answer_id, $result->posts[0]->ID );

		// Test 2.
		$question_id = $this->insert_question();
		$answer_id_1  = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title 1',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$answer_id_2  = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title 2',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$answer_id_3  = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title 3',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		ap_set_selected_answer( $question_id, $answer_id_3 );
		$this->go_to( '?post_type=question&p=' . $question_id );
		$result = ap_get_best_answer();
		$this->assertInstanceof( 'Answers_Query', $result );
		$this->assertEquals( 3, $result->found_posts );
		$this->assertEquals( $answer_id_3, $result->post->ID );
		$this->assertEquals( $answer_id_3, $result->posts[0]->ID );
	}

	/**
	 * @covers ::ap_answer_user_can_view
	 */
	public function testAPAnswerUserCanView() {
		$question_id = $this->insert_question();
		$answer_id   = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$this->go_to( '?post_type=question&p=' . $question_id );
		$result = ap_answer_user_can_view( $answer_id );
		$this->assertTrue( $result );
	}

	/**
	 * @covers ::ap_answer_user_can_view
	 */
	public function testAPAnswerUserCanViewShouldReturnFalse() {
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_type'    => 'post',
			)
		);
		$this->go_to( '?p=' . $post_id );
		$result = ap_answer_user_can_view( $post_id );
		$this->assertFalse( $result );
	}

	/**
	 * @covers ::ap_count_other_answer
	 */
	public function testAPCountOtherAnswerNoOrFalseArg() {
		// Test 1.
		$question_id = $this->insert_question();
		$this->go_to( '?post_type=question&p=' . $question_id );
		$this->assertEquals( 0, ap_count_other_answer() );

		// Test 2.
		$id = $this->insert_answer();
		$this->go_to( '?post_type=question&p=' . $id->q );
		$this->assertEquals( 1, ap_count_other_answer() );

		// Test 3.
		$ids = $this->insert_answers( [], [], 5 );
		$this->go_to( '?post_type=question&p=' . $ids['question'] );
		$this->assertEquals( 5, ap_count_other_answer( false ) );
	}

	/**
	 * @covers ::ap_count_other_answer
	 */
	public function testAPCountOtherAnswerNoOrFalseArgForSelectedAnswerSet() {
		// Before setting answer as selected.
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
			)
		);
		$answer_id_1  = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title 1',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$answer_id_2  = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title 2',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$answer_id_3  = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title 3',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$this->go_to( '?post_type=question&p=' . $question_id );
		$this->assertEquals( 3, ap_count_other_answer() );

		// After setting answer as selected.
		ap_set_selected_answer( $question_id, $answer_id_3 );
		$this->go_to( '?post_type=question&p=' . $question_id );
		$this->assertEquals( 2, ap_count_other_answer() );
	}
}
