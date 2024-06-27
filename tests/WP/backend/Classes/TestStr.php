<?php

namespace Tests\WP\backend\Classes;

use AnsPress\Classes\Str;
use Yoast\WPTestUtils\BrainMonkey\TestCase;

/**
 * @covers AnsPress\Classes\Str
 * @package Tests\WP
 */
class TestStr extends TestCase {
	/**
     * Test `toSnakeCase()` with different inputs and delimiters.
     *
     * @dataProvider toSnakeCaseProvider
     */
    public function testToSnakeCase($input, $delimiter, $expected) {
        $result = Str::toSnakeCase($input, $delimiter);
        $this->assertEquals($expected, $result);
    }

    public function toSnakeCaseProvider() {
        return [
            ['CamelCase', '_', 'camel_case'],
            ['PascalCase', '_', 'pascal_case'],
            ['Title Case', '_', 'title_case'],
            ['snake_case', '_', 'snake_case'], // Already snake_case
            ['kebab-case', '-', 'kebab-case'],
            ['Mixed_STRING With-symbols', '_', 'mixed__s_t_r_i_n_g_with-symbols'],
            ['', '_', ''], // Empty string
            ['123 Numbers 456', '_', '123_numbers456']
        ];
    }

    /**
     * Test `toCamelCase()` with different inputs.
     *
     * @dataProvider toCamelCaseProvider
     */
    public function testToCamelCase($input, $expected) {
        $result = Str::toCamelCase($input);
        $this->assertEquals($expected, $result);
    }

    public function toCamelCaseProvider() {
        return [
            ['snake_case', 'snakeCase'],
            ['kebab-case', 'kebabCase'],
            ['Title Case', 'titleCase'],
            ['camelCase', 'camelCase'], // Already camelCase
            ['Mixed_STRING With-symbols', 'mixedSTRINGWithSymbols'],
            ['', ''], // Empty string
            ['123_numbers_456', '123Numbers456']
        ];
    }
}
