<?php
namespace Tests\Unit\Functions\src\backend\Classes;

use AnsPress\Classes\Validator;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Brain\Monkey\Functions;

require_once PLUGIN_DIR . '/src/backend/autoloader.php';

/**
 * @covers AnsPress\Classes\Rules\MaxRule
 * @package Tests\Unit
 */
class TestMaxRule extends TestCase {
	public function testPassWhenValidMax() {
		$rule = new \AnsPress\Classes\Rules\MaxRule(10);
		$this->assertTrue(
			$rule->validate(
				'max',
				5,
				[10],
				new Validator(
					[
						'max' => 5,
					],
					[
						'max' => 'max:10',
					]
				)
			)
		);
	}

	public function testFailWhenInvalidMax() {
		$rule = new \AnsPress\Classes\Rules\MaxRule(10);
		$this->assertFalse(
			$rule->validate(
				'max',
				15,
				[10],
				new Validator(
					[
						'max' => 15,
					],
					[
						'max' => 'max:10',
					]
				)
			)
		);
	}

	public function testPassWhenValueIsString() {
		$rule = new \AnsPress\Classes\Rules\MaxRule(10);
		$this->assertTrue(
			$rule->validate(
				'max',
				[
					'foo' => 'one'
				],
				[10],
				new Validator(
					[
						'foo' => 'one',
					],
					[
						'max' => 'max:10',
					]
				)
			)
		);
	}

	public function testFailWhenValueIsString() {
		$rule = new \AnsPress\Classes\Rules\MaxRule(10);
		$this->assertFalse(
			$rule->validate(
				'max',
				'qwertzuiopasdfghjklyxcvbnm',
				[10],
				new Validator(
					[
						'foo' => 'qwertzuiopasdfghjklyxcvbnm',
					],
					[
						'max' => 'max:10',
					]
				)
			)
		);
	}

	public function testPassWhenValueIsArray() {
		$rule = new \AnsPress\Classes\Rules\MaxRule(10);
		$this->assertTrue(
			$rule->validate(
				'max',
				[
					'foo' => 'one',
					'bar' => 'two',
				],
				[2],
				new Validator(
					[
						'foo' => 'one',
						'bar' => 'two',
					],
					[
						'max' => 'max:2',
					]
				)
			)
		);
	}

	public function testFailWhenValueIsArray() {
		$rule = new \AnsPress\Classes\Rules\MaxRule(2);

		$this->assertFalse(
			$rule->validate(
				'max',
				[
					'foo' => 'one',
					'bar' => 'two',
					'baz' => 'three',
					'baz' => 'three',
				],
				[2],
				new Validator(
					[],
					[]
				)
			)
		);
	}

	public function testMessage() {
		$rule = new \AnsPress\Classes\Rules\MaxRule(10);
		$this->assertEquals('The :attribute may not be greater than 10.', $rule->message());
	}
}
