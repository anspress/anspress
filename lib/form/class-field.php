<?php
/**
 * AnsPress base field object.
 *
 * @package    AnsPress
 * @subpackage Fields
 * @since      4.1.0
 * @author     Rahul Aryan<support@anspress.net>
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
	 * The original field name.
	 *
	 * @var string
	 */
	public $original_name = '';

	/**
	 * The form name.
	 *
	 * @var string
	 */
	public $form_name;

	/**
	 * Unique name without square brackets.
	 *
	 * @var string
	 */
	public $field_id = '';

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
	 * @var object
	 */
	public $child;

	/**
	 * Is editing.
	 *
	 * @var boolean
	 */
	public $editing = false;

	/**
	 * Editing post ID.
	 *
	 * @var boolean|integer
	 */
	public $editing_id = false;

	/**
	 * Is validated?
	 *
	 * @var boolean
	 */
	protected $validated = false;

	/**
	 * The validation callbacks.
	 *
	 * @var array
	 */
	protected $validate_cb = [];

	/**
	 * The sanitization callback.
	 *
	 * @var array
	 */
	protected $sanitize_cb = [];

	/**
	 * Is sanitized?
	 *
	 * @var boolean
	 */
	protected $sanitized = false;

	/**
	 * The sanitized values.
	 *
	 * @var array
	 */
	public $sanitized_value;

	/**
	 * The field value (unsafe).
	 *
	 * @var mixed
	 */
	public $value = null;

	/**
	 * Initialize the class.
	 *
	 * @param string $form_name Name of parent form.
	 * @param string $name      Name of field.
	 * @param array  $args      Field arguments.
	 */
	public function __construct( $form_name, $name, $args ) {
		$this->original_name = $name;
		$this->field_name    = $form_name . '[' . $name . ']';
		$this->form_name     = $form_name;
		$this->args          = $args;
		$this->field_id      = $this->id();

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
	 * Get parent form.
	 *
	 * @return object
	 */
	public function form() {
		$form_name = explode( '.', ap_to_dot_notation( $this->form_name ) );
		return anspress()->get_form( $form_name[0] );
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
			if ( ! is_array( $array ) || ! array_key_exists( $key, $array ) ) {
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
	public function add_html( $html ) {
		$this->html = $this->html . $html;
	}

	/**
	 * Order of HTML markup.
	 *
	 * @return void
	 * @since 4.1.8 Allow overriding order from arguments.
	 */
	protected function html_order() {
		if ( empty( $this->args['output_order'] ) ) {
			$this->output_order = [ 'wrapper_start', 'label', 'field_wrap_start', 'errors', 'field_markup', 'desc', 'field_wrap_end', 'wrapper_end' ];
		} else {
			$this->output_order = $this->args['output_order'];
		}
	}

	/**
	 * Create field markup.
	 *
	 * @return null|string
	 */
	public function output() {
		$this->html_order();

		foreach ( (array) $this->output_order as $method ) {
			if ( method_exists( $this, $method ) ) {
				$this->add_html( $this->$method() );
			}
		}

		/**
		 * Filter applied before returning a html of a field.
		 *
		 * @param string $html Field HTML markup.
		 * @param object $field Current field object.
		 */
		$this->html = apply_filters( 'ap_field_html', $this->html, $this ); // xss okay.

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
		if ( isset( $request_value ) ) {
			return wp_unslash( $request_value );
		}
	}

	/**
	 * Check if request value is set.
	 *
	 * @return boolean
	 */
	public function isset_value() {
		$request_value = $this->get( ap_to_dot_notation( $this->field_name ), null, $_REQUEST );

		if ( is_null( $request_value ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get value of a field.
	 *
	 * @param mixed $custom_val Set custom value for field.
	 * @return mixed
	 *
	 * @since 4.1.8 Pass a value to set it as value of field.
	 */
	public function value( $custom_val = null ) {
		if ( null !== $custom_val ) {
			$this->value = $custom_val;
			return true;
		}

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
	 * Field wrapper start.
	 *
	 * @return void
	 */
	protected function field_wrap_start() {
		$this->add_html( '<div class="ap-field-group-w">' );
	}

	/**
	 * Field wrapper end.
	 *
	 * @return void
	 */
	protected function field_wrap_end() {
		$this->add_html( '</div>' );
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
		$this->errors[ $code ] = $message;
		$name                  = explode( '.', ap_to_dot_notation( $this->form_name ) );

		if ( is_array( $name ) ) {
			anspress()->get_form( $name[0] )->add_error( 'fields-error', __( 'Error found in fields, please check and re-submit', 'anspress-question-answer' ) );
		}
	}

	/**
	 * Output label of field.
	 *
	 * @return void
	 */
	public function errors() {
		$wrapper = $this->get( 'wrapper', [] );

		if ( false !== $wrapper ) {
			$this->add_html( '<div class="ap-field-errorsc">' );
		}

		if ( $this->have_errors() ) {
			$this->add_html( '<div class="ap-field-errors">' );
			foreach ( $this->errors as $code => $err ) {
				$this->add_html( '<span class="ap-field-error ecode-' . esc_attr( $code ) . '">' . esc_html( $err ) . '</span>' );
			}
			$this->add_html( '</div>' );
		}

		if ( false !== $wrapper ) {
			$this->add_html( '</div>' );
		}
	}

	/**
	 * Return safe css ID.
	 *
	 * @return string
	 */
	public function id( $str = false ) {
		if ( ! empty( $this->field_id ) ) {
			return $this->field_id;
		}

		if ( false === $str ) {
			$str = $this->field_name;
		}

		return sanitize_html_class(
			rtrim( preg_replace( '/-+/', '-', str_replace( [ '[', ']' ], '-', $str ) ), '-' )
		);
	}

	/**
	 * Field markup.
	 *
	 * @return void
	 */
	public function field_markup() {
		/**
		 * Action triggered before rendering field markup.
		 *
		 * @param object $field Field object passed by reference.
		 * @since 4.1.0
		 */
		do_action_ref_array( 'ap_before_field_markup', [ $this ] );
	}

	/**
	 * Output field description.
	 *
	 * @return void
	 */
	public function desc() {
		if ( $this->get( 'desc' ) ) {
			$this->add_html( '<div class="ap-field-desc">' . wp_kses_post( $this->get( 'desc' ) ) . '</div>' );
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

			$this->add_html( '<div class="ap-form-group ap-field-' . $this->id() . ' ap-field-type-' . esc_attr( $this->type ) . $errors . ' ' . esc_attr( $this->get( 'wrapper.class', '' ) ) . '"' . $this->get_attr( $this->get( 'wrapper.attr' ) ) . '>' );
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
			$unsafe_value     = $this->unsafe_value();
			$sanitize_applied = false;

			foreach ( (array) $this->sanitize_cb as $sanitize ) {
				// Callback for sanitizing field type.
				$cb = 'sanitize_' . trim( $sanitize );

				if ( method_exists( 'AnsPress\Form\Validate', $cb ) ) {
					$sanitized_val = call_user_func_array( 'AnsPress\Form\Validate::' . $cb, $this->sanitize_cb_args( $unsafe_value ) );

					// If callback is null then do not apply.
					if ( null !== $sanitized_val ) {
						$sanitize_applied = true;
						$unsafe_value     = $sanitized_val;
					}
				}
			} // End foreach().

			$this->sanitized = true;
			if ( null !== $unsafe_value && true === $sanitize_applied ) {
				$this->sanitized_value = $unsafe_value;
			}
		} // End if().

		return $this->sanitized_value;
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

				/**
				 * Custom validation callback.
				 *
				 * @param string $field_name CUrrent field.
				 * @param object $field CUrrent field.
				 * @since 4.1.7
				 */
				apply_filters_ref_array( "ap_field_{$cb}", [ $this->field_name, $this ] );

			} // End foreach().

			$this->validated = true;
		} // End if().
	}

	public function pre_get() {
	}

	public function after_save( $args = [] ) {

	}

	/**
	 * Call save callback.
	 *
	 * This will call save callback with two parameter `value`
	 * and current field object.
	 *
	 * @return mixed
	 * @since 4.1.8
	 */
	public function save_cb() {
		if ( ! empty( $this->args['save'] ) && is_callable( $this->args['save'] ) ) {
			return call_user_func( $this->args['save'], $this->value(), $this );
		}
	}

}
