<?php

namespace AnsPress\Tests\WP;

use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * @covers \AnsPress_Upgrader
 * @package AnsPress\Tests\WP
 */
class TestAnsPressUpgrader extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress_Upgrader' );
		$this->assertTrue( $class->hasProperty( 'question_ids' ) && $class->getProperty( 'question_ids' )->isPrivate() );
		$this->assertTrue( $class->hasProperty( 'answer_ids' ) && $class->getProperty( 'answer_ids' )->isPrivate() );
		$this->assertTrue( $class->hasProperty( 'meta_table_exists' ) && $class->getProperty( 'meta_table_exists' )->isPrivate() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Upgrader', 'get_instance' ) );
		$this->assertTrue( method_exists( 'AnsPress_Upgrader', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress_Upgrader', 'check_tables' ) );
	}
}
