<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestWidgetLeaderboard extends TestCase {

	public function testWidgetsInit() {
		$this->assertEquals( 10, has_action( 'widgets_init', 'ap_leaderboard_register_widgets' ) );
		$this->assertTrue( class_exists( 'AnsPress_Leaderboard_Widget' ) );
		ap_leaderboard_register_widgets();
		$this->assertArrayHasKey( 'AnsPress_Leaderboard_Widget', $GLOBALS['wp_widget_factory']->widgets );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Leaderboard_Widget', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress_Leaderboard_Widget', 'get_top_users' ) );
		$this->assertTrue( method_exists( 'AnsPress_Leaderboard_Widget', 'widget' ) );
		$this->assertTrue( method_exists( 'AnsPress_Leaderboard_Widget', 'form' ) );
		$this->assertTrue( method_exists( 'AnsPress_Leaderboard_Widget', 'update' ) );
	}

	/**
	 * @covers AnsPress_Leaderboard_Widget::__construct
	 */
	public function testConstruct() {
		$instance = new \AnsPress_Leaderboard_Widget();
		$this->assertEquals( strtolower( 'ap_leaderboard_widget' ), $instance->id_base );
		$this->assertEquals( '(AnsPress) User Leaderboard', $instance->name );
		$this->assertEquals( 'Shows users leaderboard.', $instance->widget_options['description'] );
	}
}
