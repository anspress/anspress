<?php

namespace AnsPress\Tests\Unit\Classes;

use Brain\Monkey\Functions;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use AnsPress\Core\Classes\Plugin;
use Mockery;
use Brain\Monkey;

/**
 * @covers \AnsPress\Core\Classes\Plugin
 * @package Tests\Unit\Classes
 */
class TestPlugin extends TestCase {
	public function setUp(): void{
		parent::setUp();

		Functions\when('__')->justReturn('');
		Functions\when('esc_html')->justReturn('');
		Functions\when('get_bloginfo')->justReturn('6.5.1');

		require_once PLUGIN_DIR . '/src/Classes/Container.php';
		require_once PLUGIN_DIR . '/src/Classes/Plugin.php';
	}

	public function test_constructor(){
		$container = Mockery::mock('AnsPress\Core\Classes\Container');

		$this->expectExceptionMessage('Call to private AnsPress\Core\Classes\Plugin::__construct() from scope AnsPress\Tests\Unit\Classes\TestPlugin');

		new Plugin('7.0.0', '5.0.0', '5.0.0', '5.0.0', $container);
	}

	public function test_boot(){
		$container = Mockery::mock('AnsPress\Core\Classes\Container');

		Plugin::boot('7.0.0', '5.0.0', '5.0.0', '5.0.0', $container);

		$this->assertInstanceOf('AnsPress\Core\Classes\Plugin', Plugin::getInstance());

		// Calling boot again should do nothing.
		Plugin::boot('0.0.0', '0.0.0', '0.0.0', '0.0.0', $container);

		$this->assertEquals('7.0.0', Plugin::getMinPHPVersion());
		$this->assertEquals('5.0.0', Plugin::getMinWPVersion());
		$this->assertEquals('5.0.0', Plugin::getPluginVersion());
		$this->assertEquals('5.0.0', Plugin::getDbVersion());
	}

	public function test_getInstance(){
		Plugin::getInstance();
	}


}
