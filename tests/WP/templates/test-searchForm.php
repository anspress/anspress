<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestTemplatesSearchForm extends TestCase {

	public function testSearchForm() {
		ob_start();
		ap_get_template_part( 'search-form' );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<form id="ap-search-form" class="ap-search-form" action="' . esc_url( home_url( '/' ) ) . '', $result );
		$this->assertStringContainsString( '<button class="ap-btn ap-search-btn" type="submit">Search</button>', $result );
		$this->assertStringContainsString( '<div class="ap-search-inner no-overflow">', $result );
		$this->assertStringContainsString( '<input name="s" type="text" class="ap-search-input ap-form-input" placeholder="Search questions..." value="" />', $result );
		$this->assertStringContainsString( '<input type="hidden" name="post_type" value="question" />', $result );
	}
}
