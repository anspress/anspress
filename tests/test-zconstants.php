<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestZConstantsFunctionalities extends TestCase {

	use Testcases\Common;

	/**
	 * Test if user notification is inserted if AP_DISABLE_INSERT_NOTI constant is defined.
	 *
	 * @covers ::ap_insert_notification
	 */
	public function testAPInsertNotificationShouldReturnNull() {
		// Activate addon.
		ap_activate_addon( 'notifications.php' );

		// Before defining AP_DISABLE_INSERT_NOTI.
		$this->assertFalse( defined( 'AP_DISABLE_INSERT_NOTI' ) );
		$this->setRole( 'subscriber' );
		$this->assertNotNull( ap_insert_notification( [] ) );

		// After defining AP_DISABLE_INSERT_NOTI.
		define( 'AP_DISABLE_INSERT_NOTI', true );
		$this->assertTrue( defined( 'AP_DISABLE_INSERT_NOTI' ) );
		$this->setRole( 'subscriber' );
		$this->assertNull( ap_insert_notification( [] ) );

		// Deactivate addon.
		ap_deactivate_addon( 'notifications.php' );
	}

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
