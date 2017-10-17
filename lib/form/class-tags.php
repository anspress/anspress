<?php
/**
 * AnsPress Tags type field object.
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
		$this->args = wp_parse_args( $this->args, array(
			'label'      => __( 'AnsPress Tags Field', 'anspress-question-answer' ),
			'array_max'  => 3,
			'array_min'  => 2,
			'js_options' => [],
			'terms_args'  => array(
				'taxonomy'   => 'question_tag',
				'hide_empty' => false,
				'fields'     => 'id=>name',
			),
			'options' => 'terms',
		) );

		$this->args['js_options'] = wp_parse_args( $this->args['js_options'], array(
			'label_no_tags'       => __( 'No tags found!', 'anspress-question-answer' ),
			'label_no_matching'   => __( 'No matching tags found!', 'anspress-question-answer' ),
			'label_add_new'       => __( 'Add New Tag.', 'anspress-question-answer' ),
			'label_max_tag_added' => __( 'You have already added maximum numbers of tags allowed.', 'anspress-question-answer' ),
			'add_tag'             => true,
			'min_tags'            => $this->args['array_min'],
			'max_tags'            => $this->args['array_max'],
		) );

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
		return [ $val, $this->get_options(), $this->get( 'js_options' ) ];
	}

	/**
	 * Return options of a tags field.
	 *
	 * @return array
	 */
	private function get_options() {
		$options = $this->get( 'options' );

		if ( is_string( $options ) && 'terms' === $options ) {
			return get_terms( $this->get( 'terms_args', [] ) );
		}

		if ( is_string( $options ) && 'posts' === $options ) {
			return wp_list_pluck( get_posts( $this->get( 'posts_args', [] ) ), 'post_title', 'ID' );
		}

		return (array) $options;
	}

	/**
	 * Field markup.
	 *
	 * @return void
	 */
	public function field_markup() {
		$options = $this->get_options();

		$js_options = $this->get( 'js_options' );
		$js_options_str = is_array( $js_options ) ? ' data-options="' . esc_attr( esc_js( wp_json_encode( $js_options ) ) ) . '"' : '';

		$this->add_html( '<div class="ap-tag-wrap" data-role="ap-tags" data-name="' . esc_attr( $this->field_name ) . '"' . $js_options_str . ' data-id="' . sanitize_html_class( $this->field_name ) . '">' );

		$i = 1;
		// Populate already selected tags.
		foreach ( (array) $this->unsafe_value() as $key => $label ) {
			$i++;
			$this->add_html( '<span class="ap-tag-item" data-val="' . esc_attr( $key ) . '">' . esc_attr( $label ) . '<input type="hidden" name="' . esc_attr( $this->field_name ) . '[' . esc_attr( $key ) . ']" value="' . esc_attr( $label ) . '" /></span>' );
		}

		$this->add_html( '<input type="text" id="' . sanitize_html_class( $this->field_name ) . '" class="ap-tags-input" autocomplete="off"' . $this->custom_attr() . ' />' );

		if ( $options ) {
			$this->add_html( '<script type="application/json">' . wp_json_encode( $options ) . '</script>' );
		}

		$this->add_html( '</div>' );
	}

}
