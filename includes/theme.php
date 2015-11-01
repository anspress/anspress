<?php
/**
 * AnsPress theme and template handling.
 *
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 *
 * @link      http://rahularyan.com
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
 *
 * @return string current title
 */
function ap_page_title() {

	$pages = anspress()->pages;

	$current_page = ap_current_page();

	if ( is_question() ) {
		$new_title = ap_question_title_with_solved_prefix();
	} elseif ( is_ap_edit() ) {
		$new_title = __( 'Edit post', 'ap' );
	} elseif ( is_ap_search() ) {
		$new_title = sprintf( ap_opt( 'search_page_title' ), sanitize_text_field( get_query_var( 'ap_s' ) ) );
	} elseif ( is_ask() ) {
		$new_title = ap_opt( 'ask_page_title' );
	} elseif ( is_ap_users() ) {
		$new_title = ap_opt( 'users_page_title' );
	} elseif ( '' == $current_page && ! is_question() && '' == get_query_var( 'question_name' ) ) {
		$new_title = ap_opt( 'base_page_title' );
	} elseif ( get_query_var( 'parent' ) != '' ) {
		$new_title = sprintf( __( 'Discussion on "%s"', 'ap' ), get_the_title( get_query_var( 'parent' ) ) );
	} elseif ( isset( $pages[ $current_page ]['title'] ) ) {
		$new_title = $pages[ $current_page ]['title'];
	} else {
		$new_title = __( 'Error 404', 'ap' );
	}

	$new_title = apply_filters( 'ap_page_title', $new_title );

	return $new_title;
}

/**
 * Get current answer or question id is being edited
 * @return integer|false
 */
function ap_edit_post_id() {
	if ( is_anspress() && get_query_var( 'edit_post_id' ) ) {
		return get_query_var( 'edit_post_id' );
	}

	return false;
}

/**
 * Check if current page is edit page
 * @return boolean
 */
function is_ap_edit() {
	if ( is_anspress() && get_query_var( 'edit_post_id' ) ) {
		return true;
	}

	return false;
}

/**
 * Check if current page is revision page
 * @return boolean
 */
