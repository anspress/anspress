<?php

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestFlag extends TestCase {

	use AnsPress\Tests\Testcases\Common;

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

}
