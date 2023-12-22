<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnsPressFormFieldRepeatable extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress\Form\Field\Repeatable' );
		$this->assertTrue( $class->hasProperty( 'type' ) && $class->getProperty( 'type' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'total_items' ) && $class->getProperty( 'total_items' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'main_fields' ) && $class->getProperty( 'main_fields' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Repeatable', 'prepare' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Repeatable', 'unsafe_value' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Repeatable', 'get_last_field' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Repeatable', 'html_order' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Repeatable', 'get_groups_count' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Repeatable', 'field_markup' ) );
	}
}
