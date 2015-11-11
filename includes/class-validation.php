<?php
/**
 * AnsPress form validation class
 * @link http://anspress.io
 * @since 2.0.1
 * @license GPL 2+
 * @package AnsPress
 */

class AnsPress_Validation
{
	private $args = array();

	private $errors = array();

	private $fields = array();

	/**
	 * Initialize the class
	 * @param array $args
	 */
	public function __construct($args = array()) {

		if ( empty( $args ) ) {
			return; }

		$this->args = $args;

		$this->fields_to_include();

		$this->actions();
	}

	/**
	 * Check fields to process
	 * @return void
	 * @since 2.0.1
	 */
	private function fields_to_include() {

		foreach ( $this->args as $field => $actions ) {
			$this->fields[$field] = @$_REQUEST[$field];
		}
	}

	/**
	 * Check if field is empty or not set
	 * @param  string $field
	 * @return void
	 * @since 2.0.1
	 */
	public function required($field) {

		if ( ! isset( $this->fields[$field] ) ||  $this->fields[$field] == '' ) {
			$this->errors[$field] = __( 'This field is required', 'ap' ); }
	}

	/**
	 * Sanitize text fields
	 * @param  string $field
	 * @return void
	 * @since 2.0.1
	 */
	private function sanitize_text_field($field) {

		if ( isset( $this->fields[$field] ) ) {
			$this->fields[$field] = sanitize_text_field( $this->fields[$field] ); }
	}

	/**
	 * Check length of a string, if less then specified then return error
	 * @param  string $field
	 * @param  string $param
	 * @return void
	 * @since  2.0
	 */
	private function length_check($field, $param) {

		if ( $param != 0 && ( ! isset( $this->fields[$field] ) || mb_strlen( strip_tags( $this->fields[$field] ) ) <= $param ) ) {
			$this->errors[$field] = sprintf( __( 'Its too short, it must be minimum %d characters', 'ap' ), $param ); }
	}

	/**
	 * Count comma separated strings
	 * @param  string $field
	 * @param  string $param
	 * @return void
	 * @since  2.0
	 */
	private function comma_separted_count($field, $param) {
		if ( isset( $this->fields[$field] ) ) {
			$tags = $this->fields[$field];

			if ( ! is_array( $tags ) ) {
				$tags = explode( ',', $tags );
			}

			if ( count( $tags ) < $param ) {
				$this->errors[$field] = sprintf( __( 'It must be minimum %d characters', 'ap' ), $param );
			}
		} elseif ( $param > 0 ) {
			$this->errors[$field] = sprintf( __( 'It must be minimum %d characters', 'ap' ), $param );
		}
	}

	private function is_email($field) {

		$email = is_email( $this->fields[$field] );

		if ( ! $email ) {
			$this->errors[$field] = __( 'Not a valid email address', 'ap' );
		} else {
			$this->fields[$field] = $email;
		}
	}

	/**
	 * Sanitize as a boolean value
	 * @param  string $field
	 * @return void
	 * @since 2.0.1
	 */
	private function only_boolean($field) {

		$this->fields[$field] = (bool) $this->fields[$field];

	}

	/**
	 * Sanitize as a integer value
	 * @param  string $field
	 * @return void
	 * @since 2.0.1
	 */
	private function only_int($field) {

		$this->fields[$field] = (int) $this->fields[$field];

	}

	/**
	 * Sanitize field using wp_kses
	 * @param  string $field
	 * @return void
	 * @since 2.0.1
	 */
	private function wp_kses($field) {

		$this->fields[$field] = wp_kses( $this->fields[$field], ap_form_allowed_tags() );
	}

	/**
	 * Remove wordpress read more tag
	 * @param  string $field
	 * @return void
	 * @since 2.0.1
	 */
	private function remove_more($field) {

		$this->fields[$field] = str_replace( '<!--more-->', '', $this->fields[$field] );
	}

	/**
	 * Stripe shortcode tags
	 * @param  string $field
	 * @return void
	 * @since 2.0.1
	 */
	private function strip_shortcodes($field) {

		$this->fields[$field] = strip_shortcodes( $this->fields[$field] );
	}

