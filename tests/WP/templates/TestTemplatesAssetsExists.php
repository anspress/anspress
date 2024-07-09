<?php

namespace AnsPress\Tests\WP;

use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * @coversNothing
 * @package AnsPress\Tests\WP
 */
class TestTemplatesAssetsExists extends TestCase {

	use Testcases\Common;

	public function testAssetsExists() {
		// Font files.
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/avatar-fonts/calibri.ttf' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/avatar-fonts/DeliusSwashCaps.ttf' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/avatar-fonts/Glegoo-Bold.ttf' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/avatar-fonts/OpenSans.ttf' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/avatar-fonts/Pacifico.ttf' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/avatar-fonts/SIL Open Font License.txt' );

		// CSS files.
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/css/fonts/anspress.eot' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/css/fonts/anspress.svg' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/css/fonts/anspress.ttf' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/css/fonts/anspress.woff' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/css/editor.css' );
	}
}
