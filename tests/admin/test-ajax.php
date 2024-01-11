<?php

namespace Anspress\Tests;

use AnsPress\WPTestUtils\WPIntegration\TestCaseAjax;

class TestAdminAjax extends TestCaseAjax {

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Admin_Ajax', 'init' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin_Ajax', 'ap_delete_flag' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin_Ajax', 'clear_flag' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin_Ajax', 'ap_admin_vote' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin_Ajax', 'get_all_answers' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin_Ajax', 'ap_uninstall_data' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin_Ajax', 'ap_toggle_addon' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin_Ajax', 'recount_votes' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin_Ajax', 'recount_answers' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin_Ajax', 'recount_flagged' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin_Ajax', 'recount_subscribers' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin_Ajax', 'recount_reputation' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin_Ajax', 'recount_views' ) );
	}
}
