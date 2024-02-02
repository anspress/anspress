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

	/**
	 * @covers AP_Askform_Widget::__construct
	 */
	public function testConstruct() {
		$instance = new \AP_Askform_Widget();
		$this->assertEquals( strtolower( 'ap_askform_widget' ), $instance->id_base );
		$this->assertEquals( '(AnsPress) Ask form', $instance->name );
		$this->assertEquals( 'AnsPress ask form widget', $instance->widget_options['description'] );
	}

	/**
	 * @covers AP_Askform_Widget::widget
	 */
	public function testWidget() {
		$instance = new \AP_Askform_Widget();

		// Add args.
		$args = [
			'before_widget' => '<section class="widget">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		];

		// Test begins.
		// Test 1.
		$instance_title = [
			'title' => 'Ask Form Title',
		];
		ob_start();
		$instance->widget( $args, $instance_title );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<section class="widget">', $result );
		$this->assertStringContainsString( '<h2 class="widget-title">', $result );
		$this->assertStringContainsString( 'Ask Form Title', $result );
		$this->assertStringContainsString( '<div id="ap-ask-page" class="ap-widget-inner">', $result );
		$this->assertStringContainsString( 'name="form_question[post_title]"', $result );
		$this->assertStringContainsString( 'name="form_question[post_content]"', $result );

		// Test 2.
		$instance_notitle = [
			'title' => '',
		];
		ob_start();
		$instance->widget( $args, $instance_notitle );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<section class="widget">', $result );
		$this->assertStringNotContainsString( '<h2 class="widget-title">', $result );
		$this->assertStringContainsString( '<div id="ap-ask-page" class="ap-widget-inner">', $result );
		$this->assertStringContainsString( 'name="form_question[post_title]"', $result );
		$this->assertStringContainsString( 'name="form_question[post_content]"', $result );
	}

	/**
	 * @covers AP_Askform_Widget::form
	 */
	public function testForm() {
		$instance = new \AP_Askform_Widget();

		// Test begins.
		// Test 1.
		$instance_title = [
			'title' => 'Ask Form Title',
		];
		ob_start();
		$instance->form( $instance_title );
		$result = ob_get_clean();
		$this->assertStringContainsString( '[][title]', $result );
		$this->assertStringContainsString( 'value="Ask Form Title"', $result );

		// Test 2.
		$instance_notitle = [
			'title' => '',
		];
		ob_start();
		$instance->form( $instance_notitle );
		$result = ob_get_clean();
		$this->assertStringContainsString( '[][title]', $result );
		$this->assertStringContainsString( 'value=""', $result );
	}

	/**
	 * @covers AP_Askform_Widget::update
	 */
	public function testUpdate() {
		$instance = new \AP_Askform_Widget();

		// Test begins.
		// Test 1.
		$new_title = [
			'title' => '<script>alert("Malicious Script")</script>Ask Form Widget Title',
		];
		$old_title = [
			'title' => 'Previous Widget Title',
		];
		$updated_title = $instance->update( $new_title, $old_title );
		$this->assertEquals( 'Ask Form Widget Title', $updated_title['title'] );

		// Test 2.
		$new_notitle = [
			'title' => '',
		];
		$old_notitle = [
			'title' => 'Previous Widget Title',
		];
		$updated_notitle = $instance->update( $new_notitle, $old_notitle );
		$this->assertEquals( '', $updated_notitle['title'] );
	}
}
