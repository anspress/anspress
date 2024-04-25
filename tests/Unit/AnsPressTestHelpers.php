<?php

namespace Tests\Unit;

use Mockery;
use wpdb;
use Brain\Monkey\Functions;

trait AnsPressTestHelpers {
	/**
	 * Helper to mock the database call and assert results are an integer.
	 */
	private function setupWPDBMock() {
		global $wpdb;
		$wpdb = Mockery::mock(wpdb::class)->makePartial();
		$wpdb->ap_votes = 'wp_ap_votes';
		$wpdb->prefix = 'wp_';

		return $wpdb;
	}

	/**
	 * Helper to check action is registered using do_action.
	 */
	private function assertActionRegistered($action, ...$args) {
		Functions\expect('do_action')
			->once()
			->with($action, ...$args);
	}

	/**
	 * Helper to check action is not registered using do_action.
	 */
	private function assertActionNotRegistered($action, ...$args) {
		Functions\expect('do_action')
			->never()
			->with($action, ...$args);
	}
}
