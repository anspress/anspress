<?php

/**
 * This file contains theme script, styles and other theme related functions.
 *
 * This file can be overridden by creating a anspress directory in active theme folder.
 *
 * @license    http://opensource.org/licenses/gpl-license.php  GNU Public License
 * @author    Rahul Aryan <support@anspress.io>
 */

/**
 * Enqueue scripts.
 */
add_action( 'wp_enqueue_scripts', 'ap_scripts_front', 1 );
function ap_scripts_front() {
	$dir = ap_env_dev() ? 'js' : 'min';
	$min = ap_env_dev() ? '' : '.min';

	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-form', array( 'jquery' ), false );
	wp_enqueue_script( 'ap-functions-js', ANSPRESS_URL.'assets/'.$dir.'/ap-functions'.$min.'.js', array( 'jquery', 'jquery-form' ) );
	wp_enqueue_script( 'ap-anspress_script', ANSPRESS_URL.'assets/'.$dir.'/anspress_site'.$min.'.js', array( 'jquery', 'jquery-form' ), AP_VERSION );
	wp_enqueue_script( 'peity-js', ap_get_theme_url( 'js/jquery.peity.min.js' ), 'jquery', AP_VERSION );
	wp_enqueue_script( 'ap-initial.js', ap_get_theme_url( 'js/initial.min.js' ), 'jquery', AP_VERSION );
	wp_enqueue_script( 'ap-scrollbar.js', ap_get_theme_url( 'js/jquery.scrollbar.min.js' ), 'jquery', AP_VERSION );
	wp_enqueue_script( 'ap-js', ap_get_theme_url( $dir.'/ap'.$min.'.js' ), array( 'jquery', 'jquery-form' ), AP_VERSION );
	wp_enqueue_style( 'ap-style', ap_get_theme_url( 'css/main.css' ), array(), AP_VERSION );

	$custom_css = '
        #anspress .ap-q-cells{
                margin-left: '.(ap_opt( 'avatar_size_qquestion' ) + 10).'px;
        }
        #anspress .ap-a-cells{
                margin-left: '.(ap_opt( 'avatar_size_qanswer' ) + 10).'px;
        }#anspress .ap-comment-content{
                margin-left: '.(ap_opt( 'avatar_size_qcomment' ) + 15).'px;
        }';

	wp_add_inline_style( 'ap-style', $custom_css );
	wp_enqueue_style( 'ap-fonts', ap_get_theme_url( 'fonts/style.css' ), array(), AP_VERSION );
	
	do_action( 'ap_enqueue' );
	
	wp_enqueue_style( 'ap-responsive', ap_get_theme_url( 'css/responsive.css' ), array(), AP_VERSION );
	wp_enqueue_style( 'ap-overrides', ap_get_theme_url( 'css/overrides.css' ), array(), AP_VERSION );

	echo '<script type="text/javascript">';
		echo 'var ajaxurl = "'.admin_url( 'admin-ajax.php' ).'",';
		echo 'ap_nonce 	= "'.wp_create_nonce( 'ap_ajax_nonce' ).'",';
	    echo 'ap_max_tags = "'.ap_opt( 'max_tags' ).'",';
	    echo 'disable_hover_card = "'.(ap_opt( 'disable_hover_card' ) ? true : false).'"';
	echo '</script>';

	wp_localize_script('ap-anspress_script', 'aplang', array(
		'password_field_not_macthing' => __( 'Password not matching', 'ap' ),
		'password_length_less' => __( 'Password length must be 6 or higher', 'ap' ),
		'not_valid_email' => __( 'Not a valid email', 'ap' ),
		'username_less' => __( 'Username length must be 4 or higher', 'ap' ),
		'username_not_avilable' => __( 'Username not available', 'ap' ),
		'email_already_in_use' => sprintf( __( 'Email already in use. %sDo you want to reset your password?%s', 'ap' ), '<a href="'.wp_lostpassword_url().'">', '</a>' ),
		'loading' => __( 'Loading', 'ap' ),
		'sending' => __( 'Sending request', 'ap' ),
		'adding_to_fav' => __( 'Adding question to your favorites', 'ap' ),
		'voting_on_post' => __( 'Sending your vote', 'ap' ),
		'requesting_for_closing' => __( 'Requesting for closing this question', 'ap' ),
		'sending_request' => __( 'Submitting request', 'ap' ),
		'loading_comment_form' => __( 'Loading comment form', 'ap' ),
		'submitting_your_question' => __( 'Sending your question', 'ap' ),
		'submitting_your_answer' => __( 'Sending your answer', 'ap' ),
		'submitting_your_comment' => __( 'Sending your comment', 'ap' ),
		'deleting_comment' => __( 'Deleting comment', 'ap' ),
		'updating_comment' => __( 'Updating comment', 'ap' ),
		'loading_form' => __( 'Loading form', 'ap' ),
		'saving_labels' => __( 'Saving labels', 'ap' ),
		'loading_suggestions' => __( 'Loading suggestions', 'ap' ),
		'uploading_cover' => __( 'Uploading cover', 'ap' ),
		'saving_profile' => __( 'Saving profile', 'ap' ),
		'sending_message' => __( 'Sending message', 'ap' ),
		'loading_conversation' => __( 'Loading conversation', 'ap' ),
		'loading_new_message_form' => __( 'Loading new message form', 'ap' ),
		'loading_more_conversations' => __( 'Loading more conversations', 'ap' ),
		'searching_conversations' => __( 'Searching conversations', 'ap' ),
		'loading_message_edit_form' => __( 'Loading message form', 'ap' ),
		'updating_message' => __( 'Updating message', 'ap' ),
		'deleting_message' => __( 'Deleting message', 'ap' ),
		'uploading' => __( 'Uploading', 'ap' ),
		'error' => ap_icon( 'error' ),
		'warning' => ap_icon( 'warning' ),
		'success' => ap_icon( 'success' ),
		'not_valid_response' => __( 'Something went wrong in server side, not a valid response.', 'ap' ),
	));

	wp_localize_script('ap-site-js', 'apoptions', array(
			'ajaxlogin' => ap_opt( 'ajax_login' ),
		));
}

