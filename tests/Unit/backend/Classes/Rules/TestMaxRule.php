<?php
namespace Tests\Unit\Functions\src\backend\Classes;

use AnsPress\Classes\Validator;
use Yoast\WPTestUtils\BrainMonkey\TestCase;

require_once PLUGIN_DIR . '/src/backend/autoloader.php';

/**
 * @covers AnsPress\Classes\Rules\MaxRule
 * @package Tests\Unit
 */
class TestMaxRule extends TestCase {
	public function testPassWhenValidMax() {
		$rule = new \AnsPress\Classes\Rules\MaxRule(10);
		$value = 5;
		$this->assertTrue(
			$rule->validate(
				'max',
				$value,
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
		$value = 15;
		$this->assertFalse(
			$rule->validate(
				'max',
				$value,
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
		$value = [
			'foo' => 'one'
		];
		$this->assertTrue(
			$rule->validate(
				'max',
				$value,
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
		$value = 'qwertzuiopasdfghjklyxcvbnm';
		$this->assertFalse(
			$rule->validate(
				'max',
				$value,
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
		$value = [
			'foo' => 'one',
			'bar' => 'two',
		];

		$this->assertTrue(
			$rule->validate(
				'max',
				$value,
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
		$value = [
			'foo' => 'one',
			'bar' => 'two',
			'baz' => 'three',
			'baz' => 'three',
		];
		$this->assertFalse(
			$rule->validate(
				'max',
				$value,
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
