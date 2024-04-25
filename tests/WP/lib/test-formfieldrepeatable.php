<?php

namespace AnsPress\Tests\WP;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnsPressFormFieldRepeatable extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress\Form\Field\Repeatable' );
		$this->assertTrue( $class->hasProperty( 'type' ) && $class->getProperty( 'type' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'total_items' ) && $class->getProperty( 'total_items' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'main_fields' ) && $class->getProperty( 'main_fields' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Repeatable', 'prepare' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Repeatable', 'unsafe_value' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Repeatable', 'get_last_field' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Repeatable', 'html_order' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Repeatable', 'get_groups_count' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Repeatable', 'field_markup' ) );
	}

	/**
	 * @covers AnsPress\Form\Field\Repeatable::html_order
	 */
	public function testHTMLOrder() {
		$field = new \AnsPress\Form\Field\Repeatable( 'Sample Form', 'sample-form', [ 'fields' => [] ] );
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
