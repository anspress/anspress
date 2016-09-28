<?php
/**
 * AnsPress user
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 */

/**
 * Register user page
 * @param  string   $page_slug  slug for links
 * @param  string   $page_title Page title
 * @param  callable $func Hook to run when shortcode is found.
 * @return void
 * @since 2.0.1
 */

function ap_register_user_page($page_slug, $page_title, $func, $show_in_menu = true, $public = true) {
	anspress()->user_pages[$page_slug] = array( 'title' => $page_title, 'func' => $func, 'show_in_menu' => $show_in_menu, 'public' => $public );
}
/**
 * Count user posts by post type
 * @param  int    $userid
 * @param  string $post_type
 * @return int
 * @since unknown
 */
function ap_count_user_posts_by_type($userid, $post_type = 'question') {

	global $wpdb;

	$where = get_posts_by_author_sql( $post_type, true, $userid );

	$query = "SELECT COUNT(*) FROM $wpdb->posts $where";

	$key = md5( $query );
	$cache = wp_cache_get( $key, 'count' );

	if ( $cache === false ) {
		$count = $wpdb->get_var( $query );
		wp_cache_set( $key, $count, 'count' );
	} else {
		$count = $cache;
	}

	return apply_filters( 'ap_count_user_posts_by_type', $count, $userid );
}

/**
 * retrive user question counts
 * @param  int $userid
 * @return int
 * @since unknown
 */
function ap_user_question_count($userid) {
	return ap_count_user_posts_by_type( $userid, $post_type = 'question' );
}

/**
 * get user answer counts
 * @param  int $userid
 * @return int
 * @since unknown
 */
function ap_user_answer_count($userid) {
	return ap_count_user_posts_by_type( $userid, $post_type = 'answer' );
}

function ap_user_best_answer_count($user_id) {

	global $wpdb;
	$query = $wpdb->prepare( "SELECT count(DISTINCT pm.post_id) FROM $wpdb->postmeta pm JOIN $wpdb->posts p ON (p.ID = pm.post_id) WHERE pm.meta_key = '".ANSPRESS_BEST_META."' AND pm.meta_value = 1 AND p.post_type = 'answer' AND p.post_author = %d", $user_id );

	$key = md5( $query );
	$cache = wp_cache_get( $key, 'count' );

	if ( $cache === false ) {
		$count = $wpdb->get_var( $query );
		wp_cache_set( $key, $count, 'count' );
	} else {
		$count = $cache;
	}

	return apply_filters( 'ap_user_best_answer_count', $count, $user_id );
}

function ap_user_solved_answer_count($user_id) {

	global $wpdb;
	$query = $wpdb->prepare( "SELECT count(DISTINCT pm.post_id) FROM $wpdb->postmeta pm JOIN $wpdb->posts p ON (p.ID = pm.post_id) WHERE pm.meta_key = '".ANSPRESS_SELECTED_META."' AND pm.meta_value is not null AND pm.meta_value != 0 AND p.post_type = 'question' AND p.post_author = %d", $user_id );

	$key = md5( $query );
	$cache = wp_cache_get( $key, 'count' );

	if ( $cache === false ) {
		$count = $wpdb->get_var( $query );
		wp_cache_set( $key, $count, 'count' );
	} else {
		$count = $cache;
	}

	return apply_filters( 'ap_user_best_answer_count', $count, $user_id );
}

/**
 * For user display name
 * It can be filtered for adding cutom HTML
 * @param  mixed $args
 * @return string
 * @since 0.1
 */
