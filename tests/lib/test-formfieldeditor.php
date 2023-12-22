<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnsPressFormFieldEditor extends TestCase {

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
}
