<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestWidgetAskForm extends TestCase {

	public function testWidgetsInit() {
		$this->assertEquals( 10, has_action( 'widgets_init', 'ap_quickask_register_widgets' ) );
		$this->assertTrue( class_exists( 'AP_Askform_Widget' ) );
		$this->assertTrue( array_key_exists( 'AP_Askform_Widget', $GLOBALS['wp_widget_factory']->widgets ) );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AP_Askform_Widget', '__construct' ) );
		$this->assertTrue( method_exists( 'AP_Askform_Widget', 'widget' ) );
		$this->assertTrue( method_exists( 'AP_Askform_Widget', 'form' ) );
		$this->assertTrue( method_exists( 'AP_Askform_Widget', 'update' ) );
	}
}
