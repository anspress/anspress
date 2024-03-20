<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestWidgetQuestions extends TestCase {

	public function testWidgetsInit() {
		do_action( 'widgets_init' );
		$this->assertEquals( 10, has_action( 'widgets_init', 'ap_questions_register_widgets' ) );
		$this->assertTrue( class_exists( 'AP_Questions_Widget' ) );
		ap_questions_register_widgets();
		$this->assertTrue( array_key_exists( 'AP_Questions_Widget', $GLOBALS['wp_widget_factory']->widgets ) );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AP_Questions_Widget', '__construct' ) );
		$this->assertTrue( method_exists( 'AP_Questions_Widget', 'widget' ) );
		$this->assertTrue( method_exists( 'AP_Questions_Widget', 'form' ) );
		$this->assertTrue( method_exists( 'AP_Questions_Widget', 'update' ) );
	}

	/**
	 * @covers AP_Questions_Widget::__construct
	 */
	public function testConstruct() {
		$instance = new \AP_Questions_Widget();
		$this->assertEquals( strtolower( 'ap_questions_widget' ), $instance->id_base );
		$this->assertEquals( '(AnsPress) Questions', $instance->name );
		$this->assertEquals( 'Shows list of question shorted by option.', $instance->widget_options['description'] );
	}

	/**
	 * @covers AP_Questions_Widget::update
	 */
	public function testUpdate() {
		$instance = new \AP_Questions_Widget();

		// Test.
		$new_instance = [
			'title'        => 'Test title',
			'avatar'       => '50',
			'order_by'     => 'newest',
			'limit'        => 10,
			'category_ids' => '1,2,3',
		];
		$old_instance = [
			'title'        => 'Old title',
			'avatar'       => '48',
			'order_by'     => 'newest',
			'limit'        => 5,
			'category_ids' => '4,5,6',
		];
		$expected = [
			'title'        => 'Test title',
			'avatar'       => '50',
			'order_by'     => 'newest',
			'limit'        => 10,
			'category_ids' => '1,2,3',
		];
		$this->assertEquals( $expected, $instance->update( $new_instance, $old_instance ) );
	}

	/**
	 * @covers AP_Questions_Widget::update
	 */
	public function testUpdateHTMLTagsOnTitle() {
		$instance = new \AP_Questions_Widget();

		// Test.
		$new_instance = [
			'title'        => '<h1 class="widget-title">Test title</h1>',
			'avatar'       => '50',
			'order_by'     => 'voted',
			'limit'        => '',
			'category_ids' => '1,2,3',
		];
		$old_instance = [
			'title'        => 'Old title',
			'avatar'       => '48',
			'order_by'     => 'newest',
			'limit'        => 10,
			'category_ids' => '4,5,6',
		];
		$expected = [
			'title'        => 'Test title',
			'avatar'       => '50',
			'order_by'     => 'voted',
			'limit'        => 5,
			'category_ids' => '1,2,3',
		];
		$this->assertEquals( $expected, $instance->update( $new_instance, $old_instance ) );
	}

	/**
	 * @covers AP_Questions_Widget::update
	 */
	public function testUpdateHTMLTagsOnAllOptions() {
		$instance = new \AP_Questions_Widget();

		// Test.
		$new_instance = [
			'title'        => '<strong>Test title</strong>',
			'avatar'       => '<div></div>',
			'order_by'     => 'voted',
			'limit'        => '<div>10</div>',
			'category_ids' => '<span>1</span>,2,<span>3</span>',
		];
		$old_instance = [
			'title'        => 'Old title',
			'avatar'       => '48',
			'order_by'     => 'newest',
			'limit'        => 5,
			'category_ids' => '4,5,6',
		];
		$expected = [
			'title'        => 'Test title',
			'avatar'       => '',
			'order_by'     => 'voted',
			'limit'        => 10,
			'category_ids' => '1,2,3',
		];
		$this->assertEquals( $expected, $instance->update( $new_instance, $old_instance ) );
	}

	/**
	 * @covers AP_Questions_Widget::update
	 */
	public function testUpdateEmptyTitle() {
		$instance = new \AP_Questions_Widget();

		// Test.
		$new_instance = [
			'title'        => '',
			'avatar'       => '50',
			'order_by'     => 'voted',
			'limit'        => 7,
			'category_ids' => '',
		];
		$old_instance = [
			'title'        => 'Old title',
			'avatar'       => '48',
			'order_by'     => 'newest',
			'limit'        => 5,
			'category_ids' => '4,5,6',
		];
		$expected = [
			'title'        => '',
			'avatar'       => '50',
			'order_by'     => 'voted',
			'limit'        => 7,
			'category_ids' => '',
		];
		$this->assertEquals( $expected, $instance->update( $new_instance, $old_instance ) );
	}
}
