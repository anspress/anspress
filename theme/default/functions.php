<?php

/**
 * This file contains theme script, styles and other theme related functions.
 *
 * This file can be overridden by creating a anspress directory in active theme folder.
 *
 * @package    AnsPress
 * @license    http://opensource.org/licenses/gpl-license.php  GNU Public License
 * @author    Rahul Aryan <rah12@live.com>
 */
 
//include pagination function
require_once( ap_get_theme_location('pagination.php') );


add_action('wp_enqueue_scripts', 'init_scripts_front');
function init_scripts_front(){
	if(is_anspress()){
		wp_enqueue_script( 'jquery');				
		wp_enqueue_script( 'tagsinput', ap_get_theme_url('js/bootstrap-tagsinput.min.js'), 'jquery', AP_VERSION);
		wp_enqueue_script( 'jquery-form', array('jquery'), false, true );
			
		wp_enqueue_script( 'ap-site-js', ANSPRESS_URL.'assets/ap-site.js', 'jquery', AP_VERSION);		
		wp_enqueue_script( 'tooltipster', ap_get_theme_url('js/jquery.tooltipster.min.js'), 'jquery', AP_VERSION);
		wp_enqueue_script( 'jstorage', ap_get_theme_url('js/jstorage.js'), 'jquery', AP_VERSION);
		//wp_enqueue_script( 'perfect-scrollbar', ap_get_theme_url('js/perfect-scrollbar.min.js'), 'jquery', AP_VERSION);
		wp_enqueue_script( 'ap-js', ap_get_theme_url('js/ap.js'), 'jquery', AP_VERSION);
		wp_enqueue_style( 'tagsinput', ap_get_theme_url('css/bootstrap-tagsinput.css'), array(), AP_VERSION);
		wp_enqueue_style( 'tooltipster', ap_get_theme_url('css/tooltipster.css'), array(), AP_VERSION);
		//wp_enqueue_style( 'perfect-scrollbar', ap_get_theme_url('css/perfect-scrollbar.min.css'), array(), AP_VERSION);
		wp_enqueue_style( 'ap-style', ap_get_theme_url('css/ap.css'), array(), AP_VERSION);		
		
		wp_enqueue_style( 'ap-fonts', ap_get_theme_url('fonts/style.css'), array(), AP_VERSION);
		
		do_action('ap_enqueue');
		
		wp_enqueue_style( 'ap-overrides', ap_get_theme_url('css/overrides.css'), array(), AP_VERSION);
		
		?>
			<script type="text/javascript">
				var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>',
				    ap_max_tags = '<?php echo ap_opt('max_tags'); ?>';
			</script>
		<?php

		wp_localize_script( 'ap-site-js', 'aplang', array(
			'password_field_not_macthing' 	=> __( 'Password not matching', 'ap' ),
			'password_length_less' 			=> __( 'Password length must be 6 or higher', 'ap' ),
			'not_valid_email' 				=> __( 'Not a valid email', 'ap' ),
			'username_less' 				=> __( 'Username length must be 4 or higher', 'ap' ),
			'username_not_avilable' 		=> __( 'Username not available', 'ap' ),
			'email_already_in_use' 			=> sprintf(__( 'Email already in use. %sDo you want to reset your password?%s', 'ap' ), '<a href="'. wp_lostpassword_url() .'">', '</a>'),
			'loading' 						=> __( 'Loading', 'ap' ),
			'sending' 						=> __( 'Sending request', 'ap' ),
			'adding_to_fav' 				=> __( 'Adding question to your favorites', 'ap' ),
			'voting_on_post' 				=> __( 'Sending your vote', 'ap' ),
			'requesting_for_closing' 		=> __( 'Requesting for closing this question', 'ap' ),
			'sending_request' 				=> __( 'Submitting request', 'ap' ),
			'loading_comment_form' 			=> __( 'Loading comment form', 'ap' ),
			'submitting_your_question' 		=> __( 'Sending your question', 'ap' ),
			'submitting_your_answer' 		=> __( 'Sending your answer', 'ap' ),
			'submitting_your_comment' 		=> __( 'Sending your comment', 'ap' ),
			'deleting_comment' 				=> __( 'Deleting comment', 'ap' ),
			'updating_comment' 				=> __( 'Updating comment', 'ap' ),
			'loading_form' 					=> __( 'Loading form', 'ap' ),
			'saving_labels' 				=> __( 'Saving labels', 'ap' ),
			'loading_suggestions' 			=> __( 'Loading suggestions', 'ap' ),
			'uploading_cover' 				=> __( 'Uploading cover', 'ap' ),
			'saving_profile' 				=> __( 'Saving profile', 'ap' ),
			'sending_message' 				=> __( 'Sending message', 'ap' ),
			'loading_conversation' 			=> __( 'Loading conversation', 'ap' ),
			'loading_new_message_form' 		=> __( 'Loading new message form', 'ap' ),
			'loading_more_conversations' 	=> __( 'Loading more conversations', 'ap' ),
			'searching_conversations' 		=> __( 'Searching conversations', 'ap' ),
			'loading_message_edit_form' 	=> __( 'Loading message form', 'ap' ),
			'updating_message' 				=> __( 'Updating message', 'ap' ),
			'deleting_message' 				=> __( 'Deleting message', 'ap' ),
			'uploading' 					=> __( 'Uploading', 'ap' ),
		) );

		wp_localize_script( 'ap-site-js', 'apoptions', array(
			'ajaxlogin' => ap_opt('ajax_login'),
		));
	}
}


