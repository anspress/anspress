<?php
namespace Tests\Unit\Functions\src\backend\Classes;

use AnsPress\Classes\Validator;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Brain\Monkey\Functions;

require_once PLUGIN_DIR . '/src/backend/autoloader.php';

/**
 * @covers AnsPress\Classes\Rules\IntegerRule
 * @package Tests\Unit
 */
class TestIntegerRule extends TestCase {
	public function testPassWhenValidInteger() {
		$rule = new \AnsPress\Classes\Rules\IntegerRule();
		$value = 123;
		$this->assertTrue(
			$rule->validate(
				'integer',
				$value,
				[],
				new Validator(
					[
						'integer' => 123,
					],
					[
						'integer' => 'integer',
					]
				)
			)
		);
	}

	public function testFailWhenInvalidInteger() {
		$rule = new \AnsPress\Classes\Rules\IntegerRule();
		$value = 'fdg dfg dfg f';
		$this->assertFalse(
			$rule->validate(
				'integer',
				$value,
				[],
				new Validator(
					[
						'integer' => '123',
					],
					[
						'integer' => 'integer',
					]
				)
			)
		);
	}

	public function testMessage() {
		$rule = new \AnsPress\Classes\Rules\IntegerRule();
		$this->assertEquals('The :attribute must be an integer.', $rule->message());
	}
}
