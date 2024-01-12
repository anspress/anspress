<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestTemplatesFunctions extends TestCase {

	/**
	 * @covers ::ap_widgets_positions
	 */
	public function testAPWidgetsPositions() {
		$this->assertEquals( 10, has_action( 'widgets_init', 'ap_widgets_positions' ) );
		global $wp_registered_sidebars;
		$this->assertArrayHasKey( 'ap-before', $wp_registered_sidebars );
		$this->assertArrayHasKey( 'ap-top', $wp_registered_sidebars );
		$this->assertArrayHasKey( 'ap-sidebar', $wp_registered_sidebars );
		$this->assertArrayHasKey( 'ap-qsidebar', $wp_registered_sidebars );
		$this->assertArrayHasKey( 'ap-category', $wp_registered_sidebars );
		$this->assertArrayHasKey( 'ap-tag', $wp_registered_sidebars );
		$this->assertArrayHasKey( 'ap-author', $wp_registered_sidebars );
		$this->assertEquals( 'ap-before', $wp_registered_sidebars['ap-before']['id'] );
		$this->assertEquals( 'ap-top', $wp_registered_sidebars['ap-top']['id'] );
		$this->assertEquals( 'ap-sidebar', $wp_registered_sidebars['ap-sidebar']['id'] );
		$this->assertEquals( 'ap-qsidebar', $wp_registered_sidebars['ap-qsidebar']['id'] );
		$this->assertEquals( 'ap-category', $wp_registered_sidebars['ap-category']['id'] );
		$this->assertEquals( 'ap-tag', $wp_registered_sidebars['ap-tag']['id'] );
		$this->assertEquals( 'ap-author', $wp_registered_sidebars['ap-author']['id'] );
		$this->assertEquals( '(AnsPress) Before', $wp_registered_sidebars['ap-before']['name'] );
		$this->assertEquals( '(AnsPress) Question List Top', $wp_registered_sidebars['ap-top']['name'] );
		$this->assertEquals( '(AnsPress) Sidebar', $wp_registered_sidebars['ap-sidebar']['name'] );
		$this->assertEquals( '(AnsPress) Question Sidebar', $wp_registered_sidebars['ap-qsidebar']['name'] );
		$this->assertEquals( '(AnsPress) Category Page', $wp_registered_sidebars['ap-category']['name'] );
		$this->assertEquals( '(AnsPress) Tag page', $wp_registered_sidebars['ap-tag']['name'] );
		$this->assertEquals( '(AnsPress) Author page', $wp_registered_sidebars['ap-author']['name'] );
	}
}
