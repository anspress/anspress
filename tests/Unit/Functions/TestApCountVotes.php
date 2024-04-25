<?php
namespace AnsPress\Tests\Unit\Functions;

use Mockery;
use wpdb;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers ::ap_count_votes
 * @package AnsPress\Tests\Unit\Functions
 */
class TestApCountVotes extends TestCase {
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

	public function testBasicRetrieval() {
		$test_post_id = 123;

		// Prepare the arguments for the function
		$args = array( 'vote_post_id' => $test_post_id );

		// Mock wp_parse_args.
		Functions\when('wp_parse_args')->justReturn([
			'vote_post_id' => $test_post_id,
			'group'        => false
		]);

		$wpdbMock = $this->setupDBMock();

		$wpdbMock->shouldReceive('get_results')
			->once()
			->with('SELECT count(*) as count FROM wp_ap_votes WHERE 1=1 AND vote_post_id = 123')
			->andReturn([]);

		$this->assertTrue(is_array(ap_count_votes($args)));
   	}

	public function testFalseRetrieval() {
		$test_post_id = 123;

		// Prepare the arguments for the function
		$args = array( 'vote_post_id' => $test_post_id );

		// Mock wp_parse_args.
		Functions\when('wp_parse_args')->justReturn([
			'vote_post_id' => $test_post_id,
			'group'        => false
		]);

		$wpdbMock = $this->setupDBMock();

		$wpdbMock->shouldReceive('get_results')
			->once()
			->with('SELECT count(*) as count FROM wp_ap_votes WHERE 1=1 AND vote_post_id = 123')
			->andReturn(false);

		$this->assertFalse(ap_count_votes($args));
   	}

	public function testArgsVoteType() {
		$test_post_id = 123;
		$test_vote_type = 'flag';

		// Prepare the arguments for the function
		$args = array( 'vote_post_id' => $test_post_id, 'vote_type' => $test_vote_type );

		// Mock wp_parse_args.
		Functions\when('wp_parse_args')->justReturn([
			'vote_post_id' => $test_post_id,
			'vote_type'    => $test_vote_type,
			'group'        => false
		]);

		$wpdbMock = $this->setupDBMock();

		$wpdbMock->shouldReceive('prepare')
			->once()
			->with(' AND vote_type = %s', 'flag')
			->andReturn(' AND vote_type = \'flag\'');

		$wpdbMock->shouldReceive('get_results')
			->once()
			->with('SELECT count(*) as count FROM wp_ap_votes WHERE 1=1 AND vote_post_id = 123 AND vote_type = \'flag\'')
			->andReturn([]);

		$this->assertTrue(is_array(ap_count_votes($args)));
	}

	public function testArgsVoteTypeAsArr() {
		$test_post_id = 123;
		$test_vote_type = ['flag', 'up'];

		// Prepare the arguments for the function
		$args = array( 'vote_post_id' => $test_post_id, 'vote_type' => $test_vote_type );

		// Mock wp_parse_args.
		Functions\when('wp_parse_args')->justReturn([
			'vote_post_id' => $test_post_id,
			'vote_type'    => $test_vote_type,
			'group'        => false
		]);

		// Mock function sanitize_comma_delimited.
		Functions\when('sanitize_comma_delimited')->justReturn("'flag','up'");

		$wpdbMock = $this->setupDBMock();

		$wpdbMock->shouldReceive('get_results')
			->once()
			->with("SELECT count(*) as count FROM wp_ap_votes WHERE 1=1 AND vote_post_id = 123 AND vote_type IN ('flag','up')")
			->andReturn([]);

		$this->assertTrue(is_array(ap_count_votes($args)));
	}

	public function testArgsVoteUserIdAsArr() {
		$test_post_id = 123;
		$test_vote_user_id = [456, 789];

		// Prepare the arguments for the function
		$args = array( 'vote_post_id' => $test_post_id, 'vote_user_id' => $test_vote_user_id );

		// Mock wp_parse_args.
		Functions\when('wp_parse_args')->justReturn([
			'vote_post_id' => $test_post_id,
			'vote_user_id' => $test_vote_user_id,
			'group'        => false
		]);

		// Mock function sanitize_comma_delimited.
		Functions\when('sanitize_comma_delimited')->justReturn('456,789');

		$wpdbMock = $this->setupDBMock();

		$wpdbMock->shouldReceive('get_results')
			->once()
			->with('SELECT count(*) as count FROM wp_ap_votes WHERE 1=1 AND vote_post_id = 123 AND vote_user_id IN (456,789)')
			->andReturn([]);

		$this->assertTrue(is_array(ap_count_votes($args)));
	}

