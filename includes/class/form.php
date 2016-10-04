<?php
/**
 * Form class
 *
 * @package  	AnsPress
 * @license  	https://www.gnu.org/licenses/gpl-2.0.txt GPL v2.0 (or later)
 * @link     	https://anspress.io
 * @since 		2.0
 */

/**
 * AnsPress HTML form handler
 */
class AnsPress_Form {

	/**
	 * Name of the form
	 * @var string
	 */
	private $name;

	/**
	 * Form arguments
	 * @var array
	 */
	private $args = array();

	/**
	 * HTML output
	 * @var string
	 */
	private $output = '';

	/**
	 * Current field
	 * @var array
	 */
	private $field;

	/**
	 * Form error
	 * @var array
	 */
	private $errors;

	/**
	 * Initiate the class
	 * @param array $args Form arguments
	 */
	public function __construct($args = array()) {

		// Default form arguments.
		$defaults = array(
			'method'            => 'POST',
			'action'            => '',
			'is_ajaxified'      => false,
			'class'             => 'ap-form',
			'multipart'         => false,
			'submit_button'     => __( 'Submit', 'anspress-question-answer' ),
		);

		// Merge defaults args.
		$this->args = wp_parse_args( $args, $defaults );

		// Set the name of the form.
		$this->name = $this->args['name'];

		global $ap_errors;
		$this->errors = $ap_errors;

		$this->add_default_in_field();

		$this->order_fields();
	}

	/**
	 * Add fields default values.
	 */
	private function add_default_in_field() {
		if ( ! isset( $this->args['fields'] ) ) {
			return;
		}

		foreach ( (array) $this->args['fields'] as $k => $field ) {
			if ( ! is_array( $field ) ) {
				return;
			}

			if ( ! isset( $field['order'] ) ) {
				$this->args['fields'][ $k ]['order'] = 10;
			}

			if ( ! isset( $field['show_desc_tip'] ) ) {
				$this->args['fields'][ $k ]['show_desc_tip'] = true;
			}

			// Get value from opttions if options_form.
			if ( 'options_form' == $this->name && isset( $field['name'] ) && ! isset( $field['value'] ) ) {
				$this->args['fields'][ $k ]['value'] = ap_opt( $field['name'] );
			}
		}
	}

	/**
	 * Order fields
	 * @return void
	 * @since 2.0.1
	 */
	private function order_fields() {
		if ( ! isset( $this->args['fields'] ) ) {
			return;
		}

		$this->args['fields'] = ap_sort_array_by_order( $this->args['fields'] );
	}

	/**
	 * Build the form
	 * @return void
	 * @since 2.0.1
	 */
	public function build() {

		$this->form_head();
		$this->form_fields();

		if ( ! isset( $this->args['hide_footer'] ) || $this->args['hide_footer'] !== false ) {
			$this->hidden_fields();
			$this->form_footer();
		}
	}

	/**
	 * FORM element
	 * @return void
	 * @since 2.0.1
	 */
	private function form_head() {

		$attr = '';

		if ( ! empty( $this->args['attr'] ) ) {
			$attr .= $this->args['attr'];
		}

		if ( $this->args['is_ajaxified'] ) {
			$attr .= ' data-action="ap_ajax_form"';
		}

		if ( ! empty( $this->args['class'] ) ) {
			$attr .= ' class="'.$this->args['class'].'"';
		}

		ob_start();

		/**
		 * ACTION: ap_form_before_[form_name]
		 * action for hooking before form
		 * @since 2.0.1
		 */
		do_action( 'ap_form_before_'. $this->name );

		$this->output .= ob_get_clean();

		// Add enctype if form is multipart.
		$multipart = $this->args['multipart'] ? ' enctype="multipart/form-data"' : '';
		if ( ! isset( $this->args['hide_footer'] ) || $this->args['hide_footer'] !== false ) {
			$this->output .= '<form name="'.$this->args['name'].'" id="'.$this->args['name'].'" method="'.$this->args['method'].'" action="'.$this->args['action'].'"'.$attr.$multipart.'>';
		}
	}

