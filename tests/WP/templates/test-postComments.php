<?php

namespace AnsPress\Tests\WP;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestTemplatesPostComments extends TestCase {

	public function testPostComments() {
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/post-comments.php' );
		ob_start();
		ap_get_template_part( 'post-comments' );
		$output = ob_get_clean();
		$this->assertEmpty( $output );
	}
}
