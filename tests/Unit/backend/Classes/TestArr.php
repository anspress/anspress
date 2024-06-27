<?php

use AnsPress\Classes\Arr;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Brain\Monkey\Functions;

require_once PLUGIN_DIR . '/src/backend/autoloader.php';

/**
 * @covers \AnsPress\Classes\Arr
 * @package
 */
class TestArr extends TestCase {
	public function testSimpleDotNotation()
    {
        $dotNotation = ['name' => 'Alice', 'age' => 30];
        $expected = ['name' => 'Alice', 'age' => 30];
        $this->assertEquals($expected, Arr::fromDotNotation($dotNotation));
    }

    public function testNestedDotNotation()
    {
        $dotNotation = ['address.street' => '123 Main St', 'address.city' => 'Anytown'];
        $expected = ['address' => ['street' => '123 Main St', 'city' => 'Anytown']];
        $this->assertEquals($expected, Arr::fromDotNotation($dotNotation));
    }

    public function testDeeplyNestedDotNotation()
    {
        $dotNotation = ['user.profile.name' => 'Bob', 'user.profile.settings.theme' => 'dark'];
        $expected = ['user' => ['profile' => ['name' => 'Bob', 'settings' => ['theme' => 'dark']]]];
        $this->assertEquals($expected, Arr::fromDotNotation($dotNotation));
    }

    public function testNumericIndexDotNotation()
    {
        $dotNotation = ['items.0.name' => 'Product A', 'items.1.name' => 'Product B'];
        $expected = ['items' => [
            ['name' => 'Product A'],
            ['name' => 'Product B']
        ]];

        $this->assertEquals($expected, Arr::fromDotNotation($dotNotation));
    }

    public function testMixedNumericAndAssociativeDotNotation()
    {
        $dotNotation = ['users.0.name' => 'David', 'users.0.email' => 'david@example.com', 'users.1.name' => 'Eva'];
        $expected = ['users' => [
            ['name' => 'David', 'email' => 'david@example.com'],
            ['name' => 'Eva']
        ]];
        $this->assertEquals($expected, Arr::fromDotNotation($dotNotation));
    }

    public function testEmptyDotNotation()
    {
        $dotNotation = [];
        $expected = [];
        $this->assertEquals($expected, Arr::fromDotNotation($dotNotation));
    }

    public function testExtraDotsInKeys()
    {
        $dotNotation = ['info..name' => 'Frank', 'details...phone' => '555-5555'];
        $expected = [
			'info' => [
				['name' => 'Frank']
			],
			'details' => [
				[
					['phone' => '555-5555']
				]
			]
		]; // Extra dots should be ignored

        $this->assertEquals($expected, Arr::fromDotNotation($dotNotation));
    }

	public function testFromDotNotation()
    {
        $dotNotationArray = [
            'info.name' => 'John',
            'info.age' => 30,
            'info.address.city' => 'New York',
            'info.address.zip' => 10001,
            'preferences.language' => 'English',
            'preferences.timezone' => 'EST'
        ];

        $expected = [
            'info' => [
                'name' => 'John',
                'age' => 30,
                'address' => [
                    'city' => 'New York',
                    'zip' => 10001
                ]
            ],
            'preferences' => [
                'language' => 'English',
                'timezone' => 'EST'
            ]
        ];

        $this->assertEquals($expected, Arr::fromDotNotation($dotNotationArray));
    }

    public function testFromDotNotationWithNumericKeys()
    {
        $dotNotationArray = [
            'items.0.name' => 'Item 1',
            'items.0.price' => 10,
            'items.1.name' => 'Item 2',
            'items.1.price' => 20
        ];

        $expected = [
            'items' => [
                [
                    'name' => 'Item 1',
                    'price' => 10
                ],
                [
                    'name' => 'Item 2',
                    'price' => 20
                ]
            ]
        ];

        $this->assertEquals($expected, Arr::fromDotNotation($dotNotationArray));
    }

    public function testFromDotNotationWithEmptyKeys()
    {
        $dotNotationArray = [
            'info..name' => 'John'
        ];

        $expected = [
            'info' => [
				[
					'name' => 'John'
				]
            ]
        ];

        $this->assertEquals($expected, Arr::fromDotNotation($dotNotationArray));
    }

    public function testToDotNotation()
    {
        $nestedArray = [
            'info' => [
                'name' => 'John',
                'age' => 30,
                'address' => [
                    'city' => 'New York',
                    'zip' => 10001
                ]
            ],
            'preferences' => [
                'language' => 'English',
                'timezone' => 'EST'
            ]
        ];

        $expected = [
            'info.name' => 'John',
            'info.age' => 30,
            'info.address.city' => 'New York',
            'info.address.zip' => 10001,
            'preferences.language' => 'English',
            'preferences.timezone' => 'EST'
        ];

        $this->assertEquals($expected, Arr::toDotNotation($nestedArray));
    }

    public function testToDotNotationWithNumericKeys()
    {
        $nestedArray = [
            'items' => [
                [
                    'name' => 'Item 1',
                    'price' => 10
                ],
                [
                    'name' => 'Item 2',
                    'price' => 20
                ]
            ]
        ];

        $expected = [
            'items.0.name' => 'Item 1',
            'items.0.price' => 10,
            'items.1.name' => 'Item 2',
            'items.1.price' => 20
        ];

        $this->assertEquals($expected, Arr::toDotNotation($nestedArray));
    }

    public function testToDotNotationWithEmptyKeys()
    {
        $nestedArray = [
            'info' => [
                '' => [
                    'name' => 'John'
                ]
            ]
        ];

        $expected = [
            'info..name' => 'John'
        ];

        $this->assertEquals($expected, Arr::toDotNotation($nestedArray));
    }

	public function testFromDotNotationWithWildcards()
    {
        $dotNotationArray = [
            'items.*.name' => 'Item',
            'items.*.price' => 20
        ];

        $expected = [
            'items' => [
                [
                    'name' => 'Item',
                ],
                [
                    'price' => 20
                ]
            ]
        ];

        $this->assertEquals($expected, Arr::fromDotNotation($dotNotationArray));
    }
}
