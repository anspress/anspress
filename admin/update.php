<?php
/**
 * Update related funtion.
 * @link 	https://anspress.io/
 * @since   2.4
 * @author  Rahul Aryan <support@anspress.io>
 * @package AnsPress
 */

class AP_Update_Helper
{
	/**
	 * Move subscribers from ap_meta table to ap_subscribers table.
	 * @since 2.4
	 */
	public function move_subscribers() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $wpdb;
		$count = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}ap_meta WHERE apmeta_type = 'subscriber' " );

		$i = 1;
		while ( $count >= $i ) {
		 	$subscribe = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}ap_meta WHERE apmeta_type = 'subscriber' LIMIT 0,100" );
		 	if ( $subscribe ) {
		 		$ids_to_remove = array();
		 		foreach ( $subscribe as $s ) {
		 			$type = 'q_all';
		 			$question_id = 0;
		 			$ids_to_remove[] = $s->apmeta_id;
		 			if ( $s->apmeta_param == '' ) {
		 				$question_id = $s->apmeta_actionid;
		 			}

		 			if ( $s->apmeta_param == 'tag' || $s->apmeta_param == 'category' ) {
		 				$type = 'tax_new_q';
		 			}
		 			ap_new_subscriber( $s->apmeta_userid, $s->apmeta_actionid, $type, $question_id );
		 		}

		 		if ( ! empty( $ids_to_remove ) ) {
		 			$ids_to_remove = implode( ',', $ids_to_remove );
		 			$wpdb->query( "DELETE FROM {$wpdb->prefix}ap_meta WHERE find_in_set(apmeta_id, '$ids_to_remove') " );
		 		}
		 	}

		 	$i = $i + 100;
		}
		$count = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}ap_meta WHERE apmeta_type = 'subscriber' " );

		if ( $count < 1 ) {
			update_option( 'ap_subscribers_moved', true );			
		}
	}
}
