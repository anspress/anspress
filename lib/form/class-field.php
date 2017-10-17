<?php
/**
 * AnsPress base field object.
 *
 * @package    AnsPress
 * @subpackage Fields
 * @since      4.1.0
 * @author     Rahul Aryan<support@anspress.io>
 * @copyright  Copyright (c) 2017, Rahul Aryan
 * @license    http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace AnsPress\Form;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base field class.
 *
 * @since 4.1.0
 */
class Field {
	/**
	 * The current field name.
	 *
	 * @var string
	 */
	public $field_name = '';

	/**
	 * The field arguments.
	 *
	 * @var array
	 */
	public $args = [];

	/**
	 * The field HTML markup.
	 *
	 * @var string
	 */
	protected $html = '';

	/**
	 * The HTML output order.
	 *
	 * @var array
	 */
	protected $output_order = [];

	/**
	 * The errors.
	 *
	 * @var array
	 */
	public $errors = [];

	/**
	 * Field type.
	 *
	 * @var string
	 */
	public $type = 'input';

	/**
	 * Child fields.
	 *
	 * @var array
	 */
	public $child = [];


	protected $validated = false;
	protected $validate_cb = [];
	protected $sanitize_cb = [];

	protected $sanitized = false;
	public $sanitized_value;
	public $value = null;

	/**
	 * Initialize the class.
	 *
	 * @param string $form_name Name of parent form.
	 * @param string $name      Name of field.
	 * @param array  $args      Field arguments.
	 */
	public function __construct( $form_name, $name, $args ) {
		$this->field_name = $form_name . '[' . $name . ']';
		$this->form_name  = $form_name;
		$this->args       = $args;

		$this->prepare();
	}

	/**
	 * Prepare field.
	 *
	 * @return void
	 */
	protected function prepare() {
		$this->sanitize_cb();
		$this->validate_cb();
	}

	/**
	 * Parse sanitization callbacks.
	 *
	 * @return void
	 */
	protected function sanitize_cb() {
		if ( ! empty( $this->args['sanitize'] ) ) {
			if ( is_array( $this->args['sanitize'] ) ) {
				$this->sanitize_cb = array_unique( $this->args['sanitize'] );
			} else {
				$this->sanitize_cb = array_unique( explode( ',', $this->args['sanitize'] ) );
			}
		}
	}

	/**
	 * Parse validation callbacks.
	 *
	 * @return void
	 */
	protected function validate_cb() {
		if ( ! empty( $this->args['validate'] ) ) {
			if ( is_array( $this->args['validate'] ) ) {
				$this->validate_cb = array_unique( $this->args['validate'] );
			} else {
				$this->validate_cb = array_unique( explode( ',', $this->args['validate'] ) );
			}
		}
	}

	/**
	 * Get a value from a path or default value if the path doesn't exist
	 *
	 * @param  string $key     Path.
	 * @param  mixed  $default Default value.
	 * @param  array  $array   Array to search.
	 * @return mixed
	 */
	public function get( $key, $default = null, $array = null ) {
		$keys = explode( '.', (string) $key );

		if ( null === $array ) {
			$array = &$this->args;
		}

		foreach ( $keys as $key ) {
			if ( ! array_key_exists( $key, $array ) ) {
				return $default;
			}

			$array = &$array[ $key ];
		}

		return $array;
	}

	/**
	 * Add HTML markup to property.
	 *
	 * @param string $html Html as string.
	 * @return void
	 */
	protected function add_html( $html ) {
		$this->html = $this->html . $html;
	}

	/**
	 * Order of HTML markup.
	 *
	 * @return void
	 */
	protected function html_order() {
		$this->output_order = [ 'wrapper_start', 'label', 'errors', 'field_markup', 'desc', 'wrapper_end' ];
	}

	/**
	 * Create field markup.
	 *
	 * @return void
	 */
	public function output() {
		$this->html_order();

		foreach ( (array) $this->output_order as $method ) {
			if ( method_exists( $this, $method ) ) {
				$this->add_html( $this->$method() );
			}
		}

		if ( ! empty( $this->html ) ) {
			return $this->html; // xss okay.
		}
	}

	/**
	 * Get POST (unsafe) value of a field.
	 *
	 * @return null|mixed
	 */
	public function unsafe_value() {
		$request_value = $this->get( ap_to_dot_notation( $this->field_name ), null, $_REQUEST );

		if ( $request_value ) {
			return wp_unslash( $request_value );
		}
	}

	/**
	 * Get value of a field.
	 *
	 * @return mixed
	 */
	public function value() {
		if ( ! is_null( $this->value ) ) {
			return $this->value;
		}

		$sanitized = $this->sanitize();

		if ( null !== $sanitized ) {
			return $sanitized;
		}

		return $this->get( 'value' );
	}

	/**
	 * Output label of field.
	 *
	 * @return void
	 */
	public function label() {
		$this->add_html( '<label class="ap-form-label" for="' . $this->id() . '">' . esc_html( $this->get( 'label' ) ) . '</label>' );
	}

