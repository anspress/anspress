<?php

namespace AnsPress\Tests\WP;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestProcessForm extends TestCase {

	public function testClassProperties() {
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

	/**
	 * @covers AnsPress_Process_Form::process_form
	 */
	public function testProcessForm() {
		// Test 1.
		$_REQUEST['ap_form_action'] = 'some_action';
		$hook_triggered = false;
		add_action( 'ap_process_form_some_action', function() use ( &$hook_triggered ) {
			$hook_triggered = true;
		} );
		$process_form = new \AnsPress_Process_Form();
		$process_form->process_form();
		$this->assertTrue( $hook_triggered );

		// Test 2.
		$_REQUEST['ap_form_action'] = 'another_action';
		$hook_triggered = false;
		add_action( 'ap_process_form_another_action', function() use ( &$hook_triggered ) {
			$hook_triggered = true;
		} );
		$process_form = new \AnsPress_Process_Form();
		$process_form->process_form();
		$this->assertTrue( $hook_triggered );
	}

	/**
	 * @covers AnsPress_Process_Form::__construct
	 */
	public function testConstruct() {
		$process_form = new \AnsPress_Process_Form();
		$this->assertInstanceOf( 'AnsPress_Process_Form', $process_form );
		$this->assertEquals( 10, has_action( 'wp_ajax_ap_ajax', [ $process_form, 'ap_ajax' ] ) );
		$this->assertEquals( 10, has_action( 'wp_ajax_nopriv_ap_ajax', [ $process_form, 'ap_ajax' ] ) );
	}
}
