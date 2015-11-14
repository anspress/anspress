<?php
/**
 * AnsPress user
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      http://anspress.io
 * @copyright 2014 Rahul Aryan
 */
$user_pages = array();

class AnsPress_User
{
	/**
	 * Store upload error
	 * @var string
	 */
	private $upload_error;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 * @param AnsPress $ap
	 */
	public function __construct( $ap ) {
		$ap->add_action( 'init', $this, 'init_actions' );
		$ap->add_filter( 'pre_user_query', $this, 'follower_query' );
		$ap->add_filter( 'pre_user_query', $this, 'following_query' );
		$ap->add_filter( 'pre_user_query', $this, 'user_sort_by_reputation' );
		$ap->add_action( 'wp_ajax_ap_cover_upload', $this, 'cover_upload' );
		$ap->add_action( 'wp_ajax_ap_avatar_upload', $this, 'avatar_upload' );
		$ap->add_filter( 'avatar_defaults' , $this, 'default_avatar' );
		$ap->add_filter( 'get_avatar', $this, 'get_avatar', 10, 5 );
		$ap->add_filter( 'ap_user_menu', $this, 'ap_user_menu_icons' );
	}

	/**
	 * Actions to do in init
	 */
	public function init_actions() {
		// Register AnsPress pages.
		ap_register_page( ap_opt( 'users_page_slug' ), __( 'Users', 'ap' ), array( $this, 'users_page' ) );

		ap_register_page( ap_opt( 'user_page_slug' ), __( 'User', 'ap' ), array( $this, 'user_page' ), false );
		ap_register_user_page( 'about', __( 'About', 'ap' ), array( $this, 'about_page' ) );
		ap_register_user_page( 'activity-feed', __( 'Activity Feed', 'ap' ), array( $this, 'feed_page' ), true );
		ap_register_user_page( 'notification', __( 'Notification', 'ap' ), array( $this, 'notification_page' ), true, false );
		ap_register_user_page( 'profile', __( 'Profile', 'ap' ), array( $this, 'profile_page' ), true, false );
		ap_register_user_page( 'questions', __( 'Questions', 'ap' ), array( $this, 'questions_page' ) );
		ap_register_user_page( 'answers', __( 'Answers', 'ap' ), array( $this, 'answers_page' ) );
		ap_register_user_page( 'followers', __( 'Followers', 'ap' ), array( $this, 'followers_page' ) );
		ap_register_user_page( 'following', __( 'Following', 'ap' ), array( $this, 'following_page' ) );
		add_filter( 'ap_page_title', array( $this, 'ap_page_title' ) );
	}

	/**
	 * Register users directory page in AnsPress
	 */
	public function users_page() {
		if ( ap_opt( 'enable_users_directory' ) ) {

			global $ap_user_query;
			$ap_user_query = ap_has_users();
			include ap_get_theme_location( 'users/users.php' );

		} else {
			_e( 'User directory is disabled.', 'ap' );
		}
	}

