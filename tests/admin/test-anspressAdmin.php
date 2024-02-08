<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

// Since this file is required only on admin pages so,
// we include it directly for testing.
require_once ANSPRESS_DIR . 'admin/anspress-admin.php';

class TestAnsPressAdmin extends TestCase {

	use Testcases\Common;

	public function set_up() {
		parent::set_up();
		register_taxonomy( 'question_label', array( 'question' ) );
		register_taxonomy( 'rank', array( 'question' ) );
		register_taxonomy( 'badge', array( 'question' ) );
	}

	public function tear_down() {
		unregister_taxonomy( 'question_label' );
		unregister_taxonomy( 'rank' );
		unregister_taxonomy( 'badge' );
		parent::tear_down();
	}

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

	/**
	 * @covers AnsPress_Admin::options_general_pages
	 */
	public function testOptionsGeneralPages() {
		$form = \AnsPress_Admin::options_general_pages();

		// Test starts.
		$this->assertArrayHasKey( 'submit_label', $form );
		$this->assertEquals( 'Save Pages', $form['submit_label'] );
		$this->assertArrayHasKey( 'fields', $form );

		// Test for author_credits field.
		$this->assertArrayHasKey( 'author_credits', $form['fields'] );
		$this->assertEquals( 'Hide author credits', $form['fields']['author_credits']['label'] );
		$this->assertEquals( 'Hide link to AnsPress project site.', $form['fields']['author_credits']['desc'] );
		$this->assertEquals( 'checkbox', $form['fields']['author_credits']['type'] );
		$this->assertEquals( 0, $form['fields']['author_credits']['order'] );
		$this->assertEquals( ap_opt( 'author_credits' ), $form['fields']['author_credits']['value'] );

		// Test for sep-warning field.
		$this->assertArrayHasKey( 'sep-warning', $form['fields'] );
		$this->assertEquals( '<div class="ap-uninstall-warning">If you have created main pages manually then make sure to have [anspress] shortcode in all pages.</div>', $form['fields']['sep-warning']['html'] );

		// For pages field.
		foreach ( ap_main_pages() as $slug => $args ) {
			$this->assertArrayHasKey( $slug, $form['fields'] );
			$this->assertEquals( $args['label'], $form['fields'][ $slug ]['label'] );
			$this->assertEquals( $args['desc'], $form['fields'][ $slug ]['desc'] );
			$this->assertEquals( 'select', $form['fields'][ $slug ]['type'] );
			$this->assertEquals( 'posts', $form['fields'][ $slug ]['options'] );
			$this->assertArrayHasKey( 'post_type', $form['fields'][ $slug ]['posts_args'] );
			$this->assertArrayHasKey( 'showposts', $form['fields'][ $slug ]['posts_args'] );
			$this->assertEquals( [ 'post_type' => 'page', 'showposts' => -1 ], $form['fields'][ $slug ]['posts_args'] );
			$this->assertEquals( ap_opt( $slug ), $form['fields'][ $slug ]['value'] );
			$this->assertEquals( 'absint', $form['fields'][ $slug ]['sanitize'] );
		}
	}

	/**
	 * @covers AnsPress_Admin::options_general_permalinks
	 */
	public function testOptionsGeneralPermalinks() {
		$form = \AnsPress_Admin::options_general_permalinks();

		// Test starts.
		$this->assertArrayHasKey( 'submit_label', $form );
		$this->assertEquals( 'Save Permalinks', $form['submit_label'] );
		$this->assertArrayHasKey( 'fields', $form );

		// Test for question_page_slug field.
		$this->assertArrayHasKey( 'question_page_slug', $form['fields'] );
		$this->assertEquals( 'Question slug', $form['fields']['question_page_slug']['label'] );
		$this->assertEquals( 'Slug for single question page.', $form['fields']['question_page_slug']['desc'] );
		$this->assertEquals( ap_opt( 'question_page_slug' ), $form['fields']['question_page_slug']['value'] );
		$this->assertEquals( 'required', $form['fields']['question_page_slug']['validate'] );

		// Test for question_page_permalink field.
		$this->assertArrayHasKey( 'question_page_permalink', $form['fields'] );
		$this->assertEquals( 'Question permalink', $form['fields']['question_page_permalink']['label'] );
		$this->assertEquals( 'Select single question permalink structure.', $form['fields']['question_page_permalink']['desc'] );
		$this->assertEquals( 'radio', $form['fields']['question_page_permalink']['type'] );
		$options_args = [
			'question_perma_1' => home_url( '/' . ap_base_page_slug() ) . '/<b class="ap-base-slug">' . ap_opt( 'question_page_slug' ) . '</b>/question-name/',
			'question_perma_2' => home_url( '/' ) . '<b class="ap-base-slug">' . ap_opt( 'question_page_slug' ) . '</b>/question-name/',
			'question_perma_3' => home_url( '/' ) . '<b class="ap-base-slug">' . ap_opt( 'question_page_slug' ) . '</b>/213/',
			'question_perma_4' => home_url( '/' ) . '<b class="ap-base-slug">' . ap_opt( 'question_page_slug' ) . '</b>/213/question-name/',
			'question_perma_5' => home_url( '/' ) . '<b class="ap-base-slug">' . ap_opt( 'question_page_slug' ) . '</b>/question-name/213/',
			'question_perma_6' => home_url( '/' ) . '<b class="ap-base-slug">' . ap_opt( 'question_page_slug' ) . '</b>/213-question-name/',
			'question_perma_7' => home_url( '/' ) . '<b class="ap-base-slug">' . ap_opt( 'question_page_slug' ) . '</b>/question-name-213/',
		];
		$this->assertEquals( $options_args, $form['fields']['question_page_permalink']['options'] );
		foreach ( $options_args as $key => $value ) {
			$this->assertArrayHasKey( $key, $form['fields']['question_page_permalink']['options'] );
			$this->assertEquals( $value, $form['fields']['question_page_permalink']['options'][ $key ] );
		}
		$this->assertEquals( ap_opt( 'question_page_permalink' ), $form['fields']['question_page_permalink']['value'] );
		$this->assertEquals( 'required', $form['fields']['question_page_permalink']['validate'] );

		// Test for base_page_title field.
		$this->assertArrayHasKey( 'base_page_title', $form['fields'] );
		$this->assertEquals( 'Base page title', $form['fields']['base_page_title']['label'] );
		$this->assertEquals( 'Main questions list page title', $form['fields']['base_page_title']['desc'] );
		$this->assertEquals( ap_opt( 'base_page_title' ), $form['fields']['base_page_title']['value'] );
		$this->assertEquals( 'required', $form['fields']['base_page_title']['validate'] );

		// Test for search_page_title field.
		$this->assertArrayHasKey( 'search_page_title', $form['fields'] );
		$this->assertEquals( 'Search page title', $form['fields']['search_page_title']['label'] );
		$this->assertEquals( 'Title of the search page', $form['fields']['search_page_title']['desc'] );
		$this->assertEquals( ap_opt( 'search_page_title' ), $form['fields']['search_page_title']['value'] );
		$this->assertEquals( 'required', $form['fields']['search_page_title']['validate'] );

		// Test for author_page_title field.
		$this->assertArrayHasKey( 'author_page_title', $form['fields'] );
		$this->assertEquals( 'Author page title', $form['fields']['author_page_title']['label'] );
		$this->assertEquals( 'Title of the author page', $form['fields']['author_page_title']['desc'] );
		$this->assertEquals( ap_opt( 'author_page_title' ) ?? 'User', $form['fields']['author_page_title']['value'] );
		$this->assertEquals( 'required', $form['fields']['author_page_title']['validate'] );
	}