function ap_user_display_name($args = array()) {

	global $post;

	$defaults = array(
		'user_id'            => get_the_author_meta( 'ID' ),
		'html'                => false,
		'echo'                => false,
		'anonymous_label'    => __( 'Anonymous', 'anspress-question-answer' ),
	);

	if ( ! is_array( $args ) ) {
		$defaults['user_id'] = $args;
		$args = $defaults;
	} else {
		$args = wp_parse_args( $args, $defaults );
	}

	extract( $args );

	$user = get_userdata( $user_id );

	if ( $user ) {
		if ( ! $html ) {
			$return = $user->display_name;
		} else {
			$return = '<span class="who"><a href="'.ap_user_link( $user_id ).'">'.$user->display_name.'</a></span>';
		}
	} elseif ( $post && ($post->post_type == 'question' || $post->post_type == 'answer') ) {
		$name = get_post_meta( $post->ID, 'anonymous_name', true );

		if ( ! $html ) {
			if ( $name != '' ) {
				$return = $name;
			} else {
				$return = $anonymous_label;
			}
		} else {
			if ( $name != '' ) {
				$return = '<span class="who">'.$name.__( ' (anonymous)', 'anspress-question-answer' ).'</span>';
			} else {
				$return = '<span class="who">'.$anonymous_label.'</span>';
			}
		}
	} else {
		if ( ! $html ) {
			$return = $anonymous_label;
		} else {
			$return = '<span class="who">'.$anonymous_label.'</span>';
		}
	}

	/**
	 * FILTER: ap_user_display_name
	 * Filter can be used to alter display name
	 * @var string
	 * @since 2.0.1
	 */
	$return = apply_filters( 'ap_user_display_name', $return, $args );

	if ( $echo !== false ) {
		echo $return;
		return;
	}

	return $return;
}

/**
 * Return Link to user pages
 * @param  boolean|integer $user_id    user id
 * @param  string          $sub        page slug
 * @return string
 * @since  unknown
 */
function ap_user_link($user_id = false, $sub = false) {

	if ( false === $user_id ) {
		$user_id = get_the_author_meta( 'ID' );
	}

	if ( $user_id < 1 ) {
		return '#AnonymousUser';
	}

	if ( ap_opt( 'user_profile' ) == '' ) {
		return apply_filters( 'ap_user_custom_profile_link', $user_id, $sub );
	} elseif ( function_exists( 'bp_core_get_userlink' ) && ap_opt( 'user_profile' ) == 'buddypress' ) {
		return bp_core_get_userlink( $user_id, false, true );
	} elseif ( ap_opt( 'user_profile' ) == 'userpro' ) {
		global $userpro;
		return $userpro->permalink( $user_id );
	}

	if ( 0 == $user_id ) {
		return false;
	}

	$user = get_user_by( 'id', $user_id );

	// If permalink is enabled.
	if ( get_option( 'permalink_structure' ) != '' ) {

		if ( ! ap_opt( 'base_before_user_perma' ) ) {
			$base = home_url( '/'.ap_get_user_page_slug().'/' );
		} else {
			$base = ap_get_link_to( ap_get_user_page_slug() );
		}

		if ( $sub === false ) {
			$link = $base. $user->user_login.'/';
		} elseif ( is_array( $sub ) ) {
			$link = $base . $user->user_login.'/';

			if ( ! empty( $sub ) ) {
				foreach ( $sub as $s ) {
					$link .= $s.'/'; }
			}
		} elseif ( ! is_array( $sub ) ) {
			$link = $base. $user->user_login.'/'.$sub.'/';
		}
	} else {
		if ( false === $sub ) {
			$sub = array( 'ap_page' => 'user', 'ap_user' => $user->user_login );
		} elseif ( is_array( $sub ) ) {
			$sub['ap_page']  = 'user';
			$sub['ap_user']     = $user->user_login;
		} elseif ( ! is_array( $sub ) ) {
			$sub = array( 'ap_page' => 'user', 'ap_user' => $user->user_login, 'user_page' => $sub );
		}

			$link = ap_get_link_to( $sub );
	}

	return apply_filters( 'ap_user_link', $link, $user_id );
}

/**
 * Get user menu array items
 * @param  boolean|integer $user_id
 * @return array
 */
function ap_get_user_menu($user_id = false, $private = false) {

	if ( $user_id === false ) {
		$user_id = ap_get_displayed_user_id();
	}

	$user_pages = anspress()->user_pages;

	$menus = array();

	$i = 1;
	foreach ( $user_pages as $k => $args ) {
		$link        = ap_user_link( $user_id, $k );

		$title = $k == 'notification' ? $args['title'].ap_get_the_total_unread_notification( $user_id, false ): $args['title'];

		$menus[$k]    = array( 'slug' => $k, 'title' => $title, 'link' => $link, 'order' => 5 + $i, 'show_in_menu' => $args['show_in_menu'], 'public' => $args['public'] );

		$i++;
	}

	/**
	 * FILTER: ap_user_menu
	 * filter is applied before showing user menu
	 * @var array
	 * @since  unknown
	 */
	$menus = apply_filters( 'ap_user_menu', $menus );

	$menus = ap_sort_array_by_order( $menus );

	return $menus;
}

