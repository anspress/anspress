<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestRewrite extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress_Rewrite' );
		$this->assertTrue( $class->hasProperty( 'counter' ) && $class->getProperty( 'counter' )->isPrivate() );
		$this->assertTrue( $class->hasProperty( 'counter' ) && $class->getProperty( 'counter' )->isStatic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Rewrite', 'alter_the_query' ) );
		$this->assertTrue( method_exists( 'AnsPress_Rewrite', 'query_var' ) );
		$this->assertTrue( method_exists( 'AnsPress_Rewrite', 'rewrite_rules' ) );
		$this->assertTrue( method_exists( 'AnsPress_Rewrite', 'rewrites' ) );
		$this->assertTrue( method_exists( 'AnsPress_Rewrite', 'incr_hash' ) );
		$this->assertTrue( method_exists( 'AnsPress_Rewrite', 'bp_com_paged' ) );
		$this->assertTrue( method_exists( 'AnsPress_Rewrite', 'add_query_var' ) );
		$this->assertTrue( method_exists( 'AnsPress_Rewrite', 'shortlink' ) );
	}
}
