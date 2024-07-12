<?php
namespace Tests\Unit\Functions\src\backend\Classes;

use AnsPress\Classes\Validator;
use Yoast\WPTestUtils\BrainMonkey\TestCase;

require_once PLUGIN_DIR . '/src/backend/autoloader.php';

/**
 * @covers AnsPress\Classes\Rules\ArrayRule
 * @package Tests\Unit
 */
class TestArrayRule extends TestCase {
	public function testPassWhenValidArray() {
		$rule = new \AnsPress\Classes\Rules\ArrayRule();

		$value = ['test1', 'test2'];
		$this->assertTrue(
			$rule->validate(
				'array',
				$value,
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

		$value = 'test1';
		$this->assertFalse(
			$rule->validate(
				'array',
				$value,
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
