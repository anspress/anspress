<?php

namespace Tests\Unit\src\backend\Classes;

use AnsPress\Classes\Auth;
use AnsPress\Classes\Plugin;
use AnsPress\Modules\Subscriber\SubscriberModel;
use AnsPress\Modules\Subscriber\SubscriberPolicy;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * @covers AnsPress\Modules\Subscriber\SubscriberPolicy
 * @package Tests\WP
 */
class TestSubscriberPolicy extends TestCase {
	public function setUp(): void {
		parent::setUp();

		Plugin::getContainer()->set(Auth::class, function() {
			return new Auth([ SubscriberPolicy::class ]);
		});
	}

	public function testBeforePass() {
		$policy = new SubscriberPolicy();

		$user = $this->factory()->user->create_and_get();

		$user->add_cap( 'manage_options' );

		$this->assertTrue( $policy->before( 'view', $user ) );
	}

	public function testBeforeFail() {
		$policy = new SubscriberPolicy();

		$user = $this->factory()->user->create_and_get();

		$this->assertNull( $policy->before( 'view', $user ) );
	}

	public function testViewPass() {
		$policy = new SubscriberPolicy();

		$user = $this->factory()->user->create_and_get();

		$model = new SubscriberModel();

		$model->subs_user_id = $user->ID;

		$this->assertTrue( $policy->view( $user, [ 'subscriber' => $model ] ) );
	}

	public function testViewFail() {
		$user = $this->factory()->user->create_and_get();

		$model = new SubscriberModel();

		$model->subs_user_id = $user->ID + 1;

		$this->assertFalse( Plugin::get(Auth::class)->check( 'subscriber:view', ['subscriber' => $model], $user ) );
	}

	public function testCreatePass() {
		$user = $this->factory()->user->create_and_get();

		$this->assertTrue( Plugin::get(Auth::class)->check( 'subscriber:create', [], $user ) );
	}

	public function testCreateFail() {
		$this->assertFalse( Plugin::get(Auth::class)->check( 'subscriber:create', [], null ) );
	}

	public function testUpdatePass() {
		$user = $this->factory()->user->create_and_get();

		$model = new SubscriberModel();

		$model->subs_user_id = $user->ID;

		$this->assertTrue( Plugin::get(Auth::class)->check( 'subscriber:update', ['subscriber' => $model], $user ) );
	}

	public function testUpdateFail() {
		$user = $this->factory()->user->create_and_get();

		$model = new SubscriberModel();

		$model->subs_user_id = $user->ID + 1;

		$this->assertFalse( Plugin::get(Auth::class)->check( 'subscriber:update', ['subscriber' => $model], $user ) );
	}

	public function testDeletePass() {
		$user = $this->factory()->user->create_and_get();

		$model = new SubscriberModel();

		$model->subs_user_id = $user->ID;

		$this->assertTrue( Plugin::get(Auth::class)->check( 'subscriber:delete', ['subscriber' => $model], $user ) );
	}

	public function testDeleteFail() {
		$user = $this->factory()->user->create_and_get();

		$model = new SubscriberModel();

		$model->subs_user_id = $user->ID + 1;

		$this->assertFalse( Plugin::get(Auth::class)->check( 'subscriber:delete', ['subscriber' => $model], $user ) );
	}

}
