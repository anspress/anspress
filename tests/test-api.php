<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

// Require the api.php file.
require_once ANSPRESS_DIR . 'includes/api.php';

class TestAPI extends TestCase {

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_API', 'register' ) );
		$this->assertTrue( method_exists( 'AnsPress_API', 'avatar' ) );
	}
}
