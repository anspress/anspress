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
		$this->assertTrue(
			$rule->validate(
				'string',
				'abc',
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
		$this->assertFalse(
			$rule->validate(
				'string',
				123,
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
