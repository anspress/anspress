<?php
/**
 * AnsPress theme and template handling.
 *
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 *
 * @link      https://anspress.io
 *
 * @copyright 2014 Rahul Aryan
 * @package AnsPress/theme
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Return current page title.
 * @return string current title
 */
function ap_page_title() {

	$pages = anspress()->pages;

	$current_page = ap_current_page();

	if ( is_question() ) {
		if ( ! ap_user_can_read_question( get_question_id() ) ) {
			$new_title = __('No permission', 'anspress-question-answer' );
		} else {
			$new_title = ap_question_title_with_solved_prefix();
		}
	} elseif ( is_ap_search() ) {
		$new_title = sprintf( ap_opt( 'search_page_title' ), sanitize_text_field( get_query_var( 'ap_s' ) ) );
	} elseif ( is_ask() ) {
		$new_title = ap_opt( 'ask_page_title' );
	} elseif ( '' === $current_page && ! is_question() && '' == get_query_var( 'question_name' ) ) {
		$new_title = ap_opt( 'base_page_title' );
	} elseif ( get_query_var( 'parent' ) !== '' ) {
		$new_title = sprintf( __( 'Discussion on "%s"', 'anspress-question-answer' ), get_the_title( get_query_var( 'parent' ) ) );
	} elseif ( isset( $pages[ $current_page ]['title'] ) ) {
		$new_title = $pages[ $current_page ]['title'];
	} else {
		$new_title = __( 'Error 404', 'anspress-question-answer' );
	}

	$new_title = apply_filters( 'ap_page_title', $new_title );

	return $new_title;
}

/**
 * Check if current page is AnsPress search page
 * @return boolean
 */
function is_ap_search() {
	if ( is_anspress() && get_query_var( 'ap_page' ) == 'search' ) {
		return true;
	}

	return false;
}

/**
 * Return current AnsPress page
 * @return string|false
 */
function ap_current_page_is() {
	if ( is_anspress() ) {
		if ( is_question() ) {
			$template = 'question';
		} elseif ( is_ask() ) {
			$template = 'ask';
		} elseif ( is_question_categories() ) {
			$template = 'categories';
		} elseif ( is_question_cat() ) {
			$template = 'category';
		} elseif ( is_question_edit() ) {
			$template = 'edit-question';
		} elseif ( is_answer_edit() ) {
			$template = 'edit-answer';
		} elseif ( is_ap_search() ) {
			$template = 'search';
		} elseif ( is_ap_revision() ) {
			$template = 'revision';
		} elseif ( get_query_var( 'ap_page' ) == '' ) {
			$template = 'base';
		} else {
			$template = 'not-found';
		}

		return apply_filters( 'ap_current_page_is', $template );
	}

	return false;
}

/**
 * Get current user page template file
 * @return string template file name.
 */
function ap_get_current_page_template() {
	if ( is_anspress() ) {
		$template = ap_current_page_is();

		return apply_filters( 'ap_current_page_template', $template.'.php' );
	}

	return 'content-none.php';
}

/**
 * Get post status.
 * @param boolean|integer $post_id question or answer ID.
 * @return string
 * @since 2.0.0-alpha2
 */
function ap_post_status($post_id = false) {
	if ( false === $post_id ) {
		$post_id = get_the_ID();
	}

	return get_post_status( $post_id );
}

/**
 * Check if current post is private.
 * @param boolean|integer $post_id question or answer ID.
 * @return boolean
 */
function is_private_post($post_id = false) {
	if ( ap_post_status( $post_id ) == 'private_post' ) {
		return true;
	}

	return false;
}

/**
 * Check if post is waiting moderation.
 * @param boolean|integer $post_id question or answer ID.
 * @return bool
 */
function is_post_waiting_moderation($post_id = false) {
	if ( get_post_status( $post_id ) == 'moderate' ) {
		return true;
	}

	return false;
}

/**
 * Check if question is closed.
 *
 * @param boolean|integer $post_id question or answer ID.
 * @return boolean
 * @since 2.0.0-alpha2
 */
function is_post_closed( $post_id = null ) {
	if ( ap_get_post_field( 'closed', $post_id ) ) {
		return true;
	}

	return false;
}

