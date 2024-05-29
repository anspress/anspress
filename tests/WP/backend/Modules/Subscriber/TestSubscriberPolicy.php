<?php

namespace Tests\Unit\src\backend\Classes;

use AnsPress\Classes\Auth;
use AnsPress\Modules\Subscriber\SubscriberModel;
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

		$this->assertTrue( Auth::check( 'view', $model, $user ) );
	}

	public function testViewFail() {
		$policy = new \AnsPress\Modules\Subscriber\SubscriberPolicy();

		$user = $this->factory()->user->create_and_get();

		$model = new \AnsPress\Modules\Subscriber\SubscriberModel();

		$model->subs_user_id = $user->ID + 1;

		$this->assertFalse( $policy->view( $user, $model ) );

		$this->assertFalse( Auth::check( 'view', $model, $user ) );
	}

	public function testCreatePass() {
		$policy = new \AnsPress\Modules\Subscriber\SubscriberPolicy();

		$user = $this->factory()->user->create_and_get();

		$this->assertTrue( $policy->create( $user ) );

		$this->assertTrue( Auth::check( 'create', new SubscriberModel(), $user ) );
	}

	public function testCreateFail() {
		$policy = new \AnsPress\Modules\Subscriber\SubscriberPolicy();

		$this->assertFalse( $policy->create( null ) );

		$this->assertFalse( Auth::check( 'create', new SubscriberModel(), null ) );
	}

	public function testUpdatePass() {
		$policy = new \AnsPress\Modules\Subscriber\SubscriberPolicy();

		$user = $this->factory()->user->create_and_get();

		$model = new \AnsPress\Modules\Subscriber\SubscriberModel();

		$model->subs_user_id = $user->ID;

		$this->assertTrue( $policy->update( $user, $model ) );

		$this->assertTrue( Auth::check( 'update', $model, $user ) );
	}

	public function testUpdateFail() {
		$policy = new \AnsPress\Modules\Subscriber\SubscriberPolicy();

		$user = $this->factory()->user->create_and_get();

		$model = new \AnsPress\Modules\Subscriber\SubscriberModel();

		$model->subs_user_id = $user->ID + 1;

		$this->assertFalse( $policy->update( $user, $model ) );

		$this->assertFalse( Auth::check( 'update', $model, $user ) );
	}

	public function testDeletePass() {
		$policy = new \AnsPress\Modules\Subscriber\SubscriberPolicy();

		$user = $this->factory()->user->create_and_get();

		$model = new \AnsPress\Modules\Subscriber\SubscriberModel();

		$model->subs_user_id = $user->ID;

		$this->assertTrue( $policy->delete( $user, $model ) );

		$this->assertTrue( Auth::check( 'delete', $model, $user ) );
	}

	public function testDeleteFail() {
		$policy = new \AnsPress\Modules\Subscriber\SubscriberPolicy();

		$user = $this->factory()->user->create_and_get();

		$model = new \AnsPress\Modules\Subscriber\SubscriberModel();

		$model->subs_user_id = $user->ID + 1;

		$this->assertFalse( $policy->delete( $user, $model ) );

		$this->assertFalse( Auth::check( 'delete', $model, $user ) );
	}

}
