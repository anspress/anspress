<?php
/**
 * Update related funtion.
 *
 * @link 	https://anspress.io/
 * @since   4.0.0
 * @author  Rahul Aryan <support@anspress.io>
 * @package AnsPress
 */

class AP_Update_Helper {

	/**
	 * Init class.
	 *
	 * @param boolean $init Should send initial response.
	 */
	public function __construct( $init = false ) {
		if ( ! get_option( 'ap_update_helper', false ) ) {
			return;
		}

		// Disable sending email while upgrading.
		define( 'AP_DISABLE_EMAIL', true );

		// Also disable inserting of reputations and notifications.
		define( 'AP_DISABLE_INSERT_NOTI', true );
		define( 'AP_DISABLE_INSERT_REP', true );

		if ( $init ) {
			$active = '';

			foreach ( $this->get_tasks() as $slug => $status ) {
				if ( ! $status ) {
					$this->send( true, $slug, '' );
				}
			}
		}
		$this->check_tables();
		$this->migrate_post_data();
		$this->migrate_reputations();
		$this->migrate_category_data();
	}

	/**
	 * Check if tables are updated, if not create it first.
	 */
	public function check_tables() {
		if ( get_option( 'anspress_db_version' ) !== AP_DB_VERSION ) {
			$activate = AP_Activate::get_instance();
			$activate->insert_tables();
			update_option( 'anspress_db_version', AP_DB_VERSION );
		}
	}

	/**
	 * Get all completed and uncompleted tasks.
	 *
	 * @return array
	 */
	public function get_tasks() {
		return wp_parse_args( get_option( 'anspress_updates', [] ), [
			'post_data'   => false,
			'reputations' => false,
			'category'    => false,
		] );
	}

	/**
	 * Send ajax response.
	 *
	 * @param boolean $success Is success.
	 * @param string  $active  Active task slug.
	 * @param string  $message Response message.
	 */
	public function send( $success, $active, $message, $continue = false ) {
		$tasks = $this->get_tasks();

		if ( ! in_array( false, $tasks ) ) {
			update_option( 'ap_update_helper', false );
		}

		ap_ajax_json( array(
			'success' => $success ? true: false,
			'active'  => $active,
			'message' => $message,
			'status'  => $this->get_tasks(),
			'continue'  => $continue,
		) );
	}

	public function migrate_post_data() {
		$tasks = $this->get_tasks();

		if ( $tasks['post_data'] ) {
			return;
		}

		global $wpdb;
		$done = (int) get_option( 'anspress_updated_q_offset', 0 );
		$ids = $wpdb->get_results( "SELECT SQL_CALC_FOUND_ROWS ID, post_type, post_parent, post_status FROM {$wpdb->posts} LEFT JOIN {$wpdb->ap_qameta} ON post_id = ID WHERE ptype IS NULL post_type IN ('question', 'answer') LIMIT {$done},50" );
		$total_ids = $wpdb->get_var( 'SELECT FOUND_ROWS()' ); // DB call okay, Db cache okay.

		if ( empty( $ids ) ) {
			$options = get_option( 'anspress_updates', [] );
			$options['post_data'] = true;
			update_option( 'anspress_updates', $options );
			$this->send( true, 'post_data', __( 'Post data updated successfully', 'anspress-question-answer' ), true );
		}

		// Update answers count.
		foreach ( (array) $ids as $_post ) {
			if ( 'question' === $_post->post_type ) {
				$this->update_answers_count( $_post );
				// Delete post meta.
				delete_post_meta( $_post->ID, '_ap_answers' );
				$this->migrate_views( $_post->ID );
				$this->subscribers( $_post->ID );
				$this->change_closed_status( $_post );
				$this->best_answers( $_post );
				ap_update_qameta_terms( $_post->ID );
				delete_post_meta( $_post->ID, '_ap_participants' );
			}

			if ( 'answer' === $_post->post_type ) {
				delete_post_meta( $_post->ID, '_ap_best_answer' );
			}

			$this->flags( $_post->ID );
			$this->migrate_votes( $_post->ID );
			$this->post_activities( $_post );
			$this->restore_last_updated( $_post );
		}

		$done = $done + count( $ids );
		update_option( 'anspress_updated_q_offset', $done );
		$this->send( true, 'post_data', sprintf( __( 'Updated %1$d posts out of %2$d', 'anspress-question-answer' ), count( $done ), $total_ids ), true );
	}

	/**
	 * Change closed post status to publish.
	 *
	 * @param object $_post Post object.
	 */
	public function change_closed_status( $_post ) {
		if ( 'closed' === $_post->post_status ) {
			global $wpdb;

			$wpdb->update( $wpdb->posts, [ 'post_status' => 'publish' ], [ 'ID' => $_post->ID ], [ '%s' ] );
			ap_toggle_close_question( $_post->ID );
		}
	}