/**
 * Check if question have a parent post.
 * @param boolean|integer $post_id question or answer ID.
 * @return boolean
 * @since   2.0.0-alpha2
 */
function ap_have_parent_post($post_id = false) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	// Get post.
	$post_o = ap_get_post( $post_id );

	if ( $post_o->post_parent > 0 && 'question' == $post_o->post_type ) {
		return true;
	}

	return false;
}

/**
 * Anspress pagination
 * Uses paginate_links.
 *
 * @param float  $current Current paged, if not set then get_query_var('paged') is used.
 * @param int    $total   Total number of pages, if not set then global $questions is used.
 * @param string $format  pagination format.
 *
 * @return string
 */
function ap_pagination( $current = false, $total = false, $format = '?paged=%#%', $page_num_link = false ) {
	global $ap_max_num_pages, $ap_current, $questions;

	if ( is_front_page() ) {
		$format = '';
	}

	$big = 999999999; // Need an unlikely integer.

	if ( false === $current ) {
	    $paged = isset( $_GET['ap_paged'] ) ? (int) $_GET['ap_paged'] : 1;
	    $current = is_front_page() ? max( 1, $paged ) : max( 1, get_query_var( 'paged' ) );
	} elseif ( ! empty( $ap_current ) ) {
	    $current = $ap_current;
	}

	if ( ! empty( $ap_max_num_pages ) ) {
		$total = $ap_max_num_pages;
	} elseif ( false === $total && isset( $questions->max_num_pages ) ) {
		$total = $questions->max_num_pages;
	}

	if ( false === $page_num_link ) {
		$page_num_link = str_replace( array( '&amp;', '&#038;' ), '&', get_pagenum_link( $big ) );
	}

	if ( is_front_page() ) {
		$base = add_query_arg( array( 'ap_paged' => '%#%' ), home_url( '/' ) );
	} else {
		$base = str_replace( $big, '%#%', $page_num_link );
	}

	if ( '1' == $total ) {
		return;
	}

	echo '<div class="ap-pagination clearfix">';
	echo paginate_links( array(
		'base'     => $base,
		'format'   => $format,
		'current'  => $current,
		'total'    => $total,
		'end_size' => 3,
		'mid_size' => 3,
	) );
	echo '</div>';
}

/**
 * Return font icons class of AnsPress.
 * All font icons should be called using this function so that it can be overridden.
 * @param string $name Name or class of font icon.
 * @param bool   $html return icon class wrapped by i tag.
 * @return string
 * @since 2.0.1
 */
function ap_icon($name, $html = false) {
	$icons = array(
		'upload' 			=> 'apicon-upload',
		'unchecked' 		=> 'apicon-checkbox-unchecked',
		'checked' 			=> 'apicon-checkbox-checked',
		'check' 			=> 'apicon-check',
		'select' 			=> 'apicon-check',
		'new_question' 		=> 'apicon-question',
		'new_answer' 		=> 'apicon-answer',
		'new_comment' 		=> 'apicon-talk-chat',
		'new_comment_answer' => 'apicon-mail-reply',
		'edit_question' 	=> 'apicon-pencil',
		'edit_answer' 		=> 'apicon-pencil',
		'edit_comment' 		=> 'apicon-pencil',
		'vote_up' 			=> 'apicon-thumb-up',
		'vote_down' 		=> 'apicon-thumb-down',
		'favorite' 			=> 'apicon-heart',
		'delete' 			=> 'apicon-trashcan',
		'edit' 				=> 'apicon-pencil',
		'comment' 			=> 'apicon-comments',
		'view' 				=> 'apicon-eye',
		'vote' 				=> 'apicon-triangle-up',
		'cross' 			=> 'apicon-x',
		'more' 				=> 'apicon-ellipsis',
		'upload' 			=> 'apicon-cloud-upload',
		'link' 				=> 'apicon-link',
		'help' 				=> 'apicon-question',
		'error' 			=> 'apicon-x',
		'warning' 			=> 'apicon-alert',
		'success' 			=> 'apicon-check',
		'image' 			=> 'apicon-image',
	);

	$icons = apply_filters( 'ap_icon', $icons );
	$icon = '';

	if ( isset( $icons[ $name ] ) ) {
		$icon = $icons[ $name ];
	} else {
		$icon = 'apicon-'.$name;
	}

	$icon = esc_attr( $icon ); // Escape attribute.

	if ( $html ) {
		return '<i class="'.$icon.'"></i> ';
	}

	return $icon;
}

