<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnsPressFormFieldCheckbox extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress\Form\Field\Checkbox' );
		$this->assertTrue( $class->hasProperty( 'type' ) && $class->getProperty( 'type' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Checkbox', 'prepare' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Checkbox', 'html_order' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Checkbox', 'field_markup' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Checkbox', 'unsafe_value' ) );
	}

	/**
	 * @covers AnsPress\Form\Field\Checkbox::prepare
	 */
	public function testPrepare() {
		// Test 1.
		$field = new \AnsPress\Form\Field\Checkbox( 'Sample Form', 'sample-form', [] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'sanitize_cb' );
		$property->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $property->getValue( $field ) );
		$this->assertEquals( [ 'boolean' ], $property->getValue( $field ) );
		$this->assertEquals( [ 'label' => 'AnsPress Checkbox Field' ], $field->args );

		// Test 2.
		$field = new \AnsPress\Form\Field\Checkbox( 'Sample Form', 'sample-form', [
			'label'   => 'Test Label',
			'options' => [
				'option1' => 'Option 1',
				'option2' => 'Option 2',
			]
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'sanitize_cb' );
		$property->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $property->getValue( $field ) );
		$this->assertEquals( [ 'array_remove_empty', 'text_field' ], $property->getValue( $field ) );
		$this->assertEquals( [ 'label' => 'Test Label', 'options' => [ 'option1' => 'Option 1', 'option2' => 'Option 2', ] ], $field->args );

		// Test 3.
		$field = new \AnsPress\Form\Field\Checkbox( 'Sample Form', 'sample-form', [
			'sanitize' => 'custom_sanitize_cb',
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'sanitize_cb' );
		$property->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $property->getValue( $field ) );
		$this->assertEquals( [ 'boolean', 'custom_sanitize_cb' ], $property->getValue( $field ) );
		$this->assertEquals( [ 'label' => 'AnsPress Checkbox Field', 'sanitize' => 'custom_sanitize_cb' ], $field->args );

		// Test 4.
		$field = new \AnsPress\Form\Field\Checkbox( 'Sample Form', 'sample-form', [
			'options' => [
				'option1' => 'Option 1',
				'option2' => 'Option 2',
			],
			'sanitize' => 'custom_sanitize_cb',
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'sanitize_cb' );
		$property->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $property->getValue( $field ) );
		$this->assertEquals( [ 'array_remove_empty', 'text_field', 'custom_sanitize_cb' ], $property->getValue( $field ) );
		$this->assertEquals( [ 'label' => 'AnsPress Checkbox Field', 'options' => [ 'option1' => 'Option 1', 'option2' => 'Option 2', ], 'sanitize' => 'custom_sanitize_cb' ], $field->args );
	}

	/**
	 * @covers AnsPress\Form\Field\Checkbox::html_order
	 */
	public function testHTMLOrder() {
		// Test 1.
		$field = new \AnsPress\Form\Field\Checkbox( 'Sample Form', 'sample-form', [ 'options' => [] ] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'html_order' );
		$method->setAccessible( true );
		$property = $reflection->getProperty( 'output_order' );
		$property->setAccessible( true );
		$output_order = [ 'wrapper_start', 'label', 'field_wrap_start', 'errors', 'field_markup', 'field_wrap_end', 'wrapper_end' ];
		$method->invoke( $field );
		$this->assertEquals( $output_order, $property->getValue( $field ) );

		// Test 2.
		$field = new \AnsPress\Form\Field\Checkbox( 'Sample Form', 'sample-form', [] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'html_order' );
		$method->setAccessible( true );
		$property = $reflection->getProperty( 'output_order' );
		$property->setAccessible( true );
		$output_order = [ 'wrapper_start', 'label', 'field_wrap_start', 'errors', 'field_markup', 'field_wrap_end', 'wrapper_end' ];
		$method->invoke( $field );
		$this->assertEquals( $output_order, $property->getValue( $field ) );

		// Test 3.
		$field = new \AnsPress\Form\Field\Checkbox( 'Sample Form', 'sample-form', [ 'options' => [ 'option1' => 'Option 1', 'option2' => 'Option 2' ] ] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'html_order' );
		$method->setAccessible( true );
		$property = $reflection->getProperty( 'output_order' );
		$property->setAccessible( true );
		$output_order = [ 'wrapper_start', 'label', 'field_wrap_start', 'errors', 'field_markup', 'desc', 'field_wrap_end', 'wrapper_end' ];
		$method->invoke( $field );
		$this->assertEquals( $output_order, $property->getValue( $field ) );

		// Test 4.
		$field = new \AnsPress\Form\Field\Checkbox( 'Sample Form', 'sample-form', [ 'options' => [ 'option1' => 'Option 1', 'option2' => 'Option 2' ] ] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'html_order' );
		$method->setAccessible( true );
		$property = $reflection->getProperty( 'output_order' );
		$property->setAccessible( true );
		$custom_output_order = [ 'wrapper_start', 'label', 'desc', 'errors', 'field_wrap_start', 'field_markup', 'field_wrap_end', 'wrapper_end' ];
		$field->args['output_order'] = $custom_output_order;
		$method->invoke( $field );
		$this->assertEquals( $custom_output_order, $property->getValue( $field ) );

		// Test 5.
		$field = new \AnsPress\Form\Field\Checkbox( 'Sample Form', 'sample-form', [ 'options' => [ 'option1' => 'Option 1', 'option2' => 'Option 2' ] ] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'html_order' );
		$method->setAccessible( true );
		$property = $reflection->getProperty( 'output_order' );
		$property->setAccessible( true );
		$custom_output_order = [ 'label', 'desc', 'errors', 'field_markup' ];
		$field->args['output_order'] = $custom_output_order;
		$method->invoke( $field );
		$this->assertEquals( $custom_output_order, $property->getValue( $field ) );

		// Test 6.
		$field = new \AnsPress\Form\Field\Checkbox( 'Sample Form', 'sample-form', [ 'options' => [ 'option1' => 'Option 1', 'option2' => 'Option 2' ] ] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'html_order' );
		$method->setAccessible( true );
		$property = $reflection->getProperty( 'output_order' );
		$property->setAccessible( true );
		$custom_output_order = [];
		$field->args['output_order'] = $custom_output_order;
		$method->invoke( $field );
		$output_order = [ 'wrapper_start', 'label', 'field_wrap_start', 'errors', 'field_markup', 'desc', 'field_wrap_end', 'wrapper_end' ];
		$this->assertEquals( $output_order, $property->getValue( $field ) );

		// Test 7.
		$field = new \AnsPress\Form\Field\Checkbox( 'Sample Form', 'sample-form', [] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'html_order' );
		$method->setAccessible( true );
		$property = $reflection->getProperty( 'output_order' );
		$property->setAccessible( true );
		$custom_output_order = [ 'label', 'desc', 'errors', 'field_markup' ];
		$field->args['output_order'] = $custom_output_order;
		$method->invoke( $field );
		$output_order = [ 'wrapper_start', 'label', 'field_wrap_start', 'errors', 'field_markup', 'field_wrap_end', 'wrapper_end' ];
		$this->assertEquals( $output_order, $property->getValue( $field ) );
	}
}
