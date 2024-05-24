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

		$table = self::$wpdb->prefix . 'test_users';

        // Set up your test data
        self::$wpdb->query("CREATE TABLE IF NOT EXISTS {$table} (id INT AUTO_INCREMENT PRIMARY KEY, email VARCHAR(255) UNIQUE)");
        self::$wpdb->query("INSERT INTO {$table} (email) VALUES ('existing@example.com')");
    }

    public static function tearDownAfterClass(): void {
		$table = self::$wpdb->prefix . 'test_users';
        // Clean up the database
        self::$wpdb->query("DROP TABLE IF EXISTS $table");
        parent::tearDownAfterClass();
    }

    public function testValidateExistingValue() {
		$validator = new Validator(['email' => 'existing@example.com'], ['email' => 'exists:test_users,email']);
        $this->assertFalse($validator->fails());
    }

    public function testValidateNonExistingValue() {
		$validator = new Validator(['email' => 'nonexisting@example.com'], ['email' => 'exists:test_users,email']);
        $this->assertTrue($validator->fails());
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
