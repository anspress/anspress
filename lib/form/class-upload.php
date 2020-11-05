<?php
/**
 * AnsPress Upload type field object.
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
	 * Check weather this is async upload.
	 *
	 * @var boolean
	 */
	public $async_upload = false;

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
		$this->args = wp_parse_args(
			$this->args, array(
				'label'          => __( 'AnsPress Upload Field', 'anspress-question-answer' ),
				'upload_options' => [],
				'browse_label'   => __( 'Select file(s) to upload', 'anspress-question-answer' ),
			)
		);

		$this->args['upload_options'] = wp_parse_args(
			$this->args['upload_options'], array(
				'allowed_mimes'   => ap_allowed_mimes(),
				'max_files'       => 1,
				'multiple'        => false,
				'label_deny_type' => __( 'This file type is not allowed to upload.', 'anspress-question-answer' ),
				'async_upload'    => false,
			)
		);

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
	 * Order of HTML markup.
	 *
	 * @return void
	 * @since 4.1.8
	 */
	protected function html_order() {
		if ( empty( $this->args['output_order'] ) ) {
			$this->output_order = [ 'wrapper_start', 'label', 'field_wrap_start', 'errors', 'field_markup', 'desc', 'file_list', 'field_wrap_end', 'wrapper_end' ];
		} else {
			$this->output_order = $this->args['output_order'];
		}
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

		return array_filter(
			$file_ary, function( $a ) {
				return ! empty( $a['name'] );
			}
		);
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
	 * Show the list of previously attached media.
	 *
	 * @return void
	 * @since 4.1.8
	 */
	public function file_list() {
		$medias = get_posts( array(
			'post_type'   => 'attachment',
			'title'       => '_ap_temp_media',
			'post_author' => get_current_user_id(),
		) );

		// Show temporary images uploaded.
		$this->add_html( '<div class="ap-upload-list">' );

		if ( $medias ) {
			foreach ( $medias as $media ) {
				$this->add_html( '<div><span class="ext">' . pathinfo( $media->guid, PATHINFO_EXTENSION ) . '</span>' . basename( $media->guid ) . '<span class="size">' . size_format( filesize( get_attached_file( $media->ID ) ), 2 ) . '</span></div>' );
			}
		}

		$this->add_html( '</div>' );
	}

	/**
	 * Return arguments for used by JS.
	 *
	 * @return string JSON
	 * @since 4.1.8
	 */
	public function js_args() {
		$args        = $this->get( 'upload_options' );
		$allowed_ext = '.' . str_replace( '|', ',.', implode( ',.', array_keys( $args['allowed_mimes'] ) ) );

		unset( $args['allowed_mimes'] );
		$args['field_name'] = $this->original_name;
		$args['form_name'] = $this->form_name;

		return wp_json_encode( $args );
	}

	/**
	 * Field markup.
	 *
	 * @return void
	 */
	public function field_markup() {
		parent::field_markup();

		$args        = $this->get( 'upload_options' );
		$allowed_ext = '.' . str_replace( '|', ',.', implode( ',.', array_keys( $args['allowed_mimes'] ) ) );

		$this->add_html( '<div class="ap-upload-c">' );
		$this->add_html( '<input type="file"' );
		$this->add_html( 'data-upload="' . esc_js( $this->js_args() ) . '"' . $this->common_attr() );
		$this->add_html( $this->custom_attr() );
		$this->add_html( $args['multiple'] ? ' multiple="multiple"' : '' );
		$this->add_html( ' accept="' . esc_attr( $allowed_ext ) . '" ' );

		$this->add_html( ' />' );
		//$this->add_html( '<span>' . $this->args['browse_label'] . '</span>' );
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
		$id = $this->upload( $file );

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

		// Return if value is empty.
		if ( empty( $value ) ) {
			return;
		}

		if ( $this->get( 'upload_options.multiple', false ) ) {
			foreach ( (array) $value as $file ) {
				$this->upload_file( $file );
			}
		} else {
			$this->upload_file( $value );
		}

		$this->value    = $this->uploaded_files;
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

	/**
	 * Upload file and store it in temporary directory.
	 *
	 * Copied directly from WordPress Core. Only difference is upload directory.
	 * All files were uploaded to `anspress-temp` directory and it need to moved
	 * manually. All files older then 2 hours are deleted from `anspress-temp` directory.
	 *
	 * @return void
	 * @since 4.1.8
	 */
	private function upload( $file ) {
		// If there is error in file then return.
		if ( isset( $file['error'] ) && ! is_numeric( $file['error'] ) && $file['error'] ) {
			return new \WP_Error( 'upload_file_error', $file['error'] );
		}

		// Courtesy of php.net, the strings that describe the error indicated in $_FILES[{form field}]['error'].
		$upload_error_strings = array(
			false,
			__( 'The uploaded file exceeds the upload_max_filesize directive in php.ini.' ),
			__( 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.' ),
			__( 'The uploaded file was only partially uploaded.' ),
			__( 'No file was uploaded.' ),
			'',
			__( 'Missing a temporary folder.' ),
			__( 'Failed to write file to disk.' ),
			__( 'File upload stopped by extension.' ),
		);

		// Check error.
		if ( isset( $file['error'] ) && $file['error'] > 0 ) {
			return new \WP_Error( 'upload_file_size', $upload_error_strings[ $file['error'] ] );
		}

		$file_size = $file['size'];

		// A non-empty file will pass this test.
		if ( ! ( $file_size > 0 ) ) {
			return new \WP_Error( 'upload_file_size', __( 'File is empty. Please upload something more substantial.' ) );
		}

		// Check file size.
		if ( $file_size > ap_opt( 'max_upload_size' ) ) {
			return new \WP_Error( 'upload_file_size', __( 'File is bigger than the allowed limit.' ) );
		}

		// Check file uploaded using proper method.
		if ( true !== is_uploaded_file( $file['tmp_name'] ) ) {
			return new \WP_Error( 'upload_file_failed', __( 'Specified file failed upload test.' ) );
		}

		$mimes = $this->get( 'upload_options.allowed_mimes' );
		$mimes = ! empty( $mimes ) ? $mimes : false;

		// A correct MIME type will pass this test.
		$wp_filetype     = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], $mimes );
		$ext             = empty( $wp_filetype['ext'] ) ? '' : $wp_filetype['ext'];
		$type            = empty( $wp_filetype['type'] ) ? '' : $wp_filetype['type'];
		$proper_filename = empty( $wp_filetype['proper_filename'] ) ? '' : $wp_filetype['proper_filename'];

		// Check to see if wp_check_filetype_and_ext() determined the filename was incorrect
		if ( $proper_filename ) {
			$file['name'] = $proper_filename;
		}

		if ( ! $type || !$ext ) {
			return new \WP_Error( 'upload_file_ext', __( 'Sorry, this file type is not permitted for security reasons.' ) );
		}

		if ( ! $type ) {
			$type = $file['type'];
		}

		$uploads = wp_upload_dir();

		/**
		 * A writable uploads dir will pass this test.
		 */
		if ( false !== $uploads['error'] ) {
			return new \WP_Error( 'upload_file_dir', $uploads['error'] );
		}

		$temp_dir = $uploads['basedir'] . '/anspress-temp/';

		// Make dir if not exists.
		if ( ! file_exists( $temp_dir ) ) {
			mkdir( $temp_dir );
		}

		$sha           = sha1_file( $file['tmp_name'] );
		$user_id       = get_current_user_id();
		$new_file_name = "{$sha}_$user_id.$ext";
		$new_file      = $temp_dir . "$new_file_name";

		$move_new_file = move_uploaded_file( $file['tmp_name'], $new_file );

		// Return if unable to move file.
		if ( false === $move_new_file ) {
			return new \WP_Error( 'upload_file_move', 'The uploaded file could not be moved' );
		}

		// Set correct file permissions.
		$stat = stat( dirname( $new_file ) );
		$perms = $stat['mode'] & 0000666;
		@ chmod( $new_file, $perms );

		return $new_file_name;
	}

	/**
	 * Return url of all uploaded files.
	 *
	 * @return array
	 * @since 4.1.8
	 */
	public function get_uploaded_files_url() {
		if ( true !== $this->uploaded || empty( $this->uploaded_files ) ) {
			return [];
		}

		$uploads = wp_upload_dir();
		$temp_dir = $uploads['baseurl'] . '/anspress-temp/';

		$ret = [];
		foreach ( $this->uploaded_files as $old => $new ) {
			$ret[ $old ] = $temp_dir . $new;
		}

		return $ret;
	}
}