	/**
	 * Register user page in AnsPress
	 */
	public function user_page() {
		// Return if user profile is not active
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
			_e( 'No user found', 'ap' );
		}
	}

	/**
	 * Output user about page
	 * @since 2.3
	 */
	public function about_page() {
		ap_get_template_part( 'user/about' );
	}

	/**
	 * Output user feed page.
	 * @since 2.4
	 */
	public function feed_page() {
		global $ap_activities;
		$paged = get_query_var( 'paged', 1 );
	    $ap_activities = ap_get_activities( array( 'per_page' => 20, 'subscriber' => true, 'user_id' => ap_get_displayed_user_id(), 'orderby' => 'created', 'order' => 'DESC', 'paged' => $paged ) );
		ap_get_template_part( 'user/activity-feed' );
	}

	/**
	 * Output notification page
	 * @since 2.3
	 */
	public function notification_page() {
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
	public function activity_page() {
		include ap_get_theme_location( 'user/activity.php' );
	}

	/**
	 * Output for profile page
	 * @since 2.1
	 */
	public function profile_page() {

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
	public function questions_page() {
		global $questions;
		$questions = ap_get_questions( array( 'author' => ap_get_displayed_user_id() ) );
		ap_get_template_part( 'user/user-questions' );
		wp_reset_postdata();
	}

	/**
	 * Output for user answers page
	 * @since 2.0.1
	 */
	public function answers_page() {
		global $answers;
		$answers = ap_get_answers( array( 'author' => ap_get_displayed_user_id() ) );
		include ap_get_theme_location( 'user/user-answers.php' );
		wp_reset_postdata();
	}

	/**
	 * Register user foloowers page.
	 */
	public function followers_page() {
		$followers = ap_has_users( array(
			'user_id' 	=> ap_get_displayed_user_id(),
			'sortby' 	=> 'followers',
		) );

		if ( $followers->has_users() ) {
			include ap_get_theme_location( 'user/followers.php' );
		} else {
			esc_attr_e( 'No followers found', 'ap' );
		}

	}

	/**
	 * Register followers page in AnsPress
	 */
	public function following_page() {
		$following = ap_has_users( array( 'user_id' => ap_get_displayed_user_id(), 'sortby' => 'following' ) );

		if ( $following->has_users() ) {
			include ap_get_theme_location( 'user/following.php' );
		} else {
			esc_attr_e( 'You are not following anyone.', 'ap' );
		}
	}

	
	/**
	 * Filter AnsPress page title for user sub pages
	 * @param  string $title Title.
	 * @return string
	 */
	public function ap_page_title($title) {

		if ( is_ap_user() ) {

			$active = ap_active_user_page();
			$name = ap_user_get_the_display_name();
			$my = ap_is_my_profile();
			$user_pages = anspress()->user_pages;

			if ( 'activity' == $active ) {
				$title = $my ?  __( 'My activity', 'ap' ) : sprintf( __( '%s\'s activity', 'ap' ), $name );
			} elseif ('profile' == $active)
				$title = $my ?  __( 'My profile', 'ap' ) : sprintf( __( '%s\'s profile', 'ap' ), $name );

			elseif ('questions' == $active)
				$title = $my ?  __( 'My questions', 'ap' ) : sprintf( __( '%s\'s questions', 'ap' ), $name );

			elseif ('answers' == $active)
				$title = $my ?  __( 'My answers', 'ap' ) : sprintf( __( '%s\'s answers', 'ap' ), $name );

			elseif ('reputation' == $active)
				$title = $my ?  __( 'My reputation', 'ap' ) : sprintf( __( '%s\'s reputation', 'ap' ), $name );

			elseif ('about' == $active)
				$title = $my ?  __( 'About me', 'ap' ) : sprintf( __( '%s', 'ap' ), $name );

			elseif ('followers' == $active)
				$title = $my ?  __( 'My followers', 'ap' ) : sprintf( __( '%s\'s followers', 'ap' ), $name );

			elseif ('following' == $active)
				$title = __( 'Following', 'ap' );

			elseif ('subscription' == $active)
				$title = __( 'My subscriptions', 'ap' );

			elseif ('notification' == $active)
				$title = __( 'My notification', 'ap' );
			else
				$title = $user_pages[$active]['title'];
		}

		return $title;
	}

	/**
	 * For modifying WP_User_Query, if passed with a var ap_followers_query
	 * @param  array $query Mysql clauses.
	 * @return array
	 */
	public function follower_query( $query ) {

		if ( isset( $query->query_vars['ap_query'] ) &&
			$query->query_vars['ap_query'] == 'user_sort_by_followers' &&
			isset( $query->query_vars['user_id'] ) ) {

			global $wpdb;

			$query->query_from = $query->query_from.' LEFT JOIN '.$wpdb->prefix."ap_meta M ON $wpdb->users.ID = M.apmeta_userid";

			$userid = $query->query_vars['user_id'];

			$query->query_where = $query->query_where." AND M.apmeta_type = 'follower' AND M.apmeta_actionid = $userid";
		}

		return $query;
	}

	/**
	 * Modify user query to get following users
	 * @param  array $query Mysql claueses
	 * @return array
	 */
	public function following_query($query) {

		if ( isset( $query->query_vars['ap_query'] ) &&
			$query->query_vars['ap_query'] == 'user_sort_by_following' &&
			isset( $query->query_vars['user_id'] ) ) {

			global $wpdb;

			$query->query_from = $query->query_from.' LEFT JOIN '.$wpdb->prefix."ap_meta M ON $wpdb->users.ID = M.apmeta_actionid";
			$userid = $query->query_vars['user_id'];
			$query->query_where = $query->query_where." AND M.apmeta_type = 'follower' AND M.apmeta_userid = $userid";
		}

		return $query;
	}

	/**
	 * Filter user query so that it can be sorted by user reputation
	 * @param  array $query Mysql claueses.
	 * @return array
	 */
	public function user_sort_by_reputation($query) {
		global $wpdb;

		if ( isset( $query->query_vars['ap_query'] ) ) {

			$query->query_where = $query->query_where." AND (apm1.user_id IS NULL OR (  apm1.meta_value != 1) )";

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
	public function unique_filename_callback( $dir, $user_id, $ext ) {
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
	public function upload_photo($file_name) {

		if ( $_FILES[ $file_name ]['size'] > ap_opt( 'max_upload_size' ) ) {
			$this->upload_error  = sprintf( __( 'File cannot be uploaded, size is bigger then %d Byte', 'ap' ), ap_opt( 'max_upload_size' ) );
			return false;
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
				'unique_filename_callback' => array( $this, 'unique_filename_callback' ),
			 ) );

			if ( empty( $photo[ 'file' ] ) || isset( $photo['error'] ) ) {
				// Handle failures.
				$this->upload_error = __( 'There was an error while uploading avatar, please check your image', 'ap' );
				return false;
			}

			return $photo;
		}
	}

	/**
	 * Process cover upload form
	 */
	public function cover_upload() {

		if ( ap_user_can_upload_cover() && ap_verify_nonce( 'upload_cover_'.get_current_user_id() ) ) {

			$photo = $this->upload_photo( 'image' );

			if ( $photo === false ) {
				ap_send_json( ap_ajax_responce( array( 'message' => $this->upload_error, 'message_type' => 'error' ) ) );
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
				'message' 	=> __( 'Cover photo uploaded successfully.', 'ap' ),
				'user_id' 	=> $userid,
				'image' 	=> ap_get_cover_src( $userid ),
			) );
		}

		ap_ajax_json( array(
			'message' => __( 'There was an error while uploading cover photo, please check your image and try again.', 'ap' ),
			'message_type' => 'error',
		) );
	}

	/**
	 * Process ajax user avatar upload request.
	 * Sanitize file and pass to upload_file(). Rename image to md5 and store file
	 * name in user meta. Also remove existing avtar if exists
	 * @return void
	 */
	public function avatar_upload() {

		if ( ap_user_can_upload_avatar() && ap_verify_nonce( 'upload_avatar_'.get_current_user_id() ) ) {

			$photo = $this->upload_photo( 'thumbnail' );

			if ( false === $photo ) {
				ap_send_json( ap_ajax_responce( array( 'message' => $this->upload_error, 'message_type' => 'error' ) ) );
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
				'message' 	=> __( 'Avatar uploaded successfully.', 'ap' ),
				'do' 		=> array('replaceWith'),
				'html' 		=> get_avatar( $userid, 150 ),
				'container' => '[data-view="user_avatar_'.$userid.'"]',
			) );
		}

		ap_ajax_json( array(
			'message' 		=> __( 'There was an error while uploading avatar, please check your image', 'ap' ),
			'message_type' 	=> 'error',
		) );

	}

	/**
	 * Add AnsPress avtar in Wp discussion setting
	 * @param  array $avatar_defaults Avatar types.
	 * @return array
	 */
	public function default_avatar($avatar_defaults) {
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
	public function get_avatar($avatar, $id_or_email, $size, $default, $alt) {

		if ( ! empty( $id_or_email ) ) {

			if ( is_object( $id_or_email ) ) {
				$allowed_comment_types = apply_filters('get_avatar_comment_types', array(
					'comment'
					));

				if ( ! empty( $id_or_email->comment_type ) && ! in_array( $id_or_email->comment_type, (array) $allowed_comment_types ) ) {
					return $avatar;
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

			$ap_avatar     = ap_get_avatar_src( $id_or_email, ($size > 50 ? false:  true) );

			if ( $ap_avatar !== false ) {
				return "<img data-view='user_avatar_{$id_or_email}' alt='{$alt}' src='{$ap_avatar}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
			}
		}

		if ( strpos( $avatar, 'ANSPRESS_AVATAR_SRC' ) !== false ) {
			$display_name = ap_user_display_name( array( 'user_id' => $id_or_email ) );

			return '<img data-view="user_avatar_'.$id_or_email.'" alt="' . $alt . '" data-name="' . $display_name . '" data-height="' . $size . '" data-width="' . $size . '" class="avatar avatar-' . $size . ' photo ap-dynamic-avatar" />';
		}

		return $avatar;
	}

	/**
	 * Set icon class for user menus
	 * @param  array $menus AnsPress user menu.
	 * @return array
	 * @since 2.0.1
	 */
	public function ap_user_menu_icons($menus) {

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

		foreach ( $icons as $k => $i ) {
			if ( isset( $menus[ $k ] ) ) {
				$menus[ $k ]['class'] = $i;
			}
		}

		return $menus;
	}
}

