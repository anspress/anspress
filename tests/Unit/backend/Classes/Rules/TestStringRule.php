<?php
namespace Tests\Unit\Functions\src\backend\Classes;

use AnsPress\Classes\Validator;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Brain\Monkey\Functions;

require_once PLUGIN_DIR . '/src/backend/autoloader.php';

/**
 * @covers AnsPress\Classes\Rules\StringRule
 * @package Tests\Unit
 */
class TestStringRule extends TestCase {
	public function testPassWhenValueIsString() {
		$rule = new \AnsPress\Classes\Rules\StringRule();
		$value = 'abc';
		$this->assertTrue(
			$rule->validate(
				'string',
				$value,
				[],
				new Validator(
					[
						'string' => 'abc',
					],
					[
						'string' => 'string',
					]
				)
			)
		);
	}

	public function testFailWhenValueIsNotString() {
		$rule = new \AnsPress\Classes\Rules\StringRule();
		$value = 123;
		$this->assertFalse(
			$rule->validate(
				'string',
				$value,
				[],
				new Validator(
					[
						'string' => 123,
					],
					[
						'string' => 'string',
					]
				)
			)
		);
	}
}
