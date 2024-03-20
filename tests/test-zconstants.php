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

	/**
	 * Test if all addons is activated by default if ANSPRESS_ENABLE_ADDONS constant is defined.
	 *
	 * @covers AnsPress::site_include
	 */
	public function testAnsPressSiteIncludeShouldActivateAllAddons() {
		// Before defining ANSPRESS_ENABLE_ADDONS.
		$this->assertFalse( defined( 'ANSPRESS_ENABLE_ADDONS' ) );
		$this->assertFalse( ap_is_addon_active( 'categories.php' ) );
		$this->assertFalse( ap_is_addon_active( 'email.php' ) );
		$this->assertFalse( ap_is_addon_active( 'reputation.php' ) );
		$this->assertFalse( ap_is_addon_active( 'akismet.php' ) );
		$this->assertFalse( ap_is_addon_active( 'avatar.php' ) );
		$this->assertFalse( ap_is_addon_active( 'buddypress.php' ) );
		$this->assertFalse( ap_is_addon_active( 'notifications.php' ) );
		$this->assertFalse( ap_is_addon_active( 'profile.php' ) );
		$this->assertFalse( ap_is_addon_active( 'recaptcha.php' ) );
		$this->assertFalse( ap_is_addon_active( 'syntaxhighlighter.php' ) );
		$this->assertFalse( ap_is_addon_active( 'tags.php' ) );

		// After defining ANSPRESS_ENABLE_ADDONS.
		define( 'ANSPRESS_ENABLE_ADDONS', true );
		anspress()->site_include();
		$this->assertTrue( ap_is_addon_active( 'categories.php' ) );
		$this->assertTrue( ap_is_addon_active( 'email.php' ) );
		$this->assertTrue( ap_is_addon_active( 'reputation.php' ) );
		$this->assertTrue( ap_is_addon_active( 'akismet.php' ) );
		$this->assertTrue( ap_is_addon_active( 'avatar.php' ) );
		$this->assertTrue( ap_is_addon_active( 'buddypress.php' ) );
		$this->assertTrue( ap_is_addon_active( 'notifications.php' ) );
		$this->assertTrue( ap_is_addon_active( 'profile.php' ) );
		$this->assertTrue( ap_is_addon_active( 'recaptcha.php' ) );
		$this->assertTrue( ap_is_addon_active( 'syntaxhighlighter.php' ) );
		$this->assertTrue( ap_is_addon_active( 'tags.php' ) );
	}

	/**
	 * @covers ::ap_addon_activation_hook
	 */
	public function testAPAddonActivationHook() {
		global $ap_addons_activation;
		$ap_addons_activation = [];
		ap_addon_activation_hook( 'test-addon.php', 'test_addon_activated' );
		$this->assertArrayHasKey( 'test-addon.php', $ap_addons_activation );
		$this->assertEquals( 'test_addon_activated', $ap_addons_activation['test-addon.php'] );
		$ap_addons_activation = [];
	}
}