/**
 * Register anspress pages.
 *
 * @param string   $page_slug    slug for links.
 * @param string   $page_title   Page title.
 * @param callable $func         Hook to run when shortcode is found.
 * @param bool     $show_in_menu User can add this pages to their WordPress menu from appearance->menu->AnsPress.
 *
 * @since 2.0.1
 */
function ap_register_page($page_slug, $page_title, $func, $show_in_menu = true) {
	anspress()->pages[ $page_slug ] = array(
		'title' 		=> $page_title,
		'func' 			=> $func,
		'show_in_menu' 	=> $show_in_menu,
	);
}

/**
 * Output current AnsPress page.
 * @since 2.0.0-beta
 */
function ap_page() {
	$pages = anspress()->pages;
	$current_page = ap_current_page();

	if ( is_question() ) {
		$current_page = ap_opt( 'question_page_slug' );
	} elseif ( '' == $current_page && ! is_question() ) {
		$current_page = 'base';
	}

	if ( isset( $pages[ $current_page ]['func'] ) ) {
		call_user_func( $pages[ $current_page ]['func'] );
	} else {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		include ap_get_theme_location( 'not-found.php' );
	}
}

/**
 * Return post actions array.
 *
 * @param mixed $_post Post.
 * @return array
 * @since  3.0.0
 */
function ap_post_actions( $_post = null ) {
	$_post = ap_get_post( $_post );

	$actions = [];

	if ( ! in_array( $_post->post_type, [ 'question', 'answer' ], true ) ) {
		return $actions;
	}

	// Question close action.
	if ( ap_user_can_close_question() && 'question' === $_post->post_type ) {
		$nonce = wp_create_nonce( 'close_' . $_post->ID );
		$close_label = $_post->closed ?  __( 'Open', 'anspress-question-answer' ) : __( 'Close', 'anspress-question-answer' );
		$close_title = $_post->closed ?  __( 'Open this question for new answers', 'anspress-question-answer' ) : __( 'Close this question for new answer.', 'anspress-question-answer' );

		$actions['close'] = array(
			'icon'  => 'apicon-check',
			'query' => [ 'nonce' => $nonce, 'post_id' => $_post->ID ],
			'label' => $close_label,
			'title' => $close_title,
		);
	}

	$actions['status'] = ap_post_status_btn_args( $_post );

	// Edit link.
	$actions['status'] = array(
		'label' => __( 'Edit', 'anspress-question-answer' ),
		'href'  => ap_post_edit_link( $_post ),
	);

	// Edit question link.
	/*if ( ap_user_can_edit_question( $_post->ID ) && 'question' === $_post->post_type ) {
	  $actions['dropdown']['edit_question'] = ap_edit_post_link_html();
	}*/
	/*

	// Edit answer link.
	if ( ap_user_can_edit_answer( $_post->ID ) && $_post->post_type == 'answer' ) {
		$actions['dropdown']['edit_answer'] = ap_edit_post_link_html();
	}

	// Flag link.
	if ( is_user_logged_in() ) {
		$actions['dropdown']['flag'] = ap_flag_btn_html();
	}

	// Featured link.
	if ( is_super_admin() && $_post->post_type == 'question' ) {
		$actions['dropdown']['featured'] = ap_featured_post_btn( $_post->ID );
	}

	// Delete link.
	if ( ap_user_can_delete_post( $_post->ID ) && $_post->post_status != 'trash' ) {
		$actions['dropdown']['delete'] = ap_post_delete_btn_html();
	}

	if ( ap_user_can_delete_post( $_post->ID ) && $_post->post_status == 'trash' ) {
		$actions['dropdown']['restore'] = ap_post_restore_btn_html();
	}

	// Permanent delete link.
	if ( ap_user_can_delete_post( $_post->ID ) ) {
		$actions['dropdown']['permanent_delete'] = ap_post_permanent_delete_btn_html();
	}

	// Convert question to a post.
	if ( is_super_admin( ) && 'question' === $_post->post_type ) {
		$nonce = wp_create_nonce( 'ap_ajax_nonce' );
		$actions['dropdown']['convert_to_post'] = '<a href="#" data-action="ajax_btn" data-query="convert_to_post::'.$nonce.'::'. $_post->ID .'">'.__('Convert to post', 'anspress-question-answer' ).'</a>';
	}*/

	/**
	 * FILTER: ap_post_actions_buttons
	 * For filtering post actions buttons
	 * @var     string
	 * @since   2.0
	 */
	return apply_filters( 'ap_post_actions', array_filter( $actions ) );
}

