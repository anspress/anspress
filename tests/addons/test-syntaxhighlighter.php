<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAddonSyntaxHighlighter extends TestCase {

	public function set_up() {
		parent::set_up();
		ap_activate_addon( 'syntaxhighlighter.php' );
	}

	public function tear_down() {
		parent::tear_down();
		ap_deactivate_addon( 'syntaxhighlighter.php' );
	}

	/**
	 * @covers Anspress\Addons\Syntax_Highlighter::instance
	 */
	public function testInstance() {
		$class = new \ReflectionClass( 'Anspress\Addons\Syntax_Highlighter' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic() );
	}

	public function testClassProperties() {
		$class = new \ReflectionClass( 'Anspress\Addons\Syntax_Highlighter' );
		$this->assertTrue( $class->hasProperty( 'brushes' ) && $class->getProperty( 'brushes' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'Anspress\Addons\Syntax_Highlighter', '__construct' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Syntax_Highlighter', 'scripts' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Syntax_Highlighter', 'brush' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Syntax_Highlighter', 'mce_before_init' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Syntax_Highlighter', 'editor_buttons' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Syntax_Highlighter', 'allowed_shortcodes' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Syntax_Highlighter', 'shortcode' ) );
	}

	/**
	 * @covers Anspress\Addons\Syntax_Highlighter::instance
	 */
	public function testInit() {
		$instance1 = \Anspress\Addons\Syntax_Highlighter::init();
		$this->assertInstanceOf( 'Anspress\Addons\Syntax_Highlighter', $instance1 );
		$instance2 = \Anspress\Addons\Syntax_Highlighter::init();
		$this->assertSame( $instance1, $instance2 );
	}
}
