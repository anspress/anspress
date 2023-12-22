<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnsPressFormField extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress\Form\Field' );
		$this->assertTrue( $class->hasProperty( 'field_name' ) && $class->getProperty( 'field_name' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'original_name' ) && $class->getProperty( 'original_name' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'form_name' ) && $class->getProperty( 'form_name' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'field_id' ) && $class->getProperty( 'field_id' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'args' ) && $class->getProperty( 'args' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'html' ) && $class->getProperty( 'html' )->isProtected() );
		$this->assertTrue( $class->hasProperty( 'output_order' ) && $class->getProperty( 'output_order' )->isProtected() );
		$this->assertTrue( $class->hasProperty( 'errors' ) && $class->getProperty( 'errors' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'type' ) && $class->getProperty( 'type' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'child' ) && $class->getProperty( 'child' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'editing' ) && $class->getProperty( 'editing' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'editing_id' ) && $class->getProperty( 'editing_id' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'validated' ) && $class->getProperty( 'validated' )->isProtected() );
		$this->assertTrue( $class->hasProperty( 'validate_cb' ) && $class->getProperty( 'validate_cb' )->isProtected() );
		$this->assertTrue( $class->hasProperty( 'sanitize_cb' ) && $class->getProperty( 'sanitize_cb' )->isProtected() );
		$this->assertTrue( $class->hasProperty( 'sanitized' ) && $class->getProperty( 'sanitized' )->isProtected() );
		$this->assertTrue( $class->hasProperty( 'sanitized_value' ) && $class->getProperty( 'sanitized_value' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'value' ) && $class->getProperty( 'value' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'prepare' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'form' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'sanitize_cb' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'validate_cb' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'get' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'add_html' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'html_order' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'output' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'unsafe_value' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'isset_value' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'value' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'field_wrap_start' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'field_wrap_end' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'label' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'have_errors' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'add_error' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'errors' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'id' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'field_markup' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'desc' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'wrapper_start' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'wrapper_end' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'get_attr' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'common_attr' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'custom_attr' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'sanitize_cb_args' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'sanitize' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'validate' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'pre_get' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'after_save' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field', 'save_cb' ) );
	}
}
