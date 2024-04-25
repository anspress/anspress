<?php

namespace Tests\Unit\Functions;

use Mockery;
use wpdb;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers ::ap_is_user_voted
 * @package Tests\Unit\Functions
 */
class TestApIsUserVoted extends TestCase {
    protected function setUp(): void {
        parent::setUp();

        require_once PLUGIN_DIR . '/includes/votes.php'; // Adjust the path as per your plugin structure.
    }

    public function testUserVoted() {
        Functions\when('ap_get_vote')->justReturn([
			'vote_id' => 123,
			'vote_type' => 'vote'
		]);

        $this->assertTrue(ap_is_user_voted(123, 'vote', 123));
    }

    public function testUserNotVoted() {
        Functions\when('ap_get_vote')->justReturn(false);

        $this->assertFalse(ap_is_user_voted(123, 'vote', 123));
    }

    public function testCurrentUserVoted() {
		Functions\when('get_current_user_id')->justReturn(999);
        Functions\expect('ap_get_vote')
			->with(123, 999, 'vote')
			->andReturn([]);

        // Simulating the scenario where $user_id is not provided and it defaults to the current user.
        $this->assertTrue(ap_is_user_voted(123, 'vote'));
    }
}
