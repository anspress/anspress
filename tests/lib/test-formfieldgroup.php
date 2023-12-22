<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnsPressFormFieldGroup extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress\Form\Field\Group' );
		$this->assertTrue( $class->hasProperty( 'type' ) && $class->getProperty( 'type' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'child' ) && $class->getProperty( 'child' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Group', 'prepare' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Group', 'html_order' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Group', 'label' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Group', 'field_markup' ) );
	}
}
