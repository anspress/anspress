<?php
/**
 * AnsPress Validation object.
 *
 * @package    AnsPress
 * @subpackage Form
 * @since      4.1.0
 * @author     Rahul Aryan<support@anspress.io>
 * @copyright  Copyright (c) 2017, Rahul Aryan
 * @license    http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace AnsPress\Form;

/**
 * The validation class.
 *
 * @since 4.1.0
 */
class Validate {
	/**
	 * Sanitize field value using sanitize_text_field.
	 *
	 * @param  null|string|array $value String or array to sanitize.
	 * @return null|string|array
	 */
	public static function sanitize_text_field( $value = null ) {
		if ( null !== $value ) {
			return is_array( $value ) ? array_map( 'sanitize_text_field', $value ) : sanitize_text_field( $value );
		}
	}

	/**
	 * Sanitize field value using sanitize_title.
	 *
	 * @param  null|string|array $value String or array to sanitize.
	 * @return null|string|array
	 */
	public static function sanitize_title( $value = null ) {
		if ( null !== $value ) {
			return is_array( $value ) ? array_map( 'sanitize_title', $value ) : sanitize_title( $value );
		}
	}

	/**
	 * Remove empty array items.
	 *
	 * @param  null|array $value Array to sanitize.
	 * @return array|null
	 */
	public static function sanitize_array_remove_empty( $value = null ) {
		if ( null !== $value && is_array( $value ) ) {
			return array_filter( $value );
		}
	}

	/**
	 * Sanitize field value using wp_kses.
	 *
	 * @param  null|string $value String to sanitize.
	 * @return null|string
	 */
	public static function sanitize_wp_kses( $value = null ) {
		if ( null !== $value ) {
			return wp_kses( $value, ap_form_allowed_tags() );
		}
	}

	/**
	 * Sanitize field value using absint.
	 *
	 * @param  null|string $value String to sanitize.
	 * @return null|string
	 */
	public static function sanitize_absint( $value = null ) {
		if ( ! empty( $value ) ) {
			return absint( $value );
		}
	}

	/**
	 * Sanitize field value using intval.
	 *
	 * @param  null|string $value String to sanitize.
	 * @return null|string Return integer value.
	 */
	public static function sanitize_intval( $value = null ) {
		if ( ! empty( $value ) ) {
			return intval( $value );
		}
	}

	/**
	 * Sanitize field value and return only boolean.
	 *
	 * @param  null|string $value String to sanitize.
	 * @return null|boolean Return boolean value.
	 */
	public static function sanitize_boolean( $value = null ) {
		if ( ! empty( $value ) ) {
			return (bool) $value;
		}
	}

	/**
	 * Sanitize field value and return only boolean.
	 *
	 * @param  null|array $value Array to sanitize.
	 * @return null|boolean Return boolean value.
	 */
	public static function sanitize_array_map_boolean( $value = null ) {
		if ( ! empty( $value ) ) {
			return array_map( [ __CLASS__, 'sanitize_boolean' ], $value );
		}
	}

	/**
	 * Sanitize field value and return HTML escaped value.
	 *
	 * @param  null|string $value String to sanitize.
	 * @return null|string Returns HTML escaped string.
	 */
	public static function sanitize_esc_html( $value = null ) {
		if ( ! empty( $value ) ) {
			return esc_html( $value );
		}
	}

	/**
	 * Sanitize field value and return sanitized url.
	 *
	 * @param  null|string $value String to sanitize.
	 * @return null|string
	 */
	public static function sanitize_email( $value = null ) {
		if ( ! empty( $value ) ) {
			return sanitize_email( $value );
		}
	}

	/**
	 * Sanitize field value and return sanitized url.
	 *
	 * @param  null|string $value String to sanitize.
	 * @return null|string
	 */
	public static function sanitize_esc_url( $value = null ) {
		if ( ! empty( $value ) ) {
			return esc_url( $value );
		}
	}

	/**
	 * Sanitize description field.
	 *
	 * Remove more, encode contents of code and pre tag.
	 * Replace square brackets so that shotcode dont get rendered.
	 *
	 * @param null|string $value String to sanitize.
	 *
	 * @return null|string
	 */
	public static function sanitize_description( $value = null ) {
		if ( ! empty( $value ) ) {
			$value = str_replace( '<!--more-->', '', $value );
			$value = preg_replace_callback( '/<pre.*?>(.*?)<\/pre>/imsu', [ __CLASS__, 'pre_content' ], $value );
			$value = preg_replace_callback( '/<code.*?>(.*?)<\/code>/imsu', [ __CLASS__, 'code_content' ], $value );
			$value = str_replace( [ '[', ']' ], [ '&#91;', '&#93;' ], $value );

			return $value;
		}
	}