	/**
	 * @covers AnsPress_Admin::options_general_layout
	 */
	public function testoptions_general_layout() {
		$form = \AnsPress_Admin::options_general_layout();

		// Test starts.
		$this->assertArrayHasKey( 'fields', $form );

		// Test for load_assets_in_anspress_only field.
		$this->assertArrayHasKey( 'load_assets_in_anspress_only', $form['fields'] );
		$this->assertEquals( '', $form['fields']['load_assets_in_anspress_only']['name'] );
		$this->assertEquals( 'Load assets in AnsPress page only?', $form['fields']['load_assets_in_anspress_only']['label'] );
		$this->assertEquals( 'Check this to load AnsPress JS and CSS on the AnsPress page only. Be careful, this might break layout.', $form['fields']['load_assets_in_anspress_only']['desc'] );
		$this->assertEquals( 'checkbox', $form['fields']['load_assets_in_anspress_only']['type'] );
		$this->assertEquals( ap_opt( 'load_assets_in_anspress_only' ), $form['fields']['load_assets_in_anspress_only']['value'] );

		// Test for avatar_size_list field.
		$this->assertArrayHasKey( 'avatar_size_list', $form['fields'] );
		$this->assertEquals( 'List avatar size', $form['fields']['avatar_size_list']['label'] );
		$this->assertEquals( 'User avatar size for questions list.', $form['fields']['avatar_size_list']['desc'] );
		$this->assertEquals( 'number', $form['fields']['avatar_size_list']['subtype'] );
		$this->assertEquals( ap_opt( 'avatar_size_list' ), $form['fields']['avatar_size_list']['value'] );

		// Test for avatar_size_qquestion field.
		$this->assertArrayHasKey( 'avatar_size_qquestion', $form['fields'] );
		$this->assertEquals( 'Question avatar size', $form['fields']['avatar_size_qquestion']['label'] );
		$this->assertEquals( 'User avatar size for question.', $form['fields']['avatar_size_qquestion']['desc'] );
		$this->assertEquals( 'number', $form['fields']['avatar_size_qquestion']['subtype'] );
		$this->assertEquals( ap_opt( 'avatar_size_qquestion' ), $form['fields']['avatar_size_qquestion']['value'] );

		// Test for avatar_size_qanswer field.
		$this->assertArrayHasKey( 'avatar_size_qanswer', $form['fields'] );
		$this->assertEquals( 'Answer avatar size', $form['fields']['avatar_size_qanswer']['label'] );
		$this->assertEquals( 'User avatar size for answer.', $form['fields']['avatar_size_qanswer']['desc'] );
		$this->assertEquals( 'number', $form['fields']['avatar_size_qanswer']['subtype'] );
		$this->assertEquals( ap_opt( 'avatar_size_qanswer' ), $form['fields']['avatar_size_qanswer']['value'] );

		// Test for avatar_size_qcomment field.
		$this->assertArrayHasKey( 'avatar_size_qcomment', $form['fields'] );
		$this->assertEquals( 'Comment avatar size', $form['fields']['avatar_size_qcomment']['label'] );
		$this->assertEquals( 'User avatar size for comments.', $form['fields']['avatar_size_qcomment']['desc'] );
		$this->assertEquals( 'number', $form['fields']['avatar_size_qcomment']['subtype'] );
		$this->assertEquals( ap_opt( 'avatar_size_qcomment' ), $form['fields']['avatar_size_qcomment']['value'] );

		// Test for question_per_page field.
		$this->assertArrayHasKey( 'question_per_page', $form['fields'] );
		$this->assertEquals( 'Questions per page', $form['fields']['question_per_page']['label'] );
		$this->assertEquals( 'Questions to show per page.', $form['fields']['question_per_page']['desc'] );
		$this->assertEquals( 'number', $form['fields']['question_per_page']['subtype'] );
		$this->assertEquals( ap_opt( 'question_per_page' ), $form['fields']['question_per_page']['value'] );

		// Test for answers_per_page field.
		$this->assertArrayHasKey( 'answers_per_page', $form['fields'] );
		$this->assertEquals( 'Answers per page', $form['fields']['answers_per_page']['label'] );
		$this->assertEquals( 'Answers to show per page.', $form['fields']['answers_per_page']['desc'] );
		$this->assertEquals( 'number', $form['fields']['answers_per_page']['subtype'] );
		$this->assertEquals( ap_opt( 'answers_per_page' ), $form['fields']['answers_per_page']['value'] );
	}

	/**
	 * @covers AnsPress_Admin::options_uac_reading
	 */
	public function testOptionsUACReading() {
		$form = \AnsPress_Admin::options_uac_reading();

		// Test starts.
		$this->assertArrayHasKey( 'fields', $form );

		// Test for read_question_per field.
		$this->assertArrayHasKey( 'read_question_per', $form['fields'] );
		$this->assertEquals( 'Who can read question?', $form['fields']['read_question_per']['label'] );
		$this->assertEquals( 'Set who can view or read a question.', $form['fields']['read_question_per']['desc'] );
		$this->assertEquals( 'select', $form['fields']['read_question_per']['type'] );
		$this->assertEquals( ap_opt( 'read_question_per' ), $form['fields']['read_question_per']['value'] );
		$options_args = [
			'anyone'    => 'Anyone, including non-loggedin',
			'logged_in' => 'Only logged in',
			'have_cap'  => 'Only user having ap_read_question capability',
		];
		$this->assertEquals( $options_args, $form['fields']['read_question_per']['options'] );
		foreach ( $options_args as $key => $value ) {
			$this->assertArrayHasKey( $key, $form['fields']['read_question_per']['options'] );
			$this->assertEquals( $value, $form['fields']['read_question_per']['options'][ $key ] );
		}

		// Test for read_answer_per field.
		$this->assertArrayHasKey( 'read_answer_per', $form['fields'] );
		$this->assertEquals( 'Who can read answers?', $form['fields']['read_answer_per']['label'] );
		$this->assertEquals( 'Set who can view or read a answer.', $form['fields']['read_answer_per']['desc'] );
		$this->assertEquals( 'select', $form['fields']['read_answer_per']['type'] );
		$this->assertEquals( ap_opt( 'read_answer_per' ), $form['fields']['read_answer_per']['value'] );
		$options_args = [
			'anyone'    => 'Anyone, including non-loggedin',
			'logged_in' => 'Only logged in',
			'have_cap'  => 'Only user having ap_read_answer capability',
		];
		$this->assertEquals( $options_args, $form['fields']['read_answer_per']['options'] );
		foreach ( $options_args as $key => $value ) {
			$this->assertArrayHasKey( $key, $form['fields']['read_answer_per']['options'] );
			$this->assertEquals( $value, $form['fields']['read_answer_per']['options'][ $key ] );
		}

		// Test for read_comment_per field.
		$this->assertArrayHasKey( 'read_comment_per', $form['fields'] );
		$this->assertEquals( 'Who can read comment?', $form['fields']['read_comment_per']['label'] );
		$this->assertEquals( 'Set who can view or read a comment.', $form['fields']['read_comment_per']['desc'] );
		$this->assertEquals( 'select', $form['fields']['read_comment_per']['type'] );
		$this->assertEquals( ap_opt( 'read_comment_per' ), $form['fields']['read_comment_per']['value'] );
		$options_args = [
			'anyone'    => 'Anyone, including non-loggedin',
			'logged_in' => 'Only logged in',
			'have_cap'  => 'Only user having ap_read_comment capability',
		];
		$this->assertEquals( $options_args, $form['fields']['read_comment_per']['options'] );
		foreach ( $options_args as $key => $value ) {
			$this->assertArrayHasKey( $key, $form['fields']['read_comment_per']['options'] );
			$this->assertEquals( $value, $form['fields']['read_comment_per']['options'][ $key ] );
		}
	}

