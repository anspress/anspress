<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestUpdater extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress_Prod_Updater' );
		$this->assertTrue( $class->hasProperty( 'api_url' ) && $class->getProperty( 'api_url' )->isPrivate() );
		$this->assertTrue( $class->hasProperty( 'api_data' ) && $class->getProperty( 'api_data' )->isPrivate() );
		$this->assertTrue( $class->hasProperty( 'name' ) && $class->getProperty( 'name' )->isPrivate() );
		$this->assertTrue( $class->hasProperty( 'slug' ) && $class->getProperty( 'slug' )->isPrivate() );
		$this->assertTrue( $class->hasProperty( 'version' ) && $class->getProperty( 'version' )->isPrivate() );
		$this->assertTrue( $class->hasProperty( 'response_key' ) && $class->getProperty( 'response_key' )->isPrivate() );
		$this->assertTrue( $class->hasProperty( 'license' ) && $class->getProperty( 'license' )->isPrivate() );
		$this->assertTrue( $class->hasProperty( 'strings' ) && $class->getProperty( 'strings' )->isPrivate() );
		$this->assertTrue( $class->hasProperty( 'update_checked' ) && $class->getProperty( 'update_checked' )->isPrivate() );
		$this->assertTrue( $class->hasProperty( 'is_plugin' ) && $class->getProperty( 'is_plugin' )->isPrivate() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Prod_Updater', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress_Prod_Updater', 'init' ) );
		$this->assertTrue( method_exists( 'AnsPress_Prod_Updater', 'check_update' ) );
		$this->assertTrue( method_exists( 'AnsPress_Prod_Updater', 'show_update_notification' ) );
		$this->assertTrue( method_exists( 'AnsPress_Prod_Updater', 'plugins_api_filter' ) );
		$this->assertTrue( method_exists( 'AnsPress_Prod_Updater', 'http_request_args' ) );
		$this->assertTrue( method_exists( 'AnsPress_Prod_Updater', 'api_request' ) );
		$this->assertTrue( method_exists( 'AnsPress_Prod_Updater', 'show_changelog' ) );
		$this->assertTrue( method_exists( 'AnsPress_Prod_Updater', 'theme_update_transient' ) );
		$this->assertTrue( method_exists( 'AnsPress_Prod_Updater', 'delete_theme_update_transient' ) );
		$this->assertTrue( method_exists( 'AnsPress_Prod_Updater', 'load_themes_screen' ) );
		$this->assertTrue( method_exists( 'AnsPress_Prod_Updater', 'update_nag' ) );
		$this->assertTrue( method_exists( 'AnsPress_Prod_Updater', 'check_theme_update' ) );
	}
}
