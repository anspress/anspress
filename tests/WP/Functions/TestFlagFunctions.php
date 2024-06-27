<?php

namespace AnsPress\Tests\WP\Fuctions;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestFlagFunctions extends TestCase {

	use \AnsPress\Tests\WP\Testcases\Common;

	/**
	 * @covers ::ap_is_user_flagged
	 */
	public function testAPIsUserFlagged() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_votes}" );

		$id = $this->insert_answer();
		$this->assertFalse( ap_is_user_flagged( $id->q ) );
		$this->assertFalse( ap_is_user_flagged( $id->a ) );

		$this->setRole( 'subscriber' );
		ap_add_flag( $id->q );
		ap_update_flags_count( $id->q );
		$this->assertTrue( ap_is_user_flagged( $id->q ) );
		$this->assertFalse( ap_is_user_flagged( $id->a ) );
		ap_add_flag( $id->a );
		ap_update_flags_count( $id->a );
		$this->assertTrue( ap_is_user_flagged( $id->q ) );
		$this->assertTrue( ap_is_user_flagged( $id->a ) );
		ap_delete_flags( $id->q );
		ap_delete_flags( $id->a );
		$this->assertFalse( ap_is_user_flagged( $id->q ) );
		$this->assertFalse( ap_is_user_flagged( $id->a ) );
	}

	/**
	 * @covers ::ap_delete_flags
	 */
	public function testAPDeleteFlags() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_votes}" );

		$id = $this->insert_question();
		$this->setRole( 'subscriber' );
		ap_add_flag( $id );
		ap_update_flags_count( $id );
		$this->assertTrue( ap_delete_flags( $id ) );
	}

	/**
	 * @covers ::ap_count_post_flags
	 */
	public function testAOCountPostFlags() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_votes}" );

		$id = $this->insert_answer();
		$question_count_flag = ap_count_post_flags( $id->q );
		$this->assertEquals( 0, $question_count_flag );
		$answer_count_flag = ap_count_post_flags( $id->a );
		$this->assertEquals( 0, $answer_count_flag );

		// Test after adding a flag.
		ap_add_flag( $id->q );
		ap_update_flags_count( $id->q );
		$question_count_flag = ap_count_post_flags( $id->q );
		$this->assertEquals( 1, $question_count_flag );
		ap_add_flag( $id->a );
		ap_update_flags_count( $id->a );
		$answer_count_flag = ap_count_post_flags( $id->a );
		$this->assertEquals( 1, $answer_count_flag );
		ap_add_flag( $id->q );
		ap_update_flags_count( $id->q );
		$question_count_flag = ap_count_post_flags( $id->q );
		$this->assertEquals( 2, $question_count_flag );
		ap_add_flag( $id->a );
		ap_update_flags_count( $id->a );
		$answer_count_flag = ap_count_post_flags( $id->a );
		$this->assertEquals( 2, $answer_count_flag );

		// Test after deleting flags.
		ap_delete_flags( $id->q );
		$question_count_flag = ap_count_post_flags( $id->q );
		$this->assertEquals( 0, $question_count_flag );
		ap_delete_flags( $id->a );
		$answer_count_flag = ap_count_post_flags( $id->q );
		$this->assertEquals( 0, $answer_count_flag );
	}

	/**
	 * @covers ::ap_flag_btn_args
	 */
	public function testAPFlagBtnArgs() {
		// Test for question post type.
		// Test for not logged in user.
		$id = $this->insert_question();
		$this->assertNull( ap_flag_btn_args() );
		$this->assertNull( ap_flag_btn_args( $id ) );

		// Test for logged in user.
		$this->setRole( 'subscriber' );
		$id = $this->insert_question();
		$flag_button_args = ap_flag_btn_args( $id );

		// Test begins.
		$this->assertIsArray( $flag_button_args );
		$this->assertArrayHasKey( 'cb', $flag_button_args );
		$this->assertArrayHasKey( 'icon', $flag_button_args );
		$this->assertArrayHasKey( 'query', $flag_button_args );
		$this->assertArrayHasKey( '__nonce', $flag_button_args['query'] );
		$this->assertArrayHasKey( 'post_id', $flag_button_args['query'] );
		$this->assertArrayHasKey( 'label', $flag_button_args );
		$this->assertArrayHasKey( 'title', $flag_button_args );
		$this->assertArrayHasKey( 'count', $flag_button_args );
		$this->assertArrayHasKey( 'active', $flag_button_args );

		// Test for values.
		$nonce = wp_create_nonce( 'flag_' . $id );
		$this->assertEquals( 'flag', $flag_button_args['cb'] );
		$this->assertEquals( 'apicon-check', $flag_button_args['icon'] );
		$this->assertEquals( $nonce, $flag_button_args['query']['__nonce'] );
		$this->assertEquals( $id, $flag_button_args['query']['post_id'] );
		$this->assertEquals( 'Flag', $flag_button_args['label'] );
		$this->assertEquals( 'Flag this question', $flag_button_args['title'] );
		$this->assertEquals( 0, $flag_button_args['count'] );
		$this->assertFalse( $flag_button_args['active'] );

		// Test for already logged in user but the user has already flagged.
		$this->setRole( 'subscriber' );
		$id = $this->insert_question();
		ap_add_flag( $id );
		ap_update_flags_count( $id );
		$flag_button_args = ap_flag_btn_args( $id );

		// Test begins.
		$this->assertIsArray( $flag_button_args );
		$this->assertArrayHasKey( 'cb', $flag_button_args );
		$this->assertArrayHasKey( 'icon', $flag_button_args );
		$this->assertArrayHasKey( 'query', $flag_button_args );
		$this->assertArrayHasKey( '__nonce', $flag_button_args['query'] );
		$this->assertArrayHasKey( 'post_id', $flag_button_args['query'] );
		$this->assertArrayHasKey( 'label', $flag_button_args );
		$this->assertArrayHasKey( 'title', $flag_button_args );
		$this->assertArrayHasKey( 'count', $flag_button_args );
		$this->assertArrayHasKey( 'active', $flag_button_args );

		// Test for values.
		$nonce = wp_create_nonce( 'flag_' . $id );
		$this->assertEquals( 'flag', $flag_button_args['cb'] );
		$this->assertEquals( 'apicon-check', $flag_button_args['icon'] );
		$this->assertEquals( $nonce, $flag_button_args['query']['__nonce'] );
		$this->assertEquals( $id, $flag_button_args['query']['post_id'] );
		$this->assertEquals( 'Flag', $flag_button_args['label'] );
		$this->assertEquals( 'You have flagged this question', $flag_button_args['title'] );
		$this->assertEquals( 1, $flag_button_args['count'] );
		$this->assertTrue( $flag_button_args['active'] );
		$this->logout();

		// Test for answer post type.
		// Test for not logged in user.
		$id = $this->insert_answer();
		$this->assertNull( ap_flag_btn_args() );
		$this->assertNull( ap_flag_btn_args( $id->a ) );

		// Test for logged in user.
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();
		$flag_button_args = ap_flag_btn_args( $id->a );

		// Test begins.
		$this->assertIsArray( $flag_button_args );
		$this->assertArrayHasKey( 'cb', $flag_button_args );
		$this->assertArrayHasKey( 'icon', $flag_button_args );
		$this->assertArrayHasKey( 'query', $flag_button_args );
		$this->assertArrayHasKey( '__nonce', $flag_button_args['query'] );
		$this->assertArrayHasKey( 'post_id', $flag_button_args['query'] );
		$this->assertArrayHasKey( 'label', $flag_button_args );
		$this->assertArrayHasKey( 'title', $flag_button_args );
		$this->assertArrayHasKey( 'count', $flag_button_args );
		$this->assertArrayHasKey( 'active', $flag_button_args );

		// Test for values.
		$nonce = wp_create_nonce( 'flag_' . $id->a );
		$this->assertEquals( 'flag', $flag_button_args['cb'] );
		$this->assertEquals( 'apicon-check', $flag_button_args['icon'] );
		$this->assertEquals( $nonce, $flag_button_args['query']['__nonce'] );
		$this->assertEquals( $id->a, $flag_button_args['query']['post_id'] );
		$this->assertEquals( 'Flag', $flag_button_args['label'] );
		$this->assertEquals( 'Flag this answer', $flag_button_args['title'] );
		$this->assertEquals( 0, $flag_button_args['count'] );
		$this->assertFalse( $flag_button_args['active'] );

		// Test for already logged in user but the user has already flagged.
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();
		ap_add_flag( $id->a );
		ap_update_flags_count( $id->a );
		$flag_button_args = ap_flag_btn_args( $id->a );

		// Test begins.
		$this->assertIsArray( $flag_button_args );
		$this->assertArrayHasKey( 'cb', $flag_button_args );
		$this->assertArrayHasKey( 'icon', $flag_button_args );
		$this->assertArrayHasKey( 'query', $flag_button_args );
		$this->assertArrayHasKey( '__nonce', $flag_button_args['query'] );
		$this->assertArrayHasKey( 'post_id', $flag_button_args['query'] );
		$this->assertArrayHasKey( 'label', $flag_button_args );
		$this->assertArrayHasKey( 'title', $flag_button_args );
		$this->assertArrayHasKey( 'count', $flag_button_args );
		$this->assertArrayHasKey( 'active', $flag_button_args );

		// Test for values.
		$nonce = wp_create_nonce( 'flag_' . $id->a );
		$this->assertEquals( 'flag', $flag_button_args['cb'] );
		$this->assertEquals( 'apicon-check', $flag_button_args['icon'] );
		$this->assertEquals( $nonce, $flag_button_args['query']['__nonce'] );
		$this->assertEquals( $id->a, $flag_button_args['query']['post_id'] );
		$this->assertEquals( 'Flag', $flag_button_args['label'] );
		$this->assertEquals( 'You have flagged this answer', $flag_button_args['title'] );
		$this->assertEquals( 1, $flag_button_args['count'] );
		$this->assertTrue( $flag_button_args['active'] );
		$this->logout();
	}
}
