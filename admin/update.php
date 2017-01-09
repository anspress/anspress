<?php
/**
 * Update related funtion.
 * @link 	https://anspress.io/
 * @since   2.4
 * @author  Rahul Aryan <support@anspress.io>
 * @package AnsPress
 */

class AP_Update_Helper {

	/**
	 * Init class.
	 */
	public function __construct( $init = false) {
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
		}

		$done = $done + count( $ids );
		update_option( 'anspress_updated_q_offset', $done );
		$this->send( true, 'answers_count', sprintf( __( 'Updated answers count... %1$d out of %2$d', 'anspress-question-answer' ), count( $done ), $total_ids ), true );
	}
}
