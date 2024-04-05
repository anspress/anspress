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
	 * @covers AnsPress\Form::generate
	 */
	public function testGenerateForHiddenFields() {
		$form = new \AnsPress\Form( 'Sample Form', [
			'fields'        => [
				'input_field' => [
					'label' => 'Input Field',
				],
			],
			'hidden_fields' => [
				[
					'name'  => 'hidden_field',
					'value' => 'hidden_value',
				],
				[
					'name'  => 'hidden_field_2',
					'value' => 'hidden_value_2',
				],
			]
		] );
		ob_start();
		$form->generate();
		$output = ob_get_clean();
		$this->assertStringContainsString( '<input type="hidden" name="hidden_field" value="hidden_value" />', $output );
		$this->assertStringContainsString( '<input type="hidden" name="hidden_field_2" value="hidden_value_2" />', $output );
	}

	/**
	 * @covers AnsPress\Form::generate
	 */
	public function testGenerateForCustomFormActionLink() {
		$form = new \AnsPress\Form( 'Sample Form', [
			'fields' => [
				'input_field' => [
					'label' => 'Input Field',
				],
			],
		] );
		ob_start();
		$form->generate( [ 'form_action' => 'http://example.com' ] );
		$output = ob_get_clean();
		$this->assertStringContainsString( '<form id="Sample Form" name="Sample Form" method="POST" enctype="multipart/form-data" action="http://example.com"  apform>', $output );
	}

	/**
	 * @covers AnsPress\Form::generate
	 */
	public function testGenerateForAjaxSubmitSetToFalse() {
		$form = new \AnsPress\Form( 'Sample Form', [
			'fields' => [
				'input_field' => [
					'label' => 'Input Field',
				],
			],
		] );
		ob_start();
		$form->generate( [ 'ajax_submit' => false ] );
		$output = ob_get_clean();
		$this->assertStringContainsString( '<form id="Sample Form" name="Sample Form" method="POST" enctype="multipart/form-data" action="" >', $output );
	}

	/**
	 * @covers AnsPress\Form::generate
	 */
	public function testGenerateForFormTagSetToFalse() {
		$form = new \AnsPress\Form( 'Sample Form', [
			'fields' => [
				'input_field' => [
					'label' => 'Input Field',
				],
			],
			'form_tag' => false,
		] );
		ob_start();
		$form->generate();
		$output = ob_get_clean();
		$this->assertStringNotContainsString( '<form id="Sample Form" name="Sample Form" method="POST" enctype="multipart/form-data" action="" >', $output );
	}

	/**
	 * @covers AnsPress\Form::generate
	 */
	public function testGenerateForFormFieldsHavingError() {
		$form = anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [
			'fields' => [
				'checkbox_field' => [
					'type'  => 'checkbox',
					'label' => 'Input Field',
				],
				'radio_field'    => [
					'type'  => 'radio',
					'label' => 'Input Field',
					'options' => [
						'option1' => 'Test Option',
						'option2' => 'Test Option 2',
					],
				],
				'select_field'   => [
					'type'  => 'select',
					'label' => 'Input Field',
					'options' => [
						'option1' => 'Test Option',
						'option2' => 'Test Option 2',
					],
				],
			]
		] );
		$form->add_error( 'checkbox_field', 'Error on checkbox field' );
		$form->add_error( 'radio_field', 'Error on radio field' );
		$form->add_error( 'select_field', 'Error on select field' );
		ob_start();
		$form->generate();
		$output = ob_get_clean();
		$this->assertStringContainsString( '<div class="ap-form-errors">', $output );
		$this->assertStringContainsString( '<span class="ap-form-error ecode-checkbox_field">Error on checkbox field</span>', $output );
		$this->assertStringContainsString( '<span class="ap-form-error ecode-radio_field">Error on radio field</span>', $output );
		$this->assertStringContainsString( '<span class="ap-form-error ecode-select_field">Error on select field</span>', $output );
	}

	/**
	 * @covers AnsPress\Form::generate
	 */
	public function testGenerateForSubmitButtonSetToFalse() {
		$form = new \AnsPress\Form( 'Sample Form', [
			'fields' => [
				'input_field' => [
					'label' => 'Input Field',
				],
			],
			'submit_button' => false,
		] );
		ob_start();
		$form->generate();
		$output = ob_get_clean();
		$this->assertStringNotContainsString( '<button type="submit" class="ap-btn ap-btn-submit">Submit</button>', $output );
	}

	/**
	 * @covers AnsPress\Form::generate
	 */
	public function testGenerateForAfterForm() {
		$form = new \AnsPress\Form( 'Sample Form', [
			'fields'    => [
				'input_field' => [
					'label' => 'Input Field',
				],
			],
		] );
		$form->after_form = '<p>After Form</p>';
		ob_start();
		$form->generate();
		$output = ob_get_clean();
		$this->assertStringContainsString( '<p>After Form</p>', $output );
	}

	/**
	 * @covers AnsPress\Form::generate
	 */
	public function testGenerateForActionHookTriggered() {
		// Action hook triggered.
		$callback_triggered = false;
		add_action( 'ap_after_form_field', function() use ( &$callback_triggered ) {
			$callback_triggered = true;
			echo 'This content is added after form field';
		} );

		$form = new \AnsPress\Form( 'Sample Form', [
			'fields' => [
				'input_field' => [
					'label' => 'Input Field',
				],
			],
		] );
		ob_start();
		$form->generate();
		$output = ob_get_clean();
		$this->assertStringContainsString( 'This content is added after form field', $output );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_after_form_field' ) > 0 );
	}

	/**
	 * @covers AnsPress\Form::generate
	 */
	public function testGenerate() {
		$form = new \AnsPress\Form( 'Sample Form', [
			'fields' => [
				'input_field' => [
					'label' => 'Input Field',
				],
			],
		] );
		ob_start();
		$form->generate();
		$output = ob_get_clean();
		$this->assertStringContainsString( '<form id="Sample Form" name="Sample Form" method="POST" enctype="multipart/form-data" action=""  apform>', $output );
		$this->assertStringContainsString( $form->generate_fields(), $output );
		$this->assertStringContainsString( '<input type="hidden" name="ap_form_name" value="Sample Form" />', $output );
		$this->assertStringContainsString( '<button type="submit" class="ap-btn ap-btn-submit">Submit</button>', $output );
		$this->assertStringContainsString( '<input type="hidden" name="Sample Form_nonce" value="' . esc_attr( wp_create_nonce( 'Sample Form' ) ) . '" />', $output );
		$this->assertStringContainsString( '<input type="hidden" name="Sample Form_submit" value="true" />', $output );
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

	/**
	 * @covers AnsPress\Form::prepare
	 */
	public function testPrepareWithAllowPrivatePostsOptionBeingSetToFalse() {
		ap_opt( 'allow_private_posts', false );
		$form = new \AnsPress\Form( 'Sample Form', [
			'fields' => [
				'is_private' => [
					'type'  => 'checkbox',
					'label' => 'Is Private',
				],
			]
		] );
		$form->prepare();
		$this->assertFalse( isset( $form->fields['is_private'] ) );
		$this->assertTrue( $form->prepared );

		// Reset.
		ap_opt( 'allow_private_posts', true );
	}

	/**
	 * @covers AnsPress\Form::prepare
	 */
	public function testPrepareWithAllowPrivatePostsOptionBeingSetToTrue() {
		$form = new \AnsPress\Form( 'Sample Form', [
			'fields' => [
				'is_private' => [
					'type'  => 'checkbox',
					'label' => 'Is Private',
				],
			]
		] );
		$form->prepare();
		$this->assertTrue( isset( $form->fields['is_private'] ) );
		$this->assertTrue( $form->prepared );
	}

	/**
	 * @covers AnsPress\Form::prepare
	 */
	public function testPrepareForPassingInputTypeAsFieldArg() {
		$form = new \AnsPress\Form( 'Sample Form', [
			'fields' => [
				'checkbox_field' => [
					'type'  => 'checkbox',
					'label' => 'Input Field',
				],
				'radio_field'    => [
					'type'  => 'radio',
					'label' => 'Input Field',
					'options' => [
						'option1' => 'Test Option',
						'option2' => 'Test Option 2',
					],
				],
				'select_field'   => [
					'type'  => 'select',
					'label' => 'Input Field',
					'options' => [
						'option1' => 'Test Option',
						'option2' => 'Test Option 2',
					],
				],
			]
		] );
		$result = $form->prepare();
		$this->assertInstanceof( 'AnsPress\Form\Field\Checkbox', $form->fields['checkbox_field'] );
		$this->assertInstanceof( 'AnsPress\Form\Field\Radio', $form->fields['radio_field'] );
		$this->assertInstanceof( 'AnsPress\Form\Field\Select', $form->fields['select_field'] );
		$this->assertTrue( $form->prepared );
		$this->assertInstanceof( 'AnsPress\Form', $result );
	}

	/**
	 * @covers AnsPress\Form::prepare
	 */
	public function testPrepareForNotPassingInputTypeAsFieldArg() {
		$form = new \AnsPress\Form( 'Sample Form', [
			'fields' => [
				'input_field' => [
					'label' => 'Input Field',
				],
			]
		] );
		$result = $form->prepare();
		$this->assertInstanceof( 'AnsPress\Form\Field\Input', $form->fields['input_field'] );
		$this->assertTrue( $form->prepared );
		$this->assertInstanceof( 'AnsPress\Form', $result );
	}

	/**
	 * @covers AnsPress\Form::add_field
	 */
	public function testAddFieldWithStringPath() {
		$form = new \AnsPress\Form( 'Sample Form', [] );
		$form->add_field( 'parent.child', 'value' );
		$expected = [
			'parent' => [
				'fields' => [
					'child' => [
						'fields' => [
							'fields' => 'value',
						]
					]
				]
			],
		];
		$this->assertEquals( $expected, $form->args['fields'] );
	}

	/**
	 * @covers AnsPress\Form::add_field
	 */
	public function testAddFieldWithArrayPath() {
		$form = new \AnsPress\Form( 'Sample Form', [] );
		$form->add_field( [ 'parent', 'child' ], 'value' );
		$expected = [
			'parent' => [
				'fields' => [
					'child' => [
						'fields' => [
							'fields' => 'value',
						]
					]
				]
			],
		];
		$this->assertEquals( $expected, $form->args['fields'] );
	}

	/**
	 * @covers AnsPress\Form::add_field
	 */
	public function testAddFieldWithNestedStringPath() {
		$form = new \AnsPress\Form( 'Sample Form', [] );
		$form->add_field( 'parent.child.grandchild', 'value' );
		$expected = [
			'parent' => [
				'fields' => [
					'child' => [
						'fields' => [
							'grandchild' => [
								'fields' => [
									'fields' => 'value',
								]
							]
						]
					]
				]
			],
		];
		$this->assertEquals( $expected, $form->args['fields'] );
	}

	/**
	 * @covers AnsPress\Form::add_field
	 */
	public function testAddFieldWithNestedArrayPath() {
		$form = new \AnsPress\Form( 'Sample Form', [] );
		$form->add_field( [ 'parent', 'child', 'grandchild' ], 'value' );
		$expected = [
			'parent' => [
				'fields' => [
					'child' => [
						'fields' => [
							'grandchild' => [
								'fields' => [
									'fields' => 'value',
								]
							]
						]
					]
				]
			],
		];
		$this->assertEquals( $expected, $form->args['fields'] );
	}
}