	/**
	 * @covers AnsPress_Admin::options_uac_posting
	 */
	public function testOptionsUACPosting() {
		$form = \AnsPress_Admin::options_uac_posting();

		// Test starts.
		$this->assertArrayHasKey( 'fields', $form );

		// Test for post_question_per field.
		$this->assertArrayHasKey( 'post_question_per', $form['fields'] );
		$this->assertEquals( 'Who can post question?', $form['fields']['post_question_per']['label'] );
		$this->assertEquals( 'Set who can submit a question from frontend.', $form['fields']['post_question_per']['desc'] );
		$this->assertEquals( 'select', $form['fields']['post_question_per']['type'] );
		$this->assertEquals( ap_opt( 'post_question_per' ), $form['fields']['post_question_per']['value'] );
		$options_args = [
			'anyone'    => 'Anyone, including non-loggedin',
			'logged_in' => 'Only logged in',
			'have_cap'  => 'Only user having ap_new_question capability',
		];
		$this->assertEquals( $options_args, $form['fields']['post_question_per']['options'] );
		foreach ( $options_args as $key => $value ) {
			$this->assertArrayHasKey( $key, $form['fields']['post_question_per']['options'] );
			$this->assertEquals( $value, $form['fields']['post_question_per']['options'][ $key ] );
		}

		// Test for post_answer_per field.
		$this->assertArrayHasKey( 'post_answer_per', $form['fields'] );
		$this->assertEquals( 'Who can post answer?', $form['fields']['post_answer_per']['label'] );
		$this->assertEquals( 'Set who can submit an answer from frontend.', $form['fields']['post_answer_per']['desc'] );
		$this->assertEquals( 'select', $form['fields']['post_answer_per']['type'] );
		$this->assertEquals( ap_opt( 'post_answer_per' ), $form['fields']['post_answer_per']['value'] );
		$options_args = [
			'anyone'    => 'Anyone, including non-loggedin',
			'logged_in' => 'Only logged in',
			'have_cap'  => 'Only user having ap_new_answer capability',
		];
		$this->assertEquals( $options_args, $form['fields']['post_answer_per']['options'] );
		foreach ( $options_args as $key => $value ) {
			$this->assertArrayHasKey( $key, $form['fields']['post_answer_per']['options'] );
			$this->assertEquals( $value, $form['fields']['post_answer_per']['options'][ $key ] );
		}

		// Test for create_account field.
		$this->assertArrayHasKey( 'create_account', $form['fields'] );
		$this->assertEquals( 'Create account for non-registered', $form['fields']['create_account']['label'] );
		$this->assertEquals( 'Allow non-registered users to create account by entering their email in question. After submitting post a confirmation email will be sent to the user.', $form['fields']['create_account']['desc'] );
		$this->assertEquals( 'checkbox', $form['fields']['create_account']['type'] );
		$this->assertEquals( ap_opt( 'create_account' ), $form['fields']['create_account']['value'] );

		// Test for multiple_answers field.
		$this->assertArrayHasKey( 'multiple_answers', $form['fields'] );
		$this->assertEquals( 'Multiple answers', $form['fields']['multiple_answers']['label'] );
		$this->assertEquals( 'Allow users to submit multiple answer per question.', $form['fields']['multiple_answers']['desc'] );
		$this->assertEquals( 'checkbox', $form['fields']['multiple_answers']['type'] );
		$this->assertEquals( ap_opt( 'multiple_answers' ), $form['fields']['multiple_answers']['value'] );

		// Test for disallow_op_to_answer field.
		$this->assertArrayHasKey( 'disallow_op_to_answer', $form['fields'] );
		$this->assertEquals( 'OP can answer?', $form['fields']['disallow_op_to_answer']['label'] );
		$this->assertEquals( 'OP: Original poster/asker. Enabling this option will prevent users to post an answer on their question.', $form['fields']['disallow_op_to_answer']['desc'] );
		$this->assertEquals( 'checkbox', $form['fields']['disallow_op_to_answer']['type'] );
		$this->assertEquals( ap_opt( 'disallow_op_to_answer' ), $form['fields']['disallow_op_to_answer']['value'] );

		// Test for post_comment_per field.
		$this->assertArrayHasKey( 'post_comment_per', $form['fields'] );
		$this->assertEquals( 'Who can post comment?', $form['fields']['post_comment_per']['label'] );
		$this->assertEquals( 'Set who can submit a comment from frontend.', $form['fields']['post_comment_per']['desc'] );
		$this->assertEquals( 'select', $form['fields']['post_comment_per']['type'] );
		$this->assertEquals( ap_opt( 'post_comment_per' ), $form['fields']['post_comment_per']['value'] );
		$options_args = [
			'anyone'    => 'Anyone, including non-loggedin',
			'logged_in' => 'Only logged in',
			'have_cap'  => 'Only user having ap_new_comment capability',
		];
		$this->assertEquals( $options_args, $form['fields']['post_comment_per']['options'] );
		foreach ( $options_args as $key => $value ) {
			$this->assertArrayHasKey( $key, $form['fields']['post_comment_per']['options'] );
			$this->assertEquals( $value, $form['fields']['post_comment_per']['options'][ $key ] );
		}

		// Test for new_question_status field.
		$this->assertArrayHasKey( 'new_question_status', $form['fields'] );
		$this->assertEquals( 'Status of new question', $form['fields']['new_question_status']['label'] );
		$this->assertEquals( 'Default status of new question.', $form['fields']['new_question_status']['desc'] );
		$this->assertEquals( 'select', $form['fields']['new_question_status']['type'] );
		$options_args = [
			'publish'  => 'Publish',
			'moderate' => 'Moderate',
		];
		$this->assertEquals( $options_args, $form['fields']['new_question_status']['options'] );
		foreach ( $options_args as $key => $value ) {
			$this->assertArrayHasKey( $key, $form['fields']['new_question_status']['options'] );
			$this->assertEquals( $value, $form['fields']['new_question_status']['options'][ $key ] );
		}
		$this->assertEquals( ap_opt( 'new_question_status' ), $form['fields']['new_question_status']['value'] );

		// Test for edit_question_status field.
		$this->assertArrayHasKey( 'edit_question_status', $form['fields'] );
		$this->assertEquals( 'Status of edited question', $form['fields']['edit_question_status']['label'] );
		$this->assertEquals( 'Default status of edited question.', $form['fields']['edit_question_status']['desc'] );
		$this->assertEquals( 'select', $form['fields']['edit_question_status']['type'] );
		$options_args = [
			'publish'  => 'Publish',
			'moderate' => 'Moderate',
		];
		$this->assertEquals( $options_args, $form['fields']['edit_question_status']['options'] );
		foreach ( $options_args as $key => $value ) {
			$this->assertArrayHasKey( $key, $form['fields']['edit_question_status']['options'] );
			$this->assertEquals( $value, $form['fields']['edit_question_status']['options'][ $key ] );
		}

		// Test for new_answer_status field.
		$this->assertArrayHasKey( 'new_answer_status', $form['fields'] );
		$this->assertEquals( 'Status of new answer', $form['fields']['new_answer_status']['label'] );
		$this->assertEquals( 'Default status of new answer.', $form['fields']['new_answer_status']['desc'] );
		$this->assertEquals( 'select', $form['fields']['new_answer_status']['type'] );
		$options_args = [
			'publish'  => 'Publish',
			'moderate' => 'Moderate',
		];
		$this->assertEquals( $options_args, $form['fields']['new_answer_status']['options'] );
		foreach ( $options_args as $key => $value ) {
			$this->assertArrayHasKey( $key, $form['fields']['new_answer_status']['options'] );
			$this->assertEquals( $value, $form['fields']['new_answer_status']['options'][ $key ] );
		}
		$this->assertEquals( ap_opt( 'new_answer_status' ), $form['fields']['new_answer_status']['value'] );

		// Test for edit_answer_status field.
		$this->assertArrayHasKey( 'edit_answer_status', $form['fields'] );
		$this->assertEquals( 'Status of edited answer', $form['fields']['edit_answer_status']['label'] );
		$this->assertEquals( 'Default status of edited answer.', $form['fields']['edit_answer_status']['desc'] );
		$this->assertEquals( 'select', $form['fields']['edit_answer_status']['type'] );
		$options_args = [
			'publish'  => 'Publish',
			'moderate' => 'Moderate',
		];
		$this->assertEquals( $options_args, $form['fields']['edit_answer_status']['options'] );
		foreach ( $options_args as $key => $value ) {
			$this->assertArrayHasKey( $key, $form['fields']['edit_answer_status']['options'] );
			$this->assertEquals( $value, $form['fields']['edit_answer_status']['options'][ $key ] );
		}
		$this->assertEquals( ap_opt( 'edit_answer_status' ), $form['fields']['edit_answer_status']['value'] );

		// Test for anonymous_post_status field.
		$this->assertArrayHasKey( 'anonymous_post_status', $form['fields'] );
		$this->assertEquals( 'Status of non-loggedin post', $form['fields']['anonymous_post_status']['label'] );
		$this->assertEquals( 'Default status of question or answer submitted by non-loggedin user.', $form['fields']['anonymous_post_status']['desc'] );
		$this->assertEquals( 'select', $form['fields']['anonymous_post_status']['type'] );
		$options_args = [
			'publish'  => 'Publish',
			'moderate' => 'Moderate',
		];
		$this->assertEquals( $options_args, $form['fields']['anonymous_post_status']['options'] );
		foreach ( $options_args as $key => $value ) {
			$this->assertArrayHasKey( $key, $form['fields']['anonymous_post_status']['options'] );
			$this->assertEquals( $value, $form['fields']['anonymous_post_status']['options'][ $key ] );
		}
		$this->assertEquals( ap_opt( 'anonymous_post_status' ), $form['fields']['anonymous_post_status']['value'] );
	}

