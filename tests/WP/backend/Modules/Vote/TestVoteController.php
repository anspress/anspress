<?php

namespace Tests\WP\backend\Modules\Vote;

use AnsPress\Classes\Auth;
use AnsPress\Exceptions\ValidationException;
use AnsPress\Modules\Vote\VoteController;
use AnsPress\Modules\Vote\VoteService;
use AnsPress\Tests\WP\Testcases\Common;
use WP_REST_Request;
use WP_REST_Response;

use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * @covers AnsPress\Modules\Vote\VoteController
 * @package Tests\WP
 */
class TestVoteController extends TestCase {
	use Common;

	/**
     * Instance of the controller.
     *
     * @var VoteController
     */
    private $controller;

	/**
     * Setup test dependencies.
     */
    public function setUp() : void {
        parent::setUp();

        $this->controller = new VoteController( new VoteService() );
    }

	public function testInvalidNonce() {
		$this->expectException( \AnsPress\Exceptions\HTTPException::class );
		$this->expectExceptionMessage( 'Invalid nonce' );

		$_REQUEST['__ap_vote_nonce'] = 'invalid_nonce';

		$this->controller->createVote();
	}

	/**
	 * Test createVote method when user is not logged in.
	 */
	public function testUnauthorized() {
		$_REQUEST['__ap_vote_nonce'] = wp_create_nonce( 'ap_vote_nonce' );

		$response = $this->controller->createVote();

		$this->assertEquals(
			[
				'message' => 'Unauthorized'
			],
			$response->get_data()
		);

		$this->assertEquals( 401, $response->get_status() );
	}

	public function testFailWhenNoPermission() {
		$this->setRole( 'subscriber' );

		$this->expectException( \AnsPress\Exceptions\HTTPException::class );
		$this->expectExceptionMessage( 'Forbidden' );
		$_REQUEST['__ap_vote_nonce'] = wp_create_nonce( 'ap_vote_nonce' );

		$response = $this->controller->createVote();

		$this->assertEquals(
			[
				'message' => 'Forbidden'
			],
			$response->get_data()
		);

		$this->assertEquals( 401, $response->get_status() );
	}

	public function testFailedValidation() {
		$this->setRole( 'subscriber' );

		Auth::user()->add_cap( 'vote:create' );

		$_REQUEST['__ap_vote_nonce'] = wp_create_nonce( 'ap_vote_nonce' );

		$this->expectException( ValidationException::class );
		$this->expectExceptionMessage( 'Validation failed.' );

		try {
			$response = $this->controller->createVote();
		} catch ( ValidationException $e ) {
			$this->assertEquals(
				[
					"vote" => array(
						0 => "The vote field is required.",
						1 => "The vote must be an array."
					),
					"vote.vote_type" => array(
						0 => "The vote.vote_type field is required.",
						1 => "The vote.vote_type must be a string.",
						2 => "The vote.vote_type must be one of upvote, downvote"
					),
					"vote.vote_post_id" => array(
						0 => "The vote.vote_post_id field is required.",
						1 => "The vote.vote_post_id must be a number.",
						2 => "The selected vote.vote_post_id is invalid."
					)
				],
				$e->getErrors()
			);

			throw $e;
		}
	}

	public function testVoteTypeValidation() {
		$this->setRole( 'subscriber' );

		$postId = $this->factory()->post->create();

		Auth::user()->add_cap( 'vote:create' );

		$_REQUEST['__ap_vote_nonce'] = wp_create_nonce( 'ap_vote_nonce' );

		$_REQUEST['vote'] = [
			'vote_type' => 'invalid_vote_type',
			'vote_post_id' => $postId
		];

		$this->expectException( ValidationException::class );
		$this->expectExceptionMessage( 'Validation failed.' );

		try {
			$response = $this->controller->createVote();
		} catch ( ValidationException $e ) {

			$this->assertEquals(
				[
					"vote.vote_type" => array(
						0 => "The vote.vote_type must be one of upvote, downvote"
					)
				],
				$e->getErrors()
			);

			throw $e;
		}
	}

	public function testSuccess() {
		$this->setRole( 'subscriber' );

		$postId = $this->factory()->post->create();

		Auth::user()->add_cap( 'vote:create' );

		$_REQUEST['__ap_vote_nonce'] = wp_create_nonce( 'ap_vote_nonce' );

		$_REQUEST['vote'] = [
			'vote_type' => 'upvote',
			'vote_post_id' => $postId
		];

		$response = $this->controller->createVote();

		$this->assertArrayHasKey( 'vote', $response->get_data() );

		$this->assertEquals( 200, $response->get_status() );
	}
}
