<?php

namespace Tests\Unit\Functions\src\backend\Classes;

use AnsPress\Classes\Logger;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers AnsPress\Classes\Logger
 * @package Tests\Unit
 */
class TestLogger extends TestCase {
	public function setUp() : void {
		parent::setUp();

		Functions\when('error_log')->alias(function( $message ) {
			echo $message;
		});

		Functions\expect('gmdate')
			->andReturn('[2023-05-15 14:08:26]');

		require_once PLUGIN_DIR . '/src/backend/Interfaces/SingletonInterface.php';
		require_once PLUGIN_DIR . '/src/backend/Classes/Logger.php';
	}
	/**
	 * Test `log()` method.
	 */
	public function testLog() {
		$logger = new Logger();
		$logger->log( Logger::LOG_LEVEL_ERROR, 'Test log message');

		$this->expectOutputString("AnsPress: [2023-05-15 14:08:26] [error] Test log message\n");

		$logger->log( Logger::LOG_LEVEL_ERROR, 'MMM', 'tests');

		$this->expectOutputString("AnsPress: [2023-05-15 14:08:26] [error] Test log message\nAnsPress: [2023-05-15 14:08:26] [error] MMM\ntests\n");
	}

	/**
	 * Test `formatData()` method.
	 */
	public function testFormatData() {
		$logger = new Logger();

		$data = [
			'key' => 'value',
			'key2' => 'value2',
		];

		$result = $logger->formatData($data);

		$this->assertEquals("Array\n(\n    [key] => value\n    [key2] => value2\n)\n", $result);
	}

	public function testCheckDebugLogWhenWPDebugIsTrue() {
		$logger = new Logger();
		$logger->log( Logger::LOG_LEVEL_DEBUG, 'Test log message');

		$this->expectOutputString("");

		define('WP_DEBUG', true);
		define('WP_DEBUG_LOG', false);

		$logger->log( Logger::LOG_LEVEL_DEBUG, 'Test log message');

		$this->expectOutputString("AnsPress: [2023-05-15 14:08:26] [debug] Test log message\n");
	}

	public function testError() {
		$logger = new Logger();
		$logger->error('Test error message');

		$this->expectOutputString("AnsPress: [2023-05-15 14:08:26] [error] Test error message\n");

		$logger->error('MMM', 'tests');

		$this->expectOutputString("AnsPress: [2023-05-15 14:08:26] [error] Test error message\nAnsPress: [2023-05-15 14:08:26] [error] MMM\ntests\n");
	}

	public function testWarning() {
		$logger = new Logger();
		$logger->warning('Test warning message');

		$this->expectOutputString("AnsPress: [2023-05-15 14:08:26] [warning] Test warning message\n");

		$logger->warning('MMM', 'tests');

		$this->expectOutputString("AnsPress: [2023-05-15 14:08:26] [warning] Test warning message\nAnsPress: [2023-05-15 14:08:26] [warning] MMM\ntests\n");
	}

	public function testInfo() {
		$logger = new Logger();
		$logger->info('Test info message');

		$this->expectOutputString("AnsPress: [2023-05-15 14:08:26] [info] Test info message\n");

		$logger->info('MMM', 'tests');

		$this->expectOutputString("AnsPress: [2023-05-15 14:08:26] [info] Test info message\nAnsPress: [2023-05-15 14:08:26] [info] MMM\ntests\n");
	}
}
