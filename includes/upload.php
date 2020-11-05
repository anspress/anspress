<?php
/**
 * AnsPress upload handler.
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-3.0+
 * @link      https://anspress.net
 * @copyright 2014 Rahul Aryan
 * @since     4.0.0
 */

/**
 * AnsPress upload hooks.
 */
class AnsPress_Uploader {

	/**
	 * Delete question or answer attachment.
	 */
	public static function delete_attachment() {
		$attachment_id = ap_sanitize_unslash( 'attachment_id', 'r' );

		if ( ! ap_verify_nonce( 'delete-attachment-' . $attachment_id ) ) {
			ap_ajax_json( 'no_permission' );
		}

		// If user cannot delete then die.
		if ( ! ap_user_can_delete_attachment( $attachment_id ) ) {
			ap_ajax_json( 'no_permission' );
		}

		$attach = get_post( $attachment_id );
		$row    = wp_delete_attachment( $attachment_id, true );

		if ( false !== $row ) {
			ap_update_post_attach_ids( $attach->post_parent );
			ap_ajax_json( [ 'success' => true ] );
		}

		ap_ajax_json(
			[
				'success'  => false,
				'snackbar' => [ 'message' => __( 'Unable to delete attachment', 'anspress-question-answer' ) ],
			]
		);
	}

	/**
	 * Update users temporary attachment count before a attachment deleted.
	 *
	 * @param integer $post_id Post ID.
	 */
	public static function deleted_attachment( $post_id ) {
		$_post = get_post( $post_id );

		if ( 'attachment' === $_post->post_type ) {
			ap_update_user_temp_media_count();
			ap_update_post_attach_ids( $_post->post_parent );
		}
	}

	/**
	 * Schedule event twice daily.
	 */
	public static function create_single_schedule() {
		// Check if event scheduled before.
		if ( ! wp_next_scheduled( 'ap_delete_temp_attachments' ) ) {
			// Shedule event to run every day.
			wp_schedule_event( time(), 'twicedaily', 'ap_delete_temp_attachments' );
		}
	}

	/**
	 * Delete temporary media which are older then today.
	 *
	 * @since 4.1.8 Delete files from temporary directory as we well.
	 */
	public static function cron_delete_temp_attachments() {
		global $wpdb;

		$posts = $wpdb->get_results( "SELECT ID, post_author FROM $wpdb->posts WHERE post_type = 'attachment' AND post_title='_ap_temp_media' AND post_date >= CURDATE()" ); // db call okay, db cache okay.

		$authors = [];

		if ( $posts ) {
			foreach ( (array) $posts as $_post ) {
				wp_delete_attachment( $_post->ID, true );
				ap_update_post_attach_ids( $_post->post_parent );
				$authors[] = $_post->post_author;
			}

			// Update temporary attachment counts of a user.
			foreach ( (array) array_unique( $authors ) as $author ) {
				ap_update_user_temp_media_count( $author );
			}
		}

		// Delete all temporary files.
		$uploads  = wp_upload_dir();
		$files    = glob( $uploads['basedir'] . "/anspress-temp/*" );
		$interval = strtotime( '-2 hours' );

		if ( $files ) {
			foreach ( $files as $file ) {
				if ( filemtime( $file ) <= $interval ) {
					unlink( $file );
				}
			}
		}
	}

	/**
	 * Ajax callback for image upload form.
	 *
	 * @return void
	 * @since 4.1.8
	 */
	public static function upload_modal() {
		// Check nonce.
		if ( ! ap_verify_nonce( 'ap_upload_image' ) ) {
			ap_send_json( 'something_wrong' );
		}

		// Check if user have permission to upload tem image.
		if ( ! ap_user_can_upload() ) {
			ap_send_json( array(
				'success'  => false,
				'snackbar' => array(
					'message' => __( 'Sorry! you do not have permission to upload image.', 'anspress-question-answer' ),
				),
			) );
		}

		$image_for = ap_sanitize_unslash( 'image_for', 'r' );

		ob_start();
		anspress()->get_form( 'image_upload' )->generate( array(
			'hidden_fields' => array(
				array(
					'name'  => 'action',
					'value' => 'ap_image_upload',
				),
				array(
					'name'  => 'image_for',
					'value' => $image_for,
				),
			),
		));
		$html = ob_get_clean();

		ap_send_json( array(
			'success' => true,
			'action'  => 'ap_upload_modal',
			'html'    => $html,
			'title'   => __( 'Select image file to upload', 'anspress-question-answer' ),
		) );
	}