/**
 * Output user menu
 * Extract menu from registered user pages
 * @return void
 * @since 2.0.1
 */
function ap_user_menu($collapse = true, $user_id = false) {

	if ( false === $user_id ) {
		$user_id = ap_get_displayed_user_id(); }

	$menus = ap_get_user_menu( $user_id );

	foreach ( $menus as $k => $m ) {
		if ( ( false === $m['public'] && ! ap_is_my_profile( )) ) {
			unset( $menus[$k] );
		}
	}

	$active_user_page   = get_query_var( 'user_page' );
	$active_user_page   = ap_active_user_page();

	if ( ! empty( $menus ) && is_array( $menus ) ) {

		$o = '<ul id="ap-user-menu" class="ap-user-menu '.($collapse ? 'ap_collapse_menu' : '').' clearfix">';

		foreach ( $menus as $m ) {
			$class = ! empty( $m['class'] ) ? ' '.$m['class'] : '';
			$o .= '<li'.($active_user_page == $m['slug'] ? ' class="active"' : '').'><a href="'.$m['link'].'" class="ap-user-menu-'.$m['slug'].$class.'">'.$m['title'].'</a></li>';
		}

		/*
        if ( $collapse ) {
            $o .= '<li class="ap-user-menu-more ap-dropdown"><a href="#" class="ap-dropdown-toggle">'.__( 'More', 'ap' ).ap_icon( 'chevron-down', true ).'</a><ul class="ap-dropdown-menu"></ul></li>'; }

		$o .= '</ul>';*/
		echo $o;
	}
}

/**
 * @return string
 */
function ap_get_current_user_page_template() {

	$user_page = get_query_var( 'user_page' );
	$user_page = $user_page ? $user_page : 'profile';

	$template = 'user-'.$user_page.'.php';

	return apply_filters( 'ap_get_current_user_page_template', $template );
}

/**
 * Output user page
 * @return void
 * @since  2.0
 */
function ap_user_page() {

	$user_pages     = anspress()->user_pages;
	$user_id        = ap_get_displayed_user_id();
	$user_page      = ap_active_user_page();
	$callback       = @$user_pages[$user_page]['func'];

	if ( $user_id > 0 && ((is_array( $callback ) && method_exists( $callback[0], $callback[1] )) || ( ! is_array( $callback ) && function_exists( $callback )) ) ) {
		call_user_func( $callback );
	} else {
		echo '<div class="ap-page-template-404">'.__( 'Page not found or registered.', 'anspress-question-answer' ).'</div>';
	}
}

/**
 * Return current page in user profile.
 * @since 2.0.1
 * @return string
 * @since 2.4.7 Added new filter `ap_active_user_page`.
 */
function ap_active_user_page() {
	$user_page        = sanitize_text_field( get_query_var( 'user_page' ) );

	if ( ! empty($user_page ) ) {
		return $user_page;
	}

	$page = ap_is_my_profile() ? 'activity-feed' : 'about';

	return apply_filters( 'ap_active_user_page', $page );
}

/**
 * Return meta of active user in user page
 * @param   string $meta key of meta.
 * @return  string
 * @since 	unknown
 */
function ap_get_current_user_meta($meta) {

	global $ap_current_user_meta;

	if ( $meta == 'followers' ) {
		return @$ap_current_user_meta[AP_FOLLOWERS_META] ? $ap_current_user_meta[AP_FOLLOWERS_META] : 0;
	} elseif ( $meta == 'following' ) {
		return @$ap_current_user_meta[AP_FOLLOWING_META] ? $ap_current_user_meta[AP_FOLLOWING_META] : 0;
	} elseif ( isset( $ap_current_user_meta[$meta] ) ) {
		return $ap_current_user_meta[$meta];
	}

	return false;
}

function ap_check_user_profile_complete($user_id) {

	$user_meta = array_map( 'ap_meta_array_map', get_user_meta( $user_id ) );

	$required = apply_filters( 'ap_required_user_fields', array( 'first_name', 'last_name', 'description' ) );

	if ( count( array_diff( array_values( $required ), array_keys( $user_meta ) ) ) == 0 ) {
		return true;
	}

	return false;
}

