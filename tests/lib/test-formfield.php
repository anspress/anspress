<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnsPressFormField extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress\Form\Field' );
		$this->assertTrue( $class->hasProperty( 'field_name' ) && $class->getProperty( 'field_name' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'original_name' ) && $class->getProperty( 'original_name' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'form_name' ) && $class->getProperty( 'form_name' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'field_id' ) && $class->getProperty( 'field_id' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'args' ) && $class->getProperty( 'args' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'html' ) && $class->getProperty( 'html' )->isProtected() );
		$this->assertTrue( $class->hasProperty( 'output_order' ) && $class->getProperty( 'output_order' )->isProtected() );
		$this->assertTrue( $class->hasProperty( 'errors' ) && $class->getProperty( 'errors' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'type' ) && $class->getProperty( 'type' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'child' ) && $class->getProperty( 'child' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'editing' ) && $class->getProperty( 'editing' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'editing_id' ) && $class->getProperty( 'editing_id' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'validated' ) && $class->getProperty( 'validated' )->isProtected() );
		$this->assertTrue( $class->hasProperty( 'validate_cb' ) && $class->getProperty( 'validate_cb' )->isProtected() );
		$this->assertTrue( $class->hasProperty( 'sanitize_cb' ) && $class->getProperty( 'sanitize_cb' )->isProtected() );
		$this->assertTrue( $class->hasProperty( 'sanitized' ) && $class->getProperty( 'sanitized' )->isProtected() );
		$this->assertTrue( $class->hasProperty( 'sanitized_value' ) && $class->getProperty( 'sanitized_value' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'value' ) && $class->getProperty( 'value' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'prepare' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'form' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'sanitize_cb' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'validate_cb' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'get' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'add_html' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'html_order' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'output' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'unsafe_value' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'isset_value' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'value' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'field_wrap_start' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'field_wrap_end' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'label' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'have_errors' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'add_error' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'errors' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'id' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'field_markup' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'desc' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'wrapper_start' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'wrapper_end' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'get_attr' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'common_attr' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'custom_attr' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'sanitize_cb_args' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'sanitize' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'validate' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'pre_get' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'after_save' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'save_cb' ) );
	}

	/**
	 * @covers AnsPress\Form\Field::field_wrap_start
	 * @covers AnsPress\Form\Field::field_wrap_end
	 */
	public function testFieldWrapStartEnd() {
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-form', [] );

		// Test begins.
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertEquals( '', $property->getValue( $field ) );

		// Test for field_wrap_start.
		$method = $reflection->getMethod( 'field_wrap_start' );
		$method->setAccessible( true );
		$method->invoke( $field );
		$this->assertStringContainsString( '<div class="ap-field-group-w">', $property->getValue( $field ) );

		// Test for field_wrap_end.
		$method = $reflection->getMethod( 'field_wrap_end' );
		$method->setAccessible( true );
		$method->invoke( $field );
		$this->assertStringContainsString( '</div>', $property->getValue( $field ) );
	}

	/**
	 * @covers AnsPress\Form\Field::sanitize_cb_args
	 */
	public function testSanitizeCbArgs() {
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-form', [] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'sanitize_cb_args' );
		$method->setAccessible( true );

		// Test begins.
		// Test 1.
		$result = $method->invoke( $field, '' );
		$this->assertIsArray( $result );
		$this->assertEquals( [ '' ], $result );

		// Test 2.
		$result = $method->invoke( $field, 'test_cb' );
		$this->assertIsArray( $result );
		$this->assertEquals( [ 'test_cb' ], $result );

		// Test 3.
		$result = $method->invoke( $field, [ 'callback' ] );
		$this->assertIsArray( $result );
		$this->assertEquals( [ [ 'callback' ] ], $result );

		// Test 4.
		$result = $method->invoke( $field, [ 'test_1', 'test_2' ] );
		$this->assertIsArray( $result );
		$this->assertEquals( [ [ 'test_1', 'test_2' ] ], $result );

		// Test 5.
		$result = $method->invoke( $field, [ 'callback_1' => 'Test Callback 2', 'callback_2' => 'Test Callback 2' ] );
		$this->assertIsArray( $result );
		$this->assertEquals( [ [ 'callback_1' => 'Test Callback 2', 'callback_2' => 'Test Callback 2' ] ], $result );
	}

	/**
	 * @covers AnsPress\Form\Field::have_errors
	 */
	public function testHaveErrors() {
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-form', [] );

		// Test begins.
		// Test 1.
		$field->errors = [];
		$this->assertFalse( $field->have_errors() );

		// Test 2.
		$field->errors = [ 'error' ];
		$this->assertTrue( $field->have_errors() );
	}

	/**
	 * @covers AnsPress\Form\Field::add_html
	 */
	public function testAddHTML() {
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-form', [] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );

		// Test begins.
		// Test 1.
		$this->assertEmpty( $property->getValue( $field ) );

		// Test 2.
		$field->add_html( '' );
		$this->assertEmpty( $property->getValue( $field ) );
		$this->assertEquals( '', $property->getValue( $field ) );

		// Test 3.
		$field->add_html( 'Test HTML' );
		$this->assertEquals( 'Test HTML', $property->getValue( $field ) );

		// Test 4.
		$field->add_html( 'Another Test HTML' );
		$this->assertEquals( 'Test HTMLAnother Test HTML', $property->getValue( $field ) );

		// Test 5.
		$field->add_html( '' );
		$this->assertEquals( 'Test HTMLAnother Test HTML', $property->getValue( $field ) );

		// Test 6.
		$field->add_html( '<span class="question-answer-form">QA Form</span>' );
		$this->assertEquals( 'Test HTMLAnother Test HTML<span class="question-answer-form">QA Form</span>', $property->getValue( $field ) );
	}

	/**
	 * @covers AnsPress\Form\Field::pre_get
	 */
	public function testPreGet() {
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-form', [] );
		$result = $field->pre_get();
		$this->assertNull( $result );
	}

	/**
	 * @covers AnsPress\Form\Field::after_save
	 */
	public function testAfterSave() {
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-form', [] );

		// Test 1.
		$args = [ 'post_id' => $this->factory->post->create( [ 'post_type' => 'question' ] ) ];
		$result = $field->after_save( $args );
		$this->assertNull( $result );

		// Test 2.
		$result = $field->after_save( $args );
		$this->assertNull( $result );
	}

	/**
	 * @covers AnsPress\Form\Field::field_markup
	 */
	public function testFieldMarkup() {
		// Set up the action hook callback
		$callback_triggered = false;
		add_action( 'ap_before_field_markup', function( $field ) use ( &$callback_triggered ) {
			$this->assertInstanceOf( 'AnsPress\Form\Field', $field );
			$callback_triggered = true;
		} );

		// Test begins.
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-form', [] );

		// Before the action is triggered.
		$this->assertFalse( $callback_triggered );
		$this->assertFalse( did_action( 'ap_before_field_markup' ) > 0 );

		// After the action is triggered.
		$field->field_markup();
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_before_field_markup' ) > 0 );
	}

	/**
	 * @covers AnsPress\Form\Field::html_order
	 */
	public function testHTMLOrder() {
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-form', [] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'html_order' );
		$method->setAccessible( true );
		$property = $reflection->getProperty( 'output_order' );
		$property->setAccessible( true );

		// Test begins.
		// Test 1.
		$default_output_order = [ 'wrapper_start', 'label', 'field_wrap_start', 'errors', 'field_markup', 'desc', 'field_wrap_end', 'wrapper_end' ];
		$method->invoke( $field );
		$this->assertIsArray( $property->getValue( $field ) );
		$this->assertEquals( $default_output_order, $property->getValue( $field ) );

		// Test 2.
		$custom_output_order = [ 'wrapper_start', 'label', 'desc', 'errors', 'field_wrap_start', 'field_markup', 'field_wrap_end', 'wrapper_end' ];
		$field->args['output_order'] = $custom_output_order;
		$method->invoke( $field );
		$this->assertIsArray( $property->getValue( $field ) );
		$this->assertEquals( $custom_output_order, $property->getValue( $field ) );
		$this->assertNotEquals( $default_output_order, $property->getValue( $field ) );

		// Test 3.
		$new_custom_output_order = [ 'label', 'desc', 'errors', 'field_markup' ];
		$field->args['output_order'] = $new_custom_output_order;
		$method->invoke( $field );
		$this->assertIsArray( $property->getValue( $field ) );
		$this->assertEquals( $new_custom_output_order, $property->getValue( $field ) );
		$this->assertNotEquals( $custom_output_order, $property->getValue( $field ) );
		$this->assertNotEquals( $default_output_order, $property->getValue( $field ) );

		// Test 4.
		$field->args['output_order'] = [];
		$method->invoke( $field );
		$this->assertIsArray( $property->getValue( $field ) );
		$this->assertEquals( $default_output_order, $property->getValue( $field ) );
		$this->assertNotEquals( $custom_output_order, $property->getValue( $field ) );
		$this->assertNotEquals( $new_custom_output_order, $property->getValue( $field ) );
	}
}