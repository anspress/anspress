<?php
/**
 * AnsPress Radio type field object.
 *
 * @package    AnsPress
 * @subpackage Fields
 * @since      4.1.0
 * @author     Rahul Aryan<support@anspress.io>
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
 * The Radio type field object.
 *
 * @since 4.1.0
 */
class Radio extends Field {
	/**
	 * The field type.
	 *
	 * @var string
	 */
	public $type = 'radio';

	/**
	 * Prepare field.
	 *
	 * @return void
	 */
	protected function prepare() {
		$this->args = wp_parse_args( $this->args, array(
			'label'   => __( 'AnsPress Radio Field', 'anspress-question-answer' ),
		) );

		// Call parent prepare().
		parent::prepare();

		// Make sure checkbox value are sanitized.
		if ( $this->get( 'options' ) ) {
			$this->sanitize_cb = array_merge( [ 'array_remove_empty,text_field' ], $this->sanitize_cb );
		} else {
			$this->sanitize_cb = array_merge( [ 'text_field' ], $this->sanitize_cb );
		}
	}

	/**
	 * Field markup.
	 *
	 * @return void
	 */
	public function field_markup() {
		if ( $this->get( 'options' ) ) {
			$value = $this->value();

			foreach ( $this->get( 'options' ) as $val => $label ) {
				$checked = checked( isset( $value[ $val ] ), 1, false );
				$this->add_html( '<label>' );
				$this->add_html( '<input type="radio" value="1" name="' . esc_attr( $this->field_name ) . '[' . $val . ']" id="' . sanitize_html_class( $this->field_name . $val ) . '" class="ap-form-control" ' . $checked . $this->custom_attr() . '/>' );
				$this->add_html( $label . '</label>' );
			}
		} else {
			$checked = checked( $this->value(), 1, false );
			$this->add_html( '<input type="radio" value="1" ' . $checked . $this->common_attr() . $this->custom_attr() . '/>' );
		}
	}

}