function ap_check_if_photogenic($user_id) {

	$user_meta = array_map( 'ap_meta_array_map', get_user_meta( $user_id ) );

	$required = apply_filters( 'ap_check_if_photogenic', array( '_ap_cover', '_ap_avatar' ) );

	if ( count( array_diff( array_values( $required ), array_keys( $user_meta ) ) ) == 0 ) {
		return true;
	}

	return false;
}

/**
 * Display users page tab
 * @return void
 */
function ap_users_tab() {
	$active = isset( $_GET['ap_sort'] ) ? $_GET['ap_sort'] : 'reputation';

	$link = ap_get_link_to( 'users' ).'?ap_sort=';

	?>
    <ul class="ap-questions-tab ap-ul-inline clearfix" role="tablist">
        <?php if ( ! ap_opt( 'disable_reputation' ) ) : ?>
            <li class="<?php echo $active == 'reputation' ? ' active' : ''; ?>"><a href="<?php echo $link.'reputation'; ?>"><?php _e( 'Reputation', 'anspress-question-answer' ); ?></a></li>
        <?php endif; ?>
        <li class="<?php echo $active == 'active' ? ' active' : ''; ?>"><a href="<?php echo $link.'active'; ?>"><?php _e( 'Active', 'anspress-question-answer' ); ?></a></li>
        <li class="<?php echo $active == 'best_answer' ? ' active' : ''; ?>"><a href="<?php echo $link.'best_answer'; ?>"><?php _e( 'Best answer', 'anspress-question-answer' ); ?></a></li>
        <li class="<?php echo $active == 'answer' ? ' active' : ''; ?>"><a href="<?php echo $link.'answer'; ?>"><?php _e( 'Answer', 'anspress-question-answer' ); ?></a></li>
        <li class="<?php echo $active == 'newest' ? ' active' : ''; ?>"><a href="<?php echo $link.'newest'; ?>"><?php _e( 'Newest', 'anspress-question-answer' ); ?></a></li>
        <?php
			/**
			 * ACTION: ap_users_tab
			 * Used to hook into users page tab
			 * @since 2.1.0
			 */
			do_action( 'ap_users_tab', $active );
		?>
    </ul>
    <?php
}

/**
 * echo ID of currently displaying user
 * @return integer WordPress user ID
 * @since 2.1
 */
function ap_displayed_user_id() {
	echo ap_get_displayed_user_id();
}
	/**
	 * Return ID of currently displaying user
	 * @return integer WordPress user ID
	 * @since 2.1
	 */
function ap_get_displayed_user_id() {
	$user_id = (int) get_query_var( 'ap_user_id' );

	if ( $user_id > 0 ) {
		return $user_id; }

	return get_current_user_id();
}


/**
 * Retrive image url
 * @param  integer $user_id WordPress user id
 * @param  boolean $small Set as true if you want to get big avatar
 * @return string|false Return url of avatar if file exisits else return false
 * @since  2.1.5
 */
function ap_get_avatar_src($user_id, $small = true) {

	$avatar = get_user_meta( $user_id, '_ap_avatar', true );

	if ( is_array( $avatar ) && ! empty( $avatar ) ) {
		if ( $small && file_exists( $avatar['small_file'] ) ) {
			return $avatar['small_url']; }

		if ( file_exists( $avatar['file'] ) ) {
			return $avatar['url']; }
	}

	return false;
}

	/**
	 * User's top posts tab
	 * @return void
	 * @since 2.1
	 */
function ap_user_top_posts_tab() {
	$active = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'answers';

	$link = '?tab=';
	?>
	<?php printf( __( 'Top %s', 'anspress-question-answer' ), $active ); ?>
    <ul id="ap-user-posts-tab" class="ap-flat-tab ap-ul-inline clearfix" role="tablist">
	<li class="<?php echo $active == 'answers' ? ' active' : ''; ?>"><a href="<?php echo $link.'answers'; ?>"><?php _e( 'Answers', 'anspress-question-answer' ); ?></a></li>
	<li class="<?php echo $active == 'questions' ? ' active' : ''; ?>"><a href="<?php echo $link.'questions'; ?>"><?php _e( 'Questions', 'anspress-question-answer' ); ?></a></li>
	<?php
		/**
		 * ACTION: ap_users_tab
		 * Used to hook into users page tab
		 * @since 2.1.0
		 */
		do_action( 'ap_users_tab', $active );
	?>
    </ul>
	<?php
}

	/**
	 * User's subscription tab
	 * @return void
	 * @since 2.1
	 */
