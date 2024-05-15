<?php

namespace Tests\WP\backend\Classes;

use AnsPress\Classes\Container;
use AnsPress\Classes\Plugin;
use Yoast\WPTestUtils\BrainMonkey\TestCase;

/**
 * @covers AnsPress\Classes\Plugin
 * @package Tests\WP
 */
class TestPlugin extends TestCase {

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

}
