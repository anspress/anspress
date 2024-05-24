<?php
namespace Tests\Unit\Functions\src\backend\Classes;

use AnsPress\Classes\Validator;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Brain\Monkey\Functions;

require_once PLUGIN_DIR . '/src/backend/autoloader.php';

/**
 * @covers AnsPress\Classes\Rules\EmailRule
 * @package Tests\Unit
 */
class TestEmailRule extends TestCase {
	public function testPassWhenValidEmail() {
		$rule = new \AnsPress\Classes\Rules\EmailRule();
		$this->assertTrue( $rule->validate( 'email', 'rah12@live.com', [], new Validator(
			[
				'email' => 'rah12@live.com',
			],
			[
				'email' => 'email',
			]
		) ) );
	}

	public function testFailWhenInvalidEmail() {
		$rule = new \AnsPress\Classes\Rules\EmailRule();
		$this->assertFalse( $rule->validate(
			'email',
			'rah12live.com',
			[],
			new Validator(
				[
					'email' => 'rah12@live.com',
				],
				[
					'email' => 'email',
				]
			)
		) );
	}

	public function testMessage() {
		$rule = new \AnsPress\Classes\Rules\EmailRule();
		$this->assertEquals( 'The :attribute must be a valid email address.', $rule->message() );
	}
}