	/**
	 * @covers AnsPress_Admin::options_uac_other
	 */
	public function testOptionsUACOther() {
		$form = \AnsPress_Admin::options_uac_other();

		// Test starts.
		$this->assertArrayHasKey( 'fields', $form );

		// Test for allow_upload field.
		$this->assertArrayHasKey( 'allow_upload', $form['fields'] );
		$this->assertEquals( 'Allow image upload', $form['fields']['allow_upload']['label'] );
		$this->assertEquals( 'Allow logged-in users to upload image.', $form['fields']['allow_upload']['desc'] );
		$this->assertEquals( 'checkbox', $form['fields']['allow_upload']['type'] );
		$this->assertEquals( ap_opt( 'allow_upload' ), $form['fields']['allow_upload']['value'] );

		// Test for uploads_per_post field.
		$this->assertArrayHasKey( 'uploads_per_post', $form['fields'] );
		$this->assertEquals( 'Max uploads per post', $form['fields']['uploads_per_post']['label'] );
		$this->assertEquals( 'Set numbers of media user can upload for each post.', $form['fields']['uploads_per_post']['desc'] );
		$this->assertEquals( ap_opt( 'uploads_per_post' ), $form['fields']['uploads_per_post']['value'] );

		// Test for max_upload_size field.
		$this->assertArrayHasKey( 'max_upload_size', $form['fields'] );
		$this->assertEquals( 'Max upload size', $form['fields']['max_upload_size']['label'] );
		$this->assertEquals( 'Set maximum upload size.', $form['fields']['max_upload_size']['desc'] );
		$this->assertEquals( ap_opt( 'max_upload_size' ), $form['fields']['max_upload_size']['value'] );

		// Test for allow_private_posts field.
		$this->assertArrayHasKey( 'allow_private_posts', $form['fields'] );
		$this->assertEquals( 'Allow private posts', $form['fields']['allow_private_posts']['label'] );
		$this->assertEquals( 'Allows users to create private question and answer. Private Q&A are only visible to admin and moderators.', $form['fields']['allow_private_posts']['desc'] );
		$this->assertEquals( 'checkbox', $form['fields']['allow_private_posts']['type'] );
		$this->assertEquals( ap_opt( 'allow_private_posts' ), $form['fields']['allow_private_posts']['value'] );
	}

