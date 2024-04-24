<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestTemplatesNoTagsFound extends TestCase {

	public function testNoTagsFound() {
		ob_start();
		ap_get_template_part( 'addons/tag/no-tags-found' );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<div class="ap-no-category-found ap-404">', $result );
		$this->assertStringContainsString( '<p class="ap-notice ap-yellow">', $result );
		$this->assertStringContainsString( 'No tags is set!', $result );
	}
}
