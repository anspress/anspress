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

	/**
	 * @covers AP_Questions_Widget::form
	 */
	public function testForm() {
		$instance = new \AP_Questions_Widget();

		// Register taxonomy.
		register_taxonomy( 'question_category', 'question' );

		// Test.
		$instance_data = [
			'title'        => 'Test title',
			'order_by'     => 'newest',
			'limit'        => 10,
			'category_ids' => '1,2,3',
		];
		ob_start();
		$instance->form( $instance_data );
		$result = ob_get_clean();

		// For title.
		$this->assertStringContainsString( 'Title:', $result );
		$this->assertStringContainsString( '[][title]', $result );
		$this->assertStringContainsString( 'value="Test title"', $result );

		// For order_by.
		$this->assertStringContainsString( 'Order by:', $result );
		$this->assertStringContainsString( '[][order_by]', $result );
		$this->assertStringContainsString( 'selected=\'selected\' value="newest"', $result );
		$this->assertStringContainsString( 'value="active"', $result );
		$this->assertStringContainsString( 'value="voted"', $result );
		$this->assertStringContainsString( 'value="answers"', $result );
		$this->assertStringContainsString( 'value="unanswered"', $result );

		// For category_ids.
		$this->assertStringContainsString( 'Category IDs:', $result );
		$this->assertStringContainsString( '[][category_ids]', $result );
		$this->assertStringContainsString( 'value="1,2,3"', $result );
		$this->assertStringContainsString( 'Comma separated AnsPress category ids', $result );

		// For limit.
		$this->assertStringContainsString( 'Limit:', $result );
		$this->assertStringContainsString( '[][limit]', $result );
		$this->assertStringContainsString( 'value="10"', $result );

		// Unregister taxonomy.
		unregister_taxonomy( 'question_category' );
	}

	/**
	 * @covers AP_Questions_Widget::form
	 */
	public function testFormWithoutCategory() {
		$instance = new \AP_Questions_Widget();

		// Test.
		$instance_data = [
			'title'        => 'Category title',
			'order_by'     => 'voted',
			'limit'        => 3,
			'category_ids' => '1,2,3',
		];
		ob_start();
		$instance->form( $instance_data );
		$result = ob_get_clean();

		// For title.
		$this->assertStringContainsString( 'Title:', $result );
		$this->assertStringContainsString( '[][title]', $result );
		$this->assertStringContainsString( 'value="Category title"', $result );

		// For order_by.
		$this->assertStringContainsString( 'Order by:', $result );
		$this->assertStringContainsString( '[][order_by]', $result );
		$this->assertStringContainsString( 'selected=\'selected\' value="voted"', $result );
		$this->assertStringContainsString( 'value="active"', $result );
		$this->assertStringContainsString( 'value="newest"', $result );
		$this->assertStringContainsString( 'value="answers"', $result );
		$this->assertStringContainsString( 'value="unanswered"', $result );

		// For limit.
		$this->assertStringContainsString( 'Limit:', $result );
		$this->assertStringContainsString( '[][limit]', $result );
		$this->assertStringContainsString( 'value="3"', $result );

		// For category_ids.
		$this->assertStringNotContainsString( 'Category IDs:', $result );
		$this->assertStringNotContainsString( '[][category_ids]', $result );
		$this->assertStringNotContainsString( 'value="1,2,3"', $result );
		$this->assertStringNotContainsString( 'Comma separated AnsPress category ids', $result );
	}

	/**
	 * @covers AP_Questions_Widget::form
	 */
	public function testFormWithDefaultValues() {
		$instance = new \AP_Questions_Widget();

		// Register taxonomy.
		register_taxonomy( 'question_category', 'question' );

		// Test.
		$instance_data = [];
		ob_start();
		$instance->form( $instance_data );
		$result = ob_get_clean();

		// For title.
		$this->assertStringContainsString( 'Title:', $result );
		$this->assertStringContainsString( '[][title]', $result );
		$this->assertStringContainsString( 'value="Questions"', $result );

		// For order_by.
		$this->assertStringContainsString( 'Order by:', $result );
		$this->assertStringContainsString( '[][order_by]', $result );
		$this->assertStringContainsString( 'selected=\'selected\' value="active"', $result );
		$this->assertStringContainsString( 'value="newest"', $result );
		$this->assertStringContainsString( 'value="voted"', $result );
		$this->assertStringContainsString( 'value="answers"', $result );
		$this->assertStringContainsString( 'value="unanswered"', $result );

		// For category_ids.
		$this->assertStringContainsString( 'Category IDs:', $result );
		$this->assertStringContainsString( '[][category_ids]', $result );
		$this->assertStringContainsString( 'value=""', $result );
		$this->assertStringContainsString( 'Comma separated AnsPress category ids', $result );

		// For limit.
		$this->assertStringContainsString( 'Limit:', $result );
		$this->assertStringContainsString( '[][limit]', $result );
		$this->assertStringContainsString( 'value="5"', $result );

		// Unregister taxonomy.
		unregister_taxonomy( 'question_category' );
	}
}
