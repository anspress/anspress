<?php

namespace AnsPress\Tests\WP;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestTemplatesWidgetQuestions extends TestCase {

	use Testcases\Common;

	public function testWidgetQuestions() {
		// Test 1.
		anspress()->questions = new \Question_Query();
		ob_start();
		ap_get_template_part( 'widgets/widget-questions' );
		$output = ob_get_clean();
		$this->assertStringContainsString( '<div class="ap-questions-widget clearfix">', $output );
		$this->assertStringContainsString( 'No questions found.', $output );

		// Test 2.
		$question_id = $this->insert_question();
		anspress()->questions = new \Question_Query();
		ob_start();
		ap_get_template_part( 'widgets/widget-questions' );
		$output = ob_get_clean();
		$this->assertStringContainsString( '<div class="ap-questions-widget clearfix">', $output );
		$this->assertStringContainsString( '<div class="ap-question-item">', $output );
		$this->assertStringContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink() ) . '">Question title</a>', $output );
		$this->assertStringContainsString( '<span class="ap-ans-count">', $output );
		$this->assertStringContainsString( '0 Answers', $output );
		$this->assertStringContainsString( '<span class="ap-vote-count">', $output );
		$this->assertStringContainsString( '0 Votes', $output );
		$this->assertStringNotContainsString( 'No questions found.', $output );

		// Delete previously created questions so that it will not hamper other tests.
		wp_delete_post( $question_id, true );

		// Test 3.
		$question_id_1 = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'Question Title' ] );
		$question_id_2 = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'What is Lorem Ipsum?' ] );
		$question_id_3 = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'AnsPress Question Answer Plugin' ] );
		$question_id_4 = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'Test Question' ] );
		$question_id_5 = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'How to create a WordPress theme and plugin?' ] );
		anspress()->questions = new \Question_Query();
		ob_start();
		ap_get_template_part( 'widgets/widget-questions' );
		$output = ob_get_clean();
		$this->assertStringContainsString( '<div class="ap-questions-widget clearfix">', $output );
		$this->assertStringContainsString( '<div class="ap-question-item">', $output );
		$this->assertStringContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink( $question_id_1 ) ) . '">Question Title</a>', $output );
		$this->assertStringContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink( $question_id_2 ) ) . '">What is Lorem Ipsum?</a>', $output );
		$this->assertStringContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink( $question_id_3 ) ) . '">AnsPress Question Answer Plugin</a>', $output );
		$this->assertStringContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink( $question_id_4 ) ) . '">Test Question</a>', $output );
		$this->assertStringContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink( $question_id_5 ) ) . '">How to create a WordPress theme and plugin?</a>', $output );
		$this->assertStringContainsString( '<span class="ap-ans-count">', $output );
		$this->assertStringContainsString( '0 Answers', $output );
		$this->assertStringContainsString( '<span class="ap-vote-count">', $output );
		$this->assertStringContainsString( '0 Votes', $output );
		$this->assertStringNotContainsString( 'No questions found.', $output );

		// Delete previously created questions so that it will not hamper other tests.
		wp_delete_post( $question_id_1, true );
		wp_delete_post( $question_id_2, true );
		wp_delete_post( $question_id_3, true );
		wp_delete_post( $question_id_4, true );
		wp_delete_post( $question_id_5, true );

		// Test 4.
		$ids = $this->insert_answers( [ 'post_title' => 'How to use AnsPress Question Answer plugin?' ], [], 3 );
		ap_add_post_vote( $ids['question'] );
		$user_id = $this->factory()->user->create( [ 'role' => 'subscriber' ] );
		ap_add_post_vote( $ids['question'], $user_id );
		anspress()->questions = new \Question_Query();
		ob_start();
		ap_get_template_part( 'widgets/widget-questions' );
		$output = ob_get_clean();
		$this->assertStringContainsString( '<div class="ap-questions-widget clearfix">', $output );
		$this->assertStringContainsString( '<div class="ap-question-item">', $output );
		$this->assertStringContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink() ) . '">How to use AnsPress Question Answer plugin?</a>', $output );
		$this->assertStringContainsString( '<span class="ap-ans-count">', $output );
		$this->assertStringContainsString( '3 Answers', $output );
		$this->assertStringContainsString( '<span class="ap-vote-count">', $output );
		$this->assertStringContainsString( '2 Votes', $output );
		$this->assertStringNotContainsString( 'No questions found.', $output );

		// Delete previously created questions so that it will not hamper other tests.
		wp_delete_post( $ids['question'], true );

		// Test 5.
		$id = $this->insert_answer();
		$user_id = $this->factory()->user->create( [ 'role' => 'subscriber' ] );
		ap_add_post_vote( $id->q, $user_id, false );
		anspress()->questions = new \Question_Query();
		ob_start();
		ap_get_template_part( 'widgets/widget-questions' );
		$output = ob_get_clean();
		$this->assertStringContainsString( '<div class="ap-questions-widget clearfix">', $output );
		$this->assertStringContainsString( '<div class="ap-question-item">', $output );
		$this->assertStringContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink() ) . '">Question title</a>', $output );
		$this->assertStringContainsString( '<span class="ap-ans-count">', $output );
		$this->assertStringContainsString( '1 Answer', $output );
		$this->assertStringContainsString( '<span class="ap-vote-count">', $output );
		$this->assertStringContainsString( '-1 Votes', $output );
		$this->assertStringNotContainsString( 'No questions found.', $output );
	}
}
