<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestProcessForm extends TestCase {

	public function testClassPropertiesAvailable() {
		$class = new \ReflectionClass( 'AnsPress_Process_Form' );
		$this->assertTrue( $class->hasProperty( 'result' ) && $class->getProperty( 'result' )->isPrivate() );
		$this->assertTrue( $class->hasProperty( 'redirect' ) && $class->getProperty( 'redirect' )->isPrivate() );
		$this->assertTrue( $class->hasProperty( 'request' ) && $class->getProperty( 'request' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Process_Form', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress_Process_Form', 'non_ajax_form' ) );
		$this->assertTrue( method_exists( 'AnsPress_Process_Form', 'ap_ajax' ) );
		$this->assertTrue( method_exists( 'AnsPress_Process_Form', 'process_form' ) );
	}
}
