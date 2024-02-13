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
		$this->assertStringContainsString( '<button type="button" class="ap-btn-insertimage ap-btn-small ap-btn mb-10 ap-mr-5" apajaxbtn aponce="false" apquery="' . esc_js( $btn_args ) . '"><i class="apicon-image ap-mr-3"></i>Insert image</button>', $property->getValue( $field ) );
		$this->assertTrue( did_action( 'ap_editor_buttons' ) > 0 );

		// Test 2.
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
		$this->assertStringContainsString( '<button type="button" class="ap-btn-insertimage ap-btn-small ap-btn mb-10 ap-mr-5" apajaxbtn aponce="false" apquery="' . esc_js( $btn_args ) . '"><i class="apicon-image ap-mr-3"></i>Insert image</button>', $property->getValue( $field ) );
		$this->assertTrue( did_action( 'ap_editor_buttons' ) > 0 );
		$this->logout();
	}
}
