<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestWidgetQuestions extends TestCase {

	public function testWidgetsInit() {
		do_action( 'widgets_init' );
		$this->assertEquals( 10, has_action( 'widgets_init', 'ap_questions_register_widgets' ) );
		$this->assertTrue( class_exists( 'AP_Questions_Widget' ) );
		ap_questions_register_widgets();
		$this->assertTrue( array_key_exists( 'AP_Questions_Widget', $GLOBALS['wp_widget_factory']->widgets ) );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AP_Questions_Widget', '__construct' ) );
		$this->assertTrue( method_exists( 'AP_Questions_Widget', 'widget' ) );
		$this->assertTrue( method_exists( 'AP_Questions_Widget', 'form' ) );
		$this->assertTrue( method_exists( 'AP_Questions_Widget', 'update' ) );
	}

	/**
	 * @covers AP_Questions_Widget::__construct
	 */
	public function testConstruct() {
		$instance = new \AP_Questions_Widget();
		$this->assertEquals( strtolower( 'ap_questions_widget' ), $instance->id_base );
		$this->assertEquals( '(AnsPress) Questions', $instance->name );
		$this->assertEquals( 'Shows list of question shorted by option.', $instance->widget_options['description'] );
	}

	/**
	 * @covers AP_Questions_Widget::update
	 */
	public function testUpdate() {
		$instance = new \AP_Questions_Widget();

		// Test.
		$new_instance = [
			'title'        => 'Test title',
			'avatar'       => '50',
			'order_by'     => 'newest',
			'limit'        => 10,
			'category_ids' => '1,2,3',
		];
		$old_instance = [
			'title'        => 'Old title',
			'avatar'       => '48',
			'order_by'     => 'newest',
			'limit'        => 5,
			'category_ids' => '4,5,6',
		];
		$expected = [
			'title'        => 'Test title',
			'avatar'       => '50',
			'order_by'     => 'newest',
			'limit'        => 10,
			'category_ids' => '1,2,3',
		];
		$this->assertEquals( $expected, $instance->update( $new_instance, $old_instance ) );
	}

	/**
	 * @covers AP_Questions_Widget::update
	 */
	public function testUpdateHTMLTagsOnTitle() {
		$instance = new \AP_Questions_Widget();

		// Test.
		$new_instance = [
			'title'        => '<h1 class="widget-title">Test title</h1>',
			'avatar'       => '50',
			'order_by'     => 'voted',
			'limit'        => '',
			'category_ids' => '1,2,3',
		];
		$old_instance = [
			'title'        => 'Old title',
			'avatar'       => '48',
			'order_by'     => 'newest',
			'limit'        => 10,
			'category_ids' => '4,5,6',
		];
		$expected = [
			'title'        => 'Test title',
			'avatar'       => '50',
			'order_by'     => 'voted',
			'limit'        => 5,
			'category_ids' => '1,2,3',
		];
		$this->assertEquals( $expected, $instance->update( $new_instance, $old_instance ) );
	}

	/**
	 * @covers AP_Questions_Widget::update
	 */
	public function testUpdateHTMLTagsOnAllOptions() {
		$instance = new \AP_Questions_Widget();

		// Test.
		$new_instance = [
			'title'        => '<strong>Test title</strong>',
			'avatar'       => '<div></div>',
			'order_by'     => 'voted',
			'limit'        => '<div>10</div>',
			'category_ids' => '<span>1</span>,2,<span>3</span>',
		];
		$old_instance = [
			'title'        => 'Old title',
			'avatar'       => '48',
			'order_by'     => 'newest',
			'limit'        => 5,
			'category_ids' => '4,5,6',
		];
		$expected = [
			'title'        => 'Test title',
			'avatar'       => '',
			'order_by'     => 'voted',
			'limit'        => 10,
			'category_ids' => '1,2,3',
		];
		$this->assertEquals( $expected, $instance->update( $new_instance, $old_instance ) );
	}

	/**
	 * @covers AP_Questions_Widget::update
	 */
	public function testUpdateEmptyTitle() {
		$instance = new \AP_Questions_Widget();

		// Test.
		$new_instance = [
			'title'        => '',
			'avatar'       => '50',
			'order_by'     => 'voted',
			'limit'        => 7,
			'category_ids' => '',
		];
		$old_instance = [
			'title'        => 'Old title',
			'avatar'       => '48',
			'order_by'     => 'newest',
			'limit'        => 5,
			'category_ids' => '4,5,6',
		];
		$expected = [
			'title'        => '',
			'avatar'       => '50',
			'order_by'     => 'voted',
			'limit'        => 7,
			'category_ids' => '',
		];
		$this->assertEquals( $expected, $instance->update( $new_instance, $old_instance ) );
	}

	/**
	 * @covers AP_Questions_Widget::form
	 */
	public function testForm() {
		$instance = new \AP_Questions_Widget();

		// Register taxonomy.
		register_taxonomy( 'question_category', 'question' );

		// Test.
		$instance_data = [
			'title'        => 'Test title',
			'order_by'     => 'newest',
			'limit'        => 10,
			'category_ids' => '1,2,3',
		];
		ob_start();
		$instance->form( $instance_data );
		$result = ob_get_clean();

		// For title.
		$this->assertStringContainsString( 'Title:', $result );
		$this->assertStringContainsString( '[][title]', $result );
		$this->assertStringContainsString( 'value="Test title"', $result );

		// For order_by.
		$this->assertStringContainsString( 'Order by:', $result );
		$this->assertStringContainsString( '[][order_by]', $result );
		$this->assertStringContainsString( 'selected=\'selected\' value="newest"', $result );
		$this->assertStringContainsString( 'value="active"', $result );
		$this->assertStringContainsString( 'value="voted"', $result );
		$this->assertStringContainsString( 'value="answers"', $result );
		$this->assertStringContainsString( 'value="unanswered"', $result );

		// For category_ids.
		$this->assertStringContainsString( 'Category IDs:', $result );
		$this->assertStringContainsString( '[][category_ids]', $result );
		$this->assertStringContainsString( 'value="1,2,3"', $result );
		$this->assertStringContainsString( 'Comma separated AnsPress category ids', $result );

		// For limit.
		$this->assertStringContainsString( 'Limit:', $result );
		$this->assertStringContainsString( '[][limit]', $result );
		$this->assertStringContainsString( 'value="10"', $result );

		// Unregister taxonomy.
		unregister_taxonomy( 'question_category' );
	}

	/**
	 * @covers AP_Questions_Widget::form
	 */
	public function testFormWithoutCategory() {
		$instance = new \AP_Questions_Widget();

		// Test.
		$instance_data = [
			'title'        => 'Category title',
			'order_by'     => 'voted',
			'limit'        => 3,
			'category_ids' => '1,2,3',
		];
		ob_start();
		$instance->form( $instance_data );
		$result = ob_get_clean();

		// For title.
		$this->assertStringContainsString( 'Title:', $result );
		$this->assertStringContainsString( '[][title]', $result );
		$this->assertStringContainsString( 'value="Category title"', $result );

		// For order_by.
		$this->assertStringContainsString( 'Order by:', $result );
		$this->assertStringContainsString( '[][order_by]', $result );
		$this->assertStringContainsString( 'selected=\'selected\' value="voted"', $result );
		$this->assertStringContainsString( 'value="active"', $result );
		$this->assertStringContainsString( 'value="newest"', $result );
		$this->assertStringContainsString( 'value="answers"', $result );
		$this->assertStringContainsString( 'value="unanswered"', $result );

		// For limit.
		$this->assertStringContainsString( 'Limit:', $result );
		$this->assertStringContainsString( '[][limit]', $result );
		$this->assertStringContainsString( 'value="3"', $result );

		// For category_ids.
		$this->assertStringNotContainsString( 'Category IDs:', $result );
		$this->assertStringNotContainsString( '[][category_ids]', $result );
		$this->assertStringNotContainsString( 'value="1,2,3"', $result );
		$this->assertStringNotContainsString( 'Comma separated AnsPress category ids', $result );
	}

	/**
	 * @covers AP_Questions_Widget::form
	 */
	public function testFormWithDefaultValues() {
		$instance = new \AP_Questions_Widget();

		// Register taxonomy.
		register_taxonomy( 'question_category', 'question' );

		// Test.
		$instance_data = [];
		ob_start();
		$instance->form( $instance_data );
		$result = ob_get_clean();

		// For title.
		$this->assertStringContainsString( 'Title:', $result );
		$this->assertStringContainsString( '[][title]', $result );
		$this->assertStringContainsString( 'value="Questions"', $result );

		// For order_by.
		$this->assertStringContainsString( 'Order by:', $result );
		$this->assertStringContainsString( '[][order_by]', $result );
		$this->assertStringContainsString( 'selected=\'selected\' value="active"', $result );
		$this->assertStringContainsString( 'value="newest"', $result );
		$this->assertStringContainsString( 'value="voted"', $result );
		$this->assertStringContainsString( 'value="answers"', $result );
		$this->assertStringContainsString( 'value="unanswered"', $result );

		// For category_ids.
		$this->assertStringContainsString( 'Category IDs:', $result );
		$this->assertStringContainsString( '[][category_ids]', $result );
		$this->assertStringContainsString( 'value=""', $result );
		$this->assertStringContainsString( 'Comma separated AnsPress category ids', $result );

		// For limit.
		$this->assertStringContainsString( 'Limit:', $result );
		$this->assertStringContainsString( '[][limit]', $result );
		$this->assertStringContainsString( 'value="5"', $result );

		// Unregister taxonomy.
		unregister_taxonomy( 'question_category' );
	}

	/**
	 * @covers AP_Questions_Widget::widget
	 */
	public function testWidget() {
		$instance = new \AP_Questions_Widget();

		// Register taxonomy.
		register_taxonomy( 'question_category', 'question' );

		// Create some question and assign them to categories.
		$question_id_1 = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'Question title 1' ] );
		$question_id_2 = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'Question title 2' ] );
		$question_id_3 = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'Question title 3' ] );
		$category_id_1 = $this->factory()->term->create( [ 'taxonomy' => 'question_category', 'name' => 'Category 1' ] );
		$category_id_2 = $this->factory()->term->create( [ 'taxonomy' => 'question_category', 'name' => 'Category 2' ] );
		$category_id_3 = $this->factory()->term->create( [ 'taxonomy' => 'question_category', 'name' => 'Category 3' ] );
		wp_set_post_terms( $question_id_1, [ $category_id_1, $category_id_2 ], 'question_category' );
		wp_set_post_terms( $question_id_2, [ $category_id_2 ], 'question_category' );
		wp_set_post_terms( $question_id_3, [ $category_id_3 ], 'question_category' );
		$category_ids = implode( ',', [ $category_id_1, $category_id_2 ] );

		// Test.
		$args = [
			'before_widget' => '<section class="widget">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		];
		$instance_data = [
			'title'        => 'Test title',
			'order_by'     => 'voted',
			'limit'        => 3,
			'category_ids' => $category_ids,
		];
		ob_start();
		$instance->widget( $args, $instance_data );
		$result = ob_get_clean();

		// Tests.
		$this->assertStringContainsString( '<section class="widget">', $result );
		$this->assertStringContainsString( '<h2 class="widget-title">', $result );
		$this->assertStringContainsString( 'Test title', $result );
		$this->assertStringContainsString( '<div class="ap-widget-inner">', $result );
		$this->assertStringContainsString( '<div class="ap-questions-widget clearfix">', $result );
		$this->assertStringContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink( $question_id_1 ) ) . '">Question title 1</a>', $result );
		$this->assertStringContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink( $question_id_2 ) ) . '">Question title 2</a>', $result );
		$this->assertStringNotContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink( $question_id_3 ) ) . '">Question title 3</a>', $result );

		// Unregister taxonomy.
		unregister_taxonomy( 'question_category' );
	}

	/**
	 * @covers AP_Questions_Widget::widget
	 */
	public function testWidgetWithoutCategory() {
		$instance = new \AP_Questions_Widget();

		// Create some questions.
		$question_id_1 = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'Question title 1' ] );
		$question_id_2 = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'Question title 2' ] );
		$question_id_3 = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'Question title 3' ] );
		$question_id_4 = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'Question title 4' ] );
		$question_id_5 = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'Question title 5' ] );

		// Test.
		$args = [
			'before_widget' => '<section class="widget">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		];
		$instance_data = [
			'title'        => 'Test title',
			'order_by'     => 'voted',
			'limit'        => 5,
			'category_ids' => '',
		];
		ob_start();
		$instance->widget( $args, $instance_data );
		$result = ob_get_clean();

		// Tests.
		$this->assertStringContainsString( '<section class="widget">', $result );
		$this->assertStringContainsString( '<h2 class="widget-title">', $result );
		$this->assertStringContainsString( 'Test title', $result );
		$this->assertStringContainsString( '<div class="ap-widget-inner">', $result );
		$this->assertStringContainsString( '<div class="ap-questions-widget clearfix">', $result );
		$this->assertStringContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink( $question_id_1 ) ) . '">Question title 1</a>', $result );
		$this->assertStringContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink( $question_id_2 ) ) . '">Question title 2</a>', $result );
		$this->assertStringContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink( $question_id_3 ) ) . '">Question title 3</a>', $result );
		$this->assertStringContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink( $question_id_4 ) ) . '">Question title 4</a>', $result );
		$this->assertStringContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink( $question_id_5 ) ) . '">Question title 5</a>', $result );
	}

	/**
	 * @covers AP_Questions_Widget::widget
	 */
	public function testWidgetWithoutCategoryButCategoryPassed() {
		$instance = new \AP_Questions_Widget();

		// Create some questions.
		$question_id_1 = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'Question title 1' ] );
		$question_id_2 = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'Question title 2' ] );
		$question_id_3 = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'Question title 3' ] );

		// Test.
		$args = [
			'before_widget' => '<section class="widget">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		];
		$instance_data = [
			'title'        => 'Test title',
			'order_by'     => 'voted',
			'limit'        => 3,
			'category_ids' => '1,2,3',
		];
		ob_start();
		$instance->widget( $args, $instance_data );
		$result = ob_get_clean();

		// Tests.
		$this->assertStringContainsString( '<section class="widget">', $result );
		$this->assertStringContainsString( '<h2 class="widget-title">', $result );
		$this->assertStringContainsString( 'Test title', $result );
		$this->assertStringContainsString( '<div class="ap-widget-inner">', $result );
		$this->assertStringContainsString( '<div class="ap-questions-widget clearfix">', $result );
		$this->assertStringContainsString( 'No questions found.', $result );
		$this->assertStringNotContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink( $question_id_1 ) ) . '">Question title 1</a>', $result );
		$this->assertStringNotContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink( $question_id_2 ) ) . '">Question title 2</a>', $result );
		$this->assertStringNotContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink( $question_id_3 ) ) . '">Question title 3</a>', $result );
	}

	/**
	 * @covers AP_Questions_Widget::widget
	 */
	public function testWidgetWithLimitSetToOnlyOneAndAnswersAsOrderBy() {
		$instance = new \AP_Questions_Widget();

		// Register taxonomy.
		register_taxonomy( 'question_category', 'question' );

		// Create some question and assign them to categories.
		$question_id_1 = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'Question title 1' ] );
		$question_id_2 = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'Question title 2' ] );
		$question_id_3 = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'Question title 3' ] );
		$category_id_1 = $this->factory()->term->create( [ 'taxonomy' => 'question_category', 'name' => 'Category 1' ] );
		$category_id_2 = $this->factory()->term->create( [ 'taxonomy' => 'question_category', 'name' => 'Category 2' ] );
		$category_id_3 = $this->factory()->term->create( [ 'taxonomy' => 'question_category', 'name' => 'Category 3' ] );
		wp_set_post_terms( $question_id_1, [ $category_id_1, $category_id_2 ], 'question_category' );
		wp_set_post_terms( $question_id_2, [ $category_id_2 ], 'question_category' );
		wp_set_post_terms( $question_id_3, [ $category_id_3 ], 'question_category' );
		$category_ids = implode( ',', [ $category_id_1, $category_id_2, $category_id_3 ] );
		$answer_id_1 = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_title' => 'Answer title 3', 'post_parent' => $question_id_3 ] );

		// Test.
		$args = [
			'before_widget' => '<section class="widget">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		];
		$instance_data = [
			'title'        => 'Test title',
			'order_by'     => 'answers',
			'limit'        => 1,
			'category_ids' => $category_ids,
		];
		ob_start();
		$instance->widget( $args, $instance_data );
		$result = ob_get_clean();

		// Tests.
		$this->assertStringContainsString( '<section class="widget">', $result );
		$this->assertStringContainsString( '<h2 class="widget-title">', $result );
		$this->assertStringContainsString( 'Test title', $result );
		$this->assertStringContainsString( '<div class="ap-widget-inner">', $result );
		$this->assertStringContainsString( '<div class="ap-questions-widget clearfix">', $result );
		$this->assertStringContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink( $question_id_3 ) ) . '">Question title 3</a>', $result );
		$this->assertStringNotContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink( $question_id_2 ) ) . '">Question title 2</a>', $result );
		$this->assertStringNotContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink( $question_id_1 ) ) . '">Question title 1</a>', $result );

		// Unregister taxonomy.
		unregister_taxonomy( 'question_category', 'question' );
	}

	/**
	 * @covers AP_Questions_Widget::widget
	 */
	public function testWidgetWithEmptyTitleAndNoQuestions() {
		$instance = new \AP_Questions_Widget();

		// Test.
		$args = [
			'before_widget' => '<section class="widget">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		];
		$instance_data = [
			'title'        => '',
			'order_by'     => 'answers',
			'limit'        => 10,
			'category_ids' => '',
		];
		ob_start();
		$instance->widget( $args, $instance_data );
		$result = ob_get_clean();

		// Tests.
		$this->assertStringContainsString( '<section class="widget">', $result );
		$this->assertStringNotContainsString( '<h2 class="widget-title">', $result );
		$this->assertStringContainsString( '<div class="ap-widget-inner">', $result );
		$this->assertStringContainsString( '<div class="ap-questions-widget clearfix">', $result );
		$this->assertStringContainsString( 'No questions found.', $result );
	}

	/**
	 * @covers AP_Questions_Widget::widget
	 */
	public function testWidgetWithDefaultValues() {
		$instance = new \AP_Questions_Widget();

		// Register taxonomy.
		register_taxonomy( 'question_category', 'question' );

		// Create some question and assign them to categories.
		$question_id_1 = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'Question title 1', 'post_date' => '2024-01-01 00:00:00' ] );
		$question_id_2 = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'Question title 2', 'post_date' => '2024-01-02 00:00:00' ] );
		$question_id_3 = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'Question title 3', 'post_date' => '2024-01-03 00:00:00' ] );
		$question_id_4 = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'Question title 4', 'post_date' => '2024-01-04 00:00:00' ] );
		$question_id_5 = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'Question title 5', 'post_date' => '2024-01-05 00:00:00' ] );
		$question_id_6 = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'Question title 6', 'post_date' => '2024-01-06 00:00:00' ] );
		$question_id_7 = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'Question title 7', 'post_date' => '2024-01-07 00:00:00' ] );
		$category_id_1 = $this->factory()->term->create( [ 'taxonomy' => 'question_category', 'name' => 'Category 1' ] );
		$category_id_2 = $this->factory()->term->create( [ 'taxonomy' => 'question_category', 'name' => 'Category 2' ] );
		$category_id_3 = $this->factory()->term->create( [ 'taxonomy' => 'question_category', 'name' => 'Category 3' ] );
		wp_set_post_terms( $question_id_1, [ $category_id_1, $category_id_2 ], 'question_category' );
		wp_set_post_terms( $question_id_2, [ $category_id_2 ], 'question_category' );
		wp_set_post_terms( $question_id_3, [ $category_id_3 ], 'question_category' );
		$category_ids = implode( ',', [ $category_id_1, $category_id_2, $category_id_3 ] );

		// Test.
		$args = [
			'before_widget' => '<section class="widget">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
			'limit'         => 10,
		];
		$instance_data = [];
		ob_start();
		$instance->widget( $args, $instance_data );
		$result = ob_get_clean();

		// Tests.
		$this->assertStringContainsString( '<section class="widget">', $result );
		$this->assertStringContainsString( '<h2 class="widget-title">', $result );
		$this->assertStringContainsString( 'Questions', $result );
		$this->assertStringContainsString( '<div class="ap-widget-inner">', $result );
		$this->assertStringContainsString( '<div class="ap-questions-widget clearfix">', $result );
		$this->assertStringContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink( $question_id_1 ) ) . '">Question title 1</a>', $result );
		$this->assertStringContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink( $question_id_2 ) ) . '">Question title 2</a>', $result );
		$this->assertStringContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink( $question_id_3 ) ) . '">Question title 3</a>', $result );
		$this->assertStringContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink( $question_id_4 ) ) . '">Question title 4</a>', $result );
		$this->assertStringContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink( $question_id_5 ) ) . '">Question title 5</a>', $result );
		$this->assertStringNotContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink( $question_id_6 ) ) . '">Question title 6</a>', $result );
		$this->assertStringNotContainsString( '<a class="ap-question-title" href="' . esc_url( get_permalink( $question_id_7 ) ) . '">Question title 7</a>', $result );

		// Unregister taxonomy.
		unregister_taxonomy( 'question_category', 'question' );
	}
}
