<?php
/**
 * AnsPress Form object.
 *
 * @package    AnsPress
 * @subpackage Form
 * @since      4.1.0
 * @author     Rahul Aryan<support@anspress.io>
 * @copyright  Copyright (c) 2017, Rahul Aryan
 * @license    http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace AnsPress;
use AP_Question as Question;
use PC;

/**
 * The form class.
 *
 * @since 4.1.0
 */
class Form {
	/**
	 * The form name.
	 *
	 * @var string
	 */
	public $form_name = '';

	/**
	 * The form args.
	 *
	 * @var array
	 */
	public $args = [];

	/**
	 * The fields.
	 *
	 * @var array
	 */
	public $fields = [];

	/**
	 * Is form prepared.
	 *
	 * @var boolean
	 */
	public $prepared = false;

	public $ap_qa;
	public $saved = false;
	public $have_errors = false;

	/**
	 * Initialize the class.
	 *
	 * @param string $form_name Name of form.
	 * @param array  $args      Arguments for form.
	 */
	public function __construct( $form_name, $args ) {
		$this->form_name = $form_name;
		$this->args      = $args;
	}

	/**
	 * Prepare input field.
	 *
	 * @return void
	 */
	public function prepare() {
		$fields = ap_sort_array_by_order( $this->args['fields'] );
		foreach ( (array) $fields as $field_name => $field_args ) {

			if ( empty( $field_args['type'] ) ) {
				$field_args['type'] = 'input';
			}

			$type_class = ucfirst( trim( $field_args['type'] ) );
			$field_class = 'AnsPress\\Form\\Field\\' . $type_class;

			if ( class_exists( $field_class ) ) {
				$this->fields[ $field_name ] = new $field_class( $this->form_name, $field_name, $field_args );
			}
		}

		$this->prepared = true;
	}

	/**
	 * Generate fields HTML markup.
	 *
	 * @return string
	 */
	public function generate_fields() {
		$html = '';

		if ( false === $this->prepared ) {
			$this->prepare();
		}

		foreach ( (array) $this->fields as $field ) {
			$html .= $field->output();
		}

		return $html;
	}

	/**
	 * Generate form.
	 *
	 * @return void
	 */
	public function generate() {
		echo '<form id="' . $this->form_name . '" name="' . esc_attr( $this->form_name ) . '" method="POST" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo $this->generate_fields(); // xss okay.

		echo '<input type="hidden" name="action" value="ap_forms">';
		echo '<input type="hidden" name="ap_form_name" value="' . esc_attr( $this->form_name ) . '" />';
		echo '<input type="submit" name="' . esc_attr( $this->form_name ) . '_submit" value="Submit" />';
		echo '<input type="hidden" name="' . esc_attr( $this->form_name ) . '_nonce" value="' . esc_attr( wp_create_nonce( $this->form_name ) ) . '" />';
		echo '</form>';
	}

	/**
	 * Check if current form is submitted.
	 *
	 * @return boolean
	 */
	public function is_submitted() {
		$nonce = ap_isset_post_value( esc_attr( $this->form_name ) . '_nonce' );

		if ( ap_isset_post_value( esc_attr( $this->form_name ) . '_submit' ) && wp_verify_nonce( $nonce, $this->form_name ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Find a field object.
	 *
	 * @param string        $field_name   Name of a field to find.
	 * @param boolean|array $fields       List of field where to search.
	 * @return array|boolean
	 */
	public function find( $field_name, $fields = false ) {
		$fields = false === $fields ? $this->fields : $fields;
		$found = wp_filter_object_list( $fields, [ 'field_name' => $field_name ] );

		if ( empty( $found ) ) {
			foreach ( $fields as $field ) {
				if ( ! empty( $field->child ) && ! empty( $field->child->fields ) ) {
					$child_found = $this->find( $field_name, $field->child->fields );

					if ( ! empty( $child_found ) ) {
						$found = $child_found;
						break;
					}
				}
			}
		}
		return is_array( $found ) ? reset( $found ) : $found;
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
	 * Add new field in form.
	 *
	 * @param string $path Path of new array item. This must include field name at last.
	 * @param mixed  $val  Value to set.
	 *
	 * @return void.
	 */
	public function add_field( $path, $val ) {
		$path = is_string( $path ) ? explode( '.', $path ): $path;
		$loc  = &$this->args['fields'];

		foreach ( (array) $path as $step ) {
			$loc = &$loc[ $step ]['fields'];
		}

		$loc['fields'] = $val;
	}

	/**
	 * Validate and sanitize all fields.
	 *
	 * @param boolean|array $fields Fields to process.
	 * @return void
	 */
	public function sanitize_validate( $fields = false ) {
		if ( ! ap_isset_post_value( $this->form_name . '_submit' ) ) {
			return;
		}

		if ( false === $this->prepared ) {
			$this->prepare();
		}

		if ( false === $fields ) {
			$fields = $this->fields;
		}

		foreach ( (array) $fields as $field ) {
			if ( ! empty( $field->child ) && ! empty( $field->child->fields ) ) {
				$this->sanitize_validate( $field->child->fields );
			}

			$field->sanitize();
			$field->validate();
		}
	}

	/**
	 * Callback triggered before saving.
	 *
	 * @param boolean $fields
	 * @return void
	 */
	private function pre_save( $fields = false ) {
		if ( false === $fields ) {
			$fields = $this->fields;
		}

		foreach ( (array) $fields as $field ) {
			if ( ! empty( $field->child ) && ! empty( $field->child->fields ) ) {
				$this->pre_save( $field->child->fields, true );
			}

			$field->pre_save( $this->ap_qa );
		}
	}

	/**
	 * Callback triggered after saving a post.
	 *
	 * @param boolean $fields
	 * @return void
	 */
	private function post_save( $fields = false ) {
		if ( false === $fields ) {
			$fields = $this->fields;
		}

		foreach ( (array) $fields as $field ) {
			if ( ! empty( $field->child ) && ! empty( $field->child->fields ) ) {
				$this->post_save( $field->child->fields );
			}

			$field->post_save( $this->ap_qa );
		}
	}

	/**
	 * Save all fields.
	 *
	 * @param boolean|array $fields Fields to process.
	 * @return void
	 */
	public function save_post( $fields = false ) {
		if ( true === $this->saved ) {
			return;
		}

		if ( ! $this->ap_qa instanceof Question ) {
			$this->ap_qa = new Question();
		}

		if ( false === $this->prepared ) {
			$this->prepare();
		}

		$this->pre_save();
		$ret = $this->ap_qa->save();

		if ( ! is_wp_error( $ret ) ) {
			$this->saved = true;
			$this->post_save();
		}

		return $ret;
	}

}