function is_ap_revision() {
	if ( is_anspress() && get_query_var( 'ap_page' ) == 'revision' ) {
		return true;
	}

	return false;
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
 * Check if current user page is followers page
 * @return boolean
 */
function is_ap_followers() {
	if ( is_ap_user() && get_query_var( 'user_page' ) == 'followers' ) {
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
		} elseif ( is_question_tags() ) {
			$template = 'tags';
		} elseif ( is_question_tag() ) {
			$template = 'tag';
		} elseif ( is_question_cat() ) {
			$template = 'category';
		} elseif ( is_question_edit() ) {
			$template = 'edit-question';
		} elseif ( is_answer_edit() ) {
			$template = 'edit-answer';
		} elseif ( is_ap_users() ) {
			$template = 'users';
		} elseif ( is_ap_user() ) {
			$template = 'user';
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
 * @param boolean|integer $post_id question or answer ID.
 * @return boolean
 * @since 2.0.0-alpha2
 */
function is_post_closed($post_id = false) {
	if ( get_post_status( $post_id ) == 'closed' ) {
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
	$post_o = get_post( $post_id );

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
function ap_pagination($current = false, $total = false, $format = '?paged=%#%') {
	global $ap_max_num_pages, $ap_current;

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
	} elseif ( false === $total ) {
		global $questions;
		$total = $questions->max_num_pages;
	}
	$page_num_link = str_replace( array( '&amp;', '&#038;' ), '&', get_pagenum_link( $big ) );

	if ( is_front_page() ) {
		$base = add_query_arg( array( 'ap_paged' => '%#%' ), home_url( '/' ) );
	} else {
		$base = str_replace( $big, '%#%', $page_num_link );
	}

	if ( '1' == $total ) {
		return;
	}

	echo '<div class="ap-pagination clearfix">';
	echo paginate_links(array(
		'base' => $base,
		'format' => $format,
		'current' => $current,
		'total' => $total,
		'end_size' => 3,
		'mid_size' => 3,
	));
	echo '</div>';
}

/**
 * Question meta to display.
 *
 * @param false|integer $question_id question id.
 * @return string
 * @since 2.0.1
 */
function ap_display_question_metas($question_id = false) {
	if ( false === $question_id ) {
		$question_id = get_the_ID();
	}

	$metas = array();
	if ( ! is_question() ) {
		if ( ap_question_best_answer_selected() ) {
			$metas['solved'] = '<span class="ap-best-answer-label ap-tip" title="'.__( 'answer accepted', 'ap' ).'">'.__( 'Solved', 'ap' ).'</span>';
		}

		$view_count = ap_get_qa_views();
		$metas['views'] = sprintf( __( '<i>%d views</i>', 'ap' ), $view_count );
		$metas['history'] = ap_latest_post_activity_html( $question_id );
	}

	/*
     * FILTER: ap_display_question_meta
     * Used to filter question display meta
	 */
	$metas = apply_filters( 'ap_display_question_metas', $metas, $question_id );

	$output = '';
	if ( ! empty( $metas ) && is_array( $metas ) ) {
		foreach ( $metas as $meta => $display ) {
			$output .= "<span class='ap-display-meta-item {$meta}'>{$display}</span>";
		}
	}

	return $output;
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
		'follow' 			=> 'apicon-plus',
		'unfollow' 			=> 'apicon-minus',
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
	} elseif ( '' == $current_page && ! is_question() && '' == get_query_var( 'question_name' ) ) {
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
 * Post actions buttons.
 *
 * @param array $disable pass item to hide.
 * @return string
 * @since 	2.0
 */
function ap_post_actions_buttons($disable = array()) {
	global $post;

	if ( ! $post->post_type == 'question' || ! $post->post_type == 'answer' ) {
		return;
	}

	$actions = array();

	/*
     * Select answer button
     * @var string
	 */
	if ( $post->post_type == 'answer' ) {
	    $actions['select_answer'] = ap_select_answer_btn_html( $post->ID );
	}

	/*
     * Comment button
	 */
	if ( ap_user_can_comment() ) {
	    $actions['comment'] = ap_comment_btn_html();
	}

	$actions['status'] = ap_post_change_status_btn_html( $post->ID );

	/*
     * edit question link
	 */
	if ( ap_user_can_edit_question( $post->ID ) && $post->post_type == 'question' ) {
	    $actions['dropdown']['edit_question'] = ap_edit_post_link_html();
	}

	if ( ap_user_can_edit_ans( $post->ID ) && $post->post_type == 'answer' ) {
		$actions['dropdown']['edit_answer'] = ap_edit_post_link_html();
	}

	if ( is_user_logged_in() ) {
		$actions['dropdown']['flag'] = ap_flag_btn_html();
	}

	if ( is_super_admin() && $post->post_type == 'question' ) {
		$actions['dropdown']['featured'] = ap_featured_post_btn();
	}

	if ( ap_user_can_delete( $post->ID ) && $post->post_status != 'trash' ) {
		$actions['dropdown']['delete'] = ap_post_delete_btn_html();
	}

	if ( ap_user_can_delete( $post->ID ) ) {
		$actions['dropdown']['permanent_delete'] = ap_post_permanent_delete_btn_html();
	}

	/*
     * FILTER: ap_post_actions_buttons
     * For filtering post actions buttons
     * @var     string
     * @since   2.0
	 */
	$actions = apply_filters( 'ap_post_actions_buttons', $actions );

	if ( ! empty( $actions ) && count( $actions ) > 0 ) {
		echo '<ul id="ap_post_actions_'. $post->ID .'" class="ap-q-actions ap-ul-inline clearfix">';
		foreach ( $actions as $k => $action ) {
			if ( ! empty( $action ) && 'dropdown' != $k && ! in_array( $k, $disable ) ) {
				echo '<li class="ap-post-action ap-action-'.$k.'">'.$action.'</li>';
			}
		}
		if ( ! empty( $actions['dropdown'] ) ) {
			echo '<li class="ap-post-action dropdown">';
			echo '<div id="ap_post_action_'.$post->ID.'" class="ap-dropdown">';
			echo '<a class="apicon-ellipsis more-actions ap-tip ap-dropdown-toggle" title="'.__( 'More action', 'ap' ).'" href="#"></a>';
			echo '<ul class="ap-dropdown-menu">';
			foreach ( $actions['dropdown'] as $sk => $sub ) {
				echo '<li class="ap-post-action ap-action-'.$sk.'">'.$sub.'</li>';
			}
			echo '</ul>';
			echo '</div>';
			echo '</li>';
		}
		echo '</ul>';
	}
}

/**
 * Output question list sorting dropdown.
 *
 * @param string $current_url current page url.
 *
 * @since 2.3
 */
function ap_question_sorting($current_url = '') {

	if ( is_home() || is_front_page() ) {
		$current_url = home_url( '/' );
	}

	$param = array();

	$sort = isset( $_GET['ap_sort'] ) ? sanitize_text_field( wp_unslash( $_GET['ap_sort'] ) ) : 'active';

	$search_q = sanitize_text_field( get_query_var( 'ap_s' ) );

	if ( ! empty( $search_q ) ) {
		$param['ap_s'] = $search_q;
	}

	$link = add_query_arg( $param, $current_url );

	$navs = array(
		'active' => array( 'title' => __( 'Active', 'ap' ) ),
		'newest' => array( 'title' => __( 'Newest', 'ap' ) ),
	);

	if ( ! ap_opt( 'disable_voting_on_question' ) ) {
		$navs['voted'] = array( 'title' => __( 'Voted', 'ap' ) );
	}

	$navs['answers'] = array( 'title' => __( 'Answered', 'ap' ) );
	$navs['unanswered'] = array( 'title' => __( 'Unanswered', 'ap' ) );
	$navs['unsolved'] = array( 'title' => __( 'Unsolved', 'ap' ) );

	/*
     * FILTER: ap_question_sorting
     * Before prepering questions list tab.
     * @var array
     * @since 2.3
	 */
	$navs = apply_filters( 'ap_question_sorting', $navs );
	echo '<div class="ap-dropdown">';
	echo '<a id="ap-sort-anchor" class="ap-dropdown-toggle'.('' != $sort ? ' active' : '').'" href="#">'.__( 'Sort by', 'ap' ).'</a>';
	echo '<div class="ap-dropdown-menu">';
	foreach ( $navs as $k => $nav ) {
		echo '<li '.( $k == $sort ? 'class="active" ' : '').'><a href="#" data-value="'.$k.'">'.$nav['title'].'</a></li>';
	}

	echo '<input name="ap_sort" type="hidden" value="'.esc_attr( $sort ).'" />';
	echo '</div>';
	echo '</div>';
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
		'active' => array( 'link' => add_query_arg( array( 'ap_sort' => 'active' ), $base ), 'title' => __( 'Active', 'ap' ) ),
	);

	if ( ! ap_opt( 'disable_voting_on_answer' ) ) {
		$navs['voted'] = array( 'link' => add_query_arg( array( 'ap_sort' => 'voted' ), $base ), 'title' => __( 'Voted', 'ap' ) );
	}

	$navs['newest'] = array( 'link' => add_query_arg( array( 'ap_sort' => 'newest' ), $base ), 'title' => __( 'Newest', 'ap' ) );
	$navs['oldest'] = array( 'link' => add_query_arg( array( 'ap_sort' => 'oldest' ), $base ), 'title' => __( 'Oldest', 'ap' ) );

	echo '<ul class="ap-answers-tab ap-ul-inline clearfix">';
	foreach ( $navs as $k => $nav ) {
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
	if ( ap_answer_is_best( $answer_id ) ) {
		$metas['best_answer'] = '<span class="ap-best-answer-label">'.__( 'Best answer', 'ap' ).'</span>';
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
 * Output comment action links
 */
function ap_comment_actions_buttons() {
	global $comment;
	$post_o = get_post( $comment->comment_post_ID );

	if ( ! $post_o->post_type == 'question' || ! $post_o->post_type == 'answer' ) {
		return;
	}

	$actions = array();

	if ( ap_user_can_edit_comment( get_comment_ID() ) ) {
		$nonce = wp_create_nonce( 'edit_comment_'.get_comment_ID() );
		$actions['edit'] = '<a class="comment-edit-btn" href="#" data-toggle="#li-comment-'.get_comment_ID().'" data-action="load_comment_form" data-query="ap_ajax_action=load_comment_form&comment_ID='.get_comment_ID().'&__nonce='.$nonce.'">'.__( 'Edit', 'ap' ).'</a>';
	}

	if ( ap_user_can_delete_comment( get_comment_ID() ) ) {
		$nonce = wp_create_nonce( 'delete_comment' );
		$actions['delete'] = '<a class="comment-delete-btn" href="#" data-toggle="#li-comment-'.get_comment_ID().'" data-action="delete_comment" data-query="ap_ajax_action=delete_comment&comment_ID='.get_comment_ID().'&__nonce='.$nonce.'">'.__( 'Delete', 'ap' ).'</a>';
	}

	if ( is_user_logged_in() ) {
		$actions['flag'] = ap_get_comment_flag_btn( get_comment_ID() );
	}

	/*
     * FILTER: ap_comment_actions_buttons
     * For filtering post actions buttons
     * @var     string
     * @since   2.0
	 */
	$actions = apply_filters( 'ap_comment_actions_buttons', $actions );

	if ( ! empty( $actions ) && count( $actions ) > 0 ) {
		foreach ( $actions as $k => $action ) {
			echo '<span class="ap-comment-action ap-action-'.esc_attr( $k ).'">'.$action.'</span>';
		}
	}
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
	return '<a class="ap-btn-ask" href="'.ap_get_link_to( 'ask' ).'">'.__( 'Ask question', 'ap' ).'</a>';
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
 * Output the contents of help page.
 * @since 2.2
 */
function ap_how_to_ask() {

	$content = ap_get_how_to_ask();

	if ( false !== $content ) {
		echo $content;
	}
}

/**
 * Get the contents of help page.
 * @return string|false
 * @since 2.2
 */
function ap_get_how_to_ask() {

	if ( ap_opt( 'question_help_page' ) != '' ) {
		$help = get_post( (int) ap_opt( 'question_help_page' ) );

		return apply_filters( 'the_content', $help->post_content );
	}

	return false;
}

/**
 * Output the contents of answer help page.
 * @since 2.2
 */
function ap_how_to_answer() {
	$content = ap_get_how_to_answer();

	if ( false !== $content ) {
		echo $content;
	}
}

/**
 * Get the contents of help page.
 * @return string|false
 * @since 2.2
 */
function ap_get_how_to_answer() {

	if ( ap_opt( 'answer_help_page' ) != '' ) {
		$help = get_post( (int) ap_opt( 'answer_help_page' ) );

		return $help->post_content;
	}

	return false;
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

	if ( is_question_tag() ) {
		$a['tag'] = array( 'title' => __( 'Tags', 'ap' ), 'link' => '', 'order' => 10 );
	} elseif ( is_question() ) {
		$a['page'] = array( 'title' => substr( $title, 0, 30 ).(strlen( $title ) > 30 ? __( '..', 'ap' ) : ''), 'link' => get_permalink( get_question_id() ), 'order' => 10 );
	} elseif ( 'base' != $current_page && '' != $current_page ) {
		if ( 'user' == $current_page ) {
			$a['page'] = array( 'title' => __( 'User', 'ap' ), 'link' => ap_user_link( ap_get_displayed_user_id() ), 'order' => 10 );
			$a['user_page'] = array( 'title' => substr( $title, 0, 30 ).(strlen( $title ) > 30 ? __( '..', 'ap' ) : ''), 'link' => ap_user_link( ap_get_displayed_user_id(), get_query_var( 'user_page' ) ), 'order' => 10 );
		} else {
			$a['page'] = array( 'title' => substr( $title, 0, 30 ).(strlen( $title ) > 30 ? __( '..', 'ap' ) : ''), 'link' => ap_get_link_to( $current_page ), 'order' => 10 );
		}
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

	if( is_question() ){
		$query_var = 'question';
	}elseif( '' == $query_var ){
		$query_var = 'base';
	}

	return apply_filters( 'ap_current_page', esc_attr( $query_var ) );
}
