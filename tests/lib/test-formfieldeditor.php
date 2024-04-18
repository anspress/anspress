<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnsPressFormFieldEditor extends TestCase {

	use Testcases\Common;

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress\Form\Field\Editor' );
		$this->assertTrue( $class->hasProperty( 'type' ) && $class->getProperty( 'type' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'images' ) && $class->getProperty( 'images' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Editor', 'prepare' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Editor', 'image_button' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Editor', 'field_markup' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Editor', 'apcode_cb' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Editor', 'get_attached_images' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Editor', 'image_process' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Editor', 'pre_get' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Editor', 'after_save' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Editor', 'unsafe_value' ) );
	}

	/**
	 * @covers AnsPress\Form\Field\Editor::prepare
	 */
	public function testPrepare() {
		// Test 1.
		$field = new \AnsPress\Form\Field\Editor( 'Sample Form', 'sample-form', [] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'sanitize_cb' );
		$property->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $property->getValue( $field ) );
		$this->assertEquals( [ 'description', 'wp_kses' ], $property->getValue( $field ) );
		$this->assertEquals( [ 'label' => 'AnsPress Editor Field', 'editor_args' => [ 'quicktags' => false ] ], $field->args );

		// Test 2.
		$field = new \AnsPress\Form\Field\Editor( 'Sample Form', 'sample-form', [
			'label'       => 'Test Label',
			'editor_args' => [
				'quicktags' => true,
			],
			'value'       => 'This is a test value'
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'sanitize_cb' );
		$property->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $property->getValue( $field ) );
		$this->assertEquals( [ 'description', 'wp_kses' ], $property->getValue( $field ) );
		$this->assertEquals( [ 'label' => 'Test Label', 'editor_args' => [ 'quicktags' => true ], 'value' => 'This is a test value' ], $field->args );

		// Test 3.
		$field = new \AnsPress\Form\Field\Editor( 'Sample Form', 'sample-form', [
			'sanitize' => 'sanitize_text_field',
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'sanitize_cb' );
		$property->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $property->getValue( $field ) );
		$this->assertEquals( [ 'description', 'wp_kses', 'sanitize_text_field' ], $property->getValue( $field ) );
		$this->assertEquals( [ 'label' => 'AnsPress Editor Field', 'editor_args' => [ 'quicktags' => false ], 'sanitize' => 'sanitize_text_field' ], $field->args );
	}

	/**
	 * @covers AnsPress\Form\Field\Editor::image_button
	 */
	public function testImageButton() {
		$field = new \AnsPress\Form\Field\Editor( 'Sample Form', 'sample-form', [] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );

		// Test for action hook.
		$callback_triggered = false;
		add_action( 'ap_editor_buttons', function( $original_name, $field ) use ( &$callback_triggered ) {
			$this->assertEquals( 'sample-form', $original_name );
			$this->assertInstanceOf( 'AnsPress\Form\Field', $field );
			$this->assertInstanceOf( 'AnsPress\Form\Field\Editor', $field );
			$callback_triggered = true;
		}, 10, 2 );

		// Test begins.
		// Before method is called.
		$this->assertEmpty( $property->getValue( $field ) );
		$this->assertFalse( did_action( 'ap_editor_buttons' ) > 0 );

		// After method is called.
		// Test 1.
		$property->SetValue( $field, '' );
		$btn_args = wp_json_encode(
			array(
				'__nonce'   => wp_create_nonce( 'ap_upload_image' ),
				'action'    => 'ap_upload_modal',
				'form_name' => 'Sample Form',
			)
		);
		$field->image_button();
		$this->assertEmpty( $property->getValue( $field ) );
		$this->assertStringNotContainsString( esc_js( $btn_args ), $property->getValue( $field ) );
		$this->assertStringNotContainsString( '<button type="button" class="ap-btn-insertimage ap-btn-small ap-btn mb-10 ap-mr-5" apajaxbtn aponce="false" apquery="' . esc_js( $btn_args ) . '"><i class="apicon-image ap-mr-3"></i>Insert media</button>', $property->getValue( $field ) );
		$this->assertTrue( did_action( 'ap_editor_buttons' ) > 0 );

		// Test 2.
		$property->SetValue( $field, '' );
		$this->setRole( 'subscriber' );
		$btn_args = wp_json_encode(
			array(
				'__nonce'   => wp_create_nonce( 'ap_upload_image' ),
				'action'    => 'ap_upload_modal',
				'form_name' => 'Sample Form',
			)
		);
		$field->image_button();
		$this->assertNotEmpty( $property->getValue( $field ) );
		$this->assertStringContainsString( esc_js( $btn_args ), $property->getValue( $field ) );
		$this->assertStringContainsString( '<button type="button" class="ap-btn-insertimage ap-btn-small ap-btn mb-10 ap-mr-5" apajaxbtn aponce="false" apquery="' . esc_js( $btn_args ) . '"><i class="apicon-image ap-mr-3"></i>Insert media</button>', $property->getValue( $field ) );
		$this->assertTrue( did_action( 'ap_editor_buttons' ) > 0 );
		$this->logout();

		// Test 3.
		$property->SetValue( $field, '' );
		$this->setRole( 'subscriber' );
		ap_opt( 'allow_upload', false );
		$btn_args = wp_json_encode(
			array(
				'__nonce'   => wp_create_nonce( 'ap_upload_image' ),
				'action'    => 'ap_upload_modal',
				'form_name' => 'Sample Form',
			)
		);
		$field->image_button();
		$this->assertEmpty( $property->getValue( $field ) );
		$this->assertStringNotContainsString( esc_js( $btn_args ), $property->getValue( $field ) );
		$this->assertStringNotContainsString( '<button type="button" class="ap-btn-insertimage ap-btn-small ap-btn mb-10 ap-mr-5" apajaxbtn aponce="false" apquery="' . esc_js( $btn_args ) . '"><i class="apicon-image ap-mr-3"></i>Insert media</button>', $property->getValue( $field ) );
		$this->assertTrue( did_action( 'ap_editor_buttons' ) > 0 );
		$this->logout();

		// Reset the option.
		ap_opt( 'allow_upload', true );
	}

	/**
	 * @covers AnsPress\Form\Field\Editor::unsafe_value
	 */
	public function testUnsafeValue() {
		$field = new \AnsPress\Form\Field\Editor( 'Sample Form', 'sample-form', [] );

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
			'Sample Form' => 'Request Value',
		];
		$this->assertNull( $field->unsafe_value() );

		// Test 4.
		$_REQUEST = [
			'Sample Form' => [
				'sample-form' => 'Request Value',
			],
		];
		$this->assertEquals( 'Request Value', $field->unsafe_value() );

		// Test 5.
		$_REQUEST = [
			'Sample Form' => [
				'sample-form' => [
					'key' => 'Request Value',
				],
			],
		];
		$this->assertEquals( [ 'key' => 'Request Value' ], $field->unsafe_value() );

		// Test 6.
		$_REQUEST = [
			'Sample Form' => [
				'sample-form' => [
					'child' => [
						'grand_child' => '\\\\ This is a test value \\\\',
					],
				],
			],
		];
		$this->assertEquals( [ 'child' => [ 'grand_child' => '\\\\ This is a test value \\\\' ] ], $field->unsafe_value() );

		// Test 7.
		$_REQUEST = [
			'Sample Form' => [
				'sample-form' => '     \\\\ This is a test value \\\\     ',
			],
		];
		$this->assertEquals( '     \\\\ This is a test value \\\\     ', $field->unsafe_value() );

		// Test 8.
		$_REQUEST = [
			'Test Form' => [
				'sample-form' => 'Request Value',
			],
		];
		$this->assertNull( $field->unsafe_value() );

		// Test 9.
		$_REQUEST = [
			'Sample Form' => [
				'test-form' => 'Request Value',
			],
		];
		$this->assertNull( $field->unsafe_value() );
	}

	/**
	 * @covers AnsPress\Form\Field\Editor::apcode_cb
	 */
	public function testApcodeCb() {
		// Test 1.
		$field = new \AnsPress\Form\Field\Editor( 'Sample Form', 'sample-form', [] );
		$args = [
			0 => '[apcode language="php" inline="true"]content[/apcode]',
			3 => ' language="php" inline="true"',
			5 => 'content',
		];
		$this->assertEquals( '[apcode language="php" inline="true"]content[/apcode]', $field->apcode_cb( $args ) );

		// Test 2.
		$field = new \AnsPress\Form\Field\Editor( 'Sample Form', 'sample-form', [] );
		$args = [
			0 => '[apcode]content[/apcode]',
			3 => '',
			5 => 'content',
		];
		$this->assertEquals( '[apcode]content[/apcode]', $field->apcode_cb( $args ) );

		// Test 3.
		$field = new \AnsPress\Form\Field\Editor( 'Sample Form', 'sample-form', [] );
		$args = [
			0 => '[apcode language="php"]content[/apcode]',
			3 => ' language="php"',
			5 => 'content',
		];
		$this->assertEquals( '[apcode language="php"]content[/apcode]', $field->apcode_cb( $args ) );

		// Test 4.
		$field = new \AnsPress\Form\Field\Editor( 'Sample Form', 'sample-form', [] );
		$args = [
			0 => '[apcode inline="true"]content[/apcode]',
			3 => ' inline="true"',
			5 => 'content',
		];
		$this->assertEquals( '[apcode inline="true"]content[/apcode]', $field->apcode_cb( $args ) );
	}

	/**
	 * @covers AnsPress\Form\Field\Editor::get_attached_images
	 */
	public function testGetAttachedImages() {
		// Test 1.
		$field = new \AnsPress\Form\Field\Editor( 'Sample Form', 'sample-form', [
			'value' => '{{apimage "image1.jpg"}}'
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'get_attached_images' );
		$method->setAccessible( true );
		$this->assertEquals( [ 'image1.jpg' ], $method->invoke( $field ) );

		// Test 2.
		$field = new \AnsPress\Form\Field\Editor( 'Sample Form', 'sample-form', [
			'value' => '{{apimage "image1.jpg"}}{{apimage "image2.jpg"}}'
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'get_attached_images' );
		$method->setAccessible( true );
		$this->assertEquals( [ 'image1.jpg', 'image2.jpg' ], $method->invoke( $field ) );

		// Test 3.
		$field = new \AnsPress\Form\Field\Editor( 'Sample Form', 'sample-form', [
			'value' => '{{apimage "image1.jpg"}}This is test image. {{apimage "image2.jpg"}} Test image.{{apimage "image3.jpg"}}'
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'get_attached_images' );
		$method->setAccessible( true );
		$this->assertEquals( [ 'image1.jpg', 'image2.jpg', 'image3.jpg' ], $method->invoke( $field ) );

		// Test 4.
		$field = new \AnsPress\Form\Field\Editor( 'Sample Form', 'sample-form', [
			'value' => 'This is test image'
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'get_attached_images' );
		$method->setAccessible( true );
		$this->assertEmpty( $method->invoke( $field ) );

		// Test 5.
		$field = new \AnsPress\Form\Field\Editor( 'Sample Form', 'sample-form', [
			'value' => ''
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'get_attached_images' );
		$method->setAccessible( true );
		$this->assertEmpty( $method->invoke( $field ) );

		// Test 6.
		$field = new \AnsPress\Form\Field\Editor( 'Sample Form', 'sample-form', [
			'value' => 'These are the images available: {{apimage "image1.jpg"}}{{apimage "image2.jpg"}}{{apimage "image3.jpg"}}'
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'get_attached_images' );
		$method->setAccessible( true );
		$this->assertEquals( [ 'image1.jpg', 'image2.jpg', 'image3.jpg' ], $method->invoke( $field ) );

		// Test 7.
		$field = new \AnsPress\Form\Field\Editor( 'Sample Form', 'sample-form', [
			'value' => '{{apimage image1.jpg}}'
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'get_attached_images' );
		$method->setAccessible( true );
		$this->assertEmpty( $method->invoke( $field ) );

		// Test 8.
		$field = new \AnsPress\Form\Field\Editor( 'Sample Form', 'sample-form', [
			'value' => '{{apimage image1.jpg}}{{apimage "image2.jpg"}}{{apimage ""}}'
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'get_attached_images' );
		$method->setAccessible( true );
		$this->assertEquals( [ 'image2.jpg', '' ], $method->invoke( $field ) );
	}
}
