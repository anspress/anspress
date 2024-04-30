<?php

namespace AnsPress\Tests\WP;

use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * @covers \AnsPress_Flag
 * @package AnsPress\Tests\WP
 */
class TestAnsPressFlag extends TestCase {

	use Testcases\Common;

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Flag', 'action_flag' ) );
	}

	/**
	 * @covers ::ap_add_flag
	 */
	public function testAPAddFlag() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_votes}" );

		$id = $this->insert_question();
		$this->setRole( 'subscriber' );
		$this->assertTrue( ap_add_flag( $id ) );

		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$this->assertTrue( ap_add_flag( $id, $user_id ) );
	}

}
