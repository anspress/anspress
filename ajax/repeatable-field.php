<?php
/**
 * Class used for ajax callback `ap_get_repeatable_field`.
 * This class is auto loaded by AnsPress loader on demand.
 *
 * @author Rahul Aryan <rah12@live.com>
 * @package AnsPress
 * @subpackage Ajax
 * @since 4.1.8
 */

namespace AnsPress\Ajax;

// Die if called directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The `ap_get_repeatable_field` ajax callback.
 *
 * @since 4.1.8
 */
class Repeatable_Field extends \AnsPress\Classes\Ajax {
	/**
	 * Instance of this class.
	 */
	static $instance;

	/**
	 * The class constructor.
	 *
	 * Set requests and nonce key.
	 */
	protected function __construct() {
		$this->req( 'form_name', ap_sanitize_unslash( 'form_name', 'r' ) );
		$this->req( 'field_name', ap_sanitize_unslash( 'field_name', 'r' ) );
		$this->req( 'current_groups', ap_sanitize_unslash( 'current_groups', 'r' ) );

		$this->nonce_key = 'repeatable-field';

		// Call parent.
		parent::__construct();
	}

	/**
	 * Verify user permission.
	 *
	 * @return void
	 */
	protected function verify_permission() {
		$form_name = $this->req( 'form_name' );

		if ( empty( $form_name ) ) {
			parent::verify_permission();
		}
	}

	/**
	 * Handle ajax for logged in users.
	 *
	 * @return void
	 */
	public function logged_in() {
		$field_name     = $this->req( 'field_name' );
		$current_groups = $this->req( 'current_groups' );

		$_REQUEST[ $field_name . '-g' ] = $current_groups;
		$_REQUEST[ $field_name . '-n' ] = ap_sanitize_unslash( 'current_nonce', 'r' );

		$form  = anspress()->get_form( 'question' );
		$field = $form->find( $field_name, false, 'field_name' );

		if ( ! empty( $field ) && is_object( $field ) ) {
			if ( $field->get_last_field() ) {
				$this->set_success();

				$this->add_res( 'html', $field->get_last_field()->output() );
				$this->add_res( 'nonce', wp_create_nonce( $field_name . ( $current_groups + 1 ) ) );
			}
		}

	}
}
