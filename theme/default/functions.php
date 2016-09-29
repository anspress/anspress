<?php
/**
 * This file contains theme script, styles and other theme related functions.
 * This file can be overridden by creating a anspress directory in active theme folder.
 * 
 * @license   https://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 * @author    Rahul Aryan <support@anspress.io>
 */

/**
 * Enqueue scripts.
 */
add_action( 'wp_enqueue_scripts', 'ap_scripts_front', 1 );
function ap_scripts_front() {
	if ( ! is_anspress() && ap_opt('load_assets_in_anspress_only' ) ) {
		return;
	}

	ap_enqueue_scripts();

	$custom_css = '
        #anspress .ap-q-cells{
            margin-'.(is_rtl()? 'right' : 'left').': '.(ap_opt( 'avatar_size_qquestion' ) + 10).'px;
        }
        #anspress .ap-a-cells{
            margin-'.(is_rtl()? 'right' : 'left').': '.(ap_opt( 'avatar_size_qanswer' ) + 10).'px;
        }#anspress .ap-comment-content{
            margin-'.(is_rtl()? 'right' : 'left').': '.(ap_opt( 'avatar_size_qcomment' ) + 15).'px;
        }';

	wp_add_inline_style( 'ap-theme-css', $custom_css );
	do_action( 'ap_enqueue' );
	wp_enqueue_style( 'ap-overrides', ap_get_theme_url( 'css/overrides.css' ), array( 'ap-theme-css' ), AP_VERSION );

	echo '<script type="text/javascript">';
		echo 'var ajaxurl = "'.admin_url( 'admin-ajax.php' ).'",';
		echo 'ap_nonce 	= "'.wp_create_nonce( 'ap_ajax_nonce' ).'",';
	    echo 'ap_max_tags = "'.ap_opt( 'max_tags' ).'",';
	    echo 'disable_hover_card = "'.(ap_opt( 'disable_hover_card' ) ? true : false).'";';
	    echo 'disable_q_suggestion = "'. ap_disable_question_suggestion( ) .'";';
	    
	    if( !ap_opt('disable_mentions') ){
		    echo 'var apMentions = '.json_encode( ap_search_mentions() ).';
		    	var cachequeryMentions = [], itemsMentions,
		    	at_config = {
			      at: "@",
			      data: apMentions,
			      headerTpl: \'<div class="atwho-header">Member List<small>↑&nbsp;↓&nbsp;</small></div>\',
			      insertTpl: "@${login}",
			      displayTpl: \'<li data-value="${login}">${name} <small>@${login}</small></li>\',
			      limit: 50,
			      callbacks: {
				    remoteFilter: function (query, render_view) {
	                    var thisVal = query,
	                    self = jQuery(this);
	                    if( !self.data("active") && thisVal.length >= 2 ){
	                        self.data("active", true);                            
	                        itemsMentions = cachequeryMentions[thisVal]
	                        if(typeof itemsMentions == "object"){
	                            render_view(itemsMentions);
	                        }else
	                        {                            
	                            if (self.xhr) {
	                                self.xhr.abort();
	                            }
	                            self.xhr = jQuery.getJSON(ajaxurl+"?ap_ajax_action=search_mentions&action=ap_ajax&ap_ajax_nonce="+ap_nonce,{
	                                term: thisVal
	                            }, function(data) {
	                                cachequeryMentions[thisVal] = data
	                                render_view(data);
	                            });
	                        }                            
	                        self.data("active", false);                            
	                    }                    
	                }
				  }
			 };
		    ';
		}
	echo '</script>';

	wp_localize_script('anspress-js', 'aplang', array(
		'password_field_not_macthing' => __( 'Password not matching', 'anspress-question-answer' ),
		'password_length_less' => __( 'Password length must be 6 or higher', 'anspress-question-answer' ),
		'not_valid_email' => __( 'Not a valid email', 'anspress-question-answer' ),
		'username_less' => __( 'Username length must be 4 or higher', 'anspress-question-answer' ),
		'username_not_avilable' => __( 'Username not available', 'anspress-question-answer' ),
		'email_already_in_use' => sprintf(
			'%1$s <a href="%2$s">%3$s</a>',
			__( 'Email already in use.', 'anspress-question-answer' ),
			wp_lostpassword_url(),
			__( 'Do you want to reset your password?', 'anspress-question-answer' )
		),
		'loading' => __( 'Loading', 'anspress-question-answer' ),
		'sending' => __( 'Sending request', 'anspress-question-answer' ),
		'adding_to_fav' => __( 'Adding question to your favorites', 'anspress-question-answer' ),
		'voting_on_post' => __( 'Sending your vote', 'anspress-question-answer' ),
		'requesting_for_closing' => __( 'Requesting for closing this question', 'anspress-question-answer' ),
		'sending_request' => __( 'Submitting request', 'anspress-question-answer' ),
		'loading_comment_form' => __( 'Loading comment form', 'anspress-question-answer' ),
		'submitting_your_question' => __( 'Sending your question', 'anspress-question-answer' ),
		'submitting_your_answer' => __( 'Sending your answer', 'anspress-question-answer' ),
		'submitting_your_comment' => __( 'Sending your comment', 'anspress-question-answer' ),
		'deleting_comment' => __( 'Deleting comment', 'anspress-question-answer' ),
		'updating_comment' => __( 'Updating comment', 'anspress-question-answer' ),
		'loading_form' => __( 'Loading form', 'anspress-question-answer' ),
		'saving_labels' => __( 'Saving labels', 'anspress-question-answer' ),
		'loading_suggestions' => __( 'Loading suggestions', 'anspress-question-answer' ),
		'uploading_cover' => __( 'Uploading cover', 'anspress-question-answer' ),
		'saving_profile' => __( 'Saving profile', 'anspress-question-answer' ),
		'sending_message' => __( 'Sending message', 'anspress-question-answer' ),
		'loading_conversation' => __( 'Loading conversation', 'anspress-question-answer' ),
		'loading_new_message_form' => __( 'Loading new message form', 'anspress-question-answer' ),
		'loading_more_conversations' => __( 'Loading more conversations', 'anspress-question-answer' ),
		'searching_conversations' => __( 'Searching conversations', 'anspress-question-answer' ),
		'loading_message_edit_form' => __( 'Loading message form', 'anspress-question-answer' ),
		'updating_message' => __( 'Updating message', 'anspress-question-answer' ),
		'deleting_message' => __( 'Deleting message', 'anspress-question-answer' ),
		'uploading' => __( 'Uploading', 'anspress-question-answer' ),
		'error' => ap_icon( 'error' ),
		'warning' => ap_icon( 'warning' ),
		'success' => ap_icon( 'success' ),
		'not_valid_response' => __( 'Something went wrong in server side, not a valid response.', 'anspress-question-answer' ),
	));

	wp_localize_script('ap-site-js', 'apoptions', array(
			'ajaxlogin' => ap_opt( 'ajax_login' ),
		));
}

