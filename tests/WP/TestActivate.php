<?php

namespace AnsPress\Tests\WP;

use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * @covers \AP_Activate
 * @package AnsPress\Tests\WP
 */
class TestActivate extends TestCase {

	public function testInstance() {
		$class = new \ReflectionClass( 'AP_Activate' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic() );
	}

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AP_Activate' );
		$this->assertTrue( $class->hasProperty( 'charset_collate' ) && $class->getProperty( 'charset_collate' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'tables' ) && $class->getProperty( 'tables' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'network_wide' ) && $class->getProperty( 'network_wide' )->isPublic() );
	}

	public function testMethodExists() {
		// Count total methods.
		$class = new \ReflectionClass( 'AP_Activate' );
		$methods = $class->getMethods();
		$this->assertCount( 18, $methods );

		$all_methods = array(
			'get_instance',
			'__construct',
			'delete_options',
			'enable_addons',
			'qameta_table',
			'votes_table',
			'views_table',
			'reputation_table',
			'subscribers_table',
			'activity_table',
			'reputation_events_table',
			'insert_tables',
			'activate',
			'network_activate',
			'reactivate_addons',
			'migrate',
			'set_reputation_events_icon',
			'update_disallow_op_to_answer'
		);

		foreach ( $all_methods as $method ) {
			$this->assertTrue( $class->hasMethod( $method ), "Method $method not found.");
		}
	}

	public function testGetInstance() {
		$instacne1 = \AP_Activate::get_instance();
		$this->assertInstanceOf( 'AP_Activate', $instacne1 );
		$instacne2 = \AP_Activate::get_instance();
		$this->assertSame( $instacne1, $instacne2 );
	}

	public function testDeleteOptions() {
		$ap_activate = \AP_Activate::get_instance();

		// Setup initial values and test.
		$options = array(
			'user_page_title_questions' => 'Title Questions',
			'user_page_slug_questions'  => 'slug-questions',
			'user_page_title_answers'   => 'Title Answers',
			'user_page_slug_answers'    => 'slug-answers',
		);
		update_option( 'anspress_opt', $options );
		$initial_options = get_option( 'anspress_opt', array() );
		$this->assertEquals( $options, get_option( 'anspress_opt' ) );
		$this->assertArrayHasKey( 'user_page_title_questions', $initial_options );
		$this->assertArrayHasKey( 'user_page_slug_questions', $initial_options );
		$this->assertArrayHasKey( 'user_page_title_answers', $initial_options );
		$this->assertArrayHasKey( 'user_page_slug_answers', $initial_options );

		// Call the delete_options method and test.
		$ap_activate->delete_options();
		$updated_options = get_option( 'anspress_opt', array() );
		$this->assertEmpty( $updated_options );
		$this->assertEquals( array(), get_option( 'anspress_opt' ) );
		$this->assertArrayNotHasKey( 'user_page_title_questions', $updated_options );
		$this->assertArrayNotHasKey( 'user_page_slug_questions', $updated_options );
		$this->assertArrayNotHasKey( 'user_page_title_answers', $updated_options );
		$this->assertArrayNotHasKey( 'user_page_slug_answers', $updated_options );
		$this->assertFalse( wp_cache_get( 'anspress_opt', 'ap' ) );
		$this->assertFalse( wp_cache_get( 'ap_default_options', 'ap' ) );
	}

	public function testEnableAddons() {
		ap_opt( 'ap_installed', false );

		// Test if the addons are not active.
		$this->assertFalse( ap_is_addon_active( 'reputation.php' ) );
		$this->assertFalse( ap_is_addon_active( 'email.php' ) );
		$this->assertFalse( ap_is_addon_active( 'categories.php' ) );

		// Call the enable_addons method.
		$ap_activate = \AP_Activate::get_instance();
		$ap_activate->enable_addons();

		// Test begins.
		$this->assertTrue( ap_is_addon_active( 'reputation.php' ) );
		$this->assertTrue( ap_is_addon_active( 'email.php' ) );
		$this->assertTrue( ap_is_addon_active( 'categories.php' ) );
	}

	public function testQametaTableColumns() {
		global $wpdb;

		// Call the qameta_table method.
		$ap_activate = \AP_Activate::get_instance();
		$ap_activate->qameta_table();

		// Test begins.
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->ap_qameta}'" ) == $wpdb->ap_qameta;
		$this->assertTrue( $table_exists );

		// Test if the table has the expected columns.
		$columns = $wpdb->get_col( "DESCRIBE {$wpdb->ap_qameta}" );
		$expected_columns = [
			'post_id', 'selected_id', 'comments', 'answers', 'ptype', 'featured', 'selected', 'votes_up', 'votes_down', 'subscribers', 'views', 'closed', 'flags', 'terms', 'attach', 'activities', 'fields', 'roles', 'last_updated'
		];
		foreach ( $expected_columns as $column ) {
			$this->assertContains( $column, $columns );
		}
	}

	public function testQametaTableKeys() {
		global $wpdb;

		// Test if the table has the expected primary key.
		$primary_key = null;

		$columns_info = $wpdb->get_results( "DESCRIBE {$wpdb->ap_qameta}" );

		foreach ( $columns_info as $column ) {
			if ( 'PRI' === $column->Key ) {
				$primary_key = $column->Field;
				break;
			}
		}
		$this->assertEquals( 'post_id', $primary_key );
	}

	public function testVotesTableColumnVoteId() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_votes}" );

		$this->assertCount( 7, $cols );

		$this->assertEquals( 'vote_id', $cols[0]->Field );
		$this->assertEquals( 'PRI', $cols[0]->Key); // Check if it is primary key (PRI).
		$this->assertEquals( 'auto_increment', $cols[0]->Extra); // Check if it is auto increment (auto_increment).
		$this->assertEquals( 'bigint', $cols[0]->Type); // Check if it is bigint(20).
		$this->assertEquals( 'NO', $cols[0]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[0]->Default); // Check if it is NULL (NULL).

		// Check vote_id index.
		$indexes_info = $wpdb->get_row( "SHOW INDEX FROM {$wpdb->ap_votes} WHERE Column_name = 'vote_id'" );

		$this->assertEquals( 'vote_id', $indexes_info->Column_name );
		$this->assertEquals( 'PRIMARY', $indexes_info->Key_name );
	}

	public function testVotesTableColumnVotePostId() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_votes}" );

		$this->assertEquals( 'vote_post_id', $cols[1]->Field );
		$this->assertEquals( 'MUL', $cols[1]->Key); // Check if it is primary key (PRI).
		$this->assertEquals( 'bigint', $cols[1]->Type); // Check if it is bigint(20).
		$this->assertEquals( 'NO', $cols[1]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[1]->Default); // Check if it is NULL (NULL).

		// Check vote_post_id index.
		$indexes_info = $wpdb->get_row( "SHOW INDEX FROM {$wpdb->ap_votes} WHERE Column_name = 'vote_post_id'" );

		$this->assertEquals( 'vote_post_id', $indexes_info->Column_name );
		$this->assertEquals( 'vote_post_id', $indexes_info->Key_name );
	}

	public function testVotesTableColumnVoteUserId() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_votes}" );

		$this->assertEquals( 'vote_user_id', $cols[2]->Field );
		$this->assertEquals( '', $cols[2]->Key);
		$this->assertEquals( 'bigint', $cols[2]->Type); // Check if it is bigint(20).
		$this->assertEquals( 'NO', $cols[2]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[2]->Default); // Check if it is NULL (NULL).
	}

	public function testVotesTableColumnVoteRecUser() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_votes}" );

		$this->assertEquals( 'vote_rec_user', $cols[3]->Field );
		$this->assertEquals( '', $cols[3]->Key);
		$this->assertEquals( 'bigint', $cols[3]->Type); // Check if it is bigint(20).
		$this->assertEquals( 'NO', $cols[3]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[3]->Default); // Check if it is NULL (NULL).
	}

	public function testVotesTableColumnVoteType() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_votes}" );

		$this->assertEquals( 'vote_type', $cols[4]->Field );
		$this->assertEquals( '', $cols[4]->Key);
		$this->assertEquals( 'varchar(100)', $cols[4]->Type); // Check if it is varchar(20).
		$this->assertEquals( 'YES', $cols[4]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[4]->Default); // Check if it is NULL (NULL).
	}

	public function testVotesTableColumnVoteValue() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_votes}" );

		$this->assertEquals( 'vote_value', $cols[5]->Field );
		$this->assertEquals( '', $cols[5]->Key);
		$this->assertEquals( 'varchar(100)', $cols[5]->Type);
		$this->assertEquals( 'YES', $cols[5]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[5]->Default); // Check if it is NULL (NULL).
	}

	public function testVotesTableColumnVoteDate() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_votes}" );

		$this->assertEquals( 'vote_date', $cols[6]->Field );
		$this->assertEquals( '', $cols[6]->Key);
		$this->assertEquals( 'timestamp', $cols[6]->Type);
		$this->assertEquals( 'YES', $cols[6]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[6]->Default); // Check if it is NULL (NULL).
	}

	public function testViewsTableColumnVoteId() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_views}" );

		$this->assertCount( 6, $cols );

		$this->assertEquals( 'view_id', $cols[0]->Field );
		$this->assertEquals( 'PRI', $cols[0]->Key); // Check if it is primary key (PRI).
		$this->assertEquals( 'auto_increment', $cols[0]->Extra); // Check if it is auto increment (auto_increment).
		$this->assertEquals( 'bigint', $cols[0]->Type); // Check if it is bigint(20).
		$this->assertEquals( 'NO', $cols[0]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[0]->Default); // Check if it is NULL (NULL).

		// Check vote_id index.
		$indexes_info = $wpdb->get_row( "SHOW INDEX FROM {$wpdb->ap_views} WHERE Column_name = 'view_id'" );

		$this->assertEquals( 'view_id', $indexes_info->Column_name );
		$this->assertEquals( 'PRIMARY', $indexes_info->Key_name );
	}

	public function testViewsTableColumnViewUserId() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_views}" );

		$this->assertEquals( 'view_user_id', $cols[1]->Field );
		$this->assertEquals( 'MUL', $cols[1]->Key);
		$this->assertEquals( 'bigint', $cols[1]->Type); // Check if it is bigint(20).
		$this->assertEquals( 'YES', $cols[1]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[1]->Default); // Check if it is NULL (NULL).
	}

	public function testViewsTableColumnViewType() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_views}" );

		$this->assertEquals( 'view_type', $cols[2]->Field );
		$this->assertEquals( '', $cols[2]->Key);
		$this->assertEquals( 'varchar(100)', $cols[2]->Type); // Check if it is varchar(20).
		$this->assertEquals( 'YES', $cols[2]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[2]->Default); // Check if it is NULL (NULL).
	}

	public function testViewsTableColumnViewRefId() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_views}" );

		$this->assertEquals( 'view_ref_id', $cols[3]->Field );
		$this->assertEquals( '', $cols[3]->Key);
		$this->assertEquals( 'bigint', $cols[3]->Type); // Check if it is bigint(20).
		$this->assertEquals( 'YES', $cols[3]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[3]->Default); // Check if it is NULL (NULL).
	}

	public function testViewsTableColumnViewIp() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_views}" );

		$this->assertEquals( 'view_ip', $cols[4]->Field );
		$this->assertEquals( '', $cols[4]->Key);
		$this->assertEquals( 'varchar(39)', $cols[4]->Type); // Check if it is varchar(20).
		$this->assertEquals( 'YES', $cols[4]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[4]->Default); // Check if it is NULL (NULL).
	}

	public function testViewsTableColumnViewDate() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_views}" );

		$this->assertEquals( 'view_date', $cols[5]->Field );
		$this->assertEquals( '', $cols[5]->Key);
		$this->assertEquals( 'timestamp', $cols[5]->Type);
		$this->assertEquals( 'YES', $cols[5]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[5]->Default); // Check if it is NULL (NULL).
	}

	public function testReputationTableColumnRepId() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_reputations}" );

		$this->assertCount( 5, $cols );

		$this->assertEquals( 'rep_id', $cols[0]->Field );
		$this->assertEquals( 'PRI', $cols[0]->Key); // Check if it is primary key (PRI).
		$this->assertEquals( 'auto_increment', $cols[0]->Extra); // Check if it is auto increment (auto_increment).
		$this->assertEquals( 'bigint', $cols[0]->Type); // Check if it is bigint(20).
		$this->assertEquals( 'NO', $cols[0]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[0]->Default); // Check if it is NULL (NULL).

		// Check rep_id index.
		$indexes_info = $wpdb->get_row( "SHOW INDEX FROM {$wpdb->ap_reputations} WHERE Column_name = 'rep_id'" );

		$this->assertEquals( 'rep_id', $indexes_info->Column_name );
		$this->assertEquals( 'PRIMARY', $indexes_info->Key_name );
	}

	public function testReputationTableColumnRepUserId() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_reputations}" );

		$this->assertEquals( 'rep_user_id', $cols[1]->Field );
		$this->assertEquals( 'MUL', $cols[1]->Key);
		$this->assertEquals( 'bigint', $cols[1]->Type); // Check if it is bigint(20).
		$this->assertEquals( 'YES', $cols[1]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[1]->Default); // Check if it is NULL (NULL).

		// Check rep_user_id index.
		$indexes_info = $wpdb->get_row( "SHOW INDEX FROM {$wpdb->ap_reputations} WHERE Column_name = 'rep_user_id'" );

		$this->assertEquals( 'rep_user_id', $indexes_info->Column_name );
	}

	public function testReputationTableColumnRepEvent() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_reputations}" );

		$this->assertEquals( 'rep_event', $cols[2]->Field );
		$this->assertEquals( '', $cols[2]->Key);
		$this->assertEquals( 'varchar(100)', $cols[2]->Type);
		$this->assertEquals( 'YES', $cols[2]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[2]->Default); // Check if it is NULL (NULL).
	}

	public function testReputationTableColumnRepRefId() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_reputations}" );

		$this->assertEquals( 'rep_ref_id', $cols[3]->Field );
		$this->assertEquals( 'MUL', $cols[3]->Key);
		$this->assertEquals( 'bigint', $cols[3]->Type);
		$this->assertEquals( 'YES', $cols[3]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[3]->Default); // Check if it is NULL (NULL).

		// Check rep_ref_id index.
		$indexes_info = $wpdb->get_row( "SHOW INDEX FROM {$wpdb->ap_reputations} WHERE Column_name = 'rep_ref_id'" );

		$this->assertEquals( 'rep_ref_id', $indexes_info->Column_name );
	}

	public function testReputationTableColumnRepDate() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_reputations}" );

		$this->assertEquals( 'rep_date', $cols[4]->Field );
		$this->assertEquals( '', $cols[4]->Key);
		$this->assertEquals( 'timestamp', $cols[4]->Type);
		$this->assertEquals( 'YES', $cols[4]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[4]->Default); // Check if it is NULL (NULL).
	}

	public function testSubscribersTableColumnSubId() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_subscribers}" );

		$this->assertCount( 4, $cols );

		$this->assertEquals( 'subs_id', $cols[0]->Field );
		$this->assertEquals( 'PRI', $cols[0]->Key); // Check if it is primary key (PRI).
		$this->assertEquals( 'auto_increment', $cols[0]->Extra); // Check if it is auto increment (auto_increment).
		$this->assertEquals( 'bigint unsigned', $cols[0]->Type); // Check if it is bigint(20).
		$this->assertEquals( 'NO', $cols[0]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[0]->Default); // Check if it is NULL (NULL).

		// Check subs_id index.
		$indexes_info = $wpdb->get_row( "SHOW INDEX FROM {$wpdb->ap_subscribers} WHERE Column_name = 'subs_id'" );

		$this->assertEquals( 'subs_id', $indexes_info->Column_name );
		$this->assertEquals( 'PRIMARY', $indexes_info->Key_name );
	}

	public function testSubscribersTableColumnSubUserId() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_subscribers}" );

		$this->assertEquals( 'subs_user_id', $cols[1]->Field );
		$this->assertEquals( 'MUL', $cols[1]->Key);
		$this->assertEquals( 'bigint unsigned', $cols[1]->Type); // Check if it is bigint(20).
		$this->assertEquals( 'NO', $cols[1]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[1]->Default); // Check if it is NULL (NULL).

		// Check subs_user_id index.
		$indexes_info = $wpdb->get_row( "SHOW INDEX FROM {$wpdb->ap_subscribers} WHERE Column_name = 'subs_user_id'" );

		$this->assertEquals( 'subs_user_id', $indexes_info->Column_name );
	}

	public function testSubscribersTableColumnSubsRefId() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_subscribers}" );

		$this->assertEquals( 'subs_ref_id', $cols[2]->Field );
		$this->assertEquals( 'MUL', $cols[2]->Key);
		$this->assertEquals( 'bigint unsigned', $cols[2]->Type);
		$this->assertEquals( 'NO', $cols[2]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[2]->Default); // Check if it is NULL (NULL).

		// Check subs_ref_id index.
		$indexes_info = $wpdb->get_row( "SHOW INDEX FROM {$wpdb->ap_subscribers} WHERE Column_name = 'subs_ref_id'" );

		$this->assertEquals( 'subs_ref_id', $indexes_info->Column_name );

	}

	public function testSubscribersTableColumnSubsEvent() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_subscribers}" );

		$this->assertEquals( 'subs_event', $cols[3]->Field );
		$this->assertEquals( '', $cols[3]->Key);
		$this->assertEquals( 'varchar(100)', $cols[3]->Type);
		$this->assertEquals( 'NO', $cols[3]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[3]->Default); // Check if it is NULL (NULL).
	}

	public function testActivityTableColumnActivityId() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_activity}" );

		$this->assertCount( 7, $cols );

		$this->assertEquals( 'activity_id', $cols[0]->Field );
		$this->assertEquals( 'PRI', $cols[0]->Key); // Check if it is primary key (PRI).
		$this->assertEquals( 'auto_increment', $cols[0]->Extra); // Check if it is auto increment (auto_increment).
		$this->assertEquals( 'bigint unsigned', $cols[0]->Type);
		$this->assertEquals( 'NO', $cols[0]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[0]->Default); // Check if it is NULL (NULL).

		// Check activity_id index.
		$indexes_info = $wpdb->get_row( "SHOW INDEX FROM {$wpdb->ap_activity} WHERE Column_name = 'activity_id'" );

		$this->assertEquals( 'activity_id', $indexes_info->Column_name );
		$this->assertEquals( 'PRIMARY', $indexes_info->Key_name );
	}

	public function testActivityTableColumnActivityAction() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_activity}" );

		$this->assertEquals( 'activity_action', $cols[1]->Field );
		$this->assertEquals( '', $cols[1]->Key);
		$this->assertEquals( 'varchar(45)', $cols[1]->Type);
		$this->assertEquals( 'NO', $cols[1]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[1]->Default); // Check if it is NULL (NULL).
	}

	public function testActivityTableColumnActivityQId() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_activity}" );

		$this->assertEquals( 'activity_q_id', $cols[2]->Field );
		$this->assertEquals( 'MUL', $cols[2]->Key);
		$this->assertEquals( 'bigint unsigned', $cols[2]->Type);
		$this->assertEquals( 'NO', $cols[2]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[2]->Default); // Check if it is NULL (NULL).

		// Check activity_q_id index.
		$indexes_info = $wpdb->get_row( "SHOW INDEX FROM {$wpdb->ap_activity} WHERE Column_name = 'activity_q_id'" );

		$this->assertEquals( 'activity_q_id', $indexes_info->Column_name );
	}

	public function testActivityTableColumnActivityAId() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_activity}" );

		$this->assertEquals( 'activity_a_id', $cols[3]->Field );
		$this->assertEquals( 'MUL', $cols[3]->Key);
		$this->assertEquals( 'bigint unsigned', $cols[3]->Type);
		$this->assertEquals( 'YES', $cols[3]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[3]->Default); // Check if it is NULL (NULL).

		// Check activity_a_id index.
		$indexes_info = $wpdb->get_row( "SHOW INDEX FROM {$wpdb->ap_activity} WHERE Column_name = 'activity_a_id'" );

		$this->assertEquals( 'activity_a_id', $indexes_info->Column_name );
	}

	public function testActivityTableColumnActivityCId() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_activity}" );

		$this->assertEquals( 'activity_c_id', $cols[4]->Field );
		$this->assertEquals( '', $cols[4]->Key);
		$this->assertEquals( 'bigint unsigned', $cols[4]->Type);
		$this->assertEquals( 'YES', $cols[4]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[4]->Default); // Check if it is NULL (NULL).
	}

	public function testActivityTableColumnActivityUserId() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_activity}" );

		$this->assertEquals( 'activity_user_id', $cols[5]->Field );
		$this->assertEquals( 'MUL', $cols[5]->Key);
		$this->assertEquals( 'bigint unsigned', $cols[5]->Type);
		$this->assertEquals( 'NO', $cols[5]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[5]->Default); // Check if it is NULL (NULL).

		// Check activity_user_id index.
		$indexes_info = $wpdb->get_row( "SHOW INDEX FROM {$wpdb->ap_activity} WHERE Column_name = 'activity_user_id'" );

		$this->assertEquals( 'activity_user_id', $indexes_info->Column_name );
	}

	public function testActivityTableColumnActivityDate() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_activity}" );

		$this->assertEquals( 'activity_date', $cols[6]->Field );
		$this->assertEquals( '', $cols[6]->Key);
		$this->assertEquals( 'timestamp', $cols[6]->Type);
		$this->assertEquals( 'YES', $cols[6]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[6]->Default); // Check if it is NULL (NULL).
	}

	public function testReputationEventsTableRepEventId() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_reputation_events}" );

		$this->assertCount( 8, $cols );

		$this->assertEquals( 'rep_events_id', $cols[0]->Field );
		$this->assertEquals( 'PRI', $cols[0]->Key); // Check if it is primary key (PRI).
		$this->assertEquals( 'auto_increment', $cols[0]->Extra); // Check if it is auto increment (auto_increment).
		$this->assertEquals( 'bigint unsigned', $cols[0]->Type); // Check if it is bigint(20).
		$this->assertEquals( 'NO', $cols[0]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[0]->Default); // Check if it is NULL (NULL).

		// Check rep_events_id index.
		$indexes_info = $wpdb->get_row( "SHOW INDEX FROM {$wpdb->ap_reputation_events} WHERE Column_name = 'rep_events_id'" );

		$this->assertEquals( 'rep_events_id', $indexes_info->Column_name );
		$this->assertEquals( 'PRIMARY', $indexes_info->Key_name );
	}

	public function testReputationEventsTableRepEventSlug() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_reputation_events}" );

		$this->assertEquals( 'slug', $cols[1]->Field );
		$this->assertEquals( 'UNI', $cols[1]->Key);
		$this->assertEquals( 'varchar(100)', $cols[1]->Type);
		$this->assertEquals( 'NO', $cols[1]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[1]->Default); // Check if it is NULL (NULL).

		// Check slug index.
		$indexes_info = $wpdb->get_row( "SHOW INDEX FROM {$wpdb->ap_reputation_events} WHERE Column_name = 'slug'" );

		$this->assertEquals( 'slug', $indexes_info->Column_name );
	}

	public function testReputationEventsTableRepEventIcon() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_reputation_events}" );

		$this->assertEquals( 'icon', $cols[2]->Field );
		$this->assertEquals( '', $cols[2]->Key);
		$this->assertEquals( 'varchar(100)', $cols[2]->Type);
		$this->assertEquals( 'NO', $cols[2]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[2]->Default); // Check if it is NULL (NULL).
	}

	public function testReputationEventsTableRepEventLabel() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_reputation_events}" );

		$this->assertEquals( 'label', $cols[3]->Field );
		$this->assertEquals( '', $cols[3]->Key);
		$this->assertEquals( 'varchar(100)', $cols[3]->Type);
		$this->assertEquals( 'NO', $cols[3]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[3]->Default); // Check if it is NULL (NULL).
	}

	public function testReputationEventsTableRepEventDescription() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_reputation_events}" );

		$this->assertEquals( 'description', $cols[4]->Field );
		$this->assertEquals( '', $cols[4]->Key);
		$this->assertEquals( 'varchar(200)', $cols[4]->Type);
		$this->assertEquals( 'NO', $cols[4]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[4]->Default); // Check if it is NULL (NULL).
	}

	public function testReputationEventsTableRepEventActivity() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_reputation_events}" );

		$this->assertEquals( 'activity', $cols[5]->Field );
		$this->assertEquals( '', $cols[5]->Key);
		$this->assertEquals( 'varchar(200)', $cols[5]->Type);
		$this->assertEquals( 'NO', $cols[5]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( NULL, $cols[5]->Default); // Check if it is NULL (NULL).
	}

	public function testReputationEventsTableRepEventParent() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_reputation_events}" );

		$this->assertEquals( 'parent', $cols[6]->Field );
		$this->assertEquals( 'MUL', $cols[6]->Key);
		$this->assertEquals( 'varchar(100)', $cols[6]->Type);
		$this->assertEquals( 'NO', $cols[6]->Null);
		$this->assertEquals( NULL, $cols[6]->Default);

		// Check parent index.
		$indexes_info = $wpdb->get_row( "SHOW INDEX FROM {$wpdb->ap_reputation_events} WHERE Column_name = 'parent'" );

		$this->assertEquals( 'parent', $indexes_info->Column_name );
	}

	public function testReputationEventsTableRepEventPoints() {
		global $wpdb;

		$cols = $wpdb->get_results( "DESCRIBE {$wpdb->ap_reputation_events}" );

		$this->assertEquals( 'points', $cols[7]->Field );
		$this->assertEquals( 'MUL', $cols[7]->Key);
		$this->assertEquals( 'int', $cols[7]->Type);
		$this->assertEquals( 'NO', $cols[7]->Null); // Check if it is NOT NULL (NOT NULL).
		$this->assertEquals( '0', $cols[7]->Default); // Check if it is NULL (NULL).
	}

	public function testInsertTables() {
		global $wpdb;

		// Test begins.
		$this->assertTrue( $this->tableExists( $wpdb->prefix . 'ap_qameta' ) );
		$this->assertTrue( $this->tableExists( $wpdb->prefix . 'ap_votes' ) );
		$this->assertTrue( $this->tableExists( $wpdb->prefix . 'ap_views' ) );
		$this->assertTrue( $this->tableExists( $wpdb->prefix . 'ap_reputations' ) );
		$this->assertTrue( $this->tableExists( $wpdb->prefix . 'ap_subscribers' ) );
		$this->assertTrue( $this->tableExists( $wpdb->prefix . 'ap_activity' ) );
		$this->assertTrue( $this->tableExists( $wpdb->prefix . 'ap_reputation_events' ) );
	}

	/**
	 * Test for table exists.
	 *
	 * @param string $table_name Table name.
	 *
	 * @return bool
	 */
	private function tableExists( $table_name ) {
		global $wpdb;
		$sql = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );
		return (bool) $wpdb->get_var( $sql );
	}
}
