<?php
/**
 * AnsPress Tags type field object.
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
 * The Tags type field object.
 *
 * @since 4.1.0
 */
class Tags extends Field {
	/**
	 * The field type.
	 *
	 * @var string
	 */
	public $type = 'tags';

	/**
	 * Prepare field.
	 *
	 * @return void
	 */
	protected function prepare() {
		$this->args = wp_parse_args(
			$this->args, array(
				'label'      => __( 'AnsPress Tags Field', 'anspress-question-answer' ),
				'array_max'  => 3,
				'array_min'  => 2,
				'terms_args' => array(
					'taxonomy'   => 'question_tag',
					'hide_empty' => false,
					'fields'     => 'id=>name',
				),
				'options'    => 'terms',
				'js_options' => [],
			)
		);

		$js_options = array(
			'maxItems' => $this->args['array_max'],
			'form'     => $this->form_name,
			'id'       => $this->id(),
			'field'    => $this->original_name,
			'nonce'    => wp_create_nonce( 'tags_' . $this->form_name . $this->original_name ),
			'create'   => false,
			'labelAdd' => __( 'Add', 'anspress-question-answer' ),
		);

		$this->args['js_options'] = wp_parse_args( $this->args['js_options'], $js_options );

		// Call parent prepare().
		parent::prepare();

		// Make sure field is sanitized.
		$this->sanitize_cb = array_merge( [ 'array_remove_empty', 'tags_field' ], $this->sanitize_cb );
		$this->validate_cb = array_merge( [ 'is_array', 'array_max', 'array_min' ], $this->validate_cb );
	}

	/**
	 * Arguments passed to sanitization callback.
	 *
	 * @param mixed $val Value to sanitize.
	 * @return array
	 */
	protected function sanitize_cb_args( $val ) {
		return [ $val, $this->args ];
	}

	/**
	 * Return options of a tags field.
	 *
	 * @return array
	 * @since 4.1.8 Use `include` args only if value is not empty.
	 */
	private function get_options() {
		$options = $this->get( 'options' );

		if ( is_string( $options ) && 'terms' === $options ) {
			$options = [];
			if ( ! empty( $this->value() ) ) {
				$value = $this->value();
				$tax_args = array(
					'taxonomy'   => $this->get( 'terms_args.taxonomy' ),
					'hide_empty' => false,
					'count'      => true,
					'number'     => 20,
				);

				if ( ! empty( $value ) ) {
					$tax_args['include'] = $value;
				}

				$terms = get_terms( $tax_args );

				if ( $terms ) {
					foreach ( $terms as $tag ) {
						$options[] = array(
							'term_id'     => $tag->term_id,
							'name'        => $tag->name,
							'description' => $tag->description,
							'count'       => $tag->count,
						);
					}
				}
			}

			return $options;
		} elseif ( is_string( $options ) && 'posts' === $options ) {
			return wp_list_pluck( get_posts( $this->get( 'posts_args', [] ) ), 'post_title', 'ID' );
		}

		return [];
	}

	/**
	 * Field markup.
	 *
	 * @return void
	 */
	public function field_markup() {
		parent::field_markup();

		$options = $this->get_options();
		$value   = ! empty( $this->value() ) ? implode( ',', $this->value() ) : '';
		$type    = is_string( $options ) ? $options : 'tags';

		$this->add_html( '<input type="text" id="' . $this->id() . '" data-type="' . $type . '" data-options="' . esc_js( wp_json_encode( $this->get( 'js_options' ) ) ) . '" class="ap-tags-input" autocomplete="off" aptagfield' . $this->custom_attr() . ' name="' . esc_attr( $this->field_name ) . '" value="' . $value . '" />' );

		$this->add_html( '<script id="' . $this->id() . '-options" type="application/json">' . wp_json_encode( $options ) . '</script>' );

		/** This action is documented in lib/form/class-input.php */
		do_action_ref_array( 'ap_after_field_markup', [ &$this ] );
	}

	/**
	 * Get POST (unsafe) value of a field.
	 *
	 * @return mixed
	 */
	public function unsafe_value() {
		$request_value = $this->get( ap_to_dot_notation( $this->field_name ), null, $_REQUEST );
		if ( isset( $request_value ) ) {
			return explode( ',', wp_unslash( $request_value ) );
		}
	}

}
