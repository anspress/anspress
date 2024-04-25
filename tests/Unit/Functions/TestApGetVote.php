<?php

namespace Tests\Unit\Functions;

use Mockery;
use wpdb;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers ::ap_get_vote
 * @package Tests\Unit\Functions
 */
class TestApGetVote extends TestCase {
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

		$wpdbMock->shouldReceive('prepare')
			->once()
			->with(' AND vote_post_id = %d AND  vote_user_id = %d LIMIT 1', 123, 456)
			->andReturn(' AND vote_post_id = 123 AND  vote_user_id = 456 LIMIT 1')
			;

		$wpdbMock->shouldReceive('get_row')
			->once()
			->with('SELECT * FROM wp_ap_votes WHERE 1=1  AND vote_post_id = 123 AND  vote_user_id = 456 LIMIT 1')
			->andReturn([
				'vote_type' => 'vote_up'
			]);

		$this->assertNotFalse(ap_get_vote(123, 456, ''));
   	}

	public function testReturnFalse() {
		$wpdbMock = $this->setupDBMock();

		$wpdbMock->shouldReceive('prepare')
			->once()
			->with(' AND vote_post_id = %d AND  vote_user_id = %d LIMIT 1', 123, 456)
			->andReturn(' AND vote_post_id = 123 AND  vote_user_id = 456 LIMIT 1')
			;

		$wpdbMock->shouldReceive('get_row')
			->once()
			->with('SELECT * FROM wp_ap_votes WHERE 1=1  AND vote_post_id = 123 AND  vote_user_id = 456 LIMIT 1')
			->andReturn([]);

		$this->assertFalse(ap_get_vote(123, 456, ''));
   	}

	public function testWithVoteTypeValueAsArray() {
		$wpdbMock = $this->setupDBMock();

		// Mock sanitize_comma_delimited.
		Functions\when('sanitize_comma_delimited')->justReturn('\'vote_up\',\'vote_down\'');

		$wpdbMock->shouldReceive('prepare')
			->once()
			->with(' AND vote_post_id = %d AND  vote_user_id = %d LIMIT 1', 123, 456)
			->andReturn(' AND vote_post_id = 123 AND  vote_user_id = 456 LIMIT 1');

		$wpdbMock->shouldReceive('get_row')
			->once()
			->with("SELECT * FROM wp_ap_votes WHERE 1=1  AND vote_type IN ('vote_up','vote_down') AND vote_post_id = 123 AND  vote_user_id = 456 LIMIT 1")
			->andReturn([
				'vote_type' => 'vote_up'
			]);

		$this->assertNotFalse(ap_get_vote(123, 456, ['vote_up', 'vote_down']));
   	}

	public function testWithVoteTypeAsString() {
		$wpdbMock = $this->setupDBMock();

		$wpdbMock->shouldReceive('prepare')
			->once()
			->with(' AND vote_type = %s', 'vote_up')
			->andReturn(' AND vote_type = \'vote_up\'');

		$wpdbMock->shouldReceive('prepare')
			->once()
			->with(' AND vote_post_id = %d AND  vote_user_id = %d LIMIT 1', 123, 456)
			->andReturn(' AND vote_post_id = 123 AND  vote_user_id = 456 LIMIT 1');

		$wpdbMock->shouldReceive('get_row')
			->once()
			->with("SELECT * FROM wp_ap_votes WHERE 1=1  AND vote_type = 'vote_up' AND vote_post_id = 123 AND  vote_user_id = 456 LIMIT 1")
			->andReturn([
				'vote_type' => 'vote_up'
			]);

		$this->assertNotFalse(ap_get_vote(123, 456, 'vote_up'));
   	}

	public function testWithVoteValueAsArray(){
		$wpdbMock = $this->setupDBMock();

		// Mock sanitize_comma_delimited.
		Functions\when('sanitize_comma_delimited')->justReturn("'-1','1'");

		$wpdbMock->shouldReceive('prepare')
			->once()
			->with(' AND vote_post_id = %d AND  vote_user_id = %d LIMIT 1', 123, 456)
			->andReturn(' AND vote_post_id = 123 AND  vote_user_id = 456 LIMIT 1');

		$wpdbMock->shouldReceive('get_row')
			->once()
			->with("SELECT * FROM wp_ap_votes WHERE 1=1  AND vote_value IN ('-1','1') AND vote_post_id = 123 AND  vote_user_id = 456 LIMIT 1")
			->andReturn([
				'vote_type' => 'vote_up'
			]);

		$this->assertNotFalse(ap_get_vote(123, 456, '', ['-1', '1']));
	}

	public function testWithVoteValueAsString(){
		$wpdbMock = $this->setupDBMock();

		$wpdbMock->shouldReceive('prepare')
			->once()
			->with(' AND vote_type = %s', 'vote_up')
			->andReturn(' AND vote_type = \'vote_up\'');

		$wpdbMock->shouldReceive('prepare')
			->once()
			->with(' AND vote_value = %s', '-1')
			->andReturn(' AND vote_value = \'-1\'');

		$wpdbMock->shouldReceive('prepare')
			->once()
			->with(' AND vote_post_id = %d AND  vote_user_id = %d LIMIT 1', 123, 456)
			->andReturn(' AND vote_post_id = 123 AND  vote_user_id = 456 LIMIT 1');

		$wpdbMock->shouldReceive('get_row')
			->once()
			->with("SELECT * FROM wp_ap_votes WHERE 1=1  AND vote_type = 'vote_up' AND vote_value = '-1' AND vote_post_id = 123 AND  vote_user_id = 456 LIMIT 1")
			->andReturn([
				'vote_type' => 'vote_up'
			]);

		$this->assertNotFalse(ap_get_vote(123, 456, 'vote_up', '-1'));
	}
}
