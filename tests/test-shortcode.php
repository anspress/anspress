<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class Test_Shortcode extends TestCase {

	/**
	 * @covers AnsPress_BasePage_Shortcode::get_instance
	 */
	public function testGetInstance() {
		$class = new \ReflectionClass('AnsPress_BasePage_Shortcode');
		$this->assertTrue($class->hasProperty('instance') && $class->getProperty('instance')->isStatic());
		$this->assertTrue( shortcode_exists( 'anspress' ), 'anspress shortcode not registered.' );
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
