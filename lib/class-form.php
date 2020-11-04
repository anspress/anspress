<?php
/**
 * AnsPress Form object.
 *
 * @package    AnsPress
 * @subpackage Form
 * @since      4.1.0
 * @author     Rahul Aryan<support@anspress.net>
 * @copyright  Copyright (c) 2017, Rahul Aryan
 * @license    http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace AnsPress;

use AnsPress\Session;


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

	/**
	 * The errors.
	 *
	 * @var array
	 */
	public $errors = [];

	/**
	 * The values.
	 *
	 * @var null|array
	 */
	public $values = null;

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

	public $submitted = false;

	public $after_form = '';

	/**
	 * Initialize the class.
	 *
	 * @param string $form_name Name of form.
	 * @param array  $args      Arguments for form.
	 */
	public function __construct( $form_name, $args ) {
		$this->form_name = $form_name;
		$this->args      = wp_parse_args(
			$args, array(
				'form_tag'      => true,
				'submit_button' => true,
				'submit_label'  => __( 'Submit', 'anspress-question-answer' ),
				'editing'       => false,
				'editing_id'    => 0,
			)
		);

		$this->editing    = $this->args['editing'];
		$this->editing_id = $this->args['editing_id'];
	}

	/**
	 * Prepare input field.
	 *
	 * @return AnsPress\Form
	 * @since 4.1.0
	 * @since 4.1.5 Return current instance.
	 */
	public function prepare() {
		if ( empty( $this->args['fields'] ) ) {
			return $this;
		}

		$fields = ap_sort_array_by_order( $this->args['fields'] );

		if ( ! ap_opt( 'allow_private_posts' ) && isset( $fields['is_private'] ) ) {
			unset( $fields['is_private'] );
		}

		foreach ( $fields as $field_name => $field_args ) {
			if ( empty( $field_args['type'] ) ) {
				$field_args['type'] = 'input';
			}

			$type_class  = ucfirst( trim( $field_args['type'] ) );
			$field_class = 'AnsPress\\Form\\Field\\' . $type_class;

			if ( class_exists( $field_class ) ) {
				/**
				 * Allows filtering field argument before its being passed to Field class.
				 *
				 * @param array $field_args Field arguments.
				 * @param object $form Form class, passed by reference.
				 * @since 4.1.0
				 */
				$field_args                  = apply_filters_ref_array( 'ap_before_prepare_field', [ $field_args, $this ] );
				$this->fields[ $field_name ] = new $field_class( $this->form_name, $field_name, $field_args, $this );
			}
		}

		$this->prepared = true;
		$this->sanitize_validate();

		return $this;
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

		if ( ! empty( $this->fields ) ) {
			foreach ( (array) $this->fields as $field ) {
				$html .= $field->output();
			}
		}

		return $html;
	}

	/**
	 * Generate form.
	 *
	 * @param array $form_args {
	 *      Form generate arguments.
	 *
	 *      @type string $form_action   Custom form action url.
	 *      @type array  $hidden_fields Custom hidden input fields.
	 * }
	 * @return void
	 * @since 4.1.0
	 * @since 4.1.8 Inherit `hidden_fields` from form args.
	 */
	public function generate( $form_args = [] ) {
		// Enqueue upload script.
		wp_enqueue_script( 'anspress-upload' );

		// Dont do anything if no fields.
		if ( empty( $this->args['fields'] ) ) {
			echo '<p class="ap-form-nofields">';
			printf(
				// Translators: Placeholder contain form name.
				esc_attr__( 'No fields found for form: %s', 'anspress-question-answer' ),
				$this->form_name
			);
			echo '</p>';

			return;
		}

		$form_args = wp_parse_args(
			$form_args, array(
				'form_action'   => '',
				'hidden_fields' => false,
				'ajax_submit'   => true,
				'submit_button' => $this->args['submit_button'],
				'form_tag'      => $this->args['form_tag'],
			)
		);

		if ( ! empty( $this->args['hidden_fields'] ) ) {
			$form_args['hidden_fields'] = wp_parse_args( $form_args['hidden_fields'], $this->args['hidden_fields'] );
		}

		/**
		 * Allows filtering arguments passed to @see AnsPress\Form\generate() method. Passed
		 * by reference.
		 *
		 * @param array  $form_args Form arguments.
		 * @param object $form      Current form object.
		 * @since 4.1.0
		 */
		$form_args = apply_filters_ref_array( 'ap_generate_form_args', [ $form_args, $this ] );

		$action = ! empty( $form_args['form_action'] ) ? ' action="' . esc_url( $form_args['form_action'] ) . '"' : '';

		if ( true === $form_args['form_tag'] ) {
			echo '<form id="' . esc_attr( $this->form_name ) . '" name="' . esc_attr( $this->form_name ) . '" method="POST" enctype="multipart/form-data" ' . $action . ( true === $form_args['ajax_submit'] ? ' apform' : '' ) . '>'; // xss okay.
		}

		// Output form errors.
		if ( $this->have_errors() ) {
			echo '<div class="ap-form-errors">';
			foreach ( (array) $this->errors as $code => $msg ) {
				echo '<span class="ap-form-error ecode-' . esc_attr( $code ) . '">' . esc_html( $msg ) . '</span>';
			}
			echo '</div>';
		}

		echo $this->generate_fields(); // xss okay.

		echo '<input type="hidden" name="ap_form_name" value="' . esc_attr( $this->form_name ) . '" />';

		if ( true === $form_args['submit_button'] ) {
			echo '<button type="submit" class="ap-btn ap-btn-submit">' . esc_html( $this->args['submit_label'] ) . '</button>';
		}

		echo '<input type="hidden" name="' . esc_attr( $this->form_name ) . '_nonce" value="' . esc_attr( wp_create_nonce( $this->form_name ) ) . '" />';
		echo '<input type="hidden" name="' . esc_attr( $this->form_name ) . '_submit" value="true" />';

		// Add custom hidden fields.
		if ( ! empty( $form_args['hidden_fields'] ) ) {
			foreach ( $form_args['hidden_fields'] as $field ) {
				echo '<input type="hidden" name="' . esc_attr( $field['name'] ) . '" value="' . esc_attr( $field['value'] ) . '" />';
			}
		}

		/**
		 * Action triggered after all form fields are generated and before closing
		 * form tag. This action can be used to append more fields or HTML in form.
		 *
		 * @param object $form Current form class.
		 */
		do_action_ref_array( 'ap_after_form_field', [ $this ] );

		if ( true === $this->args['form_tag'] ) {
			echo '</form>';
		}

		if ( ! empty( $this->after_form ) ) {
			echo $this->after_form;
		}
	}

	/**
	 * Check if current form is submitted.
	 *
	 * @return boolean
	 */
	public function is_submitted() {
		$nonce = ap_isset_post_value( esc_attr( $this->form_name ) . '_nonce' );

		if ( ap_isset_post_value( esc_attr( $this->form_name ) . '_submit' ) && wp_verify_nonce( $nonce, $this->form_name ) ) {
			$this->submitted = true;
			return true;
		}

		return false;
	}

	/**
	 * Find a field object.
	 *
	 * @param string        $value         Value for argument `$key`.
	 * @param boolean|array $fields        List of field where to search.
	 * @param string        $key           Search field by which property of a field object.
	 * @return object|boolean
	 */
	public function find( $value, $fields = false, $key = 'original_name' ) {
		if ( false === $this->prepared ) {
			$this->prepare();
		}

		$fields = false === $fields ? $this->fields : $fields;
		$found  = wp_filter_object_list( $fields, [ $key => $value ] );

		if ( empty( $found ) ) {
			foreach ( $fields as $field ) {
				if ( ! empty( $field->child ) && ! empty( $field->child->fields ) ) {
					$child_found = $this->find( $value, $field->child->fields );

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
	 * Add an error to form object.
	 *
	 * @param string $code Error code.
	 * @param string $msg  Error message.
	 * @return void
	 */
	public function add_error( $code, $msg = '' ) {
		$this->errors[ $code ] = $msg;
	}

	/**
	 * Check if form have any error.
	 *
	 * @return boolean
	 */
	public function have_errors() {
		return ! empty( $this->errors ) && is_array( $this->errors );
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
		$path = is_string( $path ) ? explode( '.', $path ) : $path;
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
	private function sanitize_validate( $fields = false ) {
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

			if ( true === $field->have_errors() ) {
				$this->add_error( 'fields-error', __( 'Error found in fields, please check and re-submit', 'anspress-question-answer' ) );
			}
		}
	}

	/**
	 * Get errors of all fields.
	 *
	 * @param false|array $fields Fields.
	 * @return array
	 */
	public function get_fields_errors( $fields = false ) {
		$errors = [];

		if ( false === $this->prepared ) {
			$this->prepare();
		}

		if ( false === $fields ) {
			$fields = $this->fields;
		}

		foreach ( (array) $fields as $field ) {
			if ( $field->have_errors() ) {
				$errors[ $field->id() ] = [ 'error' => $field->errors ];
			}

			if ( ! empty( $field->child ) && ! empty( $field->child->fields ) ) {
				$child_errors = $this->get_fields_errors( $field->child->fields );

				if ( ! empty( $child_errors ) ) {
					$errors[ $field->id() ]['child'] = $child_errors;
				}
			}
		}

		return $errors;
	}

	/**
	 * Get values from all fields.
	 *
	 * @param boolean|array $fields Child fields.
	 * @return array
	 * @since 4.1.0
	 */
	private function field_values( $fields = false ) {
		$values = [];

		if ( false === $this->prepared ) {
			$this->prepare();
		}

		if ( false === $fields ) {
			$fields = $this->fields;
		}

		foreach ( (array) $fields as $field ) {
			$field->pre_get();
			$values[ $field->original_name ] = [ 'value' => $field->value() ];
			if ( ! empty( $field->child ) && ! empty( $field->child->fields ) ) {
				$values[ $field->original_name ]['child'] = $this->field_values( $field->child->fields );
			}
		}

		return $values;
	}

	/**
	 * Get all values of fields.
	 *
	 * @return array|false
	 * @since 4.1.0
	 * @since 4.1.5 Return values even if there are errors.
	 */
	public function get_values() {
		// if ( $this->have_errors() ) {
		// return false;
		// }
		if ( ! is_null( $this->values ) ) {
			return $this->values;
		}

		$this->values = $this->field_values();
		return $this->values;
	}

	/**
	 * Run all after save methods in fields and child fields.
	 *
	 * @param boolean|array $fields Fields.
	 * @param array         $args   Arguments to be passed to method.
	 * @return void
	 * @since 4.1.0
	 * @since 4.1.5 Delete AnsPress session data.
	 */
	public function after_save( $fields = false, $args = [] ) {
		// Delete session data.
		$this->delete_values_session();

		if ( false === $this->prepared ) {
			$this->prepare();
		}

		if ( false === $fields ) {
			$fields = $this->fields;
		}

		foreach ( (array) $fields as $field ) {
			$field->after_save( $args );

			if ( ! empty( $field->child ) && ! empty( $field->child->fields ) ) {
				$this->after_save( $field->child->fields, $args );
			}
		}
	}

	/**
	 * Set values for a field.
	 *
	 * This must be called before initialization of form.
	 *
	 * @param array $values Values.
	 * @return AnsPress\Form
	 *
	 * @since 4.1.0
	 * @since 4.1.5 Set values after form is prepared. Return current object.
	 */
	public function set_values( $values ) {
		if ( false === $this->prepared ) {
			$this->prepare();
		}

		if ( empty( $values ) ) {
			return $this;
		}

		foreach ( $values as $key => $val ) {
			is_array( $val ) && isset( $val['value'] ) && $val = $val['value'];
			$field = $this->find( $key );

			if ( $field ) {
				$field->value = $val;
			}
		}

		return $this;
	}


	/**
	 * Save current values to AnsPress session so that users unfinished question can
	 * be retrieved later.
	 *
	 * @param integer $id Numerical id.
	 * @return void
	 * @since 4.1.5
	 */
	public function save_values_session( $id = '' ) {
		$values = $this->get_values();

		if ( ! empty( $values ) ) {
			$id = empty( $id ) ? '' : '_' . $id;
			anspress()->session->set( $this->form_name . $id, $values );
		}
	}

	/**
	 * Delete all form data stored in user session.
	 *
	 * @param integer $id Numerical id.
	 * @return void
	 */
	public function delete_values_session( $id = '' ) {
		$id = empty( $id ) ? '' : '_' . $id;
		anspress()->session->delete( $this->form_name . $id );
	}
}