/**
 * Post actions buttons.
 *
 * @param array $disable pass item to hide.
 * @return string
 * @since 	2.0
 */
function ap_post_actions_buttons() {
	$args = wp_json_encode( [
		'post_id' => get_the_ID(),
		'nonce'   => wp_create_nonce( 'post-actions-' . get_the_ID() ),
	]);

	echo '<post-actions class="ap-dropdown"><button class="ap-btn apicon-dots ap-actions-handle"></button><label><input type="checkbox" ap="actiontoggle" ap-query="' . esc_js( $args ) . '"><ul class="ap-actions"></ul></label></post-actions>';
}

/**
 * Return all shorting types for questions.
 * @param  string $current_url Current page URL.
 * @return array
 * @since  3.0.0 Moved from `ap_question_sorting()`.
 */
function ap_get_question_sorting( $current_url = '' ) {
	if ( is_home() || is_front_page() ) {
		$current_url = home_url( '/' );
	}

	$param = array();
	$search_q = get_query_var( 'ap_s' );

	if ( ! empty( $search_q ) ) {
		$param['ap_s'] = $search_q;
	}

	$link = add_query_arg( $param, $current_url );

	$navs = array(
		[ 'key' => 'active','title' => __( 'Active', 'anspress-question-answer' ) ],
		[ 'key' => 'newest', 'title' => __( 'Newest', 'anspress-question-answer' ) ],
	);

	if ( ! ap_opt( 'disable_voting_on_question' ) ) {
		$navs[] = [ 'key' => 'voted', 'title' => __( 'Voted', 'anspress-question-answer' ) ];
	}

	$navs[] = [ 'key' => 'answers','title' => __( 'Answered', 'anspress-question-answer' ) ];
	$navs[] = [ 'key' => 'unanswered', 'title' => __( 'Unanswered', 'anspress-question-answer' ) ];
	$navs[] = [ 'key' => 'unsolved', 'title' => __( 'Unsolved', 'anspress-question-answer' ) ];
	$navs[] = [ 'key' => 'views', 'title' => __( 'Views', 'anspress-question-answer' ) ];

	$active_sort = 'active';
	if ( isset($_GET['ap_filter'], $_GET['ap_filter']['sort'] ) ) {
		$active_sort = wp_unslash( $_GET['ap_filter']['sort'] );
	}

	// Add active.
	foreach ( (array) $navs as $k => $nav ) {
		if ( $nav['key'] == $active_sort ) {
			$navs[ $k ]['active'] = true;
		}
	}

	/*
     * Filter question sorting.
     * @param array Question sortings.
     * @since 2.3
	 */
	return apply_filters( 'ap_question_sorting', $navs );
}


/**
 * Output answers tab.
 * @param string|boolean $base Current page url.
 * @since 2.0.1
 */
