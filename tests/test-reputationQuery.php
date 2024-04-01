<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestReputationQuery extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress_Reputation_Query' );
		$this->assertTrue( $class->hasProperty( 'current' ) && $class->getProperty( 'current' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'count' ) && $class->getProperty( 'count' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'reputations' ) && $class->getProperty( 'reputations' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'reputation' ) && $class->getProperty( 'reputation' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'in_the_loop' ) && $class->getProperty( 'in_the_loop' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'total_count' ) && $class->getProperty( 'total_count' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'per_page' ) && $class->getProperty( 'per_page' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'total_pages' ) && $class->getProperty( 'total_pages' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'max_num_pages' ) && $class->getProperty( 'max_num_pages' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'paged' ) && $class->getProperty( 'paged' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'offset' ) && $class->getProperty( 'offset' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'with_zero_points' ) && $class->getProperty( 'with_zero_points' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'events' ) && $class->getProperty( 'events' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'ids' ) && $class->getProperty( 'ids' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'pos' ) && $class->getProperty( 'pos' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'args' ) && $class->getProperty( 'args' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'get_events_with_zero_points' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'query' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'prefetch' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'prefetch_posts' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'prefetch_comments' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'have' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'rewind_reputation' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'has' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'next_reputation' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'the_reputation' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'get_event' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'the_event' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'get_points' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'the_points' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'get_date' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'the_date' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'get_icon' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'the_icon' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'get_activity' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'the_activity' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'the_ref_content' ) );
	}

	/**
	 * @covers AnsPress_Reputation_Query::__construct
	 */
	public function testConstructor() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		$reputation = new \AnsPress_Reputation_Query();
		$this->assertInstanceOf( 'AnsPress_Reputation_Query', $reputation );
	}

	/**
	 * @covers AnsPress_Reputation_Query::__construct
	 */
	public function testConstructorWithoutArgs() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		$reputation = new \AnsPress_Reputation_Query();
		$this->assertInstanceOf( 'AnsPress_Reputation_Query', $reputation );

		// Tests.
		$this->assertEquals( ap_get_reputation_events(), $reputation->events );
		foreach ( ap_get_reputation_events() as $slug => $args ) {
			if ( 0 === $args['points'] ) {
				$this->assertTrue( in_array( $slug, $reputation->with_zero_points ) );
			}
		}
		$this->assertEquals( 1, $reputation->paged );
		$this->assertEquals( 0, $reputation->offset );
		$this->assertEquals( 0, $reputation->args['user_id'] );
		$this->assertEquals( 20, $reputation->args['number'] );
		$this->assertEquals( 0, $reputation->args['offset'] );
		$this->assertEquals( 'DESC', $reputation->args['order'] );
		$this->assertEquals( 20, $reputation->per_page );
	}

	/**
	 * @covers AnsPress_Reputation_Query::__construct
	 */
	public function testConstructorWithArgs() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		$user_id = $this->factory()->user->create();
		$reputation = new \AnsPress_Reputation_Query( [
			'user_id'       => $user_id,
			'number'        => 12,
			'offset'        => 2,
			'order'         => 'ASC',
			'paged'         => 2,
		] );
		$this->assertInstanceOf( 'AnsPress_Reputation_Query', $reputation );

		// Tests.
		$this->assertEquals( ap_get_reputation_events(), $reputation->events );
		foreach ( ap_get_reputation_events() as $slug => $args ) {
			if ( 0 === $args['points'] ) {
				$this->assertTrue( in_array( $slug, $reputation->with_zero_points ) );
			}
		}
		$this->assertEquals( 2, $reputation->paged );
		$this->assertEquals( 20, $reputation->offset );
		$this->assertEquals( $user_id, $reputation->args['user_id'] );
		$this->assertEquals( 12, $reputation->args['number'] );
		$this->assertEquals( 2, $reputation->args['offset'] );
		$this->assertEquals( 'ASC', $reputation->args['order'] );
		$this->assertEquals( 2, $reputation->args['paged'] );
		$this->assertEquals( 12, $reputation->per_page );
	}

	/**
	 * @covers AnsPress_Reputation_Query::get_events_with_zero_points
	 */
	public function testGetEventsWithZeroPoints() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		$reputation = new \AnsPress_Reputation_Query();
		$reputation->with_zero_points = [];
		$reputation->get_events_with_zero_points();
		$with_zero_points = $reputation->with_zero_points;
		$this->assertCount( 2, $with_zero_points );
		$this->assertTrue( in_array( 'given_vote_up', $with_zero_points ) );
		$this->assertTrue( in_array( 'given_vote_down', $with_zero_points ) );
	}

	/**
	 * @covers AnsPress_Reputation_Query::get_events_with_zero_points
	 */
	public function testGetEventsWithZeroPointsWithSomeNewEventsAdded() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		// Add new event.
		$args = [
			'label'         => 'Custom Test Event',
			'description'   => 'Custom event description',
			'icon'          => 'apicon-test',
			'activity'      => 'Test Activity',
			'parent'        => '',
			'points'        => 0,
			'rep_events_id' => 11,
		];
		ap_register_reputation_event( 'custom_test_event', $args );
		$reputation = new \AnsPress_Reputation_Query();
		$reputation->with_zero_points = [];
		$reputation->get_events_with_zero_points();
		$with_zero_points = $reputation->with_zero_points;
		$this->assertCount( 3, $with_zero_points );
		$this->assertTrue( in_array( 'given_vote_up', $with_zero_points ) );
		$this->assertTrue( in_array( 'given_vote_down', $with_zero_points ) );
		$this->assertTrue( in_array( 'custom_test_event', $with_zero_points ) );
	}

	/**
	 * @covers AnsPress_Reputation_Query::has
	 */
	public function testHas() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		$activity = new \AnsPress_Reputation_Query();

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
