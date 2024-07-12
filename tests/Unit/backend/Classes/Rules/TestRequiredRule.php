<?php
namespace Tests\Unit\Functions\src\backend\Classes;

use AnsPress\Classes\Validator;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Brain\Monkey\Functions;

require_once PLUGIN_DIR . '/src/backend/autoloader.php';

/**
 * @covers AnsPress\Classes\Rules\RequiredRule
 * @package Tests\Unit
 */
class TestRequiredRule extends TestCase {
	public function testPassWhenValueIsSet() {
		$rule = new \AnsPress\Classes\Rules\RequiredRule();
		$value = 'qwert';
		$this->assertTrue(
			$rule->validate(
				'required',
				$value,
				[],
				new Validator(
					[
						'required' => 'qwert',
					],
					[
						'required' => 'required',
					]
				)
			)
		);
	}

	public function testFailWhenValueIsNotSet() {
		$rule = new \AnsPress\Classes\Rules\RequiredRule();
		$value = null;
		$this->assertFalse(
			$rule->validate(
				'required',
				$value,
				[],
				new Validator(
					[
						'required' => null,
					],
					[
						'required' => 'required',
					]
				)
			)
		);
	}
}