function ap_answers_tab($base = false) {
	$sort = isset( $_GET['ap_sort'] ) ? sanitize_text_field( wp_unslash( $_GET['ap_sort'] ) ) : ap_opt( 'answers_sort' );

	if ( ! $base ) {
		$base = get_permalink();
	}

	$navs = array(
		'active' => array( 'link' => add_query_arg( [ 'ap_sort' => 'active' ], $base ), 'title' => __( 'Active', 'anspress-question-answer' ) ),
	);

	if ( ! ap_opt( 'disable_voting_on_answer' ) ) {
		$navs['voted'] = array( 'link' => add_query_arg( [ 'ap_sort' => 'voted' ], $base ), 'title' => __( 'Voted', 'anspress-question-answer' ) );
	}

	$navs['newest'] = array( 'link' => add_query_arg( [ 'ap_sort' => 'newest' ], $base ), 'title' => __( 'Newest', 'anspress-question-answer' ) );
	$navs['oldest'] = array( 'link' => add_query_arg( [ 'ap_sort' => 'oldest' ], $base ), 'title' => __( 'Oldest', 'anspress-question-answer' ) );

	echo '<ul class="ap-answers-tab ap-ul-inline clearfix">';
	foreach ( (array) $navs as $k => $nav ) {
		echo '<li'.($sort == $k ? ' class="active"' : '').'><a href="'.esc_attr( $nav['link'] ).'">'.esc_attr( $nav['title'] ).'</a></li>';
	}
	echo '</ul>';
}

/**
 * Answer meta to display.
 * @param false|integer $answer_id Answer id.
 * @return string
 * @since 2.0.1
 */
function ap_display_answer_metas($answer_id = false) {
	if ( false === $answer_id ) {
		$answer_id = get_the_ID();
	}

	$metas = array();
	if ( ap_is_selected( $answer_id ) ) {
		$metas['best_answer'] = '<span class="ap-best-answer-label">'.__( 'Best answer', 'anspress-question-answer' ).'</span>';
	}

	$metas['history'] = ap_last_active_time( $answer_id );

	/*
     * FILTER: ap_display_answer_meta
     * Used to filter answer display meta
     * @since 2.0.1
	 */
	$metas = apply_filters( 'ap_display_answer_metas', $metas, $answer_id );

	$output = '';
	if ( ! empty( $metas ) && is_array( $metas ) ) {
		foreach ( $metas as $meta => $display ) {
			$output .= "<span class='ap-display-meta-item {$meta}'>{$display}</span>";
		}
	}

	return $output;
}

/**
 * Echo ask button.
 * @since 2.1
 */
function ap_ask_btn() {
	echo ap_get_ask_btn();
}

/**
 * Return the ask button. *
 * @return string Ask button HTML
 * @since 2.1
 */
function ap_get_ask_btn() {
	$link = ap_get_link_to( 'ask' );

	/**
	 * Filter ask button link.
	 * @param string $link
	 */
	$link = apply_filters( 'ap_ask_btn_link', $link );

	return '<a class="ap-btn-ask" href="'.$link.'">'.__( 'Ask question', 'anspress-question-answer' ).'</a>';
}

/**
 * Include template php files.
 * @param string $file File name without extension.
 * @since 2.1
 */
function ap_get_template_part($file) {
	include ap_get_theme_location( $file.'.php' );
}

/**
 * Output AnsPress breadcrumbs
 */
function ap_breadcrumbs() {

	$navs = ap_get_breadcrumbs();

	echo '<ul class="ap-breadcrumbs clearfix">';
	echo '<li class="ap-breadcrumbs-home"><a href="'.esc_url( home_url( '/' ) ).'" class="apicon-home"></a></li>';
	echo '<li><i class="apicon-chevron-right"></i></li>';

	$i = 1;
	$total_nav = count( $navs );

	foreach ( $navs as $k => $nav ) {
		if ( ! empty( $nav ) ) {
			echo '<li>';
			echo '<a href="'.esc_url( $nav['link'] ).'">'. esc_attr( $nav['title'] ).'</a>';
			echo '</li>';

			if ( $total_nav != $i ) {
				echo '<li><i class="apicon-chevron-right"></i></li>';
			}
		}
		++$i;
	}

	echo '</ul>';
}

/**
 * Get breadcrumbs array
 * @return array
 */
