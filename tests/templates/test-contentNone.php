<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestTemplatesContentNone extends TestCase {

	public function testContentNone() {
		// Test 1.
		ob_start();
		@ap_get_template_part( 'content-none' );
		$output = ob_get_clean();
		$this->assertStringContainsString( '<article id="post-0" class="clearfix">', $output );
		$this->assertStringContainsString( '<div class="no-questions">', $output );
		$this->assertStringContainsString( 'Sorry! No question found.', $output );
		$this->assertStringContainsString( '</article><!-- list item -->', $output );
		$this->assertStringNotContainsString( '<div class="ap-pagging-warning">', $output );

		// Test 2.
		$_SERVER['REQUEST_URI'] = home_url( '/' );
		set_query_var( 'paged', 2 );
		ob_start();
		@ap_get_template_part( 'content-none' );
		$output = ob_get_clean();
		$this->assertStringContainsString( '<article id="post-0" class="clearfix">', $output );
		$this->assertStringContainsString( '<div class="no-questions">', $output );
		$this->assertStringContainsString( 'Sorry! No question found.', $output );
		$this->assertStringContainsString( '<div class="ap-pagging-warning">', $output );
		$this->assertStringContainsString( 'Showing results with pagination active, you are currently on page 2. Click here to return to the initial page', $output );
		$this->assertStringContainsString( '<a href="?paged=1">go to page 1</a>', $output );
		$this->assertStringContainsString( '</article><!-- list item -->', $output );

		// Test 3.
		$_SERVER['REQUEST_URI'] = home_url( '/' );
		set_query_var( 'paged', 5 );
		ob_start();
		@ap_get_template_part( 'content-none' );
		$output = ob_get_clean();
		$this->assertStringContainsString( '<article id="post-0" class="clearfix">', $output );
		$this->assertStringContainsString( '<div class="no-questions">', $output );
		$this->assertStringContainsString( 'Sorry! No question found.', $output );
		$this->assertStringContainsString( '<div class="ap-pagging-warning">', $output );
		$this->assertStringContainsString( 'Showing results with pagination active, you are currently on page 5. Click here to return to the initial page', $output );
		$this->assertStringContainsString( '<a href="?paged=1">go to page 1</a>', $output );
		$this->assertStringContainsString( '</article><!-- list item -->', $output );
	}
}
