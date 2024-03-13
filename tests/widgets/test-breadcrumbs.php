<?php

namespace Anspress\Tests;

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
}
