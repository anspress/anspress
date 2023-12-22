<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnsPressFormFieldSelect extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress\Form\Field\Select' );
		$this->assertTrue( $class->hasProperty( 'type' ) && $class->getProperty( 'type' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Select', 'prepare' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Select', 'get_options' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Select', 'field_markup' ) );
	}
}
