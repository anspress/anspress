<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

// Since this file is required only on admin pages so,
// we include it directly for testing.
require_once ANSPRESS_DIR . 'admin/anspress-admin.php';

class TestAnsPressAdmin extends TestCase {

	/**
	 * @covers AnsPress_Admin::instance
	 */
	public function testInstance() {
		$class = new \ReflectionClass( 'AnsPress_Admin' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic() );
	}

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress_Admin' );
		$this->assertTrue( $class->hasProperty( 'plugin_screen_hook_suffix' ) && $class->getProperty( 'plugin_screen_hook_suffix' )->isProtected() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'init' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'includes' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'enqueue_admin_styles' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'enqueue_admin_scripts' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'menu_counts' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'add_plugin_admin_menu' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'fix_active_admin_menu' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'get_free_menu_position' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'tax_menu_correction' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'display_plugin_options_page' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'dashboard_page' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'display_select_question' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'add_action_links' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'init_actions' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'question_meta_box_class' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'save_user_roles_fields' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'change_post_menu_label' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'ans_parent_post' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'trashed_post' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'post_data_check' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'custom_post_location' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'ap_menu_metaboxes' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'render_menu' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'serach_qa_by_userid' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'filter_comments_query' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'join_by_author_name' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'get_pages' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'modify_answer_title' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'append_post_status_list' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'anspress_notice' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'check_pages_exists' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'update_db' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'anspress_create_base_page' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'register_options' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'options_general_pages' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'options_general_permalinks' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'options_general_layout' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'options_uac_reading' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'options_uac_posting' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'options_uac_other' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'options_postscomments' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'options_user_activity' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'page_select_field_opt' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'ap_addon_options' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'save_addon_options' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin', 'admin_footer' ) );
	}

	/**
	 * @covers AnsPress_Admin::admin_footer
	 */
	public function testAdminFooter() {
		ob_start();
		\AnsPress_Admin::admin_footer();
		$output = ob_get_clean();
		$this->assertStringContainsString( '#adminmenu .anspress-license-count', $output );
		$this->assertStringContainsString( 'background: #0073aa;', $output );
	}

	/**
	 * @covers AnsPress_Admin::register_options
	 */
	public function testRegisterOptions() {
		\AnsPress_Admin::register_options();

		// Test begins.
		$this->assertEquals( 10, has_filter( 'ap_form_options_general_pages', [ 'AnsPress_Admin', 'options_general_pages' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_form_options_general_permalinks', [ 'AnsPress_Admin', 'options_general_permalinks' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_form_options_general_layout', [ 'AnsPress_Admin', 'options_general_layout' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_form_options_postscomments', [ 'AnsPress_Admin', 'options_postscomments' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_form_options_uac_reading', [ 'AnsPress_Admin', 'options_uac_reading' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_form_options_uac_posting', [ 'AnsPress_Admin', 'options_uac_posting' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_form_options_uac_other', [ 'AnsPress_Admin', 'options_uac_other' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_form_options_user_activity', [ 'AnsPress_Admin', 'options_user_activity' ] ) );
	}

	/**
	 * @covers AnsPress_Admin::includes
	 */
	public function testIncludes() {
		// Test before the method is called.
		$this->assertFalse( function_exists( 'ap_flagged_posts_count' ) );
		$this->assertFalse( function_exists( 'ap_update_caps_for_role' ) );
		$this->assertFalse( function_exists( 'ap_load_admin_assets' ) );

		// Test after the method is called.
		\AnsPress_Admin::includes();
		$this->assertTrue( function_exists( 'ap_flagged_posts_count' ) );
		$this->assertTrue( function_exists( 'ap_update_caps_for_role' ) );
		$this->assertTrue( function_exists( 'ap_load_admin_assets' ) );
		$this->assertTrue( class_exists( 'AP_license' ) );
		$this->assertInstanceOf('AP_license', new \AP_license() );
	}

	/**
	 * @covers AnsPress_Admin::add_action_links
	 */
	public function testAddActionLinks() {
		$links = \AnsPress_Admin::init();

		$links = \AnsPress_Admin::add_action_links( [] );
		$this->assertArrayHasKey( 'settings', $links );
		$this->assertStringContainsString( admin_url( 'admin.php?page=anspress_options' ), $links['settings'] );
		$this->assertStringContainsString( 'Settings', $links['settings'] );
		$this->assertEquals( '<a href="' . admin_url( 'admin.php?page=anspress_options' ) . '">Settings</a>', $links['settings'] );
	}
}