	public function testArgsVoteUserIdAsString() {
		$test_post_id = 123;
		$test_vote_user_id = '456';

		// Prepare the arguments for the function
		$args = array( 'vote_post_id' => $test_post_id, 'vote_user_id' => $test_vote_user_id );

		// Mock wp_parse_args.
		Functions\when('wp_parse_args')->justReturn([
			'vote_post_id' => $test_post_id,
			'vote_user_id' => $test_vote_user_id,
			'group'        => false
		]);

		$wpdbMock = $this->setupDBMock();

		$wpdbMock->shouldReceive('get_results')
			->once()
			->with("SELECT count(*) as count FROM wp_ap_votes WHERE 1=1 AND vote_post_id = 123 AND vote_user_id = '456'")
			->andReturn([]);

		$this->assertTrue(is_array(ap_count_votes($args)));
	}

	public function testArgsVoteActorIdAsString() {
		$test_post_id = 123;
		$test_vote_actor_id = '456';

		// Prepare the arguments for the function
		$args = array( 'vote_post_id' => $test_post_id, 'vote_actor_id' => $test_vote_actor_id );

		// Mock wp_parse_args.
		Functions\when('wp_parse_args')->justReturn([
			'vote_post_id' => $test_post_id,
			'vote_actor_id' => $test_vote_actor_id,
			'group'        => false
		]);

		$wpdbMock = $this->setupDBMock();

		$wpdbMock->shouldReceive('prepare')
			->once()
			->with(' AND vote_actor_id = %d', '456')
			->andReturn(' AND vote_actor_id = 456');

		$wpdbMock->shouldReceive('get_results')
			->once()
			->with("SELECT count(*) as count FROM wp_ap_votes WHERE 1=1 AND vote_post_id = 123 AND vote_actor_id = 456")
			->andReturn([]);

		$this->assertTrue(is_array(ap_count_votes($args)));
	}

	public function testArgsVoteActorIdAsArr() {
		$test_post_id = 123;
		$test_vote_actor_id = [456, 789];

		// Prepare the arguments for the function
		$args = array( 'vote_post_id' => $test_post_id, 'vote_actor_id' => $test_vote_actor_id );

		// Mock wp_parse_args.
		Functions\when('wp_parse_args')->justReturn([
			'vote_post_id' => $test_post_id,
			'vote_actor_id' => $test_vote_actor_id,
			'group'        => false
		]);

		// Mock function sanitize_comma_delimited.
		Functions\when('sanitize_comma_delimited')->justReturn('456,789');

		$wpdbMock = $this->setupDBMock();

		$wpdbMock->shouldReceive('get_results')
			->once()
			->with('SELECT count(*) as count FROM wp_ap_votes WHERE 1=1 AND vote_post_id = 123 AND vote_actor_id IN (456,789)')
			->andReturn([]);

		$this->assertTrue(is_array(ap_count_votes($args)));
	}

	public function testArgsVoteValueAsArr() {
		$test_post_id = 123;
		$test_vote_value = [1, 2];

		// Prepare the arguments for the function
		$args = array( 'vote_post_id' => $test_post_id, 'vote_value' => $test_vote_value );

		// Mock wp_parse_args.
		Functions\when('wp_parse_args')->justReturn([
			'vote_post_id' => $test_post_id,
			'vote_value'   => $test_vote_value,
			'group'        => false
		]);

		// Mock function sanitize_comma_delimited.
		Functions\when('sanitize_comma_delimited')->justReturn("'1','2'");

		$wpdbMock = $this->setupDBMock();

		$wpdbMock->shouldReceive('get_results')
			->once()
			->with("SELECT count(*) as count FROM wp_ap_votes WHERE 1=1 AND vote_post_id = 123 AND vote_value IN ('1','2')")
			->andReturn([]);

		$this->assertTrue(is_array(ap_count_votes($args)));
	}

	public function testArgsVoteValueAsString() {
		$test_post_id = 123;
		$test_vote_value = '1';

		// Prepare the arguments for the function
		$args = array( 'vote_post_id' => $test_post_id, 'vote_value' => $test_vote_value );

		// Mock wp_parse_args.
		Functions\when('wp_parse_args')->justReturn([
			'vote_post_id' => $test_post_id,
			'vote_value'   => $test_vote_value,
			'group'        => false
		]);

		$wpdbMock = $this->setupDBMock();

		$wpdbMock->shouldReceive('prepare')
			->once()
			->with(' AND vote_value = %s', '1')
			->andReturn(' AND vote_value = \'1\'');

		$wpdbMock->shouldReceive('get_results')
			->once()
			->with("SELECT count(*) as count FROM wp_ap_votes WHERE 1=1 AND vote_post_id = 123 AND vote_value = '1'")
			->andReturn([]);

		$this->assertTrue(is_array(ap_count_votes($args)));
	}

}
