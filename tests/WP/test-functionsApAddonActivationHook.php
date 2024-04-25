<?php

namespace AnsPress\Tests\WP;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAPAddonActivationHookFromConstant extends TestCase {

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
