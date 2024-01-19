<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestWidgetBreadcrumbs extends TestCase {

	public function testWidgetsInit() {
		$this->assertEquals( 10, has_action( 'widgets_init', 'register_anspress_breadcrumbs' ) );
		$this->assertTrue( class_exists( 'AnsPress_Breadcrumbs_Widget' ) );
		$this->assertTrue( array_key_exists( 'AnsPress_Breadcrumbs_Widget', $GLOBALS['wp_widget_factory']->widgets ) );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Breadcrumbs_Widget', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress_Breadcrumbs_Widget', 'get_breadcrumbs' ) );
		$this->assertTrue( method_exists( 'AnsPress_Breadcrumbs_Widget', 'breadcrumbs' ) );
		$this->assertTrue( method_exists( 'AnsPress_Breadcrumbs_Widget', 'widget' ) );
		$this->assertTrue( method_exists( 'AnsPress_Breadcrumbs_Widget', 'form' ) );
		$this->assertTrue( method_exists( 'AnsPress_Breadcrumbs_Widget', 'update' ) );
	}
}
