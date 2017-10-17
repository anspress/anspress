<?php
/**
 * AnsPress question object.
 *
 * @package    AnsPress
 * @subpackage Question Class
 * @since      4.1.0
 * @author     Rahul Aryan<support@anspress.io>
 * @copyright  Copyright (c) 2017, Rahul Aryan
 * @license    http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AP_Question class.
 *
 * @since 4.1.0
 */
class AP_Question extends AP_QA {

	/**
	 * Initiate the class.
	 *
	 * @param boolean $_id Question ID.
	 */
	public function __construct( $_id = false ) {

		if ( false === $_id ) {
			$_question = WP_Post::get_instance( $_id );
		} else {
			$_question = ap_get_post( $_id );
		}

		return $this->setup_question( $_question );
	}

	/**
	 * Given the question data, let's set the variables.
	 *
	 * @param  WP_Post $question The WP_Post object for question.
	 * @return bool              If the setup was successful or not
	 */
	private function setup_question( $question ) {
		if ( ! is_object( $question ) || 'question' !== $question->post_type ) {
			return false;
		}

		return parent::setup_post( $question );
	}

	/**
	 * Creates a question.
	 *
	 * This method handles everything required to create and update
	 * a question. Avoid using `wp_insert_post()` function. Also notice
	 * that `save_post` action was triggered very early. Hence terms and qameta
	 * will not be available while using `save_post` hook. Instead use `ap_create_question`.
	 *
	 * @param  array $data Array of attributes for a question.
	 * @return mixed false if data isn't passed and class not instantiated for creation, or new question ID.
	 */
	public function save() {

		$this->set( 'last_updated', current_time( 'mysql' ) );
		$activity_type = $this->ID ? 'edit_question' : 'new_question';
		$this->set_activity( $activity_type, $this->unsaved_post_fields['post_author'] );

		$ret = parent::save();

		// Return if error.
		if ( is_wp_error( $ret ) ) {
			return $ret;
		}

		/**
		 * Fired after a question is created
		 *
		 * @param AP_Question  $question AP_Question object, passed by reference.
		 * @param array        $data     The post object arguments used for creation.
		 */
		do_action_ref_array( 'ap_save_question', [ &$this ] );

		return $ret;
	}

	public function set_activity( $type, $user_id = false, $date = false ) {
		if ( false === $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( false === $date ) {
			$date = current_time( 'mysql' );
		}

		$meta_val = compact( 'type', 'user_id', 'date' );
		$this->set( 'activities',  $meta_val );
		$this->set( 'last_updated', current_time( 'mysql' ) );
	}

	public function get_answers_count() {
		return (int) $this->answers;
	}

	/**
	 * Return question display meta.
	 *
	 * @return array
	 */
	public function get_display_meta() {
		$metas = [];

		// If featured question.
		if ( $this->featured ) {
			$metas['featured'] = array(
				'title' => __( 'Featured', 'anspress-question-answer' ),
			);
		}

		if ( $this->selected_id ) {
			$metas['solved'] = array(
				'text' => __( 'Solved', 'anspress-question-answer' ),
				'icon' => 'apicon-check',
			);
		}

		$metas['views'] = array(
			// Translators: placeholder is count of views.
			'text' => sprintf( __( '%d views', 'anspress-question-answer' ), $this->views ),
			'icon' => 'apicon-eye',
		);

		if ( is_question() ) {
			$last_active = ! empty( $this->last_updated ) ? $this->last_updated : $this->post_modified_gmt;
			$metas['active'] = array(
				'text' => ap_human_time( get_gmt_from_date( $last_active ), false ),
				'icon' => 'apicon-pulse',
				'date' => $last_active,
			);
		}

		if ( ! is_question() ) {
			$metas['history'] = array(
				'text' => ap_latest_post_activity_html( $this->ID, ! is_question() ),
				'icon' => 'apicon-pulse',
			);
		}

		/**
		 * Filter display meta of a question.
		 *
		 * @param array  $metas Display metas.
		 * @param object $ap_qa AP_QA object passed by reference.
		 * @since 4.1.0
		 */
		$metas = apply_filters_ref_array( 'ap_question_display_meta', [ $metas, $this ] );
		$metas = ap_sort_array_by_order( $metas );

		return $metas;
	}


}