function ap_get_breadcrumbs() {
	$current_page = ap_current_page();
	$title = ap_page_title();
	$a = array();

	$a['base'] = array( 'title' => ap_opt( 'base_page_title' ), 'link' => ap_base_page_link(), 'order' => 0 );

	if ( is_question() ) {
		$a['page'] = array( 'title' => substr( $title, 0, 30 ) . ( strlen( $title ) > 30 ? __( '..', 'anspress-question-answer' ) : ''), 'link' => get_permalink( get_question_id() ), 'order' => 10 );
	} elseif ( 'base' != $current_page && '' != $current_page ) {
		$a['page'] = array( 'title' => substr( $title, 0, 30 ) . ( strlen( $title ) > 30 ? __( '..', 'anspress-question-answer' ) : ''), 'link' => ap_get_link_to( $current_page ), 'order' => 10 );
	}

	$a = apply_filters( 'ap_breadcrumbs', $a );

	return ap_sort_array_by_order( $a );
}

/**
 * Return current AnsPress page
 * @return string
 */
function ap_current_page() {
	$query_var = get_query_var( 'ap_page' );

	if ( '' == $query_var ) {
		$query_var = 'base';
	}

	return apply_filters( 'ap_current_page', esc_attr( $query_var ) );
}

/**
 * AnsPress CSS and JS.
 *
 * @return array
 */
function ap_assets( ) {
	$dir = ap_env_dev() ? 'js' : 'min';
	$min = ap_env_dev() ? '' : '.min';


	$assets = array(
		'js' => array(
			'common' => [ 'dep' => [ 'jquery', 'jquery-form', 'underscore', 'backbone' ], 'footer' => true ],
			'theme' => [ 'dep' => [ 'jquery', 'anspress-common' ], 'footer' => true, 'theme' => true ],
		),
		'css' => array(
			'main' => array( 'theme' => true ),
			'fonts' => array( 'theme' => true ),
		),
	);

	if ( is_ask() || ap_current_page() === 'edit' ) {
		$assets['js']['ask'] = [ 'dep' => [ 'anspress-common' ] , 'footer' => true ];
		$assets['js']['upload'] = [ 'dep' => [ 'plupload', 'anspress-common' ] , 'footer' => true ];
	}

	if ( is_question() || ap_current_page() === 'edit' ) {
		$assets['js']['question'] = [ 'dep' => [ 'anspress-common' ], 'footer' => true ];
	}

	if ( is_rtl() ) {
		$assets['css']['rtl'] = array( 'theme' => true );
	}

	$assets['js'] = apply_filters( 'ap_assets_js', $assets['js'] );
	$assets['css'] = apply_filters( 'ap_assets_css', $assets['css'] );

	return $assets;
}

/**
 * Enqueue AnsPress assets.
 * @since 2.4.6
 */
function ap_enqueue_scripts() {
	$assets = ap_assets();

	foreach ( (array) $assets['js'] as $k => $js ) {
		$path = ! empty( $js['theme'] ) ? ap_get_theme_url( 'js', false, false ) : ANSPRESS_URL . 'assets/js';

		if ( ap_env_dev() ) {
			$src = $path . '/' . $k . '.js';
		} else {
			$src = $path . '/min/' . $k . '.min.js';
		}

		$dep = isset( $js['dep'] ) ? $js['dep'] : array();
		$footer = isset( $js['footer'] ) ? $js['footer'] : false;
		wp_enqueue_script( 'anspress-' . $k, $src, $dep, AP_VERSION, $footer );
	}

	foreach ( (array) $assets['css'] as $k => $css ) {
		$path = ! empty( $css['theme'] ) ? ap_get_theme_url( 'css', false, false ) : ANSPRESS_URL . 'assets/css';

		if ( ap_env_dev() ) {
			$src = $path . '/' . $k . '.css';
		} else {
			$src = $path . '/min/' . $k . '.min.css';
		}

		$dep = isset( $css['dep'] ) ? $css['dep'] : array();
		wp_enqueue_style( 'anspress-' . $k, $src, $dep, AP_VERSION );
	}
}

function ap_get_list_filters( $current_url = '' ) {
	if ( is_home() || is_front_page() ) {
		$current_url = home_url( '/' );
	}

	$param = array();
	$search_q = get_query_var( 'ap_s' );

	if ( ! empty( $search_q ) ) {
		$param['ap_s'] = $search_q;
	}

	$link = add_query_arg( $param, $current_url );

	$filters = array(
		'sort' => array(
			'title' => __( 'Sort', 'anspress-question-answer' ),
			'items' => ap_get_question_sorting(),
		),
	);

	/*
     * Filter question sorting.
     * @param array Question sortings.
     * @since 2.3
	 */
	return apply_filters( 'ap_list_filters', $filters );
}

