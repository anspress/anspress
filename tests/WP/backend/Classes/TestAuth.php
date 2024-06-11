<?php

namespace Tests\Unit\src\backend\Classes;

use AnsPress\Classes\AbstractModel;
use AnsPress\Classes\AbstractPolicy;
use AnsPress\Classes\AbstractSchema;
use AnsPress\Classes\Auth;
use AnsPress\Classes\Plugin;
use AnsPress\Tests\WP\Testcases\Common;
use WP_User;
use Yoast\WPTestUtils\WPIntegration\TestCase;
use wpdb;

class SampleSchema extends AbstractSchema {
	public function getTableName(): string {
		global $wpdb;

		return $wpdb->prefix . 'test_table';
	}

	public function getPrimaryKey(): string {
		return 'id';
	}

	public function getColumns(): array {
		return array(
			'id'        => '%d',
			'str_field' => '%s',
		);
	}
}

class SampleModel extends AbstractModel {
	protected static function createSchema(): AbstractSchema {
		return new SampleSchema();
	}
}

class SamplePolicy extends AbstractPolicy{
	public const POLICY_NAME = 'sample';

	public array $abilities = array(
		'view' => array(
			'id',
		),
	);

	public function before( string $ability, ?WP_User $user, array $context = array() ): ?bool {
		if ( Auth::user() && Auth::user()->has_cap('manage_options') ) {
			return true;
		}

		return null;
	}
}


/**
 * @covers AnsPress\Classes\Auth
 * @package Tests\Unit
 */
class TestAuth extends TestCase {
	use Common;

	public function setUp(): void {
		parent::setUp();

		Plugin::getContainer()->set(Auth::class, function() {
			return new Auth([ SamplePolicy::class ]);
		});
	}

	public function testIsUserIsLoggedIn() {
		$auth = new \AnsPress\Classes\Auth([ SamplePolicy::class ]);

		$this->assertFalse($auth->isLoggedIn());

		$this->setRole('administrator');

		$this->assertTrue($auth->isLoggedIn());
	}

	public function testUser() {
		$this->assertNull(\AnsPress\Classes\Auth::user());

		$this->setRole('administrator');

		$this->assertInstanceOf(\WP_User::class, \AnsPress\Classes\Auth::user());
	}

	public function testCurrentUserCan() {
		$this->assertFalse(\AnsPress\Classes\Auth::currentUserCan('sample:view', ['id' => 1]));

		$this->setRole('subscriber');

		// Add ability to user in WP user.
		$user = \AnsPress\Classes\Auth::user();
		$user->add_cap('sample:view');

		$this->assertTrue(\AnsPress\Classes\Auth::currentUserCan('sample:view', ['id' => 1]));
	}

	public function testCheckBefore() {
		$this->setRole('administrator');

		$this->assertTrue(\AnsPress\Classes\Plugin::get(Auth::class)->check('sample:view', ['id' => 1]));
	}

	public function testCheckThrow() {
		$this->expectException(\AnsPress\Exceptions\AuthException::class);

		\AnsPress\Classes\Auth::checkAndThrow('sample:view', ['id' => 1]);
	}

	public function testCheckGeneralException() {

		$this->expectException(\AnsPress\Exceptions\GeneralException::class);

		$this->expectExceptionMessage('Invalid ability format, it must be policyName:ability.');

		Plugin::get(Auth::class)->check('sample');
	}

	public function testGeneralExceptionForInvalidPolicy() {
		$this->expectException(\AnsPress\Exceptions\GeneralException::class);

		$this->expectExceptionMessage('Policy does not exist.');

		Plugin::get(Auth::class)->check('invalid:view');
	}

	public function testGeneralExceptionForInvalidContext() {

		$this->expectException(\AnsPress\Exceptions\GeneralException::class);

		$this->setRole('subscriber');

		$this->expectExceptionMessage('Invalid context.');

		Plugin::get(Auth::class)->check('sample:view');
	}
}
