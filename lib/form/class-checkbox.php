<?php
/**
 * AnsPress Checkbox type field object.
 *
 * @package    AnsPress
 * @subpackage Fields
 * @since      4.1.0
 * @author     Rahul Aryan<support@anspress.net>
 * @copyright  Copyright (c) 2017, Rahul Aryan
 * @license    http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace AnsPress\Form\Field;

use AnsPress\Form\Field as Field;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Checkbox type field object.
 *
 * @since 4.1.0
 */
class Checkbox extends Field {
	/**
	 * The field type.
	 *
	 * @var string
	 */
	public $type = 'checkbox';

	/**
	 * Prepare field.
	 *
	 * @return void
	 */
	protected function prepare() {
		$this->args = wp_parse_args(
			$this->args, array(
				'label' => __( 'AnsPress Checkbox Field', 'anspress-question-answer' ),
			)
		);

		// Call parent prepare().
		parent::prepare();

		// Make sure checkbox value are sanitized.
		if ( $this->get( 'options' ) ) {
			$this->sanitize_cb = array_merge( [ 'array_remove_empty', 'text_field' ], $this->sanitize_cb );
		} else {
			$this->sanitize_cb = array_merge( [ 'boolean' ], $this->sanitize_cb );
		}
	}

	/**
	 * Order of HTML markup.
	 *
	 * @return void
	 */
	protected function html_order() {
		parent::html_order();

		if ( ! $this->get( 'options' ) ) {
			$this->output_order = [ 'wrapper_start', 'label', 'field_wrap_start', 'errors', 'field_markup', 'field_wrap_end', 'wrapper_end' ];
		}
	}

	/**
	 * Field markup.
	 *
	 * @return void
	 */
	public function field_markup() {
		parent::field_markup();

		if ( $this->get( 'options' ) ) {
			$value = $this->value();

			foreach ( $this->get( 'options' ) as $val => $label ) {
				$checked = checked( isset( $value[ $val ] ), 1, false );
				$this->add_html( '<label>' );
				$this->add_html( '<input type="checkbox" value="1" name="' . esc_attr( $this->field_name ) . '[' . $val . ']" id="' . sanitize_html_class( $this->field_name . $val ) . '" class="ap-form-control" ' . $checked . $this->custom_attr() . '/>' );
				$this->add_html( $label . '</label>' );
			}
		} else {
			$checked = checked( $this->value(), 1, false );
			$this->add_html( '<label>' );
			$this->add_html( '<input type="checkbox" value="1" ' . $checked . $this->common_attr() . $this->custom_attr() . '/>' );
			$this->add_html( $this->get( 'desc' ) . '</label>' );
		}

		/** This action is documented in lib/form/class-input.php */
		do_action_ref_array( 'ap_after_field_markup', [ &$this ] );
	}

	/**
	 * Get POST (unsafe) value of a field.
	 *
	 * @return null|mixed
	 * @since 4.1.8 Return `false` for unchecked checkbox.
	 */
	public function unsafe_value() {
		$request_value = $this->get( ap_to_dot_notation( $this->field_name ), null, $_REQUEST );

		if ( isset( $request_value ) ) {
			return wp_unslash( $request_value );
		}

		// Return `false` if form submitted but is not set.
		if ( $this->form()->is_submitted() ) {
			return false;
		}
	}

}
