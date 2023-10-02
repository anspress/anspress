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

	/**
	 * @covers AnsPress\Activity_Helper::action_exists
	 */
	public function testAnsPressActivityHelperActionExists() {
		$activity = \AnsPress\Activity_Helper::get_instance();

		// Test begins.
		// For no activity action exists.
		$this->assertEmpty( $activity->action_exists( 'test_question' ) );
		$this->assertEmpty( $activity->action_exists( 'test_answer' ) );
		$this->assertEmpty( $activity->action_exists( 'test_comment' ) );

		// For activity action exists.
		$this->assertNotEmpty( $activity->action_exists( 'new_q' ) );
		$this->assertNotEmpty( $activity->action_exists( 'edit_q' ) );
		$this->assertNotEmpty( $activity->action_exists( 'new_a' ) );
		$this->assertNotEmpty( $activity->action_exists( 'edit_a' ) );
		$this->assertNotEmpty( $activity->action_exists( 'status_publish' ) );
		$this->assertNotEmpty( $activity->action_exists( 'status_future' ) );
		$this->assertNotEmpty( $activity->action_exists( 'status_moderate' ) );
		$this->assertNotEmpty( $activity->action_exists( 'status_private_post' ) );
		$this->assertNotEmpty( $activity->action_exists( 'status_trash' ) );
		$this->assertNotEmpty( $activity->action_exists( 'featured' ) );
		$this->assertNotEmpty( $activity->action_exists( 'closed_q' ) );
		$this->assertNotEmpty( $activity->action_exists( 'new_c' ) );
		$this->assertNotEmpty( $activity->action_exists( 'edit_c' ) );
		$this->assertNotEmpty( $activity->action_exists( 'selected' ) );
		$this->assertNotEmpty( $activity->action_exists( 'unselected' ) );
	}

	/**
	 * @covers AnsPress\Activity_Helper::insert
	 * @covers AnsPress\Activity_Helper::get_activity
	 */
	public function testAnsPressActivityHelperInsert() {
		$activity = \AnsPress\Activity_Helper::get_instance();
		$id = $this->insert_answer();

		// Test begins.
		$this->setRole( 'subscriber' );
		// Inserting new question.
		$new_q_id = $activity->insert( [ 'action' => 'new_q', 'q_id' => $id->q ] );
		$this->assertNotEmpty( $new_q_id );
		$this->assertIsInt( $new_q_id );
		$new_q_activity = $activity->get_activity( $new_q_id );
		$this->assertNotEmpty( $new_q_activity );
		$this->assertIsObject( $new_q_activity );
		$this->assertEquals( $new_q_id, $new_q_activity->activity_id );
		$this->assertEquals( 'new_q', $new_q_activity->activity_action );
		$this->assertEquals( $id->q, $new_q_activity->activity_q_id );
		$this->assertEquals( 0, $new_q_activity->activity_a_id );
		$this->assertEquals( 0, $new_q_activity->activity_c_id );
		$this->assertEquals( get_current_user_id(), $new_q_activity->activity_user_id );
		$this->assertEquals( current_time( 'mysql' ), $new_q_activity->activity_date );

		// Inserting new answer.
		$new_a_id = $activity->insert( [ 'action' => 'new_a', 'q_id' => $id->q, 'a_id' => $id->a ] );
		$this->assertNotEmpty( $new_a_id );
		$this->assertIsInt( $new_a_id );
		$new_a_activity = $activity->get_activity( $new_a_id );
		$this->assertNotEmpty( $new_a_activity );
		$this->assertIsObject( $new_a_activity );
		$this->assertEquals( $new_a_id, $new_a_activity->activity_id );
		$this->assertEquals( 'new_a', $new_a_activity->activity_action );
		$this->assertEquals( $id->q, $new_a_activity->activity_q_id );
		$this->assertEquals( $id->a, $new_a_activity->activity_a_id );
		$this->assertEquals( 0, $new_a_activity->activity_c_id );
		$this->assertEquals( get_current_user_id(), $new_a_activity->activity_user_id );
		$this->assertEquals( current_time( 'mysql' ), $new_a_activity->activity_date );

		// Inserting new comment on question.
		$q_c_id  = $this->factory->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $id->q,
				'user_id'         => get_current_user_id(),
			)
		);
		$new_c_id = $activity->insert( [ 'action' => 'new_c', 'q_id' => $id->q, 'c_id' => $q_c_id ] );
		$this->assertNotEmpty( $new_c_id );
		$this->assertIsInt( $new_c_id );
		$new_c_activity = $activity->get_activity( $new_c_id );
		$this->assertNotEmpty( $new_c_activity );
		$this->assertIsObject( $new_c_activity );
		$this->assertEquals( $new_c_id, $new_c_activity->activity_id );
		$this->assertEquals( 'new_c', $new_c_activity->activity_action );
		$this->assertEquals( $id->q, $new_c_activity->activity_q_id );
		$this->assertEquals( 0, $new_c_activity->activity_a_id );
		$this->assertEquals( $q_c_id, $new_c_activity->activity_c_id );
		$this->assertEquals( get_current_user_id(), $new_c_activity->activity_user_id );
		$this->assertEquals( current_time( 'mysql' ), $new_c_activity->activity_date );

		// Inserting new comment on answer.
		$a_c_id  = $this->factory->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $id->a,
				'user_id'         => get_current_user_id(),
			)
		);
		$new_c_id = $activity->insert( [ 'action' => 'new_c', 'q_id' => $id->q, 'a_id' => $id->a, 'c_id' => $a_c_id ] );
		$this->assertNotEmpty( $new_c_id );
		$this->assertIsInt( $new_c_id );
		$new_c_activity = $activity->get_activity( $new_c_id );
		$this->assertNotEmpty( $new_c_activity );
		$this->assertIsObject( $new_c_activity );
		$this->assertEquals( $new_c_id, $new_c_activity->activity_id );
		$this->assertEquals( 'new_c', $new_c_activity->activity_action );
		$this->assertEquals( $id->q, $new_c_activity->activity_q_id );
		$this->assertEquals( $id->a, $new_c_activity->activity_a_id );
		$this->assertEquals( $a_c_id, $new_c_activity->activity_c_id );
		$this->assertEquals( get_current_user_id(), $new_c_activity->activity_user_id );
		$this->assertEquals( current_time( 'mysql' ), $new_c_activity->activity_date );

		// Setting question as featured.
		$id = $this->insert_answer();
		$featured_q_id = $activity->insert( [ 'action' => 'featured', 'q_id' => $id->q ] );
		$this->assertNotEmpty( $featured_q_id );
		$this->assertIsInt( $featured_q_id );
		$featured_activity = $activity->get_activity( $featured_q_id );
		$this->assertNotEmpty( $featured_activity );
		$this->assertIsObject( $featured_activity );
		$this->assertEquals( $featured_q_id, $featured_activity->activity_id );
		$this->assertEquals( 'featured', $featured_activity->activity_action );
		$this->assertEquals( $id->q, $featured_activity->activity_q_id );
		$this->assertEquals( 0, $featured_activity->activity_a_id );
		$this->assertEquals( 0, $featured_activity->activity_c_id );
		$this->assertEquals( get_current_user_id(), $featured_activity->activity_user_id );
		$this->assertEquals( current_time( 'mysql' ), $featured_activity->activity_date );

		// Setting answer as selected.
		$id = $this->insert_answer();
		$selected_a_id = $activity->insert( [ 'action' => 'selected', 'q_id' => $id->q, 'a_id' => $id->a ] );
		$this->assertNotEmpty( $selected_a_id );
		$this->assertIsInt( $selected_a_id );
		$selected_activity = $activity->get_activity( $selected_a_id );
		$this->assertNotEmpty( $selected_activity );
		$this->assertIsObject( $selected_activity );
		$this->assertEquals( $selected_a_id, $selected_activity->activity_id );
		$this->assertEquals( 'selected', $selected_activity->activity_action );
		$this->assertEquals( $id->q, $selected_activity->activity_q_id );
		$this->assertEquals( $id->a, $selected_activity->activity_a_id );
		$this->assertEquals( 0, $selected_activity->activity_c_id );
		$this->assertEquals( get_current_user_id(), $selected_activity->activity_user_id );
		$this->assertEquals( current_time( 'mysql' ), $selected_activity->activity_date );

		// Test for invalids.
		$id = $this->insert_answer();
		// Test for no question id being passed.
		$invalid_activity_insert = $activity->insert( [ 'action' => 'featured' ] );
		$this->assertTrue( is_wp_error( $invalid_activity_insert ) );
		$invalid_activity_insert = $activity->insert( [ 'action' => 'selected', 'a_id' => $id->a ] );
		$this->assertTrue( is_wp_error( $invalid_activity_insert ) );

		// Test for no action being passed.
		$invalid_activity_insert = $activity->insert( [ 'q_id' => $id->q ] );
		$this->assertTrue( is_wp_error( $invalid_activity_insert ) );
		$invalid_activity_insert = $activity->insert( [ 'q_id' => $id->q, 'a_id' => $id->a ] );
		$this->assertTrue( is_wp_error( $invalid_activity_insert ) );

		// Test for invalid date being passed.
		$invalid_activity_insert = $activity->insert( [ 'action' => 'featured', 'q_id' => $id->q, 'date' => '0000 00 00' ] );
		$this->assertTrue( is_wp_error( $invalid_activity_insert ) );
		$invalid_activity_insert = $activity->insert( [ 'action' => 'featured', 'q_id' => $id->q, 'date' => '5555 55 55' ] );
		$this->assertTrue( is_wp_error( $invalid_activity_insert ) );
		$invalid_activity_insert = $activity->insert( [ 'action' => 'selected', 'q_id' => $id->q, 'a_id' => $id->a, 'date' => '0000 00 00' ] );
		$this->assertTrue( is_wp_error( $invalid_activity_insert ) );
		$invalid_activity_insert = $activity->insert( [ 'action' => 'selected', 'q_id' => $id->q, 'a_id' => $id->a, 'date' => '5555 55 55' ] );
		$this->assertTrue( is_wp_error( $invalid_activity_insert ) );

		// Test for invalid activity.
		$this->assertFalse( $activity->get_activity( 0 ) );
		$this->assertNull( $activity->get_activity( 1 ) );
		$this->assertEmpty( $activity->get_activity( 1 ) );
		$this->assertNull( $activity->get_activity( 'question' ) );
		$this->assertEmpty( $activity->get_activity( 'question' ) );
	}

	/**
	 * @covers AnsPress\Activity_Helper::delete
	 */
	public function testAnsPressActivityHelperDelete() {
		$activity = \AnsPress\Activity_Helper::get_instance();
		$id = $this->insert_answer();
		$this->setRole( 'subscriber' );

		// Test begins.
		// New question add and delete.
		$q_id = $activity->insert( [ 'action' => 'new_q', 'q_id' => $id->q ] );
		$this->assertNotEmpty( $q_id );
		$this->assertIsInt( $q_id );
		$q_activity = $activity->get_activity( $q_id );
		$this->assertNotEmpty( $q_activity );
		$this->assertIsObject( $q_activity );
		// Delete activity.
		$delete = $activity->delete( [ 'q_id' => $id->q ] );
		$this->assertIsInt( $delete );
		$this->assertEquals( 1, $delete );
		$new_q_activity = $activity->get_activity( $q_id );
		$this->assertNull( $new_q_activity );
		$this->assertEmpty( $new_q_activity );

		// New question actions add and delete.
		$q_id = $activity->insert( [ 'action' => 'new_q', 'q_id' => $id->q ] );
		$this->assertNotEmpty( $q_id );
		$this->assertIsInt( $q_id );
		$q_activity = $activity->get_activity( $q_id );
		$this->assertNotEmpty( $q_activity );
		$this->assertIsObject( $q_activity );
		// Inserting new question.
		$id = $this->insert_answer();
		$new_q_id = $activity->insert( [ 'action' => 'new_q', 'q_id' => $id->q ] );
		$this->assertNotEmpty( $new_q_id );
		$this->assertIsInt( $new_q_id );
		$new_q_activity = $activity->get_activity( $new_q_id );
		$this->assertNotEmpty( $new_q_activity );
		$this->assertIsObject( $new_q_activity );
		// Delete activity.
		$delete = $activity->delete( [ 'action' => 'new_q' ] );
		$this->assertIsInt( $delete );
		$this->assertEquals( 2, $delete );
		$q_activity = $activity->get_activity( $q_id );
		$this->assertNull( $q_activity );
		$this->assertEmpty( $q_activity );
		$new_q_activity = $activity->get_activity( $new_q_id );
		$this->assertNull( $new_q_activity );
		$this->assertEmpty( $new_q_activity );

		// User add and delete.
		$id = $this->insert_answer();
		$q_id = $activity->insert( [ 'action' => 'new_q', 'q_id' => $id->q, 'user_id' => get_current_user_id() ] );
		$this->assertNotEmpty( $q_id );
		$this->assertIsInt( $q_id );
		$q_activity = $activity->get_activity( $q_id );
		$this->assertNotEmpty( $q_activity );
		$this->assertIsObject( $q_activity );
		// Delete activity.
		$delete = $activity->delete( [ 'user_id' => get_current_user_id() ] );
		$this->assertIsInt( $delete );
		$this->assertEquals( 1, $delete );
		$new_q_activity = $activity->get_activity( $q_id );
		$this->assertNull( $new_q_activity );
		$this->assertEmpty( $new_q_activity );

		// Users add and delete.
		$id = $this->insert_answer();
		$q_id = $activity->insert( [ 'action' => 'new_q', 'q_id' => $id->q, 'user_id' => get_current_user_id() ] );
		$this->assertNotEmpty( $q_id );
		$this->assertIsInt( $q_id );
		$q_activity = $activity->get_activity( $q_id );
		$this->assertNotEmpty( $q_activity );
		$this->assertIsObject( $q_activity );
		// Inserting new user.
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$id = $this->insert_answer();
		$new_q_id = $activity->insert( [ 'action' => 'new_q', 'q_id' => $id->q, 'user_id' => $user_id ] );
		$this->assertNotEmpty( $new_q_id );
		$this->assertIsInt( $new_q_id );
		$new_q_activity = $activity->get_activity( $new_q_id );
		$this->assertNotEmpty( $new_q_activity );
		$this->assertIsObject( $new_q_activity );
		// Delete activity.
		$delete = $activity->delete( [ 'user_id' => get_current_user_id() ] );
		$this->assertIsInt( $delete );
		$this->assertEquals( 1, $delete );
		$q_activity = $activity->get_activity( $q_id );
		$this->assertNull( $q_activity );
		$this->assertEmpty( $q_activity );
		$delete = $activity->delete( [ 'user_id' => $user_id ] );
		$this->assertIsInt( $delete );
		$this->assertEquals( 1, $delete );
		$new_q_activity = $activity->get_activity( $new_q_id );
		$this->assertNull( $new_q_activity );
		$this->assertEmpty( $new_q_activity );

		// Test for invalids.
		$id = $this->insert_answer();
		$q_id = $activity->insert( [ 'action' => 'new_q', 'q_id' => $id->q ] );
		$this->assertNotEmpty( $q_id );
		$this->assertIsInt( $q_id );
		$this->assertTrue( is_wp_error( $activity->delete( [ 'test' => $id->q ] ) ) );
		$this->assertTrue( is_wp_error( $activity->delete( [ 'test_activity' => $id->q ] ) ) );
		$this->assertFalse( is_wp_error( $activity->delete( [ 'action' => 'test' ] ) ) );
		$this->assertFalse( is_wp_error( $activity->delete( [ 'action' => 'test_activity' ] ) ) );
		$this->assertEquals( 0, $activity->delete( [ 'action' => 'test' ] ) );
		$this->assertEquals( 0, $activity->delete( [ 'action' => 'test_activity' ] ) );
	}

	/**
	 * @covers AnsPress\Activity_Helper::before_delete
	 */
	public function testAnsPressActivityHelperBeforeDelete() {
		$activity = \AnsPress\Activity_Helper::get_instance();
		$this->setRole( 'subscriber' );

		// Test begins.
		// For invalid post type delete directly from function..
		$id = $this->factory->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_type'    => 'post',
			)
		);
		$q_id = $activity->insert( [ 'action' => 'new_q', 'q_id' => $id ] );
		$this->assertNotEmpty( $q_id );
		$this->assertIsInt( $q_id );
		$q_activity = $activity->get_activity( $q_id );
		$this->assertNotEmpty( $q_activity );
		$this->assertIsObject( $q_activity );
		$delete = $activity::_before_delete( $id );
		$this->assertNull( $delete );
		$q_activity = $activity->get_activity( $q_id );
		$this->assertNotEmpty( $q_activity );
		$this->assertIsObject( $q_activity );

		// For invalid post type delete from deleting WordPress posts function
		$id = $this->factory->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_type'    => 'post',
			)
		);
		$q_id = $activity->insert( [ 'action' => 'new_q', 'q_id' => $id ] );
		$this->assertNotEmpty( $q_id );
		$this->assertIsInt( $q_id );
		$q_activity = $activity->get_activity( $q_id );
		$this->assertNotEmpty( $q_activity );
		$this->assertIsObject( $q_activity );
		wp_delete_post( $id, true );
		$q_activity = $activity->get_activity( $q_id );
		$this->assertNotEmpty( $q_activity );
		$this->assertIsObject( $q_activity );

		// New question add and delete directly from function.
		$id = $this->insert_answer();
		$q_id = $activity->insert( [ 'action' => 'new_q', 'q_id' => $id->q ] );
		$this->assertNotEmpty( $q_id );
		$this->assertIsInt( $q_id );
		$q_activity = $activity->get_activity( $q_id );
		$this->assertNotEmpty( $q_activity );
		$this->assertIsObject( $q_activity );
		$activity::_before_delete( $id->q );
		$q_activity = $activity->get_activity( $q_id );
		$this->assertNull( $q_activity );
		$this->assertEmpty( $q_activity );

		// New question add and delete from deleting WordPress posts function.
		$id = $this->insert_answer();
		$q_id = $activity->insert( [ 'action' => 'new_q', 'q_id' => $id->q ] );
		$this->assertNotEmpty( $q_id );
		$this->assertIsInt( $q_id );
		$q_activity = $activity->get_activity( $q_id );
		$this->assertNotEmpty( $q_activity );
		$this->assertIsObject( $q_activity );
		wp_delete_post( $id->q, true );
		$q_activity = $activity->get_activity( $q_id );
		$this->assertNull( $q_activity );
		$this->assertEmpty( $q_activity );

		// New answer add and delete directly from function.
		$id = $this->insert_answer();
		$a_id = $activity->insert( [ 'action' => 'new_q', 'q_id' => $id->q, 'a_id' => $id->a ] );
		$this->assertNotEmpty( $a_id );
		$this->assertIsInt( $a_id );
		$q_activity = $activity->get_activity( $a_id );
		$this->assertNotEmpty( $q_activity );
		$this->assertIsObject( $q_activity );
		$activity::_before_delete( $id->a );
		$q_activity = $activity->get_activity( $a_id );
		$this->assertNull( $q_activity );
		$this->assertEmpty( $q_activity );

		// New answer add and delete from deleting WordPress posts function.
		$id = $this->insert_answer();
		$a_id = $activity->insert( [ 'action' => 'new_q', 'q_id' => $id->q, 'a_id' => $id->a ] );
		$this->assertNotEmpty( $a_id );
		$this->assertIsInt( $a_id );
		$q_activity = $activity->get_activity( $a_id );
		$this->assertNotEmpty( $q_activity );
		$this->assertIsObject( $q_activity );
		wp_delete_post( $id->a, true );
		$q_activity = $activity->get_activity( $a_id );
		$this->assertNull( $q_activity );
		$this->assertEmpty( $q_activity );
	}
}
