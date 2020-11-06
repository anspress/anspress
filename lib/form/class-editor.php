<?php
/**
 * AnsPress Editor type field object.
 *
 * @package    AnsPress
 * @subpackage Fields
 * @since      4.1.0
 * @author     Rahul Aryan<support@anspress.net>
 * @copyright  Copyright (c) 2017, Rahul Aryan
 * @license    http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace AnsPress\Form\Field;

use AnsPress\Form as Form;
use AnsPress\Form\Field as Field;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AnsPress Editor type field object.
 *
 * @since 4.1.0
 */
class Editor extends Field {
	/**
	 * The field type.
	 *
	 * @var string
	 */
	public $type = 'editor';

	/**
	 * Uploaded images.
	 *
	 * @var array
	 * @since 4.1.8
	 */
	public $images = [];

	/**
	 * Prepare field.
	 *
	 * @return void
	 * @since 4.1.8 Remove child `image` field.
	 */
	protected function prepare() {
		$this->args = wp_parse_args(
			$this->args, array(
				'label'       => __( 'AnsPress Editor Field', 'anspress-question-answer' ),
				'editor_args' => array(
					'quicktags' => false,
				),
			)
		);

		// Call parent prepare().
		parent::prepare();

		// Make sure all text field are sanitized.
		$this->sanitize_cb = array_merge( [ 'description', 'wp_kses' ], $this->sanitize_cb );
	}

	/**
	 * Image upload button.
	 *
	 * @return void
	 * @since 4.1.8
	 */
	public function image_button() {
		$btn_args = wp_json_encode( array(
			'__nonce'   => wp_create_nonce( 'ap_upload_image' ),
			'action'    => 'ap_upload_modal',
			'form_name' => $this->form_name,
		) );

		$this->add_html( '<button type="button" class="ap-btn-insertimage ap-btn-small ap-btn mb-10 ap-mr-5" apajaxbtn aponce="false" apquery="' . esc_js( $btn_args ) . '"><i class="apicon-image ap-mr-3"></i>' . __( 'Insert image', 'anspress-question-answer' ) . '</button>' );

		/**
		 * Action trigged after image button before editor.
		 *
		 * @param string $field_name Original name of the field.
		 * @param object $field      Field object.
		 * @since 4.1.8
		 */
		do_action( 'ap_editor_buttons', $this->original_name, $this );
	}

	/**
	 * Field markup.
	 *
	 * @return void
	 * @since 4.1.8 Added image button.
	 */
	public function field_markup() {
		parent::field_markup();
		$args = $this->get( 'editor_args', [] );

		$settings = array(
			'textarea_rows' => 10,
			'tinymce'       => array(
				'content_css'      => ap_get_theme_url( 'css/editor.css' ),
				'wp_autoresize_on' => true,
				'statusbar'        => false,
				'codesample'       => true,
				'anspress'         => true,
				'toolbar1'         => 'bold,italic,underline,strikethrough,bullist,numlist,link,unlink,blockquote,fullscreen,apcode',
				'toolbar2'         => '',
				'toolbar3'         => '',
				'toolbar4'         => '',
			),
			'quicktags'     => false,
			'media_buttons' => false,
			'textarea_name' => $this->field_name,
		);

		if ( true === $args['quicktags'] ) {
			$settings['tinymce'] = false;
		}

		$editor_args = wp_parse_args( $this->get( 'editor_args' ), $settings );

		/**
		 * Can be used to modify wp_editor settings.
		 *
		 * @var array
		 * @since 2.0.1
		 */
		$editor_args = apply_filters( 'ap_pre_editor_settings', $editor_args );

		$this->add_html( '<div class="ap-editor">' );
		$this->image_button();

		/**
		 * Filter value before passing it to wp editor.
		 *
		 * @param string $value Value.
		 *
		 * @since 4.1.8
		 */
		$value = apply_filters( 'ap_editor_pre_value', $this->value() );

		ob_start();
		wp_editor( $value, $this->id(), $editor_args );
		$this->add_html( ob_get_clean() );

		$this->add_html( '</div>' );

		/** This action is documented in lib/form/class-input.php */
		do_action_ref_array( 'ap_after_field_markup', [ &$this ] );
	}

