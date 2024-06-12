<?php

namespace Tests\WP\backend\Modules\Vote;

use AnsPress\Classes\Auth;
use AnsPress\Classes\Plugin;
use AnsPress\Exceptions\ValidationException;
use AnsPress\Modules\Vote\VoteController;
use AnsPress\Modules\Vote\VoteModel;
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

	private $request;

	/**
     * Setup test dependencies.
     */
    public function setUp() : void {
        parent::setUp();

        $this->controller = new VoteController( new VoteService() );
		$this->request = $this->getMockBuilder('WP_REST_Request')
			->disableOriginalConstructor()
			->getMock();


		$this->controller->setRequest( $this->request );
    }

	public function testInvalidNonce() {
		$this->expectException( \AnsPress\Exceptions\HTTPException::class );
		$this->expectExceptionMessage( 'Invalid nonce' );

		$this->controller->setRequest( $this->request );

		$this->controller->createVote();
	}

	public function testFailWhenNoPermission() {
		$this->setRole( 'subscriber' );

		$this->request->method('get_param')
			->willReturn(wp_create_nonce( 'ap_vote_nonce' ));

		$this->expectException( \AnsPress\Exceptions\HTTPException::class );
		$this->expectExceptionMessage( 'Forbidden' );

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

		$this->request->method('get_param')
			->willReturn(wp_create_nonce( 'ap_vote_nonce' ));

		$this->request->method('get_params')
			->willReturn([]);

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

		// Mocked WP_REST_Request.
		$this->request->method('get_param')
			->with('__ap_vote_nonce')
			->willReturn(
				wp_create_nonce( 'ap_vote_nonce' )
			);

		$this->request->method('get_params')
			->willReturn(
				[
					'vote' => [
						'vote_type'    => 'invalid_vote_type',
						'vote_post_id' => $postId
					]
				]
			);

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

		$this->request->method('get_params')
			->willReturn(
				[
					'vote' => [
						'vote_type'    => 'upvote',
						'vote_post_id' => $postId
					]
				]
			);

		$this->request->method('get_param')
			->with('__ap_vote_nonce')
			->willReturn(
				wp_create_nonce( 'ap_vote_nonce' ),
			);

		$response = $this->controller->createVote();

		$this->assertArrayHasKey( 'vote', $response->get_data() );

		$this->assertEquals( 200, $response->get_status() );
	}

	public function testUndoVoteNonce() {
		$this->expectException( \AnsPress\Exceptions\HTTPException::class );
		$this->expectExceptionMessage( 'Invalid nonce' );

		// Mocked WP_REST_Request.
		$this->request->method('get_param')
			->willReturn( 'invalid_nonce' );

		$this->controller->undoVote();
	}

	public function testUndoVoteUnauthorized() {
		$this->request->method('get_param')
			->willReturn( wp_create_nonce( 'ap_vote_nonce' ) );

		$response = $this->controller->undoVote();

		$this->assertEquals(
			[
				'message' => 'Unauthorized'
			],
			$response->get_data()
		);

		$this->assertEquals( 401, $response->get_status() );
	}

	// public function testUndoVoteNoPermission() {
	// 	$this->setRole( 'subscriber' );

	// 	$this->expectException( \AnsPress\Exceptions\HTTPException::class );
	// 	$this->expectExceptionMessage( 'Forbidden' );

	// 	$_REQUEST['__ap_vote_nonce'] = wp_create_nonce( 'ap_vote_nonce' );

	// 	$response = $this->controller->undoVote();

	// 	$this->assertEquals(
	// 		[
	// 			'message' => 'Forbidden'
	// 		],
	// 		$response->get_data()
	// 	);

	// 	$this->assertEquals( 401, $response->get_status() );
	// }

	public function testUndoVoteFailedValidation() {
		$this->setRole( 'subscriber' );

		$this->expectException( ValidationException::class );
		$this->expectExceptionMessage( 'Validation failed.' );

		$this->request->method('get_param')
			->willReturn( wp_create_nonce( 'ap_vote_nonce' ) );

		$this->request->method('get_params')
			->willReturn([]);

		try {
			$response = $this->controller->undoVote();
		} catch ( ValidationException $e ) {
			$this->assertEquals(
				[
					"vote.vote_post_id" => array(
						0 => "The vote.vote_post_id field is required.",
						1 => "The vote.vote_post_id must be a number.",
						2 => "The selected vote.vote_post_id is invalid."
					),
					"vote.vote_type" => array(
						0 => "The vote.vote_type field is required.",
						1 => "The vote.vote_type must be a string.",
						2 => "The vote.vote_type must be one of upvote, downvote"
					)
				],
				$e->getErrors()
			);

			throw $e;
		}
	}

	public function testUndoVoteFailedValidationVoteNotFound() {
		$this->setRole( 'subscriber' );

		$postId = $this->factory()->post->create();

		$this->request->method('get_param')
			->willReturn( wp_create_nonce( 'ap_vote_nonce' ) );

		$this->request->method('get_params')
			->willReturn( [
				'vote' => [
					'vote_type'       => 'upvote',
					'vote_post_id'    => $postId,
				]
			] );

		$this->controller->setRequest( $this->request );

		$this->expectException( \AnsPress\Exceptions\HTTPException::class );
		$this->expectExceptionMessage( 'Failed to undo vote' );

		$this->controller->undoVote();
	}

	public function testUndoVoteSuccess() {
		$this->setRole( 'subscriber' );

		Auth::user()->add_cap( 'vote:delete' );

		$postId = $this->factory()->post->create();

		// Add vote.
		$vote = VoteModel::create([
			'vote_user_id'  => get_current_user_id(),
			'vote_rec_user' => get_current_user_id(),
			'vote_type'     => 'vote',
			'vote_post_id'  => $postId,
			'vote_value'    => 1
		]);

		$this->request->method('get_param')
			->willReturn( wp_create_nonce( 'ap_vote_nonce' ) );

		$this->request->method('get_params')
			->willReturn([
				'vote' => [
					'vote_type'    => 'upvote',
					'vote_post_id' => $postId
				]
			]);


		$this->controller->setRequest( $this->request );

		$response = $this->controller->undoVote();

		$this->assertEquals( 200, $response->get_status() );

		$this->assertArrayHasKey( 'vote', $response->get_data() );

		$this->assertNull( VoteModel::find( $vote->vote_id ) );
	}

	public function testGetPostVotesSuccess() {
		$postId = $this->factory()->post->create();

		$this->setRole( 'subscriber' );

		VoteModel::create([
			'vote_user_id'  => get_current_user_id(),
			'vote_rec_user' => get_current_user_id(),
			'vote_type'     => 'vote',
			'vote_post_id'  => $postId,
			'vote_value'    => 1
		]);

		// Unrelated.
		VoteModel::create([
			'vote_user_id'  => get_current_user_id(),
			'vote_rec_user' => get_current_user_id(),
			'vote_type'     => 'vote',
			'vote_post_id'  => $this->factory()->post->create(),
			'vote_value'    => 1
		]);

		$this->request->method('get_param')
			->with('post_id')
			->willReturn( $postId );

		$response = $this->controller->getPostVotes();

		$this->assertEquals( 200, $response->get_status() );

		$this->assertArrayHasKey( 'votes', $response->get_data() );

		$this->assertEquals( 1, $response->get_data()['votes'] );
	}

	public function testGetPostVotesUnauthorized() {
		$postId = $this->factory()->post->create();

		$response = $this->controller->getPostVotes( $postId );

		$this->assertEquals( 401, $response->get_status() );

		$this->assertEquals(
			[
				'message' => 'Unauthorized'
			],
			$response->get_data()
		);
	}
}
