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
}
