<?php

namespace AnsPress\Tests\WP;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestActivityClass extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress\Activity' );
		$this->assertTrue( $class->hasProperty( 'verbs' ) && $class->getProperty( 'verbs' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'per_page' ) && $class->getProperty( 'per_page' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'in_group' ) && $class->getProperty( 'in_group' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Activity', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'query' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'prefetch' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'prefetch_posts' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'prefetch_actors' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'prefetch_comments' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'have_group' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'group_start' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'group_end' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'have_group_items' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'count_group' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'has_action' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'get_the_verb' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'the_verb' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'get_avatar' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'get_user_id' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'get_the_date' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'get_the_icon' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'the_icon' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'the_ref_content' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'when' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'the_when' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'get_q_id' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'more_button' ) );
	}
}
