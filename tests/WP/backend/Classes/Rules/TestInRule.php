<?php
namespace Tests\WP\Functions\src\backend\Classes;

use AnsPress\Classes\Validator;
use Yoast\WPTestUtils\BrainMonkey\TestCase;

/**
 * @covers AnsPress\Classes\Rules\InRule
 * @package Tests\Unit
 */
class TestInRule extends TestCase {
	public function testPassWhenValidArray() {
		$rule = new \AnsPress\Classes\Rules\InRule();
		$this->assertTrue(
			$rule->validate(
				'test_field',
				'test1',
				['test1', 'test2'],
				new Validator(
					[
						'test_field' => 'test1',
					],
					[
						'test_field' => 'in:test1,test2',
					]
				)
			)
		);
	}

	public function testFailedWhenInvalidArray() {
		$rule = new \AnsPress\Classes\Rules\InRule();
		$this->assertFalse(
			$rule->validate(
				'test_field',
				'test3',
				['test1', 'test2'],
				new Validator(
					[
						'test_field' => 'test3',
					],
					[
						'test_field' => 'in:test1,test2',
					]
				)
			)
		);
	}
}
