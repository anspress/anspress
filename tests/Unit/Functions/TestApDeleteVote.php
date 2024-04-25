<?php

namespace Tests\Unit\Functions;

use Mockery;
use wpdb;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers ::ap_delete_vote
 * @package Tests\Unit\Functions
 */
class TestApDeleteVote extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		require_once PLUGIN_DIR . '/includes/votes.php';
	}

	/**
	 * Helper to mock the database call and assert results are an integer.
	 */
	private function setupDBMock() {
		global $wpdb;
		$wpdb = Mockery::mock(wpdb::class)->makePartial();
		$wpdb->ap_votes = 'wp_ap_votes';
		$wpdb->prefix = 'wp_';

		return $wpdb;
	}

	public function testWithEmptyTypeValue() {
		$wpdbMock = $this->setupDBMock();

		$wpdbMock->shouldReceive('delete')
			->once()
			->with('wp_ap_votes', [
				'vote_post_id' => 123,
				'vote_user_id' => 456,
				'vote_type' => 'vote'
			])
			->andReturn(1);

		Functions\expect('get_current_user_id')
			->once()
			->andReturn(456);

		Functions\expect('do_action')
			->once()
			->with('ap_delete_vote', 123, 456, 'vote', false);

		$this->assertEquals(1, ap_delete_vote(123));
	}

	public function testReturnFalse() {
		$wpdbMock = $this->setupDBMock();

		$wpdbMock->shouldReceive('delete')
			->once()
			->with('wp_ap_votes', [
				'vote_post_id' => 123,
				'vote_user_id' => 456,
				'vote_type' => 'vote'
			])
			->andReturn(false);

		Functions\expect('do_action')
			->never();

		$this->assertFalse(ap_delete_vote(123, 456, 'vote'));
	}

	public function testWithValueAsString() {
		$wpdbMock = $this->setupDBMock();

		$wpdbMock->shouldReceive('delete')
			->once()
			->with('wp_ap_votes', [
				'vote_post_id' => 123,
				'vote_user_id' => 456,
				'vote_type'    => 'vote',
				'vote_value'   => '-1'
			])
			->andReturn(1);

		Functions\expect('do_action')
			->once()
			->with('ap_delete_vote', 123, 456, 'vote', '-1');

		$this->assertEquals(1, ap_delete_vote(123, 456, 'vote', '-1'));
	}
}
