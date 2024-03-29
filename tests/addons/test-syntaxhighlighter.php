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

	public function testInit() {
		$instance1 = \Anspress\Addons\Syntax_Highlighter::init();
		$this->assertInstanceOf( 'Anspress\Addons\Syntax_Highlighter', $instance1 );
		$instance2 = \Anspress\Addons\Syntax_Highlighter::init();
		$this->assertSame( $instance1, $instance2 );
	}

	public function testHooksFilters() {
		$instance = \Anspress\Addons\Syntax_Highlighter::init();

		// Tests.
		$this->assertEquals( 10, has_filter( 'wp_enqueue_scripts', [ $instance, 'scripts' ] ) );
		$this->assertEquals( 10, has_filter( 'tiny_mce_before_init', [ $instance, 'mce_before_init' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_editor_buttons', [ $instance, 'editor_buttons' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_allowed_shortcodes', [ $instance, 'allowed_shortcodes' ] ) );
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
		$this->assertStringContainsString( 'window.apBrushPath = "' . esc_url( ANSPRESS_URL . 'addons/syntaxhighlighter/syntaxhighlighter/scripts/' ) . '";', $inline_script );
	}

	/**
	 * @covers Anspress\Addons\Syntax_Highlighter::mce_before_init
	 */
	public function testMCEBeforeInit() {
		$instance = \Anspress\Addons\Syntax_Highlighter::init();

		// Call the method.
		$mce_before_init = $instance->mce_before_init( [] );

		// Test begins.
		$this->assertNotEmpty( $mce_before_init );
		$this->assertArrayHasKey( 'extended_valid_elements', $mce_before_init );
		$this->assertStringContainsString( 'pre', $mce_before_init['extended_valid_elements'] );
		$this->assertStringContainsString( 'code', $mce_before_init['extended_valid_elements'] );
		$this->assertEquals( 'pre,code', $mce_before_init['extended_valid_elements'] );
	}

	/**
	 * @covers Anspress\Addons\Syntax_Highlighter::mce_before_init
	 */
	public function testMCEBeforeInitExtendedValidElementsSet() {
		$instance = \Anspress\Addons\Syntax_Highlighter::init();

		// Test begins.
		$mce_before_init = $instance->mce_before_init( [ 'extended_valid_elements' => 'code,pre,kbd,em,strong' ] );
		$this->assertNotEmpty( $mce_before_init );
		$this->assertArrayHasKey( 'extended_valid_elements', $mce_before_init );
		$this->assertStringContainsString( 'pre', $mce_before_init['extended_valid_elements'] );
		$this->assertStringContainsString( 'code', $mce_before_init['extended_valid_elements'] );
		$this->assertStringNotContainsString( 'kbd', $mce_before_init['extended_valid_elements'] );
		$this->assertStringNotContainsString( 'strong', $mce_before_init['extended_valid_elements'] );
		$this->assertEquals( 'pre,code', $mce_before_init['extended_valid_elements'] );
	}

	/**
	 * @covers Anspress\Addons\Syntax_Highlighter::mce_before_init
	 */
	public function testMCEBeforeInitExtendedValidElementsSetAsEmpty() {
		$instance = \Anspress\Addons\Syntax_Highlighter::init();

		// Test begins.
		$mce_before_init = $instance->mce_before_init( [ 'extended_valid_elements' => '' ] );
		$this->assertNotEmpty( $mce_before_init );
		$this->assertArrayHasKey( 'extended_valid_elements', $mce_before_init );
		$this->assertStringContainsString( 'pre', $mce_before_init['extended_valid_elements'] );
		$this->assertStringContainsString( 'code', $mce_before_init['extended_valid_elements'] );
		$this->assertEquals( 'pre,code', $mce_before_init['extended_valid_elements'] );
	}

	/**
	 * @covers Anspress\Addons\Syntax_Highlighter::shortcode
	 */
	public function testShortcode() {
		$this->assertTrue( shortcode_exists( 'apcode' ) );

		// Test via directly calling the method.
		$instance = \Anspress\Addons\Syntax_Highlighter::init();

		// Test with empty attributes.
		// Test 1.
		$content = '<pre>Some code</pre>';
		$shortcode = $instance->shortcode( [], $content );
		$this->assertEquals( '<pre class="brush: plain">Some code</pre>', $shortcode );

		// Test 2.
		$content = '<code>Some code</code>';
		$shortcode = $instance->shortcode( [], $content );
		$this->assertEquals( '<code class="brush: plain">Some code</code>', $shortcode );

		// Test 3.
		$content = 'Some texts';
		$shortcode = $instance->shortcode( [], $content );
		$this->assertEquals( $content, $shortcode );

		// Test with language attributes.
		// Test 1.
		$content = '<pre>Some code</pre>';
		$shortcode = $instance->shortcode( [ 'language' => 'php' ], $content );
		$this->assertEquals( '<pre class="brush: php">Some code</pre>', $shortcode );

		// Test 2.
		$content = '<code>Some code</code>';
		$shortcode = $instance->shortcode( [ 'language' => 'php' ], $content );
		$this->assertEquals( '<code class="brush: php">Some code</code>', $shortcode );

		// Test 3.
		$content = 'Some texts';
		$shortcode = $instance->shortcode( [ 'language' => 'php' ], $content );
		$this->assertEquals( $content, $shortcode );

		// Test with inline attribute.
		// Test 1.
		$content = '<pre>Some code<br><br /></pre>';
		$shortcode = $instance->shortcode( [ 'inline' => true ], $content );
		$this->assertEquals( '<pre class="brush: plain">Some code</pre>', $shortcode );

		// Test 2.
		$content = '<code>Some code<br><br /></code>';
		$shortcode = $instance->shortcode( [ 'inline' => true ], $content );
		$this->assertEquals( '<code class="brush: plain">Some code</code>', $shortcode );

		// Test 3.
		$content = 'Some texts<br><br /><br>';
		$shortcode = $instance->shortcode( [ 'inline' => true ], $content );
		$this->assertEquals( 'Some texts', $shortcode );

		// Test with both language and inline attributes.
		// Test 1.
		$content = '<pre>Some code</pre>';
		$shortcode = $instance->shortcode( [ 'language' => 'php', 'inline' => true ], $content );
		$this->assertEquals( '<pre class="brush: php">Some code</pre>', $shortcode );

		// Test 2.
		$content = '<code>Some code</code>';
		$shortcode = $instance->shortcode( [ 'language' => 'php', 'inline' => true ], $content );
		$this->assertEquals( '<code class="brush: php">Some code</code>', $shortcode );

		// Test 3.
		$content = 'Some texts';
		$shortcode = $instance->shortcode( [ 'language' => 'php', 'inline' => true ], $content );
		$this->assertEquals( $content, $shortcode );

		// Test via shortcode.
		// Test with empty attributes.
		// Test 1.
		$content = '<pre>Some code</pre>';
		$shortcode = do_shortcode( '[apcode]' . $content . '[/apcode]' );
		$this->assertEquals( '<pre class="brush: plain">Some code</pre>', $shortcode );

		// Test 2.
		$content = '<code>Some code</code>';
		$shortcode = do_shortcode( '[apcode]' . $content . '[/apcode]' );
		$this->assertEquals( '<code class="brush: plain">Some code</code>', $shortcode );

		// Test 3.
		$content = 'Some texts';
		$shortcode = do_shortcode( '[apcode]' . $content . '[/apcode]' );
		$this->assertEquals( $content, $shortcode );

		// Test with language attributes.
		// Test 1.
		$content = '<pre>Some code</pre>';
		$shortcode = do_shortcode( '[apcode language="php"]' . $content . '[/apcode]' );
		$this->assertEquals( '<pre class="brush: php">Some code</pre>', $shortcode );

		// Test 2.
		$content = '<code>Some code</code>';
		$shortcode = do_shortcode( '[apcode language="php"]' . $content . '[/apcode]' );
		$this->assertEquals( '<code class="brush: php">Some code</code>', $shortcode );

		// Test 3.
		$content = 'Some texts';
		$shortcode = do_shortcode( '[apcode language="php"]' . $content . '[/apcode]' );
		$this->assertEquals( $content, $shortcode );

		// Test with inline attribute.
		// Test 1.
		$content = '<pre>Some code<br><br /></pre>';
		$shortcode = do_shortcode( '[apcode inline="true"]' . $content . '[/apcode]' );
		$this->assertEquals( '<pre class="brush: plain">Some code</pre>', $shortcode );

		// Test 2.
		$content = '<code>Some code<br><br /></code>';
		$shortcode = do_shortcode( '[apcode inline="true"]' . $content . '[/apcode]' );
		$this->assertEquals( '<code class="brush: plain">Some code</code>', $shortcode );

		// Test 3.
		$content = 'Some texts<br><br /><br>';
		$shortcode = do_shortcode( '[apcode inline="true"]' . $content . '[/apcode]' );
		$this->assertEquals( 'Some texts', $shortcode );

		// Test with both language and inline attributes.
		// Test 1.
		$content = '<pre>Some code</pre>';
		$shortcode = do_shortcode( '[apcode language="php" inline="true"]' . $content . '[/apcode]' );
		$this->assertEquals( '<pre class="brush: php">Some code</pre>', $shortcode );

		// Test 2.
		$content = '<code>Some code</code>';
		$shortcode = do_shortcode( '[apcode language="php" inline="true"]' . $content . '[/apcode]' );
		$this->assertEquals( '<code class="brush: php">Some code</code>', $shortcode );

		// Test 3.
		$content = 'Some texts';
		$shortcode = do_shortcode( '[apcode language="php" inline="true"]' . $content . '[/apcode]' );
		$this->assertEquals( $content, $shortcode );
	}

	/**
	 * @covers Anspress\Addons\Syntax_Highlighter::allowed_shortcodes
	 */
	public function testAllowedShortcodes() {
		$instance = \Anspress\Addons\Syntax_Highlighter::init();

		// Call the method.
		$allowed_shortcodes = $instance->allowed_shortcodes( [] );

		// Test begins.
		$this->assertNotEmpty( $allowed_shortcodes );
		$this->assertIsArray( $allowed_shortcodes );
		$this->assertContains( 'apcode', $allowed_shortcodes );
	}

	/**
	 * @covers Anspress\Addons\Syntax_Highlighter::editor_buttons
	 */
	public function testEditorButtons() {
		$instance = \Anspress\Addons\Syntax_Highlighter::init();

		// Test begins.
		$field = new \AnsPress\Form\Field\Editor( 'Sample Form', 'sample-form', [] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertEmpty( $property->getValue( $field ) );
		$instance->editor_buttons( '', $field );
		$this->assertNotEmpty( $property->getValue( $field ) );
		$this->assertEquals( '<button type="button" class="ap-btn-insertcode ap-btn-small ap-btn mb-10 ap-mr-5" apinsertcode><i class="apicon-code ap-mr-3"></i>Insert Code</button>', $property->getValue( $field ) );
	}

	/**
	 * @covers Anspress\Addons\Syntax_Highlighter::editor_buttons
	 */
	public function testEditorButtonsWithFields() {
		$instance = \Anspress\Addons\Syntax_Highlighter::init();

		// Test begins.
		$field = new \AnsPress\Form\Field\Editor( 'Editor Form', 'editor-form', [
			'fields' => [
				'editor' => [
					'type'  => 'editor',
					'label' => 'Editor',
				]
			]
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertEmpty( $property->getValue( $field ) );
		$instance->editor_buttons( '', $field );
		$this->assertNotEmpty( $property->getValue( $field ) );
		$this->assertEquals( '<button type="button" class="ap-btn-insertcode ap-btn-small ap-btn mb-10 ap-mr-5" apinsertcode><i class="apicon-code ap-mr-3"></i>Insert Code</button>', $property->getValue( $field ) );
	}
}