	/**
	 * FORM footer
	 * @return void
	 * @since 2.0.1
	 */
	private function form_footer() {

		ob_start();

		/**
		 * ACTION: ap_form_bottom_[form_name]
		 * action for hooking captcha and extar fields
		 * @since 2.0.1
		 */
		do_action( 'ap_form_bottom_'. $this->name );
		$this->output .= ob_get_clean();

		$this->output .= '<button type="submit" class="ap-btn ap-btn-submit">'.$this->args['submit_button'].'</button>';
		
		if ( isset( $this->args['show_reset'] ) && true === $this->args['show_reset'] ) {
			$this->output .= '<input type="submit" name="reset" class="button ap-btn ap-btn-reset" value="'. __('Reset', 'anspress-question-answer') .'" />';
		}

		if ( isset( $this->args['show_cancel'] ) && true === $this->args['show_cancel'] ) {
			$this->output .= '<button type="button" class="ap-btn ap-btn-cancel">'.__( 'Cancel', 'anspress-question-answer' ).'</button>';
		}

		$this->output .= '</form>';
	}

	/**
	 * Add nonce field
	 */
	private function nonce() {
		$nonce_name = isset( $this->args['nonce_name'] ) ? $this->args['nonce_name'] : $this->name;
		$this->output .= wp_nonce_field( $nonce_name, '__nonce', true, false );
	}

	/**
	 * Form hidden fields
	 * @return void
	 * @since 2.0.1
	 */
	private function hidden_fields() {
		$this->output .= '<input type="hidden" name="ap_form_action" value="'.$this->name.'">';

		$this->nonce();
	}

	/**
	 * form field label
	 * @return void
	 * @since 2.0.1
	 */
	private function label() {

		if ( $this->field['label'] && ! $this->field['show_desc_tip'] ) {
			$this->output .= '<label class="ap-form-label" for="'. @$this->field['name'] .'">'. @$this->field['label'].'</label>';
		} elseif ( $this->field['label'] ) {
			$this->output .= '<label class="ap-form-label" for="'. @$this->field['name'] .'">'. @$this->field['label'];
			$this->desc();
			$this->output .= '</label>';
		}
	}

	/**
	 * Output placeholder attribute of current field
	 * @return string
	 * @since 2.0.1
	 */
	private function placeholder() {
		return ! empty( $this->field['placeholder'] ) ? ' placeholder="'.$this->field['placeholder'].'"' : '';
	}

	/**
	 * Output description of a form fields
	 * @return void
	 * @since 2.0.1
	 */
	private function desc() {
		if ( ! $this->field['show_desc_tip'] ) {
			$this->output .= ( ! empty( $this->field['desc'] ) ? '<p class="ap-field-desc">'.$this->field['desc'].'</p>' : '');
		} else {

			$this->output .= ( ! empty( $this->field['desc'] ) ? '<span class="ap-tip ap-field-desc" data-tipposition="right" title="'.esc_html( $this->field['desc'] ).'">?</span>' : '');
		}
	}

	/**
	 * Output text fields
	 * @param       array $field
	 * @return      void
	 * @since       2.0
	 */
	private function text_field($field = array(), $type = 'text') {

		if ( isset( $field['label'] ) ) {
			$this->label();
		}

		$placeholder = $this->placeholder();
		$autocomplete = isset( $field['autocomplete'] )  ? ' autocomplete="off"' : '';

		$this->output .= '<div class="ap-form-fields-in">';

		if ( ! isset( $field['repeatable'] ) || ! $field['repeatable'] ) {

			$this->output .= '<input id="'. @$field['name'] .'" type="'.$type.'" class="ap-form-control" value="'. @$field['value'] .'" name="'. @$field['name'] .'"'.$placeholder.' '. $this->attr( $field ) .$autocomplete.' />';

			if ( $type == 'password' ) {
				$this->output .= '<input id="'. @$field['name'] .'-1" type="'.$type.'" class="ap-form-control" value="'. @$field['value'] .'" name="'. @$field['name'] .'-1" placeholder="'.__( 'Repeat your password', 'anspress-question-answer' ).'" '. $this->attr( $field ) .$autocomplete.' />';
			}
		} else {
			if ( ! empty( $field['value'] ) && is_array( $field['value'] ) ) {
				$this->output .= '<div id="ap-repeat-c-'. @$field['name'] .'" class="ap-repeatbable-field">';
				foreach ( $field['value'] as $k => $rep_f ) {
					$this->output .= '<div id="ap_text_rep_'. @$field['name'] .'_'.$k.'" class="ap-repeatbable-field"><input id="'. @$field['name'] .'_'.$k.'" type="text" class="ap-form-control ap-repeatable-text" value="'. @$rep_f .'" name="'. @$field['name'] .'['.$k.']"'.$placeholder.' '. $this->attr( $field ) .$autocomplete.' />';
					$this->output .= '<button data-action="ap_delete_field" type="button" data-toggle="'. @$field['name'] .'_'.$k.'">'.__( 'Delete', 'anspress-question-answer' ).'</button>';
					$this->output .= '</div>';
				}
				$this->output .= '</div>';

				$this->output .= '<div id="ap-repeatbable-field-'. @$field['name'] .'" class="ap-reapt-field-copy">';
				$this->output .= '<div id="ap_text_rep_'. @$field['name'] .'_#" class="ap-repeatbable-field"><input id="'. @$field['name'] .'_#" type="text" class="ap-form-control ap-repeatable-text" value="" name="'. @$field['name'] .'[#]"'.$placeholder.' '. $this->attr( $field ) .$autocomplete.' />';
				$this->output .= '<button data-action="ap_delete_field" type="button" data-toggle="'. @$field['name'] .'_#">'.__( 'Delete', 'anspress-question-answer' ).'</button>';
				$this->output .= '</div></div>';
				$this->output .= '<button data-action="ap_add_field" type="button" data-field="ap-repeat-c-'. @$field['name'] .'" data-copy="ap-repeatbable-field-'. @$field['name'] .'">'.__( 'Add more', 'anspress-question-answer' ).'</button>';
			}
		}

		$this->error_messages();

		if ( ! $this->field['show_desc_tip'] ) {
			$this->desc();
		}

		$this->output .= '</div>';
	}

