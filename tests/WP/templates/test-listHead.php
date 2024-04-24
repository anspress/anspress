<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestTemplatesListHead extends TestCase {

	use Testcases\Common;

	public function testListHead() {
		// Test 1.
		ob_start();
		ap_get_template_part( 'list-head' );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<div class="ap-list-head clearfix">', $result );
		$this->assertStringContainsString( '<a class="ap-btn-ask" href="' . esc_url( ap_get_link_to( 'ask' ) ) . '">Ask question</a>', $result );
		$this->assertStringContainsString( '<form id="ap-search-form" class="ap-search-form" action="' . esc_url( home_url( '/' ) ) . '">', $result );
		$this->assertStringContainsString( '<form id="ap-filters" class="ap-filters clearfix" method="GET">', $result );
		$this->assertStringContainsString( '<button id="ap-filter-reset" type="submit" name="reset-filter" title="Reset sorting and filter"><i class="apicon-x"></i><span>Clear Filter</span></button>', $result );

		// Test 2.
		$this->setRole( 'subscriber' );
		update_user_meta( get_current_user_id(), '__ap_unpublished_questions', 1 );
		ob_start();
		ap_get_template_part( 'list-head' );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<div class="ap-list-head clearfix">', $result );
		$this->assertStringContainsString( '<a class="ap-btn-ask" href="' . esc_url( ap_get_link_to( 'ask' ) ) . '">Ask question</a>', $result );
		$this->assertStringContainsString( '<form id="ap-search-form" class="ap-search-form" action="' . esc_url( home_url( '/' ) ) . '">', $result );
		$this->assertStringContainsString( '<form id="ap-filters" class="ap-filters clearfix" method="GET">', $result );
		$this->assertStringContainsString( '<button id="ap-filter-reset" type="submit" name="reset-filter" title="Reset sorting and filter"><i class="apicon-x"></i><span>Clear Filter</span></button>', $result );
		$this->assertStringContainsString( '<div class="ap-unpublished-alert ap-alert warning"><i class="apicon-pin"></i>', $result );
		$this->assertStringContainsString( 'Your <a href="' . esc_url( ap_get_link_to( '/' ) ) . '?unpublished=true">1 question is</a> unpublished', $result );

		// Test 3.
		$this->setRole( 'subscriber' );
		update_user_meta( get_current_user_id(), '__ap_unpublished_questions', 5 );
		ob_start();
		ap_get_template_part( 'list-head' );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<div class="ap-list-head clearfix">', $result );
		$this->assertStringContainsString( '<a class="ap-btn-ask" href="' . esc_url( ap_get_link_to( 'ask' ) ) . '">Ask question</a>', $result );
		$this->assertStringContainsString( '<form id="ap-search-form" class="ap-search-form" action="' . esc_url( home_url( '/' ) ) . '">', $result );
		$this->assertStringContainsString( '<form id="ap-filters" class="ap-filters clearfix" method="GET">', $result );
		$this->assertStringContainsString( '<button id="ap-filter-reset" type="submit" name="reset-filter" title="Reset sorting and filter"><i class="apicon-x"></i><span>Clear Filter</span></button>', $result );
		$this->assertStringContainsString( '<div class="ap-unpublished-alert ap-alert warning"><i class="apicon-pin"></i>', $result );
		$this->assertStringContainsString( 'Your <a href="' . esc_url( ap_get_link_to( '/' ) ) . '?unpublished=true">5 questions are</a> unpublished', $result );
	}
}
