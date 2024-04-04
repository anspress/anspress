<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnsPress_Query extends TestCase {

	use Testcases\Common;

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress_Query' );
		$this->assertTrue( $class->hasProperty( 'current' ) && $class->getProperty( 'current' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'count' ) && $class->getProperty( 'count' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'objects' ) && $class->getProperty( 'objects' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'object' ) && $class->getProperty( 'object' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'in_the_loop' ) && $class->getProperty( 'in_the_loop' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'total_count' ) && $class->getProperty( 'total_count' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'per_page' ) && $class->getProperty( 'per_page' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'total_pages' ) && $class->getProperty( 'total_pages' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'paged' ) && $class->getProperty( 'paged' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'offset' ) && $class->getProperty( 'offset' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'args' ) && $class->getProperty( 'args' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'ids' ) && $class->getProperty( 'ids' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'pos' ) && $class->getProperty( 'pos' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Query', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress_Query', 'total_count' ) );
		$this->assertTrue( method_exists( 'AnsPress_Query', 'query' ) );
		$this->assertTrue( method_exists( 'AnsPress_Query', 'have' ) );
		$this->assertTrue( method_exists( 'AnsPress_Query', 'rewind' ) );
		$this->assertTrue( method_exists( 'AnsPress_Query', 'has' ) );
		$this->assertTrue( method_exists( 'AnsPress_Query', 'next' ) );
		$this->assertTrue( method_exists( 'AnsPress_Query', 'the_object' ) );
		$this->assertTrue( method_exists( 'AnsPress_Query', 'add_prefetch_id' ) );
		$this->assertTrue( method_exists( 'AnsPress_Query', 'add_pos' ) );
		$this->assertTrue( method_exists( 'AnsPress_Query', 'append_ref_data' ) );
		$this->assertTrue( method_exists( 'AnsPress_Query', 'template' ) );
		$this->assertTrue( method_exists( 'AnsPress_Query', 'have_pages' ) );
	}

	/**
	 * @covers AnsPress\Activity::total_count
	 */
	public function testTotalCountWithUserIDArg() {
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();

		// Add some user activity.
		ap_activity_add(
			[
				'action' => 'new_q',
				'q_id'   => $id->q,
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $id->q,
				'a_id'   => $id->a,
			]
		);

		// Test.
		$activity = new \AnsPress\Activity( [ 'user_id' => get_current_user_id() ] );
		$this->assertEquals( 2, $activity->total_count );
	}

	/**
	 * @covers AnsPress\Activity::total_count
	 */
	public function testTotalCountWithAIDArg() {
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create();
		$id = $this->insert_answer();

		// Add some user activity.
		ap_activity_add(
			[
				'action' => 'new_q',
				'q_id'   => $id->q,
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $id->q,
				'a_id'   => $id->a,
			]
		);
		ap_activity_add(
			[
				'action'  => 'new_a',
				'q_id'    => $id->q,
				'a_id'    => $id->a,
				'user_id' => $user_id,
			]
		);

		// Test.
		$activity = new \AnsPress\Activity( [ 'a_id' => $id->a ] );
		$this->assertEquals( 2, $activity->total_count );
	}

	/**
	 * @covers AnsPress\Activity::has
	 */
	public function testHas() {
		$activity = new \AnsPress\Activity();

		// Test 1.
		$activity->count = 0;
		$this->assertFalse( $activity->has() );

		// Test 2.
		$activity->count = 1;
		$this->assertTrue( $activity->has() );

		// Test 3.
		$activity->count = 2;
		$this->assertTrue( $activity->has() );
	}

	/**
	 * @covers AnsPress\Activity::the_object
	 */
	public function testTheObject() {
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();

		// Add some user activity.
		ap_activity_add(
			[
				'action' => 'new_q',
				'q_id'   => $id->q,
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $id->q,
				'a_id'   => $id->a,
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $id->q,
				'a_id'   => $id->a,
			]
		);

		// Test.
		$activities = new \AnsPress\Activity( [ 'q_id' => $id->q ] );
		$activities->in_the_loop = false;
		$activities->object = null;
		foreach ( $activities->objects as $activity ) {
			$activities->the_object();

			$this->assertTrue( $activities->in_the_loop );
			$this->assertNotNull( $activities->object );
			$this->assertEquals( $activity, $activities->object );
		}
	}

	/**
	 * @covers AnsPress\Activity::the_object
	 */
	public function testTheObjectForHookTriggered() {
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();

		// Action hook callback triggered.
		$callback_triggered = false;
		add_action( 'ap_loop_start', function () use ( &$callback_triggered ) {
			$callback_triggered = true;
		} );

		// Add some user activity.
		ap_activity_add(
			[
				'action' => 'new_q',
				'q_id'   => $id->q,
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $id->q,
				'a_id'   => $id->a,
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $id->q,
				'a_id'   => $id->a,
			]
		);

		// Tests.
		$activities = new \AnsPress\Activity( [ 'q_id' => $id->q ] );
		foreach ( $activities->objects as $activity ) {
			$activities->the_object();
		}
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_loop_start' ) === 1 );
		$this->assertFalse( did_action( 'ap_loop_start' ) === count( $activities->objects ) );
	}

	/**
	 * @covers AnsPress\Activity::have_pages
	 */
	public function testHavePagesShouldReturnTrueIfTotalPagesIsGreaterThanPaged() {
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();

		// Add some user activity.
		ap_activity_add(
			[
				'action' => 'new_q',
				'q_id'   => $id->q,
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $id->q,
				'a_id'   => $id->a,
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $id->q,
				'a_id'   => $id->a,
			]
		);

		// Test.
		$activities = new \AnsPress\Activity( [ 'q_id' => $id->q, 'number' => 2, 'paged' => 1 ] );
		$this->assertTrue( $activities->have_pages() );
	}

	/**
	 * @covers AnsPress\Activity::have_pages
	 */
	public function testHavePagesShouldReturnFalseIfTotalPagesIsLessThanPaged() {
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();

		// Add some user activity.
		ap_activity_add(
			[
				'action' => 'new_q',
				'q_id'   => $id->q,
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $id->q,
				'a_id'   => $id->a,
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $id->q,
				'a_id'   => $id->a,
			]
		);

		// Test.
		$activities = new \AnsPress\Activity( [ 'q_id' => $id->q, 'number' => 4, 'paged' => 2 ] );
		$this->assertFalse( $activities->have_pages() );
	}

	/**
	 * @covers AnsPress\Activity::have_pages
	 */
	public function testHavePagesShouldReturnFalseIfTotalPagesIsEqualToPaged() {
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();

		// Add some user activity.
		ap_activity_add(
			[
				'action' => 'new_q',
				'q_id'   => $id->q,
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $id->q,
				'a_id'   => $id->a,
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $id->q,
				'a_id'   => $id->a,
			]
		);

		// Test.
		$activities = new \AnsPress\Activity( [ 'q_id' => $id->q, 'number' => 3, 'paged' => 1 ] );
		$this->assertFalse( $activities->have_pages() );
	}

	/**
	 * @covers AnsPress\Activity::next
	 */
	public function testNext() {
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();

		// Add some user activity.
		ap_activity_add(
			[
				'action' => 'new_q',
				'q_id'   => $id->q,
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $id->q,
				'a_id'   => $id->a,
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $id->q,
				'a_id'   => $id->a,
			]
		);

		// Test.
		$activities = new \AnsPress\Activity( [ 'q_id' => $id->q ] );
		foreach ( $activities->objects as $activity ) {
			$result = $activities->next();
			$this->assertSame( $activity, $result );
		}
	}

	/**
	 * @covers AnsPress\Activity::rewind
	 */
	public function testRewind() {
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();

		// Add some user activity.
		ap_activity_add(
			[
				'action' => 'new_q',
				'q_id'   => $id->q,
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $id->q,
				'a_id'   => $id->a,
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $id->q,
				'a_id'   => $id->a,
			]
		);

		// Test.
		$activities = new \AnsPress\Activity( [ 'q_id' => $id->q ] );
		$this->assertEquals( $activities->objects[0], $activities->next() );
		$this->assertEquals( $activities->objects[1], $activities->next() );
		$this->assertEquals( $activities->objects[2], $activities->next() );
		$this->assertNotEquals( -1, $activities->current );

		// Test for rewind.
		$activities->rewind();
		$this->assertEquals( -1, $activities->current );
		$this->assertEquals( $activities->objects[0], $activities->next() );
	}

	/**
	 * @covers AnsPress\Activity::have
	 */
	public function testHaveShouldReturnTrueIfThereAreActivitiesAvailable() {
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();

		// Add some user activity.
		ap_activity_add(
			[
				'action' => 'new_q',
				'q_id'   => $id->q,
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $id->q,
				'a_id'   => $id->a,
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $id->q,
				'a_id'   => $id->a,
			]
		);

		// Test.
		$activities = new \AnsPress\Activity( [ 'q_id' => $id->q ] );
		$this->assertTrue( $activities->have() );
		$activities->next();
		$this->assertTrue( $activities->have() );
		$activities->next();
		$this->assertTrue( $activities->have() );
		$activities->next();
		$this->assertFalse( $activities->have() );
	}

	/**
	 * @covers AnsPress\Activity::have
	 */
	public function testHaveShouldReturnFalseIfThereAreNoActivitiesAvailable() {
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();

		// Test.
		$activities = new \AnsPress\Activity( [ 'q_id' => $id->q ] );
		$this->assertFalse( $activities->have() );
		$this->assertFalse( $activities->in_the_loop );
	}

	/**
	 * @covers AnsPress\Activity::have
	 */
	public function testHaveShouldTriggerActionHookIfCurrentPlusOneAndObjectCountMatches() {
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();

		// Action hook callback triggered.
		$callback_triggered = false;
		add_action( 'ap_loop_end', function () use ( &$callback_triggered ) {
			$callback_triggered = true;
		} );

		// Add some user activity.
		ap_activity_add(
			[
				'action' => 'new_q',
				'q_id'   => $id->q,
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $id->q,
				'a_id'   => $id->a,
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $id->q,
				'a_id'   => $id->a,
			]
		);

		// Test.
		$activities = new \AnsPress\Activity( [ 'q_id' => $id->q ] );
		$this->assertTrue( $activities->have() );
		$activities->next();
		$this->assertTrue( $activities->have() );
		$activities->next();
		$this->assertTrue( $activities->have() );
		$activities->next();
		$this->assertFalse( $activities->have() );

		// For action hook.
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_loop_end' ) === 1 );
		$this->assertFalse( did_action( 'ap_loop_end' ) === count( $activities->objects ) );
		$this->assertEquals( -1, $activities->current );
		$this->assertEquals( $activities->objects[0], $activities->object );
	}
}