	/**
	 * Callback for replacing contents inside <pre> tag.
	 *
	 * @see `AP_Form::sanitize_description`
	 *
	 * @param array $matches Matches.
	 * @return string
	 */
	private static function pre_content( $matches ) {
		return '<pre>' . esc_html( $matches[1] ) . '</pre>';
	}

	/**
	 * Callback for replacing contents inside <code> tag.
	 *
	 * @see `AP_Form::sanitize_description`
	 *
	 * @param array $matches Matches.
	 * @return string
	 */
	private static function code_content( $matches ) {
		return '<code>' . esc_html( $matches[1] ) . '</code>';
	}

	/**
	 * Sanitize tags field.
	 *
	 * Sanitize keys and values. Exclude new tags if not allowed.
	 * Only include numbers of max tags allowed in field option.
	 *
	 * @param null|array $value      Arrays of tags to sanitize.
	 * @param array      $options    Tags options.
	 * @param array      $js_options Tags JavaScript options.
	 *
	 * @return array|null Return sanitized tag array.
	 */
	public static function sanitize_tags_field( $value = null, $options, $js_options ) {
		if ( ! empty( $value ) ) {
			$i = 0;
			$sanitized = [];
			foreach ( (array) $value as $tag_slug => $tag_label ) {
				$i++;

				// Include new tags only if add_tag option is true.
				if ( true === $js_options['add_tag'] || ( false === $js_options['add_tag'] && in_array( $tag_slug, array_keys( $options ), true ) ) ) {
					$sanitized[ sanitize_title( $tag_slug ) ] = sanitize_text_field( $tag_label );

					// Only output max numbers of tags allowed.
					if ( $js_options['max_tags'] === $i ) {
						break;
					}
				}
			}

			return $sanitized;
		}
	}

	/**
	 * Sanitize upload field.
	 *
	 * @param  null|array $value          Array of uploads.
	 * @param  array      $upload_options Upload options.
	 * @return null|array
	 */
	public static function sanitize_upload( $value = null, $upload_options ) {
		if ( ! empty( $value ) && is_array( $value ) ) {
			if ( true === $upload_options['multiple'] && wp_is_numeric_array( $value ) ) {
				$value = array_slice( $value, 0, $upload_options['max_files'] );

				foreach ( $value as $key => $file ) {
					$value[ $key ]['error'] = (int) $file['error'];
					$value[ $key ]['name']  = sanitize_file_name( $file['name'] );
				}

				return $value;
			} elseif ( false === $upload_options['multiple'] && ! wp_is_numeric_array( $value ) ) {
				$value['error'] = (int) $value['error'];
				$value['name']  = sanitize_file_name( $value['name'] );

				return $value;
			}
		}
	}

	/**
	 * Validate `required` field.
	 *
	 * @param object $field Instance of @see `AP_Field` object.
	 * @return void
	 */
	public static function validate_required( $field ) {
		if ( '' === $field->value() || is_null( $field->value() ) ) {
			$field->add_error( 'required', sprintf(
				// Translators: placeholder contain field label.
				__( '%s field is required.', 'anspress-question-answer' ),
				$field->get( 'label' )
			) );
		}
	}

	/**
	 * Validate `is_email` field.
	 *
	 * @param object $field Instance of @see `AP_Field` object.
	 * @return void
	 */
	public static function validate_is_email( $field ) {
		if ( ! empty( $field->value() ) && ! is_email( $field->value() ) ) {
			$field->add_error( 'is-email', sprintf(
				// Translators: placeholder contain field label.
				__( 'Value provided in field %s is not a valid email.', 'anspress-question-answer' ),
				$field->get( 'label' )
			) );
		}
	}

	/**
	 * Validate `is_url` field.
	 *
	 * @param object $field Instance of @see `AP_Field` object.
	 * @return void
	 */
	public static function validate_is_url( $field ) {
		if ( ! empty( $field->unsafe_value() ) && false === filter_var( $field->unsafe_value(), FILTER_VALIDATE_URL ) ) {
			$field->add_error( 'is-url', sprintf(
				// Translators: placeholder contain field label.
				__( 'Value provided in field %s is not a valid URL.', 'anspress-question-answer' ),
				$field->get( 'label' )
			) );
		}
	}