	/**
	 * Output text type="number"
	 * @param       array $field
	 * @return      void
	 * @since       2.0.0-alpha2
	 */
	private function number_field($field = array()) {

		if ( isset( $field['label'] ) ) {
			$this->label();
		}

		$placeholder = $this->placeholder();
		$autocomplete = isset( $field['autocomplete'] )  ? ' autocomplete="off"' : '';
		$this->output .= '<div class="ap-form-fields-in">';
		$this->output .= '<input id="'. @$field['name'] .'" type="number" class="ap-form-control" value="'. @$field['value'] .'" name="'. @$field['name'] .'"'.$placeholder.' '. $this->attr( $field ) .$autocomplete.' />';
		$this->error_messages();

		if ( ! $this->field['show_desc_tip'] ) {
			$this->desc();
		}

		$this->output .= '</div>';
	}

	/**
	 * Checkbox field
	 * @param  array $field
	 * @return void
	 * @since 2.0.1
	 */
	private function checkbox_field($field = array()) {
		if ( isset( $field['label'] ) ) {
			$this->label();
		}

		$this->output .= '<div class="ap-form-fields-in">';

		if ( ! empty( $field['desc'] ) ) {
			$this->output .= '<label for="'. @$field['name'] .'">';
		}

		$this->output .= '<input id="'. @$field['name'] .'" type="checkbox" class="ap-form-control" value="1" name="'. @$field['name'] .'" '.checked( (bool) $field['value'], true, false ).' '. $this->attr( $field ) .' />';

		// Hack for getting value of unchecked checkbox.
		$this->output .= '<input type="hidden" value="0" name="_hidden_'. @$field['name'] .'" />';

		if ( ! empty( $field['desc'] ) ) {
			$this->output .= @$field['desc'].'</label>';
		}

		$this->error_messages();

		$this->output .= '</div>';
	}

	/**
	 * Output select field options
	 * @param  array $field
	 * @return void
	 * @since 2.0.1
	 */
	private function select_options($field = array()) {

		foreach ( $field['options'] as $k => $opt ) {
			$this->output .= '<option value="'.$k.'" '.selected( $k, $field['value'], false ).'>'.$opt.'</option>'; }
	}

	/**
	 * Select fields
	 * @param  array $field
	 * @return void
	 * @since 2.0.1
	 */
	private function select_field($field = array()) {

		if ( isset( $field['label'] ) ) {
			$this->label(); }
		$this->output .= '<div class="ap-form-fields-in">';
		$this->output .= '<select id="'. @$field['name'] .'" class="ap-form-control" value="'. @$field['value'] .'" name="'. @$field['name'] .'" '. $this->attr( $field ) .'>';
		$this->output .= '<option value=""></option>';
		$this->select_options( $field );
		$this->output .= '</select>';
		$this->error_messages();
		if ( ! $this->field['show_desc_tip'] ) {
			$this->desc(); }
		$this->output .= '</div>';
	}

