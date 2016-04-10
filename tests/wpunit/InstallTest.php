<?php

class InstallTest extends \Codeception\TestCase\WPTestCase
{

	public function setUp() {

		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown() {

		// your tear down methods here
		// then
		parent::tearDown();
	}

	/**
	 * Test if the default settings are set and have settings pages.
	 */
	public function test_settings() {
		$this->assertArrayHasKey( 'base_page', ap_opt() );
		$this->assertArrayHasKey( 'base_page_id', ap_opt() );
		$this->assertEquals( 'questions', ap_opt('base_page_id' ) );
	}

	/**
	 * Check if table exists.
	 */
	public function test_tables() {
		global $wpdb;

		$this->assertEquals( $wpdb->get_var( "show tables like '{$wpdb->ap_meta}'" ), $wpdb->ap_meta, 'ap_meta table does not exists' );

		$this->assertEquals( $wpdb->get_var( "show tables like '{$wpdb->ap_activity}'" ), $wpdb->ap_activity, 'ap_activity table does not exists' );

		$this->assertEquals( $wpdb->get_var( "show tables like '{$wpdb->ap_activitymeta}'" ), $wpdb->ap_activitymeta, 'ap_activitymeta table does not exists' );

		$this->assertEquals( $wpdb->get_var( "show tables like '{$wpdb->ap_notifications}'" ), $wpdb->ap_notifications, 'ap_notifications table does not exists' );

		$this->assertEquals( $wpdb->get_var( "show tables like '{$wpdb->ap_subscribers}'" ), $wpdb->ap_subscribers, 'ap_subscribers table does not exists' );
	}

    /**
     * Check table columns.
     */
	public function test_tables_have_columns() {
		global $wpdb;

		$tables = array(
			'ap_meta' => array(
				'apmeta_id',
				'apmeta_userid',
				'apmeta_type',
				'apmeta_actionid',
				'apmeta_value',
				'apmeta_param',
				'apmeta_date',
			),
			'ap_activity' => array(
				'id',
				'user_id',
				'secondary_user',
				'type',
				'parent_type',
				'status',
				'content',
				'permalink',
				'question_id',
				'answer_id',
				'item_id',
				'term_ids',
				'created',
				'updated',
			),
			'ap_activitymeta' => array(
				'meta_id',
				'ap_activity_id',
				'meta_key',
				'meta_value',
			),
			'ap_notifications' => array(
				'noti_id',
				'noti_activity_id',
				'noti_user_id',
				'noti_status',
				'noti_date',
			),
			'ap_subscribers' => array(
				'subs_id',
				'subs_user_id',
				'subs_question_id',
				'subs_item_id',
				'subs_activity',
			),
		);

		foreach ( $tables as $table => $cols ) {
			$cols_q = $wpdb->get_results("SHOW COLUMNS FROM `{$wpdb->$table}`" );
			foreach ( $cols_q as $col_q ) {
				$this->assertTrue( in_array($col_q->Field, $cols ) );
			}
		}
	}

}