if ( ! function_exists( 'ap_comment' ) ) :
	function ap_comment($comment) {

	    $GLOBALS['comment'] = $comment;
	    $class = '0' == $comment->comment_approved ? ' pending' : '';

	    include ap_get_theme_location( 'comment.php' );
	}
endif;

add_action( 'widgets_init', 'ap_widgets_positions' );
function ap_widgets_positions() {

	register_sidebar(array(
		'name' => __( 'AP Before', 'ap' ),
		'id' => 'ap-before',
		'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
		'after_widget' => '</div>',
		'description' => __( 'Widgets in this area will be shown before anspress body.', 'ap' ),
		'before_title' => '<h3 class="ap-widget-title">',
		'after_title' => '</h3>',
	));

	register_sidebar(array(
		'name' => __( 'AP Lists Top', 'ap' ),
		'id' => 'ap-top',
		'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
		'after_widget' => '</div>',
		'description' => __( 'Widgets in this area will be shown before questions list.', 'ap' ),
		'before_title' => '<h3 class="ap-widget-title">',
		'after_title' => '</h3>',
	));

	register_sidebar(array(
		'name' => __( 'AP Sidebar', 'ap' ),
		'id' => 'ap-sidebar',
		'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
		'after_widget' => '</div>',
		'description' => __( 'Widgets in this area will be shown in AnsPress sidebar.', 'ap' ),
		'before_title' => '<h3 class="ap-widget-title">',
		'after_title' => '</h3>',
	));

	register_sidebar(array(
		'name' => __( 'AP Question Sidebar', 'ap' ),
		'id' => 'ap-qsidebar',
		'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
		'after_widget' => '</div>',
		'description' => __( 'Widgets in this area will be shown in question page sidebar.', 'ap' ),
		'before_title' => '<h3 class="ap-widget-title">',
		'after_title' => '</h3>',
	));

	register_sidebar(array(
		'name' => __( 'AP Category Page', 'ap' ),
		'id' => 'ap-category',
		'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
		'after_widget' => '</div>',
		'description' => __( 'Widgets in this area will be shown in category listing page.', 'ap' ),
		'before_title' => '<h3 class="ap-widget-title">',
		'after_title' => '</h3>',
	));

	register_sidebar(array(
		'name' => __( 'AP Tag page', 'ap' ),
		'id' => 'ap-tag',
		'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
		'after_widget' => '</div>',
		'description' => __( 'Widgets in this area will be shown in tag listing page.', 'ap' ),
		'before_title' => '<h3 class="ap-widget-title">',
		'after_title' => '</h3>',
	));

	register_sidebar(array(
		'name' => __( 'AP User', 'ap' ),
		'id' => 'ap-user',
		'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
		'after_widget' => '</div>',
		'description' => __( 'Widgets in this area will be shown in user page.', 'ap' ),
		'before_title' => '<h3 class="ap-widget-title">',
		'after_title' => '</h3>',
	));
	register_sidebar(array(
		'name' => __( 'AP Activity', 'ap' ),
		'id' => 'ap-activity',
		'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
		'after_widget' => '</div>',
		'description' => __( 'Widgets in this area will be shown AnsPress activity page.', 'ap' ),
		'before_title' => '<h3 class="ap-widget-title">',
		'after_title' => '</h3>',
	));
}