	// public function unsafe_value() {
	// 	$value = parent::unsafe_value();

	// 	if ( ! empty( $value ) ) {
	// 		$value = preg_replace_callback( '/\[(\[?)(apcode)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)/', [ $this, 'apcode_cb' ], $value );
	// 	}

	// 	return $value;
	// }

	/**
	 * Callback for replacing `apcode` shortcode.
	 *
	 * @param array $matches Matches.
	 * @return string
	 */
	public function apcode_cb( $matches ) {
		return '[apcode' . $matches[3] . ']' . esc_html( $matches[5] ) . '[/apcode]';
	}

	/**
	 * Get all attached images from content.
	 *
	 * @return array
	 */
	private function get_attached_images() {
		preg_match_all( '/(?:{{apimage "([^"]*)"[^}]*}})/', $this->value(), $matches, PREG_SET_ORDER, 0 );

		$new_matches = [];

		if ( ! empty( $matches ) ) {
			foreach ( $matches as $index => $m ) {
				$new_matches[ $index ] = $m[1];
			}
		}

		return $new_matches;
	}

	/**
	 * Callback called in @see ::pre_get.
	 *
	 * Checks if current image is in `anspress-temp` directory and
	 * if so then moves it to `anspress-uploads` directory and return
	 * new `img` tag with new src.
	 *
	 * @param array $matches Regex matches.
	 * @return string Updated `img` tag.
	 */
	public function image_process( $matches ) {
		if ( false === strpos( $matches[1], 'anspress-temp/' ) ) {
			return $matches[0];
		}

		$files = anspress()->session->get( 'files' );

		$uploads    = wp_upload_dir();
		$basename   = basename( $matches[1] );
		$temp_file = $uploads['basedir'] . "/anspress-temp/" . $basename;

		// Check temp file is in anspress-temp directory.
		if ( ! file_exists( $temp_file ) ) {
			$this->add_error( 'fields-error', __( 'Sorry an error occured while processing your image, please remove it and insert again', 'anspress-question-answer' ) );
		} else {
			$upload_dir = $uploads['basedir'] . "/anspress-uploads/";

			// Make dir if not exists.
			if ( ! file_exists( $upload_dir ) ) {
				mkdir( $upload_dir );
			}

			// Check file in session and then move.
			if ( in_array( $basename, $files, true ) ) {
				$this->images[] = $basename;

				$newfile = $upload_dir . "/$basename";

				$new_file_url = $uploads['baseurl'] . "/anspress-uploads/$basename";
				rename( $uploads['basedir'] . "/anspress-temp/$basename", $newfile );

				return '<img src="' . esc_url( $new_file_url ) . '" />';
			}
		}

		return false;
	}

	/**
	 *
	 * Replace temporary images with img tags.
	 *
	 * @return void
	 * @since 4.1.8 Process uploaded images.
	 */
	public function pre_get() {
		$value = $this->value();

		if ( $this->have_errors() ) {
			return;
		}

		$this->value = preg_replace_callback( '/<img\s+src="([^"]+)"[^>]+>/i', [ $this, 'image_process' ], $value );
	}

	/**
	 * Action to do after post is saved.
	 *
	 * Add uploaded images to post meta and delete post meta on delete.
	 *
	 * @param array $args Array of arguments.
	 * @return void
	 *
	 * @since 4.1.8 Removed adding and deleting of attachment.
	 */
	public function after_save( $args = [] ) {
		parent::after_save();

		if ( empty( $args ) || empty( $args['post_id'] ) || empty( $this->images ) ) {
			return;
		}

		// Add images to post meta.
		foreach ( $this->images as $img ) {
			add_post_meta( $args['post_id'], 'anspress-image', $img );
		}

		// Delete file from session.
		anspress()->session->delete( 'files' );
	}

	/**
	 * Get POST (unsafe) value of a field.
	 *
	 * @return null|mixed
	 */
	public function unsafe_value() {
		$request_value = $this->get( ap_to_dot_notation( $this->field_name ), null, $_REQUEST );
		if ( isset( $request_value ) ) {
			return $request_value;
		}
	}

}
