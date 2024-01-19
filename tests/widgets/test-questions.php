<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestWidgetQuestions extends TestCase {

	public function testWidgetsInit() {
		$this->assertEquals( 10, has_action( 'widgets_init', 'ap_questions_register_widgets' ) );
		$this->assertTrue( class_exists( 'AP_Questions_Widget' ) );
		$this->assertTrue( array_key_exists( 'AP_Questions_Widget', $GLOBALS['wp_widget_factory']->widgets ) );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AP_Questions_Widget', '__construct' ) );
		$this->assertTrue( method_exists( 'AP_Questions_Widget', 'widget' ) );
		$this->assertTrue( method_exists( 'AP_Questions_Widget', 'form' ) );
		$this->assertTrue( method_exists( 'AP_Questions_Widget', 'update' ) );
	}
}
