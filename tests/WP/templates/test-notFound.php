<?php

namespace AnsPress\Tests\WP;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestTemplatesNotFound extends TestCase {

	public function testNotFound() {
		ob_start();
		ap_get_template_part( 'not-found' );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<h1>Error 404</h1>', $result );
	}
}
