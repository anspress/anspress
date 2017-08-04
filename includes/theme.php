<?php
/**
 * AnsPress theme and template handling.
 *
 * @author       Rahul Aryan <support@anspress.io>
 * @license      GPL-3.0+
 * @link         https://anspress.io
 * @copyright    2014 Rahul Aryan
 * @package      AnsPress
 * @subpackage   Theme Functions
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Return current page title.
 *
 * @return string current title
 */
function ap_page_title() {

	$pages = anspress()->pages;

	$current_page = ap_current_page();

	if ( is_question() ) {
		if ( ! ap_user_can_read_question( get_question_id() ) ) {
			$new_title = __( 'No permission', 'anspress-question-answer' );
		} else {
			$new_title = ap_question_title_with_solved_prefix();
		}
	} elseif ( is_ap_search() ) {
		$new_title = sprintf( ap_opt( 'search_page_title' ), sanitize_text_field( get_query_var( 'ap_s' ) ) );
	} elseif ( is_ask() ) {
		$new_title = ap_opt( 'ask_page_title' );
	} elseif ( '' === $current_page && ! is_question() && '' === get_query_var( 'question_name' ) ) {
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
 * Check if current page is search page
 *
 * @return boolean
 */
function is_ap_search() {
	if ( is_anspress() && get_query_var( 'ap_s' ) ) {
		return true;
	}

	return false;
}

/**
 * Return current AnsPress page
 *
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
		} elseif ( is_question_category() ) {
			$template = 'category';
		} elseif ( is_ap_search() ) {
			$template = 'search';
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
 *
 * @return string template file name.
 */
function ap_get_current_page_template() {
	if ( is_anspress() ) {
		$template = ap_current_page_is();

		return apply_filters( 'ap_current_page_template', $template . '.php' );
	}

	return 'content-none.php';
}

/**
 * Get post status.
 *
 * @param boolean|integer $post_id question or answer ID.
 * @return string
 * @since 2.0.0
 */
function ap_post_status( $post_id = false ) {
	if ( false === $post_id ) {
		$post_id = get_the_ID();
	}

	return get_post_status( $post_id );
}

/**
 * Check if current post is private.
 *
 * @param boolean|integer $post_id question or answer ID.
 * @return boolean
 */
function is_private_post( $post_id = false ) {
	if ( ap_post_status( $post_id ) === 'private_post' ) {
		return true;
	}

	return false;
}

/**
 * Check if post is waiting moderation.
 *
 * @param boolean|integer $post_id question or answer ID.
 * @return bool
 */
function is_post_waiting_moderation( $post_id = false ) {
	if ( get_post_status( $post_id ) === 'moderate' ) {
		return true;
	}

	return false;
}

/**
 * Check if question is closed.
 *
 * @param boolean|integer $post_id question or answer ID.
 * @return boolean
 * @since 2.0.0
 */
function is_post_closed( $post_id = null ) {
	if ( ap_get_post_field( 'closed', $post_id ) ) {
		return true;
	}

	return false;
}

/**
 * Check if question have a parent post.
 *
 * @param boolean|integer $post_id question or answer ID.
 * @return boolean
 * @since   2.0.0
 */
function ap_have_parent_post( $post_id = false ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	// Get post.
	$post_o = ap_get_post( $post_id );

	if ( $post_o->post_parent > 0 && 'question' === $post_o->post_type ) {
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
 * @param string $page_num_link  Base link.
 * @return string
 */
function ap_pagination( $current = false, $total = false, $format = '?paged=%#%', $page_num_link = false ) {
	global $ap_max_num_pages, $ap_current, $questions;

	if ( is_front_page() ) {
		$format = '';
	}

	$big = 999999999; // Need an unlikely integer.

	if ( false === $current ) {
	    $paged = ap_sanitize_unslash( 'ap_paged', 'r', 1 );
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

	if ( '1' == $total ) { // WPCS: loose comparison ok.
		return;
	}

	echo '<div class="ap-pagination clearfix">';
	echo paginate_links( array( // WPCS: xss okay.
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
 * Register anspress pages.
 *
 * @param string   $page_slug    slug for links.
 * @param string   $page_title   Page title.
 * @param callable $func         Hook to run when shortcode is found.
 * @param bool     $show_in_menu User can add this pages to their WordPress menu from appearance->menu->AnsPress.
 * @param bool     $private Only show to currently logged in user?
 *
 * @since 2.0.1
 */
function ap_register_page( $page_slug, $page_title, $func, $show_in_menu = true, $private = false ) {
	anspress()->pages[ $page_slug ] = array(
		'title' 		     => $page_title,
		'func' 			     => $func,
		'show_in_menu' 	 => $show_in_menu,
		'private' 	     => $private,
	);
}

/**
 * Output current AnsPress page.
 *
 * @since 2.0.0
 */
function ap_page() {
	$pages = anspress()->pages;
	$current_page = ap_current_page();

	if ( is_question() ) {
		$current_page = 'question';
	} elseif ( '' === $current_page && ! is_question() ) {
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

	// Featured link.
	if ( 'question' === $_post->post_type ) {
		$actions[] = ap_featured_post_args( $_post->ID );
	}

	// Question close action.
	if ( ap_user_can_close_question() && 'question' === $_post->post_type ) {
		$nonce = wp_create_nonce( 'close_' . $_post->ID );
		$close_label = $_post->closed ?  __( 'Open', 'anspress-question-answer' ) : __( 'Close', 'anspress-question-answer' );
		$close_title = $_post->closed ?  __( 'Open this question for new answers', 'anspress-question-answer' ) : __( 'Close this question for new answer.', 'anspress-question-answer' );

		$actions[] = array(
			'cb' => 'close',
			'icon'  => 'apicon-check',
			'query' => [ 'nonce' => $nonce, 'post_id' => $_post->ID ],
			'label' => $close_label,
			'title' => $close_title,
		);
	}

	// Edit link.
	if ( ap_user_can_edit_post( $_post->ID ) ) {
		$actions[] = array(
			'cb' => 'edit',
			'label' => __( 'Edit', 'anspress-question-answer' ),
			'href'  => ap_post_edit_link( $_post ),
		);
	}

	// Flag link.
	$actions[] = ap_flag_btn_args( $_post );

	$status_args = ap_post_status_btn_args( $_post );

	if ( ! empty( $status_args ) ) {
		$actions[] = array(
			'label'  => __( 'Status', 'anspress-question-answer' ),
			'header' => true,
		);

		$actions = array_merge( $actions, $status_args );
		$actions[] = array( 'header' => true );
	}

	if ( ap_user_can_delete_post( $_post->ID ) ) {

		if ( 'trash' === $_post->post_status ) {
			$label = __( 'Undelete', 'anspress-question-answer' );
			$title = __( 'Restore this post', 'anspress-question-answer' );
		} else {
			$label = __( 'Delete', 'anspress-question-answer' );
			$title = __( 'Delete this post (can be restored again)', 'anspress-question-answer' );
		}

		$actions[] = array(
			'cb'    => 'toggle_delete_post',
			'query' => [ 'post_id' => $_post->ID, '__nonce' => wp_create_nonce( 'trash_post_' . $_post->ID ) ],
			'label' => $label,
			'title' => $title,
		);
	}

	// Permanent delete link.
	if ( ap_user_can_permanent_delete( $_post->ID ) ) {
		$actions[] = array(
			'cb'    => 'delete_permanently',
			'query' => [ 'post_id' => $_post->ID, '__nonce' => wp_create_nonce( 'delete_post_' . $_post->ID ) ],
			'label' => __( 'Delete Permanently', 'anspress-question-answer' ),
			'title' => __( 'Delete post permanently (cannot be restored again)', 'anspress-question-answer' ),
		);
	}

	// Convert question to a post.
	if ( ( is_super_admin( ) || current_user_can( 'manage_options' ) ) && 'question' === $_post->post_type ) {

		$actions[] = array(
			'cb'    => 'convert_to_post',
			'query' => [ 'post_id' => $_post->ID, '__nonce' => wp_create_nonce( 'convert-post-' . $_post->ID ) ],
			'label' => __( 'Convert to post', 'anspress-question-answer' ),
			'title' => __( 'Convert this question to blog post', 'anspress-question-answer' ),
		);
	}

	/**
	 * For filtering post actions buttons
	 *
	 * @var     string
	 * @since   2.0
	 */
	$actions = apply_filters( 'ap_post_actions', array_filter( $actions ) );
	return array_values( $actions );
}

/**
 * Post actions buttons.
 *
 * @since 	2.0
 */
function ap_post_actions_buttons() {

	if ( ! is_user_logged_in() ) {
		return;
	}

	$args = wp_json_encode( [
		'post_id' => get_the_ID(),
		'nonce'   => wp_create_nonce( 'post-actions-' . get_the_ID() ),
	]);

	echo '<postActions class="ap-dropdown"><button class="ap-btn apicon-dots ap-actions-handle ap-dropdown-toggle" ap="actiontoggle" ap-query="' . esc_js( $args ) . '"></button><ul class="ap-actions ap-dropdown-menu"></ul></postActions>';
}

/**
 * Return all order by options for questions list.
 *
 * @param  string $current_url Current page URL.
 * @return array
 * @since  3.0.0 Moved from `ap_question_sorting()`.
 */
function ap_get_questions_orderby( $current_url = '' ) {
	$param = array();
	$search_q = get_query_var( 'ap_s' );

	if ( ! empty( $search_q ) ) {
		$param['ap_s'] = $search_q;
	}

	$navs = array(
		[ 'key' => 'order_by', 'value' => 'active', 'label' => __( 'Active', 'anspress-question-answer' ) ],
		[ 'key' => 'order_by', 'value' => 'newest', 'label' => __( 'New', 'anspress-question-answer' ) ],
	);

	if ( ! ap_opt( 'disable_voting_on_question' ) ) {
		$navs[] = [ 'key' => 'order_by', 'value' => 'voted', 'label' => __( 'Votes', 'anspress-question-answer' ) ];
	}

	$navs[] = [ 'key' => 'order_by', 'value' => 'answers','label' => __( 'Answers', 'anspress-question-answer' ) ];
	$navs[] = [ 'key' => 'order_by', 'value' => 'views', 'label' => __( 'Views', 'anspress-question-answer' ) ];
	$navs[] = [ 'key' => 'order_by', 'value' => 'unanswered', 'label' => __( 'Unanswered', 'anspress-question-answer' ) ];
	$navs[] = [ 'key' => 'order_by', 'value' => 'unsolved', 'label' => __( 'Unsolved', 'anspress-question-answer' ) ];

	foreach ( (array) $navs as $k => $args ) {
		$active = ap_get_current_list_filters( 'order_by' );

		if ( $active === $args['value'] ) {
			$navs[ $k ]['active'] = true;
		}
	}

	/**
	 * Filter question sorting.
	 *
	 * @param array $navs Questions orderby list.
	 * @since 2.3
	 */
	return apply_filters( 'ap_questions_order_by', $navs );
}


/**
 * Output answers tab.
 *
 * @param string|boolean $base Current page url.
 * @since 2.0.1
 */
function ap_answers_tab( $base = false ) {
	$sort = ap_sanitize_unslash( 'order_by', 'r',  ap_opt( 'answers_sort' ) );

	if ( ! $base ) {
		$base = get_permalink();
	}

	$navs = array(
		'active' => array( 'link' => add_query_arg( [ 'order_by' => 'active' ], $base ), 'title' => __( 'Active', 'anspress-question-answer' ) ),
	);

	if ( ! ap_opt( 'disable_voting_on_answer' ) ) {
		$navs['voted'] = array( 'link' => add_query_arg( [ 'order_by' => 'voted' ], $base ), 'title' => __( 'Voted', 'anspress-question-answer' ) );
	}

	$navs['newest'] = array( 'link' => add_query_arg( [ 'order_by' => 'newest' ], $base ), 'title' => __( 'Newest', 'anspress-question-answer' ) );
	$navs['oldest'] = array( 'link' => add_query_arg( [ 'order_by' => 'oldest' ], $base ), 'title' => __( 'Oldest', 'anspress-question-answer' ) );

	echo '<ul id="answers-order" class="ap-answers-tab ap-ul-inline clearfix">';
	foreach ( (array) $navs as $k => $nav ) {
		echo '<li' . ( $sort === $k ? ' class="active"' : '') . '><a href="' . esc_url( $nav['link'] .'#answers-order' ) . '">' . esc_attr( $nav['title'] ) . '</a></li>';
	}
	echo '</ul>';
}

/**
 * Answer meta to display.
 *
 * @param false|integer $answer_id Answer id.
 * @return string
 * @since 2.0.1
 */
function ap_display_answer_metas( $answer_id = false ) {

	if ( false === $answer_id ) {
		$answer_id = get_the_ID();
	}

	$metas = array();
	if ( ap_is_selected( $answer_id ) ) {
		$metas['best_answer'] = '<span class="ap-best-answer-label">' . __( 'Best answer', 'anspress-question-answer' ) . '</span>';
	}

	$metas['history'] = ap_last_active_time( $answer_id );

	/*
	 * Used to filter answer display meta.
	 *
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
 *
 * @since 2.1
 */
function ap_ask_btn() {
	echo ap_get_ask_btn(); // WPCS: xss okay.
}

/**
 * Return the ask button.
 *
 * @return string Ask button HTML
 * @since 2.1
 */
function ap_get_ask_btn() {
	$link = ap_get_link_to( 'ask' );

	/**
	 * Filter ask button link.
	 *
	 * @param string $link
	 */
	$link = apply_filters( 'ap_ask_btn_link', $link );

	return '<a class="ap-btn-ask" href="' . $link . '">' . __( 'Ask question', 'anspress-question-answer' ) . '</a>';
}

/**
 * Include template php files.
 *
 * @param string $file File name without extension.
 * @since 2.1
 */
function ap_get_template_part( $file, $args = false ) {
	if ( false !== $args ) {
		extract( $args );
	}

	include ap_get_theme_location( $file . '.php' );
}

/**
 * Return current AnsPress page
 *
 * @return string
 */
function ap_current_page() {
	$query_var = get_query_var( 'ap_page' );

	if ( '' === $query_var ) {
		$query_var = 'base';
	}

	/**
	 * Filter AnsPress current page.
	 *
	 * @param    string $query_var Current page slug.
	 */
	return apply_filters( 'ap_current_page', esc_attr( $query_var ) );
}

/**
 * AnsPress CSS and JS.
 *
 * @return array
 */
function ap_assets() {
	$assets = array(
		'js' => array(
			'main'          => [ 'dep' => [ 'jquery', 'jquery-form', 'underscore', 'backbone' ], 'footer' => true ],
			'upload'        => [ 'dep' => [ 'plupload', 'anspress-main' ], 'footer' => true ],
			'notifications' => [ 'dep' => [ 'anspress-main' ], 'footer' => true ],
			'theme' 				=> [ 'theme' => true, 'dep' => [ 'anspress-main' ], 'footer' => true ],
		),
		'css' => array(
			'main'  => array( 'theme' => true, 'dep' => [ 'anspress-fonts' ] ),
			'fonts' => array( 'theme' => true ),
			'rtl'   => array( 'theme' => true ),
		),
	);

	if ( is_ask() || ap_current_page() === 'edit' ) {
		$assets['js']['main']['active'] = true;

		if ( ap_user_can_upload( ) ) {
			$assets['js']['upload']['active'] = true;
		}
	}

	if ( is_question() || ap_current_page() === 'edit' ) {
		$assets['js']['main']['active'] = true;
	}

	if ( is_anspress() && ( ap_current_page() === 'base' || ap_current_page() === 'search' ) ) {
		$assets['js']['main']['active'] = true;
	}

	if ( is_rtl() ) {
		$assets['css']['rtl']['active'] = true;
	}

	$assets['js'] = apply_filters( 'ap_assets_js', $assets['js'] );
	$assets['css'] = apply_filters( 'ap_assets_css', $assets['css'] );

	return $assets;
}

/**
 * Enqueue AnsPress assets.
 *
 * @since 2.4.6
 */
function ap_enqueue_scripts() {
	$assets = ap_assets();

	foreach ( (array) $assets['js'] as $k => $js ) {
		$src = '/min/' . $k . '.min.js';

		$src = ! empty( $js['theme'] ) ? ap_get_theme_url( 'js' . $src, false, false ) : ANSPRESS_URL . 'assets/js' . $src;

		$dep = isset( $js['dep'] ) ? $js['dep'] : array();
		$footer = isset( $js['footer'] ) ? $js['footer'] : false;
		wp_register_script( 'anspress-' . $k, $src, $dep, AP_VERSION, $footer );

		if ( isset( $js['active'] ) && $js['active'] ) {
			wp_enqueue_script( 'anspress-' . $k );
		}
	}

	foreach ( (array) $assets['css'] as $k => $css ) {

		$src = ! empty( $css['theme'] ) ? ap_get_theme_url( 'css/' . $k . '.css', false, false ) : ANSPRESS_URL . 'assets/css' . $k . '.css';

		$dep = isset( $css['dep'] ) ? $css['dep'] : array();
		wp_register_style( 'anspress-' . $k, $src, $dep, AP_VERSION );

		if ( isset( $css['active'] ) && $css['active'] ) {
			wp_enqueue_style( 'anspress-' . $k );
		}
	}
}

/**
 * Get all list filters.
 *
 * @param string $current_url Current URL.
 */
function ap_get_list_filters( ) {
	$param = array();
	$search_q = get_query_var( 'ap_s' );

	if ( ! empty( $search_q ) ) {
		$param['ap_s'] = $search_q;
	}

	$filters = array(
		'order_by' => array(
			'title'    => __( 'Order By', 'anspress-question-answer' ),
			'items'    => [],
			'multiple' => false,
		),
	);

	/*
     * Filter question sorting.
     * @param array Question sortings.
     * @since 2.3
	 */
	return apply_filters( 'ap_list_filters', $filters );
}

/**
 * Output list filters form.
 *
 * @param string $current_url Current Url.
 */
function ap_list_filters( $current_url = '' ) {
	$filters = ap_get_list_filters();

	echo '<form id="ap-filters" class="ap-filters clearfix" method="GET">';

	foreach ( (array) $filters as $key => $filter ) {
		$active = '';

		$current_order_by = ap_get_current_list_filters( 'order_by' );

		if ( ! empty( $current_order_by ) ) {
			$active_arr = ap_search_array( ap_get_questions_orderby(), 'value', $current_order_by );

			if ( ! empty( $active_arr ) ) {
				$active = ': <span class="ap-filter-active">' . $active_arr[0]['label'] . '</span>';
			}
		}

		$active = apply_filters( 'ap_list_filter_active_' . $key, $active, $filter );

		$args = wp_json_encode( [ '__nonce' => wp_create_nonce( 'filter_' . $key ), 'filter' => $key ] );
		echo '<div class="ap-dropdown ap-filter filter-' . esc_attr( $key ) . '">';
		echo '<a class="ap-dropdown-toggle ap-filter-toggle" href="#" ap-filter ap-query="' . esc_js( $args ) . '">' . esc_attr( $filter['title'] ) . $active . '</a>'; // xss okay.
		echo '</div>';
	}

	echo '<button id="ap-filter-reset" type="submit" name="reset-filter" title="' . esc_attr__( 'Reset sorting and filter', 'anspress-question-answer' ) . '"><i class="apicon-x"></i><span>' . esc_attr__( 'Clear Filter', 'anspress-question-answer' ) . '</span></button>';

	foreach ( (array) ap_get_current_list_filters() as $key => $value ) {
		if ( ! is_array( $value ) ) {
			echo '<input type="hidden" value="' . esc_attr( $value ). '" name="' . esc_attr( $key ) . '" />';
		} else {
			foreach ( (array) $value as $v ) {
				echo '<input type="hidden" value="' . esc_attr( $v ). '" name="' . esc_attr( $key ) . '[]" />';
			}
		}
	}

	echo '</form>';
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
	$args = [];

	if ( ap_user_can_change_status( $_post->ID ) ) {
		global $wp_post_statuses;
		$allowed_status = [ 'publish', 'private_post', 'moderate' ];
		$status_labels = [];

		foreach ( (array) $allowed_status as $s ) {
			if ( isset( $wp_post_statuses[ $s ] ) ) {
				$status_labels[ $s ] = esc_attr( $wp_post_statuses[ $s ]->label );
			}
		}

		foreach ( (array) $status_labels as $slug => $label ) {
			$can = true;

			if ( 'moderate' === $slug  && ! ap_user_can_change_status_to_moderate() ) {
				$can = false;
			}

			if ( $can ) {
				$args[] = array(
					'cb'  => 'status',
					'active'  => ( $slug === $_post->post_status ),
					'query'   => [ 'status' => $slug, '__nonce' => wp_create_nonce( 'change-status-' . $slug . '-' . $_post->ID ), 'post_id' => $_post->ID ],
					'label'   => esc_attr( $label ),
				);
			}
		}

		return $args;
	}
}


/**
 * Return set featured question action args.
 *
 * @param  boolean|integer $post_id Post ID.
 * @return array
 */
function ap_featured_post_args( $post_id = false ) {
	if ( ! is_user_logged_in() || ! ap_user_can_toggle_featured() ) {
		return [];
	}

	if ( false === $post_id ) {
		$post_id = get_question_id();
	}

	$is_featured = ap_is_featured_question( $post_id );

	if ( $is_featured ) {
		$title = __( 'Unmark this question as featured', 'anspress-question-answer' );
		$label = __( 'Unfeature', 'anspress-question-answer' );
	} else {
		$title = __( 'Mark this question as featured', 'anspress-question-answer' );
		$label = __( 'Feature', 'anspress-question-answer' );
	}

	return array(
		'cb'     => 'toggle_featured',
		'active' => $is_featured,
		'query'  => [ '__nonce' => wp_create_nonce( 'set_featured_' . $post_id ), 'post_id' => $post_id ],
		'title'  => esc_attr( $title ),
		'label'  => esc_attr( $label ),
	);
}

/**
 * Output question subscribe button.
 *
 * @param object|integer|false $_post Post object or ID.
 * @param boolean              $echo Echo or return.
 * @return string|null
 * @since 4.0.0
 */
function ap_subscribe_btn( $_post = false, $echo = true ) {
	$_post = ap_get_post( $_post );

	$args = wp_json_encode( [ '__nonce' => wp_create_nonce( 'subscribe_' . $_post->ID ), 'id' => $_post->ID ] );
	$subscribers = ap_get_post_field( 'subscribers', $_post );
	$subscribed = ap_is_user_subscriber( 'question', $_post->ID );
	$label = $subscribed ? __( 'Unsubscribe', 'anspress-question-answer' ) : __( 'Subscribe', 'anspress-question-answer' );

	$html = '<a href="#" class="ap-btn ap-btn-subscribe ap-btn-small ' . ( $subscribed ? 'active' : '' ) .'" ap-subscribe ap-query="' . esc_js( $args ) . '">' . $label . '</a>' . '<span class="ap-btn ap-subscribers-count ap-btn-small">' . $subscribers . '</span>';

	if ( ! $echo ) {
		return $html;
	}

	echo $html; // WPCS: xss okay.
}

function ap_menu_obejct() {
	$menu_items = [];

	foreach ( (array) anspress()->pages as $k => $args ) {
		if ( $args['show_in_menu'] ) {
			$menu_items[] = (object) array(
				'ID'               => 1,
				'db_id'            => 0,
				'menu_item_parent' => 0,
				'object_id'        => 1,
				'post_parent'      => 0,
				'type'             => 'anspress-links',
				'object'           => $k,
				'type_label'       => __( 'AnsPress links', 'anspress-question-answer' ),
				'title'            => $args['title'],
				'url'              => home_url( '/' ),
				'target'           => '',
				'attr_title'       => '',
				'description'      => '',
				'classes'          => [ 'anspress-menu-' . $k ],
				'xfn'              => '',
			);
		}
	}

	return $menu_items;
}

/**
 * Return AnsPress page slug.
 *
 * @param string $slug Default page slug.
 * @return string
 */
function ap_get_page_slug( $slug ) {
	$option = ap_opt( $slug . '_page_slug' );

	if ( ! empty( $option ) ) {
		$slug = $option;
	}

	return apply_filters( 'ap_page_slug_' . $slug, $slug );
}
