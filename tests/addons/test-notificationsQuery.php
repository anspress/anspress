<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestNotificationsQuery extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'Anspress\Notifications' );
		$this->assertTrue( $class->hasProperty( 'verbs' ) && $class->getProperty( 'verbs' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'Anspress\Notifications', '__construct' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'query' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'prefetch' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'prefetch_posts' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'prefetch_comments' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'prefetch_actors' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'prefetch_reputations' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'verb_args' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'item_template' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'the_notification' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'get_ref_id' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'get_ref_type' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'get_permalink' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'the_permalink' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'get_actor' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'the_actor' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'actor_avatar' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'the_actor_avatar' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'get_verb' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'the_verb' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'get_date' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'the_date' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'get_ref_title' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'the_ref_title' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'get_reputation_points' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'the_reputation_points' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'hide_actor' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'get_icon' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'the_icon' ) );
	}
}
