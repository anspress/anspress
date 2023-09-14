<?php

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestFlag extends TestCase {

	use AnsPress\Tests\Testcases\Common;

	/**
	 * @covers ::ap_add_flag
	 */
	public function testAPAddFlag() {
		$id = $this->insert_question();
		$this->setRole( 'subscriber' );
		$this->assertTrue( ap_add_flag( $id ) );

		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$this->assertTrue( ap_add_flag( $id, $user_id ) );
	}

	/**
	 * @covers ::ap_is_user_flagged
	 */
	public function testAPIsUserFlagged() {
		$id = $this->insert_answer();
		$this->assertFalse( ap_is_user_flagged( $id->q ) );
		$this->assertFalse( ap_is_user_flagged( $id->a ) );

		$this->setRole( 'subscriber' );
		ap_add_flag( $id->q );
		$this->assertTrue( ap_is_user_flagged( $id->q ) );
		$this->assertFalse( ap_is_user_flagged( $id->a ) );
		ap_add_flag( $id->a );
		$this->assertTrue( ap_is_user_flagged( $id->q ) );
		$this->assertTrue( ap_is_user_flagged( $id->a ) );
	}

	/**
	 * @covers ::ap_delete_flags
	 */
	public function testAPDeleteFlags() {
		$id = $this->insert_question();
		$this->setRole( 'subscriber' );
		$this->assertTrue( ap_add_flag( $id ) );
		$this->assertTrue( ap_delete_flags( $id ) );
	}

}
