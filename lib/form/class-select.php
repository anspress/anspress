<?php
/**
 * AnsPress Select type field object.
 *
 * @package    AnsPress
 * @subpackage Fields
 * @since      4.1.0
 * @author     Rahul Aryan<support@anspress.net>
 * @copyright  Copyright (c) 2017, Rahul Aryan
 * @license    http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace AnsPress\Form\Field;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Select type field object.
 *
 * @since 4.1.0
 */
class Select extends \AnsPress\Form\Field {
	/**
	 * The field type.
	 *
	 * @var string
	 */
	public $type = 'select';

	/**
	 * Prepare field.
	 *
	 * @return void
	 */
	protected function prepare() {
		$this->args = wp_parse_args(
			$this->args,
			array(
				'label'      => __( 'AnsPress Select Field', 'anspress-question-answer' ),
				'options'    => array(),
				'terms_args' => array(
					'taxonomy'   => 'question_category',
					'hide_empty' => false,
					'fields'     => 'id=>name',
					'orderby'    => ap_opt( 'form_category_orderby' ) ? ap_opt( 'form_category_orderby' ) : 'count',
				),
			)
		);

		// Call parent prepare().
		parent::prepare();

		if ( in_array( $this->get( 'options' ), array( 'posts', 'terms' ), true ) ) {
			$this->sanitize_cb = array_merge( array( 'absint' ), $this->sanitize_cb );
		} else {
			$this->sanitize_cb = array_merge( array( 'text_field' ), $this->sanitize_cb );
		}
	}

	/**
	 * Return options of a select field.
	 *
	 * @return array
	 */
	private function get_options() {
		$options = $this->get( 'options' );

		if ( is_string( $options ) && 'terms' === $options ) {
			return get_terms( $this->get( 'terms_args', array() ) );
		}

		if ( is_string( $options ) && 'posts' === $options ) {
			return wp_list_pluck( get_posts( $this->get( 'posts_args', array() ) ), 'post_title', 'ID' );
		}

		return (array) $options;
	}

	/**
	 * Field markup.
	 *
	 * @return void
	 */
	public function field_markup() {
		parent::field_markup();

		$this->add_html( '<select' . $this->common_attr() . $this->custom_attr() . '>' );
		$this->add_html( '<option value="">' . __( 'Select an option', 'anspress-question-answer' ) . '</option>' );

		foreach ( (array) $this->get_options() as $val => $label ) {
			$selected = selected( $this->value(), $val, false );
			$this->add_html( '<option value="' . esc_attr( $val ) . '" ' . $selected . '>' . esc_html( $label ) . '</option>' );
		}

		$this->add_html( '</select>' );

		/** This action is documented in lib/form/class-input.php */
		do_action_ref_array( 'ap_after_field_markup', array( &$this ) );
	}
}
