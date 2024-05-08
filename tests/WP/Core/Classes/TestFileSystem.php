<?php

namespace AnsPress\Tests\WP\Core\Classes;

use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * @covers \AnsPress\Core\Classes\FileSystem
 * @package AnsPress\Tests\WP
 */
class TestFileSystem extends TestCase {
	public function testGetRootDir() {
		$this->assertEquals( ANSPRESS_PLUGIN_DIR . '/', \AnsPress\Core\Classes\FileSystem::getRootDir() );
	}

	public function testGetPathTo() {
		$this->assertEquals( ANSPRESS_PLUGIN_DIR . '/tests', \AnsPress\Core\Classes\FileSystem::getPathTo( 'tests' ) );

		$this->assertEquals( ANSPRESS_PLUGIN_DIR . '/tests/', \AnsPress\Core\Classes\FileSystem::getPathTo( 'tests/' ) );

		$this->assertEquals( ANSPRESS_PLUGIN_DIR . '/tests/', \AnsPress\Core\Classes\FileSystem::getPathTo( '///tests/' ) );

		$this->assertEquals( ANSPRESS_PLUGIN_DIR . '/tests/', \AnsPress\Core\Classes\FileSystem::getPathTo( '\/tests/' ) );

		$this->assertEquals( ANSPRESS_PLUGIN_DIR . '/tests/', \AnsPress\Core\Classes\FileSystem::getPathTo( '\/tests///' ) );
	}

	public function testGetUrlTo() {
		$this->assertEquals( plugins_url('tests', \AnsPress\Core\Classes\FileSystem::getRootDir()), \AnsPress\Core\Classes\FileSystem::getUrlTo( 'tests' ) );

		$this->assertEquals( plugins_url('tests/', \AnsPress\Core\Classes\FileSystem::getRootDir()), \AnsPress\Core\Classes\FileSystem::getUrlTo( 'tests/' ) );

		$this->assertEquals( plugins_url('tests/', \AnsPress\Core\Classes\FileSystem::getRootDir()), \AnsPress\Core\Classes\FileSystem::getUrlTo( '///tests/' ) );

		$this->assertEquals( plugins_url('tests/', \AnsPress\Core\Classes\FileSystem::getRootDir()), \AnsPress\Core\Classes\FileSystem::getUrlTo( '\/tests/' ) );

		$this->assertEquals( plugins_url('tests/', \AnsPress\Core\Classes\FileSystem::getRootDir()), \AnsPress\Core\Classes\FileSystem::getUrlTo( '\/tests///' ) );

		// Test default value.
		$this->assertEquals( plugins_url('', \AnsPress\Core\Classes\FileSystem::getRootDir()), \AnsPress\Core\Classes\FileSystem::getUrlTo() );
	}
}
