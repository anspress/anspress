<?php

namespace AnsPress\Tests\WP;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestReputationQuery extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress_Reputation_Query' );
		$this->assertTrue( $class->hasProperty( 'current' ) && $class->getProperty( 'current' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'count' ) && $class->getProperty( 'count' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'reputations' ) && $class->getProperty( 'reputations' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'reputation' ) && $class->getProperty( 'reputation' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'in_the_loop' ) && $class->getProperty( 'in_the_loop' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'total_count' ) && $class->getProperty( 'total_count' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'per_page' ) && $class->getProperty( 'per_page' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'total_pages' ) && $class->getProperty( 'total_pages' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'max_num_pages' ) && $class->getProperty( 'max_num_pages' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'paged' ) && $class->getProperty( 'paged' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'offset' ) && $class->getProperty( 'offset' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'with_zero_points' ) && $class->getProperty( 'with_zero_points' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'events' ) && $class->getProperty( 'events' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'ids' ) && $class->getProperty( 'ids' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'pos' ) && $class->getProperty( 'pos' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'args' ) && $class->getProperty( 'args' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'get_events_with_zero_points' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'query' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'prefetch' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'prefetch_posts' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'prefetch_comments' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'have' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'rewind_reputation' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'has' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'next_reputation' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'the_reputation' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'get_event' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'the_event' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'get_points' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'the_points' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'get_date' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'the_date' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'get_icon' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'the_icon' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'get_activity' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'the_activity' ) );
		$this->assertTrue( method_exists( 'AnsPress_Reputation_Query', 'the_ref_content' ) );
	}
}
