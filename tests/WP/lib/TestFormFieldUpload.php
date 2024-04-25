<?php

namespace AnsPress\Tests\WP;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestFormFieldUpload extends TestCase {

	use Testcases\Common;

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress\Form\Field\Upload' );
		$this->assertTrue( $class->hasProperty( 'type' ) && $class->getProperty( 'type' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'multiple_upload' ) && $class->getProperty( 'multiple_upload' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'uploaded' ) && $class->getProperty( 'uploaded' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'uploaded_files' ) && $class->getProperty( 'uploaded_files' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'async_upload' ) && $class->getProperty( 'async_upload' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Upload', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Upload', 'prepare' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Upload', 'html_order' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Upload', 'sanitize_cb_args' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Upload', 'format_multiple_files' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Upload', 'unsafe_value' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Upload', 'file_list' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Upload', 'js_args' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Upload', 'field_markup' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Upload', 'replace_temp_image' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Upload', 'file_name_search_replace' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Upload', 'upload_file' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Upload', 'save_uploads' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Upload', 'after_save' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Upload', 'upload' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Upload', 'get_uploaded_files_url' ) );
	}

	/**
	 * @covers AnsPress\Form\Field\Upload::prepare
	 */
	public function testPrepare() {
		// Test 1.
		$field = new \AnsPress\Form\Field\Upload( 'Sample Form', 'sample-form', [] );
		$expected = [
			'label'          => 'AnsPress Upload Field',
			'upload_options' => [
				'multiple'        => false,
				'max_files'       => 1,
				'allowed_mimes'   => [
					'jpeg|jpg' => 'image/jpeg',
					'png'      => 'image/png',
					'gif'      => 'image/gif',
				],
				'label_deny_type' => 'This file type is not allowed to upload.',
				'async_upload'    => false,
				'label_max_added' => 'You cannot add more then 1 files',
			],
			'browse_label'   => 'Select file(s) to upload',
		];

		$this->assertEquals( $expected, $field->args );

		// Test 2.
		$field = new \AnsPress\Form\Field\Upload( 'Sample Form', 'sample-form', [
			'upload_options' => [
				'multiple'        => true,
				'max_files'       => 2,
				'allowed_mimes'   => [
					'jpg|jpeg' => 'image/jpeg',
					'gif'      => 'image/gif',
				],
				'label_deny_type' => 'Invalid file',
				'async_upload'    => true,
				'label_max_added' => 'Max 2 files are allowed',
			],
		] );
		$expected = [
			'label'          => 'AnsPress Upload Field',
			'upload_options' => [
				'multiple'        => true,
				'max_files'       => 2,
				'allowed_mimes'   => [
					'jpg|jpeg' => 'image/jpeg',
					'gif'      => 'image/gif',
				],
				'label_deny_type' => 'Invalid file',
				'async_upload'    => true,
				'label_max_added' => 'Max 2 files are allowed',
			],
			'browse_label'   => 'Select file(s) to upload',
		];
		$this->assertEquals( $expected, $field->args );

		// Test 3.
		$field = new \AnsPress\Form\Field\Upload( 'Sample Form', 'sample-form', [
			'label'        => 'Sample Label',
			'desc'         => 'Sample Description',
			'browse_label' => 'Browse files',
		] );
		$expected = [
			'label'          => 'Sample Label',
			'desc'           => 'Sample Description',
			'upload_options' => [
				'multiple'        => false,
				'max_files'       => 1,
				'allowed_mimes'   => [
					'jpeg|jpg' => 'image/jpeg',
					'png'      => 'image/png',
					'gif'      => 'image/gif',
				],
				'label_deny_type' => 'This file type is not allowed to upload.',
				'async_upload'    => false,
				'label_max_added' => 'You cannot add more then 1 files',
			],
			'browse_label'   => 'Browse files',
		];
		$this->assertEquals( $expected, $field->args );

		// Test 4.
		$field = new \AnsPress\Form\Field\Upload( 'Sample Form', 'sample-form', [
			'label'          => 'Sample Label',
			'desc'           => 'Sample Description',
			'upload_options' => [],
		] );
		$expected = [
			'label'          => 'Sample Label',
			'desc'           => 'Sample Description',
			'upload_options' => [
				'multiple'        => false,
				'max_files'       => 1,
				'allowed_mimes'   => [
					'jpeg|jpg' => 'image/jpeg',
					'png'      => 'image/png',
					'gif'      => 'image/gif',
				],
				'label_deny_type' => 'This file type is not allowed to upload.',
				'async_upload'    => false,
				'label_max_added' => 'You cannot add more then 1 files',
			],
			'browse_label'   => 'Select file(s) to upload',
		];
		$this->assertEquals( $expected, $field->args );

		// Test 5.
		$field = new \AnsPress\Form\Field\Upload( 'Sample Form', 'sample-form', [
			'label'          => 'Sample Label',
			'desc'           => 'Sample Description',
			'upload_options' => [
				'allowed_mimes'   => [],
				'label_max_added' => 'No files can be uploaded',
			],
			'browse_label'   => 'Browse for files',
		] );
		$expected = [
			'label'          => 'Sample Label',
			'desc'           => 'Sample Description',
			'upload_options' => [
				'multiple'        => false,
				'max_files'       => 1,
				'allowed_mimes'   => [],
				'label_deny_type' => 'This file type is not allowed to upload.',
				'async_upload'    => false,
				'label_max_added' => 'No files can be uploaded',
			],
			'browse_label'   => 'Browse for files',
		];
		$this->assertEquals( $expected, $field->args );

		// Test 6.
		$field = new \AnsPress\Form\Field\Upload( 'Sample Form', 'sample-form', [
			'upload_options' => [
				'label_max_added' => 'Testing max files',
			],
		] );
		$expected = [
			'label'          => 'AnsPress Upload Field',
			'upload_options' => [
				'multiple'        => false,
				'max_files'       => 1,
				'allowed_mimes'   => [
					'jpeg|jpg' => 'image/jpeg',
					'png'      => 'image/png',
					'gif'      => 'image/gif',
				],
				'label_deny_type' => 'This file type is not allowed to upload.',
				'async_upload'    => false,
				'label_max_added' => 'Testing max files',
			],
			'browse_label'   => 'Select file(s) to upload',
		];
		$this->assertEquals( $expected, $field->args );

		// Test 7.
		$field = new \AnsPress\Form\Field\Upload( 'Sample Form', 'sample-form', [
			'upload_options' => [
				'multiple'        => true,
				'max_files'       => 3,
				'allowed_mimes'   => [
					'jpg|jpeg' => 'image/jpeg',
				],
				'label_max_added' => 'Max 3 files are allowed to get uploaded',
			],
		] );
		$expected = [
			'label'          => 'AnsPress Upload Field',
			'upload_options' => [
				'multiple'        => true,
				'max_files'       => 3,
				'allowed_mimes'   => [
					'jpg|jpeg' => 'image/jpeg',
				],
				'label_deny_type' => 'This file type is not allowed to upload.',
				'async_upload'    => false,
				'label_max_added' => 'Max 3 files are allowed to get uploaded',
			],
			'browse_label'   => 'Select file(s) to upload',
		];
		$this->assertEquals( $expected, $field->args );
	}

	/**
	 * @covers AnsPress\Form\Field\Upload::html_order
	 */
	public function testHTMLOrder() {
		$field = new \AnsPress\Form\Field\Upload( 'Sample Form', 'sample-form', [] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'html_order' );
		$method->setAccessible( true );
		$property = $reflection->getProperty( 'output_order' );
		$property->setAccessible( true );

		// Test begins.
		// Test 1.
		$default_output_order = [ 'wrapper_start', 'label', 'field_wrap_start', 'errors', 'field_markup', 'desc', 'file_list', 'field_wrap_end', 'wrapper_end' ];
		$method->invoke( $field );
		$this->assertIsArray( $property->getValue( $field ) );
		$this->assertEquals( $default_output_order, $property->getValue( $field ) );

		// Test 2.
		$custom_output_order = [ 'wrapper_start', 'label', 'desc', 'errors', 'file_list', 'field_wrap_start', 'field_markup', 'field_wrap_end', 'wrapper_end' ];
		$field->args['output_order'] = $custom_output_order;
		$method->invoke( $field );
		$this->assertIsArray( $property->getValue( $field ) );
		$this->assertEquals( $custom_output_order, $property->getValue( $field ) );
		$this->assertNotEquals( $default_output_order, $property->getValue( $field ) );

		// Test 3.
		$new_custom_output_order = [ 'label', 'desc', 'errors', 'file_list', 'field_markup' ];
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

	/**
	 * @covers AnsPress\Form\Field\Upload::sanitize_cb_args
	 */
	public function testSanitizeCBArgs() {
		// Test 1.
		$field = new \AnsPress\Form\Field\Upload( 'Sample Form', 'sample-form', [
			'upload_options' => [
				'multiple'        => false,
				'max_files'       => 1,
				'allowed_mimes'   => array(
					'jpg|jpeg' => 'image/jpeg',
					'gif'      => 'image/gif',
					'png'      => 'image/png',
				),
				'label_deny_type' => 'Invalid file type',
				'async_upload'    => false,
				'label_max_added' => 'You can only upload one file',
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'sanitize_cb_args' );
		$method->setAccessible( true );
		$result = $method->invoke( $field, 'test_value' );
		$this->assertIsArray( $result );
		$this->assertEquals( [ 'test_value', $field->args['upload_options'] ], $result );

		// Test 2.
		$field = new \AnsPress\Form\Field\Upload( 'Sample Form', 'sample-form', [
			'upload_options' => [
				'multiple'        => true,
				'max_files'       => 2,
				'allowed_mimes'   => array(
					'jpg|jpeg' => 'image/jpeg',
					'gif'      => 'image/gif',
				),
				'label_deny_type' => 'Invalid file',
				'async_upload'    => true,
				'label_max_added' => 'Uploading only 2 files is allowed',
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'sanitize_cb_args' );
		$method->setAccessible( true );
		$result = $method->invoke( $field, 'new_value' );
		$this->assertIsArray( $result );
		$this->assertEquals( [ 'new_value', $field->args['upload_options'] ], $result );

		// Test 3.
		$field = new \AnsPress\Form\Field\Upload( 'Sample Form', 'sample-form', [
			'upload_options' => [
				'multiple'        => false,
				'max_files'       => 5,
				'allowed_mimes'   => array(
					'gif' => 'image/gif',
					'png' => 'image/png',
				),
				'label_deny_type' => 'File type is not allowed',
				'async_upload'    => '',
				'label_max_added' => 'Max 5 files can be uploaded',
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'sanitize_cb_args' );
		$method->setAccessible( true );
		$result = $method->invoke( $field, [ 'array_value' ] );
		$this->assertIsArray( $result );
		$this->assertEquals( [ [ 'array_value' ], $field->args['upload_options'] ], $result );

		// Test 4.
		$field = new \AnsPress\Form\Field\Upload( 'Sample Form', 'sample-form', [
			'upload_options' => [
				'multiple'  => true,
				'max_files' => 3,
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'sanitize_cb_args' );
		$method->setAccessible( true );
		$result = $method->invoke( $field, 'additional_value' );
		$this->assertIsArray( $result );
		$this->assertEquals( [ 'additional_value', $field->args['upload_options'] ], $result );

		// Test 5.
		$field = new \AnsPress\Form\Field\Upload( 'Sample Form', 'sample-form', [ 'upload_options' => [] ] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'sanitize_cb_args' );
		$method->setAccessible( true );
		$result = $method->invoke( $field, 'latest_value' );
		$this->assertIsArray( $result );
		$this->assertEquals( [ 'latest_value', $field->args['upload_options'] ], $result );
	}

	/**
	 * @covers AnsPress\Form\Field\Upload::js_args
	 */
	public function testJSArgs() {
		// Test 1.
		$field = new \AnsPress\Form\Field\Upload( 'Sample Form', 'sample-form', [
			'upload_options' => [
				'multiple'        => false,
				'max_files'       => 1,
				'allowed_mimes'   => array(
					'jpg|jpeg' => 'image/jpeg',
					'gif'      => 'image/gif',
					'png'      => 'image/png',
				),
				'label_deny_type' => 'Invalid file type',
				'async_upload'    => false,
				'label_max_added' => 'No more than 1 file is allowed',
			],
		] );
		$result = $field->js_args();
		$expected = wp_json_encode( [
			'max_files'       => 1,
			'multiple'        => false,
			'label_deny_type' => 'Invalid file type',
			'async_upload'    => false,
			'label_max_added' => 'No more than 1 file is allowed',
			'field_name'      => 'sample-form',
			'form_name'       => 'Sample Form',
		] );
		$this->assertEquals( $expected, $result );

		// Test 2.
		$field = new \AnsPress\Form\Field\Upload( 'Sample Form', 'sample-form', [
			'upload_options' => [
				'multiple'        => true,
				'max_files'       => 2,
				'allowed_mimes'   => array(
					'jpg|jpeg' => 'image/jpeg',
					'gif'      => 'image/gif',
				),
				'label_deny_type' => 'Invalid file',
				'async_upload'    => true,
				'label_max_added' => 'Max 2 files are allowed',
			],
		] );
		$result = $field->js_args();
		$expected = wp_json_encode( [
			'max_files'       => 2,
			'multiple'        => true,
			'label_deny_type' => 'Invalid file',
			'async_upload'    => true,
			'label_max_added' => 'Max 2 files are allowed',
			'field_name'      => 'sample-form',
			'form_name'       => 'Sample Form',
		] );
		$this->assertEquals( $expected, $result );

		// Test 3.
		$field = new \AnsPress\Form\Field\Upload( 'Sample Form', 'sample-form', [
			'upload_options' => [
				'multiple'  => true,
				'max_files' => 5,
			],
		] );
		$result = $field->js_args();
		$expected = wp_json_encode( [
			'max_files'       => 5,
			'multiple'        => true,
			'label_deny_type' => 'This file type is not allowed to upload.',
			'async_upload'    => false,
			'label_max_added' => 'You cannot add more then 5 files',
			'field_name'      => 'sample-form',
			'form_name'       => 'Sample Form',
		] );
		$this->assertEquals( $expected, $result );

		// Test 4.
		$field = new \AnsPress\Form\Field\Upload( 'Test Form', 'test-form', [
			'upload_options' => [],
		] );
		$result = $field->js_args();
		$expected = wp_json_encode( [
			'max_files'       => 1,
			'multiple'        => false,
			'label_deny_type' => 'This file type is not allowed to upload.',
			'async_upload'    => false,
			'label_max_added' => 'You cannot add more then 1 files',
			'field_name'      => 'test-form',
			'form_name'       => 'Test Form',
		] );
		$this->assertEquals( $expected, $result );
	}

	/**
	 * @covers AnsPress\Form\Field\Upload::file_list
	 */
	public function testFileList() {
		// Test 1.
		$field = new \AnsPress\Form\Field\Upload( 'Sample Form', 'sample-form', [] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->file_list();
		$this->assertEquals( '<div class="ap-upload-list"></div>', $property->getValue( $field ) );

		// Test 2.
		$question_id = $this->insert_question();
		$attachment_id = $this->factory()->attachment->create_object( dirname( __DIR__ ) . '/assets/files/anspress.pdf', $question_id, [
			'post_title' => '_ap_temp_media',
		] );
		$field = new \AnsPress\Form\Field\Upload( 'Sample Form', 'sample-form', [] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->file_list();
		$media = get_post( $attachment_id );
		$this->assertEquals( '<div class="ap-upload-list"><div><span class="ext">' . pathinfo( $media->guid, PATHINFO_EXTENSION ) . '</span>' . basename( $media->guid ) . '<span class="size">' . size_format( filesize( get_attached_file( $media->ID ) ), 2 ) . '</span></div></div>', $property->getValue( $field ) );
		wp_delete_attachment( $attachment_id );

		// Test 3.
		$id = $this->insert_answer();
		$attachment_id_1 = $this->factory()->attachment->create_object( dirname( __DIR__ ) . '/assets/img/question.png', $id->q, [
			'post_title' => '_ap_temp_media',
		] );
		$attachment_id_2 = $this->factory()->attachment->create_object( dirname( __DIR__ ) . '/assets/img/answer.png', $id->a, [
			'post_title' => '_ap_temp_media',
		] );
		$attachment_id_3 = $this->factory()->attachment->create_object( dirname( __DIR__ ) . '/assets/img/anspress-hero.png', $id->a, [
			'post_title' => '_ap_temp_media',
		] );
		$field = new \AnsPress\Form\Field\Upload( 'Test Form', 'test-form', [] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->file_list();
		$media_1 = get_post( $attachment_id_1 );
		$media_2 = get_post( $attachment_id_2 );
		$media_3 = get_post( $attachment_id_3 );
		$this->assertStringContainsString( '<div><span class="ext">' . pathinfo( $media_1->guid, PATHINFO_EXTENSION ) . '</span>' . basename( $media_1->guid ) . '<span class="size">' . size_format( filesize( get_attached_file( $media_1->ID ) ), 2 ) . '</span></div>', $property->getValue( $field ) );
		$this->assertStringContainsString( '<div><span class="ext">' . pathinfo( $media_2->guid, PATHINFO_EXTENSION ) . '</span>' . basename( $media_2->guid ) . '<span class="size">' . size_format( filesize( get_attached_file( $media_2->ID ) ), 2 ) . '</span></div>', $property->getValue( $field ) );
		$this->assertStringContainsString( '<div><span class="ext">' . pathinfo( $media_3->guid, PATHINFO_EXTENSION ) . '</span>' . basename( $media_3->guid ) . '<span class="size">' . size_format( filesize( get_attached_file( $media_3->ID ) ), 2 ) . '</span></div>', $property->getValue( $field ) );
	}

	/**
	 * @covers AnsPress\Form\Field\Upload::field_markup
	 */
	public function testFieldMarkup() {
		// Set up the action hook callback
		$callback_triggered = false;
		add_action( 'ap_after_field_markup', function( $field ) use ( &$callback_triggered ) {
			$this->assertInstanceOf( 'AnsPress\Form\Field\Upload', $field );
			$this->assertInstanceOf( 'AnsPress\Form\Field', $field );
			$callback_triggered = true;
		} );

		// Test begins.
		// Test 1.
		$callback_triggered = false;
		$field = new \AnsPress\Form\Field\Upload( 'Sample Form', 'sample-form', [
			'upload_options' => [
				'multiple'        => false,
				'max_files'       => 1,
				'allowed_mimes'   => array(
					'jpg|jpeg' => 'image/jpeg',
					'gif'      => 'image/gif',
					'png'      => 'image/png',
				),
				'label_deny_type' => 'Invalid file type',
				'async_upload'    => false,
				'label_max_added' => 'No more than 1 file is allowed',
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertFalse( $callback_triggered );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertEquals( '<div class="ap-upload-c"><input type="file"data-upload="' . esc_js( $field->js_args() ) . '" name="SampleForm-sample-form" id="SampleForm-sample-form" class="ap-form-control " accept=".jpg,.jpeg,.gif,.png"  /></div>', $property->getValue( $field ) );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );

		// Test 2.
		$callback_triggered = false;
		$field = new \AnsPress\Form\Field\Upload( 'Test Form', 'test-form', [
			'upload_options' => [
				'multiple'        => true,
				'max_files'       => 2,
				'allowed_mimes'   => array(
					'jpg|jpeg' => 'image/jpeg',
					'gif'      => 'image/gif',
				),
				'label_deny_type' => 'Invalid file',
				'async_upload'    => true,
				'label_max_added' => 'Max 2 files are allowed',
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertFalse( $callback_triggered );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertEquals( '<div class="ap-upload-c"><input type="file"data-upload="' . esc_js( $field->js_args() ) . '" name="TestForm-test-form[]" id="TestForm-test-form" class="ap-form-control " multiple="multiple" accept=".jpg,.jpeg,.gif"  /></div>', $property->getValue( $field ) );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );

		// Test 3.
		$callback_triggered = false;
		$field = new \AnsPress\Form\Field\Upload( 'Sample Form', 'sample-form', [
			'wrapper'        => [
				'class' => 'custom-class',
				'attr'  => [
					'data-custom' => 'custom-data',
					'placeholder' => 'Custom Placeholder',
				],
			],
			'upload_options' => [
				'multiple'  => true,
				'max_files' => 5,
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertFalse( $callback_triggered );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertEquals( '<div class="ap-upload-c"><input type="file"data-upload="' . esc_js( $field->js_args() ) . '" name="SampleForm-sample-form[]" id="SampleForm-sample-form" class="ap-form-control " multiple="multiple" accept=".jpeg,.jpg,.png,.gif"  /></div>', $property->getValue( $field ) );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );

		// Test 4.
		$callback_triggered = false;
		$field = new \AnsPress\Form\Field\Upload( 'Test Form', 'test-form', [
			'class' => 'custom-class',
			'attr'  => [
				'data-custom' => 'custom-data',
				'placeholder' => 'Custom Placeholder',
			],
			'upload_options' => [],
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertFalse( $callback_triggered );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertEquals( '<div class="ap-upload-c"><input type="file"data-upload="' . esc_js( $field->js_args() ) . '" name="TestForm-test-form" id="TestForm-test-form" class="ap-form-control custom-class" data-custom="custom-data" placeholder="Custom Placeholder" accept=".jpeg,.jpg,.png,.gif"  /></div>', $property->getValue( $field ) );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );

		// Test 5.
		$callback_triggered = false;
		$field = new \AnsPress\Form\Field\Upload( 'Test Form', 'test-form', [
			'upload_options' => [
				'allowed_mimes' => [],
				'multiple'      => true,
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertFalse( $callback_triggered );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertEquals( '<div class="ap-upload-c"><input type="file"data-upload="' . esc_js( $field->js_args() ) . '" name="TestForm-test-form[]" id="TestForm-test-form" class="ap-form-control " multiple="multiple" accept="."  /></div>', $property->getValue( $field ) );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );
	}

	/**
	 * @covers AnsPress\Form\Field\Upload::__construct
	 */
	public function testConstruct() {
		// Test 1.
		$field = new \AnsPress\Form\Field\Upload( 'Sample Form', 'sample-form', [] );
		$reflection = new \ReflectionClass( $field );
		$sanitize_cb = $reflection->getProperty( 'sanitize_cb' );
		$sanitize_cb->setAccessible( true );
		$validate_cb = $reflection->getProperty( 'validate_cb' );
		$validate_cb->setAccessible( true );
		$this->assertEquals( 'SampleForm-sample-form', $field->field_name );
		$this->assertEquals( false, $field->multiple_upload );
		$this->assertEquals( [ 'upload' ], $sanitize_cb->getValue( $field ) );
		$this->assertEquals( [ 'upload' ], $validate_cb->getValue( $field ) );

		// Test 2.
		$field = new \AnsPress\Form\Field\Upload( 'Test Form', 'test-form', [
			'label'          => 'Test Label',
			'upload_options' => [
				'multiple' => true,
			],
			'sanitize'       => 'custom_sanitize_cb',
			'validate'       => 'custom_validate_cb',
		] );
		$reflection = new \ReflectionClass( $field );
		$sanitize_cb = $reflection->getProperty( 'sanitize_cb' );
		$sanitize_cb->setAccessible( true );
		$validate_cb = $reflection->getProperty( 'validate_cb' );
		$validate_cb->setAccessible( true );
		$this->assertNotEquals( 'TestForm-test-form', $field->field_name );
		$this->assertEquals( 'TestForm-test-form[]', $field->field_name );
		$this->assertEquals( true, $field->multiple_upload );
		$this->assertEquals( [ 'upload', 'custom_sanitize_cb' ], $sanitize_cb->getValue( $field ) );
		$this->assertEquals( [ 'upload', 'custom_validate_cb' ], $validate_cb->getValue( $field ) );
	}
}
