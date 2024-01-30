<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestWidgetSearch extends TestCase {

	public function testWidgetsInit() {
		$this->assertEquals( 10, has_action( 'widgets_init', 'ap_search_register_widgets' ) );
		$this->assertTrue( class_exists( 'AP_Search_Widget' ) );
		$this->assertTrue( array_key_exists( 'AP_Search_Widget', $GLOBALS['wp_widget_factory']->widgets ) );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AP_Search_Widget', '__construct' ) );
		$this->assertTrue( method_exists( 'AP_Search_Widget', 'widget' ) );
		$this->assertTrue( method_exists( 'AP_Search_Widget', 'form' ) );
		$this->assertTrue( method_exists( 'AP_Search_Widget', 'update' ) );
	}

	/**
	 * @covers AP_Search_Widget::__construct
	 */
	public function testConstruct() {
		$instance = new \AP_Search_Widget();
		$this->assertEquals( strtolower( 'AP_Search_Widget' ), $instance->id_base );
		$this->assertEquals( '(AnsPress) Search', $instance->name );
		$this->assertEquals( 'Question and answer search form.', $instance->widget_options['description'] );
	}

	/**
	 * @covers AP_Search_Widget::widget
	 */
	public function testWidget() {
		$instance = new \AP_Search_Widget();

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
			'title' => 'Search Title',
		];
		ob_start();
		$instance->widget( $args, $instance_title );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<section class="widget">', $result );
		$this->assertStringContainsString( '<h2 class="widget-title">', $result );
		$this->assertStringContainsString( 'Search Title', $result );
		$this->assertStringContainsString( '<form id="ap-search-form" class="ap-search-form" action="' . esc_url( home_url( '/' ) ) . '', $result );
		$this->assertStringContainsString( '<button class="ap-btn ap-search-btn" type="submit">Search</button>', $result );
		$this->assertStringContainsString( '<div class="ap-search-inner no-overflow">', $result );
		$this->assertStringContainsString( '<input name="s" type="text" class="ap-search-input ap-form-input" placeholder="Search questions..." value="" />', $result );
		$this->assertStringContainsString( '<input type="hidden" name="post_type" value="question" />', $result );

		// Test 2.
		$instance_title = [
			'title' => '',
		];
		ob_start();
		$instance->widget( $args, $instance_title );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<section class="widget">', $result );
		$this->assertStringNotContainsString( '<h2 class="widget-title">', $result );
		$this->assertStringContainsString( '<form id="ap-search-form" class="ap-search-form" action="' . esc_url( home_url( '/' ) ) . '', $result );
		$this->assertStringContainsString( '<button class="ap-btn ap-search-btn" type="submit">Search</button>', $result );
		$this->assertStringContainsString( '<div class="ap-search-inner no-overflow">', $result );
		$this->assertStringContainsString( '<input name="s" type="text" class="ap-search-input ap-form-input" placeholder="Search questions..." value="" />', $result );
		$this->assertStringContainsString( '<input type="hidden" name="post_type" value="question" />', $result );
	}

	/**
	 * @covers AP_Search_Widget::form
	 */
	public function testForm() {
		$instance = new \AP_Search_Widget();

		// Test begins.
		// Test 1.
		$instance_title = [
			'title' => 'Search Title',
		];
		ob_start();
		$instance->form( $instance_title );
		$result = ob_get_clean();
		$this->assertStringContainsString( '[][title]', $result );
		$this->assertStringContainsString( 'value="Search Title"', $result );

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
	 * @covers AP_Search_Widget::update
	 */
	public function testUpdate() {
		$instance = new \AP_Search_Widget();

		// Test begins.
		// Test 1.
		$new_title = [
			'title' => '<script>alert("Malicious Script")</script>Sample Widget Title',
		];
		$old_title = [
			'title' => 'Previous Widget Title',
		];
		$updated_title = $instance->update( $new_title, $old_title );
		$this->assertEquals( 'Sample Widget Title', $updated_title['title'] );

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
