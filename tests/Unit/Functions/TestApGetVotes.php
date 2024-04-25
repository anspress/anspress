<?php

namespace AnsPress\Tests\Unit\Functions;

use Mockery;
use wpdb;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers ::ap_get_votes
 * @package AnsPress\Tests\Unit\Functions
 */
class TestApGetVotes extends TestCase {
	public $originalWpdb;

    protected function setUp(): void {
		parent::setUp();

		require_once PLUGIN_DIR . '/includes/votes.php';

		global $wpdb;

		$this->originalWpdb = $wpdb;
	}

	/**
     * Helper to mock the database call and assert results are an array.
     */
    private function setupDbMockAndAssert($args, $expectedQuery, $returnValue, $prepare = []) {
        global $wpdb;
        $wpdb = Mockery::mock(wpdb::class)->makePartial();
        $wpdb->ap_votes = 'wp_ap_votes';
        $wpdb->prefix = 'wp_';
        $wpdb->shouldReceive('get_results')
            ->once()
            ->with($expectedQuery)
            ->andReturn($returnValue);

		if ( ! empty( $prepare ) ) {
			$wpdb->shouldReceive('prepare')
				->withArgs( $prepare['args'] )
				->andReturn( $prepare['returnValue'] )
				;
		}

        $results = ap_get_votes($args);
        $this->assertIsArray($results);
    }

    public function testBasicRetrieval() {
        $test_post_id = 123;

        // Prepare the arguments for the function
        $args = array( 'vote_post_id' => $test_post_id );

		$this->setupDbMockAndAssert($args, 'SELECT * FROM wp_ap_votes WHERE 1=1 AND vote_post_id = ' . $test_post_id, []);
   	}

	public function testArgsAsAString()
	{
			$this->setupDbMockAndAssert(
				123,
				'SELECT * FROM wp_ap_votes WHERE 1=1 AND vote_post_id = 123',
				[]
			);

			$this->setupDbMockAndAssert(
				'44,456.00',
				'SELECT * FROM wp_ap_votes WHERE 1=1 AND vote_post_id = 44',
				[]
			);
	}

	public function testArgsAsAnInvalid()
	{
		$test_post_id = ' ';

		$this->setupDbMockAndAssert(
			$test_post_id,
			'SELECT * FROM wp_ap_votes WHERE 1=1',
			[]
		);

		// Check when arg is passed as an 0.
		$this->setupDbMockAndAssert(
			0,
			'SELECT * FROM wp_ap_votes WHERE 1=1',
			[]
		);

		// Check when args is passed as a negative number.
		$this->setupDbMockAndAssert(
			-1,
			'SELECT * FROM wp_ap_votes WHERE 1=1 AND vote_post_id = -1',
			[]
		);

		// Check with invlaid array items.
		Functions\expect( 'sanitize_comma_delimited')
			->andReturn( '0,1');

		$this->setupDbMockAndAssert(
			array( 'vote_post_id' => [0,1] ),
			'SELECT * FROM wp_ap_votes WHERE 1=1 AND vote_post_id IN (0,1)',
			[]
		);
	}

	public function testVoteUserIdArgs()
	{
		// Test with user id and post id.

		$test_post_id = 123;
		$test_user_id = 456;

		$this->setupDbMockAndAssert(
			array(
				'vote_post_id' => $test_post_id,
				'vote_user_id' => $test_user_id
			),
			'SELECT * FROM wp_ap_votes WHERE 1=1 AND vote_post_id = ' . $test_post_id . ' AND vote_user_id = ' . $test_user_id,
			[]
		);

		// Without vote_post_id.
		$this->setupDbMockAndAssert(
			array( 'vote_user_id' => $test_user_id ),
			'SELECT * FROM wp_ap_votes WHERE 1=1 AND vote_user_id = ' . $test_user_id,
			[]
		);

		// Test with array of user ids.
		$test_user_id = array( 456, 789 );

		Functions\expect( 'sanitize_comma_delimited')
		->andReturn( '456,789' );

		$this->setupDbMockAndAssert(
			array( 'vote_user_id' => $test_user_id ),
			'SELECT * FROM wp_ap_votes WHERE 1=1 AND vote_user_id IN (456,789)',
			[]
		);
	}

