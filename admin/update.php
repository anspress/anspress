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

	public function migrate_votes() {
		$tasks = $this->get_tasks();

		if ( $tasks['votes'] ) {
			return;
		}

		global $wpdb;
		$old_votes = $wpdb->get_results( "SELECT SQL_CALC_FOUND_ROWS * FROM {$wpdb->prefix}ap_meta WHERE apmeta_type IN ('vote_up', 'vote_down') LIMIT 50" );

		$total_votes = $wpdb->get_var( "SELECT FOUND_ROWS()" );
		$fetched = $wpdb->num_rows;

		if ( empty( $old_votes ) ) {
			$options = get_option( 'anspress_updates', [] );
			$options['votes'] = true;
			update_option( 'anspress_updates', $options );
		}

		$apmeta_to_delete = [];
		foreach ( (array) $old_votes as $vote ) {
			ap_add_post_vote( $vote->apmeta_actionid, $vote->apmeta_userid, 'vote_up' === $vote->apmeta_type );
			$apmeta_to_delete[] = $vote->apmeta_id;
		}

		$apmeta_to_delete = sanitize_comma_delimited( $apmeta_to_delete, 'int' );
		$wpdb->query( "DELETE FROM {$wpdb->prefix}ap_meta WHERE apmeta_id IN ({$apmeta_to_delete})" );

		$this->send( true, 'votes', sprintf( __( 'Migrating votes... %1$d out of %2$d', 'anspress-question-answer' ), $fetched, $total_votes ), true );
	}

	public function answers_count(){
		$this->send( true, 'answers_count', sprintf( __( 'Updating votes count... %1$d out of %2$d', 'anspress-question-answer' ), 10, 100 ), true );
	}
}