	/**
	 * @covers AnsPress_Admin::options_postscomments
	 */
	public function testOptionsPostscomments() {
		$form = \AnsPress_Admin::options_postscomments();

		// Test starts.
		$this->assertArrayHasKey( 'fields', $form );

		// Test for comment_number field.
		$this->assertArrayHasKey( 'comment_number', $form['fields'] );
		$this->assertEquals( 'Numbers of comments to show', $form['fields']['comment_number']['label'] );
		$this->assertEquals( 'Numbers of comments to load in each query?', $form['fields']['comment_number']['desc'] );
		$this->assertEquals( ap_opt( 'comment_number' ), $form['fields']['comment_number']['value'] );
		$this->assertEquals( 'number', $form['fields']['comment_number']['subtype'] );

		// Test for duplicate_check field.
		$this->assertArrayHasKey( 'duplicate_check', $form['fields'] );
		$this->assertEquals( 'Check duplicate', $form['fields']['duplicate_check']['label'] );
		$this->assertEquals( 'Check for duplicate posts before posting', $form['fields']['duplicate_check']['desc'] );
		$this->assertEquals( 'checkbox', $form['fields']['duplicate_check']['type'] );
		$this->assertEquals( ap_opt( 'duplicate_check' ), $form['fields']['duplicate_check']['value'] );

		// Test for disable_q_suggestion field.
		$this->assertArrayHasKey( 'disable_q_suggestion', $form['fields'] );
		$this->assertEquals( 'Disable question suggestion', $form['fields']['disable_q_suggestion']['label'] );
		$this->assertEquals( 'Checking this will disable question suggestion in ask form', $form['fields']['disable_q_suggestion']['desc'] );
		$this->assertEquals( 'checkbox', $form['fields']['disable_q_suggestion']['type'] );
		$this->assertEquals( ap_opt( 'disable_q_suggestion' ), $form['fields']['disable_q_suggestion']['value'] );

		// Test for default_date_format field.
		$this->assertArrayHasKey( 'default_date_format', $form['fields'] );
		$this->assertEquals( 'Show default date format', $form['fields']['default_date_format']['label'] );
		$this->assertEquals( 'Instead of showing time passed i.e. 1 Hour ago, show default format date.', $form['fields']['default_date_format']['desc'] );
		$this->assertEquals( 'checkbox', $form['fields']['default_date_format']['type'] );
		$this->assertEquals( ap_opt( 'default_date_format' ), $form['fields']['default_date_format']['value'] );

		// Test for show_solved_prefix field.
		$this->assertArrayHasKey( 'show_solved_prefix', $form['fields'] );
		$this->assertEquals( 'Show solved prefix', $form['fields']['show_solved_prefix']['label'] );
		$this->assertEquals( 'If an answer is selected for question then [solved] prefix will be added in title.', $form['fields']['show_solved_prefix']['desc'] );
		$this->assertEquals( 'checkbox', $form['fields']['show_solved_prefix']['type'] );
		$this->assertEquals( ap_opt( 'show_solved_prefix' ), $form['fields']['show_solved_prefix']['value'] );
		$this->assertEquals( 'required', $form['fields']['show_solved_prefix']['validate'] );

		// Test for question_order_by field.
		$this->assertArrayHasKey( 'question_order_by', $form['fields'] );
		$this->assertEquals( 'Default question order', $form['fields']['question_order_by']['label'] );
		$this->assertEquals( 'Order question list by default using selected', $form['fields']['question_order_by']['desc'] );
		$this->assertEquals( 'select', $form['fields']['question_order_by']['type'] );
		$options_args = [
			'voted'  => 'Voted',
			'active' => 'Active',
			'newest' => 'Newest',
			'oldest' => 'Oldest',
		];
		$this->assertEquals( $options_args, $form['fields']['question_order_by']['options'] );
		foreach ( $options_args as $key => $value ) {
			$this->assertArrayHasKey( $key, $form['fields']['question_order_by']['options'] );
			$this->assertEquals( $value, $form['fields']['question_order_by']['options'][ $key ] );
		}
		$this->assertEquals( ap_opt( 'question_order_by' ), $form['fields']['question_order_by']['value'] );

		// Test for keep_stop_words field.
		$this->assertArrayHasKey( 'keep_stop_words', $form['fields'] );
		$this->assertEquals( 'Keep stop words in question slug', $form['fields']['keep_stop_words']['label'] );
		$this->assertEquals( 'AnsPress will not strip stop words in question slug.', $form['fields']['keep_stop_words']['desc'] );
		$this->assertEquals( 'checkbox', $form['fields']['keep_stop_words']['type'] );
		$this->assertEquals( ap_opt( 'keep_stop_words' ), $form['fields']['keep_stop_words']['value'] );

		// Test for minimum_qtitle_length field.
		$this->assertArrayHasKey( 'minimum_qtitle_length', $form['fields'] );
		$this->assertEquals( 'Minimum title length', $form['fields']['minimum_qtitle_length']['label'] );
		$this->assertEquals( 'Set minimum letters for a question title.', $form['fields']['minimum_qtitle_length']['desc'] );
		$this->assertEquals( 'number', $form['fields']['minimum_qtitle_length']['subtype'] );
		$this->assertEquals( ap_opt( 'minimum_qtitle_length' ), $form['fields']['minimum_qtitle_length']['value'] );

		// Test for minimum_question_length field.
		$this->assertArrayHasKey( 'minimum_question_length', $form['fields'] );
		$this->assertEquals( 'Minimum question content', $form['fields']['minimum_question_length']['label'] );
		$this->assertEquals( 'Set minimum letters for a question contents.', $form['fields']['minimum_question_length']['desc'] );
		$this->assertEquals( 'number', $form['fields']['minimum_question_length']['subtype'] );
		$this->assertEquals( ap_opt( 'minimum_question_length' ), $form['fields']['minimum_question_length']['value'] );

		// Test for question_text_editor field.
		$this->assertArrayHasKey( 'question_text_editor', $form['fields'] );
		$this->assertEquals( 'Question editor?', $form['fields']['question_text_editor']['label'] );
		$this->assertEquals( 'Quick tags editor', $form['fields']['question_text_editor']['desc'] );
		$this->assertEquals( 'checkbox', $form['fields']['question_text_editor']['type'] );
		$this->assertEquals( ap_opt( 'question_text_editor' ), $form['fields']['question_text_editor']['value'] );

		// Test for answer_text_editor field.
		$this->assertArrayHasKey( 'answer_text_editor', $form['fields'] );
		$this->assertEquals( 'Answer editor?', $form['fields']['answer_text_editor']['label'] );
		$this->assertEquals( 'Quick tags editor', $form['fields']['answer_text_editor']['desc'] );
		$this->assertEquals( 'checkbox', $form['fields']['answer_text_editor']['type'] );
		$this->assertEquals( ap_opt( 'answer_text_editor' ), $form['fields']['answer_text_editor']['value'] );

		// Test for disable_comments_on_question field.
		$this->assertArrayHasKey( 'disable_comments_on_question', $form['fields'] );
		$this->assertEquals( 'Disable comments', $form['fields']['disable_comments_on_question']['label'] );
		$this->assertEquals( 'Disable comments on questions.', $form['fields']['disable_comments_on_question']['desc'] );
		$this->assertEquals( 'checkbox', $form['fields']['disable_comments_on_question']['type'] );
		$this->assertEquals( ap_opt( 'disable_comments_on_question' ), $form['fields']['disable_comments_on_question']['value'] );

		// Test for disable_voting_on_question field.
		$this->assertArrayHasKey( 'disable_voting_on_question', $form['fields'] );
		$this->assertEquals( 'Disable voting', $form['fields']['disable_voting_on_question']['label'] );
		$this->assertEquals( 'Disable voting on questions.', $form['fields']['disable_voting_on_question']['desc'] );
		$this->assertEquals( 'checkbox', $form['fields']['disable_voting_on_question']['type'] );
		$this->assertEquals( ap_opt( 'disable_voting_on_question' ), $form['fields']['disable_voting_on_question']['value'] );

		// Test for disable_down_vote_on_question field.
		$this->assertArrayHasKey( 'disable_down_vote_on_question', $form['fields'] );
		$this->assertEquals( 'Disable down voting', $form['fields']['disable_down_vote_on_question']['label'] );
		$this->assertEquals( 'Disable down voting on questions.', $form['fields']['disable_down_vote_on_question']['desc'] );
		$this->assertEquals( 'checkbox', $form['fields']['disable_down_vote_on_question']['type'] );
		$this->assertEquals( ap_opt( 'disable_down_vote_on_question' ), $form['fields']['disable_down_vote_on_question']['value'] );

		// Test for close_selected field.
		$this->assertArrayHasKey( 'close_selected', $form['fields'] );
		$this->assertEquals( 'Close question after selecting answer', $form['fields']['close_selected']['label'] );
		$this->assertEquals( 'If enabled this will prevent user to submit answer on solved question.', $form['fields']['close_selected']['desc'] );
		$this->assertEquals( 'checkbox', $form['fields']['close_selected']['type'] );
		$this->assertEquals( ap_opt( 'close_selected' ), $form['fields']['close_selected']['value'] );

		// Test for answers_sort field.
		$this->assertArrayHasKey( 'answers_sort', $form['fields'] );
		$this->assertEquals( 'Default answers order', $form['fields']['answers_sort']['label'] );
		$this->assertEquals( 'Order answers by by default using selected', $form['fields']['answers_sort']['desc'] );
		$this->assertEquals( 'select', $form['fields']['answers_sort']['type'] );
		$options_args = [
			'voted'  => 'Voted',
			'active' => 'Active',
			'newest' => 'Newest',
			'oldest' => 'Oldest',
		];
		$this->assertEquals( $options_args, $form['fields']['answers_sort']['options'] );
		foreach ( $options_args as $key => $value ) {
			$this->assertArrayHasKey( $key, $form['fields']['answers_sort']['options'] );
			$this->assertEquals( $value, $form['fields']['answers_sort']['options'][ $key ] );
		}
		$this->assertEquals( ap_opt( 'answers_sort' ), $form['fields']['answers_sort']['value'] );

		// Test for minimum_ans_length field.
		$this->assertArrayHasKey( 'minimum_ans_length', $form['fields'] );
		$this->assertEquals( 'Minimum answer content', $form['fields']['minimum_ans_length']['label'] );
		$this->assertEquals( 'Set minimum letters for a answer contents.', $form['fields']['minimum_ans_length']['desc'] );
		$this->assertEquals( 'number', $form['fields']['minimum_ans_length']['subtype'] );
		$this->assertEquals( ap_opt( 'minimum_ans_length' ), $form['fields']['minimum_ans_length']['value'] );

		// Test for disable_comments_on_answer field.
		$this->assertArrayHasKey( 'disable_comments_on_answer', $form['fields'] );
		$this->assertEquals( 'Disable comments', $form['fields']['disable_comments_on_answer']['label'] );
		$this->assertEquals( 'Disable comments on answer.', $form['fields']['disable_comments_on_answer']['desc'] );
		$this->assertEquals( 'checkbox', $form['fields']['disable_comments_on_answer']['type'] );
		$this->assertEquals( ap_opt( 'disable_comments_on_answer' ), $form['fields']['disable_comments_on_answer']['value'] );

		// Test for disable_voting_on_answer field.
		$this->assertArrayHasKey( 'disable_voting_on_answer', $form['fields'] );
		$this->assertEquals( 'Disable voting', $form['fields']['disable_voting_on_answer']['label'] );
		$this->assertEquals( 'Disable voting on answers.', $form['fields']['disable_voting_on_answer']['desc'] );
		$this->assertEquals( 'checkbox', $form['fields']['disable_voting_on_answer']['type'] );
		$this->assertEquals( ap_opt( 'disable_voting_on_answer' ), $form['fields']['disable_voting_on_answer']['value'] );

		// Test for disable_down_vote_on_answer field.
		$this->assertArrayHasKey( 'disable_down_vote_on_answer', $form['fields'] );
		$this->assertEquals( 'Disable down voting', $form['fields']['disable_down_vote_on_answer']['label'] );
		$this->assertEquals( 'Disable down voting on answers.', $form['fields']['disable_down_vote_on_answer']['desc'] );
		$this->assertEquals( 'checkbox', $form['fields']['disable_down_vote_on_answer']['type'] );
		$this->assertEquals( ap_opt( 'disable_down_vote_on_answer' ), $form['fields']['disable_down_vote_on_answer']['value'] );
	}

