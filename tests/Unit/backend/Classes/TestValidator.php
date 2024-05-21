<?php
/**
 * Test validator.
 */

namespace Tests\Unit\Functions\src\backend\Classes;

use AnsPress\Classes\Validator;
use AnsPress\Exceptions\ValidationException;
use AnsPress\Interfaces\ValidationRuleInterface;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Brain\Monkey\Functions;

require_once PLUGIN_DIR . '/src/backend/autoloader.php';

/**
 * @covers AnsPress\Classes\Validator
 * @package Tests\Unit
 */
class TestValidator extends TestCase {

	public function testValidatePasses()
    {
		Functions\expect('wp_sprintf')
			->andReturn('The %s field is required.', 'The %s field is required.', 'The %s field is required.');

        $data = [
            'name'  => 'John Doe',
            'email' => 'john.doe@example.com',
            'age'   => 25,
        ];

        $rules = [
            'name'  => 'required|string',
            'email' => 'required|email',
            'age'   => 'required|integer|min:18',
        ];

        $validator = new Validator($data, $rules);

        $this->assertTrue($validator->validate());

		$this->assertEmpty($validator->errors());

		$this->assertEquals(
			[
				'name'  => 'John Doe',
				'email' => 'john.doe@example.com',
				'age'   => 25,
			],
			$validator->validated()
		);
    }

	public function testValidateFails()
    {
        $data = [
            'name'  => '',
            'email' => 'not-an-email',
            'age'   => 16,
        ];

        $rules = [
            'name'  => 'required|string',
            'email' => 'required|email',
            'age'   => 'required|integer|min:18',
        ];

        $validator = new Validator($data, $rules);

        $this->expectException(ValidationException::class);

        try {
            $validator->validate();
        } catch (ValidationException $e) {
            $errors = $e->getErrors();
            $this->assertArrayHasKey('name', $errors);
            $this->assertArrayHasKey('email', $errors);
            $this->assertArrayHasKey('age', $errors);
            throw $e;
        }

		$this->assertEmpty($validator->validated());
    }

	public function testCustomMessages()
    {
        $data = [
            'email' => 'not-an-email',
        ];

        $rules = [
            'email' => 'required|email',
        ];

        $customMessages = [
            'email.email' => 'The email format is invalid OVERRIDEN.',
        ];

        $validator = new Validator($data, $rules, $customMessages);

        $this->expectException(ValidationException::class);

        try {
            $validator->validate();
        } catch (ValidationException $e) {
            $errors = $e->getErrors();
            $this->assertEquals('The email format is invalid OVERRIDEN.', $errors['email'][0]);
            throw $e;
        }
    }

	public function testCustomAttributes()
    {
        $data = [
            'email_address' => 'not-an-email',
        ];

        $rules = [
            'email_address' => 'required|email',
        ];

        $customAttributes = [
            'email_address' => 'Email Address',
        ];

        $validator = new Validator($data, $rules, [], $customAttributes);

        $this->expectException(ValidationException::class);

        try {
            $validator->validate();
        } catch (ValidationException $e) {
            $errors = $e->getErrors();
            $this->assertStringContainsString('Email Address', $errors['email_address'][0]);
            throw $e;
        }
    }

	public function testNestedValidation()
    {
        $data = [
            'user' => [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'address' => [
                    'street' => '123 Main St',
                    'city' => 'Anytown'
                ],
            ],
        ];

        $rules = [
            'user.name'           => 'required|string',
            'user.email'          => 'required|email',
            'user.address.street' => 'required|string',
            'user.address.city'   => 'required|string',
        ];

        $validator = new Validator($data, $rules);

        $this->assertTrue($validator->validate());

		$this->assertEquals(
			$data,
			$validator->validated()
		);
    }

	public function testNullableField()
    {
        $data = [
            'bio' => null,
        ];

        $rules = [
            'bio' => 'nullable',
        ];

        $validator = new Validator($data, $rules);

        $this->assertTrue($validator->validate());
    }

