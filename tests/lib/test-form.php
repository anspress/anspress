<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnsPressForm extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress\Form' );
		$this->assertTrue( $class->hasProperty( 'form_name' ) && $class->getProperty( 'form_name' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'args' ) && $class->getProperty( 'args' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'fields' ) && $class->getProperty( 'fields' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'prepared' ) && $class->getProperty( 'prepared' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'errors' ) && $class->getProperty( 'errors' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'values' ) && $class->getProperty( 'values' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'editing' ) && $class->getProperty( 'editing' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'editing_id' ) && $class->getProperty( 'editing_id' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'submitted' ) && $class->getProperty( 'submitted' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'after_form' ) && $class->getProperty( 'after_form' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Form', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'prepare' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'generate_fields' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'generate' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'is_submitted' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'find' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'add_error' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'have_errors' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'get' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'add_field' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'sanitize_validate' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'get_fields_errors' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'field_values' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'get_values' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'after_save' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'set_values' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'save_values_session' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form', 'delete_values_session' ) );
	}

	/**
	 * @covers AnsPress\Form::add_error
	 * @covers AnsPress\Form::have_errors
	 */
	public function testAddHaveErrors() {
		$form = new \AnsPress\Form( 'Sample Form', [] );

		// Test begins.
		// Before adding any error.
		$this->assertFalse( $form->have_errors() );
		$this->assertEmpty( $form->errors );
		$this->assertIsArray( $form->errors );

		// After adding some errors.
		// Test 1.
		$error_code = 'test_error';
		$error_msg = 'This is a test error message';
		$form->add_error( $error_code, $error_msg );
		$this->assertTrue( $form->have_errors() );
		$this->assertNotEmpty( $form->errors );
		$this->assertIsArray( $form->errors );
		$expected = [
			'test_error' => 'This is a test error message',
		];
		$this->assertEquals( $expected, $form->errors );

		// Test 2.
		$error_code = 'new_error';
		$error_msg = 'This is a new error message';
		$form->add_error( $error_code, $error_msg );
		$this->assertTrue( $form->have_errors() );
		$this->assertNotEmpty( $form->errors );
		$this->assertIsArray( $form->errors );
		$expected = [
			'test_error' => 'This is a test error message',
			'new_error' => 'This is a new error message',
		];
		$this->assertEquals( $expected, $form->errors );
	}

	/**
	 * @covers AnsPress\Form::is_submitted
	 */
	public function testIsSubmitted() {
		// Test 1.
		$form = new \AnsPress\Form( 'Sample Form', [] );
		$this->assertFalse( $form->submitted );
		$this->assertFalse( $form->is_submitted() );
		$this->assertFalse( $form->submitted );

		// Test 2.
		$form = new \AnsPress\Form( 'Sample Form', [] );
		$_REQUEST['Sample Form_nonce'] = wp_create_nonce( 'Sample Form' );
		$_REQUEST['Sample Form_submit'] = true;
		$this->assertFalse( $form->submitted );
		$this->assertTrue( $form->is_submitted() );
		$this->assertTrue( $form->submitted );
		unset( $_REQUEST['Sample Form_nonce'], $_REQUEST['Sample Form_submit'] );

		// Test 3.
		$form = new \AnsPress\Form( 'Sample Form', [] );
		$_REQUEST['Sample Form_nonce'] = 'invalid_nonce';
		$_REQUEST['Sample Form_submit'] = true;
		$this->assertFalse( $form->submitted );
		$this->assertFalse( $form->is_submitted() );
		$this->assertFalse( $form->submitted );
		unset( $_REQUEST['Sample Form_nonce'], $_REQUEST['Sample Form_submit'] );

		// Test 4.
		$form = new \AnsPress\Form( 'Sample Form', [] );
		$_REQUEST['Sample Form_nonce'] = wp_create_nonce( 'Sample Form' );
		$_REQUEST['Sample Form_submit'] = false;
		$this->assertFalse( $form->submitted );
		$this->assertFalse( $form->is_submitted() );
		$this->assertFalse( $form->submitted );
		unset( $_REQUEST['Sample Form_nonce'], $_REQUEST['Sample Form_submit'] );
	}

	/**
	 * @covers AnsPress\Form::get
	 */
	public function testGet() {
		$form = new \AnsPress\Form( 'Sample Form', [] );
		$test_args = [
			'parent' => [
				'child' => [
					'grand_child' => 'value',
				],
			],
		];

		// Test for default values.
		$this->assertEquals( true, $form->get( 'form_tag' ) );
		$this->assertEquals( true, $form->get( 'submit_button' ) );
		$this->assertEquals( 'Submit', $form->get( 'submit_label' ) );
		$this->assertEquals( false, $form->get( 'editing' ) );
		$this->assertEquals( 0, $form->get( 'editing_id' ) );

		// Test 1.
		$this->assertEquals( [ 'child' => [ 'grand_child' => 'value' ] ], $form->get( 'parent', null, $test_args ) );
		$this->assertEquals( [ 'grand_child' => 'value' ], $form->get( 'parent.child', null, $test_args ) );
		$this->assertEquals( 'value', $form->get( 'parent.child.grand_child', null, $test_args ) );

		// Test 2.
		$this->assertEquals( 'default_value', $form->get( 'non_existing_parent', 'default_value', $test_args ) );
		$this->assertEquals( 'default_value', $form->get( 'parent.non_existing_child', 'default_value', $test_args ) );
		$this->assertEquals( 'default_value', $form->get( 'parent.child.non_existing_grand_child', 'default_value', $test_args ) );

		// Test 3.
		$this->assertNull( $form->get( 'non_existing_parent', null, $test_args ) );
		$this->assertNull( $form->get( 'parent.non_existing_child', null, $test_args ) );
		$this->assertNull( $form->get( 'parent.child.non_existing_grand_child', null, $test_args ) );
	}

	/**
	 * @covers AnsPress\Form::generate
	 */
	public function testGenerateForEmptyFields() {
		$form = new \AnsPress\Form( 'Sample Form', [ 'fields' => [] ] );
		ob_start();
		$form->generate();
		$output = ob_get_clean();
		$this->assertEquals( '<p class="ap-form-nofields">No fields found for form: Sample Form</p>', $output );
	}

	/**
	 * @covers AnsPress\Form::prepare
	 */
	public function testPrepareForEmptyFields() {
		$form = new \AnsPress\Form( 'Sample Form', [ 'fields' => [] ] );
		$output = $form->prepare();
		$this->assertFalse( $form->prepared );
		$this->assertInstanceof( 'AnsPress\Form', $output );
	}
}
