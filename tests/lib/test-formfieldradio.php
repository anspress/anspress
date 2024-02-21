<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnsPressFormFieldRadio extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress\Form\Field\Radio' );
		$this->assertTrue( $class->hasProperty( 'type' ) && $class->getProperty( 'type' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Radio', 'prepare' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Radio', 'field_markup' ) );
	}

	/**
	 * @covers AnsPress\Form\Field\Radio::prepare
	 */
	public function testPrepare() {
		// Test 1.
		$field = new \AnsPress\Form\Field\Radio( 'Sample Form', 'sample-form', [] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'sanitize_cb' );
		$property->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $property->getValue( $field ) );
		$this->assertEquals( [ 'text_field' ], $property->getValue( $field ) );
		$this->assertEquals( [ 'label' => 'AnsPress Radio Field' ], $field->args );

		// Test 2.
		$field = new \AnsPress\Form\Field\Radio( 'Sample Form', 'sample-form', [
			'label'   => 'Test Label',
			'value'   => 'test',
			'options' => [
				'option1' => 'Test Option',
				'option2' => 'Test Option 2',
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'sanitize_cb' );
		$property->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $property->getValue( $field ) );
		$this->assertEquals( [ 'text_field' ], $property->getValue( $field ) );
		$this->assertEquals( [ 'label' => 'Test Label', 'value' => 'test', 'options' => [ 'option1' => 'Test Option', 'option2' => 'Test Option 2' ] ], $field->args );

		// Test 3.
		$field = new \AnsPress\Form\Field\Radio( 'Sample Form', 'sample-form', [
			'sanitize' => 'custom_sanitize_cb',
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'sanitize_cb' );
		$property->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $property->getValue( $field ) );
		$this->assertEquals( [ 'text_field', 'custom_sanitize_cb' ], $property->getValue( $field ) );
		$this->assertEquals( [ 'label' => 'AnsPress Radio Field', 'sanitize' => 'custom_sanitize_cb' ], $field->args );
	}

	/**
	 * @covers AnsPress\Form\Field\Radio::field_markup
	 */
	public function testFieldMarkup() {
		// Set up the action hook callback
		$callback_triggered = false;
		add_action( 'ap_after_field_markup', function( $field ) use ( &$callback_triggered ) {
			$this->assertInstanceOf( 'AnsPress\Form\Field\Radio', $field );
			$this->assertInstanceOf( 'AnsPress\Form\Field', $field );
			$callback_triggered = true;
		} );

		// Test begins.
		// Test 1.
		$callback_triggered = false;
		$field = new \AnsPress\Form\Field\Radio( 'Sample Form', 'sample-form', [
			'value'   => 'test',
			'options' => [
				'test'  => 'Test Option',
				'test2' => 'Test Option 2',
			]
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertFalse( $callback_triggered );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertEquals( '<label><input type="radio" value="test" name="Sample Form[sample-form]" id="SampleFormsample-formtest" class="ap-form-control"  checked=\'checked\'/>Test Option</label><label><input type="radio" value="test2" name="Sample Form[sample-form]" id="SampleFormsample-formtest2" class="ap-form-control" />Test Option 2</label>', $property->getValue( $field ) );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );

		// Test 2.
		$callback_triggered = false;
		$field = new \AnsPress\Form\Field\Radio( 'Sample Form', 'sample-form', [
			'value'   => '',
			'options' => [
				'test'  => 'Test Option',
				'test2' => 'Test Option 2',
				'test3' => 'Test Option 3',
			]
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertFalse( $callback_triggered );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertEquals( '<label><input type="radio" value="test" name="Sample Form[sample-form]" id="SampleFormsample-formtest" class="ap-form-control" />Test Option</label><label><input type="radio" value="test2" name="Sample Form[sample-form]" id="SampleFormsample-formtest2" class="ap-form-control" />Test Option 2</label><label><input type="radio" value="test3" name="Sample Form[sample-form]" id="SampleFormsample-formtest3" class="ap-form-control" />Test Option 3</label>', $property->getValue( $field ) );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );

		// Test 3.
		$callback_triggered = false;
		$field = new \AnsPress\Form\Field\Radio( 'Sample Form', 'sample-form', [] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertFalse( $callback_triggered );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertEmpty( $property->getValue( $field ) );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );
	}
}
