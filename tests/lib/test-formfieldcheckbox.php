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

	/**
	 * @covers AnsPress\Form\Field\Checkbox::field_markup
	 */
	public function testFieldMarkup() {
		// Set up the action hook callback
		$callback_triggered = false;
		add_action( 'ap_after_field_markup', function( $field ) use ( &$callback_triggered ) {
			$this->assertInstanceOf( 'AnsPress\Form\Field\Checkbox', $field );
			$this->assertInstanceOf( 'AnsPress\Form\Field', $field );
			$callback_triggered = true;
		} );

		// Test begins.
		// Test 1.
		$callback_triggered = false;
		$this->assertFalse( $callback_triggered );
		$field = new \AnsPress\Form\Field\Checkbox( 'Sample Form', 'sample-form', [
			'type' => 'checkbox',
			'desc' => 'Test Description',
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertEquals( '<label><input type="checkbox" value="1"  name="Sample Form[sample-form]" id="SampleForm-sample-form" class="ap-form-control "/>Test Description</label>', $property->getValue( $field ) );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );

		// Test 2.
		$callback_triggered = false;
		$this->assertFalse( $callback_triggered );
		$field = new \AnsPress\Form\Field\Checkbox( 'Sample Form', 'sample-form', [
			'type'  => 'checkbox',
			'desc'  => 'Test Description',
			'value' => 1,
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertEquals( '<label><input type="checkbox" value="1"  checked=\'checked\' name="Sample Form[sample-form]" id="SampleForm-sample-form" class="ap-form-control "/>Test Description</label>', $property->getValue( $field ) );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );

		// Test 3.
		$callback_triggered = false;
		$this->assertFalse( $callback_triggered );
		$field = new \AnsPress\Form\Field\Checkbox( 'Sample Form', 'sample-form', [
			'type'  => 'checkbox',
			'desc'  => 'Test Description',
			'value' => 0,
			'attr'  => [
				'placeholder' => 'Placeholder',
				'data-custom' => 'Custom data',
			],
			'class' => 'custom-class',
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertEquals( '<label><input type="checkbox" value="1"  name="Sample Form[sample-form]" id="SampleForm-sample-form" class="ap-form-control custom-class" placeholder="Placeholder" data-custom="Custom data"/>Test Description</label>', $property->getValue( $field ) );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );

		// Test 4.
		$callback_triggered = false;
		$this->assertFalse( $callback_triggered );
		$field = new \AnsPress\Form\Field\Checkbox( 'Sample Form', 'sample-form', [
			'type'    => 'checkbox',
			'desc'    => 'Test Description',
			'value'   => [],
			'options' => [
				'option1' => 'Option 1',
				'option2' => 'Option 2',
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertStringContainsString( '<label><input type="checkbox" value="1" name="Sample Form[sample-form][option1]" id="SampleFormsample-formoption1" class="ap-form-control" />Option 1</label>', $property->getValue( $field ) );
		$this->assertStringContainsString( '<label><input type="checkbox" value="1" name="Sample Form[sample-form][option2]" id="SampleFormsample-formoption2" class="ap-form-control" />Option 2</label>', $property->getValue( $field ) );
		$this->assertStringNotContainsString( 'Test Description', $property->getValue( $field ) );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );

		// Test 5.
		$callback_triggered = false;
		$this->assertFalse( $callback_triggered );
		$field = new \AnsPress\Form\Field\Checkbox( 'Sample Form', 'sample-form', [
			'type'    => 'checkbox',
			'desc'    => 'Test Description',
			'value'   => [ 'option1' => 1 ],
			'options' => [
				'option1' => 'Option 1',
				'option2' => 'Option 2',
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertStringContainsString( '<label><input type="checkbox" value="1" name="Sample Form[sample-form][option1]" id="SampleFormsample-formoption1" class="ap-form-control"  checked=\'checked\'/>Option 1</label>', $property->getValue( $field ) );
		$this->assertStringContainsString( '<label><input type="checkbox" value="1" name="Sample Form[sample-form][option2]" id="SampleFormsample-formoption2" class="ap-form-control" />Option 2</label>', $property->getValue( $field ) );
		$this->assertStringNotContainsString( 'Test Description', $property->getValue( $field ) );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );

		// Test 6.
		$callback_triggered = false;
		$this->assertFalse( $callback_triggered );
		$field = new \AnsPress\Form\Field\Checkbox( 'Sample Form', 'sample-form', [
			'type'    => 'checkbox',
			'desc'    => 'Test Description',
			'value'   => [ 'option1' => 1, 'option2' => 1 ],
			'options' => [
				'option1' => 'Option 1',
				'option2' => 'Option 2',
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertStringContainsString( '<label><input type="checkbox" value="1" name="Sample Form[sample-form][option1]" id="SampleFormsample-formoption1" class="ap-form-control"  checked=\'checked\'/>Option 1</label>', $property->getValue( $field ) );
		$this->assertStringContainsString( '<label><input type="checkbox" value="1" name="Sample Form[sample-form][option2]" id="SampleFormsample-formoption2" class="ap-form-control"  checked=\'checked\'/>Option 2</label>', $property->getValue( $field ) );
		$this->assertStringNotContainsString( 'Test Description', $property->getValue( $field ) );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );

		// Test 7.
		$callback_triggered = false;
		$this->assertFalse( $callback_triggered );
		$field = new \AnsPress\Form\Field\Checkbox( 'Sample Form', 'sample-form', [
			'type'    => 'checkbox',
			'desc'    => 'Test Description',
			'value'   => [ 'option1' => 0, 'option2' => 0 ],
			'options' => [
				'option1' => 'Option 1',
				'option2' => 'Option 2',
			],
			'attr'  => [
				'placeholder' => 'Placeholder',
				'data-custom' => 'Custom data',
			],
			'class' => 'custom-class',
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertStringContainsString( '<label><input type="checkbox" value="1" name="Sample Form[sample-form][option1]" id="SampleFormsample-formoption1" class="ap-form-control"  checked=\'checked\' placeholder="Placeholder" data-custom="Custom data"/>Option 1</label>', $property->getValue( $field ) );
		$this->assertStringContainsString( '<label><input type="checkbox" value="1" name="Sample Form[sample-form][option2]" id="SampleFormsample-formoption2" class="ap-form-control"  checked=\'checked\' placeholder="Placeholder" data-custom="Custom data"/>Option 2</label>', $property->getValue( $field ) );
		$this->assertStringNotContainsString( 'Test Description', $property->getValue( $field ) );
		$this->assertStringNotContainsString( 'custom-class', $property->getValue( $field ) );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );
	}

	/**
	 * @covers AnsPress\Form\Field\Checkbox::unsafe_value
	 */
	public function testUnsafeValue() {
		$field = new \AnsPress\Form\Field\Checkbox( 'Sample Form', 'sample-form', [] );

		// Test begins.
		// Test 1.
		$_REQUEST = 'Request Value';
		$this->assertNull( $field->unsafe_value() );

		// Test 2.
		$_REQUEST = [
			'sample-form' => 'Request Value',
		];
		$this->assertNull( $field->unsafe_value() );

		// Test 3.
		$_REQUEST = [
			'Sample Form' => [
				'sample-form' => 'Request Value',
			]
		];
		$this->assertEquals( 'Request Value', $field->unsafe_value() );

		// Test 4.
		$_REQUEST = [
			'Sample Form' => [
				'sample-form' => [
					'option1' => 'Request Value',
				]
			]
		];
		$this->assertEquals( [ 'option1' => 'Request Value' ], $field->unsafe_value() );

		// Test 5.
		$_REQUEST = [
			'Sample Form' => [
				'sample-form' => '\\\\\\ Request Value \\\\\\',
			]
		];
		$this->assertEquals( '\\ Request Value \\', $field->unsafe_value() );

		// Test 6.
		$_REQUEST = [
			'Tes Form' => [
				'sample-form' => [
					'option1' => 'Request Value',
				]
			]
		];
		$this->assertNull( $field->unsafe_value() );

		// Test 7.
		$_REQUEST = [
			'Sample Form' => [
				'sample-form' => '\\ <script>alert( "Hello World!" )</script> \\'
			]
		];
		$this->assertEquals( ' <script>alert( "Hello World!" )</script> ', $field->unsafe_value() );
	}
}
