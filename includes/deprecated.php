<?php
/**
 * Contain list of function which are deprecated
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-3.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 */

if ( ! function_exists( '_deprecated_function' ) ) {
	require_once ABSPATH . WPINC . '/functions.php';
}


/**
 * Send ajax response if there is error in validation class.
 * @param  object $validate Validation class.
 * @since  3.0.0
 * @deprecated 4.1.0
 */
function ap_form_validation_error_response( $validate ) {
	// If error in form then return.
	if ( $validate->have_error() ) {
		ap_ajax_json( array(
			'success' => false,
			'form' 			=> $_POST['ap_form_action'],
			'snackbar' => [
				'message' => __( 'Check missing fields and then re-submit.', 'anspress-question-answer' ),
			],
			'errors'		=> $validate->get_errors(),
		) );
	}
}

/**
 * Upload form.
 *
 * @param  boolean|integer $post_id Post ID.
 * @return string
 *
 * @deprecated 4.1.0 This function was replaced by new form class.
 */
function ap_post_upload_form( $post_id = false ) {
	if ( ! ap_user_can_upload( ) ) {
		return;
	}

	if ( false === $post_id ) {
		$media = get_posts( [
			'post_type'   => 'attachment',
			'title'       => '_ap_temp_media',
			'post_author' => get_current_user_id(),
		]);
	} else {
		$media = get_attached_media( '', $post_id );
	}


	$label = sprintf( __( 'Insert images and attach media by %1$sselecting them%2$s', 'anspress-question-answer' ), '<a id="pickfiles" href="javascript:;">', '</a>' );
	$html = '<div id="ap-upload" class="ap-upload"><div class="ap-upload-anchor">' . $label . '</div>';

	$uploads = [];
	foreach ( (array) $media as $m ) {
		$uploads[] = [
			'id'       => $m->ID,
			'fileName' => basename( $m->guid ),
			'fileSize' => size_format( filesize( get_attached_file( $m->ID ) ), 2 ),
			'isImage'  => wp_attachment_is_image( $m->ID ),
			'nonce'    => wp_create_nonce( 'delete-attachment-' . $m->ID ),
			'url'      => $m->guid,
		];
	}

	$html .= '<script type="application/json" id="ap-uploads-data">' . wp_json_encode( $uploads ) . '</script>';
	$html .= '</div>';
	return $html;
}

/**
 * Initialize AnsPress uploader settings.
 *
 * @deprecated 4.1.0
 */
function ap_upload_js_init() {
	if ( ap_user_can_upload( ) ) {
		$mimes = [];

		foreach ( ap_allowed_mimes() as $ext => $mime ) {
			$mimes[] = [ 'title' => $mime, 'extensions' => str_replace( '|', ',', $ext ) ];
		}

		$plupload_init = array(
			'runtimes'            => 'html5,flash,silverlight,html4',
			'browse_button'       => 'plupload-browse-button',
			'container'           => 'plupload-upload-ui',
			'drop_element'        => 'ap-drop-area',
			'file_data_name'      => 'async-upload',
			'url'                 => admin_url( 'admin-ajax.php' ),
			'flash_swf_url'       => includes_url( 'js/plupload/plupload.flash.swf' ),
			'silverlight_xap_url' => includes_url( 'js/plupload/plupload.silverlight.xap' ),
			'filters'             => array(
				'mime_types'         => $mimes,
				'max_file_size'      => (int) ap_opt( 'max_upload_size' ) . 'b',
				'prevent_duplicates' => true,
			),
			//'maxfiles'            => ap_opt( 'uploads_per_post' ),
			'multipart_params'    => [
				'_wpnonce' => wp_create_nonce( 'media-upload' ),
				'action'   => 'ap_image_submission',
			],
		);

		echo '<script type="text/javascript"> wpUploaderInit =' . wp_json_encode( $plupload_init ) . ';</script>';
		echo '<script type="text/html" id="ap-upload-template">
				<span class="apicon-check"> ' . esc_attr__( 'Uploaded', 'anspress-question-answer' ) . '</span>
				<span class="apicon-stop"> ' . esc_attr__( 'Failed', 'anspress-question-answer' ) . '</span>
				<span class="ap-upload-name"></span>
				<a href="#" class="insert-to-post">' . esc_attr__( 'Insert to post', 'anspress-question-answer' ) . '</a>
				<a href="#" class="apicon-trashcan"></a>
				<div class="ap-progress"></div>
		</script>';
	}
}
