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

	/**
	 * @covers AnsPress_Reputation_Query::get_event
	 */
	public function testGetEvent() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		// Insert some reputations.
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();
		ap_insert_reputation( 'ask', $id->q );
		ap_insert_reputation( 'answer', $id->a );
		ap_insert_reputation( 'received_vote_up', $id->a );

		// Test begins.
		$reputation = new \AnsPress_Reputation_Query( [ 'user_id' => get_current_user_id() ] );

		// Test 1.
		$reputation->reputations[0];
		$reputation->the_reputation();
		$this->assertEquals( 'ask', $reputation->get_event() );

		// Test 2.
		$reputation->reputations[1];
		$reputation->the_reputation();
		$this->assertEquals( 'answer', $reputation->get_event() );

		// Test 3.
		$reputation->reputations[2];
		$reputation->the_reputation();
		$this->assertEquals( 'received_vote_up', $reputation->get_event() );
	}

	/**
	 * @covers AnsPress_Reputation_Query::the_event
	 */
	public function testTheEvent() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		// Insert some reputations.
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();
		ap_insert_reputation( 'ask', $id->q );
		ap_insert_reputation( 'answer', $id->a );
		ap_insert_reputation( 'received_vote_up', $id->a );

		// Test begins.
		$reputation = new \AnsPress_Reputation_Query( [ 'user_id' => get_current_user_id() ] );

		// Test 1.
		$reputation->reputations[0];
		$reputation->the_reputation();
		ob_start();
		$reputation->the_event();
		$event = ob_get_clean();
		$this->assertEquals( 'ask', $event );

		// Test 2.
		$reputation->reputations[1];
		$reputation->the_reputation();
		ob_start();
		$reputation->the_event();
		$event = ob_get_clean();
		$this->assertEquals( 'answer', $event );

		// Test 3.
		$reputation->reputations[2];
		$reputation->the_reputation();
		ob_start();
		$reputation->the_event();
		$event = ob_get_clean();
		$this->assertEquals( 'received_vote_up', $event );
	}

	/**
	 * @covers AnsPress_Reputation_Query::get_points
	 */
	public function testGetPoints() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		// Insert some reputations.
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();
		ap_insert_reputation( 'ask', $id->q );
		ap_insert_reputation( 'answer', $id->a );
		ap_insert_reputation( 'received_vote_down', $id->a );

		// Test begins.
		$reputation = new \AnsPress_Reputation_Query( [ 'user_id' => get_current_user_id() ] );

		// Test 1.
		$reputation->reputations[0];
		$reputation->the_reputation();
		$this->assertEquals( 2, $reputation->get_points() );

		// Test 2.
		$reputation->reputations[1];
		$reputation->the_reputation();
		$this->assertEquals( 5, $reputation->get_points() );

		// Test 3.
		$reputation->reputations[2];
		$reputation->the_reputation();
		$this->assertEquals( -2, $reputation->get_points() );
	}

	/**
	 * @covers AnsPress_Reputation_Query::the_points
	 */
	public function testThePoints() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		// Insert some reputations.
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();
		ap_insert_reputation( 'ask', $id->q );
		ap_insert_reputation( 'answer', $id->a );
		ap_insert_reputation( 'received_vote_down', $id->a );

		// Test begins.
		$reputation = new \AnsPress_Reputation_Query( [ 'user_id' => get_current_user_id() ] );

		// Test 1.
		$reputation->reputations[0];
		$reputation->the_reputation();
		ob_start();
		$reputation->the_points();
		$points = ob_get_clean();
		$this->assertEquals( 2, $points );

		// Test 2.
		$reputation->reputations[1];
		$reputation->the_reputation();
		ob_start();
		$reputation->the_points();
		$points = ob_get_clean();
		$this->assertEquals( 5, $points );

		// Test 3.
		$reputation->reputations[2];
		$reputation->the_reputation();
		ob_start();
		$reputation->the_points();
		$points = ob_get_clean();
		$this->assertEquals( -2, $points );
	}

	/**
	 * @covers AnsPress_Reputation_Query::get_date
	 */
	public function testGetDate() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		// Insert some reputations.
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();
		ap_insert_reputation( 'ask', $id->q );
		ap_insert_reputation( 'answer', $id->a );
		ap_insert_reputation( 'received_vote_down', $id->a );

		// Test begins.
		$reputation = new \AnsPress_Reputation_Query( [ 'user_id' => get_current_user_id() ] );
		foreach ( $reputation->reputations as $rep ) {
			$reputation->the_reputation();
			$this->assertEquals( current_time( 'mysql', true ), $reputation->get_date() );
		}
	}

	/**
	 * @covers AnsPress_Reputation_Query::the_date
	 */
	public function testTheDate() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		// Insert some reputations.
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();
		ap_insert_reputation( 'ask', $id->q );
		ap_insert_reputation( 'answer', $id->a );
		ap_insert_reputation( 'received_vote_down', $id->a );

		// Test begins.
		$reputation = new \AnsPress_Reputation_Query( [ 'user_id' => get_current_user_id() ] );
		foreach ( $reputation->reputations as $rep ) {
			$reputation->the_reputation();
			ob_start();
			$reputation->the_date();
			$date = ob_get_clean();
			$this->assertEquals( ap_human_time( current_time( 'mysql', true ), false ), $date );
		}
	}

	/**
	 * @covers AnsPress_Reputation_Query::get_icon
	 */
	public function testGetIcon() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		// Insert some reputations.
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();
		ap_insert_reputation( 'ask', $id->q );
		ap_insert_reputation( 'answer', $id->a );
		ap_insert_reputation( 'received_vote_down', $id->a );

		// Test begins.
		$reputation = new \AnsPress_Reputation_Query( [ 'user_id' => get_current_user_id() ] );

		// Test 1.
		$reputation->reputations[0];
		$reputation->the_reputation();
		$this->assertEquals( 'apicon-question', $reputation->get_icon() );

		// Test 2.
		$reputation->reputations[1];
		$reputation->the_reputation();
		$this->assertEquals( 'apicon-answer', $reputation->get_icon() );

		// Test 3.
		$reputation->reputations[2];
		$reputation->the_reputation();
		$this->assertEquals( 'apicon-thumb-down', $reputation->get_icon() );
	}

	/**
	 * @covers AnsPress_Reputation_Query::the_icon
	 */
	public function testTheIcon() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		// Insert some reputations.
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();
		ap_insert_reputation( 'ask', $id->q );
		ap_insert_reputation( 'answer', $id->a );
		ap_insert_reputation( 'received_vote_down', $id->a );

		// Test begins.
		$reputation = new \AnsPress_Reputation_Query( [ 'user_id' => get_current_user_id() ] );

		// Test 1.
		$reputation->reputations[0];
		$reputation->the_reputation();
		ob_start();
		$reputation->the_icon();
		$icon = ob_get_clean();
		$this->assertEquals( 'apicon-question', $icon );

		// Test 2.
		$reputation->reputations[1];
		$reputation->the_reputation();
		ob_start();
		$reputation->the_icon();
		$icon = ob_get_clean();
		$this->assertEquals( 'apicon-answer', $icon );

		// Test 3.
		$reputation->reputations[2];
		$reputation->the_reputation();
		ob_start();
		$reputation->the_icon();
		$icon = ob_get_clean();
		$this->assertEquals( 'apicon-thumb-down', $icon );
	}

	/**
	 * @covers AnsPress_Reputation_Query::get_activity
	 */
	public function testGetActivity() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		// Insert some reputations.
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();
		ap_insert_reputation( 'ask', $id->q );
		ap_insert_reputation( 'answer', $id->a );
		ap_insert_reputation( 'received_vote_down', $id->a );

		// Test begins.
		$reputation = new \AnsPress_Reputation_Query( [ 'user_id' => get_current_user_id() ] );

		// Test 1.
		$reputation->reputations[0];
		$reputation->the_reputation();
		$this->assertEquals( 'Asked a question', $reputation->get_activity() );

		// Test 2.
		$reputation->reputations[1];
		$reputation->the_reputation();
		$this->assertEquals( 'Posted an answer', $reputation->get_activity() );

		// Test 3.
		$reputation->reputations[2];
		$reputation->the_reputation();
		$this->assertEquals( 'Received a down vote', $reputation->get_activity() );
	}

	/**
	 * @covers AnsPress_Reputation_Query::the_activity
	 */
	public function testTheActivity() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		// Insert some reputations.
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();
		ap_insert_reputation( 'ask', $id->q );
		ap_insert_reputation( 'answer', $id->a );
		ap_insert_reputation( 'received_vote_down', $id->a );

		// Test begins.
		$reputation = new \AnsPress_Reputation_Query( [ 'user_id' => get_current_user_id() ] );

		// Test 1.
		$reputation->reputations[0];
		$reputation->the_reputation();
		ob_start();
		$reputation->the_activity();
		$activity = ob_get_clean();
		$this->assertEquals( 'Asked a question', $activity );

		// Test 2.
		$reputation->reputations[1];
		$reputation->the_reputation();
		ob_start();
		$reputation->the_activity();
		$activity = ob_get_clean();
		$this->assertEquals( 'Posted an answer', $activity );

		// Test 3.
		$reputation->reputations[2];
		$reputation->the_reputation();
		ob_start();
		$reputation->the_activity();
		$activity = ob_get_clean();
		$this->assertEquals( 'Received a down vote', $activity );
	}

	/**
	 * @covers AnsPress_Reputation_Query::the_ref_content
	 */
	public function testTheRefContentForQuestionSetAsParent() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		// Insert some reputations.
		$this->setRole( 'subscriber' );
		$id = $this->factory()->post->create( [
			'post_type'    => 'question',
			'post_title'   => 'Question Title',
			'post_content' => 'Question Content',
		] );
		ap_insert_reputation( 'ask', $id );

		// Test begins.
		$reputation = new \AnsPress_Reputation_Query( [ 'user_id' => get_current_user_id() ] );
		$reputation->reputations[0];
		$reputation->the_reputation();
		ob_start();
		$reputation->the_ref_content();
		$content = ob_get_clean();
		$this->assertStringContainsString( '<a class="ap-reputation-ref" href="' . esc_url( ap_get_short_link( array( 'ap_p' => $id ) ) ) . '">', $content );
		$this->assertStringContainsString( '<strong>Question Title</strong>', $content );
		$this->assertStringContainsString( '<p>Question Content</p>', $content );
	}

	/**
	 * @covers AnsPress_Reputation_Query::the_ref_content
	 */
	public function testTheRefContentForAnswerSetAsParent() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		// Insert some reputations.
		$this->setRole( 'subscriber' );
		$question_id = $this->factory()->post->create( [
			'post_type'    => 'question',
			'post_title'   => 'Question Title',
			'post_content' => 'Question Content',
		] );
		$answer_id = $this->factory()->post->create( [
			'post_type'    => 'answer',
			'post_title'   => 'Answer Title',
			'post_content' => 'Answer Content',
			'post_parent'  => $question_id,
		] );
		ap_insert_reputation( 'answer', $answer_id );

		// Test begins.
		$reputation = new \AnsPress_Reputation_Query( [ 'user_id' => get_current_user_id() ] );
		$reputation->reputations[0];
		$reputation->the_reputation();
		ob_start();
		$reputation->the_ref_content();
		$content = ob_get_clean();
		$this->assertStringContainsString( '<a class="ap-reputation-ref" href="' . esc_url( ap_get_short_link( array( 'ap_p' => $answer_id ) ) ) . '">', $content );
		$this->assertStringContainsString( '<strong>Answer Title</strong>', $content );
		$this->assertStringContainsString( '<p>Answer Content</p>', $content );
	}

	/**
	 * @covers AnsPress_Reputation_Query::the_ref_content
	 */
	public function testTheRefContentForCommentSetAsParent() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		// Insert some reputations.
		$this->setRole( 'subscriber' );
		$question_id = $this->factory()->post->create( [
			'post_type'    => 'question',
			'post_title'   => 'Question Title',
			'post_content' => 'Question Content',
		] );
		$comment_id = $this->factory()->comment->create( [
			'comment_post_ID' => $question_id,
			'comment_content' => 'Comment Content',
			'comment_type'    => 'anspres',
		] );
		ap_insert_reputation( 'comment', $comment_id );

		// Test begins.
		$reputation = new \AnsPress_Reputation_Query( [ 'user_id' => get_current_user_id() ] );
		$reputation->reputations[0];
		$reputation->the_reputation();
		ob_start();
		$reputation->the_ref_content();
		$content = ob_get_clean();
		$this->assertStringContainsString( '<a class="ap-reputation-ref" href="' . esc_url( ap_get_short_link( array( 'ap_c' => $comment_id ) ) ) . '">', $content );
		$this->assertStringContainsString( '<p>Comment Content</p>', $content );
	}
}
