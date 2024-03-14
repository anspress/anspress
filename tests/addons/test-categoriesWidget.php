<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAddonCategoriesWidget extends TestCase {

	public function testWidgetsInit() {
		$instance = \Anspress\Addons\Categories::init();
		anspress()->setup_hooks();
		$this->assertEquals( 10, has_action( 'widgets_init', [ $instance, 'widget' ] ) );
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
		$instance = new \Anspress\Widgets\Categories();
		$this->assertEquals( strtolower( 'AnsPress_Category_Widget' ), $instance->id_base );
		$this->assertEquals( '(AnsPress) Categories', $instance->name );
		$this->assertEquals( 'Display AnsPress categories', $instance->widget_options['description'] );
	}
}
