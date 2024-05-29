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
	public function before( string $ability, ?WP_User $user ) : ?bool {
		if ( ! Auth::isLoggedIn() ) {
			return false;
		}

		return null;
	}

	public function view( WP_User $user, AbstractModel $model ) : bool {
		return true;
	}
}


/**
 * @covers AnsPress\Classes\Auth
 * @package Tests\Unit
 */
class TestAuth extends TestCase {
	use Common;

	public function testIsUserIsLoggedIn() {
		$auth = new \AnsPress\Classes\Auth();

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
		$model = new SampleModel();
		$policy = new SamplePolicy();

		Plugin::registerPolicy(SampleModel::class, SamplePolicy::class);

		$this->assertFalse(\AnsPress\Classes\Auth::currentUserCan('view', $model));

		$this->setRole('subscriber');

		$this->assertTrue(\AnsPress\Classes\Auth::currentUserCan('view', $model));
	}

	public function testCheckBefore() {
		$model = new SampleModel();
		$policy = new SamplePolicy();

		Plugin::registerPolicy(SampleModel::class, SamplePolicy::class);

		$this->assertFalse(\AnsPress\Classes\Auth::check('view', $model));
	}
}