	/**
	 * Taxonomy select field
	 * @param  array $field
	 * @return void
	 * @since 2.0.1
	 */
	private function taxonomy_select_field($field = array()) {

		if ( isset( $field['label'] ) ) {
			$this->label(); }
		$this->output .= '<div class="ap-form-fields-in">';

		$taxonomies = wp_dropdown_categories( array( 'taxonomy' => $field['taxonomy'], 'orderby' => @$field['orderby'], 'hide_empty' => 0, 'hierarchical' => 1, 'selected' => @$field['value'], 'name' => @$field['name'], 'class' => 'ap-form-control', 'id' => @$field['name'], 'echo' => false ) );
		$this->output .= $taxonomies;

		$this->error_messages();
		if ( ! $this->field['show_desc_tip'] ) {
			$this->desc(); }
		$this->output .= '</div>';
	}

	/**
	 * Page select field
	 * @param  array $field
	 * @return void
	 * @since 2.0.0-alpha2
	 */
	private function page_select_field($field = array()) {

		if ( isset( $field['label'] ) ) {
			$this->label(); }
		$this->output .= '<div class="ap-form-fields-in">';
		$this->output .= wp_dropdown_pages( array( 'show_option_none' => __( 'Select a page', 'anspress-question-answer' ), 'selected' => @$field['value'], 'name' => @$field['name'], 'post_type' => 'page', 'echo' => false ) );
		$this->error_messages();
		if ( ! $this->field['show_desc_tip'] ) {
			$this->desc(); }
		$this->output .= '</div>';
	}

	/**
	 * Textarea field output.
	 * @param       array $field Field args.
	 * @return      void
	 * @since       2.0
	 */
	private function textarea_field($field = array()) {
		$field = wp_parse_args( $field, array(
			'name' => 'textarea',
			'rows' => '6',
			'value' => '',
		) );

		if ( isset( $field['label'] ) ) {
			$this->label();
		}

		$this->output .= '<div class="ap-form-fields-in">';
		$placeholder = $this->placeholder();

		$this->output .= '<textarea id="'. $field['name'] .'" rows="'. $field['rows'] .'" class="ap-form-control" name="'. $field['name'] .'"'.$placeholder.' '. $this->attr( $field ) .'>'. $field['value'] .'</textarea>';

		$this->error_messages();

		if ( ! $this->field['show_desc_tip'] ) {
			$this->desc();
		}

		$this->output .= '</div>';
	}

	/**
	 * Create wp_editor field
	 * @param  array $field
	 * @return void
	 * @since 2.0.1
	 */
	private function editor_field($field = array()) {
		if ( isset( $field['label'] ) ) {
			$this->label();
		}

		if ( $field['settings']['tinymce'] !== false ) {
			$field['settings']['tinymce'] = array(
				'content_css' => ap_get_theme_url( 'css/editor.css' ),
				'wp_autoresize_on' => true,
			);
		}

		/**
		 * FILTER: ap_pre_editor_settings
		 * Can be used to mody wp_editor settings
		 * @var array
		 * @since 2.0.1
		 */

		$settings = apply_filters( 'ap_pre_editor_settings', $field['settings'] );

		$this->output .= '<div class="ap-form-fields-in">';
		// Turn on the output buffer
		ob_start();
		echo '<div class="ap-editor">';
		wp_editor( $field['value'], $field['name'], $settings );
		echo '</div>';
		$this->output .= ob_get_clean();
		$this->error_messages();
		if ( ! $this->field['show_desc_tip'] ) {
			$this->desc();
		}
		$this->output .= '</div>';
	}
	/**
	 * For creating hidden input fields
	 * @param  array $field
	 * @return void
	 * @since 2.0.1
	 */
	private function hidden_field( $field = array() ) {
		$this->output .= '<input type="hidden" value="'. @$field['value'] .'" name="'. @$field['name'] .'" '. $this->attr( $field ) .' />';
	}

	private function custom_field($field = array()) {
		$this->output .= $field['html'];
	}

	private function attr( $field ) {
		return isset( $field['attr'] ) ? $field['attr'] : '';
	}

	/**
	 * Check if current field have any error
	 * @return boolean
	 * @since 2.0.1
	 */
	private function have_error() {
		if ( isset( $this->field['name'] ) && isset( $this->errors[$this->field['name']] ) ) {
			return true;
		}

		return false;
	}