	/**
	 * Migrate votes data from `ap_meta` table to `ap_votes` table.
	 *
	 * @param integer $post_id Post ID.
	 * @since 4.0.0
	 */
	public function migrate_votes( $post_id ) {
		if ( ! isset( $wpdb->ap_meta ) ) {
			return;
		}

		global $wpdb;

		$post_id = (int) $post_id;

		$old_votes = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}ap_meta WHERE apmeta_type IN ('vote_up', 'vote_down') AND apmeta_actionid = {$post_id}" ); // @codingStandardsIgnoreLine

		$apmeta_to_delete = [];
		foreach ( (array) $old_votes as $vote ) {
			ap_add_post_vote( $post_id, $vote->apmeta_userid, 'vote_up' === $vote->apmeta_type );
			$apmeta_to_delete[] = $vote->apmeta_id;

			// Delete post meta.
			delete_post_meta( $post_id, '_ap_vote' );
		}

		// Delete all migrated data.
		$apmeta_to_delete = sanitize_comma_delimited( $apmeta_to_delete, 'int' );
		$wpdb->query( "DELETE FROM {$wpdb->prefix}ap_meta WHERE apmeta_id IN ({$apmeta_to_delete})" ); // @codingStandardsIgnoreLine
	}

	/**
	 * Re-count answers.
	 */
	public function update_answers_count( $_post ) {

		// Update answers count.
		ap_update_answers_count( $_post->ID );

		// Delete post meta.
		delete_post_meta( $_post->ID, '_ap_answers' );
	}

	/**
	 * Migrate views data to new table.
	 */
	public function migrate_views( $post_id ) {
		if ( ! isset( $wpdb->ap_meta ) ) {
			return;
		}

		global $wpdb;
		$post_id = (int) $post_id;

		$old_views = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}ap_meta WHERE apmeta_type = 'post_view' AND apmeta_actionid = {$post_id}" ); // DB call okay, Db cache okay.

		$apmeta_to_delete = [];
		foreach ( (array) $old_views as $view ) {
			ap_insert_views( $post_id, 'question', $view->apmeta_userid, $view->apmeta_value );
			$apmeta_to_delete[] = $view->apmeta_id;
		}

		// Delete all migrated data.
		$apmeta_to_delete = sanitize_comma_delimited( $apmeta_to_delete, 'int' );
		$wpdb->query( "DELETE FROM {$wpdb->prefix}ap_meta WHERE apmeta_id IN ({$apmeta_to_delete})" ); // @codingStandardsIgnoreLine

		$views = (int) get_post_meta( $post_id, '_views', true );
		ap_update_views_count( $post_id, $views );

		// Delete post meta.
		delete_post_meta( $post_id, '_views' );
	}

	/**
	 * Update best answer meta.
	 */
	public function best_answers( $_post ) {
		$answer_id = get_post_meta( $_post->ID, '_ap_selected', true );
		ap_set_selected_answer( $_post->ID, $answer_id );
		delete_post_meta( $_post->ID, '_ap_selected' );
	}

	/**
	 * Update subscribers count and delete post meta.
	 *
	 * @param integer $post_id Post ID.
	 */
	public function subscribers( $post_id ) {
		$count = get_post_meta( $post_id, '_ap_subscriber', true );
		ap_update_subscribers_count( $post_id, $count );
		delete_post_meta( $post_id, '_ap_subscriber' );
	}

	/**
	 * Update flags count and delete post meta.
	 *
	 * @param integer $post_id Post ID.
	 */
	public function flags( $post_id ) {
		$count = get_post_meta( $post_id, '_ap_flag', true );
		ap_set_flag_count( $post_id, $count );
		delete_post_meta( $post_id, '_ap_flag' );
	}

	/**
	 * Migrate migration data to new table.
	 */
	public function migrate_reputations() {
		$tasks = $this->get_tasks();

		if ( $tasks['reputations'] ) {
			return;
		}

		global $wpdb;

		if ( ! isset( $wpdb->ap_meta ) ) {
			$options = get_option( 'anspress_updates', [] );
			$options['reputations'] = true;
			update_option( 'anspress_updates', $options );
			$this->send( true, 'reputations', __( 'Successfully migrated all reputations', 'anspress-question-answer' ), true );
		}

		global $wpdb;
		$old_reputations = $wpdb->get_results( "SELECT SQL_CALC_FOUND_ROWS * FROM {$wpdb->prefix}ap_meta WHERE apmeta_type = 'reputation' LIMIT 50" ); // DB call okay, Db cache okay.

		$total_reputations = $wpdb->get_var( 'SELECT FOUND_ROWS()' ); // DB call okay, Db cache okay.
		$fetched = $wpdb->num_rows;

		if ( empty( $old_reputations ) ) {
			$options = get_option( 'anspress_updates', [] );
			$options['reputations'] = true;
			update_option( 'anspress_updates', $options );
			$this->send( true, 'reputations', __( 'Successfully migrated all reputations', 'anspress-question-answer' ), true );
		}

		$apmeta_to_delete = [];
		foreach ( (array) $old_reputations as $rep ) {

			switch ( $rep->apmeta_param ) {
				case 'new_question' :
				case 'question' :
					$event = 'ask';

				case 'new_answer' :
				case 'answer' :
					$event = 'answer';

				case 'new_comment' :
					$event = 'comment';

				case 'vote_up' :
				case 'question_upvote' :
				case 'answer_upvote' :
					$event = 'received_vote_up';

				case 'vote_down' :
				case 'question_downvote' :
				case 'answer_downvote' :
					$event = 'received_vote_down';

				case 'voted_up' :
				case 'question_upvoted' :
				case 'answer_upvoted' :
					$event = 'given_vote_up';

				case 'voted_down' :
				case 'question_downvoted' :
				case 'answer_downvoted' :
					$event = 'given_vote_down';

				case 'selecting_answer' :
					$event = 'select_answer';

				case 'select_answer' :
					$event = 'best_answer';

				default:
					$event = $rep->apmeta_param;
			}

			ap_insert_reputation( $event, $rep->apmeta_actionid, $rep->apmeta_userid );
			$apmeta_to_delete[] = $rep->apmeta_id;

			// Delete user meta.
			delete_user_meta( $rep->apmeta_userid, 'ap_reputation' );
		}

		// Delete all migrated data.
		$apmeta_to_delete = sanitize_comma_delimited( $apmeta_to_delete, 'int' );
		$wpdb->query( "DELETE FROM {$wpdb->prefix}ap_meta WHERE apmeta_id IN ({$apmeta_to_delete})" ); // DB call okay, Db cache okay.

		$this->send( true, 'reputations', sprintf( __( 'Migrated reputation... %1$d out of %2$d', 'anspress-question-answer' ), $fetched, $total_reputations ), true );
	}

	/**
	 * Update post activities meta.
	 */
	public function post_activities( $_post ) {
		global $wpdb;
		$activity = maybe_serialize( get_post_meta( $_post->ID, '__ap_activity', true ) );
		delete_post_meta( $_post->ID, '__ap_activity' );

		$wpdb->update( $wpdb->ap_qameta, [ 'activities' => $activity ], [ 'post_id' => $_post->ID ], [ '%s' ] );
	}

	/**
	 * Restore last_updated date of question and answer.
	 */
	public function restore_last_updated( $_post ) {
		global $wpdb;
		$last_updated = get_post_meta( $_post->ID, '_ap_updated', true );
		$wpdb->update( $wpdb->ap_qameta, [ 'last_updated' => $last_updated ], [ 'post_id' => $_post->ID ], [ '%s' ] ); // @codingStandardsIgnoreLine
		delete_post_meta( $_post->ID, '_ap_updated' );
	}

	/**
	 * Migrate old category options from option table to term meta table.
	 */
	public function migrate_category_data() {
		$tasks = $this->get_tasks();

		if ( $tasks['category'] ) {
			return;
		}

		global $wpdb;
		$terms = $wpdb->get_results( "SELECT SQL_CALC_FOUND_ROWS t.*, tt.* FROM wp_terms AS t INNER JOIN wp_term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('question_category') ORDER BY t.name ASC" ); // @codingStandardsIgnoreLine.

		$total_ids = $wpdb->get_var( 'SELECT FOUND_ROWS()' ); // DB call okay, Db cache okay.

		foreach ( (array) $terms as $term ) {
			$term_meta = get_option( 'ap_cat_' . $term->term_id );

			if ( isset( $term_meta['ap_image'] ) ) {
				$term_meta['image'] = $term_meta['ap_image'];
				unset( $term_meta['ap_image'] );
			}

			if ( isset( $term_meta['ap_icon'] ) ) {
				$term_meta['icon'] = $term_meta['ap_icon'];
				unset( $term_meta['ap_icon'] );
			}

			if ( isset( $term_meta['ap_color'] ) ) {
				$term_meta['color'] = $term_meta['ap_color'];
				unset( $term_meta['ap_color'] );
			}

			update_term_meta( $term->term_id, 'ap_category', $term_meta );
			delete_option( 'ap_cat_' . $term->term_id );
		}

		$options = get_option( 'anspress_updates', [] );
		$options['category'] = true;
		update_option( 'anspress_updates', $options );

		$this->send( true, 'category', sprintf( __( 'Migrated categories data... %1$d out of %2$d', 'anspress-question-answer' ), count( $terms ), $total_ids ), true );
	}
}