if ( ! function_exists( 'ap_comment' ) ) :
	function ap_comment($comment) {
	    $GLOBALS['comment'] = $comment;
	    include ap_get_theme_location( 'comment.php' );
	}
endif;

add_action( 'widgets_init', 'ap_widgets_positions' );
function ap_widgets_positions() {

	register_sidebar(array(
		'name' => __( 'AP Before', 'anspress-question-answer' ),
		'id' => 'ap-before',
		'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
		'after_widget' => '</div>',
		'description' => __( 'Widgets in this area will be shown before anspress body.', 'anspress-question-answer' ),
		'before_title' => '<h3 class="ap-widget-title">',
		'after_title' => '</h3>',
	));

	register_sidebar(array(
		'name' => __( 'AP Lists Top', 'anspress-question-answer' ),
		'id' => 'ap-top',
		'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
		'after_widget' => '</div>',
		'description' => __( 'Widgets in this area will be shown before questions list.', 'anspress-question-answer' ),
		'before_title' => '<h3 class="ap-widget-title">',
		'after_title' => '</h3>',
	));

	register_sidebar(array(
		'name' => __( 'AP Sidebar', 'anspress-question-answer' ),
		'id' => 'ap-sidebar',
		'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
		'after_widget' => '</div>',
		'description' => __( 'Widgets in this area will be shown in AnsPress sidebar.', 'anspress-question-answer' ),
		'before_title' => '<h3 class="ap-widget-title">',
		'after_title' => '</h3>',
	));

	register_sidebar(array(
		'name' => __( 'AP Question Sidebar', 'anspress-question-answer' ),
		'id' => 'ap-qsidebar',
		'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
		'after_widget' => '</div>',
		'description' => __( 'Widgets in this area will be shown in question page sidebar.', 'anspress-question-answer' ),
		'before_title' => '<h3 class="ap-widget-title">',
		'after_title' => '</h3>',
	));

	register_sidebar(array(
		'name' => __( 'AP Category Page', 'anspress-question-answer' ),
		'id' => 'ap-category',
		'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
		'after_widget' => '</div>',
		'description' => __( 'Widgets in this area will be shown in category listing page.', 'anspress-question-answer' ),
		'before_title' => '<h3 class="ap-widget-title">',
		'after_title' => '</h3>',
	));

	register_sidebar(array(
		'name' => __( 'AP Tag page', 'anspress-question-answer' ),
		'id' => 'ap-tag',
		'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
		'after_widget' => '</div>',
		'description' => __( 'Widgets in this area will be shown in tag listing page.', 'anspress-question-answer' ),
		'before_title' => '<h3 class="ap-widget-title">',
		'after_title' => '</h3>',
	));

	register_sidebar(array(
		'name' => __( 'AP User', 'anspress-question-answer' ),
		'id' => 'ap-user',
		'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
		'after_widget' => '</div>',
		'description' => __( 'Widgets in this area will be shown in user page.', 'anspress-question-answer' ),
		'before_title' => '<h3 class="ap-widget-title">',
		'after_title' => '</h3>',
	));
	register_sidebar(array(
		'name' => __( 'AP Activity', 'anspress-question-answer' ),
		'id' => 'ap-activity',
		'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
		'after_widget' => '</div>',
		'description' => __( 'Widgets in this area will be shown AnsPress activity page.', 'anspress-question-answer' ),
		'before_title' => '<h3 class="ap-widget-title">',
		'after_title' => '</h3>',
	));
}

