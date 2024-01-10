<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAddonTags extends TestCase {

	public function set_up() {
		parent::set_up();
		ap_activate_addon( 'tags.php' );
	}

	public function tear_down() {
		parent::tear_down();
		ap_deactivate_addon( 'tags.php' );
	}

	/**
	 * @covers Anspress\Addons\Tags::instance
	 */
	public function testInstance() {
		$class = new \ReflectionClass( 'Anspress\Addons\Tags' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'Anspress\Addons\Tags', '__construct' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Tags', 'tag_page' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Tags', 'tags_page' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Tags', 'widget_positions' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Tags', 'register_question_tag' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Tags', 'admin_tags_menu' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Tags', 'add_to_settings_page' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Tags', 'option_fields' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Tags', 'ap_display_question_metas' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Tags', 'ap_question_info' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Tags', 'ap_assets_js' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Tags', 'ap_localize_scripts' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Tags', 'term_link_filter' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Tags', 'ap_question_form_fields' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Tags', 'after_new_question' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Tags', 'page_title' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Tags', 'ap_breadcrumbs' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Tags', 'ap_tags_suggestion' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Tags', 'rewrite_rules' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Tags', 'ap_main_questions_args' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Tags', 'ap_list_filters' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Tags', 'load_filter_tag' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Tags', 'load_filter_tags_order' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Tags', 'filter_active_tag' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Tags', 'filter_active_tags_order' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Tags', 'ap_current_page' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Tags', 'modify_query_archive' ) );
	}

	/**
	 * @covers Anspress\Addons\Tags::instance
	 */
	public function testInit() {
		$instance1 = \Anspress\Addons\Tags::init();
		$this->assertInstanceOf( 'Anspress\Addons\Tags', $instance1 );
		$instance2 = \Anspress\Addons\Tags::init();
		$this->assertSame( $instance1, $instance2 );
	}
}
