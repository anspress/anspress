<?php
namespace AnsPress\Tests\Unit\Functions;

use Mockery;
use wpdb;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers ::ap_vote_insert
 */
class TestApVoteInsert extends TestCase {
    public static function setUpBeforeClass(): void
	{
        parent::set_up_before_class();
        self::makeDoublesForUnavailableClasses( [ wpdb::class ] );
    }

	protected function setUp(): void {
		parent::setUp();

		require_once PLUGIN_DIR . '/includes/votes.php';
	}

    public function testVoteInsertSuccess() {
        global $wpdb;

		Functions\expect( 'current_time' )
			->with( 'mysql' )
			->andReturn( '1234-12-12 12:12:12' );

		$wpdb = Mockery::mock( wpdb::class );

        // Mocking the $wpdb->insert method
        $wpdb->insert = function( $table, $data, $format ) {
            return true; // Mock success
        };

		$wpdb->prefix = 'wp_';

		$wpdb->ap_votes = $wpdb->prefix . 'ap_votes';

		$wpdb->shouldReceive( 'insert' )
			->once()
			->withArgs( [
				$wpdb->ap_votes,
				[
					'vote_post_id'  => 123,
					'vote_user_id'  => 456,
					'vote_rec_user' => 0,
					'vote_type'     => 'vote',
					'vote_value'    => '',
					'vote_date'     => '1234-12-12 12:12:12'
				],
				[ '%d', '%d', '%d', '%s', '%s', '%s' ]
			])
			->andReturn( true );

        // Call the function
        $result = ap_vote_insert( 123, 456 );

        // Assert the result
        $this->assertTrue( $result );
    }

	public function testWhenInsertFails() {
		global $wpdb;

		Functions\expect( 'current_time' )
			->with( 'mysql' )
			->andReturn( '1234-12-12 12:12:12' );

		$wpdb = Mockery::mock( wpdb::class );

		// Mocking the $wpdb->insert method
		$wpdb->insert = function( $table, $data, $format ) {
			var_dump($table);
			return false; // Mock failure
		};

		$wpdb->prefix = 'wp_';

		$wpdb->ap_votes = $wpdb->prefix . 'ap_votes';

		$wpdb->shouldReceive( 'insert' )
			->once()
			->andReturn( false );

		// Call the function
		$result = ap_vote_insert( 123, 456 );

		// Assert the result
		$this->assertFalse( $result );
	}

	public function testCurrentUser() {
		global $wpdb;

		Functions\expect( 'current_time' )
			->with( 'mysql' )
			->andReturn( '1234-12-12 12:12:12' );

		Functions\expect( 'get_current_user_id' )
			->andReturn( 999 );

		$wpdb = Mockery::mock( wpdb::class );

		// Mocking the $wpdb->insert method
		$wpdb->insert = function( $table, $data, $format ) {
			return true; // Mock success
		};

		$wpdb->prefix = 'wp_';

		$wpdb->ap_votes = $wpdb->prefix . 'ap_votes';

		$wpdb->shouldReceive( 'insert' )
			->once()
			->withArgs( [
				$wpdb->ap_votes,
				[
					'vote_post_id'  => 123,
					'vote_user_id'  => 999,
					'vote_rec_user' => 0,
					'vote_type'     => 'vote',
					'vote_value'    => '',
					'vote_date'     => '1234-12-12 12:12:12'
				],
				[ '%d', '%d', '%d', '%s', '%s', '%s' ]
			])
			->andReturn( true );

		// Call the function
		$result = ap_vote_insert( 123, false, 'vote' );

		// Assert the result
		$this->assertTrue( $result );
	}
}
