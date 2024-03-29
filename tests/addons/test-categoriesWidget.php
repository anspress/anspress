<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAddonCategoriesWidget extends TestCase {

	public function set_up() {
		parent::set_up();
		ap_activate_addon( 'categories.php' );
		$this->assertTrue(ap_is_addon_active('categories.php'));
	}

	public function testWidgetsInit() {
		$instance = \Anspress\Addons\Categories::init();
		anspress()->setup_hooks();
		$this->assertEquals( 10, has_action( 'widgets_init', [ $instance, 'widget' ] ) );
		do_action('widgets_init');

		$this->assertTrue( class_exists( 'Anspress\Widgets\Categories' ) );
		$this->assertTrue( array_key_exists( 'Anspress\Widgets\Categories', $GLOBALS['wp_widget_factory']->widgets ) );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'Anspress\Widgets\Categories', '__construct' ) );
		$this->assertTrue( method_exists( 'Anspress\Widgets\Categories', 'widget' ) );
		$this->assertTrue( method_exists( 'Anspress\Widgets\Categories', 'form' ) );
		$this->assertTrue( method_exists( 'Anspress\Widgets\Categories', 'update' ) );
	}

	/**
	 * @covers Anspress\Widgets\Categories::__construct
	 */
	public function testConstruct() {
		\Anspress\Addons\Categories::init();
		anspress()->setup_hooks();

		do_action('widgets_init');

		$instance = new \Anspress\Widgets\Categories();

		$this->assertEquals( strtolower( 'AnsPress_Category_Widget' ), $instance->id_base );
		$this->assertEquals( '(AnsPress) Categories', $instance->name );
		$this->assertEquals( 'Display AnsPress categories', $instance->widget_options['description'] );
	}

	public function testupdate() {
		\Anspress\Addons\Categories::init();
		anspress()->setup_hooks();

		do_action('widgets_init');

		$instance = new \Anspress\Widgets\Categories();

		// Test.
		$new_instance = [
			'title'       => 'Test title',
			'hide_empty'  => true,
			'parent'      => '3',
			'number'      => '10',
			'orderby'     => 'none',
			'order'       => 'DESC',
			'icon_width'  => 48,
			'icon_height' => 48,
		];
		$old_instance = [
			'title'       => 'Old title',
			'hide_empty'  => false,
			'parent'      => '',
			'number'      => '10',
			'orderby'     => 'id',
			'order'       => 'ASC',
			'icon_width'  => 30,
			'icon_height' => 30,
		];
		$expected = [
			'title'       => 'Test title',
			'hide_empty'  => true,
			'parent'      => '3',
			'number'      => '10',
			'orderby'     => 'none',
			'order'       => 'DESC',
			'icon_width'  => 48,
			'icon_height' => 48,
		];
		$this->assertEquals( $expected, $instance->update( $new_instance, $old_instance ) );
	}

	public function testUpdateHTMLTagsOnTitle() {
		\Anspress\Addons\Categories::init();
		anspress()->setup_hooks();

		do_action('widgets_init');

		$instance = new \Anspress\Widgets\Categories();

		// Test.
		$new_instance = [
			'title'       => '<h1 class="widget-title">Test title</h1>',
			'hide_empty'  => true,
			'parent'      => '3',
			'number'      => '10',
			'orderby'     => 'name',
			'order'       => 'ASC',
			'icon_width'  => 64,
			'icon_height' => 64,
		];
		$old_instance = [
			'title'       => 'Old title',
			'hide_empty'  => false,
			'parent'      => '',
			'number'      => '10',
			'orderby'     => 'slug',
			'order'       => 'ASC',
			'icon_width'  => 30,
			'icon_height' => 30,
		];
		$expected = [
			'title'       => 'Test title',
			'hide_empty'  => '1',
			'parent'      => '3',
			'number'      => '10',
			'orderby'     => 'name',
			'order'       => 'ASC',
			'icon_width'  => 64,
			'icon_height' => 64,
		];
		$this->assertEquals( $expected, $instance->update( $new_instance, $old_instance ) );
	}

	public function testUpdateWithEmptyOptions() {
		\Anspress\Addons\Categories::init();
		anspress()->setup_hooks();

		do_action('widgets_init');

		$instance = new \Anspress\Widgets\Categories();

		// Test.
		$new_instance = [
			'title'       => '',
			'hide_empty'  => '',
			'parent'      => '',
			'number'      => '',
			'orderby'     => '',
			'order'       => '',
			'icon_width'  => '',
			'icon_height' => '',
		];
		$old_instance = [
			'title'       => 'Old title',
			'hide_empty'  => true,
			'parent'      => '3',
			'number'      => '10',
			'orderby'     => 'name',
			'order'       => 'ASC',
			'icon_width'  => 64,
			'icon_height' => 64,
		];
		$expected = [
			'title'       => '',
			'hide_empty'  => false,
			'parent'      => '0',
			'number'      => '5',
			'orderby'     => 'count',
			'order'       => 'DESC',
			'icon_width'  => 32,
			'icon_height' => 32,
		];
		$this->assertEquals( $expected, $instance->update( $new_instance, $old_instance ) );
	}

	public function testUpdateHTMLTagsOnTitleWithEmptyTitle() {
		$instance = new \Anspress\Widgets\Categories();

		// Test.
		$new_instance = [
			'title'       => '<strong></strong>',
			'hide_empty'  => true,
			'parent'      => '3',
			'number'      => '10',
			'orderby'     => 'name',
			'order'       => 'ASC',
			'icon_width'  => 64,
			'icon_height' => 64,
		];
		$old_instance = [
			'title'       => 'Old title',
			'hide_empty'  => false,
			'parent'      => '',
			'number'      => '10',
			'orderby'     => 'slug',
			'order'       => 'ASC',
			'icon_width'  => 30,
			'icon_height' => 30,
		];
		$expected = [
			'title'       => '',
			'hide_empty'  => '1',
			'parent'      => '3',
			'number'      => '10',
			'orderby'     => 'name',
			'order'       => 'ASC',
			'icon_width'  => 64,
			'icon_height' => 64,
		];
		$this->assertEquals( $expected, $instance->update( $new_instance, $old_instance ) );
	}

	/**
	 * @covers Anspress\Widgets\Categories::form
	 */
	public function testForm() {
		$instance = new \Anspress\Widgets\Categories();

		// Create some categories.
		$category_id_1 = $this->factory()->term->create( [ 'name' => 'Question Category 1', 'taxonomy' => 'question_category' ] );
		$category_id_2 = $this->factory()->term->create( [ 'name' => 'Question Category 2', 'taxonomy' => 'question_category' ] );
		$category_id_3 = $this->factory()->term->create( [ 'name' => 'Question Category 3', 'taxonomy' => 'question_category' ] );

		// Test.
		$instance_data = [
			'title'       => 'Test Title',
			'hide_empty'  => true,
			'parent'      => $category_id_3,
			'number'      => '5',
			'orderby'     => 'none',
			'order'       => 'ASC',
			'icon_width'  => 48,
			'icon_height' => 48,
		];
		ob_start();
		$instance->form( $instance_data );
		$form = ob_get_clean();

		// For title.
		$this->assertStringContainsString( 'Title:', $form );
		$this->assertStringContainsString( '[][title]', $form );
		$this->assertStringContainsString( 'value="Test Title"', $form );

		// For hide_empty.
		$this->assertStringContainsString( 'Hide empty:', $form );
		$this->assertStringContainsString( '[][hide_empty]', $form );
		$this->assertStringContainsString( 'value="1"  checked=\'checked\'', $form );

		// For parent.
		$this->assertStringContainsString( 'Parent:', $form );
		$this->assertStringContainsString( '[][parent]', $form );
		$this->assertStringContainsString( 'value="0"', $form );
		$this->assertStringContainsString( 'value="' . $category_id_1 . '"', $form );
		$this->assertStringContainsString( 'value="' . $category_id_2 . '"', $form );
		$this->assertStringContainsString( 'value="' . $category_id_3 . '"  selected=\'selected\'', $form );

		// For number.
		$this->assertStringContainsString( 'Number:', $form );
		$this->assertStringContainsString( '[][number]', $form );
		$this->assertStringContainsString( 'value="5"', $form );

		// For orderby.
		$this->assertStringContainsString( 'Order By:', $form );
		$this->assertStringContainsString( '[][orderby]', $form );
		$this->assertStringContainsString( 'value="none"  selected=\'selected\'', $form );
		$this->assertStringContainsString( 'value="count"', $form );
		$this->assertStringContainsString( 'value="id"', $form );
		$this->assertStringContainsString( 'value="name"', $form );
		$this->assertStringContainsString( 'value="slug"', $form );
		$this->assertStringContainsString( 'value="term_group"', $form );

		// For order.
		$this->assertStringContainsString( 'Order:', $form );
		$this->assertStringContainsString( '[][order]', $form );
		$this->assertStringContainsString( 'value="ASC"  selected=\'selected\'', $form );
		$this->assertStringContainsString( 'value="DESC"', $form );

		// For icon_width.
		$this->assertStringContainsString( 'Icon width:', $form );
		$this->assertStringContainsString( '[][icon_width]', $form );
		$this->assertStringContainsString( 'value="48"', $form );

		// For icon_height.
		$this->assertStringContainsString( 'Icon height:', $form );
		$this->assertStringContainsString( '[][icon_height]', $form );
		$this->assertStringContainsString( 'value="48"', $form );
	}

	/**
	 * @covers Anspress\Widgets\Categories::form
	 */
	public function testFormWithDefaultValues() {
		$instance = new \Anspress\Widgets\Categories();

		// Create some categories.
		$category_id_1 = $this->factory()->term->create( [ 'name' => 'Question Category 1', 'taxonomy' => 'question_category' ] );
		$category_id_2 = $this->factory()->term->create( [ 'name' => 'Question Category 2', 'taxonomy' => 'question_category' ] );
		$category_id_3 = $this->factory()->term->create( [ 'name' => 'Question Category 3', 'taxonomy' => 'question_category' ] );

		// Test.
		$instance_data = [];
		ob_start();
		$instance->form( $instance_data );
		$form = ob_get_clean();

		// For title.
		$this->assertStringContainsString( 'Title:', $form );
		$this->assertStringContainsString( '[][title]', $form );
		$this->assertStringContainsString( 'value="Categories"', $form );

		// For hide_empty.
		$this->assertStringContainsString( 'Hide empty:', $form );
		$this->assertStringContainsString( '[][hide_empty]', $form );
		$this->assertStringContainsString( 'value="1" ', $form );
		$this->assertStringNotContainsString( 'value="1"  checked=\'checked\'', $form );

		// For parent.
		$this->assertStringContainsString( 'Parent:', $form );
		$this->assertStringContainsString( '[][parent]', $form );
		$this->assertStringContainsString( 'value="0"', $form );
		$this->assertStringNotContainsString( 'value="0"  selected=\'selected\'', $form );
		$this->assertStringContainsString( 'value="' . $category_id_1 . '"', $form );
		$this->assertStringContainsString( 'value="' . $category_id_2 . '"', $form );
		$this->assertStringContainsString( 'value="' . $category_id_3 . '"', $form );

		// For number.
		$this->assertStringContainsString( 'Number:', $form );
		$this->assertStringContainsString( '[][number]', $form );
		$this->assertStringContainsString( 'value="10"', $form );

		// For orderby.
		$this->assertStringContainsString( 'Order By:', $form );
		$this->assertStringContainsString( '[][orderby]', $form );
		$this->assertStringContainsString( 'value="count"  selected=\'selected\'', $form );
		$this->assertStringContainsString( 'value="id"', $form );
		$this->assertStringContainsString( 'value="name"', $form );
		$this->assertStringContainsString( 'value="slug"', $form );
		$this->assertStringContainsString( 'value="term_group"', $form );

		// For order.
		$this->assertStringContainsString( 'Order:', $form );
		$this->assertStringContainsString( '[][order]', $form );
		$this->assertStringContainsString( 'value="ASC"', $form );
		$this->assertStringContainsString( 'value="DESC"  selected=\'selected\'', $form );

		// For icon_width.
		$this->assertStringContainsString( 'Icon width:', $form );
		$this->assertStringContainsString( '[][icon_width]', $form );
		$this->assertStringContainsString( 'value="32"', $form );

		// For icon_height.
		$this->assertStringContainsString( 'Icon height:', $form );
		$this->assertStringContainsString( '[][icon_height]', $form );
		$this->assertStringContainsString( 'value="32"', $form );
	}

	/**
	 * @covers Anspress\Widgets\Categories::form
	 */
	public function testFormWithEmptyValues() {
		$instance = new \Anspress\Widgets\Categories();

		// Create some categories.
		$category_id_1 = $this->factory()->term->create( [ 'name' => 'Question Category 1', 'taxonomy' => 'question_category' ] );
		$category_id_2 = $this->factory()->term->create( [ 'name' => 'Question Category 2', 'taxonomy' => 'question_category' ] );
		$category_id_3 = $this->factory()->term->create( [ 'name' => 'Question Category 3', 'taxonomy' => 'question_category' ] );

		// Test.
		$instance_data = [
			'title'       => '',
			'hide_empty'  => '',
			'parent'      => '',
			'number'      => '',
			'orderby'     => '',
			'order'       => '',
			'icon_width'  => '',
			'icon_height' => '',
		];
		ob_start();
		$instance->form( $instance_data );
		$form = ob_get_clean();

		// For title.
		$this->assertStringContainsString( 'Title:', $form );
		$this->assertStringContainsString( '[][title]', $form );
		$this->assertStringContainsString( 'value="Categories"', $form );

		// For hide_empty.
		$this->assertStringContainsString( 'Hide empty:', $form );
		$this->assertStringContainsString( '[][hide_empty]', $form );
		$this->assertStringContainsString( 'value="1" ', $form );
		$this->assertStringNotContainsString( 'value="1"  checked=\'checked\'', $form );

		// For parent.
		$this->assertStringContainsString( 'Parent:', $form );
		$this->assertStringContainsString( '[][parent]', $form );
		$this->assertStringContainsString( 'value="0"', $form );
		$this->assertStringNotContainsString( 'value="0"  selected=\'selected\'', $form );
		$this->assertStringContainsString( 'value="' . $category_id_1 . '"', $form );
		$this->assertStringContainsString( 'value="' . $category_id_2 . '"', $form );
		$this->assertStringContainsString( 'value="' . $category_id_3 . '"', $form );

		// For number.
		$this->assertStringContainsString( 'Number:', $form );
		$this->assertStringContainsString( '[][number]', $form );
		$this->assertStringContainsString( 'value="10"', $form );

		// For orderby.
		$this->assertStringContainsString( 'Order By:', $form );
		$this->assertStringContainsString( '[][orderby]', $form );
		$this->assertStringContainsString( 'value="count"  selected=\'selected\'', $form );
		$this->assertStringContainsString( 'value="id"', $form );
		$this->assertStringContainsString( 'value="name"', $form );
		$this->assertStringContainsString( 'value="slug"', $form );
		$this->assertStringContainsString( 'value="term_group"', $form );

		// For order.
		$this->assertStringContainsString( 'Order:', $form );
		$this->assertStringContainsString( '[][order]', $form );
		$this->assertStringContainsString( 'value="ASC"', $form );
		$this->assertStringContainsString( 'value="DESC"  selected=\'selected\'', $form );

		// For icon_width.
		$this->assertStringContainsString( 'Icon width:', $form );
		$this->assertStringContainsString( '[][icon_width]', $form );
		$this->assertStringContainsString( 'value="32"', $form );

		// For icon_height.
		$this->assertStringContainsString( 'Icon height:', $form );
		$this->assertStringContainsString( '[][icon_height]', $form );
		$this->assertStringContainsString( 'value="32"', $form );
	}

	/**
	 * @covers Anspress\Widgets\Categories::widget
	 */
	public function testWidget() {
		$instance = new \Anspress\Widgets\Categories();

		// Create some categories.
		$category_id_1 = $this->factory()->term->create( [ 'name' => 'Question Category 1', 'taxonomy' => 'question_category' ] );
		$category_id_1_1 = $this->factory()->term->create( [ 'name' => 'Question Category 1.1', 'taxonomy' => 'question_category', 'parent' => $category_id_1 ] );
		$category_id_2 = $this->factory()->term->create( [ 'name' => 'Question Category 2', 'taxonomy' => 'question_category' ] );
		$category_id_3 = $this->factory()->term->create( [ 'name' => 'Question Category 3', 'taxonomy' => 'question_category' ] );
		$question_id_1 = $this->factory()->post->create( [ 'title' => 'Question 1', 'post_type' => 'question' ] );
		wp_set_object_terms( $question_id_1, [ $category_id_1, $category_id_2 ], 'question_category' );

		// Test.
		$args = [
			'before_widget' => '<section class="widget">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		];
		$instance_data = [
			'title'       => 'Test Title',
			'hide_empty'  => true,
			'parent'      => '0',
			'number'      => '5',
			'orderby'     => 'none',
			'order'       => 'ASC',
			'icon_width'  => 48,
			'icon_height' => 48,
		];
		ob_start();
		$instance->widget( $args, $instance_data );
		$widget = ob_get_clean();
		$this->assertStringContainsString( '<section class="widget">', $widget );
		$this->assertStringContainsString( '<h2 class="widget-title">', $widget );
		$this->assertStringContainsString( 'Test Title', $widget );
		$this->assertStringContainsString( '<ul id="ap-categories-widget" class="ap-cat-wid clearfix">', $widget );

		// Test for category 1.
		$this->assertStringContainsString( '<a class="ap-cat-image" style="height:48px;width:48px;background: #333" href="' . esc_url( get_category_link( $category_id_1 ) ) . '">', $widget );
		$this->assertStringContainsString( '<span class="ap-category-icon apicon-category"></span>', $widget );
		$this->assertStringContainsString( '<a class="ap-cat-wid-title" href="' . esc_url( get_category_link( $category_id_1 ) ) . '">', $widget );
		$this->assertStringContainsString( 'Question Category 1', $widget );
		$this->assertStringContainsString( '<div class="ap-cat-count">', $widget );
		$this->assertStringContainsString( '1 Question', $widget );
		$this->assertStringContainsString( '1 Child', $widget );

		// Test for category 2.
		$this->assertStringContainsString( '<a class="ap-cat-image" style="height:48px;width:48px;background: #333" href="' . esc_url( get_category_link( $category_id_2 ) ) . '">', $widget );
		$this->assertStringContainsString( '<span class="ap-category-icon apicon-category"></span>', $widget );
		$this->assertStringContainsString( '<a class="ap-cat-wid-title" href="' . esc_url( get_category_link( $category_id_2 ) ) . '">', $widget );
		$this->assertStringContainsString( 'Question Category 2', $widget );
		$this->assertStringContainsString( '<div class="ap-cat-count">', $widget );
		$this->assertStringContainsString( '1 Question', $widget );
		$this->assertStringNotContainsString( '0 Child', $widget );

		// Test for category 3.
		$this->assertStringNotContainsString( '<a class="ap-cat-image" style="height:48px;width:48px;background: #333" href="' . esc_url( get_category_link( $category_id_3 ) ) . '">', $widget );
	}

	/**
	 * @covers Anspress\Widgets\Categories::widget
	 */
	public function testWidgetWithDefaultValues() {
		$instance = new \Anspress\Widgets\Categories();

		// Create some categories.
		$category_id_1 = $this->factory()->term->create( [ 'name' => 'Question Category 1', 'taxonomy' => 'question_category' ] );
		$category_id_1_1 = $this->factory()->term->create( [ 'name' => 'Question Category 1.1', 'taxonomy' => 'question_category', 'parent' => $category_id_1 ] );
		$category_id_1_2 = $this->factory()->term->create( [ 'name' => 'Question Category 1.2', 'taxonomy' => 'question_category', 'parent' => $category_id_1 ] );
		$category_id_1_3 = $this->factory()->term->create( [ 'name' => 'Question Category 1.3', 'taxonomy' => 'question_category', 'parent' => $category_id_1 ] );
		$category_id_2 = $this->factory()->term->create( [ 'name' => 'Question Category 2', 'taxonomy' => 'question_category' ] );
		$category_id_3 = $this->factory()->term->create( [ 'name' => 'Question Category 3', 'taxonomy' => 'question_category' ] );
		$question_id_1 = $this->factory()->post->create( [ 'title' => 'Question 1', 'post_type' => 'question' ] );
		$question_id_2 = $this->factory()->post->create( [ 'title' => 'Question 2', 'post_type' => 'question' ] );
		wp_set_object_terms( $question_id_1, [ $category_id_1, $category_id_2 ], 'question_category' );
		wp_set_object_terms( $question_id_2, [ $category_id_1 ], 'question_category' );

		// Update term meta.
		update_term_meta( $category_id_1, 'ap_category', [
			'color' => '#eaeaea',
			'icon'  => 'apicon-star',
		] );
		update_term_meta( $category_id_3, 'ap_category', [
			'color' => '#ddd',
			'icon'  => '',
		] );

		// Test.
		$args = [
			'before_widget' => '<section class="widget">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		];
		$instance_data = [];
		ob_start();
		$instance->widget( $args, $instance_data );
		$widget = ob_get_clean();
		$this->assertStringContainsString( '<section class="widget">', $widget );
		$this->assertStringContainsString( '<h2 class="widget-title">', $widget );
		$this->assertStringContainsString( 'Categories', $widget );
		$this->assertStringContainsString( '<ul id="ap-categories-widget" class="ap-cat-wid clearfix">', $widget );

		// Test for category 1.
		$this->assertStringContainsString( '<a class="ap-cat-image" style="height:32px;width:32px;background: #eaeaea" href="' . esc_url( get_category_link( $category_id_1 ) ) . '">', $widget );
		$this->assertStringContainsString( '<span class="ap-category-icon apicon-star"></span>', $widget );
		$this->assertStringContainsString( '<a class="ap-cat-wid-title" href="' . esc_url( get_category_link( $category_id_1 ) ) . '">', $widget );
		$this->assertStringContainsString( 'Question Category 1', $widget );
		$this->assertStringContainsString( '<div class="ap-cat-count">', $widget );
		$this->assertStringContainsString( '2 Questions', $widget );
		$this->assertStringContainsString( '3 Child', $widget );

		// Test for category 2.
		$this->assertStringContainsString( '<a class="ap-cat-image" style="height:32px;width:32px;background: #333" href="' . esc_url( get_category_link( $category_id_2 ) ) . '">', $widget );
		$this->assertStringContainsString( '<span class="ap-category-icon apicon-category"></span>', $widget );
		$this->assertStringContainsString( '<a class="ap-cat-wid-title" href="' . esc_url( get_category_link( $category_id_2 ) ) . '">', $widget );
		$this->assertStringContainsString( 'Question Category 2', $widget );
		$this->assertStringContainsString( '<div class="ap-cat-count">', $widget );
		$this->assertStringContainsString( '1 Question', $widget );
		$this->assertStringNotContainsString( '0 Child', $widget );

		// Test for category 3.
		$this->assertStringContainsString( '<a class="ap-cat-image" style="height:32px;width:32px;background: #ddd" href="' . esc_url( get_category_link( $category_id_3 ) ) . '">', $widget );
		$this->assertStringContainsString( '<span class="ap-category-icon "></span>', $widget );
		$this->assertStringContainsString( '<a class="ap-cat-wid-title" href="' . esc_url( get_category_link( $category_id_3 ) ) . '">', $widget );
		$this->assertStringContainsString( 'Question Category 3', $widget );
		$this->assertStringContainsString( '<div class="ap-cat-count">', $widget );
		$this->assertStringContainsString( '0 Questions', $widget );
		$this->assertStringNotContainsString( '0 Child', $widget );
	}

	/**
	 * @covers Anspress\Widgets\Categories::widget
	 */
	public function testWidgetWithPassingParentValue() {
		$instance = new \Anspress\Widgets\Categories();

		// Create some categories.
		$category_id_1 = $this->factory()->term->create( [ 'name' => 'Question Category 1', 'taxonomy' => 'question_category' ] );
		$category_id_1_1 = $this->factory()->term->create( [ 'name' => 'Question Category 1.1', 'taxonomy' => 'question_category', 'parent' => $category_id_1 ] );
		$category_id_1_1_1 = $this->factory()->term->create( [ 'name' => 'Question Category 1.1.1', 'taxonomy' => 'question_category', 'parent' => $category_id_1_1 ] );
		$category_id_1_2 = $this->factory()->term->create( [ 'name' => 'Question Category 1.2', 'taxonomy' => 'question_category', 'parent' => $category_id_1 ] );
		$category_id_2 = $this->factory()->term->create( [ 'name' => 'Question Category 2', 'taxonomy' => 'question_category' ] );
		$question_id_1 = $this->factory()->post->create( [ 'title' => 'Question 1', 'post_type' => 'question' ] );
		wp_set_object_terms( $question_id_1, [ $category_id_1_1 ], 'question_category' );

		// Update term meta.
		update_term_meta( $category_id_1_1, 'ap_category', [
			'color' => '#eaeaea',
			'icon'  => 'apicon-star',
		] );

		// Test.
		$args = [
			'before_widget' => '<section class="widget">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		];
		$instance_data = [
			'title'       => '',
			'hide_empty'  => false,
			'parent'      => $category_id_1,
			'number'      => '3',
			'orderby'     => 'term_group',
			'order'       => 'DESC',
			'icon_width'  => 64,
			'icon_height' => 64,
		];
		ob_start();
		$instance->widget( $args, $instance_data );
		$widget = ob_get_clean();
		$this->assertStringContainsString( '<section class="widget">', $widget );
		$this->assertStringNotContainsString( '<h2 class="widget-title">', $widget );
		$this->assertStringContainsString( '<ul id="ap-categories-widget" class="ap-cat-wid clearfix">', $widget );

		// Test for category 1.
		$this->assertStringNotContainsString( '<a class="ap-cat-image" style="height:48px;width:48px;background: #333" href="' . esc_url( get_category_link( $category_id_1 ) ) . '">', $widget );

		// Test for category 1.1.
		$this->assertStringContainsString( '<a class="ap-cat-image" style="height:64px;width:64px;background: #eaeaea" href="' . esc_url( get_category_link( $category_id_1_1 ) ) . '">', $widget );
		$this->assertStringContainsString( '<span class="ap-category-icon apicon-star"></span>', $widget );
		$this->assertStringContainsString( '<a class="ap-cat-wid-title" href="' . esc_url( get_category_link( $category_id_1_1 ) ) . '">', $widget );
		$this->assertStringContainsString( 'Question Category 1.1', $widget );
		$this->assertStringContainsString( '<div class="ap-cat-count">', $widget );
		$this->assertStringContainsString( '1 Question', $widget );
		$this->assertStringContainsString( '1 Child', $widget );

		// Test for category 1.1.1.
		$this->assertStringNotContainsString( '<a class="ap-cat-image" style="height:64px;width:64px;background: #333" href="' . esc_url( get_category_link( $category_id_1_1_1 ) ) . '">', $widget );

		// Test for category 1.2.
		$this->assertStringContainsString( '<a class="ap-cat-image" style="height:64px;width:64px;background: #333" href="' . esc_url( get_category_link( $category_id_1_2 ) ) . '">', $widget );
		$this->assertStringContainsString( '<span class="ap-category-icon apicon-category"></span>', $widget );
		$this->assertStringContainsString( '<a class="ap-cat-wid-title" href="' . esc_url( get_category_link( $category_id_1_2 ) ) . '">', $widget );
		$this->assertStringContainsString( 'Question Category 1.2', $widget );
		$this->assertStringContainsString( '<div class="ap-cat-count">', $widget );
		$this->assertStringContainsString( '0 Question', $widget );
		$this->assertStringNotContainsString( '0 Child', $widget );

		// Test for category 2.
		$this->assertStringNotContainsString( '<a class="ap-cat-image" style="height:64px;width:64px;background: #333" href="' . esc_url( get_category_link( $category_id_2 ) ) . '">', $widget );
	}
}
