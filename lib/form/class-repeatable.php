<?php
/**
 * AnsPress repeatable field object.
 *
 * @package    AnsPress
 * @subpackage Fields
 * @since      4.1.0
 * @author     Rahul Aryan<support@anspress.net>
 * @copyright  Copyright (c) 2017, Rahul Aryan
 * @license    http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace AnsPress\Form\Field;

use AnsPress\Form as Form;
use AnsPress\Form\Field as Field;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Repeatable field.
 *
 * @since 4.1.0
 */
class Repeatable extends Field {
	/**
	 * Field type.
	 *
	 * @var string
	 */
	public $type = 'repeatable';

	/**
	 * Total repeatable groups.
	 *
	 * @var integer
	 */
	public $total_items = 0;

	/**
	 * Base fields used for repeatable groups.
	 *
	 * @var array
	 */
	public $main_fields = [];

	/**
	 * Prepare field.
	 *
	 * @return void
	 */
	protected function prepare() {
		$this->args = wp_parse_args(
			$this->args, array(
				'label' => __( 'AnsPress Repeatable Field', 'anspress-question-answer' ),
			)
		);

		$this->main_fields = $this->args['fields'];
		unset( $this->args['fields'] );

		$value_count       = ! empty( $this->value() ) ? count( $this->value() ) : $this->get_groups_count() + 1;
		$this->total_items = $value_count > 0 ? $value_count : 1;

		$new_fields = [];

		$i = 0;
		while ( $this->total_items > $i ) {
			$i++;

			$this->args['fields'][ $i ] = array(
				'label'         => $this->get( 'label' ) . ' #' . number_format_i18n( $i ),
				'fields'        => $this->main_fields,
				'type'          => 'group',
				'delete_button' => true,
			);
		}

		$this->child = new Form( $this->form_name, $this->args );
		$this->child->prepare();

		// Call parent prepare().
		parent::prepare();

		// Make sure all text field are sanitized.
		$this->sanitize_cb = array_merge( [ 'array_remove_empty' ], $this->sanitize_cb );
	}

	/**
	 * Get POST (unsafe) value of a field.
	 *
	 * @return null|mixed
	 */
	public function unsafe_value() {
		$request_value = $this->get( ap_to_dot_notation( $this->field_name ), null, $_REQUEST );

		if ( $request_value ) {
			$value = array_filter( wp_unslash( $request_value ) );

			foreach ( $value as $k => $val ) {
				if ( empty( array_filter( $val ) ) ) {
					unset( $value[ $k ] );
				}
			}

			return $value;
		}
	}

	/**
	 * Return last repeatable group.
	 *
	 * @return null|object Returns @see `AP_Field` object.
	 */
	public function get_last_field() {
		if ( ! empty( $this->child ) && ! empty( $this->child->fields ) ) {
			return $this->child->fields[ $this->total_items ];
		}
	}

	/**
	 * Html order for a field.
	 *
	 * @return void
	 */
	protected function html_order() {
		$this->output_order = [ 'wrapper_start', 'label', 'field_wrap_start', 'desc', 'errors', 'field_markup', 'field_wrap_end', 'wrapper_end' ];
	}

	/**
	 * Get group count when requesting group from ajax.
	 *
	 * @return null|object Returns @see `AP_Field` object.
	 */
	public function get_groups_count() {
		$current_groups = $this->get( sanitize_title( $this->field_name ) . '-g', null, $_REQUEST );
		$nonce          = $this->get( sanitize_title( $this->field_name ) . '-n', null, $_REQUEST );

		if ( wp_verify_nonce( $nonce, $this->field_name . $current_groups ) ) {
			return $current_groups;
		}
	}

	/**
	 * Field markup.
	 *
	 * @return void
	 */
	public function field_markup() {
		parent::field_markup();

		$this->add_html( '<div class="ap-fieldrepeatable-c" data-role="ap-repeatable" data-args="">' );
		$this->add_html( $this->child->generate_fields() );

		$add_button_args = wp_json_encode(
			array(
				'action'     => 'ap_repeatable_field',
				'form_name'  => $this->form_name,
				'field_name' => $this->field_name,
				'field_id'   => sanitize_title( $this->field_name ),
				'__nonce'    => wp_create_nonce( 'repeatable-field' ),
			)
		);

		$this->add_html( '<a class="ap-btn ap-repeatable-add" href="#" apquery="' . esc_js( $add_button_args ) . '">' . sprintf( __( 'Add More %s', 'anspress-question-answer' ), $this->get( 'label' ) ) . '</a>' );

		$this->add_html( '<input name="' . sanitize_title( $this->field_name ) . '-groups" value="' . $this->total_items . '" type="hidden" />' );

		$this->add_html( '<input name="' . sanitize_title( $this->field_name ) . '-nonce" value="' . wp_create_nonce( $this->field_name . $this->total_items ) . '" type="hidden" />' );

		$this->add_html( '</div>' );

		/** This action is documented in lib/form/class-input.php */
		do_action_ref_array( 'ap_after_field_markup', [ &$this ] );
	}

}
