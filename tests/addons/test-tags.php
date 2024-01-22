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

	/**
	 * @covers Anspress\Addons\Tags::add_to_settings_page
	 */
	public function testAddToSettingsPage() {
		$instance = \Anspress\Addons\Tags::init();

		// Call the method.
		$groups = $instance->add_to_settings_page( [] );

		// Test if the Tag group is added to the settings page.
		$this->assertArrayHasKey( 'tag', $groups );
		$this->assertEquals( 'Tag', $groups['tag']['label'] );

		// Test by adding new group.
		$groups = $instance->add_to_settings_page( [ 'some_other_group' => [ 'label' => 'Some Other Group' ] ] );

		// Test if the new group is added to the settings page.
		$this->assertArrayHasKey( 'some_other_group', $groups );
		$this->assertEquals( 'Some Other Group', $groups['some_other_group']['label'] );

		// Test if the existing group are retained to the settings page.
		$this->assertArrayHasKey( 'tag', $groups );
		$this->assertEquals( 'Tag', $groups['tag']['label'] );
	}

	/**
	 * @covers Anspress\Addons\Tags::option_fields
	 */
	public function testOptionFields() {
		$instance = \Anspress\Addons\Tags::init();

		// Add max_tags, min_tags, tags_per_page and tag_page_slug options.
		ap_add_default_options(
			array(
				'max_tags'      => 5,
				'min_tags'      => 1,
				'tags_per_page' => 20,
				'tag_page_slug' => 'tag',
			)
		);

		// Call the method.
		$forms = $instance->option_fields();

		// Test begins.
		$this->assertNotEmpty( $forms );
		$this->assertArrayHasKey( 'max_tags', $forms['fields'] );
		$this->assertArrayHasKey( 'min_tags', $forms['fields'] );
		$this->assertArrayHasKey( 'tags_per_page', $forms['fields'] );
		$this->assertArrayHasKey( 'tag_page_slug', $forms['fields'] );

		// Test for tags_per_page field.
		$this->assertArrayHasKey( 'label', $forms['fields']['tags_per_page'] );
		$this->assertEquals( 'Tags to show', $forms['fields']['tags_per_page']['label'] );
		$this->assertArrayHasKey( 'description', $forms['fields']['tags_per_page'] );
		$this->assertEquals( 'Numbers of tags to show in tags page.', $forms['fields']['tags_per_page']['description'] );
		$this->assertArrayHasKey( 'subtype', $forms['fields']['tags_per_page'] );
		$this->assertEquals( 'number', $forms['fields']['tags_per_page']['subtype'] );
		$this->assertArrayHasKey( 'value', $forms['fields']['tags_per_page'] );
		$this->assertEquals( ap_opt( 'tags_per_page' ), $forms['fields']['tags_per_page']['value'] );

		// Test for max_tags field.
		$this->assertArrayHasKey( 'label', $forms['fields']['max_tags'] );
		$this->assertEquals( 'Maximum tags', $forms['fields']['max_tags']['label'] );
		$this->assertArrayHasKey( 'description', $forms['fields']['max_tags'] );
		$this->assertEquals( 'Maximum numbers of tags that user can add when asking.', $forms['fields']['max_tags']['description'] );
		$this->assertArrayHasKey( 'subtype', $forms['fields']['max_tags'] );
		$this->assertEquals( 'number', $forms['fields']['max_tags']['subtype'] );
		$this->assertArrayHasKey( 'value', $forms['fields']['max_tags'] );
		$this->assertEquals( ap_opt( 'max_tags' ), $forms['fields']['max_tags']['value'] );

		// Test for min_tags field.
		$this->assertArrayHasKey( 'label', $forms['fields']['min_tags'] );
		$this->assertEquals( 'Minimum tags', $forms['fields']['min_tags']['label'] );
		$this->assertArrayHasKey( 'description', $forms['fields']['min_tags'] );
		$this->assertEquals( 'minimum numbers of tags that user must add when asking.', $forms['fields']['min_tags']['description'] );
		$this->assertArrayHasKey( 'subtype', $forms['fields']['min_tags'] );
		$this->assertEquals( 'number', $forms['fields']['min_tags']['subtype'] );
		$this->assertArrayHasKey( 'value', $forms['fields']['min_tags'] );
		$this->assertEquals( ap_opt( 'min_tags' ), $forms['fields']['min_tags']['value'] );

		// Test for tag_page_slug field.
		$this->assertArrayHasKey( 'label', $forms['fields']['tag_page_slug'] );
		$this->assertEquals( 'Tag page slug', $forms['fields']['tag_page_slug']['label'] );
		$this->assertArrayHasKey( 'desc', $forms['fields']['tag_page_slug'] );
		$this->assertEquals( 'Slug for tag page', $forms['fields']['tag_page_slug']['desc'] );
		$this->assertArrayHasKey( 'value', $forms['fields']['tag_page_slug'] );
		$this->assertEquals( ap_opt( 'tag_page_slug' ), $forms['fields']['tag_page_slug']['value'] );
	}

	/**
	 * @covers Anspress\Addons\Tags::register_question_tag
	 */
	public function testRegisterQuestionTag() {
		$instance = \Anspress\Addons\Tags::init();

		// Call the method.
		$instance->register_question_tag();

		// Test begins.
		$tag_options = [
			'max_tags'      => 5,
			'min_tags'      => 1,
			'tags_per_page' => 20,
			'tag_page_slug' => 'tag',
		];
		foreach ( $tag_options as $key => $value ) {
			$this->assertEquals( $value, ap_opt( $key ) );
		}

		global $wp_taxonomies;
		$question_tag = $wp_taxonomies['question_tag'];
		$this->assertTrue( isset( $question_tag ) );
		$this->assertTrue( taxonomy_exists( 'question_tag' ) );
		$this->assertEquals( 'question_tag', $question_tag->name );
		$this->assertEquals( 'Question Tags', $question_tag->label );
		$this->assertEquals( 'Question Tags', $question_tag->labels->name );
		$this->assertEquals( 'Tag', $question_tag->labels->singular_name );
		$this->assertEquals( 'All Tags', $question_tag->labels->all_items );
		$this->assertEquals( 'Add New Tag', $question_tag->labels->add_new_item );
		$this->assertEquals( 'Edit Tag', $question_tag->labels->edit_item );
		$this->assertEquals( 'New Tag', $question_tag->labels->new_item );
		$this->assertEquals( 'View Tag', $question_tag->labels->view_item );
		$this->assertEquals( 'Search Tag', $question_tag->labels->search_items );
		$this->assertEquals( 'Nothing Found', $question_tag->labels->not_found );
		$this->assertEquals( 'Nothing found in Trash', $question_tag->labels->not_found_in_trash );
		$this->assertEquals( '', $question_tag->labels->parent_item_colon );
		$this->assertEquals( 1, $question_tag->hierarchical );
		$this->assertEquals( 0, $question_tag->rewrite );
	}

	/**
	 * @covers Anspress\Addons\Tags::widget_positions
	 */
	public function testWidgetPositions() {
		$instance = \Anspress\Addons\Tags::init();

		// Call the method.
		$instance->widget_positions();

		global $wp_registered_sidebars;
		$this->assertArrayHasKey( 'ap-tags', $wp_registered_sidebars );
		$this->assertEquals( 'ap-tags', $wp_registered_sidebars['ap-tags']['id'] );
		$this->assertEquals( '(AnsPress) Tags', $wp_registered_sidebars['ap-tags']['name'] );
	}

	public function testTagTagsPagesRegistered() {
		$instance = \Anspress\Addons\Tags::init();

		// Test if the tag page is registered.
		$tag_page = anspress()->pages['tag'];
		$this->assertIsArray( $tag_page );
		$this->assertEquals( 'Tag', $tag_page['title'] );
		$this->assertEquals( [ $instance, 'tag_page' ], $tag_page['func'] );
		$this->assertEquals( false, $tag_page['show_in_menu'] );
		$this->assertEquals( false, $tag_page['private'] );

		// Test if the tags page is registered.
		$tags_page = anspress()->pages['tags'];
		$this->assertIsArray( $tags_page );
		$this->assertEquals( 'Tags', $tags_page['title'] );
		$this->assertEquals( [ $instance, 'tags_page' ], $tags_page['func'] );
		$this->assertEquals( true, $tags_page['show_in_menu'] );
		$this->assertEquals( false, $tags_page['private'] );
	}

	/**
	 * @covers Anspress\Addons\Tags::ap_current_page
	 */
	public function testAPCurrentPage() {
		$instance = \Anspress\Addons\Tags::init();

		// Test by visiting other page.
		$this->go_to( '/' );
		$result = $instance->ap_current_page( 'other_query_var' );
		$this->assertEquals( 'other_query_var', $result );

		// Test by visiting tags page.
		$tags_page = $this->factory()->post->create(
			[
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'Tags',
			]
		);
		ap_opt( 'tags_page', $tags_page );
		// Test by just visiting the tags page.
		$this->go_to( '/?post_type=page&p=' . $tags_page );
		$method = $instance->ap_current_page( 'other_query_var' );
		$this->assertEquals( 'other_query_var', $method );
		set_query_var( 'ap_page', 'tag' );
		$method = $instance->ap_current_page( 'other_query_var' );
		$this->assertEquals( 'other_query_var', $method );

		// Test on the single tag page.
		$tag_id = $this->factory->term->create( [ 'taxonomy' => 'question_tag' ] );
		$term = get_term_by( 'id', $tag_id, 'question_tag' );
		$this->go_to( '/?ap_page=tag&question_tag=' . $term->slug );
		// Test for passing invalid query var.
		$method = $instance->ap_current_page( 'other_query_var' );
		$this->assertEquals( 'other_query_var', $method );
		// Test for passing valid query var.
		$method = $instance->ap_current_page( 'tags' );
		$this->assertEquals( 'tag', $method );
	}
}
