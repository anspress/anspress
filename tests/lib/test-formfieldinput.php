<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnsPressFormFieldInput extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress\Form\Field\Input' );
		$this->assertTrue( $class->hasProperty( 'type' ) && $class->getProperty( 'type' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'subtype' ) && $class->getProperty( 'subtype' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Input', 'prepare' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Input', 'set_subtype' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Input', 'html_order' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Input', 'field_markup' ) );
	}
}
