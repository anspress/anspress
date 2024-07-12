<?php
namespace Tests\Unit\Functions\src\backend\Classes;

use AnsPress\Classes\Validator;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
require_once PLUGIN_DIR . '/src/backend/autoloader.php';

/**
 * @covers AnsPress\Classes\Rules\MinRule
 * @package Tests\Unit
 */
class TestMinRule extends TestCase {
	public function testPassWhenValidMin() {
		$rule = new \AnsPress\Classes\Rules\MinRule(5);
		$value = 10;
		$this->assertTrue(
			$rule->validate(
				'min',
				$value,
				[5],
				new Validator(
					[
						'min' => 10,
					],
					[
						'min' => 'min:5',
					]
				)
			)
		);
	}

	public function testFailWhenInvalidMin() {
		$rule = new \AnsPress\Classes\Rules\MinRule(5);
		$value = 2;
		$this->assertFalse(
			$rule->validate(
				'min',
				$value,
				[5],
				new Validator(
					[
						'min' => 2,
					],
					[
						'min' => 'min:5',
					]
				)
			)
		);
	}

	public function testPassWhenValueIsString() {
		$rule = new \AnsPress\Classes\Rules\MinRule(5);
		$value = 'qwert';
		$this->assertTrue(
			$rule->validate(
				'min',
				$value,
				[5],
				new Validator(
					[
						'min' => [
							'foo' => 'one'
						],
					],
					[
						'min' => 'min:5',
					]
				)
			)
		);
	}

	public function testFailWhenValueIsString() {
		$rule = new \AnsPress\Classes\Rules\MinRule(5);
		$value = 'qwe';
		$this->assertFalse(
			$rule->validate(
				'min',
				$value,
				[5],
				new Validator(
					[
						'min' => 'qwe',
					],
					[
						'min' => 'min:5',
					]
				)
			)
		);
	}

	public function testPassWhenValueIsArray() {
		$rule = new \AnsPress\Classes\Rules\MinRule(5);
		$value = [
			'foo' => 'one',
			'bar' => 'two',
			'baz' => 'three',
			'qux' => 'four',
			'quux' => 'five',
		];
		$this->assertTrue(
			$rule->validate(
				'min',
				$value,
				[5],
				new Validator(
					[
						'min' => [
							'foo' => 'one',
							'bar' => 'two',
							'baz' => 'three',
							'qux' => 'four',
							'quux' => 'five',
						],
					],
					[
						'min' => 'min:5',
					]
				)
			)
		);
	}

	public function testFailWhenValueIsArray() {
		$rule = new \AnsPress\Classes\Rules\MinRule(5);
		$value = [
			'foo' => 'one',
			'bar' => 'two',
			'baz' => 'three',
			'qux' => 'four',
		];
		$this->assertFalse(
			$rule->validate(
				'min',
				$value,
				[5],
				new Validator(
					[
						'min' => [
							'foo' => 'one',
							'bar' => 'two',
							'baz' => 'three',
							'qux' => 'four',
						],
					],
					[
						'min' => 'min:5',
					]
				)
			)
		);
	}

	public function testMessage() {
		$rule = new \AnsPress\Classes\Rules\MinRule(5);
		$this->assertEquals('The :attribute must be at least 5.', $rule->message());
	}
}
