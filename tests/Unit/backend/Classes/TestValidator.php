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
            $validator->validated();
        } catch (ValidationException $e) {
            $errors = $e->getErrors();
            $this->assertEquals('The email format is invalid OVERRIDEN.', $errors['email'][0]);
            throw $e;
        }
    }

	public function testDeepRules()
	{
		$data = [
			'foo' => [
				'bar' => ['test'],
				'test' => [1]
			],
			'obj' => [
				[
					'obj1' => 'obj1val'
				]
			],
			'nested' => [
				'nested' => [
					[
						[
							'name' => 'John Doe',
							'email' => 'test@email.com',
							'obj' => [
								'obj1' => 'obj1val',
								'obj2' => [
									'key' => 'val'
								]
							]
						]
					]
				]
			],
			'testnonarray' => 'testNonArray',
			'nonexistant' => 'nonexistant',
		];

		$rules = [
			'foo.bar' => 'required|array',
			'foo.test' => 'required|array',
			'foo.test.*' => 'required|integer',
			'obj.*.obj1' => 'required|string',
			'nested.nested' => 'required|array',
			'nested.nested.*.*.name' => 'required|string',
			'nested.nested.*.*.email' => 'required|email',
			'nested.nested.*.*.obj.obj1' => 'required|string',
			'nested.nested.*.*.obj.obj2' => 'required|array',
			'testnonarray.*' => 'required|string'
		];

		$validator = new Validator($data, $rules);

		$valiated = $validator->validated();

		// Check for non-existant key
		$this->assertArrayNotHasKey('nonexistant', $valiated);

		unset($data['nonexistant']);

		$this->assertEquals(
			$data,
			$valiated
		);
	}


	public function testCustomAttributes()
    {
        $data = [
            'email_address' => 'not-an-email',
            'foo' => [
				'bar' => 222,
				'test' => [
					'foo' => 'bar',
					'nested' => [
						[
							[
								'name' => 'John Doe'
							]
						]
					]
				]
			],
			'obj' => [
				[
					'foo' => 'bar'
				]
			]
        ];

        $rules = [
            'email_address' => 'required|email',
			'foo.bar' => 'required|array',
			'foo.test.*' => 'required|array',
			'obj.*.foo' => 'integer',
			'foo.test.nested' => 'required|string',
			'foo.test.nested.*.*' => 'required|string',
			'foo.test.nested.*.*.name' => 'required|integer',
        ];

        $customAttributes = [
            'email_address' => 'XXXXXXX3333E',
			'foo.bar' => 'BarXXXXX',
			'foo.test.*.foo' => 'TestXXXXX',
			'obj.*.foo' => 'ObjXXXXX'
        ];

        $validator = new Validator($data, $rules, [], $customAttributes);

        $this->expectException(ValidationException::class);

        try {
            $validator->validated();
        } catch (ValidationException $e) {
            $errors = $e->getErrors();

            $this->assertEquals('The XXXXXXX3333E must be a valid email address.', $errors['email_address'][0]);
			$this->assertEquals('The BarXXXXX must be an array.', $errors['foo.bar'][0]);
			$this->assertEquals('The foo.test.foo must be an array.', $errors['foo.test.foo'][0]);
			$this->assertEquals('The ObjXXXXX must be an integer.', $errors['obj.0.foo'][0]);

			$this->assertCount(7, $errors);

			$this->assertCount(1, $errors['email_address']);
			$this->assertCount(1, $errors['foo.bar']);
			$this->assertCount(1, $errors['foo.test.foo']);
			$this->assertCount(1, $errors['obj.0.foo']);
			$this->assertCount(1, $errors['foo.test.nested']);
			$this->assertCount(1, $errors['foo.test.nested.0.0']);
			$this->assertCount(1, $errors['foo.test.nested.0.0.name']);

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

		$this->assertFalse($validator->fails());

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

        $this->assertFalse($validator->fails());
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

                public function message(): string
                {
                    return "The :attribute is invalid.";
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

                public function message(): string
                {
                    return "The :attribute is invalid.";
                }
            }],
        ];

        $validator = new Validator($data, $rules);

        $this->expectException(ValidationException::class);

        try {
            $validator->validated();
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
            $validator->validated();
        } catch (ValidationException $e) {
            $errors = $e->getErrors();
            $this->assertEquals('Validation failed for username.', $errors['username'][0]);
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
        $validator->validated();
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
        $validator->validated();
    }

	public function testNestedAttributesWithAdditionalLevels()
    {
        $data = [
            'order' => [
                'items' => [
                    ['name' => 'Item 1', 'price' => 100],
                    ['name' => 'Item 2', 'price' => 200],
                ],
            ],
        ];

        $rules = [
            'order.items.*'       => 'required|array',
            'order.items.*.name'  => 'required|string',
            'order.items.*.price' => 'required|numeric|min:0',
        ];

        $validator = new Validator($data, $rules);

        $validator->validate();

        $validatedData = $validator->validated();

        $this->assertCount(2, $validatedData['order']['items']);
        $this->assertEquals('Item 1', $validatedData['order']['items'][0]['name']);
        $this->assertEquals(100, $validatedData['order']['items'][0]['price']);
        $this->assertEquals('Item 2', $validatedData['order']['items'][1]['name']);
        $this->assertEquals(200, $validatedData['order']['items'][1]['price']);
    }

	public function testInvalidRule()
	{
		Functions\expect('esc_attr__')->andReturn('Validation rule %1$s not found.');

		Functions\expect('esc_attr')->andReturn('unknownRule');

		Functions\expect('wp_sprintf')->andReturn('Validation rule unknownRule not found.');

		$data = [
			'username' => 'testuser',
		];

		$rules = [
			'username' => 'required|unknownRule',
		];

		$this->expectException(ValidationException::class);
		$this->getExpectedExceptionMessage('Validation rule unknownRule not found.');

		$validator = new Validator($data, $rules);
	}

	public function testDataShouldNotBeIncludedWithoutRules()
	{
		$data = [
			'username' => 'testuser',
			'email' => 'rah12@live.com',
		];

		$rules = [
			'username' => 'required|string',
		];

		$validator = new Validator($data, $rules);

		$validatedData = $validator->validated();

		$this->assertArrayNotHasKey('email', $validatedData);

		$this->assertEquals(
			[
				'username' => 'testuser',
			],
			$validatedData
		);

	}
}
