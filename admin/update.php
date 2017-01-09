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
		if ( $init ) {
			$active = '';

			foreach ( $this->get_tasks() as $slug => $status ) {
				if ( ! $status ) {
					$this->send( true, $slug, '' );
				}
			}
		}
		$this->check_tables();
		$this->migrate_votes();
		$this->answers_count();
		$this->migrate_views();
		$this->migrate_reputations();
		$this->best_answers();
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
			'votes'         => false,
			'answers_count' => false,
			'views_count'   => false,
			'reputations'   => false,
			'best_answers'  => false,
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
		ap_ajax_json( array(
			'success' => $success ? true: false,
			'active'  => $active,
			'message' => $message,
			'status'  => $this->get_tasks(),
			'continue'  => $continue,
		) );
	}

	/**
	 * Migrate votes data from `ap_meta` table to `ap_votes` table.
	 *
	 * @since 4.0.0
	 */
	public function migrate_votes() {
		$tasks = $this->get_tasks();

		if ( $tasks['votes'] ) {
			return;
		}

		global $wpdb;
		$old_votes = $wpdb->get_results( "SELECT SQL_CALC_FOUND_ROWS * FROM {$wpdb->prefix}ap_meta WHERE apmeta_type IN ('vote_up', 'vote_down') LIMIT 50" ); // DB call okay, Db cache okay.

		$total_votes = $wpdb->get_var( 'SELECT FOUND_ROWS()' ); // DB call okay, Db cache okay.
		$fetched = $wpdb->num_rows;

		if ( empty( $old_votes ) ) {
			$options = get_option( 'anspress_updates', [] );
			$options['votes'] = true;
			update_option( 'anspress_updates', $options );
			$this->send( true, 'votes', __( 'Successfully migrated all votes', 'anspress-question-answer' ), true );
		}

		$apmeta_to_delete = [];
		foreach ( (array) $old_votes as $vote ) {
			ap_add_post_vote( $vote->apmeta_actionid, $vote->apmeta_userid, 'vote_up' === $vote->apmeta_type );
			$apmeta_to_delete[] = $vote->apmeta_id;

			// Delete post meta.
			delete_post_meta( $vote->apmeta_actionid, '_ap_vote' );
		}

		// Delete all migrated data.
		$apmeta_to_delete = sanitize_comma_delimited( $apmeta_to_delete, 'int' );
		$wpdb->query( "DELETE FROM {$wpdb->prefix}ap_meta WHERE apmeta_id IN ({$apmeta_to_delete})" ); // DB call okay, Db cache okay.

		$this->send( true, 'votes', sprintf( __( 'Migrating votes... %1$d out of %2$d', 'anspress-question-answer' ), $fetched, $total_votes ), true );
	}

	/**
	 * Re-count answers.
	 */
	public function answers_count() {
		$tasks = $this->get_tasks();

		if ( $tasks['answers_count'] ) {
			return;
		}

		global $wpdb;
		$done = (int) get_option( 'anspress_updated_q_offset', 0 );
		$ids = $wpdb->get_col( "SELECT SQL_CALC_FOUND_ROWS ID FROM {$wpdb->posts} WHERE post_type='question' LIMIT {$done},50" );
		$total_ids = $wpdb->get_var( 'SELECT FOUND_ROWS()' ); // DB call okay, Db cache okay.

		if ( empty( $ids ) ) {
			$options = get_option( 'anspress_updates', [] );
			$options['answers_count'] = true;
			update_option( 'anspress_updates', $options );
			$this->send( true, 'answers_count', __( 'Answers count updated', 'anspress-question-answer' ), true );
		}

		// Update answers count.
		foreach ( (array) $ids as $id ) {
			ap_update_answers_count( $id );

			// Delete post meta.
			delete_post_meta( $id, '_ap_answers' );
		}

		$done = $done + count( $ids );
		update_option( 'anspress_updated_q_offset', $done );
		$this->send( true, 'answers_count', sprintf( __( 'Updated answers count... %1$d out of %2$d', 'anspress-question-answer' ), count( $done ), $total_ids ), true );
	}

	/**
	 * Migrate views data to new table.
	 */
	public function migrate_views() {
		$tasks = $this->get_tasks();

		if ( $tasks['views_count'] ) {
			return;
		}

		global $wpdb;
		$old_views = $wpdb->get_results( "SELECT SQL_CALC_FOUND_ROWS * FROM {$wpdb->prefix}ap_meta WHERE apmeta_type = 'post_view' LIMIT 50" ); // DB call okay, Db cache okay.

		$total_views = $wpdb->get_var( 'SELECT FOUND_ROWS()' ); // DB call okay, Db cache okay.
		$fetched = $wpdb->num_rows;

		if ( empty( $old_votes ) ) {
			$options = get_option( 'anspress_updates', [] );
			$options['views_count'] = true;
			update_option( 'anspress_updates', $options );
			$this->send( true, 'views_count', __( 'Successfully migrated all views', 'anspress-question-answer' ), true );
		}

		$apmeta_to_delete = [];
		foreach ( (array) $old_views as $view ) {
			ap_insert_views( $view->apmeta_actionid, 'question', $view->apmeta_userid, $view->apmeta_value );
			$apmeta_to_delete[] = $vote->apmeta_id;

			$views = (int) get_post_meta( $vote->apmeta_actionid, '_views', true );
			ap_update_views_count( $vote->apmeta_actionid, $views );

			// Delete post meta.
			delete_post_meta( $vote->apmeta_actionid, '_views' );
		}

		// Delete all migrated data.
		$apmeta_to_delete = sanitize_comma_delimited( $apmeta_to_delete, 'int' );
		$wpdb->query( "DELETE FROM {$wpdb->prefix}ap_meta WHERE apmeta_id IN ({$apmeta_to_delete})" ); // DB call okay, Db cache okay.

		$this->send( true, 'votes', sprintf( __( 'Migrated views... %1$d out of %2$d', 'anspress-question-answer' ), $fetched, $total_views ), true );
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
	 * Update best answer meta.
	 */
	public function best_answers() {
		$tasks = $this->get_tasks();

		if ( $tasks['best_answers'] ) {
			return;
		}

		global $wpdb;
		$old = $wpdb->get_results( "SELECT SQL_CALC_FOUND_ROWS * FROM {$wpdb->prefix}postmeta WHERE meta_key = '_ap_best_answer' LIMIT 50" ); // DB call okay, Db cache okay.

		$total = $wpdb->get_var( 'SELECT FOUND_ROWS()' ); // DB call okay, Db cache okay.
		$fetched = $wpdb->num_rows;

		if ( empty( $old ) ) {
			$options = get_option( 'anspress_updates', [] );
			$options['best_answers'] = true;
			update_option( 'anspress_updates', $options );
			$this->send( true, 'reputations', __( 'Successfully updated best answers', 'anspress-question-answer' ), true );
		}

		foreach ( (array) $old as $meta ) {
			ap_set_selected_answer( $meta->post_id, $meta->meta_value );
			delete_post_meta( $meta->post_id, '_ap_best_answer' );
			delete_post_meta( $meta->meta_value, '_ap_selected' );
		}

		$this->send( true, 'best_answers', sprintf( __( 'Updated best answers... %1$d out of %2$d', 'anspress-question-answer' ), $fetched, $total ), true );
	}
}
