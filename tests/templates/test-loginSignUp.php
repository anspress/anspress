<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestTemplatesLoginSignUp extends TestCase {

	use Testcases\Common;

	public function testLoginSignUpWithUserLogin() {
		$this->setRole( 'subscriber' );
		add_action( 'wordpress_social_login', function() {} );
		$this->assertFalse( did_action( 'wordpress_social_login' ) > 0 );

		ob_start();
		ap_get_template_part( 'login-signup' );
		$result = ob_get_clean();
		$this->assertStringNotContainsString( '<div class="ap-login">', $result );
		$this->assertFalse( did_action( 'wordpress_social_login' ) > 0 );
	}

	public function testLoginSignUpNoUserLogin() {
		add_action( 'wordpress_social_login', function() {} );
		$this->assertFalse( did_action( 'wordpress_social_login' ) > 0 );

		// Test 1.
		ob_start();
		ap_get_template_part( 'login-signup' );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<div class="ap-login">', $result );
		$this->assertStringContainsString( '<div class="ap-login-buttons">', $result );
		$this->assertStringContainsString( '<a href="' . esc_url( wp_login_url( get_the_permalink() ) ) . '">Login</a>', $result );
		$this->assertStringNotContainsString( '<a href="' . esc_url( wp_registration_url() ) . '">Register</a>', $result );
		$this->assertTrue( did_action( 'wordpress_social_login' ) > 0 );

		// Test 2.
		update_option( 'users_can_register', true );
		ob_start();
		ap_get_template_part( 'login-signup' );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<div class="ap-login">', $result );
		$this->assertStringContainsString( '<div class="ap-login-buttons">', $result );
		$this->assertStringContainsString( '<a href="' . esc_url( wp_login_url( get_the_permalink() ) ) . '">Login</a>', $result );
		$this->assertTrue( did_action( 'wordpress_social_login' ) > 0 );
		if ( ! \is_multisite() ) {
			$this->assertStringContainsString( '<a href="' . esc_url( wp_registration_url() ) . '">Register</a>', $result );
		}

		// Reset option.
		update_option( 'users_can_register', false );
	}
}
