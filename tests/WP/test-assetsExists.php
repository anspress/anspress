<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAssetsExists extends TestCase {

	use Testcases\Common;

	public function testAssetsExists() {
		// Image files.
		$this->assertFileExists( ANSPRESS_DIR . '/assets/images/backbone-js.svg' );
		$this->assertFileExists( ANSPRESS_DIR . '/assets/images/cancel.svg' );
		$this->assertFileExists( ANSPRESS_DIR . '/assets/images/coding.svg' );
		$this->assertFileExists( ANSPRESS_DIR . '/assets/images/laptop.svg' );
		$this->assertFileExists( ANSPRESS_DIR . '/assets/images/line-chart.svg' );
		$this->assertFileExists( ANSPRESS_DIR . '/assets/images/more_functions.svg' );
		$this->assertFileExists( ANSPRESS_DIR . '/assets/images/php.svg' );
		$this->assertFileExists( ANSPRESS_DIR . '/assets/images/plug.svg' );
		$this->assertFileExists( ANSPRESS_DIR . '/assets/images/server.svg' );
		$this->assertFileExists( ANSPRESS_DIR . '/assets/images/user.svg' );
		$this->assertFileExists( ANSPRESS_DIR . '/assets/answer.png' );
		$this->assertFileExists( ANSPRESS_DIR . '/assets/question.png' );

		// JS files.
		$this->assertFileExists( ANSPRESS_DIR . '/assets/js/lib/selectize.min.js' );
		$this->assertFileExists( ANSPRESS_DIR . '/assets/js/admin-app.js' );
		$this->assertFileExists( ANSPRESS_DIR . '/assets/js/ap-admin.js' );
		$this->assertFileExists( ANSPRESS_DIR . '/assets/js/ask.js' );
		$this->assertFileExists( ANSPRESS_DIR . '/assets/js/common.js' );
		$this->assertFileExists( ANSPRESS_DIR . '/assets/js/list.js' );
		$this->assertFileExists( ANSPRESS_DIR . '/assets/js/notifications.js' );
		$this->assertFileExists( ANSPRESS_DIR . '/assets/js/question.js' );
		$this->assertFileExists( ANSPRESS_DIR . '/assets/js/tags.js' );

		// CSS files.
		// $this->assertFileExists( ANSPRESS_DIR . '/assets/ap-admin.css' );

		// SCSS files.
		$this->assertFileExists( ANSPRESS_DIR . '/assets/ap-admin.scss' );
	}
}
