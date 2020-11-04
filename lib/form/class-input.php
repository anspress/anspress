<?php
/**
 * AnsPress Input type field object.
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
 * The Input type field object.
 *
 * Valid sub type values are: text, hidden, number, email,
 * password, datetime-local, color, url.
 *
 * @since 4.1.0
 */
class Input extends Field {
	/**
	 * The field type.
	 *
	 * @var string
	 */
	public $type = 'input';

	/**
	 * Sub type of input field.
	 *
	 * @var string
	 */
	public $subtype = 'text';

	/**
	 * Prepare field.
	 *
	 * @return void
	 */
	protected function prepare() {
		$this->args = wp_parse_args(
			$this->args, array(
				'subtype' => 'text',
				'label'   => __( 'AnsPress Input Field', 'anspress-question-answer' ),
			)
		);

		// Remove label if custom html.
		if ( ! empty( $this->args['html'] ) ) {
			unset( $this->args['label'] );
		}

		$this->set_subtype();

		// Call parent prepare().
		parent::prepare();

		$sanitize_subtype = array(
			'number' => 'intval',
			'email'  => 'email',
			'url'    => 'esc_url',
		);

		// Make sure all text field are sanitized.
		if ( in_array( $this->subtype, array_keys( $sanitize_subtype ), true ) ) {
			$this->sanitize_cb = array_merge( [ $sanitize_subtype[ $this->subtype ] ], $this->sanitize_cb );
		} else {
			$this->sanitize_cb = array_merge( [ 'text_field' ], $this->sanitize_cb );
		}

		$validate_subtype = array(
			'number' => 'is_numeric',
			'email'  => 'is_email',
			'url'    => 'is_url',
		);

		if ( in_array( $this->subtype, array_keys( $validate_subtype ), true ) ) {
			$this->validate_cb = array_merge( [ $validate_subtype[ $this->subtype ] ], $this->validate_cb );
		}
	}

	/**
	 * Check and set sub type of a field.
	 *
	 * @return void
	 * @since 4.1.8 Added date type.
	 */
	private function set_subtype() {
		$allowed_subtype = array(
			'text',
			'hidden',
			'number',
			'email',
			'password',
			'date',
			'datetime-local',
			'color',
			'url',
		);

		$subtype = 'text';

		if ( in_array( $this->args['subtype'], $allowed_subtype, true ) ) {
			$subtype = $this->args['subtype'];
		}

		$this->subtype = $subtype;
	}

	/**
	 * Order of HTML markup.
	 *
	 * @return void
	 */
	protected function html_order() {
		if ( 'hidden' === $this->subtype ) {
			$this->output_order = [ 'wrapper_start', 'errors', 'field_markup', 'wrapper_end' ];
		} else {
			parent::html_order();
		}

		// Remove label output if custom HTML.
		if ( $this->get( 'html' ) && empty( $this->args['label'] ) ) {
			$this->output_order = array_diff( $this->output_order, [ 'label', 'field_wrap_start', 'field_wrap_end' ] );
		}
	}

	/**
	 * Field markup.
	 *
	 * @return void
	 */
	public function field_markup() {
		parent::field_markup();

		if ( $this->get( 'html' ) ) {
			$this->add_html( $this->get( 'html' ) );
		} else {
			$this->add_html( '<input type="' . esc_attr( $this->subtype ) . '" value="' . esc_attr( $this->value() ) . '"' . $this->common_attr() . $this->custom_attr() . '/>' );
		}

		/**
		 * Action triggered after rendering field markup.
		 *
		 * @param object $field Field object passed by reference.
		 * @since 4.1.0
		 */
		do_action_ref_array( 'ap_after_field_markup', [ &$this ] );
	}

}