function ap_user_subscription_tab() {
	$active = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'question';

	$link = '?tab=';

	printf( __( 'My subscriptions', 'anspress-question-answer' ), $active );

	?>
    <ul id="ap-user-posts-tab" class="ap-flat-tab ap-ul-inline clearfix" role="tablist">
	<li class="<?php echo $active == 'question' ? ' active' : ''; ?>"><a href="<?php echo $link.'question'; ?>"><?php _e( 'Questions', 'anspress-question-answer' ); ?></a></li>
	<?php
		/**
		 * ACTION: ap_user_subscription_tab
		 * Used to hook into users page tab
		 * @since 2.3
		 */
		do_action( 'ap_user_subscription_tab', $active );
	?>
    </ul>
	<?php
}

	/**
	 * Display user meta
	 * @param   boolean       $html  for html output
	 * @param   false|integer $user_id  User id, if empty then post author witll be user
	 * @param   boolen        $echo
	 * @return  string
	 */
function ap_user_display_meta($html = false, $user_id = false, $echo = false) {

	if ( false === $user_id ) {
		$user_id = get_the_author_meta( 'ID' );
	}

	$metas = array();

	$metas['display_name'] = '<span class="ap-user-meta ap-user-meta-display_name">'. ap_user_display_name( array( 'html' => true ) ) .'</span>';

	/**
	 * FILTER: ap_user_display_meta_array
	 * Can be used to alter user display meta
	 * @var array
	 */
	$metas = apply_filters( 'ap_user_display_meta_array', $metas, $user_id );

	$output = '';

	if ( ! empty( $metas ) && is_array( $metas ) && count( $metas ) > 0 ) {
		$output .= '<span class="ap-user-meta">';
		foreach ( $metas as $meta ) {
			$output .= $meta.' ';
		}
		$output .= '</span>';
	}

	if ( $echo ) {
		echo $output;
	} else {
		return $output;
	}
}

	/**
	 * Output user profile photo upload form
	 * @return void
	 * @since 2.1.5
	 */
function ap_avatar_upload_form() {
	if ( ap_user_can_upload_avatar() ) {
		?>
            <form method="post" action="#" enctype="multipart/form-data" data-action="ap_upload_form" class="ap-avatar-upload-form">
            <div class="ap-btn ap-upload-o <?php echo ap_icon( 'upload' ); ?>" title="<?php _e( 'Upload an avatar', 'anspress-question-answer' ); ?>">
                <span><?php _e( 'Upload avatar', 'anspress-question-answer' ); ?></span>
                <input type="file" name="thumbnail" class="ap-upload-input" data-action="ap_upload_field">
            </div>
            <input type='hidden' value='<?php echo wp_create_nonce( 'upload_avatar_'.get_current_user_id() ); ?>' name='__nonce' />
            <input type="hidden" name="action" id="action" value="ap_avatar_upload">
        </form>
        <?php
	}
}

	/**
	 * Output user profile tab
	 * @return string
	 */
function ap_user_profile_tab() {
	$param = array();

	$group = isset( $_GET['group'] ) ? $_GET['group'] : 'basic';

	$link = ap_user_link( ap_get_displayed_user_id(), 'profile' );

	$navs = array(
	'basic' => array( 'link' => add_query_arg( array( 'group' => 'basic' ), $link ), 'title' => __( 'Basic', 'anspress-question-answer' ) ),
	'account' => array( 'link' => add_query_arg( array( 'group' => 'account' ), $link ), 'title' => __( 'Account', 'anspress-question-answer' ) ),
	);

	/**
	 * FILTER: ap_questions_tab
	 * Before prepering questions list tab.
	 * @var array
	 * @since 2.0.1
	 */
	$navs = apply_filters( 'ap_user_profile_tab', $navs );

	echo '<ul id="ap-profile-tab" class="ap-questions-tab ap-ul-inline clearfix">';
	foreach ( $navs as $k => $nav ) {
		echo '<li class="ap-profile-tab-'.esc_attr( $k ).( $group == $k ? ' active' : '') .'"><a href="'. esc_url( $nav['link'] ) .'">'. $nav['title'] .'</a></li>';
	}
	echo '</ul>';

	?>
	<?php
}

