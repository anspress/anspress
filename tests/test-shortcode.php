<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class Test_Shortcode extends TestCase {

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
		$question_id = $this->factory->post->create(
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
}
