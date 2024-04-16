<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestReputationQuery extends TestCase {

	use Testcases\Common;

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

		$reputation = new \AnsPress_Reputation_Query();

		// Test 1.
		$reputation->count = 0;
		$this->assertFalse( $reputation->has() );

		// Test 2.
		$reputation->count = 1;
		$this->assertTrue( $reputation->has() );

		// Test 3.
		$reputation->count = 2;
		$this->assertTrue( $reputation->has() );
	}

	/**
	 * @covers AnsPress_Reputation_Query::have
	 */
	public function testHaveShouldReturnTrueIfReputationsAvailable() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		// Insert some reputations.
		$this->setRole( 'subscriber' );
		$q_id_1 = $this->insert_question();
		ap_insert_reputation( 'ask', $q_id_1 );
		$q_id_2 = $this->insert_question();
		ap_insert_reputation( 'ask', $q_id_2 );
		$q_id_3 = $this->insert_question();
		ap_insert_reputation( 'ask', $q_id_3 );

		// Test.
		$reputation = new \AnsPress_Reputation_Query( [ 'user_id' => get_current_user_id() ] );
		$this->assertTrue( $reputation->have() );
		$this->assertNull( $reputation->in_the_loop );
	}

	/**
	 * @covers AnsPress_Reputation_Query::have
	 */
	public function testHaveShouldReturnFalseIfNoReputationsAvailable() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		$this->setRole( 'subscriber' );
		$reputation = new \AnsPress_Reputation_Query( [ 'user_id' => get_current_user_id() ] );
		$this->assertFalse( $reputation->have() );
		$this->assertEquals( false, $reputation->in_the_loop );
	}

	/**
	 * @covers AnsPress_Reputation_Query::have
	 */
	public function testHaveShouldShouldFireAPReputationsLoopEndActionHookAfterAllReputationEventIsLoopedThrough() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		// Callback triggered.
		$callback_triggered = false;
		add_action( 'ap_reputations_loop_end', function() use ( &$callback_triggered ) {
			$callback_triggered = true;
		} );

		// Test begins.
		$this->setRole( 'subscriber' );
		$q_id_1 = $this->insert_question();
		ap_insert_reputation( 'ask', $q_id_1 );
		$q_id_2 = $this->insert_question();
		ap_insert_reputation( 'ask', $q_id_2 );
		$q_id_3 = $this->insert_question();
		ap_insert_reputation( 'ask', $q_id_3 );
		$reputation = new \AnsPress_Reputation_Query( [ 'user_id' => get_current_user_id() ] );

		// Test 1.
		$reputation->reputations[0];
		$reputation->the_reputation();
		$this->assertTrue( $reputation->have() );
		$this->assertFalse( $callback_triggered );

		// Test 2.
		$reputation->reputations[1];
		$reputation->the_reputation();
		$this->assertTrue( $reputation->have() );
		$this->assertFalse( $callback_triggered );

		// Test 3.
		$reputation->reputations[2];
		$reputation->the_reputation();
		$this->assertFalse( $reputation->have() );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_reputations_loop_end' ) > 0 );
		$this->assertEquals( -1, $reputation->current );
		$this->assertEquals( $reputation->reputation, $reputation->reputations[0] );
	}

	/**
	 * @covers AnsPress_Reputation_Query::rewind_reputation
	 */
	public function testRewindReputation() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		// Insert some reputations.
		$this->setRole( 'subscriber' );
		$q_id_1 = $this->insert_question();
		ap_insert_reputation( 'ask', $q_id_1 );
		$q_id_2 = $this->insert_question();
		ap_insert_reputation( 'ask', $q_id_2 );
		$q_id_3 = $this->insert_question();
		ap_insert_reputation( 'ask', $q_id_3 );

		// Test begins.
		$reputation = new \AnsPress_Reputation_Query( [ 'user_id' => get_current_user_id() ] );

		// Before calling the method.
		// Test 1.
		$reputation->reputations[0];
		$reputation->the_reputation();
		$this->assertEquals( $reputation->reputations[0], $reputation->reputation );
		$this->assertEquals( 0, $reputation->current );

		// Test 2.
		$reputation->reputations[1];
		$reputation->the_reputation();
		$this->assertEquals( $reputation->reputations[1], $reputation->reputation );
		$this->assertEquals( 1, $reputation->current );

		// Test 3.
		$reputation->reputations[2];
		$reputation->the_reputation();
		$this->assertEquals( $reputation->reputations[2], $reputation->reputation );
		$this->assertEquals( 2, $reputation->current );

		// After calling the method.
		$reputation->rewind_reputation();
		$this->assertEquals( $reputation->reputations[0], $reputation->reputation );
		$this->assertEquals( -1, $reputation->current );
	}

	/**
	 * @covers AnsPress_Reputation_Query::next_reputation
	 */
	public function testNextReputation() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		// Insert some reputations.
		$this->setRole( 'subscriber' );
		$q_id_1 = $this->insert_question();
		ap_insert_reputation( 'ask', $q_id_1 );
		$q_id_2 = $this->insert_question();
		ap_insert_reputation( 'ask', $q_id_2 );
		$q_id_3 = $this->insert_question();
		ap_insert_reputation( 'ask', $q_id_3 );

		// Test begins.
		$reputation = new \AnsPress_Reputation_Query( [ 'user_id' => get_current_user_id() ] );

		// Test 1.
		$result = $reputation->next_reputation();
		$this->assertEquals( 0, $reputation->current );
		$this->assertEquals( $reputation->reputations[0], $result );

		// Test 2.
		$result = $reputation->next_reputation();
		$this->assertEquals( 1, $reputation->current );
		$this->assertEquals( $reputation->reputations[1], $result );

		// Test 3.
		$result = $reputation->next_reputation();
		$this->assertEquals( 2, $reputation->current );
		$this->assertEquals( $reputation->reputations[2], $result );
	}

	/**
	 * @covers AnsPress_Reputation_Query::the_reputation
	 */
	public function testTheReputation() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		// Insert some reputations.
		$this->setRole( 'subscriber' );
		$q_id_1 = $this->insert_question();
		ap_insert_reputation( 'ask', $q_id_1 );
		$q_id_2 = $this->insert_question();
		ap_insert_reputation( 'ask', $q_id_2 );
		$q_id_3 = $this->insert_question();
		ap_insert_reputation( 'ask', $q_id_3 );

		// Test begins.
		$reputation = new \AnsPress_Reputation_Query( [ 'user_id' => get_current_user_id() ] );

		// Test 1.
		$reputation->reputations[0];
		$reputation->the_reputation();
		$this->assertTrue( $reputation->in_the_loop );
		$this->assertEquals( 0, $reputation->current );
		$this->assertEquals( $reputation->reputations[0], $reputation->reputation );

		// Test 2.
		$reputation->reputations[1];
		$reputation->the_reputation();
		$this->assertTrue( $reputation->in_the_loop );
		$this->assertEquals( 1, $reputation->current );
		$this->assertEquals( $reputation->reputations[1], $reputation->reputation );

		// Test 3.
		$reputation->reputations[2];
		$reputation->the_reputation();
		$this->assertTrue( $reputation->in_the_loop );
		$this->assertEquals( 2, $reputation->current );
		$this->assertEquals( $reputation->reputations[2], $reputation->reputation );
	}

	/**
	 * @covers AnsPress_Reputation_Query::the_reputation
	 */
	public function testTheReputationShouldTriggerTheAPReputationLoopStartActionHookOnFirstLoopOnly() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		// Callback triggered.
		$callback_triggered = false;
		add_action( 'ap_reputation_loop_start', function() use ( &$callback_triggered ) {
			$callback_triggered = true;
		} );

		// Test begins.
		$this->setRole( 'subscriber' );
		$q_id_1 = $this->insert_question();
		ap_insert_reputation( 'ask', $q_id_1 );
		$q_id_2 = $this->insert_question();
		ap_insert_reputation( 'ask', $q_id_2 );
		$q_id_3 = $this->insert_question();
		ap_insert_reputation( 'ask', $q_id_3 );
		$reputation = new \AnsPress_Reputation_Query( [ 'user_id' => get_current_user_id() ] );

		// Test 1.
		$reputation->reputations[0];
		$reputation->the_reputation();
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_reputation_loop_start' ) > 0 );
		$this->assertTrue( $reputation->in_the_loop );
		$this->assertEquals( 0, $reputation->current );
		$this->assertEquals( $reputation->reputations[0], $reputation->reputation );

		// Test 2.
		$callback_triggered = false;
		$reputation->reputations[1];
		$reputation->the_reputation();
		$this->assertFalse( $callback_triggered );
		$this->assertTrue( $reputation->in_the_loop );
		$this->assertEquals( 1, $reputation->current );
		$this->assertEquals( $reputation->reputations[1], $reputation->reputation );

		// Test 3.
		$callback_triggered = false;
		$reputation->reputations[2];
		$reputation->the_reputation();
		$this->assertFalse( $callback_triggered );
		$this->assertTrue( $reputation->in_the_loop );
		$this->assertEquals( 2, $reputation->current );
		$this->assertEquals( $reputation->reputations[2], $reputation->reputation );
	}
}