	/**
	 * Validate `is_url` field.
	 *
	 * @param object $field Instance of @see `AP_Field` object.
	 * @return void
	 */
	public static function validate_is_numeric( $field ) {
		if ( ! empty( $field->unsafe_value() ) && ! is_numeric( $field->unsafe_value() ) ) {
			$field->add_error( 'is-numeric', sprintf(
				// Translators: placeholder contain field label.
				__( 'Value provided in field %s is not numeric.', 'anspress-question-answer' ),
				$field->get( 'label' )
			) );
		}
	}

	/**
	 * Validate if length of a string is at least as defined.
	 *
	 * @param object $field Instance of @see `AP_Field` object.
	 * @return void
	 */
	public static function validate_min_string_length( $field ) {
		$value = $field->value();

		if ( ! empty( $value ) && $field->get( 'min_length' ) ) {
			$min_length = $field->get( 'min_length', 0 );
			$value = wp_strip_all_tags( $value );
			$value = html_entity_decode( $value, ENT_XML1, 'UTF-8' );

			if ( mb_strlen( $value, 'utf-8' ) < $min_length ) {
				$field->add_error( 'min-string-length', sprintf(
					// Translators: placeholder contain field label.
					__( 'Value provided in field %1$s must be at least %2$d characters long.', 'anspress-question-answer' ),
					$field->get( 'label' ), $min_length
				) );
			}
		}
	}

	/**
	 * Validate if length of a string is at least as defined.
	 *
	 * @param object $field Instance of @see `AP_Field` object.
	 * @return void
	 */
	public static function validate_max_string_length( $field ) {
		$value = $field->value();

		if ( ! empty( $value ) && $field->get( 'max_length' ) ) {
			$max_length = $field->get( 'max_length', 10 );
			$value = wp_strip_all_tags( $value );
			$value = html_entity_decode( $value, ENT_XML1, 'UTF-8' );

			if ( mb_strlen( $value, 'utf-8' ) > $max_length ) {
				$field->add_error( 'max-string-length', sprintf(
					// Translators: placeholder contain field label.
					__( 'Value provided in field %1$s must not exceeds %2$d characters.', 'anspress-question-answer' ),
					$field->get( 'label' ), $max_length
				) );
			}
		} // End if().
	}

	/**
	 * Validate if field is array.
	 *
	 * @param object $field Instance of @see `AP_Field` object.
	 * @return void
	 */
	public static function validate_is_array( $field ) {
		$value = $field->value();

		if ( ! empty( $value ) && ! is_array( $value ) ) {
			$field->add_error( 'is-array', sprintf(
				// Translators: placeholder contain field label.
				__( 'Value provided in field %s is not an array.', 'anspress-question-answer' ),
				$field->get( 'label' )
			) );
		}
	}

	/**
	 * Validate if there are minimum items in an array.
	 *
	 * @param object $field Instance of @see `AP_Field` object.
	 * @return void
	 */
	public static function validate_array_min( $field ) {
		$min_arr = $field->get( 'array_min', 0 );
		$value = $field->value();

		if ( empty( $value ) || ! is_array( $value ) || $min_arr > count( $value ) ) {
			$field->add_error( 'array-min', sprintf(
				// Translators: placeholder contain field label.
				__( 'Minimum %1$d values are required in field %2$s.', 'anspress-question-answer' ),
				$min_arr, $field->get( 'label' )
			) );
		}
	}

	/**
	 * Validate if there are minimum items in an array.
	 *
	 * @param object $field Instance of @see `AP_Field` object.
	 * @return void
	 */
	public static function validate_array_max( $field ) {
		$max_arr = (int) $field->get( 'array_max', 0 );
		$value   = $field->value();

		if ( ! empty( $value ) && count( $value ) > $max_arr ) {
			$field->add_error( 'array-max', sprintf(
				// Translators: placeholder contain field label.
				__( 'Maximum values allowed in field %2$s is %1$d.', 'anspress-question-answer' ),
				$max_arr, $field->get( 'label' )
			) );
		}
	}