/**
 * Check if currently displayed user profile is for current user
 * @param  boolean $user_id Its @deprecated since 2.4.
 * @return boolean
 */
function ap_is_my_profile($user_id = false) {
	if ( false !== $user_id ) {
		_deprecated_argument( __FUNCTION__, '2.4', __( 'Passing user_id in ap_is_my_profile is deprecated, function will check again currently logged in user.', 'anspress-question-answer' ) );
	}

	$user_id = get_current_user_id();

	if ( is_user_logged_in() && $user_id == ap_get_displayed_user_id() ) {
		return true;
	}

	return false;
}

/**
 * @param string $page
 */
function ap_is_user_page_public($page) {
	$user_pages = anspress()->user_pages;

	if ( isset( $user_pages[$page] ) && $user_pages[$page]['public'] ) {
		return true; }

	return false;
}

	/**
	 * Update users question count meta
	 * @param  integer $question_id     WordPress post ID
	 * @return void
	 * @since 2.3
	 */
function ap_update_user_questions_count_meta($question_id) {
	$post = get_post( $question_id );

	if ( $post->post_type == 'question' ) {
		update_user_meta( $post->post_author, '__total_questions', ap_user_question_count( $post->post_author ) ); }
}

	/**
	 * Update users answer count meta
	 * @param  integer $answer_id     WordPress post ID
	 * @return void
	 * @since 2.3
	 */
function ap_update_user_answers_count_meta($answer_id) {
	$post = get_post( $answer_id );

	if ( $post->post_type == 'answer' ) {
		update_user_meta( $post->post_author, '__total_answers', ap_user_answer_count( $post->post_author ) ); }
}

	/**
	 * Update users best answer count
	 * @param  integer $user_id     WordPress user ID
	 * @return void
	 * @since 2.3
	 */
function ap_update_user_best_answers_count_meta($user_id = false) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id(); }

	update_user_meta( $user_id, '__best_answers', ap_user_best_answer_count( $user_id ) );
}

function ap_update_user_solved_answers_count_meta($user_id = false) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id(); }

	update_user_meta( $user_id, '__solved_answers', ap_user_solved_answer_count( $user_id ) );
}

/**
 * Get last 28 days reputation earned by user group by day.
 * This data is used in bard chart
 *
 * @param  boolean|integer $user_id    WordPress user ID if false @see ap_get_displayed_user_id() will be used
 * @param  boolean         $object     Return object or string
 * @return string|object
 */
function ap_user_get_28_days_reputation($user_id = false, $object = false) {

	if ( $user_id === false ) {
		$user_id = ap_get_displayed_user_id(); }

	global $wpdb;

	$current_time = current_time( 'mysql' );

	$query = $wpdb->prepare( "SELECT sum(v.apmeta_value) as points, date_format(v.apmeta_date, '%%m.%%d') as day FROM ".$wpdb->prefix."ap_meta v WHERE v.apmeta_type='reputation' AND v.apmeta_userid = %d AND v.apmeta_date BETWEEN %s - INTERVAL 28 DAY AND %s group by date_format(v.apmeta_date,'%%m.%%d')", $user_id, $current_time, $current_time );

	$key = md5( $query );

	$result = wp_cache_get( $key, 'ap' );

	if ( $result === false ) {
		$result = $wpdb->get_results( $query );
		wp_cache_set( $key, $result, 'ap' );
	}

	$days = array();

	for ( $i = 0; $i < 28; $i++ ) {
		$days[date( 'm.d', strtotime( $i.' days ago' ) )] = 0;
	}

	if ( $result ) {
		foreach ( $result as $reputation ) {
			$days[$reputation->day]  = $reputation->points;
		}
	}

	$days = array_reverse( $days );

	if ( $object === false ) {
		return implode( ',', $days ); }

	return (object) $days;
}

/**
 * Output user cover upload form
 */
