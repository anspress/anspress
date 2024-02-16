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

	/**
	 * @covers AnsPress\Form\Field\Group::label
	 */
	public function testLabel() {
		// Test 1.
		$field = new \AnsPress\Form\Field\Group( 'Sample Form', 'sample-form', [
			'label' => 'Test Label',
			'type'  => 'text',
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->label();
		$this->assertNotEmpty( $property->getValue( $field ) );
		$this->assertEquals( '<label class="ap-form-label" for="SampleFormsample-form">Test Label</label>', $property->getValue( $field ) );

		// Test 2.
		$field = new \AnsPress\Form\Field\Group( 'Sample Form', 'sample-form', [
			'label'         => 'Test Label',
			'type'          => 'text',
			'delete_button' => true,
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->label();
		$this->assertNotEmpty( $property->getValue( $field ) );
		$this->assertStringContainsString( '<button class="ap-btn ap-repeatable-delete">Delete</button>', $property->getValue( $field ) );
		$this->assertEquals( '<label class="ap-form-label" for="SampleFormsample-form">Test Label<button class="ap-btn ap-repeatable-delete">Delete</button></label>', $property->getValue( $field ) );

		// Test 3.
		$field = new \AnsPress\Form\Field\Group( 'Sample Form', 'sample-form', [
			'label'         => '<i class="sample-icon">Test Label</i>',
			'type'          => 'text',
			'delete_button' => false,
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_id = 'custom-field-id';
		$field->label();
		$this->assertNotEmpty( $property->getValue( $field ) );
		$this->assertStringNotContainsString( '<button class="ap-btn ap-repeatable-delete">Delete</button>', $property->getValue( $field ) );
		$this->assertEquals( '<label class="ap-form-label" for="SampleFormsample-form">&lt;i class=&quot;sample-icon&quot;&gt;Test Label&lt;/i&gt;</label>', $property->getValue( $field ) );

		// Test 4.
		$field = new \AnsPress\Form\Field\Group( 'Sample Form', 'sample-form', [
			'label'         => '<h2>Test Label</h2>',
			'type'          => 'text',
			'delete_button' => true,
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_name = 'custom-field-name';
		$field->label();
		$this->assertNotEmpty( $property->getValue( $field ) );
		$this->assertStringContainsString( '<button class="ap-btn ap-repeatable-delete">Delete</button>', $property->getValue( $field ) );
		$this->assertEquals( '<label class="ap-form-label" for="custom-field-name">&lt;h2&gt;Test Label&lt;/h2&gt;<button class="ap-btn ap-repeatable-delete">Delete</button></label>', $property->getValue( $field ) );
	}
}
