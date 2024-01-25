<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAdminFunctions extends TestCase {

	use Testcases\Common;

	/**
	 * @covers ::ap_flagged_posts_count
	 */
	public function testAPFlaggedPostsCount() {
		$ids = $this->insert_answers( [], [], 5 );

		// Test begins.
		// Without adding any flag.
		$result = ap_flagged_posts_count();
		$this->assertEquals( 0, $result->publish );
		$this->assertEquals( 0, $result->future );
		$this->assertEquals( 0, $result->draft );
		$this->assertEquals( 0, $result->pending );
		$this->assertEquals( 0, $result->private );
		$this->assertEquals( 0, $result->trash );
		$this->assertEquals( 0, $result->{'auto-draft'} );
		$this->assertEquals( 0, $result->inherit );
		$this->assertEquals( 0, $result->{'request-pending'} );
		$this->assertEquals( 0, $result->{'request-confirmed'} );
		$this->assertEquals( 0, $result->{'request-failed'} );
		$this->assertEquals( 0, $result->{'request-completed'} );
		$this->assertEquals( 0, $result->moderate );
		$this->assertEquals( 0, $result->private_post );
		$this->assertEquals( 0, $result->total );

		// With adding flag.
		// Test 1.
		ap_add_flag( $ids['question'] );
		ap_update_flags_count( $ids['question'] );
		ap_add_flag( $ids['answers'][3] );
		ap_update_flags_count( $ids['answers'][3] );
		ap_add_flag( $ids['answers'][4] );
		ap_update_flags_count( $ids['answers'][4] );
		$result = ap_flagged_posts_count();
		$this->assertEquals( 3, $result->publish );
		$this->assertEquals( 3, $result->total );

		// Test 2.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'private' ] );
		ap_add_flag( $question_id );
		ap_update_flags_count( $question_id );
		$result = ap_flagged_posts_count();
		$this->assertEquals( 1, $result->private );
		$this->assertEquals( 4, $result->total );

		// Test 3.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'moderate' ] );
		ap_add_flag( $question_id );
		ap_update_flags_count( $question_id );
		$result = ap_flagged_posts_count();
		$this->assertEquals( 1, $result->moderate );
		$this->assertEquals( 5, $result->total );

		// Test 4.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'publish' ] );
		ap_add_flag( $question_id );
		ap_update_flags_count( $question_id );
		$result = ap_flagged_posts_count();
		$this->assertEquals( 4, $result->publish );
		$this->assertEquals( 6, $result->total );

		// Test 5.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'moderate' ] );
		ap_add_flag( $question_id );
		ap_update_flags_count( $question_id );
		$result = ap_flagged_posts_count();
		$this->assertEquals( 2, $result->moderate );
		$this->assertEquals( 7, $result->total );

		// Test 6.
		$result = ap_flagged_posts_count();
		$this->assertEquals( 4, $result->publish );
		$this->assertEquals( 0, $result->future );
		$this->assertEquals( 0, $result->draft );
		$this->assertEquals( 0, $result->pending );
		$this->assertEquals( 1, $result->private );
		$this->assertEquals( 0, $result->trash );
		$this->assertEquals( 0, $result->{'auto-draft'} );
		$this->assertEquals( 0, $result->inherit );
		$this->assertEquals( 0, $result->{'request-pending'} );
		$this->assertEquals( 0, $result->{'request-confirmed'} );
		$this->assertEquals( 0, $result->{'request-failed'} );
		$this->assertEquals( 0, $result->{'request-completed'} );
		$this->assertEquals( 2, $result->moderate );
		$this->assertEquals( 0, $result->private_post );
		$this->assertEquals( 7, $result->total );
	}
}
