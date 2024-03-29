<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAddonCategoriesWidget extends TestCase {

	public function set_up() {
		parent::set_up();
		ap_activate_addon( 'categories.php' );
		$this->assertTrue(ap_is_addon_active('categories.php'));
	}

	public function testWidgetsInit() {
		$instance = \Anspress\Addons\Categories::init();
		$this->assertEquals( 10, has_action( 'widgets_init', [ $instance, 'widget' ] ) );
		do_action('widgets_init');

		$this->assertTrue( class_exists( 'Anspress\Widgets\Categories' ) );
		$this->assertTrue( array_key_exists( 'Anspress\Widgets\Categories', $GLOBALS['wp_widget_factory']->widgets ) );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'Anspress\Widgets\Categories', '__construct' ) );
		$this->assertTrue( method_exists( 'Anspress\Widgets\Categories', 'widget' ) );
		$this->assertTrue( method_exists( 'Anspress\Widgets\Categories', 'form' ) );
		$this->assertTrue( method_exists( 'Anspress\Widgets\Categories', 'update' ) );
	}

	/**
	 * @covers Anspress\Widgets\Categories::__construct
	 */
	public function testConstruct() {
		\Anspress\Addons\Categories::init();

		do_action('widgets_init');

		$instance = new \Anspress\Widgets\Categories();

		$this->assertEquals( strtolower( 'AnsPress_Category_Widget' ), $instance->id_base );
		$this->assertEquals( '(AnsPress) Categories', $instance->name );
		$this->assertEquals( 'Display AnsPress categories', $instance->widget_options['description'] );
	}

	public function testupdate() {
		\Anspress\Addons\Categories::init();

		do_action('widgets_init');

		$instance = new \Anspress\Widgets\Categories();

		// Test.
		$new_instance = [
			'title'       => 'Test title',
			'hide_empty'  => true,
			'parent'      => '3',
			'number'      => '10',
			'orderby'     => 'none',
			'order'       => 'DESC',
			'icon_width'  => 48,
			'icon_height' => 48,
		];
		$old_instance = [
			'title'       => 'Old title',
			'hide_empty'  => false,
			'parent'      => '',
			'number'      => '10',
			'orderby'     => 'id',
			'order'       => 'ASC',
			'icon_width'  => 30,
			'icon_height' => 30,
		];
		$expected = [
			'title'       => 'Test title',
			'hide_empty'  => true,
			'parent'      => '3',
			'number'      => '10',
			'orderby'     => 'none',
			'order'       => 'DESC',
			'icon_width'  => 48,
			'icon_height' => 48,
		];
		$this->assertEquals( $expected, $instance->update( $new_instance, $old_instance ) );
	}

	public function testUpdateHTMLTagsOnTitle() {
		\Anspress\Addons\Categories::init();

		do_action('widgets_init');

		$instance = new \Anspress\Widgets\Categories();

		// Test.
		$new_instance = [
			'title'       => '<h1 class="widget-title">Test title</h1>',
			'hide_empty'  => true,
			'parent'      => '3',
			'number'      => '10',
			'orderby'     => 'name',
			'order'       => 'ASC',
			'icon_width'  => 64,
			'icon_height' => 64,
		];
		$old_instance = [
			'title'       => 'Old title',
			'hide_empty'  => false,
			'parent'      => '',
			'number'      => '10',
			'orderby'     => 'slug',
			'order'       => 'ASC',
			'icon_width'  => 30,
			'icon_height' => 30,
		];
		$expected = [
			'title'       => 'Test title',
			'hide_empty'  => '1',
			'parent'      => '3',
			'number'      => '10',
			'orderby'     => 'name',
			'order'       => 'ASC',
			'icon_width'  => 64,
			'icon_height' => 64,
		];
		$this->assertEquals( $expected, $instance->update( $new_instance, $old_instance ) );
	}

	public function testUpdateWithEmptyOptions() {
		\Anspress\Addons\Categories::init();

		do_action('widgets_init');

		$instance = new \Anspress\Widgets\Categories();

		// Test.
		$new_instance = [
			'title'       => '',
			'hide_empty'  => '',
			'parent'      => '',
			'number'      => '',
			'orderby'     => '',
			'order'       => '',
			'icon_width'  => '',
			'icon_height' => '',
		];
		$old_instance = [
			'title'       => 'Old title',
			'hide_empty'  => true,
			'parent'      => '3',
			'number'      => '10',
			'orderby'     => 'name',
			'order'       => 'ASC',
			'icon_width'  => 64,
			'icon_height' => 64,
		];
		$expected = [
			'title'       => '',
			'hide_empty'  => false,
			'parent'      => '0',
			'number'      => '5',
			'orderby'     => 'count',
			'order'       => 'DESC',
			'icon_width'  => 32,
			'icon_height' => 32,
		];
		$this->assertEquals( $expected, $instance->update( $new_instance, $old_instance ) );
	}

	public function testUpdateHTMLTagsOnTitleWithEmptyTitle() {
		$instance = new \Anspress\Widgets\Categories();

		// Test.
		$new_instance = [
			'title'       => '<strong></strong>',
			'hide_empty'  => true,
			'parent'      => '3',
			'number'      => '10',
			'orderby'     => 'name',
			'order'       => 'ASC',
			'icon_width'  => 64,
			'icon_height' => 64,
		];
		$old_instance = [
			'title'       => 'Old title',
			'hide_empty'  => false,
			'parent'      => '',
			'number'      => '10',
			'orderby'     => 'slug',
			'order'       => 'ASC',
			'icon_width'  => 30,
			'icon_height' => 30,
		];
		$expected = [
			'title'       => '',
			'hide_empty'  => '1',
			'parent'      => '3',
			'number'      => '10',
			'orderby'     => 'name',
			'order'       => 'ASC',
			'icon_width'  => 64,
			'icon_height' => 64,
		];
		$this->assertEquals( $expected, $instance->update( $new_instance, $old_instance ) );
	}
}
