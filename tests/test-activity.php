<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestActivity extends TestCase {

	use Testcases\Common;

	/**
	 * @covers AnsPress\Activity_Helper::instance
	 */
	public function testInstance() {
		$class = new \ReflectionClass( 'AnsPress\Activity_Helper' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic() );
	}

	public function testClassPropertiesAvailable() {
		$class = new \ReflectionClass( 'AnsPress\Activity_Helper' );
		$this->assertTrue( $class->hasProperty( 'table' ) && $class->getProperty( 'table' )->isPrivate() );
		$this->assertTrue( $class->hasProperty( 'actions' ) && $class->getProperty( 'actions' )->isPrivate() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Activity_Helper', 'get_instance' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity_Helper', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity_Helper', 'hooks' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity_Helper', '_before_delete' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity_Helper', '_delete_comment' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity_Helper', '_ajax_more_activities' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity_Helper', '_delete_user' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity_Helper', 'prepare_actions' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity_Helper', 'get_actions' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity_Helper', 'get_action' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity_Helper', 'action_exists' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity_Helper', 'insert' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity_Helper', 'get_activity' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity_Helper', 'delete' ) );
	}

	/**
	 * @covers AnsPress\Activity_Helper::hooks
	 */
	public function testAnsPressActivityHelperHooks() {
		$this->assertEquals( 10, has_action( 'before_delete_post', [ 'AnsPress\Activity_Helper', '_before_delete' ] ) );
		$this->assertEquals( 10, has_action( 'delete_comment', [ 'AnsPress\Activity_Helper', '_delete_comment' ] ) );
		$this->assertEquals( 10, has_action( 'delete_user', [ 'AnsPress\Activity_Helper', '_delete_user' ] ) );
		$this->assertEquals( 10, has_action( 'ap_ajax_more_activities', [ 'AnsPress\Activity_Helper', '_ajax_more_activities' ] ) );
	}

	/**
	 * @covers AnsPress\Activity_Helper::get_actions
	 */
	public function testAnsPressActivityHelperGetActions() {
		$activity = \AnsPress\Activity_Helper::get_instance();
		$get_actions = $activity->get_actions();

		// Test begins.
		$this->assertNotEmpty( $get_actions );
		$this->assertIsArray( $get_actions );

		// Test if exists the array key.
		$this->assertArrayHasKey( 'new_q', $get_actions );
		$this->assertArrayHasKey( 'edit_q', $get_actions );
		$this->assertArrayHasKey( 'new_a', $get_actions );
		$this->assertArrayHasKey( 'edit_a', $get_actions );
		$this->assertArrayHasKey( 'status_publish', $get_actions );
		$this->assertArrayHasKey( 'status_future', $get_actions );
		$this->assertArrayHasKey( 'status_moderate', $get_actions );
		$this->assertArrayHasKey( 'status_private_post', $get_actions );
		$this->assertArrayHasKey( 'status_trash', $get_actions );
		$this->assertArrayHasKey( 'featured', $get_actions );
		$this->assertArrayHasKey( 'closed_q', $get_actions );
		$this->assertArrayHasKey( 'new_c', $get_actions );
		$this->assertArrayHasKey( 'edit_c', $get_actions );
		$this->assertArrayHasKey( 'selected', $get_actions );
		$this->assertArrayHasKey( 'unselected', $get_actions );

		// Test if the needed activity details is present inside the array or not.
		foreach ( $get_actions as $action ) {
			$this->assertArrayHasKey( 'ref_type', $action );
			$this->assertArrayHasKey( 'verb', $action );
			$this->assertArrayHasKey( 'icon', $action );
		}
	}

	/**
	 * @covers AnsPress\Activity_Helper::get_action
	 */
	public function testAnsPressActivityHelperGetAction() {
		$activity = \AnsPress\Activity_Helper::get_instance();

		// Test begins.
		// For no activity action exists.
		$this->assertEmpty( $activity->get_action( 'test_question' ) );
		$this->assertEmpty( $activity->get_action( 'test_answer' ) );

		// For activity action exists.
		$this->assertNotEmpty( $activity->get_action( 'new_q' ) );
		$this->assertNotEmpty( $activity->get_action( 'edit_q' ) );
		$this->assertNotEmpty( $activity->get_action( 'new_a' ) );
		$this->assertNotEmpty( $activity->get_action( 'edit_a' ) );
		$this->assertNotEmpty( $activity->get_action( 'status_publish' ) );
		$this->assertNotEmpty( $activity->get_action( 'status_future' ) );
		$this->assertNotEmpty( $activity->get_action( 'status_moderate' ) );
		$this->assertNotEmpty( $activity->get_action( 'status_private_post' ) );
		$this->assertNotEmpty( $activity->get_action( 'status_trash' ) );
		$this->assertNotEmpty( $activity->get_action( 'featured' ) );
		$this->assertNotEmpty( $activity->get_action( 'closed_q' ) );
		$this->assertNotEmpty( $activity->get_action( 'new_c' ) );
		$this->assertNotEmpty( $activity->get_action( 'edit_c' ) );
		$this->assertNotEmpty( $activity->get_action( 'selected' ) );
		$this->assertNotEmpty( $activity->get_action( 'unselected' ) );

		// Test inner activity details can be found.
		// For new question.
		$new_q = $activity->get_action( 'new_q' );
		$this->assertArrayHasKey( 'ref_type', $new_q );
		$this->assertArrayHasKey( 'verb', $new_q );
		$this->assertArrayHasKey( 'icon', $new_q );
		$this->assertEquals( 'question', $new_q['ref_type'] );
		$this->assertEquals( 'Asked question', $new_q['verb'] );
		$this->assertEquals( 'apicon-question', $new_q['icon'] );

		// For edit question.
		$edit_q = $activity->get_action( 'edit_q' );
		$this->assertArrayHasKey( 'ref_type', $edit_q );
		$this->assertArrayHasKey( 'verb', $edit_q );
		$this->assertArrayHasKey( 'icon', $edit_q );
		$this->assertEquals( 'question', $edit_q['ref_type'] );
		$this->assertEquals( 'Edited question', $edit_q['verb'] );
		$this->assertEquals( 'apicon-pencil', $edit_q['icon'] );

		// For new answer.
		$new_a = $activity->get_action( 'new_a' );
		$this->assertArrayHasKey( 'ref_type', $new_a );
		$this->assertArrayHasKey( 'verb', $new_a );
		$this->assertArrayHasKey( 'icon', $new_a );
		$this->assertEquals( 'answer', $new_a['ref_type'] );
		$this->assertEquals( 'Answered question', $new_a['verb'] );
		$this->assertEquals( 'apicon-answer', $new_a['icon'] );

		// For edit answer.
		$edit_a = $activity->get_action( 'edit_a' );
		$this->assertArrayHasKey( 'ref_type', $edit_a );
		$this->assertArrayHasKey( 'verb', $edit_a );
		$this->assertArrayHasKey( 'icon', $edit_a );
		$this->assertEquals( 'answer', $edit_a['ref_type'] );
		$this->assertEquals( 'Edited answer', $edit_a['verb'] );
		$this->assertEquals( 'apicon-answer', $edit_a['icon'] );

		// For status publish.
		$status_publish = $activity->get_action( 'status_publish' );
		$this->assertArrayHasKey( 'ref_type', $status_publish );
		$this->assertArrayHasKey( 'verb', $status_publish );
		$this->assertArrayHasKey( 'icon', $status_publish );
		$this->assertEquals( 'post', $status_publish['ref_type'] );
		$this->assertEquals( 'Changed status to publish', $status_publish['verb'] );
		$this->assertEquals( 'apicon-flag', $status_publish['icon'] );

		// For status future.
		$status_future = $activity->get_action( 'status_future' );
		$this->assertArrayHasKey( 'ref_type', $status_future );
		$this->assertArrayHasKey( 'verb', $status_future );
		$this->assertArrayHasKey( 'icon', $status_future );
		$this->assertEquals( 'post', $status_future['ref_type'] );
		$this->assertEquals( 'Changed publish date to future', $status_future['verb'] );
		$this->assertEquals( 'apicon-flag', $status_future['icon'] );

		// For status moderate.
		$status_moderate = $activity->get_action( 'status_moderate' );
		$this->assertArrayHasKey( 'ref_type', $status_moderate );
		$this->assertArrayHasKey( 'verb', $status_moderate );
		$this->assertArrayHasKey( 'icon', $status_moderate );
		$this->assertEquals( 'post', $status_moderate['ref_type'] );
		$this->assertEquals( 'Changed status to moderate', $status_moderate['verb'] );
		$this->assertEquals( 'apicon-flag', $status_moderate['icon'] );

		// For status private post.
		$status_private_post = $activity->get_action( 'status_private_post' );
		$this->assertArrayHasKey( 'ref_type', $status_private_post );
		$this->assertArrayHasKey( 'verb', $status_private_post );
		$this->assertArrayHasKey( 'icon', $status_private_post );
		$this->assertEquals( 'post', $status_private_post['ref_type'] );
		$this->assertEquals( 'Changed visibility to private', $status_private_post['verb'] );
		$this->assertEquals( 'apicon-flag', $status_private_post['icon'] );

		// For status trash.
		$status_trash = $activity->get_action( 'status_trash' );
		$this->assertArrayHasKey( 'ref_type', $status_trash );
		$this->assertArrayHasKey( 'verb', $status_trash );
		$this->assertArrayHasKey( 'icon', $status_trash );
		$this->assertEquals( 'post', $status_trash['ref_type'] );
		$this->assertEquals( 'Trashed', $status_trash['verb'] );
		$this->assertEquals( 'apicon-trashcan', $status_trash['icon'] );

		// For feature question.
		$featured = $activity->get_action( 'featured' );
		$this->assertArrayHasKey( 'ref_type', $featured );
		$this->assertArrayHasKey( 'verb', $featured );
		$this->assertArrayHasKey( 'icon', $featured );
		$this->assertEquals( 'question', $featured['ref_type'] );
		$this->assertEquals( 'Marked as featured question', $featured['verb'] );
		$this->assertEquals( 'apicon-star', $featured['icon'] );

		// For closed question.
		$closed_q = $activity->get_action( 'closed_q' );
		$this->assertArrayHasKey( 'ref_type', $closed_q );
		$this->assertArrayHasKey( 'verb', $closed_q );
		$this->assertArrayHasKey( 'icon', $closed_q );
		$this->assertEquals( 'question', $closed_q['ref_type'] );
		$this->assertEquals( 'Marked as closed', $closed_q['verb'] );
		$this->assertEquals( 'apicon-alert', $closed_q['icon'] );

		// For new comment.
		$new_c = $activity->get_action( 'new_c' );
		$this->assertArrayHasKey( 'ref_type', $new_c );
		$this->assertArrayHasKey( 'verb', $new_c );
		$this->assertArrayHasKey( 'icon', $new_c );
		$this->assertEquals( 'comment', $new_c['ref_type'] );
		$this->assertEquals( 'Posted new comment', $new_c['verb'] );
		$this->assertEquals( 'apicon-comments', $new_c['icon'] );

		// For edit comment.
		$edit_c = $activity->get_action( 'edit_c' );
		$this->assertArrayHasKey( 'ref_type', $edit_c );
		$this->assertArrayHasKey( 'verb', $edit_c );
		$this->assertArrayHasKey( 'icon', $edit_c );
		$this->assertEquals( 'comment', $edit_c['ref_type'] );
		$this->assertEquals( 'Edited comment', $edit_c['verb'] );
		$this->assertEquals( 'apicon-comments', $edit_c['icon'] );

		// For selected answer.
		$selected = $activity->get_action( 'selected' );
		$this->assertArrayHasKey( 'ref_type', $selected );
		$this->assertArrayHasKey( 'verb', $selected );
		$this->assertArrayHasKey( 'icon', $selected );
		$this->assertEquals( 'answer', $selected['ref_type'] );
		$this->assertEquals( 'Selected answer as best', $selected['verb'] );
		$this->assertEquals( 'apicon-check', $selected['icon'] );

		// For unselected answer.
		$unselected = $activity->get_action( 'unselected' );
		$this->assertArrayHasKey( 'ref_type', $unselected );
		$this->assertArrayHasKey( 'verb', $unselected );
		$this->assertArrayHasKey( 'icon', $unselected );
		$this->assertEquals( 'answer', $unselected['ref_type'] );
		$this->assertEquals( 'Unselected an answer', $unselected['verb'] );
		$this->assertEquals( 'apicon-check', $unselected['icon'] );
	}
}
