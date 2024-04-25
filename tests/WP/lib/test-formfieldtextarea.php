<?php

namespace AnsPress\Tests\WP;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnsPressFormFieldTextarea extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress\Form\Field\Textarea' );
		$this->assertTrue( $class->hasProperty( 'type' ) && $class->getProperty( 'type' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Textarea', 'prepare' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Textarea', 'field_markup' ) );
	}

	/**
	 * @covers AnsPress\Form\Field\Textarea::prepare
	 */
	public function testPrepare() {
		// Test 1.
		$field = new \AnsPress\Form\Field\Textarea( 'Sample Form', 'sample-form', [] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'sanitize_cb' );
		$property->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $property->getValue( $field ) );
		$this->assertEquals( [ 'textarea_field' ], $property->getValue( $field ) );
		$this->assertEquals( [ 'label' => 'AnsPress Textarea Field', 'attr' => [ 'rows' => 8 ] ], $field->args );

		// Test 2.
		$field = new \AnsPress\Form\Field\Textarea( 'Sample Form', 'sample-form', [
			'value' => 'This is textarea custom value',
			'type'  => 'textarea',
			'label' => 'Test Label',
			'attr'  => [
				'rows' => 10,
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'sanitize_cb' );
		$property->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $property->getValue( $field ) );
		$this->assertEquals( [ 'textarea_field' ], $property->getValue( $field ) );
		$this->assertEquals( [ 'label' => 'Test Label', 'type'  => 'textarea', 'attr' => [ 'rows' => 10 ], 'value' => 'This is textarea custom value', ], $field->args );
	}

	/**
	 * @covers AnsPress\Form\Field\Textarea::field_markup
	 */
	public function testFieldMarkup() {
		// Set up the action hook callback
		$callback_triggered = false;
		add_action( 'ap_after_field_markup', function( $field ) use ( &$callback_triggered ) {
			$this->assertInstanceOf( 'AnsPress\Form\Field\Textarea', $field );
			$this->assertInstanceOf( 'AnsPress\Form\Field', $field );
			$callback_triggered = true;
		} );

		// Test begins.
		// Test 1.
		$callback_triggered = false;
		$field = new \AnsPress\Form\Field\Textarea( 'Sample Form', 'sample-form', [
			'value' => 'This is textarea custom value',
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertFalse( $callback_triggered );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertEquals( '<textarea name="Sample Form[sample-form]" id="SampleForm-sample-form" class="ap-form-control " rows="8">This is textarea custom value</textarea>', $property->getValue( $field ) );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );

		// Test 2.
		$callback_triggered = false;
		$field = new \AnsPress\Form\Field\Textarea( 'Sample Form', 'sample-form', [
			'value' => '',
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertFalse( $callback_triggered );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertEquals( '<textarea name="Sample Form[sample-form]" id="SampleForm-sample-form" class="ap-form-control " rows="8"></textarea>', $property->getValue( $field ) );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );

		// Test 3.
		$callback_triggered = false;
		$field = new \AnsPress\Form\Field\Textarea( 'Sample Form', 'sample-form', [
			'value' => 'This is textarea custom value',
			'attr' => [
				'rows'        => 10,
				'placeholder' => 'This is placeholder',
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertFalse( $callback_triggered );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertEquals( '<textarea name="Sample Form[sample-form]" id="SampleForm-sample-form" class="ap-form-control " rows="10" placeholder="This is placeholder">This is textarea custom value</textarea>', $property->getValue( $field ) );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );

		// Test 4.
		$callback_triggered = false;
		$field = new \AnsPress\Form\Field\Textarea( 'Sample Form', 'sample-form', [
			'value' => '',
			'class' => 'custom-class',
			'attr'  => [
				'rows' => 7,
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertFalse( $callback_triggered );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertEquals( '<textarea name="Sample Form[sample-form]" id="SampleForm-sample-form" class="ap-form-control custom-class" rows="7"></textarea>', $property->getValue( $field ) );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );
	}
}
