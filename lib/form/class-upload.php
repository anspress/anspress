<?php
/**
 * AnsPress Upload type field object.
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
 * AnsPress Upload type field object.
 *
 * @since 4.1.0
 */
class Upload extends Field {
	/**
	 * The field type.
	 *
	 * @var string
	 */
	public $type = 'upload';

	/**
	 * Is multiple upload field.
	 *
	 * @var boolean
	 */
	public $multiple_upload = false;

	/**
	 * Is files uploaded.
	 *
	 * @var boolean
	 * @since 4.1.0
	 */
	public $uploaded = false;

	/**
	 * The uploaded files.
	 *
	 * @var boolean|array
	 */
	public $uploaded_files = false;

	/**
	 * Initialize the class.
	 *
	 * @param string $form_name Name of parent form.
	 * @param string $name      Name of field.
	 * @param array  $args      Field arguments.
	 */
	public function __construct( $form_name, $name, $args ) {

		parent::__construct( $form_name, $name, $args );

		// Do not add array in field name.
		$this->field_name = $this->id();

		$this->multiple_upload = $this->args['upload_options']['multiple'];

		// Add array to field name if multiple file allowed.
		if ( true === $this->multiple_upload ) {
			$this->field_name = $this->field_name . '[]';
		}

		// Make sure field is sanitized.
		$this->sanitize_cb = array_merge( [ 'upload' ], $this->sanitize_cb );
		$this->validate_cb = array_merge( [ 'upload' ], $this->validate_cb );
	}

	/**
	 * Prepare field.
	 *
	 * @return void
	 */
	protected function prepare() {
		$this->args = wp_parse_args( $this->args, array(
			'label' => __( 'AnsPress Upload Field', 'anspress-question-answer' ),
			'upload_options' => [],
		) );

		$this->args['upload_options'] = wp_parse_args( $this->args['upload_options'], array(
			'allowed_mimes'   => ap_allowed_mimes(),
			'max_files'       => 1,
			'multiple'        => false,
			'label_deny_type' => __( 'This file type is not allowed to upload.', 'anspress-question-answer' ),
		));

		if ( ! isset( $this->args['upload_options']['label_max_added'] ) ) {
			$this->args['upload_options']['label_max_added'] = sprintf(
				// Translators: %d contains maximum files allowed to upload.
				__( 'You cannot add more then %d files', 'anspress-question-answer' ),
				$this->args['upload_options']['max_files']
			);
		}

		// Call parent prepare().
		parent::prepare();
	}

	/**
	 * Arguments passed to sanitization callback.
	 *
	 * @param mixed $val Value to sanitize.
	 * @return array
	 */
	protected function sanitize_cb_args( $val ) {
		return [ $val, $this->get( 'upload_options' ) ];
	}

	/**
	 * Format $_FILES field which have multiple files.
	 *
	 * @param array $file_post single $_FILE field.
	 * @return array
	 */
	private function format_multiple_files( $file_post ) {
		if ( ! is_array( $file_post['name'] ) ) {
			return $file_post;
		}

		$file_ary   = array();
		$file_count = count( $file_post['name'] );
		$file_keys  = array_keys( $file_post );

		for ( $i = 0; $i < $file_count; $i++ ) {
			foreach ( $file_keys as $key ) {
				$file_ary[ $i ][ $key ] = $file_post[ $key ][ $i ];
			}
		}

		return array_filter( $file_ary, function( $a ) {
			return ! empty( $a['name'] );
		});
	}

	/**
	 * Get POST (unsafe) value of a field.
	 *
	 * @return mixed
	 */
	public function unsafe_value() {
		$request_value = $this->get( $this->id( $this->field_name ), null, $_FILES );
		if ( $request_value ) {
			return $this->format_multiple_files( $request_value );
		}
	}

