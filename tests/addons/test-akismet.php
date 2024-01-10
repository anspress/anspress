<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAkismet extends TestCase {

	public function set_up() {
		parent::set_up();
		ap_activate_addon( 'akismet.php' );
	}

	public function tear_down() {
		parent::tear_down();
		ap_deactivate_addon( 'akismet.php' );
	}

	/**
	 * @covers Anspress\Addons\Akismet::instance
	 */
	public function testInstance() {
		$class = new \ReflectionClass( 'Anspress\Addons\Akismet' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'Anspress\Addons\Akismet', '__construct' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Akismet', 'add_to_settings_page' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Akismet', 'option_form' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Akismet', 'api_request' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Akismet', 'spam_post_action' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Akismet', 'new_question_answer' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Akismet', 'submit_spam' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Akismet', 'row_actions' ) );
	}
}
