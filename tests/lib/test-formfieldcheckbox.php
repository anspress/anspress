<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnsPressFormFieldCheckbox extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress\Form\Field\Checkbox' );
		$this->assertTrue( $class->hasProperty( 'type' ) && $class->getProperty( 'type' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Checkbox', 'prepare' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Checkbox', 'html_order' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Checkbox', 'field_markup' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Checkbox', 'unsafe_value' ) );
	}
}