	/**
	 * @covers AnsPress_Admin::options_user_activity
	 */
	public function testOptionsUserActivity() {
		$form = \AnsPress_Admin::options_user_activity();

		// Get roles datas.
		global $wp_roles;
		$roles = array();
		foreach ( $wp_roles->roles as $key => $role ) {
			$roles[ $key ] = $role['name'];
		}

		// Test starts.
		$this->assertArrayHasKey( 'fields', $form );

		// Test for activity_exclude_roles field.
		$this->assertArrayHasKey( 'activity_exclude_roles', $form['fields'] );
		$this->assertEquals( 'Select the roles to exclude in activity feed.', $form['fields']['activity_exclude_roles']['label'] );
		$this->assertEquals( 'Selected role\'s activities will be excluded in site activity feed.', $form['fields']['activity_exclude_roles']['desc'] );
		$this->assertEquals( 'checkbox', $form['fields']['activity_exclude_roles']['type'] );
		$this->assertEquals( ap_opt( 'activity_exclude_roles' ), $form['fields']['activity_exclude_roles']['value'] );
		$this->assertEquals( $roles, $form['fields']['activity_exclude_roles']['options'] );
		foreach ( $roles as $key => $value ) {
			$this->assertArrayHasKey( $key, $form['fields']['activity_exclude_roles']['options'] );
			$this->assertEquals( $value, $form['fields']['activity_exclude_roles']['options'][ $key ] );
		}
	}

	/**
	 * @covers AnsPress_Admin::append_post_status_list
	 */
	public function testAppendPostStatusList() {
		global $post;

		// Test for question post type.
		// Test 1.
		$question_id = $this->factory->post->create( [ 'post_type' => 'question', 'post_status' => 'moderate' ] );
		$post = get_post( $question_id );
		ob_start();
		\AnsPress_Admin::append_post_status_list();
		$output = ob_get_clean();
		$this->assertStringContainsString( 'Moderate', $output );
		$this->assertStringContainsString( 'Private Post', $output );
		$this->assertStringContainsString( 'jQuery("select#post_status")', $output );
		$this->assertStringContainsString( 'jQuery(".misc-pub-section label")', $output );
		$this->assertStringContainsString( '<option value=\'moderate\'  selected=\'selected\'>', $output );
		$this->assertStringContainsString( '<span id=\'post-status-display\'>Moderate</span>', $output );

		// Test 2.
		$question_id = $this->factory->post->create( [ 'post_type' => 'question', 'post_status' => 'private_post' ] );
		$post = get_post( $question_id );
		ob_start();
		\AnsPress_Admin::append_post_status_list();
		$output = ob_get_clean();
		$this->assertStringContainsString( 'Moderate', $output );
		$this->assertStringContainsString( 'Private Post', $output );
		$this->assertStringContainsString( 'jQuery("select#post_status")', $output );
		$this->assertStringContainsString( 'jQuery(".misc-pub-section label")', $output );
		$this->assertStringContainsString( '<option value=\'private_post\'  selected=\'selected\'>', $output );
		$this->assertStringContainsString( '<span id=\'post-status-display\'>Private Post</span>', $output );

		// Test for answer post type.
		// Test 1.
		$answer_id = $this->factory->post->create( [ 'post_type' => 'answer', 'post_status' => 'moderate' ] );
		$post = get_post( $answer_id );
		ob_start();
		\AnsPress_Admin::append_post_status_list();
		$output = ob_get_clean();
		$this->assertStringContainsString( 'Moderate', $output );
		$this->assertStringContainsString( 'Private Post', $output );
		$this->assertStringContainsString( 'jQuery("select#post_status")', $output );
		$this->assertStringContainsString( 'jQuery(".misc-pub-section label")', $output );
		$this->assertStringContainsString( '<option value=\'moderate\'  selected=\'selected\'>', $output );
		$this->assertStringContainsString( '<span id=\'post-status-display\'>Moderate</span>', $output );

		// Test 2.
		$answer_id = $this->factory->post->create( [ 'post_type' => 'answer', 'post_status' => 'private_post' ] );
		$post = get_post( $answer_id );
		ob_start();
		\AnsPress_Admin::append_post_status_list();
		$output = ob_get_clean();
		$this->assertStringContainsString( 'Moderate', $output );
		$this->assertStringContainsString( 'Private Post', $output );
		$this->assertStringContainsString( 'jQuery("select#post_status")', $output );
		$this->assertStringContainsString( 'jQuery(".misc-pub-section label")', $output );
		$this->assertStringContainsString( '<option value=\'private_post\'  selected=\'selected\'>', $output );
		$this->assertStringContainsString( '<span id=\'post-status-display\'>Private Post</span>', $output );
	}