	/**
	 * Encode contents inside pre and code tag
	 * @param  string $field
	 * @return void
	 * @since 2.0.1
	 */
	private function encode_pre_code($field) {

		$this->fields[$field] = preg_replace_callback( '/<pre.*?>(.*?)<\/pre>/imsu', array( $this, 'pre_content' ), $this->fields[$field] );
		$this->fields[$field] = preg_replace_callback( '/<code.*?>(.*?)<\/code>/imsu', array( $this, 'code_content' ), $this->fields[$field] );
	}

	private function pre_content($matches) {

		return '<pre>'.esc_html( $matches[1] ).'</pre>';
	}

	private function code_content($matches) {

		return '<code>'.esc_html( $matches[1] ).'</code>';
	}

	/**
	 * Strip all tags
	 * @param  string $field
	 * @return void
	 * @since  2.0
	 */
	private function strip_tags($field) {

		$this->fields[$field] = strip_tags( $this->fields[$field] );
	}

	/**
	 * Santitize tags field
	 * @param  string $field
	 * @return void
	 * @since  2.0
	 */
	private function sanitize_tags($field) {

		$this->fields[$field] = $this->fields[$field];

		$tags = $this->fields[$field];

		if ( ! is_array( $tags ) ) {
			$tags = explode( ',', $tags ); }

		$sanitized_tags = '';

		if ( is_array( $tags ) ) {
			$count = count( $tags );
			$i = 1;
			foreach ( $tags  as $tag ) {
				$sanitized_tags .= sanitize_text_field( $tag );

				if ( $count != $i ) {
					$sanitized_tags .= ','; }

				$i++;
			}
		}

		$this->fields[$field] = $sanitized_tags;
	}

	/**
	 * Sanitize field based on actions passed
	 * @param  string $field
	 * @param  array  $actions
	 * @return void
	 * @since 2.0.1
	 */
	private function sanitize($field, $actions) {

		foreach ( $actions as $type ) {
			switch ( $type ) {
				case 'sanitize_text_field':
					$this->sanitize_text_field( $field );
					break;

				case 'only_boolean':
					$this->only_boolean( $field );
					break;

				case 'only_int':
					$this->only_int( $field );
					break;

				case 'wp_kses':
					$this->wp_kses( $field );
					break;

				case 'remove_more':
					$this->remove_more( $field );
					break;

				case 'strip_shortcodes':
					$this->strip_shortcodes( $field );
					break;

				case 'encode_pre_code':
					$this->encode_pre_code( $field );
					break;

				case 'strip_tags':
					$this->strip_tags( $field );
					break;

				case 'sanitize_tags':
					$this->sanitize_tags( $field );
					break;

				case 'is_email':
					$this->is_email( $field );
					break;

				default:
					$this->fields[$field] = apply_filters( 'ap_validation_sanitize_field', $field, $actions );
					break;
			}
		}
	}

	/**
	 * Validate a field based on actions passed
	 * @param  string $field
	 * @param  array  $actions
	 * @return void
	 * @since 2.0.1
	 */
	private function validate($field, $actions) {

		foreach ( $actions as $type => $param ) {
			if ( isset( $this->errors[$field] ) ) {
				return; }

			switch ( $type ) {
				case 'required':
					$this->required( $field );
					break;

				case 'length_check':
					$this->length_check( $field, $param );
					break;

				case 'comma_separted_count':
					$this->comma_separted_count( $field, $param );
					break;

				case 'is_email':
					$this->is_email( $field );
					break;

				default:
					$this->errors[$field] = apply_filters( 'ap_validation_validate_field', $field, $actions );
					break;
			}
		}
	}

	/**
	 * Field is being checked and sanitized
	 * @return void
	 * @since 2.0.1
	 */
	private function actions() {

		foreach ( $this->args as $field => $actions ) {
			if ( isset( $actions['sanitize'] ) ) {
				$this->sanitize( $field, $actions['sanitize'] ); }

			if ( isset( $actions['validate'] ) ) {
				$this->validate( $field, $actions['validate'] ); }
		}

	}

	/**
	 * Check if fields have any error
	 * @return boolean
	 * @since 2.0.1
	 */
	public function have_error() {
		if ( count( $this->errors ) > 0 ) {
			return true; }

		return false;
	}

	/**
	 * Get all errors
	 * @return array | boolean
	 */
	public function get_errors() {
		if ( count( $this->errors ) > 0 ) {
			return $this->errors; }

		return false;
	}

	/**
	 * Return all sanitized fields
	 * @return array
	 * @since 2.0.1
	 */
	public function get_sanitized_fields() {

		return $this->fields;
	}
}
