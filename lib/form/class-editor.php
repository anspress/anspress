<?php
/**
 * AnsPress Editor type field object.
 *
 * @package    AnsPress
 * @subpackage Fields
 * @since      4.1.0
 * @author     Rahul Aryan<support@anspress.io>
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
	 * Prepare field.
	 *
	 * @return void
	 */
	protected function prepare() {
		$this->args = wp_parse_args( $this->args, array(
			'label' => __( 'AnsPress Editor Field', 'anspress-question-answer' ),
		) );

		$this->args['fields'] = array(
			'images'          => array(
				'label'          => sprintf(
					// Translators: %s contain label of editor field.
					__( '%s images', 'anspress-question-answer' ),
					$this->get( 'label' )
				),
				'type'           => 'upload',
				'upload_options' => array(
					'multiple'  => true,
					'max_files' => ap_opt( 'uploads_per_post' ),
				),
			),
		);

		$this->child = new Form( $this->field_name, $this->args );
		$this->child->prepare();

		// Call parent prepare().
		parent::prepare();

		// Make sure all text field are sanitized.
		$this->sanitize_cb = array_merge( [ 'description', 'wp_kses' ], $this->sanitize_cb );
	}

	/**
	 * Field markup.
	 *
	 * @return void
	 */
	public function field_markup() {
		$settings = array(
			'textarea_rows' => 10,
			'tinymce'           => array(
				'content_css'      => ap_get_theme_url( 'css/editor.css' ),
				'wp_autoresize_on' => true,
				'statusbar'        => false,
				'codesample'       => true,
			),
			'quicktags'     => false,
			'media_buttons' => false,
			'textarea_name' => $this->field_name,
		);

		$editor_args = wp_parse_args( $this->get( 'editor_args' ), $settings );

		/**
		 * Can be used to modify wp_editor settings.
		 *
		 * @var array
		 * @since 2.0.1
		 */
		$editor_args = apply_filters( 'ap_pre_editor_settings', $editor_args );

		$this->add_html( '<div class="ap-editor">' );

		ob_start();
		wp_editor( $this->value(), $this->id(), $editor_args );
		$this->add_html( ob_get_clean() );

		$this->add_html( '</div>' );
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
	 *
	 * Replace temporary images with img tags.
	 *
	 * @return void
	 */
	public function pre_get() {
		$value = $this->value();

		if ( $this->have_errors() ) {
			return;
		}

		$image_field = $this->child->find( 'images' );

		if ( $image_field && ! empty( $image_field->value() ) ) {
			if ( ! $image_field->have_errors() ) {
				$this->value = $image_field->replace_temp_image( $value, $this->get_attached_images() );
			}

			if ( $image_field->have_errors() ) {
				foreach ( (array) $image_field->errors as $code => $msg ) {
					$this->add_error( $code, $msg );
				}
			}
		}
	}
}