	/**
	 * Ajax callback for `ap_image_upload`. Process `image_upload` form.
	 *
	 * @return void
	 * @since 4.1.8
	 * @since 4.1.13 Pass a `image_for` in JSON so that javascript callback can be triggered.
	 */
	public static function image_upload() {
		$form = anspress()->get_form( 'image_upload' );

		// Check if user have permission to upload tem image.
		if ( ! ap_user_can_upload() ) {
			ap_send_json( array(
				'success'  => false,
				'snackbar' => array(
					'message' => __( 'Sorry! you do not have permission to upload image.', 'anspress-question-answer' ),
				),
			) );
		}

		// Nonce check.
		if ( ! $form->is_submitted() ) {
			ap_send_json( 'something_wrong' );
		}

		$image_for = ap_sanitize_unslash( 'image_for', 'r' );
		$values    = $form->get_values();

		// Check for errors.
		if ( $form->have_errors() ) {
			ap_send_json( array(
				'success'       => false,
				'snackbar'      => array(
					'message'      => __( 'Unable to upload image(s). Please check errors.', 'anspress-question-answer' ),
				),
				'form_errors'   => $form->errors,
				'fields_errors' => $form->get_fields_errors(),
			) );
		}

		$field = $form->find( 'image' );

		// Call save.
		$files = $field->save_cb();

		$res = array(
			'success'   => true,
			'action'    => 'ap_image_upload',
			'image_for' => $image_for,
			'snackbar'  => [ 'message' => __( 'Successfully uploaded image', 'anspress-question-answer' ) ],
			'files'     => $files,
		);

		// Send response.
		if ( is_array( $res ) ) {
			ap_send_json( $res );
		}

		ap_send_json( 'something_wrong' );
	}

	public static function image_sizes_advanced( $sizes ) {
		global $ap_thumbnail_only;

		if ( true === $ap_thumbnail_only ) {
			return array(
				'thumbnail' => array(
					'width'  => 150,
					'height' => 150,
					'crop'   => true,
				),
			);
		}

		return $sizes;
	}
}

/**
 * Upload and create an attachment. Set post_status as _ap_temp_media,
 * later it will be removed using cron if no post parent is set.
 *
 * This function will prevent users to upload if they have more then defined
 * numbers of un-attached medias.
 *
 * @param array   $file           $_FILE variable.
 * @param boolean $temp           Is temporary image? If so it will be deleted if no post parent.
 * @param boolean $parent_post    Attachment parent post ID.
 * @return integer|boolean|object
 * @since  3.0.0 Added new argument `$post_parent`.
 * @since  4.1.5 Added new argument `$mimes` so that default mimes can be overridden.
 */
