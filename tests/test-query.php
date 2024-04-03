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
}
