<?php

namespace Anspress\Tests;

use AnsPress\Classes\AbstractAddon;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 *
 * @package Anspress\Tests
 */
class TestAbstractAddon extends TestCase {
	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress\Classes\AbstractAddon' );
		$this->assertFalse(
			$class->hasProperty( 'initialized' )
		);

		$this->assertTrue( $class->hasMethod( 'is_initialized' ) && $class->getMethod( 'is_initialized' )->isPublic() );
		$this->assertTrue( $class->hasMethod( 'init' ) && $class->getMethod( 'init' )->isPublic() );
	}

	public function testMethodExists() {
		// count the number of methods in the AbstractAddon class
		$class = new \ReflectionClass( 'AnsPress\Classes\AbstractAddon' );
		$methods = $class->getMethods();
		$this->assertCount( 10, $methods );

		$this->assertTrue( $class->hasMethod('is_initialized') );
		$this->assertTrue( $class->getMethod('is_initialized')->isPublic() );

		$this->assertTrue( $class->hasMethod('install') );
		$this->assertTrue( $class->getMethod('install')->isPublic() );
		$this->assertTrue( $class->getMethod('install')->isAbstract() );

		$this->assertTrue( $class->hasMethod('uninstall') );
		$this->assertTrue( $class->getMethod('uninstall')->isPublic() );
		$this->assertTrue( $class->getMethod('uninstall')->isAbstract() );

		$this->assertTrue( $class->hasMethod('default_options') );
		$this->assertTrue( $class->getMethod('default_options')->isProtected() );
		$this->assertTrue( $class->getMethod('default_options')->isAbstract() );

		$this->assertTrue( $class->hasMethod('is_initialized') );
		$this->assertTrue( $class->getMethod('is_initialized')->isAbstract() );

		$this->assertTrue( $class->hasMethod('set_initialized') );
		$this->assertTrue( $class->getMethod('set_initialized')->isAbstract() );

		$this->assertTrue( $class->hasMethod('init') );
		$this->assertTrue( $class->getMethod('init')->isPublic() );

		$this->assertTrue( $class->hasMethod('addon_file') );
		$this->assertTrue( $class->getMethod('addon_file')->isPublic() );

		$this->assertTrue( $class->hasMethod('pre_check_passed') );
		$this->assertTrue( $class->getMethod('pre_check_passed')->isPublic() );

		$this->assertTrue( $class->hasMethod('require_files') );
		$this->assertTrue( $class->getMethod('require_files')->isProtected() );

		$this->assertTrue( $class->hasMethod('add_filters_and_actions') );
		$this->assertTrue( $class->getMethod('add_filters_and_actions')->isProtected() );
	}

	public function testIsInitialized() {
		$addon = new class() extends AbstractAddon {
			protected static bool $initialized = false;
			public function install(): void {}
			public function uninstall(): void {}
			public function is_initialized(): bool {
				return self::$initialized;
			}

			public function set_initialized(): void {
				self::$initialized = true;
			}
			protected function default_options(): array { return []; }
			public function pre_check_passed(): bool {
				return true;
			}
		};

		$this->assertFalse( $addon->is_initialized() );

		$addon->init();

		$this->assertTrue( $addon->is_initialized() );
	}

	public function testPreTestPassed() {
		$addon = new class() extends AbstractAddon {
			protected static bool $initialized = false;
			public function install(): void {}
			public function uninstall(): void {}

			public function is_initialized(): bool {
				return self::$initialized;
			}

			public function set_initialized(): void {
				self::$initialized = true;
			}

			public function pre_check_passed(): bool {
				return true;
			}

			protected function default_options(): array {
				return [];
			}
		};

		$this->assertTrue( $addon->pre_check_passed() );
	}

	public function testInit() {
		$addon = new class() extends AbstractAddon {
			protected static bool $initialized = false;
			public function install(): void {}
			public function uninstall(): void {}

			public function is_initialized(): bool {
				return self::$initialized;
			}

			public function set_initialized(): void {
				self::$initialized = true;
			}

			public function pre_check_passed(): bool {
				return true;
			}

			protected function default_options(): array {
				return [
					'test_option' => 'test_value'
				];
			}
		};

		$addon->init();

		$this->assertArrayHasKey( 'test_option', ap_opt() );
		$this->assertEquals( 'test_value', ap_opt( 'test_option' ) );
	}

	public function testInitWithAddonAlreadyActive() {
		$addon = new class() extends AbstractAddon {
			protected static bool $initialized = false;
			public function install(): void {}
			public function uninstall(): void {}

			public function is_initialized(): bool {
				return self::$initialized;
			}

			public function set_initialized(): void {
				self::$initialized = true;
			}

			public function pre_check_passed(): bool {
				return true;
			}

			protected function default_options(): array {
				return [];
			}
		};

		$addon->init();

		// Assert throws.
		$this->expectExceptionMessage( 'Addon already initialized.' );
		$addon->init();
	}

	public function testInitWithPreCheckFailed() {
		$addon = new class() extends AbstractAddon {
			protected static bool $initialized = false;
			public function install(): void {}
			public function uninstall(): void {}

			public function is_initialized(): bool {
				return self::$initialized;
			}

			public function set_initialized(): void {
				self::$initialized = true;
			}

			public function pre_check_passed(): bool {
				return false;
			}

			protected function default_options(): array {
				return [];
			}
		};

	 	$this->assertFalse($addon->init());
	}

	public function testAddonFile() {
		$addon = new class() extends AbstractAddon {
			protected static bool $initialized = false;
			public function install(): void {}
			public function uninstall(): void {}

			public function is_initialized(): bool {
				return self::$initialized;
			}

			public function set_initialized(): void {
				self::$initialized = true;
			}

			public function pre_check_passed(): bool {
				return true;
			}

			protected function default_options(): array {
				return [];
			}
		};

		$this->assertEquals( 'class-abstract-addon.php', $addon->addon_file() );
	}
}
