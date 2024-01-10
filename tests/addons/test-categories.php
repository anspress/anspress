<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestCategories extends TestCase {

	/**
	 * @covers Anspress\Addons\Categories::instance
	 */
	public function testInstance() {
		$class = new \ReflectionClass( 'Anspress\Addons\Categories' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', '__construct' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'category_page' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'categories_page' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'register_question_categories' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'load_options' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'register_general_settings_form' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'admin_enqueue_scripts' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'ap_load_admin_assets' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'admin_category_menu' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'ap_display_question_metas' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'ap_assets_js' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'term_link_filter' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'ap_question_form_fields' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'after_new_question' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'ap_breadcrumbs' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'terms_clauses' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'ap_list_filters' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'image_field_new' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'image_field_edit' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'save_image_field' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'rewrite_rules' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'ap_main_questions_args' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'subscribers_action_id' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'ap_ask_btn_link' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'ap_canonical_url' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'category_feed' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'load_filter_category' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'filter_active_category' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'column_header' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'column_content' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'ap_current_page' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'modify_query_category_archive' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Categories', 'widget' ) );
	}
}
