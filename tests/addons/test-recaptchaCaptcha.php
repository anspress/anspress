<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAddonCaptchaCaptcha extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress\Form\Field\Captcha' );
		$this->assertTrue( $class->hasProperty( 'type' ) && $class->getProperty( 'type' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'response' ) && $class->getProperty( 'response' )->isPrivate() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Captcha', 'prepare' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Captcha', 'sanitize' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Captcha', 'field_markup' ) );
	}
}
