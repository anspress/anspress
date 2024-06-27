<?php

namespace Tests\Unit\src\backend\Modules\Vote;

use AnsPress\Classes\Plugin;
use AnsPress\Modules\Vote\VoteModule;
use AnsPress\Tests\WP\Testcases\Common;
use InvalidArgumentException;
use SebastianBergmann\RecursionContext\InvalidArgumentException as RecursionContextInvalidArgumentException;
use PHPUnit\Framework\ExpectationFailedException;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * @covers AnsPress\Modules\Vote\VoteModule
 * @package Tests\WP
 */
class TestVoteModule extends TestCase {
	use Common;

	public function setUp() : void
	{
		parent::setUp();

		$this->setUpRestServer();
	}

	public function tearDown() : void
	{
		parent::tearDown();

		$this->tearDownRestServer();
	}

	public function testRegisterHooks() {
		$module = Plugin::get( VoteModule::class);

		$module->register_hooks();

		$this->assertSame( 10, has_action( 'rest_api_init', array( $module, 'registerRoutes' ) ) );
	}

	/**
	 * @covers \AnsPress\Modules\Vote\VoteController::createVote
	 * @return void
	 * @throws InvalidArgumentException
	 * @throws RecursionContextInvalidArgumentException
	 * @throws ExpectationFailedException
	 */
	public function testCreateVoteRoute() {
		$postId = $this->factory()->post->create( [ 'post_type' => 'question' ] );

		$data = $this->getRestData(
			'/anspress/v1/post/' . $postId .'/actions/vote/upvote',
			'POST'
		);

		$this->assertEquals( ['message' => 'Invalid nonce'], $data->get_data() );
	}

	/**
	 * @covers \AnsPress\Modules\Vote\VoteController::undoVote
	 * @return void
	 * @throws InvalidArgumentException
	 * @throws RecursionContextInvalidArgumentException
	 * @throws ExpectationFailedException
	 */
	public function testUndoVoteRoute() {
		$postId = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$data = $this->getRestData(
			'/anspress/v1/post/' . $postId . '/actions/undo-vote',
			'POST'
		);

		$this->assertEquals( ['message' => 'Invalid nonce'], $data->get_data() );
	}

	/**
	 * @covers \AnsPress\Modules\Vote\VoteController::getPostVotes
	 * @return void
	 * @throws InvalidArgumentException
	 * @throws RecursionContextInvalidArgumentException
	 * @throws ExpectationFailedException
	 */
	public function testGetPostVotesRoute() {
		$this->setRole( 'subscriber' );
		$postId = $this->factory()->post->create( [ 'post_type' => 'question' ] );

		$data = $this->getRestData(
			'/anspress/v1/post/' . $postId . '/meta/votes',
			'GET'
		);

		$this->assertEquals( ['votes' => 0], $data->get_data() );
	}
}
