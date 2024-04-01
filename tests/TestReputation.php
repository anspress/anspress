<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestReputation extends TestCase {

	use Testcases\Common;

	public function set_up() {
		parent::set_up();

		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->prefix}ap_reputation_events" );
		$wpdb->query( "DELETE FROM {$wpdb->ap_reputations}" );

		anspress()->reputation_events = [];
	}

	public function testAPInsertReputation() {
		anspress()->reputation_events = [];
		$args = array(
			'slug'        => 'test',
			'points'      => 455433,
			'label'       => __( 'Asking', 'anspress-question-answer' ),
			'description' => __( 'Points awarded when user asks a question', 'anspress-question-answer' ),
			'icon'        => 'apicon-question',
			'activity'    => __( 'Asked a question', 'anspress-question-answer' ),
			'parent'      => 'question',
		);
		ap_register_reputation_event( 'test', $args );

		$question_id = $this->factory()->post->create_and_get( array( 'post_type' => 'question' ) );
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );

		ap_insert_reputation( 'test', $question_id->ID, $user_id );

		$this->assertEquals( 455433, ap_get_user_reputation( $user_id ) );
	}

	public function testApInsertReputationOnDisabed() {
		ap_opt('disable_reputation', true);

		anspress()->reputation_events = [];
		$args = array(
			'slug'        => 'test',
			'points'      => 455433,
			'label'       => __( 'Asking', 'anspress-question-answer' ),
			'description' => __( 'Points awarded when user asks a question', 'anspress-question-answer' ),
			'icon'        => 'apicon-question',
			'activity'    => __( 'Asked a question', 'anspress-question-answer' ),
			'parent'      => 'question',
			'disabled'    => true,
		);
		ap_register_reputation_event( 'test', $args );

		$question_id = $this->factory()->post->create_and_get( array( 'post_type' => 'question' ) );
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );

		ap_insert_reputation( 'test', $question_id->ID, $user_id );

		$this->assertEquals( 455433, ap_get_user_reputation( $user_id ) );
	}

	/**
	 * @covers ::ap_get_reputation
	 */
	public function testAPGetReputation() {
		global $wpdb;

		$id = $this->insert_answer();

		// Test begins.
		$this->setRole( 'subscriber' );
		$this->assertNull( ap_get_reputation( 'ask', $id->q ) );
		$this->assertNull( ap_get_reputation( 'answer', $id->a ) );
		$this->assertNull( ap_get_reputation( 'best_answer', $id->a ) );
		$this->assertNull( ap_get_reputation( 'select_answer', $id->a ) );
		$this->assertNull( ap_get_reputation( 'received_vote_up', $id->q ) );
		$this->assertNull( ap_get_reputation( 'received_vote_down', $id->q ) );
		$this->assertNull( ap_get_reputation( 'given_vote_up', $id->q ) );
		$this->assertNull( ap_get_reputation( 'given_vote_down', $id->q ) );

		// After inserting reputation.
		ap_insert_reputation( '', $id->q );
		$this->assertNull( ap_get_reputation( '', $id->q ) );
		ap_insert_reputation( 'ask', $id->q );
		$get_reputation = ap_get_reputation( 'ask', $id->q );
		$this->assertNotEmpty( $get_reputation );
		$this->assertEquals( get_current_user_id(), $get_reputation->rep_user_id );
		$this->assertEquals( 'ask', $get_reputation->rep_event );
		$this->assertEquals( $id->q, $get_reputation->rep_ref_id );
		$this->assertObjectHasProperty( 'rep_id', $get_reputation );
		$this->assertObjectHasProperty( 'rep_user_id', $get_reputation );
		$this->assertObjectHasProperty( 'rep_event', $get_reputation );
		$this->assertObjectHasProperty( 'rep_ref_id', $get_reputation );
		$this->assertObjectHasProperty( 'rep_date', $get_reputation );
		ap_insert_reputation( 'best_answer', $id->a );
		$get_reputation = ap_get_reputation( 'best_answer', $id->a );
		$this->assertNotEmpty( $get_reputation );
		$this->assertEquals( get_current_user_id(), $get_reputation->rep_user_id );
		$this->assertEquals( 'best_answer', $get_reputation->rep_event );
		$this->assertEquals( $id->a, $get_reputation->rep_ref_id );
		$this->assertObjectHasProperty( 'rep_id', $get_reputation );
		$this->assertObjectHasProperty( 'rep_user_id', $get_reputation );
		$this->assertObjectHasProperty( 'rep_event', $get_reputation );
		$this->assertObjectHasProperty( 'rep_ref_id', $get_reputation );
		$this->assertObjectHasProperty( 'rep_date', $get_reputation );
		ap_insert_reputation( 'best_answer', $id->a );
		$get_reputation = ap_get_reputation( 'best_answer', $id->a );
		$this->assertNotEmpty( $get_reputation );
		$this->assertEquals( get_current_user_id(), $get_reputation->rep_user_id );
		$this->assertEquals( 'best_answer', $get_reputation->rep_event );
		$this->assertEquals( $id->a, $get_reputation->rep_ref_id );
		$this->assertObjectHasProperty( 'rep_id', $get_reputation );
		$this->assertObjectHasProperty( 'rep_user_id', $get_reputation );
		$this->assertObjectHasProperty( 'rep_event', $get_reputation );
		$this->assertObjectHasProperty( 'rep_ref_id', $get_reputation );
		$this->assertObjectHasProperty( 'rep_date', $get_reputation );
		ap_insert_reputation( 'received_vote_up', $id->q );
		$get_reputation = ap_get_reputation( 'received_vote_up', $id->q );
		$this->assertNotEmpty( $get_reputation );
		$this->assertEquals( get_current_user_id(), $get_reputation->rep_user_id );
		$this->assertEquals( 'received_vote_up', $get_reputation->rep_event );
		$this->assertEquals( $id->q, $get_reputation->rep_ref_id );
		$this->assertObjectHasProperty( 'rep_id', $get_reputation );
		$this->assertObjectHasProperty( 'rep_user_id', $get_reputation );
		$this->assertObjectHasProperty( 'rep_event', $get_reputation );
		$this->assertObjectHasProperty( 'rep_ref_id', $get_reputation );
		$this->assertObjectHasProperty( 'rep_date', $get_reputation );
		ap_insert_reputation( 'received_vote_down', $id->q );
		$get_reputation = ap_get_reputation( 'received_vote_down', $id->q );
		$this->assertNotEmpty( $get_reputation );
		$this->assertEquals( get_current_user_id(), $get_reputation->rep_user_id );
		$this->assertEquals( 'received_vote_down', $get_reputation->rep_event );
		$this->assertEquals( $id->q, $get_reputation->rep_ref_id );
		$this->assertObjectHasProperty( 'rep_id', $get_reputation );
		$this->assertObjectHasProperty( 'rep_user_id', $get_reputation );
		$this->assertObjectHasProperty( 'rep_event', $get_reputation );
		$this->assertObjectHasProperty( 'rep_ref_id', $get_reputation );
		$this->assertObjectHasProperty( 'rep_date', $get_reputation );
		ap_insert_reputation( 'given_vote_up', $id->q );
		$get_reputation = ap_get_reputation( 'given_vote_up', $id->q );
		$this->assertNotEmpty( $get_reputation );
		$this->assertEquals( get_current_user_id(), $get_reputation->rep_user_id );
		$this->assertEquals( 'given_vote_up', $get_reputation->rep_event );
		$this->assertEquals( $id->q, $get_reputation->rep_ref_id );
		$this->assertObjectHasProperty( 'rep_id', $get_reputation );
		$this->assertObjectHasProperty( 'rep_user_id', $get_reputation );
		$this->assertObjectHasProperty( 'rep_event', $get_reputation );
		$this->assertObjectHasProperty( 'rep_ref_id', $get_reputation );
		$this->assertObjectHasProperty( 'rep_date', $get_reputation );
		ap_insert_reputation( 'given_vote_down', $id->q );
		$get_reputation = ap_get_reputation( 'given_vote_down', $id->q );
		$this->assertNotEmpty( $get_reputation );
		$this->assertEquals( get_current_user_id(), $get_reputation->rep_user_id );
		$this->assertEquals( 'given_vote_down', $get_reputation->rep_event );
		$this->assertEquals( $id->q, $get_reputation->rep_ref_id );
		$this->assertObjectHasProperty( 'rep_id', $get_reputation );
		$this->assertObjectHasProperty( 'rep_user_id', $get_reputation );
		$this->assertObjectHasProperty( 'rep_event', $get_reputation );
		$this->assertObjectHasProperty( 'rep_ref_id', $get_reputation );
		$this->assertObjectHasProperty( 'rep_date', $get_reputation );
	}

	/**
	 * @covers ::ap_delete_reputation
	 */
	public function testAPDeleteReputation() {

		$id = $this->insert_answer();

		// Test begins.
		$this->setRole( 'subscriber' );
		$this->assertEquals( 0, ap_delete_reputation( 'question', $id->q ) );
		$this->assertEquals( 0, ap_delete_reputation( 'answer', $id->a ) );
		$this->assertEquals( 0, ap_delete_reputation( 'best_answer', $id->a ) );
		$this->assertEquals( 0, ap_delete_reputation( 'select_answer', $id->a ) );
		$this->assertEquals( 0, ap_delete_reputation( 'received_vote_up', $id->q ) );
		$this->assertEquals( 0, ap_delete_reputation( 'received_vote_down', $id->q ) );
		$this->assertEquals( 0, ap_delete_reputation( 'given_vote_up', $id->q ) );
		$this->assertEquals( 0, ap_delete_reputation( 'given_vote_down', $id->q ) );

		// After inserting reputation and deleting them.
		ap_insert_reputation( 'question', $id->q );
		$this->assertNotEmpty( ap_get_reputation( 'question', $id->q ) );
		$this->assertEquals( 1, ap_delete_reputation( 'question', $id->q ) );
		$this->assertNull( ap_get_reputation( 'question', $id->q ) );
		ap_insert_reputation( 'answer', $id->a );
		$this->assertNotEmpty( ap_get_reputation( 'answer', $id->a ) );
		$this->assertEquals( 1, ap_delete_reputation( 'answer', $id->a ) );
		$this->assertNull( ap_get_reputation( 'answer', $id->a ) );
		ap_insert_reputation( 'best_answer', $id->a );
		$this->assertNotEmpty( ap_get_reputation( 'best_answer', $id->a ) );
		$this->assertEquals( 1, ap_delete_reputation( 'best_answer', $id->a ) );
		$this->assertNull( ap_get_reputation( 'best_answer', $id->a ) );
		ap_insert_reputation( 'select_answer', $id->a );
		$this->assertNotEmpty( ap_get_reputation( 'select_answer', $id->a ) );
		$this->assertEquals( 1, ap_delete_reputation( 'select_answer', $id->a ) );
		$this->assertNull( ap_get_reputation( 'select_answer', $id->a ) );
		ap_insert_reputation( 'received_vote_up', $id->q );
		$this->assertNotEmpty( ap_get_reputation( 'received_vote_up', $id->q ) );
		$this->assertEquals( 1, ap_delete_reputation( 'received_vote_up', $id->q ) );
		$this->assertNull( ap_get_reputation( 'received_vote_up', $id->q ) );
		ap_insert_reputation( 'received_vote_down', $id->q );
		$this->assertNotEmpty( ap_get_reputation( 'received_vote_down', $id->q ) );
		$this->assertEquals( 1, ap_delete_reputation( 'received_vote_down', $id->q ) );
		$this->assertNull( ap_get_reputation( 'received_vote_down', $id->q ) );
		ap_insert_reputation( 'given_vote_up', $id->q );
		$this->assertNotEmpty( ap_get_reputation( 'given_vote_up', $id->q ) );
		$this->assertEquals( 1, ap_delete_reputation( 'given_vote_up', $id->q ) );
		$this->assertNull( ap_get_reputation( 'given_vote_up', $id->q ) );
		ap_insert_reputation( 'given_vote_down', $id->q );
		$this->assertNotEmpty( ap_get_reputation( 'given_vote_down', $id->q ) );
		$this->assertEquals( 1, ap_delete_reputation( 'given_vote_down', $id->q ) );
		$this->assertNull( ap_get_reputation( 'given_vote_down', $id->q ) );
	}

	/**
	 * @covers ::ap_register_reputation_event
	 */
	public function testAPRegisterReputationEvent() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		// First test.
		$args = array(
			'label'         => 'Test reputation event register',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => 'apicon-test-reputation',
			'activity'      => 'Reputation registered',
			'parent'        => '',
			'points'        => 8,
			'rep_events_id' => 11,
		);
		ap_register_reputation_event( 'test_register_reputation_event', $args );
		$reputation_events = anspress()->reputation_events;
		$this->assertArrayNotHasKey( 'test', $reputation_events );
		$this->assertArrayHasKey( 'test_register_reputation_event', $reputation_events );
		$test_register_reputation_event = $reputation_events['test_register_reputation_event'];
		$this->assertEquals( 'Test reputation event register', $test_register_reputation_event['label'] );
		$this->assertEquals( 'Lorem ipsum dolor sit amet', $test_register_reputation_event['description'] );
		$this->assertEquals( 'apicon-test-reputation', $test_register_reputation_event['icon'] );
		$this->assertEquals( 'Reputation registered', $test_register_reputation_event['activity'] );
		$this->assertEquals( '', $test_register_reputation_event['parent'] );
		$this->assertEquals( 8, $test_register_reputation_event['points'] );
		$this->assertEquals( 11, $test_register_reputation_event['rep_events_id'] );

		// Second test.
		$args = array(
			'label'         => 'Reputation event register',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => 'apicon-reputation',
			'activity'      => 'Reputation',
			'parent'        => '',
			'points'        => 12,
			'rep_events_id' => 12,
		);
		ap_register_reputation_event( 'register_reputation_event', $args );
		$reputation_events = anspress()->reputation_events;
		$this->assertArrayNotHasKey( 'test', $reputation_events );
		$this->assertArrayHasKey( 'register_reputation_event', $reputation_events );
		$register_reputation_event = $reputation_events['register_reputation_event'];
		$this->assertEquals( 'Reputation event register', $register_reputation_event['label'] );
		$this->assertEquals( 'Lorem ipsum dolor sit amet', $register_reputation_event['description'] );
		$this->assertEquals( 'apicon-reputation', $register_reputation_event['icon'] );
		$this->assertEquals( 'Reputation', $register_reputation_event['activity'] );
		$this->assertEquals( '', $register_reputation_event['parent'] );
		$this->assertEquals( 12, $register_reputation_event['points'] );
		$this->assertEquals( 12, $register_reputation_event['rep_events_id'] );
	}

	public function testAPGetReputationEventActivity() {
		$args = array(
			'label'         => 'Reputation event register',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => 'apicon-reputation',
			'activity'      => 'Test',
			'parent'        => '',
			'points'        => 12,
			'rep_events_id' => 12,
		);
		ap_register_reputation_event( 'test', $args );

		// Test for non existance events.
		$this->assertEquals( 'Test', ap_get_reputation_event_activity( 'test' ) );
	}

	public function testGetReputationEventPoints() {
		$args = array(
			'label'         => 'Reputation event register',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => 'apicon-reputation',
			'activity'      => 'Test',
			'parent'        => '',
			'points'        => 12,
			'rep_events_id' => 12,
		);
		ap_register_reputation_event( 'test', $args );

		// Test for non existance events.
		$this->assertEquals( 12, ap_get_reputation_event_points( 'test' ) );
	}

	public function testGetRepuationEventIcon()
	{
		$args = array(
			'label'         => 'Reputation event register',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => 'apicon-reputation',
			'activity'      => 'Test',
			'parent'        => '',
			'points'        => 12,
			'rep_events_id' => 12,
		);
		ap_register_reputation_event( 'test', $args );

		// Test for non existance events.
		$this->assertEquals( 'apicon-reputation', ap_get_reputation_event_icon( 'test' ) );

	}

	public function testAPGetUserReputation() {
		$args = array(
			'slug'        => 'ask',
			'points'      => 2,
			'label'       => __( 'Asking', 'anspress-question-answer' ),
			'description' => __( 'Points awarded when user asks a question', 'anspress-question-answer' ),
			'icon'        => 'apicon-question',
			'activity'    => __( 'Asked a question', 'anspress-question-answer' ),
			'parent'      => 'question',
		);
		ap_register_reputation_event( 'ask', $args );

		// Test for manually adding the user reputation.
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );

		$question = $this->factory()->post->create_and_get(
			array(
				'post_type'    => 'question',
			)
		);

		ap_insert_reputation( 'ask', $question->ID, $user_id );

		$this->assertEquals( 2, ap_get_user_reputation( $user_id ) );
	}

	public function testAPGetUserReputationMeta() {
		anspress()->reputation_events = [];
		$args = array(
			'slug'        => 'test',
			'points'      => 299922,
			'label'       => __( 'Asking', 'anspress-question-answer' ),
			'description' => __( 'Points awarded when user asks a question', 'anspress-question-answer' ),
			'icon'        => 'apicon-question',
			'activity'    => __( 'Asked a question', 'anspress-question-answer' ),
			'parent'      => 'question',
		);
		ap_register_reputation_event( 'test', $args );

		$user = $this->factory()->user->create_and_get( array( 'role' => 'subscriber' ) );

		$ref_id = $this->factory()->post->create(
			array(
				'post_type'    => 'question',
			)
		);

		$this->assertNotFalse(ap_insert_reputation( 'test', $ref_id, $user->ID ));
		$this->assertEquals( 299922, ap_get_user_reputation_meta($user->ID, false) );
		$this->assertEquals( '299.92K', ap_get_user_reputation_meta($user->ID) );
	}

	public function testApGetUserReputationMetaWithNegativeValue() {
		anspress()->reputation_events = [];
		$args = array(
			'slug'        => 'test',
			'points'      => -299922,
			'label'       => __( 'Asking', 'anspress-question-answer' ),
			'description' => __( 'Points awarded when user asks a question', 'anspress-question-answer' ),
			'icon'        => 'apicon-question',
			'activity'    => __( 'Asked a question', 'anspress-question-answer' ),
			'parent'      => 'question',
		);
		ap_register_reputation_event( 'test', $args );

		$user = $this->factory()->user->create_and_get( array( 'role' => 'subscriber' ) );

		$ref_id = $this->factory()->post->create(
			array(
				'post_type'    => 'question',
			)
		);

		$this->assertNotFalse(ap_insert_reputation( 'test', $ref_id, $user->ID ));
		$this->assertEquals( '-299922', ap_get_user_reputation_meta($user->ID, false) );
		$this->assertEquals( '-299.92K', ap_get_user_reputation_meta($user->ID, true) );
	}

	public function testApGetUserReputationMetaForCurrentUser() {
		anspress()->reputation_events = [];
		$args = array(
			'slug'        => 'test',
			'points'      => 299922,
			'label'       => __( 'Asking', 'anspress-question-answer' ),
			'description' => __( 'Points awarded when user asks a question', 'anspress-question-answer' ),
			'icon'        => 'apicon-question',
			'activity'    => __( 'Asked a question', 'anspress-question-answer' ),
			'parent'      => 'question',
		);
		ap_register_reputation_event( 'test', $args );

		$this->setRole( 'subscriber' );

		$ref_id = $this->factory()->post->create(
			array(
				'post_type'    => 'question',
			)
		);

		$this->assertNotFalse(ap_insert_reputation( 'test', $ref_id, get_current_user_id() ));
		$this->assertEquals( '299.92K', ap_get_user_reputation_meta() );
	}

	public function testApGetUserReputationMetaForInvalidUser() {
		anspress()->reputation_events = [];
		$args = array(
			'slug'        => 'test',
			'points'      => 299922,
			'label'       => __( 'Asking', 'anspress-question-answer' ),
			'description' => __( 'Points awarded when user asks a question', 'anspress-question-answer' ),
			'icon'        => 'apicon-question',
			'activity'    => __( 'Asked a question', 'anspress-question-answer' ),
			'parent'      => 'question',
		);
		ap_register_reputation_event( 'test', $args );

		$this->assertEquals( '0', ap_get_user_reputation_meta( 0 ) );
	}

	public function testUpdateUserReputationMeta() {
		anspress()->reputation_events = [];
		$args = array(
			'slug'        => 'test',
			'points'      => 299922,
			'label'       => __( 'Asking', 'anspress-question-answer' ),
			'description' => __( 'Points awarded when user asks a question', 'anspress-question-answer' ),
			'icon'        => 'apicon-question',
			'activity'    => __( 'Asked a question', 'anspress-question-answer' ),
			'parent'      => 'question',
		);
		ap_register_reputation_event( 'test', $args );

		$user = $this->factory()->user->create_and_get( array( 'role' => 'subscriber' ) );

		$ref_id = $this->factory()->post->create(
			array(
				'post_type'    => 'question',
			)
		);

		$this->assertNotFalse(ap_insert_reputation( 'test', $ref_id, $user->ID ));
		$this->assertEquals( '299.92K', ap_get_user_reputation_meta($user->ID) );
	}

	public function testUpdateUserReputationMetaForCurrentUser() {
		anspress()->reputation_events = [];
		$args = array(
			'slug'        => 'test',
			'points'      => 299922,
			'label'       => __( 'Asking', 'anspress-question-answer' ),
			'description' => __( 'Points awarded when user asks a question', 'anspress-question-answer' ),
			'icon'        => 'apicon-question',
			'activity'    => __( 'Asked a question', 'anspress-question-answer' ),
			'parent'      => 'question',
		);
		ap_register_reputation_event( 'test', $args );

		$this->setRole( 'subscriber' );

		$ref_id = $this->factory()->post->create(
			array(
				'post_type'    => 'question',
			)
		);

		$this->assertNotFalse(ap_insert_reputation( 'test', $ref_id, get_current_user_id() ));
		$this->assertEquals( '299.92K', ap_get_user_reputation_meta() );
	}

	public function testUpdateUserReputationMetaForInvalidUser() {
		anspress()->reputation_events = [];
		$args = array(
			'slug'        => 'test',
			'points'      => 299922,
			'label'       => __( 'Asking', 'anspress-question-answer' ),
			'description' => __( 'Points awarded when user asks a question', 'anspress-question-answer' ),
			'icon'        => 'apicon-question',
			'activity'    => __( 'Asked a question', 'anspress-question-answer' ),
			'parent'      => 'question',
		);
		ap_register_reputation_event( 'test', $args );

		$ref_id = $this->factory()->post->create(
			array(
				'post_type'    => 'question',
			)
		);

		ap_update_user_reputation_meta( 999 );

		$this->assertEquals( '0', ap_get_user_reputation_meta( 999 ) );
	}

	/**
	 * @covers ::ap_insert_reputation_event
	 */
	public function testAPInsertReputationEvent() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		// Insert reputation events.
		$ap_reputation_events = \AnsPress\Addons\Reputation::init();
		$ap_reputation_events->register_default_events();

		// Test begins.
		$this->assertTrue( is_wp_error( ap_insert_reputation_event( 'register', 'Registration', 'Points awarded when user account is created', 10, 'Registered' ) ) );
		$this->assertFalse( is_wp_error( ap_insert_reputation_event( 'test_event', 'Test the event', 'Test the event description', 10, 'Test event passed' ) ) );
		$this->assertIsInt( ap_insert_reputation_event( 'test_new_event', 'Test the new event', 'Test the new event description', 10, 'Test new event passed' ) );
		$this->assertIsInt( ap_insert_reputation_event( 'test_reputation_event', 'Test the reputation event', 'Test the reputation event description', 10, 'Test reputation event passed' ) );
		$this->assertIsInt( ap_insert_reputation_event( 'test_new_reputation_event', 'Test the new reputation event', 'Test the new reputation event description', 10, 'Test new reputation event passed' ) );
	}

	/**
	 * @covers ::ap_get_reputation_event_by_slug
	 */
	public function testAPGetReputationEventBySlug() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		// Insert reputation events.
		$ap_reputation_events = \AnsPress\Addons\Reputation::init();
		$ap_reputation_events->register_default_events();

		// Test for non-existance reputation event.
		$this->assertNull( ap_get_reputation_event_by_slug( 'test' ) );
		$this->assertNull( ap_get_reputation_event_by_slug( 'test_rep_event' ) );
		$this->assertNull( ap_get_reputation_event_by_slug( 'test_event_rep' ) );

		// Test for existing reputation event.
		// Test 1.
		$reputation_event_by_slug = ap_get_reputation_event_by_slug( 'ask' );
		$this->assertNotNull( $reputation_event_by_slug );
		$this->assertisObject( $reputation_event_by_slug );
		$this->assertEquals( 'ask', $reputation_event_by_slug->slug );
		$this->assertEquals( 'apicon-question', $reputation_event_by_slug->icon );
		$this->assertEquals( 'Asking', $reputation_event_by_slug->label );
		$this->assertEquals( 'Points awarded when user asks a question', $reputation_event_by_slug->description );
		$this->assertEquals( 'Asked a question', $reputation_event_by_slug->activity );
		$this->assertEquals( 'question', $reputation_event_by_slug->parent );
		$this->assertEquals( 2, $reputation_event_by_slug->points );

		// Test 2.
		$reputation_event_by_slug = ap_get_reputation_event_by_slug( 'answer' );
		$this->assertNotNull( $reputation_event_by_slug );
		$this->assertisObject( $reputation_event_by_slug );
		$this->assertEquals( 'answer', $reputation_event_by_slug->slug );
		$this->assertEquals( 'apicon-answer', $reputation_event_by_slug->icon );
		$this->assertEquals( 'Answering', $reputation_event_by_slug->label );
		$this->assertEquals( 'Points awarded when user answers a question', $reputation_event_by_slug->description );
		$this->assertEquals( 'Posted an answer', $reputation_event_by_slug->activity );
		$this->assertEquals( 'answer', $reputation_event_by_slug->parent );
		$this->assertEquals( 5, $reputation_event_by_slug->points );

		// Test 3.
		$reputation_event_by_slug = ap_get_reputation_event_by_slug( 'best_answer' );
		$this->assertNotNull( $reputation_event_by_slug );
		$this->assertisObject( $reputation_event_by_slug );
		$this->assertEquals( 'best_answer', $reputation_event_by_slug->slug );
		$this->assertEquals( 'apicon-check', $reputation_event_by_slug->icon );
		$this->assertEquals( 'Answer selected as best', $reputation_event_by_slug->label );
		$this->assertEquals( 'Points awarded when user\'s answer is selected as best', $reputation_event_by_slug->description );
		$this->assertEquals( 'Answer was selected as best', $reputation_event_by_slug->activity );
		$this->assertEquals( 'answer', $reputation_event_by_slug->parent );
		$this->assertEquals( 10, $reputation_event_by_slug->points );
	}

	/**
	 * @covers ::ap_delete_reputation_event_by_slug
	 */
	public function testAPDeleteReputationEventBySlug() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		// Insert reputation events.
		$ap_reputation_events = \AnsPress\Addons\Reputation::init();
		$ap_reputation_events->register_default_events();

		// Test for non-existance reputation event.
		$this->assertTrue( is_wp_error( ap_delete_reputation_event_by_slug( 'test_rep_event' ) ) );
		$this->assertTrue( is_wp_error( ap_delete_reputation_event_by_slug( 'test_event_rep' ) ) );

		// Test for existing reputation event.
		// Test 1.
		$this->assertNotNull( ap_get_reputation_event_by_slug( 'register' ) );
		$this->assertFalse( is_wp_error( ap_delete_reputation_event_by_slug( 'register' ) ) );
		$this->assertNull( ap_get_reputation_event_by_slug( 'register' ) );

		// Test 2.
		$this->assertNotNull( ap_get_reputation_event_by_slug( 'ask' ) );
		$this->assertFalse( is_wp_error( ap_delete_reputation_event_by_slug( 'ask' ) ) );
		$this->assertNull( ap_get_reputation_event_by_slug( 'ask' ) );

		// Test 3.
		$this->assertNotNull( ap_get_reputation_event_by_slug( 'answer' ) );
		$this->assertTrue( ap_delete_reputation_event_by_slug( 'answer' ) );
		$this->assertNull( ap_get_reputation_event_by_slug( 'answer' ) );

		// Test 4.
		$this->assertNotNull( ap_get_reputation_event_by_slug( 'comment' ) );
		$this->assertTrue( ap_delete_reputation_event_by_slug( 'comment' ) );
		$this->assertNull( ap_get_reputation_event_by_slug( 'comment' ) );
	}

	/**
	 * @covers ::ap_get_all_reputation_events
	 */
	public function testAPGetAllReputationEvents() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		// Insert reputation events.
		$ap_reputation_events = \AnsPress\Addons\Reputation::init();
		$ap_reputation_events->register_default_events();

		// Test begins.
		$all_reputation_events = ap_get_all_reputation_events();
		$this->assertIsArray( $all_reputation_events );
		foreach ( $all_reputation_events as $reputation_event ) {
			$this->assertObjectHasProperty( 'rep_events_id', $reputation_event );
			$this->assertObjectHasProperty( 'slug', $reputation_event );
			$this->assertObjectHasProperty( 'icon', $reputation_event );
			$this->assertObjectHasProperty( 'label', $reputation_event );
			$this->assertObjectHasProperty( 'description', $reputation_event );
			$this->assertObjectHasProperty( 'activity', $reputation_event );
			$this->assertObjectHasProperty( 'parent', $reputation_event );
			$this->assertObjectHasProperty( 'points', $reputation_event );
		}
	}

	public function testAPUpdateUserReputationWithNoArgs() {
		ap_opt( 'enable_reputation', true );

		anspress()->reputation_events = [
			'test' => [
				'label'       => 'Test reputation event register',
				'description' => 'Lorem ipsum dolor sit amet',
				'icon'        => 'apicon-test-reputation',
				'activity'    => 'Reputation registered',
				'parent'      => '',
				'points'      => 111,
			],
		];

		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );

		$question_id = $this->factory()->post->create(
			array(
				'post_type'    => 'question',
			)
		);

		wp_set_current_user( $user_id );

		// Before calling the function.
		$reputation = get_user_meta( $user_id, 'ap_reputations', true );
		$this->assertEquals( '0', $reputation );

		ap_insert_reputation( 'test', $question_id, $user_id );

		$this->assertEquals( 111, ap_get_user_reputation( $user_id ) );

		delete_user_meta( $user_id, 'ap_reputations' );

		$this->assertEmpty( get_user_meta( $user_id, 'ap_reputations', true ) );

		// After calling the function.
		ap_update_user_reputation_meta();
		$reputation = get_user_meta( $user_id, 'ap_reputations', true );
		$this->assertEquals( 111, ap_get_user_reputation( $user_id ) );
	}
}
