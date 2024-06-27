<?php

namespace Tests\WP\backend\Classes;

use AnsPress\Classes\AbstractModel;
use AnsPress\Classes\AbstractPolicy;
use AnsPress\Classes\Container;
use AnsPress\Classes\Plugin;
use AnsPress\Interfaces\SingletonInterface;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Brain\Monkey\Functions;
use AnsPress\Classes\AbstractService;
use AnsPress\Classes\Auth;
use AnsPress\Exceptions\GeneralException;
use AnsPress\Modules\Config\ConfigService;
use WP_User;

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

class SamplePolicy extends AbstractPolicy{
	const POLICY_NAME = 'sample';

	public function before( string $ability, ?WP_User $user, array $context = array() ): ?bool {
		if ( ! Auth::isLoggedIn() ) {
			return false;
		}

		return null;
	}

	public function view( WP_User $user, AbstractModel $model ) : bool {
		return true;
	}
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
