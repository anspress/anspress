<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestWidgetQuestionStats extends TestCase {

	public function testWidgetsInit() {
		$this->assertEquals( 10, has_action( 'widgets_init', 'ap_stats_register_widgets' ) );
		$this->assertTrue( class_exists( 'AnsPress_Stats_Widget' ) );
		$this->assertTrue( array_key_exists( 'AnsPress_Stats_Widget', $GLOBALS['wp_widget_factory']->widgets ) );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Stats_Widget', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress_Stats_Widget', 'widget' ) );
		$this->assertTrue( method_exists( 'AnsPress_Stats_Widget', 'form' ) );
		$this->assertTrue( method_exists( 'AnsPress_Stats_Widget', 'update' ) );
	}
}
