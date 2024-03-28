<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestWidgetQuestionStats extends TestCase {

	public function testWidgetsInit() {
		do_action( 'widgets_init' );
		$this->assertEquals( 10, has_action( 'widgets_init', 'ap_stats_register_widgets' ) );
		$this->assertTrue( class_exists( 'AnsPress_Stats_Widget' ) );
		ap_stats_register_widgets();
		$this->assertTrue( array_key_exists( 'AnsPress_Stats_Widget', $GLOBALS['wp_widget_factory']->widgets ) );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Stats_Widget', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress_Stats_Widget', 'widget' ) );
		$this->assertTrue( method_exists( 'AnsPress_Stats_Widget', 'form' ) );
		$this->assertTrue( method_exists( 'AnsPress_Stats_Widget', 'update' ) );
	}

	/**
	 * @covers AnsPress_Stats_Widget::__construct
	 */
	public function testConstruct() {
		$instance = new \AnsPress_Stats_Widget();
		$this->assertEquals( strtolower( 'ap_stats_widget' ), $instance->id_base );
		$this->assertEquals( '(AnsPress) Question Stats', $instance->name );
		$this->assertEquals( 'Shows question stats in single question page.', $instance->widget_options['description'] );
	}

	/**
	 * @covers AnsPress_Stats_Widget::update
	 */
	public function testUpdate() {
		$instance = new \AnsPress_Stats_Widget();

		// Test.
		$new_instance = [
			'title' => 'Test title',
		];
		$old_instance = [
			'title' => 'Old title',
		];
		$expected = [
			'title' => 'Test title',
		];
		$this->assertEquals( $expected, $instance->update( $new_instance, $old_instance ) );
	}

	/**
	 * @covers AnsPress_Stats_Widget::update
	 */
	public function testUpdateHTMLTagsOnTitle() {
		$instance = new \AnsPress_Stats_Widget();

		// Test.
		$new_instance = [
			'title' => '<h1 class="widget-title">Test title</h1>',
		];
		$old_instance = [
			'title' => 'Old title',
		];
		$expected = [
			'title' => 'Test title',
		];
		$this->assertEquals( $expected, $instance->update( $new_instance, $old_instance ) );
	}

	/**
	 * @covers AnsPress_Stats_Widget::update
	 */
	public function testUpdateHTMLTagsOnTitleWithEmptyTitle() {
		$instance = new \AnsPress_Stats_Widget();

		// Test.
		$new_instance = [
			'title' => '<strong></strong>',
		];
		$old_instance = [
			'title' => 'Old title',
		];
		$expected = [
			'title' => '',
		];
		$this->assertEquals( $expected, $instance->update( $new_instance, $old_instance ) );
	}

	/**
	 * @covers AnsPress_Stats_Widget::update
	 */
	public function testUpdateEmptyTitle() {
		$instance = new \AnsPress_Stats_Widget();

		// Test.
		$new_instance = [
			'title' => '',
		];
		$old_instance = [
			'title' => 'Old title',
		];
		$expected = [
			'title' => '',
		];
		$this->assertEquals( $expected, $instance->update( $new_instance, $old_instance ) );
	}

	/**
	 * @covers AnsPress_Stats_Widget::form
	 */
	public function testForm() {
		$instance = new \AnsPress_Stats_Widget();

		// Test.
		$instance_args = [
			'title' => 'Question Stats Form Title',
		];
		ob_start();
		$instance->form( $instance_args );
		$result = ob_get_clean();
		$this->assertStringContainsString( '[][title]', $result );
		$this->assertStringContainsString( 'value="Question Stats Form Title"', $result );
	}

	/**
	 * @covers AnsPress_Stats_Widget::form
	 */
	public function testFormWithEmptyTitle() {
		$instance = new \AnsPress_Stats_Widget();

		// Test.
		$instance_args = [
			'title' => '',
		];
		ob_start();
		$instance->form( $instance_args );
		$result = ob_get_clean();
		$this->assertStringContainsString( '[][title]', $result );
		$this->assertStringContainsString( 'value=""', $result );
	}

	/**
	 * @covers AnsPress_Stats_Widget::form
	 */
	public function testFormWithDefaultTitle() {
		$instance = new \AnsPress_Stats_Widget();

		// Test.
		$instance_args = [];
		ob_start();
		$instance->form( $instance_args );
		$result = ob_get_clean();
		$this->assertStringContainsString( '[][title]', $result );
		$this->assertStringContainsString( 'value="Question stats"', $result );
	}

	/**
	 * @covers AnsPress_Stats_Widget::widget
	 */
	public function testWidgetNotOnSingleQuestionPage() {
		$instance = new \AnsPress_Stats_Widget();

		// Test.
		$args = [
			'before_widget' => '<section class="widget">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		];
		$instance_args = [
			'title' => 'Question Stats Title',
		];
		ob_start();
		$instance->widget( $args, $instance_args );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<section class="widget">', $result );
		$this->assertStringContainsString( '<h2 class="widget-title">', $result );
		$this->assertStringContainsString( 'Question Stats Title', $result );
		$this->assertStringContainsString( 'This widget can only be used in single question page', $result );
	}

	/**
	 * @covers AnsPress_Stats_Widget::widget
	 */
	public function testWidgetNotOnSingleQuestionPageWithEmptyTitle() {
		$instance = new \AnsPress_Stats_Widget();

		// Test.
		$args = [
			'before_widget' => '<section class="widget">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		];
		$instance_args = [
			'title' => '',
		];
		ob_start();
		$instance->widget( $args, $instance_args );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<section class="widget">', $result );
		$this->assertStringNotContainsString( '<h2 class="widget-title">', $result );
		$this->assertStringContainsString( 'This widget can only be used in single question page', $result );
	}

	/**
	 * @covers AnsPress_Stats_Widget::widget
	 */
	public function testWidgetOnSingleQuestionPage() {
		$instance = new \AnsPress_Stats_Widget();
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$answer_ids = $this->factory()->post->create_many( 3, [ 'post_type' => 'answer', 'post_parent' => $question_id ] );

		// Test.
		$args = [
			'before_widget' => '<section class="widget">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		];
		$instance_args = [
			'title' => 'Question Stats Title',
		];
		$this->go_to( '?post_type=question&p=' . $question_id );
		ob_start();
		$instance->widget( $args, $instance_args );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<section class="widget">', $result );
		$this->assertStringContainsString( '<h2 class="widget-title">', $result );
		$this->assertStringContainsString( 'Question Stats Title', $result );
		$this->assertStringContainsString( '<ul class="ap-stats-widget">', $result );
		$this->assertStringContainsString( '<li><span class="stat-label apicon-pulse">Active</span><span class="stat-value"><time class="published updated" itemprop="dateModified" datetime', $result );
		$this->assertStringContainsString( '<li><span class="stat-label apicon-eye">Views</span><span class="stat-value">0 times</span></li>', $result );
		$this->assertStringContainsString( '<li><span class="stat-label apicon-answer">Answers</span><span class="stat-value"><span data-view="answer_count">3</span> answers</span></li>', $result );
	}

	/**
	 * @covers AnsPress_Stats_Widget::widget
	 */
	public function testWidgetOnSingleQuestionPageWithEmptyTitleAndOnlyOneAnswerAndTenViews() {
		$instance = new \AnsPress_Stats_Widget();
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$answer_ids = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		ap_insert_qameta( $question_id, [ 'views' => 10 ] );

		// Test.
		$args = [
			'before_widget' => '<section class="widget">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		];
		$instance_args = [
			'title' => '',
		];
		$this->go_to( '?post_type=question&p=' . $question_id );
		ob_start();
		$instance->widget( $args, $instance_args );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<section class="widget">', $result );
		$this->assertStringNotContainsString( '<h2 class="widget-title">', $result );
		$this->assertStringContainsString( '<ul class="ap-stats-widget">', $result );
		$this->assertStringContainsString( '<li><span class="stat-label apicon-pulse">Active</span><span class="stat-value"><time class="published updated" itemprop="dateModified" datetime', $result );
		$this->assertStringContainsString( '<li><span class="stat-label apicon-eye">Views</span><span class="stat-value">10 times</span></li>', $result );
		$this->assertStringContainsString( '<li><span class="stat-label apicon-answer">Answers</span><span class="stat-value"><span data-view="answer_count">1</span> answer</span></li>', $result );
	}

	/**
	 * @covers AnsPress_Stats_Widget::widget
	 */
	public function testWidgetOnSingleQuestionPageWithOnlyOneView() {
		$instance = new \AnsPress_Stats_Widget();
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		ap_insert_qameta( $question_id, [ 'views' => 1 ] );

		// Test.
		$args = [
			'before_widget' => '<section class="widget">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		];
		$instance_args = [
			'title' => 'Question Stats Title',
		];
		$this->go_to( '?post_type=question&p=' . $question_id );
		ob_start();
		$instance->widget( $args, $instance_args );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<section class="widget">', $result );
		$this->assertStringContainsString( '<h2 class="widget-title">', $result );
		$this->assertStringContainsString( 'Question Stats Title', $result );
		$this->assertStringContainsString( '<ul class="ap-stats-widget">', $result );
		$this->assertStringContainsString( '<li><span class="stat-label apicon-pulse">Active</span><span class="stat-value"><time class="published updated" itemprop="dateModified" datetime', $result );
		$this->assertStringContainsString( '<li><span class="stat-label apicon-eye">Views</span><span class="stat-value">1 time</span></li>', $result );
		$this->assertStringContainsString( '<li><span class="stat-label apicon-answer">Answers</span><span class="stat-value"><span data-view="answer_count">0</span> answers</span></li>', $result );
	}
}
