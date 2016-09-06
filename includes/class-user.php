<?php
/**
 * AnsPress user actions and filters
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-3.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$user_pages = array();

class AnsPress_User
{
	/**
	 * Actions to do in init
	 */
	public static function init_actions() {
		// If profile is disabled then return.
		if( !ap_is_profile_active() ){
			return;
		}

		// Register AnsPress pages.
		ap_register_page( ap_opt( 'users_page_slug' ), __( 'Users', 'anspress-question-answer' ), array( __CLASS__, 'users_page' ) );
		ap_register_page( ap_opt( 'user_page_slug' ), __( 'User', 'anspress-question-answer' ), array( __CLASS__, 'user_page' ), false );
		ap_register_user_page( 'about', __( 'About', 'anspress-question-answer' ), array( __CLASS__, 'about_page' ) );
		ap_register_user_page( 'activity-feed', __( 'Activity Feed', 'anspress-question-answer' ), array( __CLASS__, 'feed_page' ), true );
		ap_register_user_page( 'notification', __( 'Notification', 'anspress-question-answer' ), array( __CLASS__, 'notification_page' ), true, false );
		ap_register_user_page( 'profile', __( 'Profile', 'anspress-question-answer' ), array( __CLASS__, 'profile_page' ), true, false );
		ap_register_user_page( 'questions', __( 'Questions', 'anspress-question-answer' ), array( __CLASS__, 'questions_page' ) );
		ap_register_user_page( 'answers', __( 'Answers', 'anspress-question-answer' ), array( __CLASS__, 'answers_page' ) );
		ap_register_user_page( 'followers', __( 'Followers', 'anspress-question-answer' ), array( __CLASS__, 'followers_page' ) );
		ap_register_user_page( 'following', __( 'Following', 'anspress-question-answer' ), array( __CLASS__, 'following_page' ) );
		add_filter( 'ap_page_title', array( __CLASS__, 'ap_page_title' ) );
	}

	/**
	 * Register users directory page in AnsPress
	 */
	public static function users_page() {
		if ( ap_opt( 'enable_users_directory' ) ) {
			global $ap_user_query;
			$ap_user_query = ap_has_users();
			include ap_get_theme_location( 'users/users.php' );

		} else {
			_e( 'User directory is disabled.', 'anspress-question-answer' );
		}
	}

	/**
	 * Register user page in AnsPress
	 */
	public static function user_page() {
		// Return if user profile is not active.
		if ( ! ap_is_profile_active() ) {
			return;
		}

		global $ap_user_query;

		if ( ap_get_displayed_user_id() == 0 && ! is_user_logged_in() ) {
			ap_get_template_part( 'login-signup' );
			return;
		}

		$ap_user_query = ap_has_users( array( 'ID' => ap_get_displayed_user_id() ) );

		if ( $ap_user_query->has_users() ) {
			include ap_get_theme_location( 'user/user.php' );
		} else {
			_e( 'No user found.', 'anspress-question-answer' );
		}
	}

	/**
	 * Output user about page
	 * @since 2.3
	 */
	public static function about_page() {
		ap_get_template_part( 'user/about' );
	}

	/**
	 * Output user feed page.
	 * @since 2.4
	 */
	public static function feed_page() {
		global $ap_activities;
		$paged = get_query_var( 'paged', 1 );
	    $ap_activities = ap_get_activities( array( 'per_page' => 20, 'subscriber' => true, 'user_id' => ap_get_displayed_user_id(), 'orderby' => 'created', 'order' => 'DESC', 'paged' => $paged ) );
		ap_get_template_part( 'user/activity-feed' );
	}

	/**
	 * Output notification page
	 * @since 2.3
	 */
	public static function notification_page() {
		if ( ! ap_is_user_page_public( 'profile' ) && ! ap_is_my_profile() ) {
			ap_get_template_part( 'not-found' );
			return;
		}

		global $ap_activities;

		$ap_activities = ap_get_activities( array( 'notification' => true, 'user_id' => ap_get_displayed_user_id() ) );

		ap_get_template_part( 'user/notification' );
	}

	/**
	 * Output for activity page
	 * @since 2.1
	 */
	public static function activity_page() {
		include ap_get_theme_location( 'user/activity.php' );
	}

	/**
	 * Output for profile page
	 * @since 2.1
	 */
	public static function profile_page() {
		if ( ! ap_is_user_page_public( 'profile' ) && ! ap_is_my_profile() ) {
			ap_get_template_part( 'not-found' );
			return;
		}

		include ap_get_theme_location( 'user/profile.php' );
	}

	/**
	 * Output for user questions page
	 * @since 2.1
	 */
	public static function questions_page() {
		global $questions;
		$questions = ap_get_questions( array( 'author' => ap_get_displayed_user_id() ) );
		ap_get_template_part( 'user/user-questions' );
		wp_reset_postdata();
	}

	/**
	 * Output for user answers page
	 * @since 2.0.1
	 */
	public static function answers_page() {
		global $answers;
		$answers = ap_get_answers( array( 'author' => ap_get_displayed_user_id() ) );
		include ap_get_theme_location( 'user/user-answers.php' );
		wp_reset_postdata();
	}

	/**
	 * Register user foloowers page.
	 */
	public static function followers_page() {
		$followers = ap_has_users( array(
			'user_id' 	=> ap_get_displayed_user_id(),
			'sortby' 	=> 'followers',
		) );

		if ( $followers->has_users() ) {
			include ap_get_theme_location( 'user/followers.php' );
		} else {
			esc_attr_e( 'No followers found', 'anspress-question-answer' );
		}

	}

	/**
	 * Register followers page in AnsPress
	 */
	public static function following_page() {
		$following = ap_has_users( array( 'user_id' => ap_get_displayed_user_id(), 'sortby' => 'following' ) );

		if ( $following->has_users() ) {
			include ap_get_theme_location( 'user/following.php' );
		} else {
			esc_attr_e( 'You are not following anyone.', 'anspress-question-answer' );
		}
	}


	/**
	 * Filter AnsPress page title for user sub pages
	 * @param  string $title Title.
	 * @return string
	 */
	public static function ap_page_title($title) {
		if ( is_ap_user() ) {
			$active = ap_active_user_page();
			$name = ap_user_get_the_display_name();
			$my = ap_is_my_profile();
			$user_pages = anspress()->user_pages;

			$titles = array(
				'activity' => $my ?  __( 'My activity', 'anspress-question-answer' ) : sprintf( __( '%s\'s activity', 'anspress-question-answer' ), $name ),
				'profile' => $my ?  __( 'My profile', 'anspress-question-answer' ) : sprintf( __( '%s\'s profile', 'anspress-question-answer' ), $name ),
				'questions' => $my ?  __( 'My questions', 'anspress-question-answer' ) : sprintf( __( '%s\'s questions', 'anspress-question-answer' ), $name ),
				'answers' => $my ?  __( 'My answers', 'anspress-question-answer' ) : sprintf( __( '%s\'s answers', 'anspress-question-answer' ), $name ),
				'about' => $my ?  __( 'About me', 'anspress-question-answer' ) : $name,
				'followers' => $my ?  __( 'My followers', 'anspress-question-answer' ) : sprintf( __( '%s\'s followers', 'anspress-question-answer' ), $name ),
				'following' => __( 'Following', 'anspress-question-answer' ),
				'subscription' => __( 'My subscriptions', 'anspress-question-answer' ),
				'notification' => __( 'My notification', 'anspress-question-answer' ),
			);

			foreach ( (array) $titles as $page => $user_title ) {
				if ( $page == $active ) {
					$title = $user_title;
				} else {
					$title = $user_pages[$active]['title'];
				}
			}
		}

		return $title;
	}

	/**
	 * For modifying WP_User_Query, if passed with a var ap_followers_query
	 * @param  array $query Mysql clauses.
	 * @return array
	 */
	public static function follower_query( $query ) {
		if ( isset( $query->query_vars['ap_query'] ) &&
			$query->query_vars['ap_query'] == 'user_sort_by_followers' &&
			isset( $query->query_vars['user_id'] ) ) {

			global $wpdb;

			$query->query_from = $query->query_from.' LEFT JOIN '.$wpdb->ap_subscribers.' ON ID = subs_user_id';

			$userid = $query->query_vars['user_id'];

			$query->query_where = $query->query_where." AND subs_activity = 'u_all' AND subs_item_id = $userid";
		}

		return $query;
	}

	/**
	 * Modify user query to get following users
	 * @param  array $query Mysql claueses
	 * @return array
	 */
	public static function following_query($query) {
		if ( isset( $query->query_vars['ap_query'] ) &&
			$query->query_vars['ap_query'] == 'user_sort_by_following' &&
			isset( $query->query_vars['user_id'] ) ) {

			global $wpdb;

			$query->query_from = $query->query_from.' LEFT JOIN '.$wpdb->ap_subscribers.' ON ID = subs_item_id';
			$userid = $query->query_vars['user_id'];
			$query->query_where = $query->query_where." AND subs_activity = 'u_all' AND subs_user_id = $userid";
		}

		return $query;
	}

	/**
	 * Filter user query so that it can be sorted by user reputation
	 * @param  array $query Mysql claueses.
	 * @return array
	 */
	public static function user_sort_by_reputation($query) {
		global $wpdb;

		if ( isset( $query->query_vars['ap_query'] ) ) {

			$query->query_where = $query->query_where.' AND (apm1.user_id IS NULL OR (  apm1.meta_value != 1) )';

			$query->query_from = $query->query_from. " LEFT JOIN {$wpdb->usermeta} AS apm1 ON ( {$wpdb->users}.ID = apm1.user_id AND apm1.meta_key = 'hide_profile' )";

			if ( $query->query_vars['ap_query'] == 'user_sort_by_reputation' ) {
				$query->query_orderby = 'ORDER BY cast(mt1.meta_value AS DECIMAL) DESC';
			}
		}

		return $query;
	}

	/**
	 * Create unique name for files
	 * @param  string  $dir 	 Directory.
	 * @param  integer $user_id  User ID.
	 * @param  string  $ext      Image extension.
	 * @return string
	 * @since  2.1.5
	 */
	public static function unique_filename_callback( $dir, $user_id, $ext ) {
		global $user_id;
		$md5 = md5( $user_id.time() );
		return $md5 . $ext;
	}

	/**
	 * Upload a photo to server. Before uploading it check for valid image type
	 * @uses 	wp_handle_upload
	 * @param  	string $file_name      Name of file input field.
	 * @return 	array|false
	 */
	public static function upload_photo($file_name) {
		if ( $_FILES[ $file_name ]['size'] > ap_opt( 'max_upload_size' ) ) {
			new WP_Error( 'avatar_upload_error', sprintf( __( 'File cannot be uploaded, size is bigger then %d Byte', 'anspress-question-answer' ), ap_opt( 'max_upload_size' ) ) );
		}

		require_once ABSPATH.'wp-admin/includes/image.php';
		require_once ABSPATH.'wp-admin/includes/file.php';
		require_once ABSPATH.'wp-admin/includes/media.php';

		if ( ! empty( $_FILES[ $file_name ][ 'name' ] ) && is_uploaded_file( $_FILES[ $file_name ]['tmp_name'] ) ) {
			$mimes = array(
				'jpg|jpeg|jpe' 	=> 'image/jpeg',
				'gif' 			=> 'image/gif',
				'png' 			=> 'image/png',
			);

			$photo = wp_handle_upload( $_FILES[ $file_name ], array(
				'mimes' => $mimes,
				'test_form' => false,
				'unique_filename_callback' => array( __CLASS__, 'unique_filename_callback' ),
			 ) );

			if ( empty( $photo[ 'file' ] ) || isset( $photo['error'] ) ) {
				// Handle failures.
				return new WP_Error( 'avatar_upload_error', __( 'There was an error while uploading avatar, please check your image', 'anspress-question-answer' ) );
			}

			return $photo;
		}
	}

	/**
	 * Process cover upload form
	 */
	public static function cover_upload() {
		if ( ap_user_can_upload_cover() && ap_verify_nonce( 'upload_cover_'.get_current_user_id() ) ) {

			$photo = SELF::upload_photo( 'image' );

			// Check if wp_error.
			if ( is_wp_error( $photo ) ) {
				ap_send_json( ap_ajax_responce( array( 'message' => $photo->get_error_message(), 'message_type' => 'error' ) ) );
			}

			$file = str_replace( '\\', '\\\\', $photo['file'] );
			$photo['file'] = $file;

			$photo['small_url'] = str_replace( basename( $photo['url'] ), 'small_'.basename( $photo['url'] ), $photo['url'] );

			$small_name = str_replace( basename( $photo['file'] ), 'small_'.basename( $photo['file'] ), $photo['file'] );

			$photo['small_file'] = $small_name;

			$userid = get_current_user_id();

			// Remove previous cover image.
			$previous_cover = get_user_meta( $userid, '_ap_cover', true );

			if ( $previous_cover['file'] && file_exists( $previous_cover['file'] ) ) {
				unlink( $previous_cover['file'] );
			}

			// Delete previous image.
			if ( $previous_cover['small_file'] && file_exists( $previous_cover['small_file'] ) ) {
				unlink( $previous_cover['small_file'] );
			}

			// Resize thumbnail.
			$image = wp_get_image_editor( $file );

			if ( ! is_wp_error( $image ) ) {
				$image->resize( 960, 250, true );
				$image->save( $file );
				$image->resize( 350, 95, true );
				$image->save( $small_name );
			}

			// Update new photo link.
			update_user_meta( $userid, '_ap_cover', $photo );

			do_action( 'ap_after_cover_upload', $userid, $photo );

			ap_ajax_json( array(
				'action' 	=> 'cover_uploaded',
				'status' 	=> true,
				'message' 	=> __( 'Cover photo uploaded successfully.', 'anspress-question-answer' ),
				'user_id' 	=> $userid,
				'image' 	=> ap_get_cover_src( $userid ),
			) );
		}

		ap_ajax_json( array(
			'message' => __( 'There was an error while uploading cover photo, please check your image and try again.', 'anspress-question-answer' ),
			'message_type' => 'error',
		) );
	}

	/**
	 * Process ajax user avatar upload request.
	 * Sanitize file and pass to upload_file(). Rename image to md5 and store file
	 * name in user meta. Also remove existing avtar if exists
	 * @return void
	 */
	public static function avatar_upload() {
		if ( ap_user_can_upload_avatar() && ap_verify_nonce( 'upload_avatar_'.get_current_user_id() ) ) {

			$photo = SELF::upload_photo( 'thumbnail' );
			if ( is_wp_error( $photo ) ) {
				ap_send_json( ap_ajax_responce( array( 'message' => $photo->get_error_message(), 'message_type' => 'error' ) ) );
			}

			$file = str_replace( '\\', '\\\\', $photo['file'] );
			$photo['file'] = $file;

			$photo['small_url'] = str_replace( basename( $photo['url'] ), 'small_'.basename( $photo['url'] ), $photo['url'] );

			$small_name = str_replace( basename( $photo['file'] ), 'small_'.basename( $photo['file'] ), $photo['file'] );

			$photo['small_file'] = $small_name;

			$userid = get_current_user_id();

			// Remove previous image.
			$previous_avatar = get_user_meta( $userid, '_ap_avatar', true );

			if ( $previous_avatar['file'] && file_exists( $previous_avatar['file'] ) ) {
				unlink( $previous_avatar['file'] );
			}

			if ( $previous_avatar['small_file'] && file_exists( $previous_avatar['small_file'] ) ) {
				unlink( $previous_avatar['small_file'] );
			}

			// Resize thumbnail.
			$image = wp_get_image_editor( $file );

			if ( ! is_wp_error( $image ) ) {
				$image->resize( 200, 200, true );
				$image->save( $file );
				$image->resize( 50, 50, true );
				$image->save( $small_name );
			}

			update_user_meta( $userid, '_ap_avatar', $photo );

			do_action( 'ap_after_avatar_upload', $userid, $photo );

			ap_ajax_json( array(
				'status' 	=> true,
				'action' 	=> 'avatar_uploaded',
				'user_id' 	=> $userid,
				'message' 	=> __( 'Avatar uploaded successfully.', 'anspress-question-answer' ),
				'html' 		=> get_avatar( $userid, 150 ),
			) );
		}

		ap_ajax_json( array(
			'message' 		=> __( 'There was an error while uploading avatar, please check your image', 'anspress-question-answer' ),
			'message_type' 	=> 'error',
		) );

	}

	/**
	 * Add AnsPress avtar in Wp discussion setting
	 * @param  array $avatar_defaults Avatar types.
	 * @return array
	 */
	public static function default_avatar($avatar_defaults) {
		$new_avatar = 'ANSPRESS_AVATAR_SRC';
		$avatar_defaults[$new_avatar] = 'AnsPress';

		return $avatar_defaults;
	}

	/**
	 * Override get_avatar
	 * @param  string         $avatar 		Avatar image.
	 * @param  integar|string $id_or_email 	User id or email.
	 * @param  string         $size 		Avatar size.
	 * @param  string         $default 		Default avatar.
	 * @param  string         $alt 			Avatar image alternate text.
	 * @return string
	 */
	public static function get_avatar($args, $id_or_email) {
		if ( ! empty( $id_or_email ) ) {
			if ( is_object( $id_or_email ) ) {
				$allowed_comment_types = apply_filters('get_avatar_comment_types', array(
					'comment'
					));

				if ( ! empty( $id_or_email->comment_type ) && ! in_array( $id_or_email->comment_type, (array) $allowed_comment_types ) ) {
					return $args;
				}

				if ( ! empty( $id_or_email->user_id ) ) {
					$id          = (int) $id_or_email->user_id;
					$user        = get_userdata( $id );
					if ( $user ) {
						$id_or_email = $user->ID;
					}
				} else {
					$id_or_email = 0;
				}
			} elseif ( is_email( $id_or_email ) ) {
				$u           = get_user_by( 'email', $id_or_email );
				$id_or_email = $u->ID;
			}

			$ap_avatar     = ap_get_avatar_src( $id_or_email, ($args['size'] > 50 ? false:  true) );

			if ( $ap_avatar !== false ) {
				$args['url'] = $ap_avatar;
			}
		}

		// Set default avatar url.
		if ( empty( $args['url'] ) && 'ANSPRESS_AVATAR_SRC' == get_option( 'avatar_default' ) ) {
			$display_name = substr( ap_user_display_name( array( 'user_id' => $id_or_email ) ), 0, 2 );
			$args['url'] = 'http://ANSPRESS_AVATAR_SRC::'.$display_name;
		}

		return $args;
	}

	/**
	 * Set icon class for user menus
	 * @param  array $menus AnsPress user menu.
	 * @return array
	 * @since 2.0.1
	 */
	public static function ap_user_menu_icons($menus) {
		$icons = array(
			'about'         => ap_icon( 'home' ),
			'profile'       => ap_icon( 'board' ),
			'questions'     => ap_icon( 'question' ),
			'answers'       => ap_icon( 'answer' ),
			'activity'      => ap_icon( 'pulse' ),
			'reputation'    => ap_icon( 'reputation' ),
			'followers'     => ap_icon( 'users' ),
			'following'     => ap_icon( 'users' ),
			'subscription'  => ap_icon( 'mail' ),
			'notification'  => ap_icon( 'globe' ),
			'activity-feed'  		=> ap_icon( 'rss' ),
		);

		foreach ( (array) $icons as $k => $i ) {
			if ( isset( $menus[ $k ] ) ) {
				$menus[ $k ]['class'] = $i;
			}
		}

		return $menus;
	}

	/**
	 * Ajax callback for user dropdown
	 * @since 3.0.0
	 */
	public static function user_dp() {
		if ( ! ap_verify_nonce('ap_ajax_nonce' ) || ! is_user_logged_in() ) {
			ap_ajax_json('something_wrong' );
		}

		$type = sanitize_text_field( wp_unslash( $_POST['args'][0] ) );

		$ap_data = false;

		if ( 'noti' === $type ) {
			$ap_data = SELF::user_notification_dropdown();
		} elseif ( 'menu' === $type ) {
			$ap_data = SELF::user_menu_dropdown();
		}

		if ( false === $ap_data ) {
			ap_ajax_json( 'something_wrong' );
		}

		$data = array(
			'template' => 'user-dropdown-'.$type,
			'appendTo' => '.ap-userdp-'.$type,
			'do' => [ 'addClass' => [ '#ap-userdp-'.$type, 'ajax-disabled' ] ],
			'apData' => $ap_data,
			'key' => get_current_user_id().$type.'Dp', // i.e. 1notiDp
		);

		ap_ajax_json( $data );
	}

	/**
	 * Return data for user notification dropdown menu.
	 * @return array
	 * @since  3.0.0
	 */
	public static function user_notification_dropdown() {
		global $ap_activities;

		/**
		 * Dropdown notification arguments.
		 * Allow filtering of dropdown notification arguments.
		 * @since 2.4.5
		 * @var array
		 */
		$notification_args = apply_filters( 'ap_dropdown_notification_args', array(
			'per_page' 		=> 20,
			'notification' 	=> true,
			'user_id' 		=> get_current_user_id(),
		) );

		$ap_activities = ap_get_activities( $notification_args );

		$ap_data = array(
			'title'	=> __('Notifications', 'anspress-question-answer' ),
			'mark_all_read'	=> [ 'label' => __('Mark all as read', 'anspress-question-answer' ), 'nonce' => wp_create_nonce( 'ap_markread_notification_'.get_current_user_id() ) ],
			'all_link' 		=> ap_user_link(get_current_user_id(), 'notification' ),
			'view_all_text' => __('View all notifications', 'anspress-question-answer' ),
			'no_item' => __('No notification', 'anspress-question-answer' ),
			'notifications'	=> array(),
			'have_notifications'	=> ap_has_activities(),
		);

		if ( ap_has_activities() ) :
			while ( ap_activities() ) : ap_the_activity();
				$ap_data['notifications'][] = array(
					'id' 			=> ap_activity_id(),
					'is_unread' 	=> ap_notification_is_unread(),
					'avatar' 		=> get_avatar( ap_activity_user_id(), 35 ),
					'user_link' 	=> ap_user_link( ap_activity_user_id() ),
					'permalink' 	=> ap_activity_permalink(),
					'content' 		=> ap_activity_content(),
					'date' 			=> ap_human_time( get_gmt_from_date( ap_activity_date() ), false ),
				);
			endwhile;
		endif;

		return $ap_data;
	}

	/**
	 * Return data for user profile dropdown menu.
	 * @return array
	 * @since  3.0.0
	 */
	public static function user_menu_dropdown() {
		$ap_data = array(
			'active_page' => get_query_var( 'user_page' ) ? esc_attr( get_query_var( 'user_page' ) ) : 'about',
		);
		$ap_data['links'] = ap_get_user_menu( get_current_user_id() );

		return $ap_data;
	}

}

