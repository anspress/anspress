<?php

namespace Tests\Unit\Functions;

use Mockery;
use wpdb;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers ::ap_delete_votes
 * @package Tests\Unit\Functions
 */
class TestApDeleteVotes extends TestCase {
	use \Tests\Unit\AnsPressTestHelpers;

	protected function setUp(): void {
		parent::setUp();

		require_once PLUGIN_DIR . '/includes/votes.php';
	}

	public function testDeleteVotes() {
		$post_id = 123;

		$wpdb = $this->setupWPDBMock();

		$wpdb->shouldReceive('delete')
			->once()
			->with('wp_ap_votes', ['vote_post_id' => $post_id, 'vote_type' => 'vote'])
			->andReturn(3);

		$this->assertActionRegistered('ap_deleted_votes', $post_id, 'vote');

		$this->assertEquals(
			3,
			ap_delete_votes($post_id)
		);
	}

	public function testFalseReturn() {
		$post_id = 123;

		$wpdb = $this->setupWPDBMock();

		$wpdb->shouldReceive('delete')
			->once()
			->with('wp_ap_votes', ['vote_post_id' => $post_id, 'vote_type' => 'vote'])
			->andReturn(false);

		$this->assertActionNotRegistered('ap_deleted_votes', $post_id, 'vote');

		$this->assertFalse(
			ap_delete_votes($post_id)
		);
	}
}
