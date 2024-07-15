<?php

namespace AnsPress\Tests\WP;

use AnsPress\Classes\Plugin;
use AnsPress\Modules\Category\CategoryModule;
use AnsPress\Modules\Category\CategoryWidget;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * @covers AnsPress\Modules\Category\CategoryModule
 */
class TestCategoryModule extends TestCase {

	use Testcases\Common;

	public function testHooks() {
		$instance = new CategoryModule();

		$instance->register_hooks();

		$this->assertEquals( 1, has_action( 'init', [ $instance, 'registerQuestionCategories' ], 1 ) );
		$this->assertEquals( 10, has_action( 'ap_settings_menu_features_groups', [ $instance, 'load_options' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_form_options_features_category', [ $instance, 'register_general_settings_form' ] ) );
		$this->assertEquals( 10, has_action( 'admin_enqueue_scripts', [ $instance, 'admin_enqueue_scripts' ] ) );
		$this->assertEquals( 10, has_action( 'ap_load_admin_assets', [ $instance, 'ap_load_admin_assets' ] ) );
		$this->assertEquals( 10, has_action( 'ap_admin_menu', [ $instance, 'admin_category_menu' ] ) );
		$this->assertEquals( 10, has_action( 'ap_display_question_metas', [ $instance, 'ap_display_question_metas' ] ) );
		$this->assertEquals( 10, has_action( 'ap_enqueue', [ $instance, 'ap_assets_js' ] ) );
		$this->assertEquals( 10, has_filter( 'term_link', [ $instance, 'termLinkFilter' ] ) );
		$this->assertEquals( 10, has_action( 'ap_question_form_fields', [ $instance, 'ap_question_form_fields' ] ) );
		$this->assertEquals( 0, has_action( 'save_post_question', [ $instance, 'after_new_question' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_breadcrumbs', [ $instance, 'ap_breadcrumbs' ] ) );
		$this->assertEquals( 10, has_action( 'terms_clauses', [ $instance, 'terms_clauses' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_list_filters', [ $instance, 'ap_list_filters' ] ) );
		$this->assertEquals( 10, has_action( 'question_category_add_form_fields', [ $instance, 'customFieldsOnNewForm' ] ) );
		$this->assertEquals( 10, has_action( 'question_category_edit_form_fields', [ $instance, 'image_field_edit' ] ) );
		$this->assertEquals( 10, has_action( 'create_question_category', [ $instance, 'save_image_field' ] ) );
		$this->assertEquals( 10, has_action( 'edited_question_category', [ $instance, 'save_image_field' ] ) );
		$this->assertEquals( 10, has_action( 'ap_rewrites', [ $instance, 'rewrite_rules' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_main_questions_args', [ $instance, 'ap_main_questions_args' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_question_subscribers_action_id', [ $instance, 'subscribers_action_id' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_ask_btn_link', [ $instance, 'ap_ask_btn_link' ] ) );
		$this->assertEquals( 10, has_filter( 'wp_head', [ $instance, 'category_feed' ] ) );
		$this->assertEquals( 10, has_filter( 'manage_edit-question_category_columns', [ $instance, 'column_header' ] ) );
		$this->assertEquals( 10, has_filter( 'manage_question_category_custom_column', [ $instance, 'column_content' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_current_page', [ $instance, 'ap_current_page' ] ) );
		$this->assertEquals( 9999, has_action( 'posts_pre_query', [ $instance, 'modify_query_category_archive' ] ) );
		$this->assertEquals( 10, has_action( 'ap_ajax_load_filter_category', [ $instance, 'load_filter_category' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_list_filter_active_category', [ $instance, 'filter_active_category' ] ) );
		$this->assertEquals( 10, has_action( 'widgets_init', [ $instance, 'widget' ] ) );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'categoryPage' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'categoriesPage' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'registerQuestionCategories' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'load_options' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'register_general_settings_form' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'admin_enqueue_scripts' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'ap_load_admin_assets' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'admin_category_menu' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'ap_display_question_metas' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'ap_assets_js' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'termLinkFilter' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'ap_question_form_fields' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'after_new_question' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'ap_breadcrumbs' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'terms_clauses' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'ap_list_filters' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'image_field_new' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'image_field_edit' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'save_image_field' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'rewrite_rules' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'ap_main_questions_args' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'subscribers_action_id' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'ap_ask_btn_link' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'ap_canonical_url' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'category_feed' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'load_filter_category' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'filter_active_category' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'column_header' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'column_content' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'ap_current_page' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'modify_query_category_archive' ) );
		$this->assertTrue( method_exists( 'AnsPress\Modules\Category\CategoryModule', 'widget' ) );
	}

	public function testCategoryPage() {
		$this->assertEquals(
			[
				'title'        => 'Categories',
				'show_in_menu' => true,
				'private'      => false,
				'func'         => [ Plugin::get(CategoryModule::class), 'categoriesPage' ]
			],
			anspress()->pages['categories']
		);
	}

	public function testCategoriesPage() {
		$this->assertEquals(
			[
				'title'        => 'Category',
				'show_in_menu' => false,
				'private'      => false,
				'func'         => [ Plugin::get(CategoryModule::class), 'categoryPage' ]
			],
			anspress()->pages['category']
		);
	}

	public function testTestRegisterCategoriesPage() {
		$this->assertTrue(taxonomy_exists( 'question_category' ));

		// Assert taxonomy labels.
		$taxonomy = get_taxonomy( 'question_category' );
		$this->assertEquals( 'Question Categories', $taxonomy->label );
		$this->assertEquals( 'Question Categories', $taxonomy->labels->name );
		$this->assertEquals( 'Category', $taxonomy->labels->singular_name );
		$this->assertEquals( 'All Categories', $taxonomy->labels->all_items );
		$this->assertEquals( 'Add New Category', $taxonomy->labels->add_new_item );
		$this->assertEquals( 'Edit Category', $taxonomy->labels->edit_item );
		$this->assertEquals( 'New Category', $taxonomy->labels->new_item );
		$this->assertEquals( 'View Category', $taxonomy->labels->view_item );
		$this->assertEquals( 'Search Category', $taxonomy->labels->search_items );
		$this->assertEquals( 'Nothing Found', $taxonomy->labels->not_found );
		$this->assertEquals( 'Nothing found in Trash', $taxonomy->labels->not_found_in_trash );
		$this->assertEquals( '', $taxonomy->labels->parent_item_colon );

		$this->assertEquals( 'question_category', $taxonomy->name );
		$this->assertTrue( $taxonomy->hierarchical );
		$this->assertTrue( $taxonomy->publicly_queryable );
		$this->assertFalse( $taxonomy->rewrite );

	}

	public function test_ap_breadcrumbs_single_question()
	{
		$question = $this->factory()->post->create_and_get([
			'post_type'  => 'question',
			'post_title' => 'Question Title',
		]);

		$instance = new CategoryModule();

		// Call the method.
		$instance->registerQuestionCategories();

		$category = $this->factory()->term->create_and_get([
			'taxonomy' => 'question_category',
			'name'     => 'Category Name',
		]);

		wp_set_post_terms( $question->ID, $category->term_id, 'question_category' );

		$this->go_to( '/?post_type=question&p=' . $question->ID );

		$breadcrumbs = apply_filters( 'ap_breadcrumbs', [] );

		$this->assertNotEmpty( $breadcrumbs['category'] );
		$this->assertEquals(
			[
				'title' => 'Category Name',
				'link'   => get_term_link( $category->term_id, 'question_category' ),
				'order' => 2,
			],
			$breadcrumbs['category']
		);
	}

	public function test_ap_breadcrumbs_single_category()
	{
		$categories_page = $this->factory()->post->create_and_get([
			'post_type'  => 'page',
			'post_title' => 'Categories',
		]);

		ap_opt( 'categories_page', $categories_page->ID );

		$question = $this->factory()->post->create_and_get([
			'post_type'  => 'question',
			'post_title' => 'Question Title',
		]);

		$instance = new CategoryModule();

		// Call the method.
		$instance->registerQuestionCategories();

		$category = $this->factory()->term->create_and_get([
			'taxonomy' => 'question_category',
			'name'     => 'Category Name',
		]);

		wp_set_post_terms( $question->ID, $category->term_id, 'question_category' );

		$this->go_to( '/?ap_page=category&question_category=' . $category->slug );

		$breadcrumbs = apply_filters( 'ap_breadcrumbs', [] );

		$this->assertNotEmpty( $breadcrumbs['category'] );
		$this->assertEquals(
			[
				'title' => 'Category Name',
				'link'   => get_term_link( $category->term_id, 'question_category' ),
				'order' => 8,
			],
			$breadcrumbs['category']
		);
		$this->assertEquals(
			array(
				'title' => 'Categories',
				'link'  => ap_get_link_to( 'categories' ),
				'order' => 8,
			),
			$breadcrumbs['page']
		);
	}

	public function testLoadOptions() {
		$instance = new CategoryModule();

		// Call the method.
		$groups = $instance->load_options( [] );

		// Test if the Category group is added to the settings page.
		$this->assertArrayHasKey( 'category', $groups );
		$this->assertEquals( 'Category', $groups['category']['label'] );

		// Test by adding new group.
		$groups = $instance->load_options( [ 'some_other_group' => [ 'label' => 'Some Other Group' ] ] );

		// Test if the new group is added to the settings page.
		$this->assertArrayHasKey( 'some_other_group', $groups );
		$this->assertEquals( 'Some Other Group', $groups['some_other_group']['label'] );

		// Test if the existing group are retained to the settings page.
		$this->assertArrayHasKey( 'category', $groups );
		$this->assertEquals( 'Category', $groups['category']['label'] );
	}

	public function testRegisterGeneralSettingsForm() {
		$instance = new CategoryModule();

		// Add form_category_orderby, categories_page_order, categories_page_orderby, category_page_slug, categories_per_page and categories_image_height options.
		ap_add_default_options(
			array(
				'form_category_orderby'   => 'count',
				'categories_page_order'   => 'DESC',
				'categories_page_orderby' => 'count',
				'category_page_slug'      => 'category',
				'categories_per_page'     => 20,
				'categories_image_height' => 150,
			)
		);

		// Call the method.
		$forms = $instance->register_general_settings_form();

		// Test begins.
		$this->assertNotEmpty( $forms );
		$this->assertArrayHasKey( 'categories_page_info', $forms['fields'] );
		$this->assertArrayHasKey( 'form_category_orderby', $forms['fields'] );
		$this->assertArrayHasKey( 'categories_page_orderby', $forms['fields'] );
		$this->assertArrayHasKey( 'categories_page_order', $forms['fields'] );
		$this->assertArrayHasKey( 'categories_per_page', $forms['fields'] );
		$this->assertArrayHasKey( 'categories_image_height', $forms['fields'] );

		// Test for categories_page_info field.
		$this->assertArrayHasKey( 'html', $forms['fields']['categories_page_info'] );
		$this->assertStringContainsString( '<label class="ap-form-label" for="form_options_category_general-categories_page_info">Categories base page</label>', $forms['fields']['categories_page_info']['html'] );
		$this->assertStringContainsString( 'Base page for categories can be configured in general settings of AnsPress.', $forms['fields']['categories_page_info']['html'] );

		// Test for form_category_orderby field.
		$this->assertArrayHasKey( 'label', $forms['fields']['form_category_orderby'] );
		$this->assertEquals( 'Ask form category order', $forms['fields']['form_category_orderby']['label'] );
		$this->assertArrayHasKey( 'description', $forms['fields']['form_category_orderby'] );
		$this->assertEquals( 'Set how you want to order categories in form.', $forms['fields']['form_category_orderby']['description'] );
		$this->assertArrayHasKey( 'type', $forms['fields']['form_category_orderby'] );
		$this->assertEquals( 'select', $forms['fields']['form_category_orderby']['type'] );
		$this->assertArrayHasKey( 'options', $forms['fields']['form_category_orderby'] );
		$options_array = [
			'ID'         => 'ID',
			'name'       => 'Name',
			'slug'       => 'Slug',
			'count'      => 'Count',
			'term_group' => 'Group',
		];
		$this->assertEquals( $options_array, $forms['fields']['form_category_orderby']['options'] );
		$this->assertArrayHasKey( 'ID', $forms['fields']['form_category_orderby']['options'] );
		$this->assertEquals( 'ID', $forms['fields']['form_category_orderby']['options']['ID'] );
		$this->assertArrayHasKey( 'name', $forms['fields']['form_category_orderby']['options'] );
		$this->assertEquals( 'Name', $forms['fields']['form_category_orderby']['options']['name'] );
		$this->assertArrayHasKey( 'slug', $forms['fields']['form_category_orderby']['options'] );
		$this->assertEquals( 'Slug', $forms['fields']['form_category_orderby']['options']['slug'] );
		$this->assertArrayHasKey( 'count', $forms['fields']['form_category_orderby']['options'] );
		$this->assertEquals( 'Count', $forms['fields']['form_category_orderby']['options']['count'] );
		$this->assertArrayHasKey( 'term_group', $forms['fields']['form_category_orderby']['options'] );
		$this->assertEquals( 'Group', $forms['fields']['form_category_orderby']['options']['term_group'] );
		$this->assertArrayHasKey( 'value', $forms['fields']['form_category_orderby'] );
		$this->assertEquals( ap_opt( 'form_category_orderby' ), $forms['fields']['form_category_orderby']['value'] );

		// Test for categories_page_orderby field.
		$this->assertArrayHasKey( 'label', $forms['fields']['categories_page_orderby'] );
		$this->assertEquals( 'Categories page order by', $forms['fields']['categories_page_orderby']['label'] );
		$this->assertArrayHasKey( 'description', $forms['fields']['categories_page_orderby'] );
		$this->assertEquals( 'Set how you want to order categories in categories page.', $forms['fields']['categories_page_orderby']['description'] );
		$this->assertArrayHasKey( 'type', $forms['fields']['categories_page_orderby'] );
		$this->assertEquals( 'select', $forms['fields']['categories_page_orderby']['type'] );
		$this->assertArrayHasKey( 'options', $forms['fields']['categories_page_orderby'] );
		$options_array = [
			'ID'         => 'ID',
			'name'       => 'Name',
			'slug'       => 'Slug',
			'count'      => 'Count',
			'term_group' => 'Group',
		];
		$this->assertEquals( $options_array, $forms['fields']['categories_page_orderby']['options'] );
		$this->assertArrayHasKey( 'ID', $forms['fields']['categories_page_orderby']['options'] );
		$this->assertEquals( 'ID', $forms['fields']['categories_page_orderby']['options']['ID'] );
		$this->assertArrayHasKey( 'name', $forms['fields']['categories_page_orderby']['options'] );
		$this->assertEquals( 'Name', $forms['fields']['categories_page_orderby']['options']['name'] );
		$this->assertArrayHasKey( 'slug', $forms['fields']['categories_page_orderby']['options'] );
		$this->assertEquals( 'Slug', $forms['fields']['categories_page_orderby']['options']['slug'] );
		$this->assertArrayHasKey( 'count', $forms['fields']['categories_page_orderby']['options'] );
		$this->assertEquals( 'Count', $forms['fields']['categories_page_orderby']['options']['count'] );
		$this->assertArrayHasKey( 'term_group', $forms['fields']['categories_page_orderby']['options'] );
		$this->assertEquals( 'Group', $forms['fields']['categories_page_orderby']['options']['term_group'] );
		$this->assertArrayHasKey( 'value', $forms['fields']['categories_page_orderby'] );
		$this->assertEquals( ap_opt( 'categories_page_orderby' ), $forms['fields']['categories_page_orderby']['value'] );

		// Test for categories_page_order field.
		$this->assertArrayHasKey( 'label', $forms['fields']['categories_page_order'] );
		$this->assertEquals( 'Categories page order', $forms['fields']['categories_page_order']['label'] );
		$this->assertArrayHasKey( 'description', $forms['fields']['categories_page_order'] );
		$this->assertEquals( 'Set how you want to order categories in categories page.', $forms['fields']['categories_page_order']['description'] );
		$this->assertArrayHasKey( 'type', $forms['fields']['categories_page_order'] );
		$this->assertEquals( 'select', $forms['fields']['categories_page_order']['type'] );
		$this->assertArrayHasKey( 'options', $forms['fields']['categories_page_order'] );
		$options_array = [
			'ASC'  => 'Ascending',
			'DESC' => 'Descending',
		];
		$this->assertEquals( $options_array, $forms['fields']['categories_page_order']['options'] );
		$this->assertArrayHasKey( 'ASC', $forms['fields']['categories_page_order']['options'] );
		$this->assertEquals( 'Ascending', $forms['fields']['categories_page_order']['options']['ASC'] );
		$this->assertArrayHasKey( 'DESC', $forms['fields']['categories_page_order']['options'] );
		$this->assertEquals( 'Descending', $forms['fields']['categories_page_order']['options']['DESC'] );
		$this->assertArrayHasKey( 'value', $forms['fields']['categories_page_order'] );
		$this->assertEquals( ap_opt( 'categories_page_order' ), $forms['fields']['categories_page_order']['value'] );

		// Test for categories_per_page field.
		$this->assertArrayHasKey( 'label', $forms['fields']['categories_per_page'] );
		$this->assertEquals( 'Category per page', $forms['fields']['categories_per_page']['label'] );
		$this->assertArrayHasKey( 'desc', $forms['fields']['categories_per_page'] );
		$this->assertEquals( 'Category to show per page', $forms['fields']['categories_per_page']['desc'] );
		$this->assertArrayHasKey( 'subtype', $forms['fields']['categories_per_page'] );
		$this->assertEquals( 'number', $forms['fields']['categories_per_page']['subtype'] );
		$this->assertArrayHasKey( 'value', $forms['fields']['categories_per_page'] );
		$this->assertEquals( ap_opt( 'categories_per_page' ), $forms['fields']['categories_per_page']['value'] );

		// Test for categories_image_height field.
		$this->assertArrayHasKey( 'label', $forms['fields']['categories_image_height'] );
		$this->assertEquals( 'Categories image height', $forms['fields']['categories_image_height']['label'] );
		$this->assertArrayHasKey( 'desc', $forms['fields']['categories_image_height'] );
		$this->assertEquals( 'Image height in categories page', $forms['fields']['categories_image_height']['desc'] );
		$this->assertArrayHasKey( 'subtype', $forms['fields']['categories_image_height'] );
		$this->assertEquals( 'number', $forms['fields']['categories_image_height']['subtype'] );
		$this->assertArrayHasKey( 'value', $forms['fields']['categories_image_height'] );
		$this->assertEquals( ap_opt( 'categories_image_height' ), $forms['fields']['categories_image_height']['value'] );
	}

	public function testWidget() {
		$instance = new CategoryModule;
		$instance->widget();
		$this->assertTrue( array_key_exists( CategoryWidget::class, $GLOBALS['wp_widget_factory']->widgets ) );
	}

	public function testCategoryFeed() {
		$instance = new CategoryModule();

		// Test begins.
		// Test without viewing the category page.
		$this->go_to( '/' );
		ob_start();
		$instance->category_feed();
		$result = ob_get_clean();
		$this->assertEmpty( $result );
		$this->assertEquals( '', $result );

		// Test with viewing the category page.
		$category_id = $this->factory()->term->create( [ 'taxonomy' => 'question_category' ] );
		$term = get_term_by( 'id', $category_id, 'question_category' );
		$this->go_to( '/?ap_page=category&question_category=' . $term->slug );
		ob_start();
		$instance->category_feed();
		$result = ob_get_clean();
		$this->assertNotNull( $result );
		$this->assertStringContainsString( esc_url( home_url( 'feed' ) ) . '?post_type=question&question_category=' . esc_attr( $term->slug ), $result );
		$this->assertStringContainsString( 'Question category feed', $result );
		$this->assertStringContainsString( 'application/rss+xml', $result );
		$this->assertStringContainsString( 'alternate', $result );
		$this->assertEquals( '<link href="' . esc_url( home_url( 'feed' ) ) . '?post_type=question&question_category=' . esc_attr( $term->slug ) . '" title="Question category feed" type="application/rss+xml" rel="alternate">', $result );
	}

	public function testAPAskBtnLink() {
		$instance = new CategoryModule();

		$this->go_to( '/' );
		$result = $instance->ap_ask_btn_link( '' );
		$this->assertEmpty( $result );

		// Test 2.
		$result = $instance->ap_ask_btn_link( 'http://example.com' );
		$this->assertNotEmpty( $result );
		$this->assertEquals( 'http://example.com', $result );

		// Test with viewing the category page.
		$category_id = $this->factory()->term->create( [ 'taxonomy' => 'question_category' ] );
		$term = get_term_by( 'id', $category_id, 'question_category' );

		// Test 1.
		$this->go_to( '/?ap_page=category&question_category=' . $term->slug );
		$result = $instance->ap_ask_btn_link( '' );
		$this->assertNotEmpty( $result );
		$this->assertEquals( '?category=' . $term->term_id, $result );

		// Test 2.
		$result = $instance->ap_ask_btn_link( 'http://example.com' );
		$this->assertNotEmpty( $result );
		$this->assertEquals( 'http://example.com?category=' . $term->term_id, $result );
	}

	public function testColumnHeader() {
		$instance = new CategoryModule();

		// Call the method.
		$columns = $instance->column_header( [] );

		// Test begins.
		$this->assertNotEmpty( $columns );
		$this->assertArrayHasKey( 'icon', $columns );
		$this->assertEquals( 'Icon', $columns['icon'] );
	}

	public function testColumnContent() {
		$instance = new CategoryModule();

		// Test begins.
		$category = $this->factory()->term->create( [ 'taxonomy' => 'question_category' ] );
		$term = get_term_by( 'id', $category, 'question_category' );

		// Test without any term meta values.
		ob_start();
		$instance->column_content( '', 'icon', $term->term_id );
		$result = ob_get_clean();
		$this->assertEmpty( $result );

		// Test with term meta values.
		// Test 1.
		$term_meta = [ 'icon' => 'apicon-star' ];
		update_term_meta( $term->term_id, 'ap_category', $term_meta );
		ob_start();
		$instance->column_content( '', 'icon', $term->term_id );
		$result = ob_get_clean();
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( 'apicon-star', $result );
		$this->assertEquals( '<span class="ap-category-icon apicon-star"style=""></span>', $result );

		// Test 2.
		$term_meta = [ 'icon' => 'apicon-question', 'color' => '#000000' ];
		update_term_meta( $term->term_id, 'ap_category', $term_meta );
		ob_start();
		$instance->column_content( '', 'icon', $term->term_id );
		$result = ob_get_clean();
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( 'apicon-question', $result );
		$this->assertStringContainsString( 'background:#000000', $result );
		$this->assertEquals( '<span class="ap-category-icon apicon-question"style=" background:#000000;"></span>', $result );
	}

	public function testAPCurrentPage() {
		$instance = new CategoryModule();

		// Test by visiting other page.
		$this->go_to( '/' );
		$result = $instance->ap_current_page( 'other_query_var' );
		$this->assertEquals( 'other_query_var', $result );

		// Test by visiting categories page.
		$categories_page = $this->factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'Categories',
			)
		);

		ap_opt( 'categories_page', $categories_page );
		// Test by just visiting the categories page.
		$this->go_to( '/?post_type=page&p=' . $categories_page );
		$method = $instance->ap_current_page( 'other_query_var' );
		$this->assertEquals( 'other_query_var', $method );
		set_query_var( 'ap_page', 'category' );
		$method = $instance->ap_current_page( 'other_query_var' );
		$this->assertEquals( 'other_query_var', $method );

		// Test on the single category page.
		$category_id = $this->factory()->term->create( [ 'taxonomy' => 'question_category' ] );
		$term = get_term_by( 'id', $category_id, 'question_category' );
		$this->go_to( '/?ap_page=category&question_category=' . $term->slug );
		// Test for passing invalid query var.
		$method = $instance->ap_current_page( 'other_query_var' );
		$this->assertEquals( 'other_query_var', $method );
		// Test for passing valid query var.
		$method = $instance->ap_current_page( 'categories' );
		$this->assertEquals( 'category', $method );
	}

	public function testAPAssetsJS() {
		$instance = new CategoryModule();

		// Required for wp_script_is() to work as expected.
		ob_start();
		do_action( 'wp_enqueue_scripts' );
		ob_end_clean();

		// Test begins.
		// Directly cally the method.
		$instance->ap_assets_js( [] );
		$this->assertFalse( wp_script_is( 'anspress-theme' ) );

		// Without visting the category page.
		$this->go_to( '/' );
		$instance->ap_assets_js( [] );
		$this->assertFalse( wp_script_is( 'anspress-theme' ) );

		// With visting the category page.
		$category_id = $this->factory()->term->create( [ 'taxonomy' => 'question_category' ] );
		$term = get_term_by( 'id', $category_id, 'question_category' );
		$this->go_to( '/?ap_page=category&question_category=' . $term->slug );
		$instance->ap_assets_js( [] );
		$this->assertTrue( wp_script_is( 'anspress-theme' ) );
	}

	public function testImageFieldNew() {
		$instance = new CategoryModule();

		// Test begins.
		ob_start();
		$instance->customFieldsOnNewForm( '' );
		$result = ob_get_clean();
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( '<a href="#" id="ap-category-upload" class="button" data-action="ap_media_uplaod" data-title="Upload image" data-urlc="#ap_category_media_url" data-idc="#ap_category_media_id">Upload image</a>', $result );
		$this->assertStringContainsString( '<input id="ap_category_media_url" type="hidden" data-action="ap_media_value" name="ap_category_image_url" value="">', $result );
		$this->assertStringContainsString( '<input id="ap_category_media_id" type="hidden" data-action="ap_media_value" name="ap_category_image_id" value="">', $result );
		$this->assertStringContainsString( '<input id="ap-category-color" type="text" name="ap_color" value="">', $result );
		$this->assertStringContainsString( 'jQuery(\'#ap-category-color\').wpColorPicker();', $result );
	}

	/**
	 * @covers AnsPress\Modules\Category\CategoryModule::image_field_edit
	 */
	public function testImageFieldEdit() {
		$instance = new CategoryModule();

		// Test 1.
		$term_id = $this->factory()->term->create( [ 'taxonomy' => 'question_category' ] );
		ob_start();
		$instance->image_field_edit( get_term( $term_id, 'question_category' ) );
		$result = ob_get_clean();
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( '<a href="#" id="ap-category-upload" class="button" data-action="ap_media_uplaod" data-title="Upload image" data-idc="#ap_category_media_id" data-urlc="#ap_category_media_url">Upload image</a>', $result );
		$this->assertStringContainsString( '<input id="ap_category_media_url" type="hidden" data-action="ap_media_value" name="ap_category_image_url" value="">', $result );
		$this->assertStringContainsString( '<input id="ap_category_media_id" type="hidden" data-action="ap_media_value" name="ap_category_image_id" value="">', $result );
		$this->assertStringNotContainsString( '<a href="#" id="ap-category-upload-remove" data-action="ap_media_remove">Remove image</a>', $result );
		$this->assertStringContainsString( '<input id="ap_icon" type="text" name="ap_icon" value="">', $result );
		$this->assertStringContainsString( '<input id="ap-category-color" type="text" name="ap_color" value="">', $result );
		$this->assertStringContainsString( 'jQuery(\'#ap-category-color\').wpColorPicker();', $result );
		$this->assertStringNotContainsString( '<img id="ap_category_media_preview" data-action="ap_media_value" src="" />', $result );

		// Test 2.
		$term_id = $this->factory()->term->create( [ 'taxonomy' => 'question_category' ] );
		$meta = [
			'image' => [
				'id'  => 1,
				'url' => 'http://example.com/image.jpg',
			],
			'icon'  => 'apicon-star',
			'color' => '#000000',
		];
		update_term_meta( $term_id, 'ap_category', $meta );
		ob_start();
		$instance->image_field_edit( get_term( $term_id, 'question_category' ) );
		$result = ob_get_clean();
		$term_meta = get_term_meta( $term_id, 'ap_category', true );
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( '<a href="#" id="ap-category-upload" class="button" data-action="ap_media_uplaod" data-title="Upload image" data-idc="#ap_category_media_id" data-urlc="#ap_category_media_url">Upload image</a>', $result );
		$this->assertStringContainsString( '<img id="ap_category_media_preview" data-action="ap_media_value" src="' . esc_url( $term_meta['image']['url'] ) . '" />', $result );
		$this->assertStringNotContainsString( '<input id="ap_category_media_url" type="hidden" data-action="ap_media_value" name="ap_category_image_url" value="">', $result );
		$this->assertStringContainsString( '<input id="ap_category_media_url" type="hidden" data-action="ap_media_value" name="ap_category_image_url" value="' . esc_url( $term_meta['image']['url'] ) . '">', $result );
		$this->assertStringNotContainsString( '<input id="ap_category_media_id" type="hidden" data-action="ap_media_value" name="ap_category_image_id" value="">', $result );
		$this->assertStringContainsString( '<input id="ap_category_media_id" type="hidden" data-action="ap_media_value" name="ap_category_image_id" value="' . $term_meta['image']['id'] . '">', $result );
		$this->assertStringContainsString( '<a href="#" id="ap-category-upload-remove" data-action="ap_media_remove">Remove image</a>', $result );
		$this->assertStringContainsString( '<input id="ap_icon" type="text" name="ap_icon" value="' . $term_meta['icon'] . '">', $result );
		$this->assertStringNotContainsString( '<input id="ap_icon" type="text" name="ap_icon" value="">', $result );
		$this->assertStringContainsString( '<input id="ap-category-color" type="text" name="ap_color" value="' . $term_meta['color'] . '">', $result );
		$this->assertStringNotContainsString( '<input id="ap-category-color" type="text" name="ap_color" value="">', $result );
		$this->assertStringContainsString( 'jQuery(\'#ap-category-color\').wpColorPicker();', $result );
	}

	public function testSaveImageField() {
		$instance = new CategoryModule();
		$term_id = $this->factory()->term->create( [ 'taxonomy' => 'question_category' ] );

		// Test begins.
		// Test without any input.
		// For users who don't have manage_categories capability.
		$this->setRole( 'subscriber' );
		$instance->save_image_field( $term_id );
		$term_meta = get_term_meta( $term_id, 'ap_category', true );
		$this->assertEmpty( $term_meta );

		// For users who have manage_categories capability.
		$this->setRole( 'administrator' );
		$instance->save_image_field( $term_id );
		$term_meta = get_term_meta( $term_id, 'ap_category', true );
		$this->assertEmpty( $term_meta );

		// Test with invalid input.
		$_REQUEST['ap_category_image_url'] = '';
		$_REQUEST['ap_category_image_id'] = '';
		$_REQUEST['ap_icon'] = '';
		$_REQUEST['ap_color'] = '';

		// For users who don't have manage_categories capability.
		$this->setRole( 'subscriber' );
		$instance->save_image_field( $term_id );
		$term_meta = get_term_meta( $term_id, 'ap_category', true );
		$this->assertEmpty( $term_meta );

		// For users who have manage_categories capability.
		$this->setRole( 'administrator' );
		$instance->save_image_field( $term_id );
		$term_meta = get_term_meta( $term_id, 'ap_category', true );
		$this->assertEmpty( $term_meta );

		// Test with valid input.
		$_REQUEST['ap_category_image_url'] = 'http://example.com/image.jpg';
		$_REQUEST['ap_category_image_id'] = 1;
		$_REQUEST['ap_icon'] = 'apicon-star';
		$_REQUEST['ap_color'] = '#000000';

		// For users who don't have manage_categories capability.
		$this->setRole( 'subscriber' );
		$instance->save_image_field( $term_id );
		$term_meta = get_term_meta( $term_id, 'ap_category', true );
		$this->assertEmpty( $term_meta );

		// For users who have manage_categories capability.
		$this->setRole( 'administrator' );
		$instance->save_image_field( $term_id );
		$term_meta = get_term_meta( $term_id, 'ap_category', true );
		$this->assertNotEmpty( $term_meta );
		$this->assertArrayHasKey( 'image', $term_meta );
		$this->assertArrayHasKey( 'icon', $term_meta );
		$this->assertArrayHasKey( 'color', $term_meta );
		$this->assertEquals( 'http://example.com/image.jpg', $term_meta['image']['url'] );
		$this->assertEquals( 1, $term_meta['image']['id'] );
		$this->assertEquals( 'apicon-star', $term_meta['icon'] );
		$this->assertEquals( '#000000', $term_meta['color'] );
	}

	public function testSubscribersActionID() {
		$instance = new CategoryModule();

		// Test begins.
		// Test 1.
		$this->go_to( '/' );
		$result = $instance->subscribers_action_id( '' );
		$this->assertEquals( '', $result );

		// Test 2.
		$this->go_to( '/' );
		$result = $instance->subscribers_action_id( '123' );
		$this->assertEquals( '123', $result );

		// Test 3.
		$term_id = $this->factory()->term->create( [ 'taxonomy' => 'question_category' ] );
		$term = get_term_by( 'id', $term_id, 'question_category' );
		$this->go_to( '/?ap_page=category&question_category=' . $term->slug );
		global $question_category;
		$question_category = $term;
		$result = $instance->subscribers_action_id( '' );
		$this->assertEquals( $term_id, $result );

		// Test 4.
		$term_id = $this->factory()->term->create( [ 'taxonomy' => 'question_category' ] );
		$term = get_term_by( 'id', $term_id, 'question_category' );
		$this->go_to( '/?ap_page=category&question_category=' . $term->slug );
		global $question_category;
		$question_category = $term;
		$result = $instance->subscribers_action_id( '111' );
		$this->assertEquals( $term_id, $result );
	}

	/**
	 * @covers AnsPress\Modules\Category\CategoryModule::ap_canonical_url
	 */
	public function testAPCanonicalUrl() {
		$instance = new CategoryModule();

		// Test begins.
		// Test 1.
		$this->go_to( '/' );
		$result = $instance->ap_canonical_url( '' );
		$this->assertEquals( '', $result );

		// Test 2.
		$this->go_to( '/' );
		$result = $instance->ap_canonical_url( 'http://example.com' );
		$this->assertEquals( 'http://example.com', $result );

		// Test 3.
		$term_id = $this->factory()->term->create( [ 'taxonomy' => 'question_category' ] );
		$term = get_term_by( 'id', $term_id, 'question_category' );
		$this->go_to( '/?ap_page=category&question_category=' . $term->slug );
		global $question_category;
		$question_category = $term;
		$result = $instance->ap_canonical_url( '' );
		$this->assertEquals( get_term_link( $term_id ), $result );

		// Test 4.
		$term_id = $this->factory()->term->create( [ 'taxonomy' => 'question_category' ] );
		$term = get_term_by( 'id', $term_id, 'question_category' );
		$this->go_to( '/?ap_page=category&question_category=' . $term->slug );
		global $question_category;
		$question_category = $term;
		$result = $instance->ap_canonical_url( 'http://example.com' );
		$this->assertEquals( get_term_link( $term_id ), $result );
		$question_category = null;
	}

	public function testAPLoadAdminAssets() {
		$instance = new CategoryModule();

		// Test begins.
		// Test 1.
		set_current_screen( 'edit-tags.php' );
		$result = $instance->ap_load_admin_assets( true );
		$this->assertTrue( $result );
		$result = $instance->ap_load_admin_assets( false );
		$this->assertFalse( $result );

		// Test 2.
		set_current_screen( 'dashboard' );
		$result = $instance->ap_load_admin_assets( true );
		$this->assertTrue( $result );
		$result = $instance->ap_load_admin_assets( false );
		$this->assertFalse( $result );

		// Test 3.
		set_current_screen( 'question_category' );
		$result = $instance->ap_load_admin_assets( true );
		$this->assertTrue( $result );
		$result = $instance->ap_load_admin_assets( false );
		$this->assertFalse( $result );

		// Test 4,
		set_current_screen( 'edit-question_tag' );
		$result = $instance->ap_load_admin_assets( true );
		$this->assertTrue( $result );
		$result = $instance->ap_load_admin_assets( false );
		$this->assertFalse( $result );

		// Test 5.
		set_current_screen( 'edit-question_category' );
		$result = $instance->ap_load_admin_assets( true );
		$this->assertTrue( $result );
		$result = $instance->ap_load_admin_assets( false );
		$this->assertTrue( $result );
	}

	public function testAdminCategoryMenu() {
		$this->setRole( 'administrator' );
		global $submenu;
		$instance = new CategoryModule();

		// Test begins.
		$instance->admin_category_menu();
		$this->assertNotEmpty( menu_page_url( 'edit-tags.php?taxonomy=question_category', false ) );
		$this->assertArrayHasKey( 'anspress', $submenu );
		$this->assertContains( 'manage_options', $submenu['anspress'][0] );
		$this->assertContains( 'Question Categories', $submenu['anspress'][0] );
		$this->assertContains( 'edit-tags.php?taxonomy=question_category', $submenu['anspress'][0] );
		unset( $submenu['anspress'] );
		$this->logout();
	}

	public function testTermLinkFilter() {
		$instance = new CategoryModule();

		// Test begins.
		$term_id = $this->factory()->term->create( [ 'taxonomy' => 'question_category' ] );
		$term = get_term_by( 'id', $term_id, 'question_category' );

		// Test 1.
		$result = $instance->termLinkFilter( 'http://example.com/sample-page/', $term, 'question_tag' );
		$this->assertEquals( 'http://example.com/sample-page/', $result );

		// Test 2.
		update_option( 'permalink_structure', '' );
		$result = $instance->termLinkFilter( 'http://example.com/sample-page/', $term, 'question_category' );
		$this->assertStringContainsString( 'ap_page=category', $result );
		$this->assertStringContainsString( 'question_category=' . $term->slug, $result );
		$this->assertEquals( home_url() . '?ap_page=category&question_category=' . $term->slug, $result );

		// Test 3.
		update_option( 'permalink_structure', '/%postname%/' );
		$result = $instance->termLinkFilter( 'http://example.com/sample-page/', $term, 'question_category' );
		$this->assertStringContainsString( 'categories', $result );
		$this->assertStringContainsString( $term->slug, $result );
		$this->assertEquals( home_url() . '/categories/' . $term->slug . '/', $result );

		// Test 4.
		update_option( 'ap_categories_path', 'tests' );
		$result = $instance->termLinkFilter( 'http://example.com/sample-page/', $term, 'question_category' );
		$this->assertStringContainsString( 'tests', $result );
		$this->assertStringContainsString( $term->slug, $result );
		$this->assertEquals( home_url() . '/tests/' . $term->slug . '/', $result );

		// Reset options to default.
		update_option( 'ap_categories_path', 'categories' );
		update_option( 'permalink_structure', '' );
	}

	public function testAPQuestionFormFields() {
		$instance = new CategoryModule();

		// Test with empty question categories.
		$form = $instance->ap_question_form_fields( [] );
		$this->assertEmpty( $form );

		// Test with question categories.
		$term_id_1 = $this->factory()->term->create( [ 'taxonomy' => 'question_category' ] );
		$term_id_2 = $this->factory()->term->create( [ 'taxonomy' => 'question_category' ] );
		$term_id_3 = $this->factory()->term->create( [ 'taxonomy' => 'question_category' ] );
		$question_id = $this->insert_question();
		wp_set_object_terms( $question_id, [ $term_id_2, $term_id_3 ], 'question_category' );

		// Test.
		$form = $instance->ap_question_form_fields( [] );
		$this->assertNotEmpty( $form );
		$this->assertArrayHasKey( 'fields', $form );

		// Test on category field.
		$form = $instance->ap_question_form_fields( [] );
		$this->assertArrayHasKey( 'category', $form['fields'] );
		$expected_category = [
			'label'    => 'Category',
			'desc'     => 'Select a topic that best fits your question.',
			'type'     => 'select',
			'options'  => 'terms',
			'order'    => 2,
			'validate' => 'required,not_zero',
		];
		$this->assertEquals( $expected_category, $form['fields']['category'] );

		// Test if passing the category id.
		$_REQUEST['category'] = $term_id_1;
		$form = $instance->ap_question_form_fields( [] );
		$this->assertArrayHasKey( 'value', $form['fields']['category'] );
		$this->assertEquals( $term_id_1, $form['fields']['category']['value'] );
		$expected_category['value'] = $term_id_1;
		$this->assertEquals( $expected_category, $form['fields']['category'] );
		unset( $_REQUEST['category'] );

		// Test if passing editing id.
		$_REQUEST['id'] = $question_id;
		$form = $instance->ap_question_form_fields( [] );
		$this->assertArrayHasKey( 'value', $form['fields']['category'] );
		$this->assertEquals( $term_id_2, $form['fields']['category']['value'] );
		$expected_category['value'] = $term_id_2;
		$this->assertEquals( $expected_category, $form['fields']['category'] );
		unset( $_REQUEST['id'] );

		// Test passing both category id and editing id.
		$_REQUEST['category'] = $term_id_3;
		$_REQUEST['id'] = $question_id;
		$form = $instance->ap_question_form_fields( [] );
		$this->assertArrayHasKey( 'value', $form['fields']['category'] );
		$this->assertEquals( $term_id_2, $form['fields']['category']['value'] );
		$expected_category['value'] = $term_id_2;
		$this->assertEquals( $expected_category, $form['fields']['category'] );
		unset( $_REQUEST['category'] );
		unset( $_REQUEST['id'] );
	}

	public function testAPListFilters() {
		global $wp;
		$instance = new CategoryModule();
		$category_id = $this->factory()->term->create( [ 'taxonomy' => 'question_category' ] );
		$term = get_term_by( 'id', $category_id, 'question_category' );

		// Test begins.
		// Test 1.
		$wp->query_vars['ap_categories'] = '';
		$this->go_to( '/?ap_page=category&question_category=' . $term->slug );
		$result = $instance->ap_list_filters( [] );
		$this->assertIsArray( $result );
		$this->assertEmpty( $result );

		// Test 2.
		$wp->query_vars['ap_categories'] = '';
		$this->go_to( '/' );
		$result = $instance->ap_list_filters( [] );
		$this->assertIsArray( $result );
		$this->assertNotEmpty( $result );
		$expected_result = [
			'category' => [
				'title'    => 'Category',
				'items'    => [],
				'search'   => true,
				'multiple' => true,
			],
		];
		$this->assertEquals( $expected_result, $result );

		// Test 3.
		$wp->query_vars['ap_categories'] = '';
		$this->go_to( '/?ap_page=category&question_category=' . $term->slug );
		$filter_args = [
			'category' => [
				'title'       => 'Question Category',
				'description' => 'Question Description',
			],
		];
		$result = $instance->ap_list_filters( $filter_args );
		$this->assertIsArray( $result );
		$this->assertNotEmpty( $result );
		$this->assertEqualSets( $filter_args, $result );
	}

	public function testSaveImageFieldForImageNotAsAnArray() {
		$instance = new CategoryModule();
		$term_id = $this->factory()->term->create( [ 'taxonomy' => 'question_category' ] );

		// Test.
		$this->setRole( 'administrator' );
		$_REQUEST['ap_category_image_url'] = 'http://example.com/image.jpg';
		$_REQUEST['ap_category_image_id'] = 1;
		$_REQUEST['ap_icon'] = 'apicon-star';
		$_REQUEST['ap_color'] = '#000000';
		update_term_meta( $term_id, 'ap_category', [ 'image' => '' ] );
		$term_meta = get_term_meta( $term_id, 'ap_category', true );
		$this->assertIsString( $term_meta['image'] );
		$instance->save_image_field( $term_id );
		$term_meta = get_term_meta( $term_id, 'ap_category', true );
		$this->assertIsArray( $term_meta['image'] );
	}

	public function testAPCanonicalUrlForNotSettingGlobalQuestionCategoryVariable() {
		$instance = new CategoryModule();

		// Test.
		$term_id = $this->factory()->term->create( [ 'taxonomy' => 'question_category' ] );
		$term = get_term_by( 'id', $term_id, 'question_category' );
		$this->go_to( '/?ap_page=category&question_category=' . $term->slug );
		global $question_category;
		$question_category = null;
		$result = $instance->ap_canonical_url( '' );
		$this->assertEquals( get_term_link( $term_id ), $result );
	}
}
