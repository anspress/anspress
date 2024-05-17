<?php

namespace Tests\WP\backend\Classes;

use AnsPress\Classes\Container;
use AnsPress\Classes\Plugin;
use AnsPress\Interfaces\SingletonInterface;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Brain\Monkey\Functions;
use AnsPress\Classes\AbstractService;
use AnsPress\Modules\Config\ConfigService;


/**
 * Dummy class.
 */
class DummyClass implements SingletonInterface {
	protected $sampleService;

	/**
	 * Constructor class.
	 *
	 * @return void
	 */
	public function __construct(SampleService $sampleService) {
		$this->sampleService = $sampleService;
	}

	public function __clone()
	{

	}

	public function __wakeup()
	{

	}

	/**
	 * Get the sample service.
	 *
	 * @return SampleService
	 */
	public function getSampleService(): SampleService {
		return $this->sampleService;
	}
}



/**
 * Dummy class.
 */
class SampleService extends AbstractService {

}


/**
 * @covers AnsPress\Classes\Plugin
 * @package Tests\WP
 */
class TestPlugin extends TestCase {
	public function setUp() : void {
		parent::setUp();
	}

	public function testGetPathTo() {
		$this->assertEquals( dirname( Plugin::getPluginFile() ) . '/test.php', Plugin::getPathTo( 'test.php' ) );
	}
}
