<?php

namespace Anspress\Tests;

use AnsPress\WPTestUtils\WPIntegration\TestCaseAjax;

class TestAjax extends TestCaseAjax {
	public static $current_post;

	public static function wpSetUpBeforeClass(\WP_UnitTest_Factory $factory)
	{
		self::$current_post = $factory->post->create(
			array(
				'post_title'   => 'Comment form loading',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
	}

	public function testMethodExists() {
		// Require ajax-hooks.php file.
		require_once ANSPRESS_DIR . 'includes/ajax-hooks.php';

		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'init' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'suggest_similar_questions' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'toggle_delete_post' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'permanent_delete_post' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'toggle_featured' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'close_question' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'send' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'load_tinymce' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'convert_to_post' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'load_filter_order_by' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'subscribe_to_question' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'search_tags' ) );
	}
}