	/**
	 * @covers AnsPress_Admin::get_pages
	 */
	public function testGetPages() {
		// Test if base page is set.
		// Create some pages and a base page.
		$pages = [
			ap_get_post( $this->factory->post->create( [ 'post_type' => 'page' ] ) ),
			ap_get_post( $this->factory->post->create( [ 'post_type' => 'page' ] ) ),
			ap_get_post( $this->factory->post->create( [ 'post_type' => 'page' ] ) ),
			ap_get_post( $this->factory->post->create( [ 'post_type' => 'page' ] ) ),
			ap_get_post( $this->factory->post->create( [ 'post_type' => 'page' ] ) ),
		];
		$base_page = ap_get_post( $this->factory->post->create( [ 'post_type' => 'page' ] ) );
		$pages[] = $base_page;

		// Set the AnsPress base page.
		ap_opt( 'base_page', $base_page->ID );

		// Call the method.
		$filtered_pages = \AnsPress_Admin::get_pages( $pages, [ 'name' => 'page_on_front' ] );

		// Test begins.
		foreach ( $filtered_pages as $page ) {
			$page_id = (array) $page->ID;
			$this->assertFalse( in_array( $base_page->ID, $page_id ) );
			if ( $page->ID !== $base_page->ID ) {
				$this->assertTrue( in_array( $page->ID, $page_id ) );
			}
		}

		// Test if base page is not set.
		// Create some pages.
		$pages = [
			ap_get_post( $this->factory->post->create( [ 'post_type' => 'page' ] ) ),
			ap_get_post( $this->factory->post->create( [ 'post_type' => 'page' ] ) ),
			ap_get_post( $this->factory->post->create( [ 'post_type' => 'page' ] ) ),
			ap_get_post( $this->factory->post->create( [ 'post_type' => 'page' ] ) ),
			ap_get_post( $this->factory->post->create( [ 'post_type' => 'page' ] ) ),
		];

		// Call the method.
		$filtered_pages = \AnsPress_Admin::get_pages( $pages, [ 'name' => 'page_on_front' ] );

		// Test begins.
		foreach ( $filtered_pages as $page ) {
			$page_id = (array) $page->ID;
			$this->assertTrue( in_array( $page->ID, $page_id ) );
		}
		foreach ( $pages as $key => $page ) {
			$page_id = (array) $filtered_pages[ $key ]->ID;
			$this->assertTrue( in_array( $page->ID, $page_id ) );
		}
	}

	/**
	 * @covers AnsPress_Admin::display_select_question
	 */
	public function testDisplaySelectQuestion() {
		ob_start();
		\AnsPress_Admin::display_select_question();
		$output = ob_get_clean();
		$this->assertStringContainsString( '<div id="ap-admin-dashboard" class="wrap">', $output );
		$this->assertStringContainsString( 'Select a question for new answer', $output );
		$this->assertStringContainsString( 'Slowly type for question suggestion and then click select button right to question title.', $output );
		$this->assertStringContainsString( '<form class="question-selection">', $output );
		$this->assertStringContainsString( '<input type="text" name="question_id" class="ap-select-question" id="select-question-for-answer" />', $output );
		$this->assertStringContainsString( '<input type="hidden" name="is_admin" value="true" />', $output );
		$this->assertStringContainsString( '<div id="similar_suggestions">', $output );
	}

	/**
	 * @covers AnsPress_Admin::trashed_post
	 */
	public function testTrashedPost() {
		// Test 1.
		$base_page = $this->factory->post->create( [ 'post_type' => 'page' ] );
		ap_opt( 'base_page', $base_page );
		set_transient( 'ap_pages_check', '1', HOUR_IN_SECONDS );
		\AnsPress_Admin::trashed_post( $base_page );
		$this->assertFalse( get_transient( 'ap_pages_check' ) );

		// Test 2.
		$ask_page = $this->factory->post->create( [ 'post_type' => 'page' ] );
		ap_opt( 'ask_page', $ask_page );
		set_transient( 'ap_pages_check', '1', HOUR_IN_SECONDS );
		\AnsPress_Admin::trashed_post( $ask_page );
		$this->assertFalse( get_transient( 'ap_pages_check' ) );

		// Test 3.
		$other_page = $this->factory->post->create( [ 'post_type' => 'page' ] );
		set_transient( 'ap_pages_check', '1', HOUR_IN_SECONDS );
		\AnsPress_Admin::trashed_post( $other_page );
		$this->assertNotEmpty( get_transient( 'ap_pages_check' ) );

		// Test 4.
		$other_post = $this->factory->post->create( [ 'post_type' => 'post' ] );
		set_transient( 'ap_pages_check', '1', HOUR_IN_SECONDS );
		\AnsPress_Admin::trashed_post( $other_post );
		$this->assertNotEmpty( get_transient( 'ap_pages_check' ) );
	}

