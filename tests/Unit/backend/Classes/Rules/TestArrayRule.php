<?php
namespace Tests\Unit\Functions\src\backend\Classes;

use AnsPress\Classes\Validator;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Brain\Monkey\Functions;

require_once PLUGIN_DIR . '/src/backend/autoloader.php';

/**
 * @covers AnsPress\Classes\Rules\ArrayRule
 * @package Tests\Unit
 */
class TestArrayRule extends TestCase {
	public function testPassWhenValidArray() {
		$rule = new \AnsPress\Classes\Rules\ArrayRule();
		$this->assertTrue(
			$rule->validate(
				'array',
				['test1', 'test2'],
				[],
				new Validator(
					[
						'array' => ['test1', 'test2'],
					],
					[
						'array' => 'array',
					]
				)
			)
		);
	}

	public function testFailWhenInvalidArray() {
		$rule = new \AnsPress\Classes\Rules\ArrayRule();
		$this->assertFalse(
			$rule->validate(
				'array',
				'test1',
				[],
				new Validator(
					[
						'array' => 'test1',
					],
					[
						'array' => 'array',
					]
				)
			)
		);
	}

	public function testMessage() {
		$rule = new \AnsPress\Classes\Rules\ArrayRule();
		$this->assertEquals('The :attribute must be an array.', $rule->message());
	}
}
