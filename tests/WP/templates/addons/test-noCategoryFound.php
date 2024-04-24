<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestTemplatesNoCategoryFound extends TestCase {

	public function testNoCategoryFound() {
		ob_start();
		ap_get_template_part( 'addons/category/no-category-found' );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<div class="ap-no-category-found ap-404">', $result );
		$this->assertStringContainsString( '<p class="ap-notice ap-yellow">', $result );
		$this->assertStringContainsString( 'No category is set!', $result );
	}
}