	public function testInvalidData()
    {
        $data = [
            'bio' => 12345,
        ];

        $rules = [
            'bio' => 'nullable|string',
        ];

        $validator = new Validator($data, $rules);

        $this->expectException(ValidationException::class);

        try {
            $validator->validate();
        } catch (ValidationException $e) {
            $errors = $e->getErrors();
            $this->assertArrayHasKey('bio', $errors);
            throw $e;
        }

		$this->assertEmpty($validator->validated());
    }

	public function testValidationRuleInterface()
    {
        $data = [
            'username' => 'validuser',
        ];

        $rules = [
            'username' => [new class implements ValidationRuleInterface {
				public function ruleName(): string
				{
					return 'validuser';
				}

                public function validate($attribute, $value, $parameters, $validator): bool
                {
                    return $value === 'validuser';
                }

                public function message($attribute, $parameters): string
                {
                    return "The {$attribute} is invalid.";
                }
            }],
        ];

        $validator = new Validator($data, $rules);

        $validator->validate();

        $validatedData = $validator->validated();
        $this->assertEquals('validuser', $validatedData['username']);
    }

	public function testValidationRuleInterfaceFailure()
    {
        $data = [
            'username' => 'invaliduser',
        ];

        $rules = [
            'username' => [new class implements ValidationRuleInterface {
				public function ruleName(): string
				{
					return 'validuser';
				}

                public function validate($attribute, $value, $parameters, $validator): bool
                {
                    return $value === 'validuser';
                }

                public function message($attribute, $parameters): string
                {
                    return "The {$attribute} is invalid.";
                }
            }],
        ];

        $validator = new Validator($data, $rules);

        $this->expectException(ValidationException::class);

        try {
            $validator->validate();
        } catch (ValidationException $e) {
            $errors = $e->getErrors();
            $this->assertEquals('The username is invalid.', $errors['username'][0]);
            throw $e;
        }
    }

	public function testCallableRule()
    {
        $data = [
            'username' => 'validuser',
        ];

        $rules = [
            'username' => [function($attribute, $value, $parameters, $validator) {
                return $value === 'validuser';
            }],
        ];

        $validator = new Validator($data, $rules);

        $validator->validate();

        $validatedData = $validator->validated();
        $this->assertEquals('validuser', $validatedData['username']);
    }

	public function testCallableRuleFailure()
    {
        $data = [
            'username' => 'invaliduser',
        ];

        $rules = [
            'username' => [function($attribute, $value, $parameters, $validator) {
                return $value === 'validuser';
            }],
        ];

        $validator = new Validator($data, $rules);

        $this->expectException(ValidationException::class);

        try {
            $validator->validate();
        } catch (ValidationException $e) {
            $errors = $e->getErrors();
            $this->assertEquals('The username field failed validation for rule custom.', $errors['username'][0]);
            throw $e;
        }
    }

	public function testUnknownRule()
    {
		Functions\expect('esc_attr__')->andReturn('Validation rule unknownRule not found.');
		Functions\expect('wp_sprintf')->andReturn('Validation rule unknownRule not found.');
		Functions\expect('esc_attr')->andReturn('unknownRule');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Validation rule unknownRule not found.');

        $data = [
            'username' => 'testuser',
        ];

        $rules = [
            'username' => 'unknownRule',
        ];

        $validator = new Validator($data, $rules);
        $validator->validate();
    }

	public function testWhenNoneOfTheRuleMatches()
    {
		Functions\expect('esc_attr__')->andReturn('Validation rule unknownRule not found.');
		Functions\expect('wp_sprintf')->andReturn('Validation rule unknownRule not found.');
		Functions\expect('esc_attr')->andReturn('unknownRule');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Validation rule unknownRule not found.');

        $data = [
            'username' => '111',
        ];

        $rules = [
            'username' => [4545],
        ];

        $validator = new Validator($data, $rules);
        $validator->validate();
    }
}
