<?php

namespace AnsPress\Tests\Unit\Functions;

use Mockery;
use wpdb;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers ::ap_count_post_votes_by
 * @package AnsPress\Tests\Unit\Functions
 */
class TestApCountPostVotesBy extends TestCase {
	public $originalWpdb;

	protected function setUp(): void {
		parent::setUp();

		require_once PLUGIN_DIR . '/includes/votes.php';

		global $wpdb;

		$this->originalWpdb = $wpdb;
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

	public function testAllowedBy() {
		$this->assertFalse(ap_count_post_votes_by('non_allowed', '1'));
   	}

	public function testByPostId() {
		Functions\expect('ap_count_votes')
			->once()
			->with([
				'vote_post_id' => '1',
				'group'        => 'vote_value',
				'vote_type'    => 'vote'
			])
			->andReturn([]);

		ap_count_post_votes_by('post_id', '1');
   	}

	public function testByUserId() {
		Functions\expect('ap_count_votes')
			->once()
			->with([
				'vote_user_id' => '1',
				'group'        => 'vote_value',
				'vote_type'    => 'vote',
			])
			->andReturn([]);

		ap_count_post_votes_by('user_id', '1');
   	}

	public function testByActorId() {
		Functions\expect('ap_count_votes')
			->once()
			->with([
				'vote_actor_id' => '1',
				'group'         => 'vote_value',
				'vote_type'     => 'vote',
			])
			->andReturn([]);

		ap_count_post_votes_by('actor_id', '1');
   	}

	public function testReturnType() {
		Functions\expect('ap_count_votes')
			->once()
			->with([
				'vote_post_id' => '1',
				'group'        => 'vote_value',
				'vote_type'    => 'vote',
			])
			->andReturn([
				(object) [
					'count'      => 2,
					'vote_value' => '1'
				],
				(object) [
					'count'      => 5,
					'vote_value' => '-1'
				]
			]);

		$this->assertEquals(
			[
				'votes_net'  => -3,
				'votes_down' => 5,
				'votes_up'   => 2,
			],
			ap_count_post_votes_by('post_id', '1')
		);
   	}
}
