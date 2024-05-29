<?php

namespace Tests\Unit\src\backend\Classes;

use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * @covers AnsPress\Modules\Subscriber\SubscriberPolicy
 * @package Tests\WP
 */
class TestSubscriberPolicy extends TestCase {

	public function testBeforePass() {
		$policy = new \AnsPress\Modules\Subscriber\SubscriberPolicy();

		$user = $this->factory()->user->create_and_get();

		$user->add_cap( 'manage_options' );

		$this->assertTrue( $policy->before( 'view', $user ) );
	}

	public function testBeforeFail() {
		$policy = new \AnsPress\Modules\Subscriber\SubscriberPolicy();

		$user = $this->factory()->user->create_and_get();

		$this->assertNull( $policy->before( 'view', $user ) );
	}

	public function testViewPass() {
		$policy = new \AnsPress\Modules\Subscriber\SubscriberPolicy();

		$user = $this->factory()->user->create_and_get();

		$model = new \AnsPress\Modules\Subscriber\SubscriberModel();

		$model->subs_user_id = $user->ID;

		$this->assertTrue( $policy->view( $user, $model ) );
	}

	public function testViewFail() {
		$policy = new \AnsPress\Modules\Subscriber\SubscriberPolicy();

		$user = $this->factory()->user->create_and_get();

		$model = new \AnsPress\Modules\Subscriber\SubscriberModel();

		$model->subs_user_id = $user->ID + 1;

		$this->assertFalse( $policy->view( $user, $model ) );
	}

	public function testCreatePass() {
		$policy = new \AnsPress\Modules\Subscriber\SubscriberPolicy();

		$user = $this->factory()->user->create_and_get();

		$this->assertTrue( $policy->create( $user ) );
	}

	public function testUpdatePass() {
		$policy = new \AnsPress\Modules\Subscriber\SubscriberPolicy();

		$user = $this->factory()->user->create_and_get();

		$model = new \AnsPress\Modules\Subscriber\SubscriberModel();

		$model->subs_user_id = $user->ID;

		$this->assertTrue( $policy->update( $user, $model ) );
	}

	public function testUpdateFail() {
		$policy = new \AnsPress\Modules\Subscriber\SubscriberPolicy();

		$user = $this->factory()->user->create_and_get();

		$model = new \AnsPress\Modules\Subscriber\SubscriberModel();

		$model->subs_user_id = $user->ID + 1;

		$this->assertFalse( $policy->update( $user, $model ) );
	}

	public function testDeletePass() {
		$policy = new \AnsPress\Modules\Subscriber\SubscriberPolicy();

		$user = $this->factory()->user->create_and_get();

		$model = new \AnsPress\Modules\Subscriber\SubscriberModel();

		$model->subs_user_id = $user->ID;

		$this->assertTrue( $policy->delete( $user, $model ) );
	}

	public function testDeleteFail() {
		$policy = new \AnsPress\Modules\Subscriber\SubscriberPolicy();

		$user = $this->factory()->user->create_and_get();

		$model = new \AnsPress\Modules\Subscriber\SubscriberModel();

		$model->subs_user_id = $user->ID + 1;

		$this->assertFalse( $policy->delete( $user, $model ) );
	}

}
