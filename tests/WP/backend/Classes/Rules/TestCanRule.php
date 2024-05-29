<?php
namespace Tests\Unit\src\backend\Classes\Rules;

use AnsPress\Classes\Rules\ExistsRule;
use AnsPress\Classes\Validator;
use AnsPress\Exceptions\GeneralException;
use AnsPress\Tests\WP\Testcases\Common;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * @covers AnsPress\Classes\Rules\CanRule
 * @package Tests\WP
 */
class TestCanRule extends TestCase {
	use Common;

	public function testValidateWihoutIdAndUser() {
        $validator = new Validator(
			['action' => 'test'],
			['action' => 'can:create,AnsPress\Modules\Subscriber\SubscriberModel']
		);
        $this->assertTrue($validator->fails());


		// Check with logged in user, who are allowed to create.
		$this->setRole('subscriber');

		$validator = new Validator(
			['action' => 'test'],
			['action' => 'can:create,AnsPress\Modules\Subscriber\SubscriberModel']
		);
        $this->assertFalse($validator->fails());
    }

	public function testValidateWithId() {
		$this->setRole('subscriber');

		$subscriber = new \AnsPress\Modules\Subscriber\SubscriberModel([
			'subs_user_id' => get_current_user_id(),
			'subs_event'   => 'test',
			'subs_ref_id'  => 1
		]);

		$subscriber = $subscriber->save();

		$validator = new Validator(
			['action' => 'test', 'subs_user_id' => get_current_user_id()],
			['action' => 'can:update,AnsPress\Modules\Subscriber\SubscriberModel,' . $subscriber->subs_id]
		);

		$this->assertFalse($validator->fails());

		$this->setRole('subscriber');

		$validator = new Validator(
			['action' => 'test', 'subs_user_id' => get_current_user_id()],
			['action' => 'can:update,AnsPress\Modules\Subscriber\SubscriberModel,' . $subscriber->subs_id]
		);

		$this->assertTrue($validator->fails());
	}

	public function testValidateWithIdAndUser() {
		$user = $this->factory()->user->create_and_get();

		$subscriber = new \AnsPress\Modules\Subscriber\SubscriberModel([
			'subs_user_id' => $user->ID,
			'subs_event'   => 'test',
			'subs_ref_id'  => 1
		]);

		$subscriber = $subscriber->save();

		$validator = new Validator(
			['action' => 'test'],
			['action' => 'can:update,AnsPress\Modules\Subscriber\SubscriberModel,' . $subscriber->subs_id. ',' . $user->ID]
		);

		$this->assertFalse($validator->fails());

		$this->setRole('subscriber');

		$validator = new Validator(
			['action' => 'test'],
			['action' => 'can:update,AnsPress\Modules\Subscriber\SubscriberModel,' . $subscriber->subs_id. ',0']
		);

		$this->assertTrue($validator->fails());
	}

	public function testTestInvalidPolicy() {
		$this->expectException( GeneralException::class );
		$this->expectExceptionMessage('No policy registered for model AnsPress\Modules\Subscriber\NonExistingModel');

		$validator = new Validator(
			['action' => 'test'],
			['action' => 'can:invalid,AnsPress\Modules\Subscriber\NonExistingModel']
		);

		$this->assertTrue($validator->fails());
	}

}