	/**
	 * Check if field have any errors.
	 *
	 * @return boolean
	 */
	public function have_errors() {
		if ( ! empty( $this->errors ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Add error message to a field.
	 *
	 * @param string $code    Error code.
	 * @param string $message Error message.
	 * @return void
	 */
	public function add_error( $code, $message = '' ) {
		// Update parent form property.
		//anspress()->get_form( ltrim( $this->form_name, 'form_' ) )->have_error = true;

		$this->errors[ $code ] = $message;
	}

	/**
	 * Output label of field.
	 *
	 * @return void
	 */
	public function errors() {
		if ( $this->have_errors() ) {
			$this->add_html( '<div class="ap-field-errors">' );
			foreach ( $this->errors as $code => $err ) {
				$this->add_html( '<span class="ap-field-error ecode-' . esc_attr( $code ) . '">' . esc_html( $err ) . '</span>' );
			}
			$this->add_html( '</div>' );
		}
	}

	/**
	 * Return safe css ID.
	 *
	 * @return string
	 */
	public function id( $str = false ) {
		if ( false === $str ) {
			$str = $this->field_name;
		}

		return sanitize_html_class(
			rtrim( preg_replace( '/-+/', '-', str_replace( [ '[', ']' ], '-', $str ) ), '-' )
		);
	}

	/**
	 * Output field description.
	 *
	 * @return void
	 */
	public function desc() {
		if ( $this->get( 'desc' ) ) {
			$this->add_html( '<div class="ap-field-desc">' . esc_html( $this->get( 'desc' ) ) . '</div>' );
		}
	}

	/**
	 * Form field wrapper start.
	 *
	 * @return void
	 */
	protected function wrapper_start() {
		$wrapper = $this->get( 'wrapper', [] );

		if ( false !== $wrapper ) {
			$errors = $this->have_errors() ? ' ap-have-errors' : '';

			$this->add_html( '<div class="ap-form-group ap-field-' . sanitize_html_class( $this->field_name ) . ' ap-field-type-' . esc_attr( $this->type ) . $errors . ' ' . esc_attr( $this->get( 'wrapper.class', '' ) ) . '"' . $this->get_attr( $this->get( 'wrapper.attr' ) ) . '>' );
		}
	}

	/**
	 * Form field wrapper end.
	 *
	 * @return void
	 */
	protected function wrapper_end() {
		if ( false !== $this->get( 'wrapper', [] ) ) {
			$this->add_html( '</div>' );
		}
	}

	/**
	 * Convert and sanitize attributes of array to string.
	 *
	 * @param array $arr Custom attributes to apply.
	 * @return string
	 */
	protected function get_attr( $arr ) {
		$html = '';
		if ( ! empty( $arr ) && is_array( $arr ) ) {
			foreach ( $arr as $attr_key => $attr_value ) {
				$html .= ' ' . sanitize_html_class( $attr_key ) . '="' . esc_attr( $attr_value ) . '"';
			}
		}

		return $html;
	}

	/**
	 * Output common attributes of a field.
	 *
	 * @return string
	 */
	protected function common_attr() {
		return ' name="' . esc_attr( $this->field_name ) . '" id="' . $this->id() . '" class="ap-form-control ' . esc_attr( $this->get( 'class', '' ) ) . '"';
	}

	/**
	 * Sanitized custom attributes of a field.
	 *
	 * @return string
	 */
	protected function custom_attr() {
		return $this->get_attr( $this->get( 'attr', [] ) );
	}

	/**
	 * Arguments passed to sanitization callback.
	 *
	 * @param mixed $val Value to sanitize.
	 * @return array
	 */
	protected function sanitize_cb_args( $val ) {
		return [ $val ];
	}

	/**
	 * Sanitize value of current field.
	 *
	 * @return mixed
	 */
	public function sanitize() {
		if ( true === $this->sanitized ) {
			return $this->sanitized_value;
		}

		if ( ! empty( $this->sanitize_cb ) ) {
			$unsafe_value = $this->unsafe_value();
			$sanitize_applied = false;

			foreach ( (array) $this->sanitize_cb as $sanitize ) {
				// Callback for sanitizing field type.
				$cb = 'sanitize_' . trim( $sanitize );

				if ( method_exists( 'AnsPress\Form\Validate', $cb ) ) {
					$sanitized_val = call_user_func_array( 'AnsPress\Form\Validate::' . $cb, $this->sanitize_cb_args( $unsafe_value ) );

					// If callback is null then do not apply.
					if ( null !== $sanitized_val ) {
						$sanitize_applied = true;
						$unsafe_value = $sanitized_val;
					}
				}
			} // End foreach().

			$this->sanitized = true;

			if ( null !== $unsafe_value && true === $sanitize_applied ) {
				$this->sanitized_value = $unsafe_value;
			}
		} // End if().
	}

	/**
	 * Validate current field.
	 *
	 * @return void
	 */
	public function validate() {
		if ( true === $this->validated ) {
			return;
		}

		if ( ! empty( $this->validate_cb ) ) {

			foreach ( (array) $this->validate_cb as $validate ) {
				// Callback for validating field type.
				$cb = 'validate_' . trim( $validate );

				if ( method_exists( 'AnsPress\Form\Validate', $cb ) ) {
					call_user_func_array( 'AnsPress\Form\Validate::' . $cb, [ $this ] );
				}
			} // End foreach().

			$this->validated = true;
		} // End if().
	}

	public function pre_save( $ap_qa ) {
		// Do not save on validation error.
		if ( $this->have_errors() ) {
			return;
		}

		$save_cb = $this->get( 'save' );
		$value   = $this->value();

		if ( $save_cb ) {

			if ( ! empty( $save_cb['post'] ) && ! is_null( $value ) ) {
				if ( is_array( $save_cb['post'] ) ) {
					call_user_func( $save_cb['post'], $this, $ap_qa );
				} else {
					$ap_qa->set( $save_cb['post'], $value );
				}
			}

			if ( ! empty( $save_cb['terms'] ) && ! is_null( $value ) ) {
				$ap_qa->set_terms( $value, $save_cb['terms'] );
			}
		}

	}

	public function post_save() {

	}
}
