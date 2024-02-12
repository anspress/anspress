<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnsPressFormFieldGroup extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress\Form\Field\Group' );
		$this->assertTrue( $class->hasProperty( 'type' ) && $class->getProperty( 'type' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'child' ) && $class->getProperty( 'child' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Group', 'prepare' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Group', 'html_order' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Group', 'label' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Group', 'field_markup' ) );
	}

	/**
	 * @covers AnsPress\Form\Field\Group::html_order
	 */
	public function testHTMLOrder() {
		$field = new \AnsPress\Form\Field\Group( 'Sample Form', 'sample-form', [] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'html_order' );
		$method->setAccessible( true );
		$property = $reflection->getProperty( 'output_order' );
		$property->setAccessible( true );

		// Test begins.
		$accepted_html_order = [ 'wrapper_start', 'label', 'field_wrap_start', 'desc', 'errors', 'field_markup', 'field_wrap_end', 'wrapper_end' ];

		// Before method is called.
		$this->assertEmpty( $property->getValue( $field ) );

		// After method is called.
		// Test 1.
		$method->invoke( $field );
		$this->assertNotEmpty( $property->getValue( $field ) );
		$this->assertEquals( $accepted_html_order, $property->getValue( $field ) );

		// Test 2.
		$custom_output_order = [ 'label', 'desc', 'errors', 'field_markup' ];
		$field->args['output_order'] = $custom_output_order;
		$method->invoke( $field );
		$this->assertNotEmpty( $property->getValue( $field ) );
		$this->assertEquals( $accepted_html_order, $property->getValue( $field ) );
		$this->assertNotEquals( $custom_output_order, $property->getValue( $field ) );
	}
}
