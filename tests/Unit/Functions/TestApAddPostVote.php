<?php

namespace Tests\Unit\Functions;

use Mockery;
use wpdb;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers ::ap_add_post_vote
 * @package Tests\Unit\Functions
 */
class TestApAddPostVote extends TestCase {
    protected function setUp(): void {
        parent::setUp();

        require_once PLUGIN_DIR . '/includes/votes.php';
    }

    public function testAddPostVoteUpSuccess() {
        $post_id = 123;
        $user_id = 456;

		$counts = [
			'votes_net'  => 0,
			'votes_down' => 0,
			'votes_up'   => 0,
		];

		$mockedPost = Mockery::mock('WP_Post');
		$mockedPost->post_author = 789;

		Functions\expect('get_post')
			->andReturn($mockedPost);

        Functions\expect('ap_vote_insert')
			->once()
			->with($post_id, $user_id, 'vote', 789, 1)
			->andReturn((object) ['post_author' => 789]);

        Functions\expect('ap_update_votes_count')
			->once()
			->with($post_id)
			->andReturn($counts);

        Functions\expect('do_action')
			->once()
			->with('ap_vote_up', $post_id, $counts);

        $this->assertEquals(
			$counts,
			ap_add_post_vote($post_id, $user_id, true)
		);
    }

	public function testVoteDownSuccess() {
		$post_id = 123;
		$user_id = 456;

		$counts = [
			'votes_net'  => 0,
			'votes_down' => 0,
			'votes_up'   => 0,
		];

		$mockedPost = Mockery::mock('WP_Post');
		$mockedPost->post_author = 789;

		Functions\expect('get_post')
			->andReturn($mockedPost);

		Functions\expect('ap_vote_insert')
			->once()
			->with($post_id, $user_id, 'vote', 789, -1)
			->andReturn((object) ['post_author' => 789]);

		Functions\expect('ap_update_votes_count')
			->once()
			->with($post_id)
			->andReturn($counts);

		Functions\expect('do_action')
			->once()
			->with('ap_vote_down', $post_id, $counts);

		$this->assertEquals(
			$counts,
			ap_add_post_vote($post_id, $user_id, false)
		);
	}

	public function testCurrentUserId() {
		$post_id = 123;

		$counts = [
			'votes_net'  => 0,
			'votes_down' => 0,
			'votes_up'   => 0,
		];

		$mockedPost = Mockery::mock('WP_Post');
		$mockedPost->post_author = 789;

		Functions\expect('get_post')
			->andReturn($mockedPost);

		Functions\expect('get_current_user_id')
			->andReturn(999);

		Functions\expect('ap_vote_insert')
			->once()
			->with($post_id, 999, 'vote', 789, 1)
			->andReturn((object) ['post_author' => 789]);

		Functions\expect('ap_update_votes_count')
			->once()
			->with($post_id)
			->andReturn($counts);

		Functions\expect('do_action')
			->once()
			->with('ap_vote_up', $post_id, $counts);

		$this->assertEquals(
			$counts,
			ap_add_post_vote($post_id, false, true)
		);
	}

	public function testFalseReturn() {
		$post_id = 123;
		$user_id = 456;

		$counts = [
			'votes_net'  => 0,
			'votes_down' => 0,
			'votes_up'   => 0,
		];

		$mockedPost = Mockery::mock('WP_Post');
		$mockedPost->post_author = 789;

		Functions\expect('get_post')
			->andReturn($mockedPost);

		Functions\expect('ap_vote_insert')
			->once()
			->with($post_id, $user_id, 'vote', 789, 1)
			->andReturn(false);

		$this->assertFalse(ap_add_post_vote($post_id, $user_id, true));
	}
}
