<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestWidgetQuestionStats extends TestCase {

	public function testWidgetsInit() {
		do_action( 'widgets_init' );
		$this->assertEquals( 10, has_action( 'widgets_init', 'ap_stats_register_widgets' ) );
		$this->assertTrue( class_exists( 'AnsPress_Stats_Widget' ) );
		ap_stats_register_widgets();
		$this->assertTrue( array_key_exists( 'AnsPress_Stats_Widget', $GLOBALS['wp_widget_factory']->widgets ) );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Stats_Widget', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress_Stats_Widget', 'widget' ) );
		$this->assertTrue( method_exists( 'AnsPress_Stats_Widget', 'form' ) );
		$this->assertTrue( method_exists( 'AnsPress_Stats_Widget', 'update' ) );
	}

	/**
	 * @covers AnsPress_Stats_Widget::__construct
	 */
	public function testConstruct() {
		$instance = new \AnsPress_Stats_Widget();
		$this->assertEquals( strtolower( 'ap_stats_widget' ), $instance->id_base );
		$this->assertEquals( '(AnsPress) Question Stats', $instance->name );
		$this->assertEquals( 'Shows question stats in single question page.', $instance->widget_options['description'] );
	}

	/**
	 * @covers AnsPress_Stats_Widget::update
	 */
	public function testUpdate() {
		$instance = new \AnsPress_Stats_Widget();

		// Test.
		$new_instance = [
			'title' => 'Test title',
		];
		$old_instance = [
			'title' => 'Old title',
		];
		$expected = [
			'title' => 'Test title',
		];
		$this->assertEquals( $expected, $instance->update( $new_instance, $old_instance ) );
	}

	/**
	 * @covers AnsPress_Stats_Widget::update
	 */
	public function testUpdateHTMLTagsOnTitle() {
		$instance = new \AnsPress_Stats_Widget();

		// Test.
		$new_instance = [
			'title' => '<h1 class="widget-title">Test title</h1>',
		];
		$old_instance = [
			'title' => 'Old title',
		];
		$expected = [
			'title' => 'Test title',
		];
		$this->assertEquals( $expected, $instance->update( $new_instance, $old_instance ) );
	}

	/**
	 * @covers AnsPress_Stats_Widget::update
	 */
	public function testUpdateHTMLTagsOnTitleWithEmptyTitle() {
		$instance = new \AnsPress_Stats_Widget();

		// Test.
		$new_instance = [
			'title' => '<strong></strong>',
		];
		$old_instance = [
			'title' => 'Old title',
		];
		$expected = [
			'title' => '',
		];
		$this->assertEquals( $expected, $instance->update( $new_instance, $old_instance ) );
	}

	/**
	 * @covers AnsPress_Stats_Widget::update
	 */
	public function testUpdateEmptyTitle() {
		$instance = new \AnsPress_Stats_Widget();

		// Test.
		$new_instance = [
			'title' => '',
		];
		$old_instance = [
			'title' => 'Old title',
		];
		$expected = [
			'title' => '',
		];
		$this->assertEquals( $expected, $instance->update( $new_instance, $old_instance ) );
	}
}
