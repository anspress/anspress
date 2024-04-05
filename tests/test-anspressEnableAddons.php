<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestEnableAllAddonsFromConstant extends TestCase {

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
		$this->assertTrue( ap_is_addon_active( 'buddypress.php' ) );
		$this->assertTrue( ap_is_addon_active( 'notifications.php' ) );
		$this->assertTrue( ap_is_addon_active( 'profile.php' ) );
		$this->assertTrue( ap_is_addon_active( 'recaptcha.php' ) );
		$this->assertTrue( ap_is_addon_active( 'syntaxhighlighter.php' ) );
		$this->assertTrue( ap_is_addon_active( 'tags.php' ) );
	}
}