function ap_list_filters( $current_url = '' ) {
	$filters = ap_get_list_filters( $current_url );
	$ap_filter = isset( $_GET['ap_filter'] ) ? wp_unslash( $_GET['ap_filter'] ) : '';
	foreach ( (array) $filters as $key => $filter ) {
		echo '<div class="ap-dropdown filter-'.esc_attr( $key ).'">';
		echo '<a id="ap-sort-anchor" class="ap-dropdown-toggle" href="#" data-query="list_filter::'. wp_create_nonce( 'ap_ajax_nonce' ) .'::'. esc_attr( $key ) .'" data-action="load_filter">'. esc_attr( $filter[ 'title' ] ) .'</a>';
		echo '</div>';
	}
	// Send current GET, so that it can be used by JS templates.
	if ( isset( $_GET['ap_filter'] ) ) {
		echo '<script type="text/html" id="current_filter">'. http_build_query( $ap_filter ) .'</script>';
	}
}
/**
 * Print select anser HTML button.
 *
 * @param mixed $_post Post.
 * @return string
 */
function ap_select_answer_btn_html( $_post = null ) {

	if ( ! ap_user_can_select_answer( $_post ) ) {
		return;
	}

	$_post = ap_get_post( $_post );
	$nonce = wp_create_nonce( 'select-answer-' . $_post->ID );

	$q = esc_js( wp_json_encode( [ 'answer_id' => $_post->ID, 'nonce' => $nonce ] ) );
	$active = false;

	$title = __( 'Select this answer as best', 'anspress-question-answer' );
	$label = __( 'Select', 'anspress-question-answer' );

	$have_best = ap_have_answer_selected( $_post->post_parent );
	$selected = ap_is_selected( $_post );
	$hide = false;

	if ( $have_best && $selected ) {
		$title = __( 'Unselect this answer', 'anspress-question-answer' );
		$label = __( 'Unselect', 'anspress-question-answer' );
		$active = true;
	}

	if ( $have_best && ! $selected ) {
		$hide = true;
	}

	return '<a href="#" class="ap-btn-select ap-btn ' . ( $active ? ' active' : '' ) . ( $hide ? ' hide' : '' ) . '" ap="select_answer" ap-query="' . $q . '" title="' . $title . '">' . $label . '</a>';
}

/**
 * Output chnage post status button.
 *
 * @param 	mixed $_post Post.
 * @return 	null|string
 * @since 	4.0.0
 */
function ap_post_status_btn_args( $_post = null ) {
	$_post = ap_get_post( $_post );

	if ( ap_user_can_change_status( $_post->ID ) ) {
		global $wp_post_statuses;
		$allowed_status = [ 'publish', 'trash', 'private_post', 'moderate' ];
		$status_labels = [];

		foreach ( (array) $allowed_status as $s ) {
			if ( isset( $wp_post_statuses[ $s ] ) ) {
				$status_labels[ $s ] = esc_attr( $wp_post_statuses[ $s ]->label );
			}
		}

		$args = array(
			'query' => [ 'nonce' => $nonce, 'post_id' => $_post->ID, 'ap_ajax_action' => 'select_best_answer' ],
			'label' => __( 'Status', 'anspress-question-answer' ),
			'title' => __( 'Change status of post', 'anspress-question-answer' ),
		);

		foreach ( (array) $status_labels as $slug => $label ) {
			$can = true;

			if ( 'moderate' === $k  && ! ap_user_can_change_status_to_moderate() ) {
				$can = false;
			}

			if ( $can ) {
				$args[ 'sub' ] = array(
					'active' => ( $slug === $_post->post_status ),
					'query' => [ 'ap_ajax_action' => 'change_post_status', 'nonce' => wp_create_nonce( 'change-status-' . $slug . '-' . $_post->ID ), 'post_id' => $_post->ID ],
					'label' => esc_attr( $label ),
				);
			}
		}

		return $args;
	}
}