<?php
namespace Tests\Unit\Functions\src\backend\Classes;

use AnsPress\Classes\Validator;
use Yoast\WPTestUtils\BrainMonkey\TestCase;

require_once PLUGIN_DIR . '/src/backend/autoloader.php';

/**
 * @covers AnsPress\Classes\Rules\NumericRule
 * @package Tests\Unit
 */
class TestNumericRule extends TestCase {
	public function testPassWhenValueIsNumeric() {
		$rule = new \AnsPress\Classes\Rules\NumericRule();
		$value = '123';
		$this->assertTrue(
			$rule->validate(
				'numeric',
				$value,
				[],
				new Validator(
					[
						'numeric' => '123',
					],
					[
						'numeric' => 'numeric',
					]
				)
			)
		);
	}

	public function testFailWhenValueIsNotNumeric() {
		$rule = new \AnsPress\Classes\Rules\NumericRule();
		$value = 'abc';
		$this->assertFalse(
			$rule->validate(
				'numeric',
				$value,
				[],
				new Validator(
					[
						'numeric' => 'abc',
					],
					[
						'numeric' => 'numeric',
					]
				)
			)
		);
	}
}