	public function testVoteActorIdArgs()
	{
		// Test with user id and post id.
		$test_post_id       = 123;
		$test_user_id       = 456;
		$test_vote_actor_id = 789;

		$this->setupDbMockAndAssert(
			array(
				'vote_post_id'  => $test_post_id,
				'vote_user_id' => $test_user_id,
				'vote_actor_id' => $test_vote_actor_id
			),
			'SELECT * FROM wp_ap_votes WHERE 1=1 AND vote_post_id = ' . $test_post_id . ' AND vote_user_id = ' . $test_user_id . ' AND vote_actor_id = ' . $test_vote_actor_id,
			[]
		);

		// With just vote_actor_id.
		$this->setupDbMockAndAssert(
			array( 'vote_actor_id' => $test_vote_actor_id ),
			'SELECT * FROM wp_ap_votes WHERE 1=1 AND vote_actor_id = ' . $test_vote_actor_id,
			[]
		);

		// Test with array of vote_actor_id.
		$test_vote_actor_id = array( 789, 101 );

		Functions\expect( 'sanitize_comma_delimited')
			->andReturn( '789,101' );

		$this->setupDbMockAndAssert(
			array( 'vote_actor_id' => $test_vote_actor_id ),
			'SELECT * FROM wp_ap_votes WHERE 1=1 AND vote_actor_id IN (789,101)',
			[]
		);

		// Test with empty vote_actor_id.
		$this->setupDbMockAndAssert(
			array( 'vote_actor_id' => '' ),
			'SELECT * FROM wp_ap_votes WHERE 1=1',
			[]
		);

		// Test with empty vote_actor_id array.
		$this->setupDbMockAndAssert(
			array( 'vote_actor_id' => [] ),
			'SELECT * FROM wp_ap_votes WHERE 1=1',
			[]
		);
	}

	public function testVoteTypeArgs()
	{
		// Test with user id and post id.
		$test_post_id = 123;
		$test_user_id = 456;
		$test_vote_type = 'up';

		$this->setupDbMockAndAssert(
			array(
				'vote_post_id' => $test_post_id,
				'vote_user_id' => $test_user_id,
				'vote_type'    => $test_vote_type
			),
			'SELECT * FROM wp_ap_votes WHERE 1=1 AND vote_post_id = ' . $test_post_id . ' AND vote_user_id = ' . $test_user_id . ' AND vote_type = \'up\'',
			[],
            [
                'args' => [
					' AND vote_type = %s',
					'up'
				],
                'returnValue' => ' AND vote_type = \'up\'',
                'times' => 1
            ]
		);

		// With just vote_type.
		$this->setupDbMockAndAssert(
			array( 'vote_type' => $test_vote_type ),
			'SELECT * FROM wp_ap_votes WHERE 1=1 AND vote_type = \'up\'',
			[],
			[
                'args' => [
					' AND vote_type = %s',
					'up'
				],
                'returnValue' => ' AND vote_type = \'up\'',
                'times' => 1
            ]
		);

		// Test with array of vote_type.
		$test_vote_type = array( 'up', 'down' );

		Functions\expect( 'sanitize_comma_delimited')
			->andReturn( '\'up\',\'down\'' );

		$this->setupDbMockAndAssert(
			array( 'vote_type' => $test_vote_type ),
			'SELECT * FROM wp_ap_votes WHERE 1=1 AND vote_type IN (\'up\',\'down\')',
			[]
		);

		// Test with empty vote_type.
		$this->setupDbMockAndAssert(
			array( 'vote_type' => '' ),
			'SELECT * FROM wp_ap_votes WHERE 1=1',
			[]
		);

		// Test with empty vote_type array.
		$this->setupDbMockAndAssert(
			array( 'vote_type' => [] ),
			'SELECT * FROM wp_ap_votes WHERE 1=1',
			[]
		);
	}

}