	private function error_messages() {
		if ( isset( $this->errors[$this->field['name']] ) ) {
			$this->output .= '<div class="ap-form-error-messages">';

			foreach ( $this->errors[$this->field['name']] as $error ) {
				$this->output .= '<p class="ap-form-error-message">'. $error .'</p>';
			}

			$this->output .= '</div>';
		}
	}

	/**
	 * Out put all form fields based on on their type
	 * @return void
	 * @since  2.0
	 */
	private function form_fields() {
		/**
		 * FILTER: ap_pre_form_fields
		 * Provide filter to add or override form fields before output.
		 * @var array
		 * @since 2.0.1
		 */
		$this->args['fields'] = apply_filters( 'ap_pre_form_fields', $this->args['fields'] );

		foreach ( $this->args['fields'] as $field ) {

			$this->field = $field;

			$error_class = $this->have_error() ? ' ap-have-error' : '';

			if ( isset( $this->args['field_hidden'] ) && $this->args['field_hidden'] ) {
				if ( isset( $field['name'] ) && $field['type'] != 'hidden' && (@$field['visibility'] != 'me' || ( @$field['visibility'] == 'me' && $this->args['user_id'] == get_current_user_id())) ) {
					$nonce = wp_create_nonce( 'user_field_form_'.$field['name'].'_'.$this->args['user_id'] );

					$this->output .= '<div id="'.@$field['name'].'_field_wrap" class="clearfix ap-form-fields-wrap'.$error_class.'">';
						$this->output .= '<label class="ap-form-fields-wrap-label">'.@$field['label'].'</label>';
						$this->output .= '<div id="user_field_form_'.$field['name'].'" class="ap-form-fields-wrap-inner"><span>'.@$field['value'].'</apn></div>';
					$this->output .= '</div>';
				}
			} else {

				switch ( $field['type'] ) {

					case 'text':
						$this->output .= '<div class="ap-field-'.@$field['name'].' ap-form-fields'.$error_class.'">';
						$this->text_field( $field, 'text' );
						$this->output .= '</div>';
						break;

					case 'password':
						$this->output .= '<div class="ap-field-'.@$field['name'].' ap-form-fields'.$error_class.'">';
						$this->text_field( $field, 'password' );
						$this->output .= '</div>';
						break;

					case 'number':
						$this->output .= '<div class="ap-field-'.@$field['name'].' ap-form-fields'.$error_class.'">';
						$this->number_field( $field );
						$this->output .= '</div>';
						break;

					case 'checkbox':
						$this->output .= '<div class="ap-field-'.@$field['name'].' ap-form-fields'.$error_class.'">';
						$this->checkbox_field( $field );
						$this->output .= '</div>';
						break;

					case 'select':
						$this->output .= '<div class="ap-field-'.@$field['name'].' ap-form-fields'.$error_class.'">';
						$this->select_field( $field );
						$this->output .= '</div>';
						break;

					case 'taxonomy_select':
						$this->output .= '<div class="ap-field-'.@$field['name'].' ap-form-fields'.$error_class.'">';
						$this->taxonomy_select_field( $field );
						$this->output .= '</div>';
						break;

					case 'page_select':
						$this->output .= '<div class="ap-field-'.@$field['name'].' ap-form-fields'.$error_class.'">';
						$this->page_select_field( $field );
						$this->output .= '</div>';
						break;

					case 'textarea':
						$this->output .= '<div class="ap-field-'.@$field['name'].' ap-form-fields'.$error_class.'">';
						$this->textarea_field( $field );
						$this->output .= '</div>';
						break;

					case 'editor':
						$this->output .= '<div class="ap-field-'.@$field['name'].' ap-form-fields'.$error_class.'">';
						$this->editor_field( $field );
						$this->output .= '</div>';
						break;

					case 'hidden':
						$this->hidden_field( $field );
						break;

					case 'custom':
						$this->custom_field( $field );
						break;

					default:
						/**
						 * FILTER: ap_form_fields_[type]
						 * filter for custom form field type
						 */
						$this->output .= apply_filters( 'ap_form_fields_'.$field['type'],  $field );
						break;
				}
			}
		}
	}

	/**
	 * Output form
	 * @return string
	 * @since 2.0.1
	 */
	public function get_form() {

		if ( empty( $this->args['fields'] ) ) {
			return __( 'No fields found', 'anspress-question-answer' ); }

		$this->build();

		return $this->output;
	}

}
