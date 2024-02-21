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
}
