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

	/**
	 * @covers AnsPress_Leaderboard_Widget::update
	 */
	public function testUpdate() {
		$instance = new \AnsPress_Leaderboard_Widget();

		// Test.
		$new_instance = [
			'title'         => 'Test title',
			'avatar_size'   => 50,
			'show_users'    => 10,
			'users_per_row' => 5,
			'interval'      => 12,
		];
		$old_instance = [
			'title'         => 'Old title',
			'avatar_size'   => 48,
			'show_users'    => 5,
			'users_per_row' => 4,
			'interval'      => 30,
		];
		$expected = [
			'title'         => 'Test title',
			'avatar_size'   => 50,
			'show_users'    => 10,
			'users_per_row' => 5,
			'interval'      => 12,
		];
		$this->assertEquals( $expected, $instance->update( $new_instance, $old_instance ) );
	}

	/**
	 * @covers AnsPress_Leaderboard_Widget::update
	 */
	public function testUpdateHTMLTagsOnTitle() {
		$instance = new \AnsPress_Leaderboard_Widget();

		// Test.
		$new_instance = [
			'title'         => '<h1 class="widget-title">Test title</h1>',
			'avatar_size'   => 0,
			'show_users'    => 7,
			'users_per_row' => 3,
			'interval'      => 10,
		];
		$old_instance = [
			'title'         => 'Old title',
			'avatar_size'   => 48,
			'show_users'    => 5,
			'users_per_row' => 4,
			'interval'      => 30,
		];
		$expected = [
			'title'         => 'Test title',
			'avatar_size'   => 40,
			'show_users'    => 7,
			'users_per_row' => 3,
			'interval'      => 10,
		];
		$this->assertEquals( $expected, $instance->update( $new_instance, $old_instance ) );
	}

	/**
	 * @covers AnsPress_Leaderboard_Widget::update
	 */
	public function testUpdateHTMLTagsOnTitleWithEmptyTitleAndNegativeValuesOnOtherOptions() {
		$instance = new \AnsPress_Leaderboard_Widget();

		// Test.
		$new_instance = [
			'title'         => '<strong></strong>',
			'avatar_size'   => -64,
			'show_users'    => -10,
			'users_per_row' => -5,
			'interval'      => -20,
		];
		$old_instance = [
			'title'         => 'Old title',
			'avatar_size'   => 48,
			'show_users'    => 5,
			'users_per_row' => 4,
			'interval'      => 30,
		];
		$expected = [
			'title'         => '',
			'avatar_size'   => 64,
			'show_users'    => 10,
			'users_per_row' => 5,
			'interval'      => 20,
		];
		$this->assertEquals( $expected, $instance->update( $new_instance, $old_instance ) );
	}

	/**
	 * @covers AnsPress_Leaderboard_Widget::update
	 */
	public function testUpdateWithEmptyValuesOnAllOptions() {
		$instance = new \AnsPress_Leaderboard_Widget();

		// Test.
		$new_instance = [
			'title'         => '',
			'avatar_size'   => '',
			'show_users'    => '',
			'users_per_row' => '',
			'interval'      => '',
		];
		$old_instance = [
			'title'         => 'Old title',
			'avatar_size'   => 48,
			'show_users'    => 5,
			'users_per_row' => 4,
			'interval'      => 30,
		];
		$expected = [
			'title'         => '',
			'avatar_size'   => 40,
			'show_users'    => 12,
			'users_per_row' => 4,
			'interval'      => 30,
		];
		$this->assertEquals( $expected, $instance->update( $new_instance, $old_instance ) );
	}

	/**
	 * @covers AnsPress_Leaderboard_Widget::form
	 */
	public function testForm() {
		$instance = new \AnsPress_Leaderboard_Widget();

		// Test.
		$instance_data = [
			'title'         => 'Test title',
			'avatar_size'   => 48,
			'show_users'    => 15,
			'users_per_row' => 3,
			'interval'      => 60,
		];
		ob_start();
		$instance->form( $instance_data );
		$result = ob_get_clean();

		// For title.
		$this->assertStringContainsString( 'Title:', $result );
		$this->assertStringContainsString( '[][title]', $result );
		$this->assertStringContainsString( 'value="Test title"', $result );

		// For interval.
		$this->assertStringContainsString( 'Interval (in days):', $result );
		$this->assertStringContainsString( '[][interval]', $result );
		$this->assertStringContainsString( 'value="60"', $result );

		// For avatar size.
		$this->assertStringContainsString( 'Avatar size:', $result );
		$this->assertStringContainsString( '[][avatar_size]', $result );
		$this->assertStringContainsString( 'value="48"', $result );

		// For show users.
		$this->assertStringContainsString( 'Show users:', $result );
		$this->assertStringContainsString( '[][show_users]', $result );
		$this->assertStringContainsString( 'value="15"', $result );

		// For users per row.
		$this->assertStringContainsString( 'Users per row:', $result );
		$this->assertStringContainsString( '[][users_per_row]', $result );
		$this->assertStringContainsString( 'value="3"', $result );
	}

	/**
	 * @covers AnsPress_Leaderboard_Widget::form
	 */
	public function testFormWithEmptyTitle() {
		$instance = new \AnsPress_Leaderboard_Widget();

		// Test.
		$instance_data = [
			'title'         => '',
			'avatar_size'   => 64,
			'show_users'    => 24,
			'users_per_row' => 2,
			'interval'      => 10,
		];
		ob_start();
		$instance->form( $instance_data );
		$result = ob_get_clean();

		// For title.
		$this->assertStringContainsString( 'Title:', $result );
		$this->assertStringContainsString( '[][title]', $result );
		$this->assertStringContainsString( 'value=""', $result );

		// For interval.
		$this->assertStringContainsString( 'Interval (in days):', $result );
		$this->assertStringContainsString( '[][interval]', $result );
		$this->assertStringContainsString( 'value="64"', $result );

		// For avatar size.
		$this->assertStringContainsString( 'Avatar size:', $result );
		$this->assertStringContainsString( '[][avatar_size]', $result );
		$this->assertStringContainsString( 'value="24"', $result );

		// For show users.
		$this->assertStringContainsString( 'Show users:', $result );
		$this->assertStringContainsString( '[][show_users]', $result );
		$this->assertStringContainsString( 'value="2"', $result );

		// For users per row.
		$this->assertStringContainsString( 'Users per row:', $result );
		$this->assertStringContainsString( '[][users_per_row]', $result );
		$this->assertStringContainsString( 'value="10"', $result );
	}

	/**
	 * @covers AnsPress_Leaderboard_Widget::form
	 */
	public function testFormWithEmptyValues() {
		$instance = new \AnsPress_Leaderboard_Widget();

		// Test.
		$instance_data = [
			'title'         => '',
			'avatar_size'   => '',
			'show_users'    => '',
			'users_per_row' => '',
			'interval'      => '',
		];
		ob_start();
		$instance->form( $instance_data );
		$result = ob_get_clean();

		// For title.
		$this->assertStringContainsString( 'Title:', $result );
		$this->assertStringContainsString( '[][title]', $result );
		$this->assertStringContainsString( 'value=""', $result );

		// For interval.
		$this->assertStringContainsString( 'Interval (in days):', $result );
		$this->assertStringContainsString( '[][interval]', $result );
		$this->assertStringContainsString( 'value="30"', $result );

		// For avatar size.
		$this->assertStringContainsString( 'Avatar size:', $result );
		$this->assertStringContainsString( '[][avatar_size]', $result );
		$this->assertStringContainsString( 'value="40"', $result );

		// For show users.
		$this->assertStringContainsString( 'Show users:', $result );
		$this->assertStringContainsString( '[][show_users]', $result );
		$this->assertStringContainsString( 'value="12"', $result );

		// For users per row.
		$this->assertStringContainsString( 'Users per row:', $result );
		$this->assertStringContainsString( '[][users_per_row]', $result );
		$this->assertStringContainsString( 'value="4"', $result );
	}

	/**
	 * @covers AnsPress_Leaderboard_Widget::form
	 */
	public function testFormWithDefaultValues() {
		$instance = new \AnsPress_Leaderboard_Widget();

		// Test.
		$instance_data = [];
		ob_start();
		$instance->form( $instance_data );
		$result = ob_get_clean();

		// For title.
		$this->assertStringContainsString( 'Title:', $result );
		$this->assertStringContainsString( '[][title]', $result );
		$this->assertStringContainsString( 'value="AnsPress Leader board"', $result );

		// For interval.
		$this->assertStringContainsString( 'Interval (in days):', $result );
		$this->assertStringContainsString( '[][interval]', $result );
		$this->assertStringContainsString( 'value="30"', $result );

		// For avatar size.
		$this->assertStringContainsString( 'Avatar size:', $result );
		$this->assertStringContainsString( '[][avatar_size]', $result );
		$this->assertStringContainsString( 'value="40"', $result );

		// For show users.
		$this->assertStringContainsString( 'Show users:', $result );
		$this->assertStringContainsString( '[][show_users]', $result );
		$this->assertStringContainsString( 'value="12"', $result );

		// For users per row.
		$this->assertStringContainsString( 'Users per row:', $result );
		$this->assertStringContainsString( '[][users_per_row]', $result );
		$this->assertStringContainsString( 'value="4"', $result );
	}
}
