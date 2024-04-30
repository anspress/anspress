<?php

namespace AnsPress\Tests\WP;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestReputationInsertDisableFromConstant extends TestCase {

	use Testcases\Common;

	/**
	 * Test if user reputation is inserted if AP_DISABLE_INSERT_REP constant is defined.
	 *
	 * @covers ::ap_insert_reputation
	 */
	public function testAPInsertReputationShouldReturnNull() {
		// Activate addon.
		ap_activate_addon( 'reputation.php' );

		// Before defining AP_DISABLE_INSERT_REP.
		$this->assertFalse( defined( 'AP_DISABLE_INSERT_REP' ) );
		$question_id = $this->insert_question();
		$this->assertNotNull( ap_insert_reputation( 'ask', $question_id ) );

		// After defining AP_DISABLE_INSERT_REP.
		define( 'AP_DISABLE_INSERT_REP', true );
		$this->assertTrue( defined( 'AP_DISABLE_INSERT_REP' ) );
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question();
		$this->assertNull( ap_insert_reputation( 'ask', $question_id ) );

		// Deactivate addon.
		ap_deactivate_addon( 'reputation.php' );
	}
}
