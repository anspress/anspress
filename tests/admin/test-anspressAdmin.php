<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

// Since this file is required only on admin pages so,
// we include it directly for testing.
require_once ANSPRESS_DIR . 'admin/anspress-admin.php';

class TestAnsPressAdmin extends TestCase {

	use Testcases\Common;

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

	/**
	 * @covers AnsPress_Admin::question_meta_box_class
	 */
	public function testQuestionMetaBoxClass() {
		// Test before the method is called.
		$this->assertFalse( class_exists( 'AP_Question_Meta_Box' ) );

		// Test after the method is called.
		\AnsPress_Admin::question_meta_box_class();
		$this->assertTrue( class_exists( 'AP_Question_Meta_Box' ) );
		$this->assertInstanceOf( 'AP_Question_Meta_Box', new \AP_Question_Meta_Box() );
	}

	/**
	 * @covers AnsPress_Admin::anspress_notice
	 */
	public function testAnsPressNotice() {
		// Test for displaying notice.
		// For db version.
		update_option( 'anspress_db_version', 0 );
		ob_start();
		\AnsPress_Admin::anspress_notice();
		$output = ob_get_clean();
		$this->assertStringContainsString( '<div class="ap-notice notice notice-error apicon-anspress-icon">', $output );
		$this->assertStringContainsString( 'AnsPress database is not updated.', $output );
		$this->assertStringContainsString( '<a class="button" href="' . admin_url( 'admin-post.php?action=anspress_update_db' ) . '">Update now</a>', $output );

		// For missing pages.
		$this->assertStringContainsString( '<div class="ap-notice notice notice-error apicon-anspress-icon">', $output );
		$this->assertStringContainsString( 'One or more AnsPress page(s) does not exists.', $output );
		$this->assertStringContainsString( '<a href="' . admin_url( 'admin-post.php?action=anspress_create_base_page' ) . '">Set automatically</a> Or <a href="' . admin_url( 'admin.php?page=anspress_options' ) . '">Set set by yourself</a>', $output );

		// Test for not displaying notice.
		// Generate required pages.
		$this->setRole( 'administrator' );
		ap_create_base_page();
		flush_rewrite_rules();
		delete_transient( 'ap_pages_check' );

		// For db version.
		update_option( 'anspress_db_version', AP_DB_VERSION );
		ob_start();
		\AnsPress_Admin::anspress_notice();
		$output = ob_get_clean();
		$this->assertStringNotContainsString( '<div class="ap-notice notice notice-error apicon-anspress-icon">', $output );
		$this->assertStringNotContainsString( 'AnsPress database is not updated.', $output );
		$this->assertStringNotContainsString( '<a class="button" href="' . admin_url( 'admin-post.php?action=anspress_update_db' ) . '">Update now</a>', $output );

		// For missing pages.
		$this->assertStringNotContainsString( '<div class="ap-notice notice notice-error apicon-anspress-icon">', $output );
		$this->assertStringNotContainsString( 'One or more AnsPress page(s) does not exists.', $output );
		$this->assertStringNotContainsString( '<a href="' . admin_url( 'admin-post.php?action=anspress_create_base_page' ) . '">Set automatically</a> Or <a href="' . admin_url( 'admin.php?page=anspress_options' ) . '">Set set by yourself</a>', $output );
		$this->logout();
	}

	/**
	 * @covers AnsPress_Admin::modify_answer_title
	 */
	public function testModifyAnswerTitle() {
		$question_id = $this->factory->post->create(
			[
				'post_type'    => 'question',
				'post_title'   => 'Question title',
				'post_content' => 'Question content'
			]
		);
		$answer_id = $this->factory->post->create(
			[
				'post_type'    => 'answer',
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_parent'  => $question_id
			]
		);
		$post_id = $this->factory->post->create(
			[
				'post_type'    => 'post',
				'post_title'   => 'Post title',
				'post_content' => 'Post content'
			]
		);
		$page_id = $this->factory->post->create(
			[
				'post_type'    => 'page',
				'post_title'   => 'Page title',
				'post_content' => 'Page content'
			]
		);

		// Test begins.
		// Test for post post type.
		$result = \AnsPress_Admin::modify_answer_title( (array) get_post( $post_id ) );
		$this->assertEquals( 'Post title', $result['post_title'] );

		// Test for page post type.
		$result = \AnsPress_Admin::modify_answer_title( (array) get_post( $page_id ) );
		$this->assertEquals( 'Page title', $result['post_title'] );

		// Test for question post type.
		$result = \AnsPress_Admin::modify_answer_title( (array) get_post( $question_id ) );
		$this->assertEquals( 'Question title', $result['post_title'] );

		// Test for answer post type.
		$result = \AnsPress_Admin::modify_answer_title( (array) get_post( $answer_id ) );
		$this->assertEquals( 'Question title', $result['post_title'] );
	}

	/**
	 * @covers AnsPress_Admin::save_user_roles_fields
	 */
	public function testSaveUserRolesFields() {
		// Test 1.
		$user_id = $this->factory->user->create();
		$_REQUEST['ap_role'] = 'administrator';
		\AnsPress_Admin::save_user_roles_fields( $user_id );
		$saved_role = get_user_meta( $user_id, 'ap_role', true );
		$this->assertEquals( ap_sanitize_unslash( 'ap_role', 'p' ), $saved_role );

		// Test 2.
		$user_id = $this->factory->user->create();
		$_REQUEST['ap_role'] = [ 'moderator', 'editor' ];
		\AnsPress_Admin::save_user_roles_fields( $user_id );
		$saved_role = get_user_meta( $user_id, 'ap_role', true );
		$this->assertEquals( ap_sanitize_unslash( 'ap_role', 'p' ), $saved_role );

		// Reset $_REQUEST.
		unset( $_REQUEST['ap_role'] );
	}

	/**
	 * @covers AnsPress_Admin::custom_post_location
	 */
	public function testCustomPostLocation() {
		$initial_location = 'http://example.com/sample-post/';
		$updated_location = \AnsPress_Admin::custom_post_location( $initial_location );

		// Test begins.
		$this->assertStringNotContainsString( 'message=99', $initial_location );
		$this->assertStringContainsString( 'message=99', $updated_location );
		$this->assertEquals( $initial_location . '?message=99', $updated_location );
	}

	/**
	 * @covers AnsPress_Admin::change_post_menu_label
	 */
	public function testChangePostMenuLabel() {
		// Test before the method is called.
		global $submenu;
		$submenu['anspress'][0][0] = 'Old Menu';
		$this->assertEquals( 'Old Menu', $submenu['anspress'][0][0] );

		// Test after the method is called.
		\AnsPress_Admin::change_post_menu_label();
		$this->assertEquals( 'AnsPress', $submenu['anspress'][0][0] );
	}
}
