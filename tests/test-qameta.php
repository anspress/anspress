<?php

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestQAMeta extends TestCase {

	use AnsPress\Tests\Testcases\Common;

	/**
	 * @covers ::ap_qameta_fields
	 */
	public function testAPQametaFields() {
		// Test for if the array key exists or not.
		$qameta_fields_array = array(
			'post_id',
			'selected',
			'selected_id',
			'comments',
			'answers',
			'ptype',
			'featured',
			'closed',
			'views',
			'votes_up',
			'votes_down',
			'subscribers',
			'flags',
			'terms',
			'attach',
			'activities',
			'fields',
			'roles',
			'last_updated',
			'is_new',
		);
		foreach ( $qameta_fields_array as $qameta_field_item ) {
			$this->assertArrayHasKey( $qameta_field_item, ap_qameta_fields() );
		}

		// Test for the qameta fields default value is matching or not.
		$qameta_fields = ap_qameta_fields();
		$this->assertEquals( '', $qameta_fields['post_id'] );
		$this->assertFalse( $qameta_fields['selected'] );
		$this->assertEquals( 0, $qameta_fields['selected_id'] );
		$this->assertEquals( 0, $qameta_fields['comments'] );
		$this->assertEquals( 0, $qameta_fields['answers'] );
		$this->assertEquals( 'question', $qameta_fields['ptype'] );
		$this->assertEquals( 0, $qameta_fields['featured'] );
		$this->assertEquals( 0, $qameta_fields['closed'] );
		$this->assertEquals( 0, $qameta_fields['views'] );
		$this->assertEquals( 0, $qameta_fields['votes_up'] );
		$this->assertEquals( 0, $qameta_fields['votes_down'] );
		$this->assertEquals( 0, $qameta_fields['subscribers'] );
		$this->assertEquals( 0, $qameta_fields['flags'] );
		$this->assertEquals( '', $qameta_fields['terms'] );
		$this->assertEquals( '', $qameta_fields['attach'] );
		$this->assertEquals( '', $qameta_fields['activities'] );
		$this->assertEquals( '', $qameta_fields['fields'] );
		$this->assertEquals( '', $qameta_fields['roles'] );
		$this->assertEquals( '', $qameta_fields['last_updated'] );
		$this->assertFalse( $qameta_fields['is_new'] );
	}

	/**
	 * @covers ::ap_set_selected_answer
	 * @covers ::ap_unset_selected_answer
	 */
	public function testSelectedAnswer() {
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question Content',
				'post_type'    => 'question',
			)
		);
		$answer1_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer Content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$answer2_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer Content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		ap_set_selected_answer( $question_id, $answer1_id );
		$get_qameta = ap_get_qameta( $answer1_id );
		$this->assertEquals( 1, $get_qameta->selected );
		$get_qameta = ap_get_qameta( $answer2_id );
		$this->assertNotEquals( 1, $get_qameta->selected );

		// Updating the selected answer test.
		ap_unset_selected_answer( $question_id, $answer1_id );
		$get_qameta = ap_get_qameta( $answer1_id );
		$this->assertNotEquals( 1, $get_qameta->selected );
		$get_qameta = ap_get_qameta( $answer2_id );
		$this->assertNotEquals( 1, $get_qameta->selected );

		ap_set_selected_answer( $question_id, $answer2_id );
		$get_qameta = ap_get_qameta( $answer1_id );
		$this->assertNotEquals( 1, $get_qameta->selected );
		$get_qameta = ap_get_qameta( $answer2_id );
		$this->assertEquals( 1, $get_qameta->selected );

		// Updating the selected answer test.
		ap_unset_selected_answer( $question_id, $answer2_id );
		$get_qameta = ap_get_qameta( $answer1_id );
		$this->assertNotEquals( 1, $get_qameta->selected );
		$get_qameta = ap_get_qameta( $answer2_id );
		$this->assertNotEquals( 1, $get_qameta->selected );
	}

	/**
	 * @covers ::ap_update_views_count
	 */
	public function testAPUpdateViewsCount() {
		$id = $this->insert_question();
		$this->assertEquals( 1, ap_update_views_count( $id ) );
		$this->assertEquals( 50, ap_update_views_count( $id, 50 ) );
		ap_insert_qameta( $id, array( 'views' => 100 ) );
		$this->assertEquals( 101, ap_update_views_count( $id ) );
	}

	/**
	 * @covers ::ap_update_answers_count
	 */
	public function testAPUpdateAnswersCount() {
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question Content',
				'post_type'    => 'question',
			)
		);
		$get_qameta = ap_get_qameta( $question_id );
		$this->assertEquals( 0, $get_qameta->answers );
		$answer1_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer Content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$get_qameta = ap_get_qameta( $question_id );
		$this->assertEquals( 1, $get_qameta->answers );
		$answer2_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer Content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$get_qameta = ap_get_qameta( $question_id );
		$this->assertEquals( 2, $get_qameta->answers );

		// Start the main test.
		ap_update_answers_count( $question_id );
		$get_qameta = ap_get_qameta( $question_id );
		$this->assertEquals( 2, $get_qameta->answers );
		ap_update_answers_count( $question_id, 100 );
		$get_qameta = ap_get_qameta( $question_id );
		$this->assertEquals( 100, $get_qameta->answers );
	}

	/**
	 * @covers ::ap_update_last_active
	 */
	public function testAPUpdateLastActive() {
		$id = $this->insert_question();
		ap_insert_qameta(
			$id,
			array(
				'last_updated' => '0000-00-00 00:00:00',
			)
		);
		$get_qameta = ap_get_qameta( $id );
		$this->assertEquals( '0000-00-00 00:00:00', $get_qameta->last_updated );

		// Real function test goes here.
		ap_update_last_active( $id );
		$get_qameta = ap_get_qameta( $id );
		$this->assertEquals( current_time( 'mysql' ), $get_qameta->last_updated );
	}

	/**
	 * @covers ::ap_set_flag_count
	 */
	public function testAPSetFlagCount() {
		$id = $this->insert_answer();
		$qget_qameta = ap_get_qameta( $id->q );
		$aget_qameta = ap_get_qameta( $id->a );
		$this->assertEquals( 0, $qget_qameta->flags );
		$this->assertEquals( 0, $aget_qameta->flags );

		// Real function test goes here.
		ap_set_flag_count( $id->q );
		ap_set_flag_count( $id->a );
		$qget_qameta = ap_get_qameta( $id->q );
		$aget_qameta = ap_get_qameta( $id->a );
		$this->assertEquals( 1, $qget_qameta->flags );
		$this->assertEquals( 1, $aget_qameta->flags );

		// Modifying the flags.
		ap_set_flag_count( $id->q, 5 );
		ap_set_flag_count( $id->a, 10 );
		$qget_qameta = ap_get_qameta( $id->q );
		$aget_qameta = ap_get_qameta( $id->a );
		$this->assertEquals( 5, $qget_qameta->flags );
		$this->assertEquals( 10, $aget_qameta->flags );

		// Resetting the flags to 0.
		ap_set_flag_count( $id->q, 0 );
		ap_set_flag_count( $id->a, 0 );
		$qget_qameta = ap_get_qameta( $id->q );
		$aget_qameta = ap_get_qameta( $id->a );
		$this->assertEquals( 0, $qget_qameta->flags );
		$this->assertEquals( 0, $aget_qameta->flags );
		$this->assertNotEquals( 1, $qget_qameta->flags );
		$this->assertNotEquals( 1, $aget_qameta->flags );
		$this->assertNotEquals( 5, $qget_qameta->flags );
		$this->assertNotEquals( 10, $aget_qameta->flags );
	}

	/**
	 * @covers ::ap_update_answer_selected
	 */
	public function testAPUpdateAnswerSelected() {
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question Content',
				'post_type'    => 'question',
			)
		);
		$answer1_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer Content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$this->assertEquals( 0, ap_is_selected( $answer1_id ) );
		$answer2_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer Content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$this->assertEquals( 0, ap_is_selected( $answer2_id ) );

		// Real function test goes here.
		ap_update_answer_selected( $answer1_id );
		$this->assertEquals( 1, ap_is_selected( $answer1_id ) );
		ap_update_answer_selected( $answer1_id, false );
		$this->assertEquals( 0, ap_is_selected( $answer1_id ) );
		$this->assertNotEquals( 1, ap_is_selected( $answer1_id ) );
		ap_update_answer_selected( $answer2_id );
		$this->assertEquals( 1, ap_is_selected( $answer2_id ) );
		ap_update_answer_selected( $answer2_id, false );
		$this->assertEquals( 0, ap_is_selected( $answer2_id ) );
		$this->assertNotEquals( 1, ap_is_selected( $answer2_id ) );
	}

	/**
	 * @covers ::ap_update_subscribers_count
	 */
	public function testAPUpdateSubscribersCount() {
		$id = $this->insert_question();
		$this->assertEquals( 0, ap_subscribers_count( 'question', $id ) );
		$this->assertEquals( 0, ap_update_subscribers_count( $id ) );
		$this->assertEquals( 100, ap_update_subscribers_count( $id, 100 ) );
		$this->assertEquals( 1000, ap_update_subscribers_count( $id, 1000 ) );
	}

	/**
	 * @covers ::ap_set_featured_question
	 * @covers ::ap_unset_featured_question
	 */
	public function testFeaturedQuestion() {
		$id = $this->insert_question();
		$this->assertFalse( ap_is_featured_question( $id ) );
		ap_set_featured_question( $id );
		$this->assertTrue( ap_is_featured_question( $id ) );
		ap_unset_featured_question( $id );
		$this->assertFalse( ap_is_featured_question( $id ) );
	}
}
