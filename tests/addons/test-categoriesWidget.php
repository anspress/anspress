<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

// Since this file is required on widget_init hook,
// we include the file here directly for testing purpose.
require_once ANSPRESS_ADDONS_DIR . '/categories/widget.php';

class TestAddonCategoriesWidget extends TestCase {

	public function testWidgetsInit() {
		$instance = \Anspress\Addons\Categories::init();
		anspress()->setup_hooks();
		$this->assertEquals( 10, has_action( 'widgets_init', [ $instance, 'widget' ] ) );
		$this->assertTrue( class_exists( 'Anspress\Widgets\Categories' ) );
		$this->assertTrue( array_key_exists( 'Anspress\Widgets\Categories', $GLOBALS['wp_widget_factory']->widgets ) );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'Anspress\Widgets\Categories', '__construct' ) );
		$this->assertTrue( method_exists( 'Anspress\Widgets\Categories', 'widget' ) );
		$this->assertTrue( method_exists( 'Anspress\Widgets\Categories', 'form' ) );
		$this->assertTrue( method_exists( 'Anspress\Widgets\Categories', 'update' ) );
	}
}
