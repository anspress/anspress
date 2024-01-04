<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestActivate extends TestCase {

	/**
	 * @covers AP_Activate::instance
	 */
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
		$this->assertTrue( method_exists( 'AP_Activate', 'get_instance' ) );
		$this->assertTrue( method_exists( 'AP_Activate', '__construct' ) );
		$this->assertTrue( method_exists( 'AP_Activate', 'disable_ext' ) );
		$this->assertTrue( method_exists( 'AP_Activate', 'delete_options' ) );
		$this->assertTrue( method_exists( 'AP_Activate', 'enable_addons' ) );
		$this->assertTrue( method_exists( 'AP_Activate', 'qameta_table' ) );
		$this->assertTrue( method_exists( 'AP_Activate', 'votes_table' ) );
		$this->assertTrue( method_exists( 'AP_Activate', 'views_table' ) );
		$this->assertTrue( method_exists( 'AP_Activate', 'reputation_table' ) );
		$this->assertTrue( method_exists( 'AP_Activate', 'subscribers_table' ) );
		$this->assertTrue( method_exists( 'AP_Activate', 'activity_table' ) );
		$this->assertTrue( method_exists( 'AP_Activate', 'reputation_events_table' ) );
		$this->assertTrue( method_exists( 'AP_Activate', 'insert_tables' ) );
		$this->assertTrue( method_exists( 'AP_Activate', 'activate' ) );
		$this->assertTrue( method_exists( 'AP_Activate', 'network_activate' ) );
		$this->assertTrue( method_exists( 'AP_Activate', 'reactivate_addons' ) );
	}

	/**
	 * @covers AP_Activate::get_instance
	 */
	public function testGetInstance() {
		$instacne1 = \AP_Activate::get_instance();
		$this->assertInstanceOf( 'AP_Activate', $instacne1 );
		$instacne2 = \AP_Activate::get_instance();
		$this->assertSame( $instacne1, $instacne2 );
	}

	/**
	 * @covers AP_Activate::delete_options
	 */
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

	/**
	 * @covers AP_Activate::enable_addons
	 */
	public function testEnableAddons() {
		// By default these addons are activated on plugin activation,
		// so we need to deactivate them first.
		ap_deactivate_addon( 'reputation.php' );
		ap_deactivate_addon( 'email.php' );
		ap_deactivate_addon( 'categories.php' );

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

	/**
	 * @covers AP_Activate::qameta_table
	 */
	public function testQametaTable() {
		global $wpdb;

		// Call the qameta_table method.
		$ap_activate = \AP_Activate::get_instance();
		$ap_activate->qameta_table();

		// Test begins.
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->ap_qameta}'" ) == $wpdb->ap_qameta;
		$this->assertTrue( $table_exists );

		// Test if the table has the expected columns.
		$columns = $wpdb->get_col( "DESCRIBE {$wpdb->ap_qameta}" );
		$expected_columns = [ 'post_id', 'selected_id', 'comments', 'answers', 'ptype', 'featured', 'selected', 'votes_up', 'votes_down', 'subscribers', 'views', 'closed', 'flags', 'terms', 'attach', 'activities', 'fields', 'roles', 'last_updated' ];
		foreach ( $expected_columns as $column ) {
			$this->assertContains( $column, $columns );
		}

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

	/**
	 * @covers AP_Activate::votes_table
	 */
	public function testVotesTable() {
		global $wpdb;

		// Call the votes_table method.
		$ap_activate = \AP_Activate::get_instance();
		$ap_activate->votes_table();

		// Test begins.
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->ap_votes}'" ) == $wpdb->ap_votes;
		$this->assertTrue( $table_exists );

		// Test if the table has the expected columns.
		$columns = $wpdb->get_col( "DESCRIBE {$wpdb->ap_votes}" );
		$expected_columns = [ 'vote_id', 'vote_post_id', 'vote_user_id', 'vote_rec_user', 'vote_type', 'vote_value', 'vote_date' ];
		foreach ( $expected_columns as $column ) {
			$this->assertContains( $column, $columns );
		}

		// Test if the table has the expected primary key.
		$primary_key = null;
		$columns_info = $wpdb->get_results( "DESCRIBE {$wpdb->ap_votes}" );
		foreach ( $columns_info as $column ) {
			if ( 'PRI' === $column->Key ) {
				$primary_key = $column->Field;
				break;
			}
		}
		$this->assertEquals( 'vote_id', $primary_key );

		// Test if the table has the expected index.
		$index_exists = false;
		$indexes_info = $wpdb->get_results( "SHOW INDEX FROM {$wpdb->ap_votes}" );
		foreach ( $indexes_info as $index ) {
			if ( 'vote_post_id' === $index->Key_name ) {
				$index_exists = true;
				break;
			}
		}
		$this->assertTrue( $index_exists );
	}

	/**
	 * @covers AP_Activate::views_table
	 */
	public function testViewsTable() {
		global $wpdb;

		// Call the views_table method.
		$ap_activate = \AP_Activate::get_instance();
		$ap_activate->views_table();

		// Test begins.
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->ap_views}'" ) == $wpdb->ap_views;
		$this->assertTrue( $table_exists );

		// Test if the table has the expected columns.
		$columns = $wpdb->get_col( "DESCRIBE {$wpdb->ap_views}" );
		$expected_columns = [ 'view_id', 'view_user_id', 'view_type', 'view_ref_id', 'view_ip', 'view_date' ];
		foreach ( $expected_columns as $column ) {
			$this->assertContains( $column, $columns );
		}

		// Test if the table has the expected primary key.
		$primary_key = null;
		$columns_info = $wpdb->get_results( "DESCRIBE {$wpdb->ap_views}" );
		foreach ( $columns_info as $column ) {
			if ( 'PRI' === $column->Key ) {
				$primary_key = $column->Field;
				break;
			}
		}
		$this->assertEquals( 'view_id', $primary_key );

		// Test if the table has the expected index.
		$index_exists = false;
		$indexes_info = $wpdb->get_results( "SHOW INDEX FROM {$wpdb->ap_views}" );
		foreach ( $indexes_info as $index ) {
			if ( 'view_user_id' === $index->Key_name ) {
				$index_exists = true;
				break;
			}
		}
		$this->assertTrue( $index_exists );
	}

	/**
	 * @covers AP_Activate::reputation_table
	 */
	public function testReputationTable() {
		global $wpdb;

		// Call the reputation_table method.
		$ap_activate = \AP_Activate::get_instance();
		$ap_activate->reputation_table();

		// Test begins.
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->ap_reputations}'" ) == $wpdb->ap_reputations;
		$this->assertTrue( $table_exists );

		// Test if the table has the expected columns.
		$columns = $wpdb->get_col( "DESCRIBE {$wpdb->ap_reputations}" );
		$expected_columns = [ 'rep_id', 'rep_user_id', 'rep_event', 'rep_ref_id', 'rep_date' ];
		foreach ( $expected_columns as $column ) {
			$this->assertContains( $column, $columns );
		}

		// Test if the table has the expected primary key.
		$primary_key = null;
		$columns_info = $wpdb->get_results( "DESCRIBE {$wpdb->ap_reputations}" );
		foreach ( $columns_info as $column ) {
			if ( 'PRI' === $column->Key ) {
				$primary_key = $column->Field;
				break;
			}
		}
		$this->assertEquals( 'rep_id', $primary_key );

		// Test if the table has the expected index.
		$index_exists = false;
		$indexes_info = $wpdb->get_results( "SHOW INDEX FROM {$wpdb->ap_reputations}" );
		foreach ( $indexes_info as $index ) {
			if ( 'rep_user_id' === $index->Key_name ) {
				$index_exists = true;
				break;
			}
		}
		$this->assertTrue( $index_exists );
		$index_exists = false;
		foreach ( $indexes_info as $index ) {
			if ( 'rep_ref_id' === $index->Key_name ) {
				$index_exists = true;
				break;
			}
		}
		$this->assertTrue( $index_exists );
	}

	/**
	 * @covers AP_Activate::subscribers_table
	 */
	public function testSubscribersTable() {
		global $wpdb;

		// Call the subscribers_table method.
		$ap_activate = \AP_Activate::get_instance();
		$ap_activate->subscribers_table();

		// Test begins.
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->ap_subscribers}'" ) == $wpdb->ap_subscribers;
		$this->assertTrue( $table_exists );

		// Test if the table has the expected columns.
		$columns = $wpdb->get_col( "DESCRIBE {$wpdb->ap_subscribers}" );
		$expected_columns = [ 'subs_id', 'subs_user_id', 'subs_ref_id', 'subs_event' ];
		foreach ( $expected_columns as $column ) {
			$this->assertContains( $column, $columns );
		}

		// Test if the table has the expected primary key.
		$primary_key = null;
		$columns_info = $wpdb->get_results( "DESCRIBE {$wpdb->ap_subscribers}" );
		foreach ( $columns_info as $column ) {
			if ( 'PRI' === $column->Key ) {
				$primary_key = $column->Field;
				break;
			}
		}
		$this->assertEquals( 'subs_id', $primary_key );

		// Test if the table has the expected index.
		$index_exists = false;
		$indexes_info = $wpdb->get_results( "SHOW INDEX FROM {$wpdb->ap_subscribers}" );
		foreach ( $indexes_info as $index ) {
			if ( 'subs_user_id' === $index->Key_name ) {
				$index_exists = true;
				break;
			}
		}
		$this->assertTrue( $index_exists );
		$index_exists = false;
		foreach ( $indexes_info as $index ) {
			if ( 'subs_ref_id' === $index->Key_name ) {
				$index_exists = true;
				break;
			}
		}
		$this->assertTrue( $index_exists );
	}

	/**
	 * @covers AP_Activate::activity_table
	 */
	public function testActivityTable() {
		global $wpdb;

		// Call the activity_table method.
		$ap_activate = \AP_Activate::get_instance();
		$ap_activate->activity_table();

		// Test begins.
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->ap_activity}'" ) == $wpdb->ap_activity;
		$this->assertTrue( $table_exists );

		// Test if the table has the expected columns.
		$columns = $wpdb->get_col( "DESCRIBE {$wpdb->ap_activity}" );
		$expected_columns = [ 'activity_id', 'activity_action', 'activity_q_id', 'activity_a_id', 'activity_c_id', 'activity_user_id', 'activity_date' ];
		foreach ( $expected_columns as $column ) {
			$this->assertContains( $column, $columns );
		}

		// Test if the table has the expected primary key.
		$primary_key = null;
		$columns_info = $wpdb->get_results( "DESCRIBE {$wpdb->ap_activity}" );
		foreach ( $columns_info as $column ) {
			if ( 'PRI' === $column->Key ) {
				$primary_key = $column->Field;
				break;
			}
		}
		$this->assertEquals( 'activity_id', $primary_key );

		// Test if the table has the expected index.
		$index_exists = false;
		$indexes_info = $wpdb->get_results( "SHOW INDEX FROM {$wpdb->ap_activity}" );
		foreach ( $indexes_info as $index ) {
			if ( 'activity_q_id' === $index->Key_name ) {
				$index_exists = true;
				break;
			}
		}
		$this->assertTrue( $index_exists );
		$index_exists = false;
		foreach ( $indexes_info as $index ) {
			if ( 'activity_a_id' === $index->Key_name ) {
				$index_exists = true;
				break;
			}
		}
		$this->assertTrue( $index_exists );
		$index_exists = false;
		foreach ( $indexes_info as $index ) {
			if ( 'activity_user_id' === $index->Key_name ) {
				$index_exists = true;
				break;
			}
		}
		$this->assertTrue( $index_exists );
	}

	/**
	 * @covers AP_Activate::reputation_events_table
	 */
	public function testReputationEventsTable() {
		global $wpdb;

		// Call the reputation_events_table method.
		$ap_activate = \AP_Activate::get_instance();
		$ap_activate->reputation_events_table();

		// Test begins.
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->ap_reputation_events}'" ) == $wpdb->ap_reputation_events;
		$this->assertTrue( $table_exists );

		// Test if the table has the expected columns.
		$columns = $wpdb->get_col( "DESCRIBE {$wpdb->ap_reputation_events}" );
		$expected_columns = [ 'rep_events_id', 'slug', 'icon', 'label', 'description', 'activity', 'parent', 'points' ];
		foreach ( $expected_columns as $column ) {
			$this->assertContains( $column, $columns );
		}

		// Test if the table has the expected primary key.
		$primary_key = null;
		$columns_info = $wpdb->get_results( "DESCRIBE {$wpdb->ap_reputation_events}" );
		foreach ( $columns_info as $column ) {
			if ( 'PRI' === $column->Key ) {
				$primary_key = $column->Field;
				break;
			}
		}
		$this->assertEquals( 'rep_events_id', $primary_key );

		// Test if the table has the expected index.
		$index_exists = false;
		$indexes_info = $wpdb->get_results( "SHOW INDEX FROM {$wpdb->ap_reputation_events}" );
		foreach ( $indexes_info as $index ) {
			if ( 'points_key' === $index->Key_name ) {
				$index_exists = true;
				break;
			}
		}
		$this->assertTrue( $index_exists );
		$index_exists = false;
		foreach ( $indexes_info as $index ) {
			if ( 'parent_key' === $index->Key_name ) {
				$index_exists = true;
				break;
			}
		}
		$this->assertTrue( $index_exists );
	}
}