if ( ! function_exists( 'ap_comment' ) ) :
	function ap_comment( $comment ) {
		$GLOBALS['comment'] = $comment;
		?>
		<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
			<!-- comment #<?php comment_ID(); ?> -->
			<article id="comment-<?php comment_ID(); ?>" class="comment">
				<div class="ap-avatar">
					<a href="<?php echo ap_user_link($comment->user_id); ?>">
					<?php echo get_avatar( $comment, ap_opt('avatar_size_qcomment') ); ?>
					</a>
				</div>
				<div class="comment-content">
					<?php if ( '0' == $comment->comment_approved ) : ?>
						<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'ap' ); ?></p>
					<?php endif; ?>
										
					<p class="ap-comment-texts">
						<?php echo get_comment_text(); ?>
						<?php $a=" e ";$b=" ";$time=get_option('date_format').$b.get_option('time_format').$a.get_option('gmt_offset');
								printf( ' - <a title="%6$s" href="#li-comment-%7$s"><time datetime="%1$s">%2$s %3$s %5$s %4$s</time></a>',
								get_comment_time( 'c' ),
								ap_human_time(get_comment_time('U')),
								__('ago', 'ap'),
								$author = get_comment_author( $comment),
								__('by','ap'),
								get_comment_time($time),
								$comment_id = get_comment_ID()
							);
						?>
					</p>
					<div class="comment-meta">
						<?php
							
							if(ap_user_can_edit_comment(get_comment_ID()))
								echo '<a class="comment-edit-btn" href="#" data-button="ap-edit-comment" data-args="'.get_comment_ID().'-'.wp_create_nonce( 'comment-'.get_comment_ID() ).'"><i class="aicon-pencil"></i> '.__('Edit', 'ap').'</a>';
							
							if(ap_user_can_delete_comment(get_comment_ID()))
								echo '<a class="comment-delete-btn" href="#" data-button="ap-delete-comment" data-confirm="'.__('Are you sure? It cannot be undone!', 'ap').'" data-args="'.get_comment_ID().'-'.wp_create_nonce( 'delete-comment-'.get_comment_ID() ).'"><i class="aicon-close"></i> '.__('Delete', 'ap').'</a>';
						?>
					</div>					
				</div>
			</article>
		<?php
	}
endif;

add_action( 'widgets_init', 'ap_widgets_positions' );
function ap_widgets_positions(){
	register_sidebar( array(
		'name'         	=> __( 'AP Before', 'ap' ),
		'id'           	=> 'ap-before',
		'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
		'after_widget' 	=> '</div>',
		'description'  	=> __( 'Widgets in this area will be shown before anspress body.', 'ap' ),
		'before_title' 	=> '<h3 class="ap-widget-title">',
		'after_title'  	=> '</h3>',
	) );
	
	register_sidebar( array(
		'name'         	=> __( 'AP Lists Top', 'ap' ),
		'id'           	=> 'ap-top',
		'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
		'after_widget' 	=> '</div>',
		'description'  	=> __( 'Widgets in this area will be shown before questions list.', 'ap' ),
		'before_title' 	=> '<h3 class="ap-widget-title">',
		'after_title'  	=> '</h3>',
	) );
	
	register_sidebar( array(
		'name'         	=> __( 'AP Sidebar', 'ap' ),
		'id'           	=> 'ap-sidebar',
		'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
		'after_widget' 	=> '</div>',
		'description'  	=> __( 'Widgets in this area will be shown in AnsPress sidebar.', 'ap' ),
		'before_title' 	=> '<h3 class="ap-widget-title">',
		'after_title'  	=> '</h3>',
	) );
	
	register_sidebar( array(
		'name'         	=> __( 'AP Question Sidebar', 'ap' ),
		'id'           	=> 'ap-qsidebar',
		'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
		'after_widget' 	=> '</div>',
		'description'  	=> __( 'Widgets in this area will be shown in question page sidebar.', 'ap' ),
		'before_title' 	=> '<h3 class="ap-widget-title">',
		'after_title'  	=> '</h3>',
	) );
}

/* for overriding icon in social login plugin */
function ap_social_login_icons( $provider_id, $provider_name, $authenticate_url )
{
	?>
	<a rel = "nofollow" href = "<?php echo $authenticate_url; ?>" data-provider = "<?php echo  $provider_id ?>" class = "wp-social-login-provider wp-social-login-provider-<?php echo strtolower( $provider_id ); ?> btn btn-<?php echo strtolower( $provider_id ); ?>">
		<i class="ap-icon-<?php echo strtolower( $provider_id ); ?>"></i> <span><?php echo $provider_name; ?></span>
	</a>
	<?php
}
add_filter( 'wsl_render_login_form_alter_provider_icon_markup', 'ap_social_login_icons', 10, 3 );