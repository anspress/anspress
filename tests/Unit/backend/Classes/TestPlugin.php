<?php

namespace Tests\WP\backend\Classes;

use AnsPress\Classes\Container;
use AnsPress\Classes\Plugin;
use AnsPress\Interfaces\SingletonInterface;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Brain\Monkey\Functions;
use AnsPress\Classes\AbstractService;
use AnsPress\Modules\Config\ConfigService;

require_once PLUGIN_DIR . '/src/backend/autoloader.php';



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


	public function testProperties() {
		$plugin = Plugin::make(
			'test.php',
			'1.1.1',
			'33000',
			'7.4',
			'5.8',
			new Container()
		);

		$this->assertEquals( 'test.php', $plugin::getPluginFile() );

		$this->assertEquals( '1.1.1', $plugin::getPluginVersion() );

		$this->assertEquals( PHP_VERSION, $plugin::getCurrentPHPVersion() );

		$this->assertEquals( '5.8', $plugin::getMinWPVersion() );

		$this->assertEquals( '7.4', $plugin::getMinPHPVersion() );

		$this->assertEquals( '33000', $plugin::getDbVersion() );
	}

	public function testGetContainer() {
		$container = new Container();
		$plugin = Plugin::make(
			'test.php',
			'1.1.1',
			'33000',
			'7.4',
			'5.8',
			$container
		);

		$this->assertEquals( $container, $plugin::getContainer() );
	}

	public function testGetMethod() {
		$plugin = Plugin::make(
			'test.php',
			'1.1.1',
			'33000',
			'7.4',
			'5.8',
			new Container()
		);

		$this->assertInstanceOf( DummyClass::class, $plugin::get( DummyClass::class ) );
	}

	public function testGetMagicMethod() {
		$plugin = Plugin::make(
			'test.php',
			'1.1.1',
			'33000',
			'7.4',
			'5.8',
			new Container()
		);

		$this->assertEquals( 'test.php', $plugin::getPluginFile() );

		$this->assertEquals( '1.1.1', $plugin::getPluginVersion() );

		$this->expectExceptionMessage( 'Method not found.' );
		$plugin::invalidMethod();
	}

	public function testGetMagicMethodInvalidProperty() {
		$plugin = Plugin::make(
			'test.php',
			'1.1.1',
			'33000',
			'7.4',
			'5.8',
			new Container()
		);

		$this->expectExceptionMessage( 'Attribute not found.' );
		$plugin::getInvalidProperty();
	}

	public function testGetInstalledDbVersion() {
		Functions\expect( 'update_option' )->once()->andReturn( true );

		$container = new Container();

		$plugin = Plugin::make(
			'test.php',
			'1.1.1',
			'33000',
			'7.4',
			'5.8',
			$container
		);

		$container->set( ConfigService::class, function () {
			return new ConfigService( [
				'migration.installed_version' => [
					'type' => 'integer',
					'value' => 0
				]
			] );
		} );

		$plugin::updateInstalledDbVersion(3300001);

		$this->assertEquals( 33000, $plugin::getInstalledDbVersion() );
	}
}
