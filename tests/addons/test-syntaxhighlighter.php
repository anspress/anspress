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

	/**
	 * Return the available brushes lists.
	 *
	 * @return array
	 */
	public static function brushes() {
		return [
			'php'        => 'PHP',
			'css'        => 'CSS',
			'xml'        => 'XML/HTML',
			'jscript'    => 'Javascript',
			'sql'        => 'SQL',
			'bash'       => 'Bash/Shell',
			'clojure'    => 'Clojure',
			'cpp'        => 'C++/C',
			'csharp'     => 'C#',
			'delphi'     => 'Delphi',
			'diff'       => 'Diff',
			'erlang'     => 'Erlang',
			'fsharp'     => 'F#',
			'groovy'     => 'Groovy',
			'java'       => 'Java',
			'javafx'     => 'JavaFX',
			'latex'      => 'Latex',
			'plain'      => 'Plain text',
			'matlab'     => 'Matlabkey',
			'objc'       => 'Object',
			'perl'       => 'Perl',
			'powershell' => 'PowerShell',
			'python'     => 'Python',
			'r'          => 'R',
			'ruby'       => 'Ruby/Rails',
			'scala'      => 'Scala',
			'vb'         => 'VisualBasic',
		];
	}

	/**
	 * @covers Anspress\Addons\Syntax_Highlighter::brush
	 */
	public function testBrush() {
		$instance = \Anspress\Addons\Syntax_Highlighter::init();

		// Call the method.
		$brush = $instance->brush();

		// Get the brushes property.
		$brushes = $instance->brushes;

		// Test begins.
		$expected_brushes = self::brushes();
		$this->assertEquals( $expected_brushes, $brushes );
	}

	/**
	 * @covers Anspress\Addons\Syntax_Highlighter::scripts
	 */
	public function testScripts() {
		$instance = \Anspress\Addons\Syntax_Highlighter::init();

		// Assign the method contents in a variable.
		ob_start();
		$instance->scripts();
		do_action( 'wp_enqueue_scripts' );
		$scripts = ob_get_clean();

		// Test begins.
		$this->assertNotEmpty( $scripts );

		// Test for enqueued scripts and styles.
		$this->assertTrue( wp_script_is( 'syntaxhighlighter-core', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'syntaxhighlighter-autoloader', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'syntaxhighlighter', 'enqueued' ) );
		$this->assertTrue( wp_style_is( 'syntaxhighlighter-core', 'enqueued' ) );
		$this->assertTrue( wp_style_is( 'syntaxhighlighter-theme-default', 'enqueued' ) );

		// Test for script.
		$this->assertStringContainsString( '<script type="text/javascript">', $scripts );
		$this->assertStringContainsString( 'AP_Brushes = ', $scripts );
		$this->assertStringContainsString( wp_json_encode( self::brushes() ), $scripts );
		$this->assertStringContainsString( '</script>', $scripts );

		// Test for inline script.
		$inline_script = wp_scripts()->get_inline_script_data( 'syntaxhighlighter', 'before' );
		$this->assertStringContainsString( 'aplang = aplang||{};', $inline_script );
		$this->assertStringContainsString( 'aplang.shLanguage = \'Language\';', $inline_script );
		$this->assertStringContainsString( 'aplang.shInline = \'Is inline?\';', $inline_script );
		$this->assertStringContainsString( 'aplang.shTxtPlholder = \'Insert code snippet here ...\';', $inline_script );
		$this->assertStringContainsString( 'aplang.shButton = \'Insert to editor\';', $inline_script );
		$this->assertStringContainsString( 'aplang.shTitle = \'Insert code\';', $inline_script );
		$this->assertStringContainsString( 'window.apBrushPath = "' . esc_url( ANSPRESS_URL . '/addons/syntaxhighlighter/syntaxhighlighter/scripts/' ) . '";', $inline_script );
	}
}
