<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnsPressFormFieldUpload extends TestCase {

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
}