	/**
	 * Field markup.
	 *
	 * @return void
	 */
	public function field_markup() {
		parent::field_markup();

		$args = $this->get( 'upload_options' );
		$allowed_ext = '.' . str_replace( '|', ',.', implode( ',.', array_keys( $args['allowed_mimes'] ) ) );
		unset( $args['allowed_mimes'] );

		$medias = get_posts( [
			'post_type'   => 'attachment',
			'title'       => '_ap_temp_media',
			'post_author' => get_current_user_id(),
		]);

		// Show temporary images uploaded.
		if ( $medias ) {
			$this->add_html( '<div class="ap-upload-list">' );

			foreach ( $medias as $media ) {
				$this->add_html( '<div><span class="ext">' . pathinfo( $media->guid, PATHINFO_EXTENSION ) . '</span>' . basename( $media->guid ) . '<span class="size">' . size_format( filesize( get_attached_file( $media->ID ) ), 2 ) . '</span></div>' );
			}

			$this->add_html( '</div>' );
		}

		$this->add_html( '<div class="ap-upload-c">' );
		$this->add_html( '<input type="file" data-upload="' . esc_js( wp_json_encode( $args ) ) . '"' . $this->common_attr() . $this->custom_attr() . ( $args['multiple'] ? ' multiple="multiple"' : '' ) . ' accept="' . esc_attr( $allowed_ext ) . '" />' );
		$this->add_html( '<span>' . esc_attr__( 'Browse file(s)', 'anspress-question-answer' ) . '</span>' );
		$this->add_html( '<b>' . esc_html( number_format_i18n( 0 ) ) . '</b>' );
		$this->add_html( '</div>' );

		/** This action is documented in lib/form/class-input.php */
		do_action_ref_array( 'ap_after_field_markup', [ &$this ] );
	}

	/**
	 * Replace all dummy images found in editor type field.
	 *
	 * @param string      $string        Content where to replace images.
	 * @param array|false $allowed_files Pass array of allowed file names .
	 * @return string     Replaced string.
	 */
	public function replace_temp_image( $string, $allowed_files = false ) {
		$allowed_files = array_map( 'sanitize_file_name', $allowed_files );

		// Check for allowed files.
		$new_files = [];
		if ( false !== $allowed_files && $this->value() ) {
			foreach ( $this->value() as $k => $file ) {
				if ( in_array( $file['name'], $allowed_files, true ) ) {
					$new_files[] = $file;
				}
			}
			$this->value = $new_files;
		}

		if ( false === $this->uploaded ) {
			$this->save_uploads();
		}

		return preg_replace_callback( '/({{apimage (.*?)}})/', [ $this, 'file_name_search_replace' ], $string );
	}

	/**
	 * Callback for preg replace callback for replacing temporary
	 * images in a string.
	 *
	 * @param array $m Matching tags.
	 * @return string
	 */
	private function file_name_search_replace( $m ) {
		preg_match_all( '/"([^"]*)"/', $m[2], $attrs, PREG_SET_ORDER, 0 );
		$sanitized_filename = sanitize_file_name( $attrs[0][1] );

		if ( ! empty( $this->uploaded_files[ $sanitized_filename ] ) ) {
			$url = wp_get_attachment_url( $this->uploaded_files[ $sanitized_filename ] );
			$alt = ! empty( $attrs[1][1] ) ? ' alt="' . esc_attr( $attrs[1][1] ) . '"' : '';
			return '<img src="' . $url . '"' . $alt . ' />';
		}
	}

	/**
	 * Upload a file.
	 *
	 * @param array $file File array.
	 * @return void
	 * @since 4.1.0
	 * @since 4.1.5 Fixed: custom mimes are not working.
	 */
	private function upload_file( $file ) {
		$id = ap_upload_user_file( $file, true, '', $this->get( 'upload_options.allowed_mimes' ) );
		if ( is_wp_error( $id ) ) {
			$this->add_error( $id->get_error_code(), $id->get_error_message() );
		} else {
			$this->uploaded_files[ sanitize_file_name( $file['name'] ) ] = $id;
		}
	}

	/**
	 * Save all uploads to server.
	 *
	 * @return void
	 */
	public function save_uploads() {
		if ( $this->have_errors() || true === $this->uploaded ) {
			return;
		}

		$value = $this->value();

		if ( $this->get( 'upload_options.multiple', false ) ) {
			foreach ( (array) $value as $file ) {
				$this->upload_file( $file );
			}
		} else {
			$this->upload_file( $value );
		}

		$this->value = $this->uploaded_files;
		$this->uploaded = true;
	}

	/**
	 * Set post parent of uploaded files.
	 *
	 * @param array $args Array of arguments.
	 * @return void
	 */
	public function after_save( $args = [] ) {
		parent::after_save();

		if ( empty( $args ) || empty( $args['post_id'] ) ) {
			return;
		}

		if ( ! empty( $this->uploaded_files ) ) {
			foreach ( $this->uploaded_files as $id ) {
				ap_set_media_post_parent( $id, $args['post_id'] );
			}
		}
	}
}
