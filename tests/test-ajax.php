<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAjax extends TestCase {
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
}