function ap_cover_upload_form() {
	if ( ap_user_can_upload_cover() ) {
		?>
            <form method="post" action="#" enctype="multipart/form-data" data-action="ap_upload_form" class="ap-avatar-upload-form">
            <div class="ap-btn ap-upload-o <?php echo ap_icon( 'upload' ); ?>" title="<?php _e( 'Upload a cover photo', 'anspress-question-answer' ); ?>">
                <span><?php _e( 'Upload cover', 'anspress-question-answer' ); ?></span>
                <input type="file" name="image" class="ap-upload-input" data-action="ap_upload_field">
            </div>
            <input type='hidden' value='<?php echo wp_create_nonce( 'upload_cover_'.get_current_user_id() ); ?>' name='__nonce' />
            <input type="hidden" name="action" id="action" value="ap_cover_upload">
        </form>
        <?php
	}
}

function ap_get_cover_src($user_id = false, $small = false) {

	if ( $user_id === false ) {
		$user_id = ap_get_displayed_user_id();
	}

	$cover = get_user_meta( $user_id, '_ap_cover', true );

	if ( is_array( $cover ) && ! empty( $cover ) ) {

		if ( $small && file_exists( $cover['small_file'] ) ) {
			return esc_url( $cover['small_url'] ); }

		if ( file_exists( $cover['file'] ) ) {
			return esc_url( $cover['url'] ); }
	} else {
		if ( $small ) {
			return ap_get_theme_url( 'images/small_cover.jpg' ); } else {
			return ap_get_theme_url( 'images/cover.jpg' ); }
	}
}

function ap_hover_card_ajax_query($user_id = false) {
	if ( $user_id === false ) {
		$user_id = ap_get_displayed_user_id(); }
	return 'action=ap_ajax&ap_ajax_action=user_cover&user_id='.$user_id;
}

/**
 * Return or echo hovercard data attribute.
 * @param  integer $user_id User id.
 * @param  boolean $echo    Echo or return? default is true.
 * @return string
 */
function ap_hover_card_attributes($user_id, $echo = true) {
	if ( $user_id > 0 ) {
		$attr = ' data-userid="'.$user_id.'"';

		if ( true !== $echo ) {
			return $attr;
		}

		echo $attr;
	}
}

/**
 * Ouput user avatar with link and hovercard attribute.
 * @param  integer $user_id User id.
 * @param  integer $size    Avatar size.
 * @return void
 */
function ap_user_link_avatar( $user_id, $size = 30 ) {
	echo '<a href="'.ap_user_link( $user_id ).'" '. ap_hover_card_attributes( $user_id, false ) .'>';
	echo get_avatar( $user_id, $size );
	echo '</a>';
}

/**
 * Check if AnsPress profile is active.
 * @return boolean Return true if AnsPress profile is active.
 */
function ap_is_profile_active() {
	$option = ap_opt( 'user_profile' );

	if ( empty( $option ) ) {
		$option = 'anspress';
	}

	return apply_filters( 'ap_user_profile_active', 'anspress' == $option );
}

/**
 * Return the slug of user page
 * @return string
 */
function ap_get_user_page_slug() {
	$slug = ap_opt( 'user_page_slug' );

	if ( ! empty( $slug ) ) {
		return $slug;
	}

	return 'user';
}

/**
 * @param string $user_id
 */
function ap_user_link_anchor($user_id, $echo = true) {

	$name = ap_user_display_name( $user_id );

	if ( $user_id < 1 ) {
		if ( $echo ) {
			echo $name;
		} else {
			return $name;
		}
	}

	$html = '<a href="'.ap_user_link( $user_id ).'"' . ap_hover_card_attributes( $user_id, false ). '>';
	$html .= $name;
	$html .= '</a>';

	if ( $echo ) {
		echo $html;
	}

	return $html;
}

/**
 * Count total numbers of comment by a user.
 * @param  boolean|integer $user_id User ID.
 * @return integer
 * @since  2.4.7
 */
function ap_user_comment_count( $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if( empty( $user_id ) ){
		return 0;
	}

	$key = 'comment_count_'.$user_id;

	$cache = wp_cache_get( $key, 'ap_user_comment_count' );

	if ( false !== $cache ) {
		return $cache;
	}

	global $wpdb;

	$count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE user_id = %d and comment_approved = 1", $user_id ) );

	wp_cache_set( $key, $count, 'ap_user_comment_count' );

	return (int) $count;
}
