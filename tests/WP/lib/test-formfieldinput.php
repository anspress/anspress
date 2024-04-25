<?php

namespace AnsPress\Tests\WP;

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
	 * @covers AnsPress\Form\Field\Input::prepare
	 */
	public function testPrepare() {
		// Test 1.
		$field = new \AnsPress\Form\Field\Input( 'Sample Form', 'sample-form', [] );
		$reflection = new \ReflectionClass( $field );
		$sanitize_cb = $reflection->getProperty( 'sanitize_cb' );
		$sanitize_cb->setAccessible( true );
		$validate_cb = $reflection->getProperty( 'validate_cb' );
		$validate_cb->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $sanitize_cb->getValue( $field ) );
		$this->assertIsArray( $validate_cb->getValue( $field ) );
		$this->assertEquals( 'text', $field->subtype );
		$this->assertEquals( [ 'text_field' ], $sanitize_cb->getValue( $field ) );
		$this->assertEmpty( $validate_cb->getValue( $field ) );
		$this->assertEquals( [ 'subtype' => 'text', 'label' => 'AnsPress Input Field' ], $field->args );

		// Test 2.
		$field = new \AnsPress\Form\Field\Input( 'Sample Form', 'sample-form', [ 'subtype' => 'number' ] );
		$reflection = new \ReflectionClass( $field );
		$sanitize_cb = $reflection->getProperty( 'sanitize_cb' );
		$sanitize_cb->setAccessible( true );
		$validate_cb = $reflection->getProperty( 'validate_cb' );
		$validate_cb->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $sanitize_cb->getValue( $field ) );
		$this->assertIsArray( $validate_cb->getValue( $field ) );
		$this->assertEquals( 'number', $field->subtype );
		$this->assertEquals( [ 'intval' ], $sanitize_cb->getValue( $field ) );
		$this->assertEquals( [ 'is_numeric' ], $validate_cb->getValue( $field ) );
		$this->assertEquals( [ 'subtype' => 'number', 'label' => 'AnsPress Input Field' ], $field->args );

		// Test 3.
		$field = new \AnsPress\Form\Field\Input( 'Sample Form', 'sample-form', [ 'subtype' => 'email' ] );
		$reflection = new \ReflectionClass( $field );
		$sanitize_cb = $reflection->getProperty( 'sanitize_cb' );
		$sanitize_cb->setAccessible( true );
		$validate_cb = $reflection->getProperty( 'validate_cb' );
		$validate_cb->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $sanitize_cb->getValue( $field ) );
		$this->assertIsArray( $validate_cb->getValue( $field ) );
		$this->assertEquals( 'email', $field->subtype );
		$this->assertEquals( [ 'email' ], $sanitize_cb->getValue( $field ) );
		$this->assertEquals( [ 'is_email' ], $validate_cb->getValue( $field ) );
		$this->assertEquals( [ 'subtype' => 'email', 'label' => 'AnsPress Input Field' ], $field->args );

		// Test 4.
		$field = new \AnsPress\Form\Field\Input( 'Sample Form', 'sample-form', [ 'subtype' => 'url' ] );
		$reflection = new \ReflectionClass( $field );
		$sanitize_cb = $reflection->getProperty( 'sanitize_cb' );
		$sanitize_cb->setAccessible( true );
		$validate_cb = $reflection->getProperty( 'validate_cb' );
		$validate_cb->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $sanitize_cb->getValue( $field ) );
		$this->assertIsArray( $validate_cb->getValue( $field ) );
		$this->assertEquals( 'url', $field->subtype );
		$this->assertEquals( [ 'esc_url' ], $sanitize_cb->getValue( $field ) );
		$this->assertEquals( [ 'is_url' ], $validate_cb->getValue( $field ) );
		$this->assertEquals( [ 'subtype' => 'url', 'label' => 'AnsPress Input Field' ], $field->args );

		// Test 5.
		$field = new \AnsPress\Form\Field\Input( 'Sample Form', 'sample-form', [
			'subtype' => 'invalid',
			'label'   => 'Custom Label',
			'html'    => '<div>Custom HTML</div>',
		] );
		$reflection = new \ReflectionClass( $field );
		$sanitize_cb = $reflection->getProperty( 'sanitize_cb' );
		$sanitize_cb->setAccessible( true );
		$validate_cb = $reflection->getProperty( 'validate_cb' );
		$validate_cb->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $sanitize_cb->getValue( $field ) );
		$this->assertIsArray( $validate_cb->getValue( $field ) );
		$this->assertEquals( 'text', $field->subtype );
		$this->assertEquals( [ 'text_field' ], $sanitize_cb->getValue( $field ) );
		$this->assertEmpty( $validate_cb->getValue( $field ) );
		$this->assertEquals( [ 'subtype' => 'invalid', 'html' => '<div>Custom HTML</div>' ], $field->args );

		// Test 6.
		$field = new \AnsPress\Form\Field\Input( 'Sample Form', 'sample-form', [
			'subtype'  => 'number',
			'label'    => 'Custom Label',
			'html'     => '<div>Custom HTML</div>',
			'sanitize' => 'custom_sanitize_cb',
			'validate' => 'custom_validate_cb',
		] );
		$reflection = new \ReflectionClass( $field );
		$sanitize_cb = $reflection->getProperty( 'sanitize_cb' );
		$sanitize_cb->setAccessible( true );
		$validate_cb = $reflection->getProperty( 'validate_cb' );
		$validate_cb->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $sanitize_cb->getValue( $field ) );
		$this->assertIsArray( $validate_cb->getValue( $field ) );
		$this->assertEquals( 'number', $field->subtype );
		$this->assertEquals( [ 'intval', 'custom_sanitize_cb' ], $sanitize_cb->getValue( $field ) );
		$this->assertEquals( [ 'is_numeric', 'custom_validate_cb' ], $validate_cb->getValue( $field ) );
		$this->assertEquals( [ 'subtype' => 'number', 'html' => '<div>Custom HTML</div>', 'sanitize' => 'custom_sanitize_cb', 'validate' => 'custom_validate_cb' ], $field->args );

		// Test 7.
		$field = new \AnsPress\Form\Field\Input( 'Sample Form', 'sample-form', [
			'subtype' => 'password',
			'label'   => 'Custom Label',
			'attr'    => [
				'placeholder' => 'Placeholder',
				'data-custom' => 'Custom data',
			],
			'class'   => 'custom-class',
		] );
		$reflection = new \ReflectionClass( $field );
		$sanitize_cb = $reflection->getProperty( 'sanitize_cb' );
		$sanitize_cb->setAccessible( true );
		$validate_cb = $reflection->getProperty( 'validate_cb' );
		$validate_cb->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $sanitize_cb->getValue( $field ) );
		$this->assertIsArray( $validate_cb->getValue( $field ) );
		$this->assertEquals( 'password', $field->subtype );
		$this->assertEquals( [ 'text_field' ], $sanitize_cb->getValue( $field ) );
		$this->assertEmpty( $validate_cb->getValue( $field ) );
		$this->assertEquals( [ 'subtype' => 'password', 'label' => 'Custom Label', 'attr' => [ 'placeholder' => 'Placeholder', 'data-custom' => 'Custom data' ], 'class' => 'custom-class' ], $field->args );
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

	/**
	 * @covers AnsPress\Form\Field\Input::field_markup
	 */
	public function testFieldMarkup() {
		// Set up the action hook callback
		$callback_triggered = false;
		add_action( 'ap_after_field_markup', function( $field ) use ( &$callback_triggered ) {
			$this->assertInstanceOf( 'AnsPress\Form\Field\Input', $field );
			$this->assertInstanceOf( 'AnsPress\Form\Field', $field );
			$callback_triggered = true;
		} );

		// Test begins.
		// Test 1.
		$field = new \AnsPress\Form\Field\Input( 'Sample Form', 'sample-form', [
			'type'  => 'text',
			'value' => 'Sample Value',
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertEquals( '<input type="text" value="Sample Value" name="Sample Form[sample-form]" id="SampleForm-sample-form" class="ap-form-control "/>', $property->getValue( $field ) );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );

		// Test 2.
		$field = new \AnsPress\Form\Field\Input( 'Test Form', 'sample-form', [
			'type'    => 'text',
			'subtype' => 'hidden',
			'value'   => 'Test Value',
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertEquals( '<input type="hidden" value="Test Value" name="Test Form[sample-form]" id="TestForm-sample-form" class="ap-form-control "/>', $property->getValue( $field ) );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );

		// Test 3.
		$field = new \AnsPress\Form\Field\Input( 'Sample Form', 'sample-form', [
			'type'  => 'text',
			'value' => 'Test Value',
			'html'  => '<div>Custom HTML</div>',
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertEquals( '<div>Custom HTML</div>', $property->getValue( $field ) );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );

		// Test 4.
		$field = new \AnsPress\Form\Field\Input( 'Test Form', 'test-form', [
			'type'  => 'text',
			'value' => 'Test Value',
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
		$this->assertEquals( '<input type="text" value="Test Value" name="Test Form[test-form]" id="TestForm-test-form" class="ap-form-control custom-class" placeholder="Placeholder" data-custom="Custom data"/>', $property->getValue( $field ) );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );

		// Test 5.
		$field = new \AnsPress\Form\Field\Input( 'Test Form', 'test-form', [
			'type'  => 'text',
			'value' => 'Test Value',
			'attr'  => [
				'placeholder' => 'Placeholder',
				'data-custom' => 'Custom data',
			],
			'class' => 'custom-class',
			'html'  => '<div>Custom HTML</div>',
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertEquals( '<div>Custom HTML</div>', $property->getValue( $field ) );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );
	}
}
