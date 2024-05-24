<?php
namespace Tests\Unit\src\backend\Classes\Rules;

use AnsPress\Classes\Rules\ExistsRule;
use AnsPress\Classes\Validator;
use Yoast\WPTestUtils\BrainMonkey\TestCase;

/**
 * @covers AnsPress\Classes\Rules\ExistsRule
 * @package Tests\WP
 */
class TestExistsRule extends TestCase {
	protected static $wpdb;

	public static function setUpBeforeClass(): void {
        parent::setUpBeforeClass();

        global $wpdb;
        self::$wpdb = $wpdb;

        // Set up your test data
        self::$wpdb->query("CREATE TABLE IF NOT EXISTS test_users (id INT AUTO_INCREMENT PRIMARY KEY, email VARCHAR(255) UNIQUE)");
        self::$wpdb->query("INSERT INTO test_users (email) VALUES ('existing@example.com')");
    }

    public static function tearDownAfterClass(): void {
        // Clean up the database
        self::$wpdb->query("DROP TABLE IF EXISTS test_users");
        parent::tearDownAfterClass();
    }

    public function testValidateExistingValue() {
        $rule = new ExistsRule('test_users', 'email');
        $this->assertTrue($rule->validate('email', 'existing@example.com', [], null));
    }

    public function testValidateNonExistingValue() {
        $rule = new ExistsRule('test_users', 'email');
        $this->assertFalse($rule->validate('email', 'nonexisting@example.com', [], null));
    }

	public function testRuleWithValidator() {
		$data = ['email' => 'existing@example.com'];
		$rules = ['email' => 'exists:test_users,email'];

		$validator = new Validator($data, $rules);

		$this->assertFalse($validator->fails());
	}

	public function testRuleWithValidatorFails() {
		$data = ['email' => 'nonexisting@example.com'];

		$rules = ['email' => 'exists:test_users,email'];

		$validator = new Validator($data, $rules);

		$this->assertTrue($validator->fails());

        $this->assertEquals(
            ['email' => ['The selected email is invalid.']],
            $validator->errors()
        );
    }

	public function testPassRuleWithoutColumn() {
		$data = ['email' => 'existing@example.com'];

		$rules = ['email' => 'exists:test_users'];

		$validator = new Validator($data, $rules);

		$this->assertFalse($validator->fails());
	}

	public function testFailRuleWithoutColumn() {
		$data = ['email' => 'nonexisting@example.com'];

		$rules = ['email' => 'exists:test_users'];

		$validator = new Validator($data, $rules);

		$this->assertTrue($validator->fails());

		$this->assertEquals(
			['email' => ['The selected email is invalid.']],
			$validator->errors()
		);
	}

}
