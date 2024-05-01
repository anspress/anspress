<?php
namespace AnsPress\Tests\Unit\Classes;

use AnsPress\Core\Classes\Container;
use Mockery;
use Yoast\WPTestUtils\BrainMonkey\TestCase;

require_once PLUGIN_DIR . '/src/Classes/BaseService.php';

/**
 * @covers \AnsPress\Core\Classes\Container
 * @package Tests\Unit\Classes
 */
class TestContainer extends TestCase {
	protected function setUp(): void {
		parent::setUp();
		require_once PLUGIN_DIR . '/src/Classes/Container.php';
		require_once PLUGIN_DIR . '/src/Interfaces/ServiceInterface.php';
	}

	public function testMethodAndProperties() {
		$reflection = new \ReflectionClass( Container::class );
		$services = $reflection->getProperty( 'services' );

		$this->assertTrue( $services->isPrivate() );

		$this->assertTrue( $reflection->hasMethod( 'set' ) );
		$this->assertTrue( $reflection->hasMethod( 'get' ) );
	}

	/**
     * Test if service object is set correctly.
     */
    public function testSet() {
        $container = new Container();
        $service = new MockService();

        $container->set($service);

        $this->assertSame($service, $container->get(get_class($service)));
    }

	public function testGetAutoSet() {
		$container = new Container();
		$service = new MockService();

		$this->assertInstanceOf(MockService::class, $container->get(get_class($service)));
	}
}

/**
 * Mock service class for testing.
 */
class MockService extends \AnsPress\Core\Classes\BaseService {
    // Mock implementation
}
