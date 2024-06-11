<?php

namespace Tests\Unit\src\backend\Modules\Vote;

use AnsPress\Classes\Plugin;
use AnsPress\Modules\Vote\VoteModule;
use AnsPress\Tests\WP\Testcases\Common;
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

	public function testRegisterRoutes() {

		$module = Plugin::get( VoteModule::class);

		$module->register_hooks();

		$data = $this->getRestData(
			'/anspress/v1/vote', 'POST'
		);

		$this->assertEquals( ['message' => 'Invalid nonce'], $data->get_data() );
	}
}
