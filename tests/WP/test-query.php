<?php

namespace AnsPress\Tests\WP;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnsPress_Query extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress_Query' );
		$this->assertTrue( $class->hasProperty( 'current' ) && $class->getProperty( 'current' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'count' ) && $class->getProperty( 'count' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'objects' ) && $class->getProperty( 'objects' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'object' ) && $class->getProperty( 'object' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'in_the_loop' ) && $class->getProperty( 'in_the_loop' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'total_count' ) && $class->getProperty( 'total_count' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'per_page' ) && $class->getProperty( 'per_page' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'total_pages' ) && $class->getProperty( 'total_pages' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'paged' ) && $class->getProperty( 'paged' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'offset' ) && $class->getProperty( 'offset' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'args' ) && $class->getProperty( 'args' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'ids' ) && $class->getProperty( 'ids' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'pos' ) && $class->getProperty( 'pos' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Query', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress_Query', 'total_count' ) );
		$this->assertTrue( method_exists( 'AnsPress_Query', 'query' ) );
		$this->assertTrue( method_exists( 'AnsPress_Query', 'have' ) );
		$this->assertTrue( method_exists( 'AnsPress_Query', 'rewind' ) );
		$this->assertTrue( method_exists( 'AnsPress_Query', 'has' ) );
		$this->assertTrue( method_exists( 'AnsPress_Query', 'next' ) );
		$this->assertTrue( method_exists( 'AnsPress_Query', 'the_object' ) );
		$this->assertTrue( method_exists( 'AnsPress_Query', 'add_prefetch_id' ) );
		$this->assertTrue( method_exists( 'AnsPress_Query', 'add_pos' ) );
		$this->assertTrue( method_exists( 'AnsPress_Query', 'append_ref_data' ) );
		$this->assertTrue( method_exists( 'AnsPress_Query', 'template' ) );
		$this->assertTrue( method_exists( 'AnsPress_Query', 'have_pages' ) );
	}
}
