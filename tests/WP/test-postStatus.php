<?php

namespace AnsPress\Tests\WP;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestPostStatus extends TestCase {

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Post_Status', 'register_post_status' ) );
		$this->assertTrue( method_exists( 'AnsPress_Post_Status', 'change_post_status' ) );
	}

	public function TestHooks() {
		$this->assertEquals( 10, has_action( 'init', [ 'AnsPress_Post_Status', 'register_post_status' ] ) );
	}

	public function testRegisterPostStatuses() {
		\AnsPress_Post_Status::register_post_status();
		global $wp_post_statuses;

		$this->assertArrayHasKey( 'moderate', $wp_post_statuses );
		$this->assertArrayHasKey( 'private_post', $wp_post_statuses );
	}

	/**
	 * @covers ::ap_get_post_status_message
	 */
	public function testAPGetPostStatusMessage() {
		// Test 1.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$answer_id   = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$this->assertEquals( '', ap_get_post_status_message( $question_id ) );
		$this->assertEquals( '', ap_get_post_status_message( $answer_id ) );

		// Test 2.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'private_post' ] );
		$answer_id   = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_status' => 'private_post' ] );
		$this->assertStringContainsString( '<i class="apicon-lock"></i>', ap_get_post_status_message( $question_id ) );
		$this->assertStringContainsString( 'This Question is marked as a private, only admin and post author can see.', ap_get_post_status_message( $question_id ) );
		$this->assertEquals(
			'<div class="ap-notice status-private_post"><i class="apicon-lock"></i><span>This Question is marked as a private, only admin and post author can see.</span></div>',
			ap_get_post_status_message( $question_id )
		);
		$this->assertStringContainsString( '<i class="apicon-lock"></i>', ap_get_post_status_message( $answer_id ) );
		$this->assertStringContainsString( 'This Answer is marked as a private, only admin and post author can see.', ap_get_post_status_message( $answer_id ) );
		$this->assertEquals(
			'<div class="ap-notice status-private_post"><i class="apicon-lock"></i><span>This Answer is marked as a private, only admin and post author can see.</span></div>',
			ap_get_post_status_message( $answer_id )
		);

		// Test 3.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'moderate' ] );
		$answer_id   = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_status' => 'moderate' ] );
		$this->assertStringContainsString( '<i class="apicon-alert"></i>', ap_get_post_status_message( $question_id ) );
		$this->assertStringContainsString( 'This Question is waiting for the approval by the moderator.', ap_get_post_status_message( $question_id ) );
		$this->assertEquals(
			'<div class="ap-notice status-moderate"><i class="apicon-alert"></i><span>This Question is waiting for the approval by the moderator.</span></div>',
			ap_get_post_status_message( $question_id )
		);
		$this->assertStringContainsString( '<i class="apicon-alert"></i>', ap_get_post_status_message( $answer_id ) );
		$this->assertStringContainsString( 'This Answer is waiting for the approval by the moderator.', ap_get_post_status_message( $answer_id ) );
		$this->assertEquals(
			'<div class="ap-notice status-moderate"><i class="apicon-alert"></i><span>This Answer is waiting for the approval by the moderator.</span></div>',
			ap_get_post_status_message( $answer_id )
		);

		// Test 4.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		ap_insert_qameta(
			$question_id,
			array(
				'selected_id'  => '',
				'last_updated' => current_time( 'mysql' ),
				'closed'       => 1,
			)
		);
		$this->assertStringContainsString( '<i class="apicon-x"></i>', ap_get_post_status_message( $question_id ) );
		$this->assertStringContainsString( 'Question is closed for new answers.', ap_get_post_status_message( $question_id ) );
		$this->assertEquals(
			'<div class="ap-notice status-publish closed"><i class="apicon-x"></i><span>Question is closed for new answers.</span></div>',
			ap_get_post_status_message( $question_id )
		);

		// Test 5.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'trash' ] );
		$answer_id   = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_status' => 'trash' ] );
		$this->assertStringContainsString( '<i class="apicon-trashcan"></i>', ap_get_post_status_message( $question_id ) );
		$this->assertStringContainsString( 'This Question has been trashed, you can delete it permanently from wp-admin.', ap_get_post_status_message( $question_id ) );
		$this->assertEquals(
			'<div class="ap-notice status-trash"><i class="apicon-trashcan"></i><span>This Question has been trashed, you can delete it permanently from wp-admin.</span></div>',
			ap_get_post_status_message( $question_id )
		);
		$this->assertStringContainsString( '<i class="apicon-trashcan"></i>', ap_get_post_status_message( $answer_id ) );
		$this->assertStringContainsString( 'This Answer has been trashed, you can delete it permanently from wp-admin.', ap_get_post_status_message( $answer_id ) );
		$this->assertEquals(
			'<div class="ap-notice status-trash"><i class="apicon-trashcan"></i><span>This Answer has been trashed, you can delete it permanently from wp-admin.</span></div>',
			ap_get_post_status_message( $answer_id )
		);

		// Test 6.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'future', 'post_date' => '9999-12-31 23:59:59', ] );
		$answer_id   = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_status' => 'future', 'post_date' => '9999-12-31 23:59:59', ] );
		$this->assertStringContainsString( '<i class="apicon-clock"></i>', ap_get_post_status_message( $question_id ) );
		$this->assertStringContainsString( 'This Question is not published yet and is not accessible to anyone until it get published.', ap_get_post_status_message( $question_id ) );
		$this->assertEquals(
			'<div class="ap-notice status-future"><i class="apicon-clock"></i><span>This Question is not published yet and is not accessible to anyone until it get published.</span></div>',
			ap_get_post_status_message( $question_id )
		);
		$this->assertStringContainsString( '<i class="apicon-clock"></i>', ap_get_post_status_message( $answer_id ) );
		$this->assertStringContainsString( 'This Answer is not published yet and is not accessible to anyone until it get published.', ap_get_post_status_message( $answer_id ) );
		$this->assertEquals(
			'<div class="ap-notice status-future"><i class="apicon-clock"></i><span>This Answer is not published yet and is not accessible to anyone until it get published.</span></div>',
			ap_get_post_status_message( $answer_id )
		);
	}

	/**
	 * @covers ::ap_post_status_badge
	 */
	public function testAPPostStatusBadge() {
		// Test 1.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$answer_id   = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$this->assertEquals( '<postmessage></postmessage>', ap_post_status_badge( $question_id ) );
		$this->assertEquals( '<postmessage></postmessage>', ap_post_status_badge( $answer_id ) );

		// Test 2.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'private_post' ] );
		$answer_id   = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_status' => 'private_post' ] );
		$this->assertStringContainsString( '<i class="apicon-lock"></i>', ap_post_status_badge( $question_id ) );
		$this->assertStringContainsString( 'This Question is marked as a private, only admin and post author can see.', ap_post_status_badge( $question_id ) );
		$this->assertEquals(
			'<postmessage><div class="ap-notice status-private_post"><i class="apicon-lock"></i><span>This Question is marked as a private, only admin and post author can see.</span></div></postmessage>',
			ap_post_status_badge( $question_id )
		);
		$this->assertStringContainsString( '<i class="apicon-lock"></i>', ap_post_status_badge( $answer_id ) );
		$this->assertStringContainsString( 'This Answer is marked as a private, only admin and post author can see.', ap_post_status_badge( $answer_id ) );
		$this->assertEquals(
			'<postmessage><div class="ap-notice status-private_post"><i class="apicon-lock"></i><span>This Answer is marked as a private, only admin and post author can see.</span></div></postmessage>',
			ap_post_status_badge( $answer_id )
		);

		// Test 3.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'moderate' ] );
		$answer_id   = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_status' => 'moderate' ] );
		$this->assertStringContainsString( '<i class="apicon-alert"></i>', ap_post_status_badge( $question_id ) );
		$this->assertStringContainsString( 'This Question is waiting for the approval by the moderator.', ap_post_status_badge( $question_id ) );
		$this->assertEquals(
			'<postmessage><div class="ap-notice status-moderate"><i class="apicon-alert"></i><span>This Question is waiting for the approval by the moderator.</span></div></postmessage>',
			ap_post_status_badge( $question_id )
		);
		$this->assertStringContainsString( '<i class="apicon-alert"></i>', ap_post_status_badge( $answer_id ) );
		$this->assertStringContainsString( 'This Answer is waiting for the approval by the moderator.', ap_post_status_badge( $answer_id ) );
		$this->assertEquals(
			'<postmessage><div class="ap-notice status-moderate"><i class="apicon-alert"></i><span>This Answer is waiting for the approval by the moderator.</span></div></postmessage>',
			ap_post_status_badge( $answer_id )
		);

		// Test 4.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		ap_insert_qameta(
			$question_id,
			array(
				'selected_id'  => '',
				'last_updated' => current_time( 'mysql' ),
				'closed'       => 1,
			)
		);
		$this->assertStringContainsString( '<i class="apicon-x"></i>', ap_post_status_badge( $question_id ) );
		$this->assertStringContainsString( 'Question is closed for new answers.', ap_post_status_badge( $question_id ) );
		$this->assertEquals(
			'<postmessage><div class="ap-notice status-publish closed"><i class="apicon-x"></i><span>Question is closed for new answers.</span></div></postmessage>',
			ap_post_status_badge( $question_id )
		);

		// Test 5.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'trash' ] );
		$answer_id   = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_status' => 'trash' ] );
		$this->assertStringContainsString( '<i class="apicon-trashcan"></i>', ap_post_status_badge( $question_id ) );
		$this->assertStringContainsString( 'This Question has been trashed, you can delete it permanently from wp-admin.', ap_post_status_badge( $question_id ) );
		$this->assertEquals(
			'<postmessage><div class="ap-notice status-trash"><i class="apicon-trashcan"></i><span>This Question has been trashed, you can delete it permanently from wp-admin.</span></div></postmessage>',
			ap_post_status_badge( $question_id )
		);
		$this->assertStringContainsString( '<i class="apicon-trashcan"></i>', ap_post_status_badge( $answer_id ) );
		$this->assertStringContainsString( 'This Answer has been trashed, you can delete it permanently from wp-admin.', ap_post_status_badge( $answer_id ) );
		$this->assertEquals(
			'<postmessage><div class="ap-notice status-trash"><i class="apicon-trashcan"></i><span>This Answer has been trashed, you can delete it permanently from wp-admin.</span></div></postmessage>',
			ap_post_status_badge( $answer_id )
		);

		// Test 6.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'future', 'post_date' => '9999-12-31 23:59:59', ] );
		$answer_id   = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_status' => 'future', 'post_date' => '9999-12-31 23:59:59', ] );
		$this->assertStringContainsString( '<i class="apicon-clock"></i>', ap_post_status_badge( $question_id ) );
		$this->assertStringContainsString( 'This Question is not published yet and is not accessible to anyone until it get published.', ap_post_status_badge( $question_id ) );
		$this->assertEquals(
			'<postmessage><div class="ap-notice status-future"><i class="apicon-clock"></i><span>This Question is not published yet and is not accessible to anyone until it get published.</span></div></postmessage>',
			ap_post_status_badge( $question_id )
		);
		$this->assertStringContainsString( '<i class="apicon-clock"></i>', ap_post_status_badge( $answer_id ) );
		$this->assertStringContainsString( 'This Answer is not published yet and is not accessible to anyone until it get published.', ap_post_status_badge( $answer_id ) );
		$this->assertEquals(
			'<postmessage><div class="ap-notice status-future"><i class="apicon-clock"></i><span>This Answer is not published yet and is not accessible to anyone until it get published.</span></div></postmessage>',
			ap_post_status_badge( $answer_id )
		);
	}
}
