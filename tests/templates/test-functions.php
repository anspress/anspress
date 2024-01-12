<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestTemplatesFunctions extends TestCase {

	use Testcases\Common;

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

	/**
	 * @covers ::ap_scripts_front
	 */
	public function testAPScriptsFront() {
		$this->assertEquals( 1, has_action( 'wp_enqueue_scripts', 'ap_scripts_front' ) );

		// Required for testing.
		ob_start();
		do_action( 'wp_enqueue_scripts' );
		ob_end_clean();

		// Test for non AnsPress pages.
		$this->go_to( '/' );
		ob_start();
		$required_scripts = ap_scripts_front();
		ob_get_clean();
		$this->assertNull( $required_scripts );

		// Test by visiting AnsPress related page.
		$id = $this->insert_question();
		$this->go_to( '/?post_type=question&p=' . $id );
		ob_start();
		$required_scripts = ap_scripts_front();
		$output = ob_get_clean();
		$this->assertNull( $required_scripts );

		// Add action hook.
		add_action( 'ap_enqueue', function() {} );

		// Test for enqueues and registered scripts
		// as well as echo of script tags.
		$this->assertTrue( did_action( 'ap_enqueue' ) > 0 );
		$this->assertTrue( wp_style_is( 'anspress-main', 'enqueued' ) );
		$this->assertTrue( wp_style_is( 'ap-overrides', 'enqueued' ) );
		$this->assertStringContainsString( 'ajaxurl', $output );
		$this->assertStringContainsString( 'ap_nonce', $output );
		$this->assertStringContainsString( 'ap_nonce', $output );
		$this->assertStringContainsString( 'apTemplateUrl', $output );
		$this->assertStringContainsString( 'apQuestionID', $output );
		$this->assertStringContainsString( 'aplang', $output );
		$this->assertStringContainsString( 'disable_q_suggestion', $output );
		$this->assertStringContainsString( 'loading', $output );
		$this->assertStringContainsString( 'sending', $output );
		$this->assertStringContainsString( 'file_size_error', $output );
		$this->assertStringContainsString( 'attached_max', $output );
		$this->assertStringContainsString( 'commented', $output );
		$this->assertStringContainsString( 'comment', $output );
		$this->assertStringContainsString( 'cancel', $output );
		$this->assertStringContainsString( 'update', $output );
		$this->assertStringContainsString( 'your_comment', $output );
		$this->assertStringContainsString( 'notifications', $output );
		$this->assertStringContainsString( 'mark_all_seen', $output );
		$this->assertStringContainsString( 'search', $output );
		$this->assertStringContainsString( 'no_permission_comments', $output );
		$this->assertStringContainsString( 'Loading.', $output );
		$this->assertStringContainsString( 'Sending request', $output );
		$this->assertStringContainsString( 'You have already attached maximum numbers of allowed attachments', $output );
		$this->assertStringContainsString( 'commented', $output );
		$this->assertStringContainsString( 'Comment', $output );
		$this->assertStringContainsString( 'Cancel', $output );
		$this->assertStringContainsString( 'Update', $output );
		$this->assertStringContainsString( 'Write your comment...', $output );
		$this->assertStringContainsString( 'Notifications', $output );
		$this->assertStringContainsString( 'Mark all as seen', $output );
		$this->assertStringContainsString( 'Search', $output );
		$this->assertStringContainsString( 'Sorry, you don\'t have permission to read comments.', $output );
		$this->assertStringContainsString( esc_url( admin_url( 'admin-ajax.php' ) ), $output );
		$this->assertStringContainsString( wp_create_nonce( 'ap_ajax_nonce' ), $output );
		$this->assertStringContainsString( $id, $output );
	}
}
