<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnsPressFormFieldTags extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress\Form\Field\Tags' );
		$this->assertTrue( $class->hasProperty( 'type' ) && $class->getProperty( 'type' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Tags', 'prepare' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Tags', 'sanitize_cb_args' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Tags', 'get_options' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Tags', 'field_markup' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Tags', 'unsafe_value' ) );
	}

	/**
	 * @covers \AnsPress\Form\Field\Tags::prepare
	 */
	public function testPrepare() {
		// Test 1.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [] );
		$reflection = new \ReflectionClass( $field );
		$sanitize_cb = $reflection->getProperty( 'sanitize_cb' );
		$sanitize_cb->setAccessible( true );
		$valiate_cb = $reflection->getProperty( 'validate_cb' );
		$valiate_cb->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $sanitize_cb->getValue( $field ) );
		$this->assertIsArray( $valiate_cb->getValue( $field ) );
		$this->assertEquals( [ 'array_remove_empty', 'tags_field' ], $sanitize_cb->getValue( $field ) );
		$this->assertEquals( [ 'is_array', 'array_max', 'array_min' ], $valiate_cb->getValue( $field ) );
		$expected = [
			'label'      => 'AnsPress Tags Field',
			'array_max'  => 3,
			'array_min'  => 2,
			'terms_args' => [ 'taxonomy' => 'question_tag', 'hide_empty' => false, 'fields' => 'id=>name' ],
			'options'    => 'terms',
			'js_options' => [
				'maxItems' => 3,
				'form'     => 'Sample Form',
				'id'       => 'SampleForm-sample-form',
				'field'    => 'sample-form',
				'nonce'    => wp_create_nonce( 'tags_Sample Formsample-form' ),
				'create'   => false,
				'labelAdd' => 'Add',
			],
		];
		$this->assertEquals( $expected, $field->args );

		// Test 2.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [
			'label'     => 'Test Label',
			'array_max' => 5,
			'array_min' => 2,
		] );
		$reflection = new \ReflectionClass( $field );
		$sanitize_cb = $reflection->getProperty( 'sanitize_cb' );
		$sanitize_cb->setAccessible( true );
		$valiate_cb = $reflection->getProperty( 'validate_cb' );
		$valiate_cb->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $sanitize_cb->getValue( $field ) );
		$this->assertIsArray( $valiate_cb->getValue( $field ) );
		$this->assertEquals( [ 'array_remove_empty', 'tags_field' ], $sanitize_cb->getValue( $field ) );
		$this->assertEquals( [ 'is_array', 'array_max', 'array_min' ], $valiate_cb->getValue( $field ) );
		$expected = [
			'label'      => 'Test Label',
			'array_max'  => 5,
			'array_min'  => 2,
			'terms_args' => [ 'taxonomy' => 'question_tag', 'hide_empty' => false, 'fields' => 'id=>name' ],
			'options'    => 'terms',
			'js_options' => [
				'maxItems' => 5,
				'form'     => 'Sample Form',
				'id'       => 'SampleForm-sample-form',
				'field'    => 'sample-form',
				'nonce'    => wp_create_nonce( 'tags_Sample Formsample-form' ),
				'create'   => false,
				'labelAdd' => 'Add',
			],
		];
		$this->assertEquals( $expected, $field->args );

		// Test 3.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [
			'array_max'  => 10,
			'array_min'  => 3,
			'options'    => 'posts',
			'js_options' => [],
			'sanitize'   => 'custom_sanitize_cb',
			'validate'   => 'custom_validate_cb',
		] );
		$reflection = new \ReflectionClass( $field );
		$sanitize_cb = $reflection->getProperty( 'sanitize_cb' );
		$sanitize_cb->setAccessible( true );
		$valiate_cb = $reflection->getProperty( 'validate_cb' );
		$valiate_cb->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $sanitize_cb->getValue( $field ) );
		$this->assertIsArray( $valiate_cb->getValue( $field ) );
		$this->assertEquals( [ 'array_remove_empty', 'tags_field', 'custom_sanitize_cb' ], $sanitize_cb->getValue( $field ) );
		$this->assertEquals( [ 'is_array', 'array_max', 'array_min', 'custom_validate_cb' ], $valiate_cb->getValue( $field ) );
		$expected = [
			'label'      => 'AnsPress Tags Field',
			'array_max'  => 10,
			'array_min'  => 3,
			'terms_args' => [ 'taxonomy' => 'question_tag', 'hide_empty' => false, 'fields' => 'id=>name' ],
			'options'    => 'posts',
			'js_options' => [
				'maxItems' => 10,
				'form'     => 'Sample Form',
				'id'       => 'SampleForm-sample-form',
				'field'    => 'sample-form',
				'nonce'    => wp_create_nonce( 'tags_Sample Formsample-form' ),
				'create'   => false,
				'labelAdd' => 'Add',
			],
			'sanitize'   => 'custom_sanitize_cb',
			'validate'   => 'custom_validate_cb',
		];
		$this->assertEquals( $expected, $field->args );

		// Test 4.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [
			'label'      => 'Test Label',
			'array_max'  => 10,
			'array_min'  => 3,
			'options'    => [
				'option1' => 'Option 1',
				'option2' => 'Option 2',
				'option3' => 'Option 3',
			],
			'js_options' => [
				'maxItems' => 10,
				'form'     => 'Test Form',
				'id'       => 'test-form',
				'field'    => 'test-form',
				'nonce'    => wp_create_nonce( 'tags_Test Formtest-form' ),
				'create'   => false,
				'labelAdd' => 'New Tag',
			],
			'sanitize'   => 'custom_sanitize_cb',
			'validate'   => 'custom_validate_cb',
		] );
		$reflection = new \ReflectionClass( $field );
		$sanitize_cb = $reflection->getProperty( 'sanitize_cb' );
		$sanitize_cb->setAccessible( true );
		$valiate_cb = $reflection->getProperty( 'validate_cb' );
		$valiate_cb->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $sanitize_cb->getValue( $field ) );
		$this->assertIsArray( $valiate_cb->getValue( $field ) );
		$this->assertEquals( [ 'array_remove_empty', 'tags_field', 'custom_sanitize_cb' ], $sanitize_cb->getValue( $field ) );
		$this->assertEquals( [ 'is_array', 'array_max', 'array_min', 'custom_validate_cb' ], $valiate_cb->getValue( $field ) );
		$expected = [
			'label'      => 'Test Label',
			'array_max'  => 10,
			'array_min'  => 3,
			'terms_args' => [ 'taxonomy' => 'question_tag', 'hide_empty' => false, 'fields' => 'id=>name' ],
			'options'    => [
				'option1' => 'Option 1',
				'option2' => 'Option 2',
				'option3' => 'Option 3',
			],
			'js_options' => [
				'maxItems' => 10,
				'form'     => 'Test Form',
				'id'       => 'test-form',
				'field'    => 'test-form',
				'nonce'    => wp_create_nonce( 'tags_Test Formtest-form' ),
				'create'   => false,
				'labelAdd' => 'New Tag',
			],
			'sanitize'   => 'custom_sanitize_cb',
			'validate'   => 'custom_validate_cb',
		];
		$this->assertEquals( $expected, $field->args );
	}

	/**
	 * @covers AnsPress\Form\Field\Tags::sanitize_cb_args
	 */
	public function testSanitizeCbArgs() {
		// Test 1.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'sanitize_cb_args' );
		$method->setAccessible( true );
		$result = $method->invoke( $field, 'test' );
		$this->assertIsArray( $result );
		$this->assertEquals( [ 'test', $field->args ], $result );

		// Test 2.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [
			'label'     => 'Test Label',
			'array_max' => 5,
			'array_min' => 2,
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'sanitize_cb_args' );
		$method->setAccessible( true );
		$result = $method->invoke( $field, 'test_value' );
		$this->assertIsArray( $result );
		$this->assertEquals( [ 'test_value', $field->args ], $result );

		// Test 3.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [
			'array_max' => 5,
			'array_min' => 2,
			'options'   => 'terms',
			'js_options' => [],
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'sanitize_cb_args' );
		$method->setAccessible( true );
		$result = $method->invoke( $field, 'test_value' );
		$this->assertIsArray( $result );
		$this->assertEquals( [ 'test_value', $field->args ], $result );

		// Test 4.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [
			'array_max' => 5,
			'array_min' => 2,
			'options'   => 'terms',
			'js_options' => [
				'maxItems' => 5,
				'form'     => 'Sample Form',
				'id'       => 'sample-form',
				'field'    => 'sample-form',
				'nonce'    => wp_create_nonce( 'tags_Sample Formsample-form' ),
				'create'   => false,
				'labelAdd' => 'Add',
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'sanitize_cb_args' );
		$method->setAccessible( true );
		$result = $method->invoke( $field, [ 'new_value' ] );
		$this->assertIsArray( $result );
		$this->assertEquals( [ [ 'new_value' ], $field->args ], $result );
	}

	/**
	 * @covers AnsPress\Form\Field\Tags::unsafe_value
	 */
	public function testUnsafeValue() {
		// Test 1.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [] );
		$_REQUEST = [
			'Sample Form' => [
				'sample-form' => 'test1,test2',
			],
		];
		$result = $field->unsafe_value();
		$this->assertIsArray( $result );
		$this->assertEquals( [ 'test1', 'test2' ], $result );

		// Test 2.
		$field = new \AnsPress\Form\Field\Tags( 'Test Form', 'test-form', [] );
		$_REQUEST = [
			'Test Form' => [
				'test-form' => 'test1',
			],
		];
		$result = $field->unsafe_value();
		$this->assertIsArray( $result );
		$this->assertEquals( [ 'test1' ], $result );

		// Test 3.
		$field = new \AnsPress\Form\Field\Tags( 'Test Form', 'test-form', [] );
		$_REQUEST = [
			'Test Form' => [
				'test-form' => '',
			],
		];
		$result = $field->unsafe_value();
		$this->assertIsArray( $result );
		$this->assertEquals( [ '' ], $result );

		// Test 4.
		$field = new \AnsPress\Form\Field\Tags( 'Test Form', 'test-form', [] );
		$_REQUEST = [
			'Test Form' => [
				'test-form' => 'this is test, \\test value\\',
			],
		];
		$result = $field->unsafe_value();
		$this->assertIsArray( $result );
		$this->assertEquals( [ 'this is test', ' test value' ], $result );

		// Test 5.
		$field = new \AnsPress\Form\Field\Tags( 'Test Form', 'test-form', [] );
		$_REQUEST = [
			'Test Form' => [
				'test-form' => '0,test value,valid tag id,\\valid tag name\\',
			],
		];
		$result = $field->unsafe_value();
		$this->assertIsArray( $result );
		$this->assertEquals( [ '0', 'test value', 'valid tag id', 'valid tag name' ], $result );
	}
}
