<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

// Since this file is required on widget_init hook,
// we include the file here directly for testing purpose.
require_once ANSPRESS_ADDONS_DIR . '/categories/widget.php';

class TestAddonCategoriesWidget extends TestCase {

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'Anspress\Widgets\Categories', '__construct' ) );
		$this->assertTrue( method_exists( 'Anspress\Widgets\Categories', 'widget' ) );
		$this->assertTrue( method_exists( 'Anspress\Widgets\Categories', 'form' ) );
		$this->assertTrue( method_exists( 'Anspress\Widgets\Categories', 'update' ) );
	}
}
