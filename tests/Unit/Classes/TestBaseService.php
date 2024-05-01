<?php

namespace Tests\Unit\Classes;

use AnsPress\Core\Classes\BaseService;
use AnsPress\Core\Classes\Container;
use Yoast\WPTestUtils\BrainMonkey\TestCase;

/**
 * @covers \AnsPress\Core\Classes\BaseService
 * @package Tests\Unit\Classes
 */
class TestBaseService extends TestCase {
	protected function setUp(): void {
		parent::setUp();
		require_once PLUGIN_DIR . '/src/Classes/Container.php';
		require_once PLUGIN_DIR . '/src/Classes/BaseService.php';
	}

	public function testRegisterHooksMock() {
		// Create a mock for the Main class
		$mainMock = $this->createMock(Container::class);

		// Create a partial mock for BaseService, mocking only the register_hooks method
		$baseServiceMock = $this->getMockBuilder(BaseService::class)
			->setConstructorArgs([$mainMock])
			->onlyMethods(['register_hooks'])
			->getMock();

		// Expect that the register_hooks method will be called once
		$baseServiceMock->expects($this->once())
			->method('register_hooks');

		// Call the register_hooks method
		$baseServiceMock->register_hooks();
	}
}
