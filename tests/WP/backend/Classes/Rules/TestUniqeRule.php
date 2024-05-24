<?php
namespace Tests\Unit\src\backend\Classes\Rules;

use AnsPress\Classes\Rules\UniqueRule;
use AnsPress\Classes\Validator;
use Yoast\WPTestUtils\BrainMonkey\TestCase;

/**
 * @covers AnsPress\Classes\Rules\UniqueRule
 * @package Tests\WP
 */
class TestUniqueRule extends TestCase {
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

    public function testValidateUniqueValue() {
		$validator = new Validator(['email' => 'unique@example.com'], ['email' => 'unique:test_users,email']);
        $this->assertFalse($validator->fails());
    }

	public function testValidateNonUniqueValue() {
		$validator = new Validator(['email' => 'existing@example.com'], ['email' => 'unique:test_users,email']);
		$this->assertTrue($validator->fails());
	}

    public function testValidateUniqueValueWithIgnore() {
        $validator = new Validator(['email' => 'existing@example.com'], ['email' => 'unique:test_users,email,1']);
        $this->assertFalse($validator->fails());
    }

    public function testValidateNonUniqueValueWithIgnore() {
        // Insert a new user to test ignoring
        self::$wpdb->query("INSERT INTO test_users (email) VALUES ('another@example.com')");

		$validator = new Validator(['email' => 'another@example.com'], ['email' => 'unique:test_users,email,1']);
		$this->assertTrue($validator->fails());
    }

	public function testUniqueValuePasses() {
        $data = ['email' => 'unique@example.com'];
        $rules = ['email' => 'unique:test_users,email'];

        $validator = new Validator($data, $rules);

        $this->assertFalse($validator->fails());
    }

	public function testNonUniqueValueFails() {
        $data = ['email' => 'existing@example.com'];
        $rules = ['email' => 'unique:test_users,email'];

        $validator = new Validator($data, $rules);

        $this->assertTrue($validator->fails());
    }

	public function testUniqueValuePassesWithIgnore() {
        $data = ['email' => 'existing@example.com'];
        $rules = ['email' => 'unique:test_users,email,1'];

        $validator = new Validator($data, $rules);

        $this->assertFalse($validator->fails());
    }

	public function testNonUniqueValueFailsWithIgnore() {
        $data = ['email' => 'another@example.com'];

        $rules = ['email' => 'unique:test_users,email,1'];

        $validator = new Validator($data, $rules);

        $this->assertTrue($validator->fails());

		$this->assertEquals(['email' => ['The email has already been taken.']], $validator->errors());
    }

	public function testPassRuleWithoutColumn() {
		$data = ['email' => 'nonexisting@example.com'];

		$rules = ['email' => 'unique:test_users'];

		$validator = new Validator($data, $rules);

		$this->assertFalse($validator->fails());
	}

	public function testFailRuleWithoutColumn() {
		$data = ['email' => 'existing@example.com'];

		$rules = ['email' => 'unique:test_users'];

		$validator = new Validator($data, $rules);

		$this->assertTrue($validator->fails());

		$this->assertEquals(
			['email' => ['The email has already been taken.']],
			$validator->errors()
		);
	}
}