	/**
	 * @covers AnsPress_Admin::menu_counts
	 */
	public function testMenuCounts() {
		$this->setRole( 'subscriber' );

		// Create some questions and answers.
		$q_id1 = $this->factory->post->create( [ 'post_type' => 'question', 'post_status' => 'publish' ] );
		$q_id2 = $this->factory->post->create( [ 'post_type' => 'question', 'post_status' => 'publish' ] );
		$q_id3 = $this->factory->post->create( [ 'post_type' => 'question', 'post_status' => 'publish' ] );
		$a_id1 = $this->factory->post->create( [ 'post_type' => 'answer', 'post_status' => 'publish', 'post_parent' => $q_id1 ] );
		$a_id2 = $this->factory->post->create( [ 'post_type' => 'answer', 'post_status' => 'publish', 'post_parent' => $q_id2 ] );
		$a_id3 = $this->factory->post->create( [ 'post_type' => 'answer', 'post_status' => 'publish', 'post_parent' => $q_id3 ] );

		// Add some flags to question and answer.
		ap_add_flag( $q_id1 );
		ap_update_flags_count( $q_id1 );
		ap_add_flag( $q_id2 );
		ap_update_flags_count( $q_id2 );
		ap_add_flag( $a_id3 );
		ap_update_flags_count( $a_id3 );

		// Test begins.
		$counts = \AnsPress_Admin::menu_counts();
		$this->assertIsArray( $counts );
		$this->assertArrayHasKey( 'question', $counts );
		$this->assertArrayHasKey( 'answer', $counts );
		$this->assertArrayHasKey( 'flagged', $counts );
		$this->assertArrayHasKey( 'total', $counts );
		$this->assertEquals( ' <span class="update-plugins count ap-menu-counts"><span class="plugin-count">2</span></span>', $counts['question'] );
		$this->assertEquals( ' <span class="update-plugins count ap-menu-counts"><span class="plugin-count">1</span></span>', $counts['answer'] );
		$this->assertEquals( ' <span class="update-plugins count ap-menu-counts"><span class="plugin-count">3</span></span>', $counts['flagged'] );
		$this->assertEquals( ' <span class="update-plugins count ap-menu-counts"><span class="plugin-count">6</span></span>', $counts['total'] );

		// Test after removing the flag.
		// Test 1.
		ap_delete_flags( $q_id1 );
		ap_delete_flags( $q_id2 );
		$counts = \AnsPress_Admin::menu_counts();
		$this->assertEquals( '', $counts['question'] );
		$this->assertEquals( ' <span class="update-plugins count ap-menu-counts"><span class="plugin-count">1</span></span>', $counts['answer'] );
		$this->assertEquals( ' <span class="update-plugins count ap-menu-counts"><span class="plugin-count">1</span></span>', $counts['flagged'] );
		$this->assertEquals( ' <span class="update-plugins count ap-menu-counts"><span class="plugin-count">2</span></span>', $counts['total'] );

		// Test 2.
		ap_delete_flags( $a_id3 );
		$counts = \AnsPress_Admin::menu_counts();
		$this->assertEquals( '', $counts['question'] );
		$this->assertEquals( '', $counts['answer'] );
		$this->assertEquals( '', $counts['flagged'] );
		$this->assertEquals( '', $counts['total'] );
	}

	/**
	 * @covers AnsPress_Admin::tax_menu_correction
	 */
	public function testTaxMenuCorrection() {
		// Test 1.
		set_current_screen( 'edit-question_category' );
		$result = \AnsPress_Admin::tax_menu_correction( '' );
		$this->assertEquals( 'anspress', $result );

		// Test 2.
		set_current_screen( 'edit-question_tag' );
		$result = \AnsPress_Admin::tax_menu_correction( '' );
		$this->assertEquals( 'anspress', $result );

		// Test 3.
		set_current_screen( 'edit-question_label' );
		$result = \AnsPress_Admin::tax_menu_correction( '' );
		$this->assertEquals( 'anspress', $result );

		// Test 4.
		set_current_screen( 'edit-rank' );
		$result = \AnsPress_Admin::tax_menu_correction( '' );
		$this->assertEquals( 'anspress', $result );

		// Test 5.
		set_current_screen( 'edit-badge' );
		$result = \AnsPress_Admin::tax_menu_correction( '' );
		$this->assertEquals( 'anspress', $result );

		// Test 6.
		set_current_screen( 'edit-test-tag' );
		$result = \AnsPress_Admin::tax_menu_correction( 'test-screen' );
		$this->assertEquals( 'test-screen', $result );
	}

	/**
	 * @covers AnsPress_Admin::fix_active_admin_menu
	 */
	public function testFixActiveAdminMenu() {
		global $submenu_file, $current_screen;
		$submenu_file = '';

		// Test 1.
		set_current_screen( 'question' );
		$result = \AnsPress_Admin::fix_active_admin_menu( '' );
		$this->assertEquals( 'anspress', $result );

		// Test 2.
		$current_screen = get_current_screen();
		$current_screen->action = 'add';
		$result = \AnsPress_Admin::fix_active_admin_menu( '' );
		$this->assertEquals( 'anspress', $result );

		// Test 3.
		set_current_screen( 'question' );
		$result = \AnsPress_Admin::fix_active_admin_menu( '' );
		$this->assertEquals( 'anspress', $result );

		// Test 4.
		$current_screen = get_current_screen();
		$current_screen->action = 'add';
		$result = \AnsPress_Admin::fix_active_admin_menu( '' );
		$this->assertEquals( 'anspress', $result );

		// Test 5.
		set_current_screen( 'post' );
		$current_screen = get_current_screen();
		$current_screen->action = 'edit';
		$result = \AnsPress_Admin::fix_active_admin_menu( 'test-screen' );
		$this->assertEquals( 'test-screen', $result );
	}

	/**
	 * @covers AnsPress_Admin::get_free_menu_position
	 */
	public function testGetFreeMenuPosition() {
		global $menu;
		$menu = [
			5  => [ 'AnsPress', 'manage_options', 'anspress', 'AnsPress', 'menu-top toplevel_page_anspress', 'toplevel_page_anspress', 'dashicons-anspress' ],
			10 => [ 'Questions', 'manage_options', 'questions', 'Questions', 'menu-top toplevel_page_questions', 'toplevel_page_questions', 'dashicons-questions' ],
			15 => [ 'Answers', 'manage_options', 'answers', 'Answers', 'menu-top toplevel_page_answers', 'toplevel_page_answers', 'dashicons-answers' ],
		];

		// Test begins.
		// Test 1.
		$result = \AnsPress_Admin::get_free_menu_position( 10 );
		$this->assertEquals( 10.99, $result );

		// Test 2.
		$result = \AnsPress_Admin::get_free_menu_position( 5, 0.5 );
		$this->assertEquals( 5.5, $result );

		// Test 3.
		$result = \AnsPress_Admin::get_free_menu_position( 15, 0.15 );
		$this->assertEquals( 15.15, $result );

		// Test 4.
		$result = \AnsPress_Admin::get_free_menu_position( 0 );
		$this->assertEquals( 0, $result );

		// Test 5.
		$result = \AnsPress_Admin::get_free_menu_position( 20, 20 );
		$this->assertEquals( 20, $result );
	}

	/**
	 * @covers AnsPress_Admin::ap_menu_metaboxes
	 */
	public function testAPMenuMetaboxes() {
		$GLOBALS['wp_meta_boxes']['nav-menus']['side']['high'] = [];

		// Test before calling the method.
		$this->assertArrayNotHasKey( 'anspress-menu-mb', $GLOBALS['wp_meta_boxes']['nav-menus']['side']['high'] );

		// Test after calling the method.
		\AnsPress_Admin::ap_menu_metaboxes();
		$this->assertArrayHasKey( 'anspress-menu-mb', $GLOBALS['wp_meta_boxes']['nav-menus']['side']['high'] );
		$this->assertEquals( 'anspress-menu-mb', $GLOBALS['wp_meta_boxes']['nav-menus']['side']['high']['anspress-menu-mb']['id'] );
		$this->assertEquals( 'AnsPress', $GLOBALS['wp_meta_boxes']['nav-menus']['side']['high']['anspress-menu-mb']['title'] );
		$this->assertEquals( [ 'AnsPress_Admin', 'render_menu' ], $GLOBALS['wp_meta_boxes']['nav-menus']['side']['high']['anspress-menu-mb']['callback'] );
	}
}
