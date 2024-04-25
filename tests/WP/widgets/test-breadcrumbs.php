<?php

namespace AnsPress\Tests\WP;

use AnsPress_Breadcrumbs_Widget;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestWidgetBreadcrumbs extends TestCase {
	public function testWidgetsInit() {
		$this->assertEquals( 10, has_action( 'widgets_init', 'register_anspress_breadcrumbs' ) );
		$this->assertTrue( class_exists( 'AnsPress_Breadcrumbs_Widget' ) );
		register_anspress_breadcrumbs();
		$this->assertArrayHasKey( 'AnsPress_Breadcrumbs_Widget',$GLOBALS['wp_widget_factory']->widgets );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Breadcrumbs_Widget', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress_Breadcrumbs_Widget', 'get_breadcrumbs' ) );
		$this->assertTrue( method_exists( 'AnsPress_Breadcrumbs_Widget', 'breadcrumbs' ) );
		$this->assertTrue( method_exists( 'AnsPress_Breadcrumbs_Widget', 'widget' ) );
		$this->assertTrue( method_exists( 'AnsPress_Breadcrumbs_Widget', 'form' ) );
		$this->assertTrue( method_exists( 'AnsPress_Breadcrumbs_Widget', 'update' ) );
	}

	/**
	 * @covers AnsPress_Breadcrumbs_Widget::__construct
	 */
	public function testConstruct() {
		$instance = new \AnsPress_Breadcrumbs_Widget();
		$this->assertEquals( strtolower( 'ap_breadcrumbs_widget' ), $instance->id_base );
		$this->assertEquals( '(AnsPress) Breadcrumbs', $instance->name );
		$this->assertEquals( 'Show current anspress page navigation', $instance->widget_options['description'] );
	}

	public function test_get_breadcrumbs_base_page() {
		$this->go_to(ap_base_page_link());

		$breadcrumbs = AnsPress_Breadcrumbs_Widget::get_breadcrumbs();
		$this->assertIsArray($breadcrumbs);

		$this->assertArrayHasKey('base', $breadcrumbs);
		$this->assertEquals([
			'base' => [
				'title' => 'Questions',
				'link' => ap_base_page_link(),
				'order' => 0
			]
		], $breadcrumbs);
	}

	public function test_get_breadcrumbs_question() {
		$question = $this->factory()->post->create_and_get([
			'post_type'    => 'question',
			'post_title'   => 'Test question',
			'post_content' => 'Test question content',
		]);

		$this->go_to("?post_type=question&p={$question->ID}");

		$breadcrumbs = AnsPress_Breadcrumbs_Widget::get_breadcrumbs();
		$this->assertIsArray($breadcrumbs);

		$this->assertArrayHasKey('base', $breadcrumbs);
		$this->assertEquals([
			'base' => [
				'title' => 'Questions',
				'link' => ap_base_page_link(),
				'order' => 0
			],
			'page' => [
				'title' => get_the_title(),
				'link' => get_permalink( get_question_id() ),
				'order' => 10
			]
		], $breadcrumbs);
	}

	/**
	 * @covers AnsPress_Breadcrumbs_Widget::get_breadcrumbs
	 */
	public function testGetBreadcrumbsAskPage() {
		$ask_page = $this->factory()->post->create_and_get( [
			'post_type'    => 'page',
			'post_title'   => 'Ask',
			'post_content' => 'Ask page content',
		] );
		ap_opt( 'ask_page', $ask_page->ID );
		$this->go_to( ap_get_link_to( 'ask' ) );

		// Get breadcrumbs.
		$breadcrumbs = AnsPress_Breadcrumbs_Widget::get_breadcrumbs();

		// Tests.
		$this->assertIsArray( $breadcrumbs );
		$this->assertArrayHasKey( 'base', $breadcrumbs );
		$this->assertEquals(
			[
				'base' => [
					'title' => 'Questions',
					'link'  => ap_base_page_link(),
					'order' => 0
				],
				'page' => [
					'title' => get_the_title(),
					'link'  => ap_get_link_to( 'ask' ),
					'order' => 10
				]
			],
			$breadcrumbs
		);
	}

	/**
	 * @covers AnsPress_Breadcrumbs_Widget::get_breadcrumbs
	 */
	public function testGetBreadcrumbsActivitiesPage() {
		$activities_page = $this->factory()->post->create_and_get( [
			'post_type'    => 'page',
			'post_title'   => 'Activities',
			'post_content' => 'Activities page content',
		] );
		ap_opt( 'activities_page', $activities_page->ID );
		$this->go_to( ap_get_link_to( 'activities' ) );
		set_query_var( 'ap_page', 'activities' );

		// Get breadcrumbs.
		$breadcrumbs = AnsPress_Breadcrumbs_Widget::get_breadcrumbs();

		// Tests.
		$this->assertIsArray( $breadcrumbs );
		$this->assertArrayHasKey( 'base', $breadcrumbs );
		$this->assertEquals(
			[
				'base' => [
					'title' => 'Questions',
					'link'  => ap_base_page_link(),
					'order' => 0
				],
				'page' => [
					'title' => get_the_title(),
					'link'  => ap_get_link_to( 'activities' ),
					'order' => 10
				]
			],
			$breadcrumbs
		);
	}

	public static function APPageTitle( $title ) {
		return 'Test page';
	}

	/**
	 * @covers AnsPress_Breadcrumbs_Widget::get_breadcrumbs
	 */
	public function testGetBreadcrumbsPage() {
		$page = $this->factory()->post->create_and_get( [
			'post_type'    => 'page',
			'post_title'   => 'Test page',
			'post_content' => 'Test page content',
		] );
		$this->go_to( get_permalink( $page->ID ) );
		set_query_var( 'ap_page', 'https://example.com' );

		// Get breadcrumbs.
		add_filter( 'ap_page_title', [ $this, 'APPageTitle' ] );
		$breadcrumbs = AnsPress_Breadcrumbs_Widget::get_breadcrumbs();

		// Tests.
		$this->assertIsArray( $breadcrumbs );
		$this->assertArrayHasKey( 'base', $breadcrumbs );
		$this->assertEquals(
			[
				'base' => [
					'title' => 'Questions',
					'link'  => ap_base_page_link(),
					'order' => 0
				],
				'page' => [
					'title' => 'Test page',
					'link'  => 'https://example.com',
					'order' => 10
				]
			],
			$breadcrumbs
		);
		remove_filter( 'ap_page_title', [ $this, 'APPageTitle' ] );
	}

	/**
	 * @covers AnsPress_Breadcrumbs_Widget::breadcrumbs
	 */
	public function testBreadcrumbsBasePage() {
		$this->go_to( ap_base_page_link() );

		// Get breadcrumbs.
		ob_start();
		AnsPress_Breadcrumbs_Widget::breadcrumbs();
		$output = ob_get_clean();

		// Tests.
		$this->assertStringContainsString( '<ul class="ap-breadcrumbs clearfix">', $output );
		$this->assertStringContainsString( '<li class="ap-breadcrumbs-home"><a href="' . esc_url( home_url( '/' ) ) . '" class="apicon-home"></a></li>', $output );
		$this->assertStringContainsString( '<li><i class="apicon-chevron-right"></i></li>', $output );
		$this->assertStringContainsString( '<li><a href="' . esc_url( ap_base_page_link() ) . '">Questions</a></li>', $output );
		$this->assertStringNotContainsString( '<li><a href="' . esc_url( ap_base_page_link() ) . '">Questions</a></li><li><i class="apicon-chevron-right"></i></li>', $output );
	}

	/**
	 * @covers AnsPress_Breadcrumbs_Widget::breadcrumbs
	 */
	public function testBreadcrumbsQuestion() {
		$question = $this->factory()->post->create_and_get( [
			'post_type'    => 'question',
			'post_title'   => 'Test question',
			'post_content' => 'Test question content',
		] );
		$this->go_to( '?post_type=question&p=' . $question->ID );

		// Get breadcrumbs.
		ob_start();
		AnsPress_Breadcrumbs_Widget::breadcrumbs();
		$output = ob_get_clean();

		// Tests.
		$this->assertStringContainsString( '<ul class="ap-breadcrumbs clearfix">', $output );
		$this->assertStringContainsString( '<li class="ap-breadcrumbs-home"><a href="' . esc_url( home_url( '/' ) ) . '" class="apicon-home"></a></li>', $output );
		$this->assertStringContainsString( '<li><i class="apicon-chevron-right"></i></li>', $output );
		$this->assertStringContainsString( '<li><a href="' . esc_url( ap_base_page_link() ) . '">Questions</a></li>', $output );
		$this->assertStringContainsString( '<li><a href="' . esc_url( ap_base_page_link() ) . '">Questions</a></li><li><i class="apicon-chevron-right"></i></li>', $output );
		$this->assertStringContainsString( '<li><a href="' . esc_url( get_permalink( get_question_id() ) ) . '">Test question</a></li>', $output );
		$this->assertStringNotContainsString( '<li><a href="' . esc_url( get_permalink( get_question_id() ) ) . '">Test question</a></li><li><i class="apicon-chevron-right"></i></li>', $output );
	}

	/**
	 * @covers AnsPress_Breadcrumbs_Widget::breadcrumbs
	 */
	public function testBreadcrumbsAskPage() {
		$ask_page = $this->factory()->post->create_and_get( [
			'post_type'    => 'page',
			'post_title'   => 'Ask',
			'post_content' => 'Ask page content',
		] );
		ap_opt( 'ask_page', $ask_page->ID );
		$this->go_to( ap_get_link_to( 'ask' ) );

		// Get breadcrumbs.
		ob_start();
		AnsPress_Breadcrumbs_Widget::breadcrumbs();
		$output = ob_get_clean();

		// Tests.
		$this->assertStringContainsString( '<ul class="ap-breadcrumbs clearfix">', $output );
		$this->assertStringContainsString( '<li class="ap-breadcrumbs-home"><a href="' . esc_url( home_url( '/' ) ) . '" class="apicon-home"></a></li>', $output );
		$this->assertStringContainsString( '<li><i class="apicon-chevron-right"></i></li>', $output );
		$this->assertStringContainsString( '<li><a href="' . esc_url( ap_base_page_link() ) . '">Questions</a></li>', $output );
		$this->assertStringContainsString( '<li><a href="' . esc_url( ap_base_page_link() ) . '">Questions</a></li><li><i class="apicon-chevron-right"></i></li>', $output );
		$this->assertStringContainsString( '<li><a href="' . esc_url( ap_get_link_to( 'ask' ) ) . '">Ask</a></li>', $output );
		$this->assertStringNotContainsString( '<li><a href="' . esc_url( ap_get_link_to( 'ask' ) ) . '">Ask</a></li><li><i class="apicon-chevron-right"></i></li>', $output );
	}

	/**
	 * @covers AnsPress_Breadcrumbs_Widget::widget
	 */
	public function testWidgetBasePage() {
		$instance = new \AnsPress_Breadcrumbs_Widget();
		$args     = [
			'before_widget' => '<section class="widget">',
			'after_widget'  => '</section>',
		];
		$this->go_to( ap_base_page_link() );
		ob_start();
		$instance->widget( $args, [] );
		$output = ob_get_clean();
		$this->assertStringContainsString( '<section class="widget">', $output );
		$this->assertStringContainsString( '<li><a href="' . esc_url( ap_base_page_link() ) . '">Questions</a></li>', $output );
		$this->assertStringNotContainsString( '<li><a href="' . esc_url( ap_base_page_link() ) . '">Questions</a></li><li><i class="apicon-chevron-right"></i></li>', $output );
	}

	/**
	 * @covers AnsPress_Breadcrumbs_Widget::widget
	 */
	public function testWidgetQuestion() {
		$instance = new \AnsPress_Breadcrumbs_Widget();
		$args     = [
			'before_widget' => '<section class="widget">',
			'after_widget'  => '</section>',
		];
		$question = $this->factory()->post->create_and_get( [
			'post_type'    => 'question',
			'post_title'   => 'Test question',
			'post_content' => 'Test question content',
		] );
		$this->go_to( '?post_type=question&p=' . $question->ID );
		ob_start();
		$instance->widget( $args, [] );
		$output = ob_get_clean();
		$this->assertStringContainsString( '<section class="widget">', $output );
		$this->assertStringContainsString( '<li><a href="' . esc_url( ap_base_page_link() ) . '">Questions</a></li>', $output );
		$this->assertStringContainsString( '<li><a href="' . esc_url( ap_base_page_link() ) . '">Questions</a></li><li><i class="apicon-chevron-right"></i></li>', $output );
		$this->assertStringContainsString( '<li><a href="' . esc_url( get_permalink( get_question_id() ) ) . '">Test question</a></li>', $output );
		$this->assertStringNotContainsString( '<li><a href="' . esc_url( get_permalink( get_question_id() ) ) . '">Test question</a></li><li><i class="apicon-chevron-right"></i></li>', $output );
	}
}
