<?php

namespace Tests\Unit\src\backend\Classes;

use AnsPress\Classes\Container;
use AnsPress\Tests\Unit\src\backend\Classes\DummyClass;
use AnsPress\Tests\Unit\src\backend\Classes\SampleService;
use Mockery;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Brain\Monkey\Functions;
use wpdb;

class SampleClassWithoutImplimentation {
}

/**
 * @covers AnsPress\Classes\Container
 * @package Tests\Unit
 */
class TestContainer extends TestCase {

	protected function setUp() : void {
		parent::setUp();

		require_once __DIR__ . '/DummyClass.php';
		require_once __DIR__ . '/SampleService.php';
	}

	public function testSetValidClass() {
		$container = new Container();

		$container->set( DummyClass::class );

		$this->assertInstanceOf( DummyClass::class, $container->get( DummyClass::class ) );

		$this->assertInstanceOf( SampleService::class, $container->get( DummyClass::class )->getSampleService() );
	}

	public function testSetInvalidClass() {
		$container = new Container();

		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'Not a valid class.' );
		$container->set( 'InvalidClass' );
	}

	public function testBuildReflectionException() {
		$container = new Container();

		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'Target class [InvalidClass] does not exist.' );
		$container->build( 'InvalidClass' );
	}

	public function testBuildForPrmitiveType() {
		$container = new Container();

		$classWithPrimitiveType = new class("test", 22) {
			public function __construct( public string $string, public int $int ) {
			}
		};

		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'Missing type hint for string. Please provide a class type.' );
		$container->get( $classWithPrimitiveType::class );
	}

	public function testBuildForNonSingletonClasses() {
		$container = new Container();

		$classWithPrimitiveType = new class(new SampleClassWithoutImplimentation()) {
			public function __construct( public SampleClassWithoutImplimentation $sampleClassWithoutImplimentation ) {
			}
		};

		$container->set( SampleService::class );

		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'Only classes which impliments SingletonInface can be used as dependency. For sampleClassWithoutImplimentation. Please provide a valid class type.' );
		$container->get( $classWithPrimitiveType::class );
	}

	public function testGetAll() {
		$container = new Container();

		$container->set( DummyClass::class );
		$container->set( SampleService::class );

		$this->assertCount( 2, $container->getAll() );
	}

}
