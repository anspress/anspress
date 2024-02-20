<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnsPressFormFieldInput extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress\Form\Field\Input' );
		$this->assertTrue( $class->hasProperty( 'type' ) && $class->getProperty( 'type' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'subtype' ) && $class->getProperty( 'subtype' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Input', 'prepare' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Input', 'set_subtype' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Input', 'html_order' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Input', 'field_markup' ) );
	}

	/**
	 * @covers AnsPress\Form\Field\Input::set_subtype
	 */
	public function testSetSubtype() {
		$field = new \AnsPress\Form\Field\Input( 'Sample Form', 'sample-form', [] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'set_subtype' );
		$method->setAccessible( true );

		// Test begins.
		// Before method is called.
		$this->assertEquals( 'text', $field->subtype );

		// After method is called.
		// Test 1.
		$field->args['subtype'] = 'text';
		$method->invoke( $field );
		$this->assertEquals( 'text', $field->subtype );

		// Test 2.
		$field->args['subtype'] = 'number';
		$method->invoke( $field );
		$this->assertEquals( 'number', $field->subtype );

		// Test 3.
		$field->args['subtype'] = 'color';
		$method->invoke( $field );
		$this->assertEquals( 'color', $field->subtype );

		// Test 4.
		$field->args['subtype'] = 'invalid';
		$method->invoke( $field );
		$this->assertEquals( 'text', $field->subtype );

		// Reset subtype.
		$field->args['subtype'] = 'text';
	}

	/**
	 * @covers AnsPress\Form\Field\Input::html_order
	 */
	public function testHTMLOrder() {
		$field = new \AnsPress\Form\Field\Input( 'Sample Form', 'sample-form', [] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'html_order' );
		$method->setAccessible( true );
		$property = $reflection->getProperty( 'output_order' );
		$property->setAccessible( true );

		// Test begins.
		// Test 1.
		$field->subtype = 'hidden';
		$hidden_output_order = [ 'wrapper_start', 'errors', 'field_markup', 'wrapper_end' ];
		$method->invoke( $field );
		$this->assertEquals( $hidden_output_order, $property->getValue( $field ) );

		// Test 2.
		$field->subtype = 'text';
		$default_output_order = [ 'wrapper_start', 'label', 'field_wrap_start', 'errors', 'field_markup', 'desc', 'field_wrap_end', 'wrapper_end' ];
		$method->invoke( $field );
		$this->assertEquals( $default_output_order, $property->getValue( $field ) );

		// Test 3.
		$custom_output_order = [ 'wrapper_start', 'label', 'desc', 'errors', 'field_wrap_start', 'field_markup', 'field_wrap_end', 'wrapper_end' ];
		$field->args['output_order'] = $custom_output_order;
		$method->invoke( $field );
		$this->assertEquals( $custom_output_order, $property->getValue( $field ) );

		// Test 4.
		$new_custom_output_order = [ 'label', 'desc', 'errors', 'field_markup' ];
		$field->args['output_order'] = $new_custom_output_order;
		$method->invoke( $field );
		$this->assertEquals( $new_custom_output_order, $property->getValue( $field ) );

		// Test 5.
		$field->args['output_order'] = [];
		$method->invoke( $field );
		$this->assertEquals( $default_output_order, $property->getValue( $field ) );

		// Test 6.
		$field->subtype = 'hidden';
		$field->args['output_order'] = [];
		$method->invoke( $field );
		$this->assertEquals( $hidden_output_order, $property->getValue( $field ) );

		// Test 7.
		$field->subtype = 'text';
		$field->args['output_order'] = $default_output_order;
		$field->args['html'] = '<div>Custom HTML</div>';
		$field->args['label'] = '';
		$html_output_order = [ 0 => 'wrapper_start', 3 => 'errors', 4 => 'field_markup', 5 => 'desc', 7 => 'wrapper_end' ];
		$method->invoke( $field );
		$this->assertEquals( $html_output_order, $property->getValue( $field ) );

		// Test 8.
		$field->args['output_order'] = $new_custom_output_order;
		$field->args['html'] = '<div>Custom HTML</div>';
		$field->args['label'] = '';
		$new_html_output_order = [ 1 => 'desc', 2 => 'errors', 3 => 'field_markup' ];
		$method->invoke( $field );
		$this->assertEquals( $new_html_output_order, $property->getValue( $field ) );

		// Test 9.
		$field->args['output_order'] = $default_output_order;
		$field->args['html'] = '<div>Custom HTML</div>';
		$field->args['label'] = 'Label';
		$method->invoke( $field );
		$this->assertEquals( $default_output_order, $property->getValue( $field ) );

		// Test 10.
		$field->args['output_order'] = $default_output_order;
		$field->args['html'] = '';
		$field->args['label'] = 'Label';
		$method->invoke( $field );
		$this->assertEquals( $default_output_order, $property->getValue( $field ) );
	}
}