	/**
	 * Check if a upload field's value array have error.
	 *
	 * @param object $field Instance of @see `AP_Field` object.
	 * @return false|string Return error message if exists.
	 */
	private static function file_have_error( $field ) {
		$args  = $field->get( 'upload_options' );
		$value = $field->value();

		$errors = array(
			0 => __( 'There is no error, the file uploaded with success', 'anspress-question-answer' ),
			1 => __( 'The uploaded file exceeds the upload_max_filesize directive in php.ini', 'anspress-question-answer' ),
			2 => __( 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form', 'anspress-question-answer' ),
			3 => __( 'The uploaded file was only partially uploaded', 'anspress-question-answer' ),
			4 => __( 'No file was uploaded', 'anspress-question-answer' ),
			6 => __( 'Missing a temporary folder', 'anspress-question-answer' ),
			7 => __( 'Failed to write file to disk.', 'anspress-question-answer' ),
			8 => __( 'A PHP extension stopped the file upload.', 'anspress-question-answer' ),
		);

		$have_error = false;

		if ( true === $args['multiple'] && wp_is_numeric_array( $value ) ) {
			foreach ( $value as $key => $file ) {
				if ( 0 !== $file['error'] ) {
					$have_error = $errors[ $file['error'] ];
				}
			}
		} else {
			if ( 0 !== $value['error'] ) {
				$have_error = $errors[ $value['error'] ];
			}
		}

		return $have_error;
	}

	/**
	 * Check if upload field file size is greater then allowed.
	 *
	 * @param object $field Instance of @see `AP_Field` object.
	 * @return false
	 */
	private static function file_size_error( $field ) {
		$args       = $field->get( 'upload_options' );
		$value      = $field->value();
		$have_error = false;
		$is_numeric = wp_is_numeric_array( $value );

		if ( true === $args['multiple'] && $is_numeric ) {
			foreach ( $value as $key => $file ) {
				if ( $file['size'] > ap_opt( 'max_upload_size' ) ) {
					$have_error = true;
				}
			}
		} elseif ( ! $is_numeric && $value['size'] > ap_opt( 'max_upload_size' ) ) {
			$have_error = true;
		}

		return $have_error;
	}

	/**
	 * Check file array contain allowed mime types.
	 *
	 * @param object $field Instance of @see `AP_Field` object.
	 * @return boolean
	 */
	private static function file_valid_type( $field ) {
		$args       = $field->get( 'upload_options' );
		$value      = $field->value();
		$have_error = true;
		$is_numeric = wp_is_numeric_array( $value );

		if ( true === $args['multiple'] && $is_numeric ) {
			foreach ( $value as $key => $file ) {
				$actual_mime = mime_content_type( $file['tmp_name'] );

				if ( in_array( $actual_mime, $args['allowed_mimes'], true ) ) {
					$have_error = false;
				}
			}
		} elseif ( ! $is_numeric ) {
			$actual_mime = mime_content_type( $value['tmp_name'] );

			if ( in_array( $actual_mime, $args['allowed_mimes'], true ) ) {
				$have_error = false;
			}
		}

		return $have_error;
	}

	/**
	 * Validate an upload field.
	 *
	 * @param object $field Instance of @see `AP_Field` object.
	 * @return void
	 */
	public static function validate_upload( $field ) {
		$args  = $field->get( 'upload_options' );
		$value = $field->value();

		if ( ! empty( $value ) ) {

			// Check if user have permission to upload files.
			if ( ! ap_user_can_upload() ) {
				$field->add_error( 'deny-upload', __( 'You are not allowed to upload file(s)', 'anspress-question-answer' ) );
			}

			$is_numeric = wp_is_numeric_array( $value );
			if ( ( false === $args['multiple'] && $is_numeric ) ||
					 ( true === $args['multiple'] && count( $value ) > $args['max_files'] ) ) {
				$field->add_error( 'max-uploads', sprintf(
					// Translators: %1$d contain maximum files user can upload, %2$s contain label of field.
					__( 'You cannot upload more than %1$d file in field %2$s', 'anspress-question-answer' ),  $args['max_files'], $field->get( 'label' )
				) );
			}

			// Check if allowed mimes.
			$valid_mimes = self::file_valid_type( $field );
			if ( false !== $valid_mimes ) {
				$field->add_error( 'mimes-not-allowed', __( 'File type is not allowed to upload.', 'anspress-question-answer' ) );
			}

			// Check if file have any error.
			$error = self::file_have_error( $field );
			if ( false !== $error ) {
				$field->add_error( 'upload-file-error', $error );
			}

			// Check file size.
			$file_size = self::file_size_error( $field );
			if ( false !== $file_size ) {
				$field->add_error( 'max-size-upload', sprintf(
					// Translators: %s contain maximum file size user can upload.
					__( 'File(s) size is bigger then %s MB', 'anspress-question-answer' ),
					round( ap_opt( 'max_upload_size' ) / ( 1024 * 1024 ), 2 )
				) );
			}
		} // End if().
	}
}