function ap_upload_user_file( $file = array(), $temp = true, $parent_post = '', $mimes = false ) {
	require_once ABSPATH . 'wp-admin/includes/admin.php';

	// Check if file is greater then allowed size.
	if ( $file['size'] > ap_opt( 'max_upload_size' ) ) {
		return new WP_Error( 'file_size_error', sprintf( __( 'File cannot be uploaded, size is bigger than %s MB', 'anspress-question-answer' ), round( ap_opt( 'max_upload_size' ) / ( 1024 * 1024 ), 2 ) ) );
	}

	$file_return = wp_handle_upload(
		$file, array(
			'test_form' => false,
			'mimes'     => false === $mimes ? ap_allowed_mimes() : $mimes,
		)
	);

	if ( isset( $file_return['error'] ) || isset( $file_return['upload_error_handler'] ) ) {
		return new WP_Error( 'upload_error', $file_return['error'], $file_return );
	}

	$attachment = array(
		'post_parent'    => $parent_post,
		'post_mime_type' => $file_return['type'],
		'post_content'   => '',
		'guid'           => $file_return['url'],
	);

	// Add special post status if is temporary attachment.
	if ( false !== $temp ) {
		$attachment['post_title'] = '_ap_temp_media';
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	$attachment_id = wp_insert_attachment( $attachment, $file_return['file'] );

	if ( ! empty( $attachment_id ) ) {
		ap_update_user_temp_media_count();
	}

	return $attachment_id;
}

/**
 * Return allowed mime types.
 *
 * @return array
 * @since  3.0.0
 */
function ap_allowed_mimes() {
	$mimes = array(
		'jpg|jpeg' => 'image/jpeg',
		'gif'      => 'image/gif',
		'png'      => 'image/png',
		'doc|docx' => 'application/msword',
		'xls'      => 'application/vnd.ms-excel',
	);

	/**
	 * Filter allowed mimes types.
	 *
	 * @param array $mimes Default mimes types.
	 * @since 3.0.0
	 */
	return apply_filters( 'ap_allowed_mimes', $mimes );
}

/**
 * Delete all un-attached media of user.
 *
 * @param integer $user_id User ID.
 */
function ap_clear_unattached_media( $user_id = false ) {

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	global $wpdb;
	$post_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND post_title='_ap_temp_media' AND post_author = %d", $user_id ) ); // db call okay, db cache okay.

	foreach ( (array) $post_ids as $id ) {
		wp_delete_attachment( $id, true );
	}
}

/**
 * Set parent post for an attachment.
 *
 * @param integer|array $media_id Attachment ID.
 * @param integer       $post_parent   Attachment ID.
 */
function ap_set_media_post_parent( $media_id, $post_parent, $user_id = false ) {
	if ( ! is_array( $media_id ) ) {
		$media_id = [ $media_id ];
	}

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	foreach ( (array) $media_id as $id ) {
		$attach = get_post( $id );

		if ( $attach && 'attachment' === $attach->post_type && $user_id == $attach->post_author ) { // loose comparison okay.
			$postarr = array(
				'ID'          => $attach->ID,
				'post_parent' => $post_parent,
				'post_title'  => preg_replace( '/\.[^.]+$/', '', basename( $attach->guid ) ),
			);

			wp_update_post( $postarr );
		}
	}

	ap_update_post_attach_ids( $post_parent );
	ap_update_user_temp_media_count( $user_id );
}

/**
 * Count temproary attachments of a user.
 *
 * @param  integer $user_id User ID.
 * @return integer
 */
function ap_count_users_temp_media( $user_id ) {
	global $wpdb;

	$count = $wpdb->get_var( $wpdb->prepare( "SELECT count(*) FROM $wpdb->posts WHERE post_title = '_ap_temp_media' AND post_author=%d AND post_type='attachment'", $user_id ) ); // db call okay.

	return (int) $count;
}

/**
 * Update users temproary media uploads count.
 *
 * @param integer $user_id User ID.
 */
function ap_update_user_temp_media_count( $user_id = false ) {

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	// @codingStandardsIgnoreLine
	update_user_meta( $user_id, '_ap_temp_media', ap_count_users_temp_media( $user_id ) );
}

/**
 * Check if user have uploaded maximum numbers of allowed attachments.
 *
 * @param  integer $user_id User ID.
 * @return boolean
 */
function ap_user_can_upload_temp_media( $user_id = false ) {

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	// @codingStandardsIgnoreLine
	$temp_images = (int) get_user_meta( $user_id, '_ap_temp_media', true );

	if ( $temp_images < ap_opt( 'uploads_per_post' ) ) {
		return true;
	}

	return false;
}

/**
 * Pre fetch and cache all question and answer attachments.
 *
 * @param  array $ids Post IDs.
 * @since  4.0.0
 */
function ap_post_attach_pre_fetch( $ids ) {

	if ( $ids && is_user_logged_in() ) {
		$args = array(
			'post_type' => 'attachment',
			'include'   => $ids,
		);

		$posts = get_posts( $args );// @codingStandardsIgnoreLine
		update_post_cache( $posts );
	}
}

/**
 * Delete images uploaded in post.
 *
 * This function should be called after saving post content. This
 * will find previously uploaded images from post meta and then
 * compare with the image `src` present in content and if any image
 * does not exists then image file and post meta is deleted.
 *
 * @param integer $post_id Post ID.
 * @return void
 * @since 4.1.8
 */
function ap_delete_images_not_in_content( $post_id ) {
	$_post  = ap_get_post( $post_id );

	preg_match_all( '/<img.*?src\s*="([^"]+)".*?>/', $_post->post_content, $matches, PREG_SET_ORDER );

	$new_matches = [];

	if ( ! empty( $matches ) ) {
		foreach ( $matches as $m ) {
			$new_matches[] = basename( $m[1] );
		}
	}

	$images  = get_post_meta( $post_id, 'anspress-image' );

	if ( ! empty( $images ) ) {
		// Delete image if not in $matches.
		foreach ( $images as $img ) {
			if ( ! in_array( $img, $new_matches, true ) ) {
				delete_post_meta( $post_id, 'anspress-image', $img );

				$uploads = wp_upload_dir();
				$file    = $uploads['basedir'] . "/anspress-uploads/$img";
				unlink( $file );
			}
		}
	}
}
