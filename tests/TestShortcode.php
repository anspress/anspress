<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestShortcode extends TestCase {

	public function testClassProperties() {
		// For AnsPress_BasePage_Shortcode class.
		$class = new \ReflectionClass( 'AnsPress_BasePage_Shortcode' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isProtected() );
		$this->assertTrue( $class->hasProperty( 'current_page' ) && $class->getProperty( 'current_page' )->isPublic() );

		// For AnsPress_Question_Shortcode class.
		$class = new \ReflectionClass( 'AnsPress_Question_Shortcode' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isProtected() );
	}

	public function testMethodExists() {
		// For AnsPress_BasePage_Shortcode class.
		$this->assertTrue( method_exists( 'AnsPress_BasePage_Shortcode', 'get_instance' ) );
		$this->assertTrue( method_exists( 'AnsPress_BasePage_Shortcode', 'anspress_sc' ) );
		$this->assertTrue( method_exists( 'AnsPress_BasePage_Shortcode', 'attributes' ) );

		// For AnsPress_Question_Shortcode class.
		$this->assertTrue( method_exists( 'AnsPress_Question_Shortcode', 'get_instance' ) );
		$this->assertTrue( method_exists( 'AnsPress_Question_Shortcode', 'anspress_question_sc' ) );
	}

	/**
	 * @covers AnsPress_BasePage_Shortcode::get_instance
	 * @covers AnsPress_Question_Shortcode::get_instance
	 */
	public function testGetInstance() {
		// For AnsPress_BasePage_Shortcode class.
		$class = new \ReflectionClass( 'AnsPress_BasePage_Shortcode' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic());
		$this->assertTrue( shortcode_exists( 'anspress' ) );

		// For AnsPress_Question_Shortcode class.
		$class = new \ReflectionClass( 'AnsPress_BasePage_Shortcode' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic());
		$this->assertTrue( shortcode_exists( 'anspress' ) );

		// Test on instance match for AnsPress_BasePage_Shortcode class.
		$instance1 = \AnsPress_BasePage_Shortcode::get_instance();
		$this->assertInstanceOf( 'AnsPress_BasePage_Shortcode', $instance1 );
		$instance2 = \AnsPress_BasePage_Shortcode::get_instance();
		$this->assertSame( $instance1, $instance2 );

		// Test on instance match for AnsPress_Question_Shortcode class.
		$instance1 = \AnsPress_Question_Shortcode::get_instance();
		$this->assertInstanceOf( 'AnsPress_Question_Shortcode', $instance1 );
		$instance2 = \AnsPress_Question_Shortcode::get_instance();
		$this->assertSame( $instance1, $instance2 );
	}

	/**
	 * @covers AnsPress_BasePage_Shortcode::anspress_sc
	 */
	public function testAnspressSc() {
		$this->go_to( home_url() );
		global $ap_shortcode_loaded;
		$this->assertNotEquals( true, $ap_shortcode_loaded );
		ob_start();
		do_action( 'wp_enqueue_scripts' );
		ob_end_clean();

		$this->go_to( ap_get_link_to( 'base' ) );
		ap_enqueue_scripts();

		$this->assertTrue( wp_script_is( 'anspress-theme' ) );
		$this->assertTrue( wp_style_is( 'anspress-main' ) );
		$this->assertTrue( wp_style_is( 'anspress-fonts' ) );

		// Make sure shortcode does not echo anything.
		ob_start();
		$content = do_shortcode( '[anspress]' );
		$output  = ob_get_clean();
		$this->assertEquals( '', $output );
		$this->assertFalse( empty( $content ) );
	}

	/**
	 * @covers AnsPress_Common_Pages::base_page
	 */
	public function testBasePage() {
		$this->go_to( home_url() );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Sample question',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'test content',
			)
		);

		add_filter( 'the_content', [ $this, 'the_content' ] );
		$content = do_shortcode( '[anspress page="base"]' );
		remove_filter( 'the_content', [ $this, 'the_content' ] );

		$this->assertStringContainsString( 'id="anspress"', $content );
		$this->assertStringContainsString( 'id="ap-lists"', $content );
		$this->assertStringContainsString( 'class="ap-list-head', $content );
		$this->assertStringContainsString( 'Sample question', $content );
		$this->assertStringNotContainsString( 'AnsPress shortcode cannot be nested.', $content );
	}

	public function the_content( $content ) {
		$content = '[anspress]';
		return $content;
	}

	/**
	 * @covers AnsPress_BasePage_Shortcode::attributes
	 */
	public function testAttributes() {
		$instance = \AnsPress_BasePage_Shortcode::get_instance();

		// Set up attributes array.
		$atts = [
			'categories'          => 'category1, category2',
			'tags'                => 'tag1, tag2',
			'tax_relation'        => 'AND',
			'tags_operator'       => 'OR',
			'categories_operator' => 'IN',
			'page'                => 11,
			'hide_list_head'      => true,
			'order_by'            => 'date',
			'post_parent'         => 10,
		];

		// Invoke the attributes method.
		$instance->attributes( $atts, 'content' );

		// Test begins.
		global $wp;
		$this->assertEquals( [ 'category1', 'category2' ], $wp->query_vars['ap_categories'] );
		$this->assertEquals( [ 'tag1', 'tag2' ], $wp->query_vars['ap_tags'] );
		$this->assertEquals( 'AND', $wp->query_vars['ap_tax_relation'] );
		$this->assertEquals( 'OR', $wp->query_vars['ap_tags_operator'] );
		$this->assertEquals( 'IN', $wp->query_vars['ap_categories_operator'] );
		$this->assertEquals( 11, $instance->current_page );
		$this->assertEquals( 11, $_GET['ap_page'] );
		$this->assertEquals( 11, get_query_var( 'ap_page' ) );
		$this->assertEquals( true, get_query_var( 'ap_hide_list_head' ) );
		$this->assertEquals( true, $_GET['ap_hide_list_head'] );
		$this->assertEquals( [ 'order_by' => 'date' ], $_GET['filters'] );
		$this->assertEquals( 10, get_query_var( 'post_parent' ) );
	}

	/**
	 * @covers AnsPress_Question_Shortcode::anspress_question_sc
	 */
	public function testAnsPressQuestionSC() {
		$instance = \AnsPress_Question_Shortcode::get_instance();
		add_action( 'ap_before_question_shortcode', function() {} );

		$id = $this->factory()->post->create(
			array(
				'post_title'   => 'Sample question',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Sample question content',
			)
		);

		// Set up attributes array.
		$atts = [ 'ID' => $id ];

		// Test begins.
		// By directly invoking the method.
		$content = $instance->anspress_question_sc( $atts, 'content' );
		$this->assertStringContainsString( 'id="anspress"', $content );
		$this->assertStringContainsString( 'class="ap-eq"', $content );
		$this->assertStringContainsString( '<div id="anspress" class="ap-eq">', $content );
		$this->assertStringContainsString( '</div>', $content );
		$this->assertStringContainsString( 'Sample question', $content );
		$this->assertStringContainsString( 'Sample question content', $content );
		$this->assertTrue( did_action( 'ap_before_question_shortcode' ) > 0 );

		// By using the shortcode method.
		ob_start();
		$content = do_shortcode( '[question id="' . $id . '"]' );
		$output  = ob_get_clean();
		$this->assertEquals( '', $output );
		$this->assertFalse( empty( $content ) );
		$this->assertStringContainsString( 'id="anspress"', $content );
		$this->assertStringContainsString( 'id="anspress"', $content );
		$this->assertStringContainsString( 'class="ap-eq"', $content );
		$this->assertStringContainsString( '<div id="anspress" class="ap-eq">', $content );
		$this->assertStringContainsString( '</div>', $content );
		$this->assertStringContainsString( 'Sample question', $content );
		$this->assertStringContainsString( 'Sample question content', $content );
		$this->assertTrue( did_action( 'ap_before_question_shortcode' ) > 0 );

		// Test with invalid question id.
		$atts = [ 'ID' => -1 ];

		// Test begins.
		// By directly invoking the method.
		$content = $instance->anspress_question_sc( $atts, 'content' );
		$this->assertStringContainsString( '<div id="anspress" class="ap-eq">', $content );
		$this->assertStringContainsString( '</div>', $content );
		$this->assertStringContainsString( 'Invalid or non existing question id.', $content );
		$this->assertTrue( did_action( 'ap_before_question_shortcode' ) > 0 );

		// By using the shortcode method.
		ob_start();
		$content = do_shortcode( '[question id="-1"]' );
		$output  = ob_get_clean();
		$this->assertEquals( '', $output );
		$this->assertFalse( empty( $content ) );
		$this->assertStringContainsString( '<div id="anspress" class="ap-eq">', $content );
		$this->assertStringContainsString( '</div>', $content );
		$this->assertStringContainsString( 'Invalid or non existing question id.', $content );
		$this->assertTrue( did_action( 'ap_before_question_shortcode' ) > 0 );
	}

	/**
	 * @covers AnsPress_BasePage_Shortcode::anspress_sc
	 */
	public function testAnsPressScShouldReturnMessageThatShortcodeCantBeNested() {
		global $ap_shortcode_loaded;
		$ap_shortcode_loaded = true;

		// Action callback triggered.
		$callback_triggered = false;
		add_action( 'ap_before', function() use ( &$callback_triggered ) {
			$callback_triggered = true;
		} );

		// Test.
		$content = do_shortcode( '[anspress]' );
		$this->assertEquals( 'AnsPress shortcode cannot be nested.', $content );
		$this->assertStringNotContainsString( '<div id="anspress" class="anspress">', $content );
		$this->assertTrue( $ap_shortcode_loaded );
		$this->assertFalse( $callback_triggered );
		$ap_shortcode_loaded = false;
	}

	/**
	 * @covers AnsPress_Question_Shortcode::get_instance
	 */
	public function testAnsPressQuestionShortcodeGetInstanceOfSingletonWhenNull() {
		$reflectionClass = new \ReflectionClass( 'AnsPress_Question_Shortcode' );
		$property = $reflectionClass->getProperty( 'instance' );
		$property->setAccessible( true );
		$property->setValue( null );

		// Test.
		$instance = \AnsPress_Question_Shortcode::get_instance();
		$this->assertNotNull( $instance );
		$this->assertInstanceOf( 'AnsPress_Question_Shortcode', $instance );
	}

	/**
	 * @covers AnsPress_BasePage_Shortcode::get_instance
	 */
	public function testAnsPressBasePageShortcodeGetInstanceOfSingletonWhenNull() {
		$reflectionClass = new \ReflectionClass( 'AnsPress_BasePage_Shortcode' );
		$property = $reflectionClass->getProperty( 'instance' );
		$property->setAccessible( true );
		$property->setValue( null );

		// Test.
		$instance = \AnsPress_BasePage_Shortcode::get_instance();
		$this->assertNotNull( $instance );
		$this->assertInstanceOf( 'AnsPress_BasePage_Shortcode', $instance );
	}
}
