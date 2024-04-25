<?php

namespace Tests\Unit\Functions;

use Mockery;
use wpdb;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers ::ap_delete_post_vote
 * @package Tests\Unit\Functions
 */
class TestApDeletePostVote extends TestCase {
    protected function setUp(): void {
        parent::setUp();

        require_once PLUGIN_DIR . '/includes/votes.php';
    }

    public function testDeleteVoteUpSuccess() {
        $post_id = 123;
        $user_id = 456;

        Functions\expect('ap_update_votes_count')
            ->once()
            ->with($post_id)
            ->andReturn(10);

		Functions\expect('ap_delete_vote')
			->once()
			->with($post_id, $user_id, 'vote', '1')
			->andReturn(1);

        Functions\expect('do_action')
            ->once()
            ->with('ap_undo_vote', $post_id, 10)
            ->once()
            ->with('ap_undo_vote_up', $post_id, 10);

        $this->assertEquals(
            10,
            ap_delete_post_vote($post_id, $user_id, true)
        );
    }

	public function testDeleteVoteDownSuccess() {
		$post_id = 123;
		$user_id = 456;

		Functions\expect('ap_update_votes_count')
			->once()
			->with($post_id)
			->andReturn(10);

		Functions\expect('ap_delete_vote')
			->once()
			->with($post_id, $user_id, 'vote', '-1')
			->andReturn(1);

		Functions\expect('do_action')
			->once()
			->with('ap_undo_vote', $post_id, 10)
			->once()
			->with('ap_undo_vote_down', $post_id, 10);

		$this->assertEquals(
			10,
			ap_delete_post_vote($post_id, $user_id, false)
		);
	}

	public function testFalseReturn() {
		$post_id = 123;
		$user_id = 456;

		Functions\expect('ap_delete_vote')
			->once()
			->with($post_id, $user_id, 'vote', '1')
			->andReturn(false);

		Functions\expect('do_action')
			->never()
			->with('ap_undo_vote', $post_id, 10)
			->never()
			->with('ap_undo_vote_down', $post_id, 10);

		$this->assertFalse(
			ap_delete_post_vote($post_id, $user_id, true)
		);
	}
}
