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
}
