<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnsPressFormFieldTags extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress\Form\Field\Tags' );
		$this->assertTrue( $class->hasProperty( 'type' ) && $class->getProperty( 'type' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Tags', 'prepare' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Tags', 'sanitize_cb_args' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Tags', 'get_options' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Tags', 'field_markup' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Tags', 'unsafe_value' ) );
	}
}
