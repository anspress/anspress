<?php
/**
 * AnsPress Group type field object.
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
 * AnsPress Group type field object.
 *
 * @since 4.1.0
 */
class Group extends Field {
	/**
	 * The field type.
	 *
	 * @var string
	 */
	public $type = 'group';

	/**
	 * The child fields.
	 *
	 * @var array
	 */
	public $child = [];

	/**
	 * Prepare field.
	 *
	 * @return void
	 */
	protected function prepare() {
		$this->args = wp_parse_args(
			$this->args, array(
				'label'         => __( 'AnsPress Group Field', 'anspress-question-answer' ),
				'toggleable'    => false,
				'delete_button' => false, // Used for repeatable fields.
			)
		);

		$this->child = new Form( $this->form_name, $this->args );
		$this->child->prepare();

		// Call parent prepare().
		parent::prepare();
	}

	/**
	 * Order of HTML markup.
	 *
	 * @return void
	 */
	protected function html_order() {
		$this->output_order = [ 'wrapper_start', 'label', 'field_wrap_start', 'desc', 'errors', 'field_markup', 'field_wrap_end', 'wrapper_end' ];
	}

	/**
	 * Output label of field.
	 *
	 * @return void
	 */
	public function label() {
		$this->add_html( '<label class="ap-form-label" for="' . sanitize_html_class( $this->field_name ) . '">' . esc_html( $this->get( 'label' ) ) );

		// Shows delete button for repeatable fields.
		if ( true === $this->get( 'delete_button', false ) ) {
			$this->add_html( '<button class="ap-btn ap-repeatable-delete">' . __( 'Delete', 'anspress-question-answer' ) . '</button>' );
		}

		$this->add_html( '</label>' );
	}

	/**
	 * Field markup.
	 *
	 * @return void
	 */
	public function field_markup() {
		parent::field_markup();

		$checked = true;

		if ( $this->get( 'toggleable' ) ) {
			// Show toggle group if child fields have errors.
			$value   = $this->have_errors() ? 1 : ! empty( array_filter( (array) $this->value() ) );
			$checked = checked( $value, 1, false );

			$this->add_html( '<label for="' . sanitize_html_class( $this->field_name ) . '"><input' . $this->common_attr() . ' ' . $checked . ' type="checkbox" value="1" onchange="AnsPress.Helper.toggleNextClass(this);" />' . esc_html( $this->get( 'toggleable.label', $this->get( 'label' ) ) ) );
			$this->add_html( '</label>' );
		}

		$this->add_html( '<div class="ap-fieldgroup-c' . ( $checked ? ' show' : '' ) . '">' );
		$this->add_html( $this->child->generate_fields() );
		$this->add_html( '</div>' );

		/** This action is documented in lib/form/class-input.php */
		do_action_ref_array( 'ap_after_field_markup', [ &$this ] );
	}

}
