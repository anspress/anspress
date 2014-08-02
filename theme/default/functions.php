<?php
//include pagination function
require_once( ap_get_theme_location('pagination.php') );


add_action('wp_enqueue_scripts', 'init_scripts_front');
function init_scripts_front(){
	if(is_anspress()){
		wp_enqueue_script( 'jquery');				
		wp_enqueue_script( 'tagsinput', ap_get_theme_url('js/bootstrap-tagsinput.min.js'), 'jquery', AP_VERSION);
		wp_enqueue_script( 'jquery-form', array('jquery'), false, true );
			
		wp_enqueue_script( 'ap-site-js', ANSPRESS_URL.'assets/ap-site.js', 'jquery', AP_VERSION);		
		wp_enqueue_script( 'ap-js', ap_get_theme_url('js/ap.js'), 'jquery', AP_VERSION);
		wp_enqueue_style( 'tagsinput', ap_get_theme_url('css/bootstrap-tagsinput.css'), array(), AP_VERSION);
		wp_enqueue_style( 'ap-style', ap_get_theme_url('css/ap.css'), array(), AP_VERSION);
		
		wp_enqueue_style( 'ap-fonts', ap_get_theme_url('fonts/style.css'), array(), AP_VERSION);
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
			'email_already_in_use' 			=> __( 'Email already in use', 'ap' ),
			'loading' 						=> __( 'Loading', 'ap' ),
			'adding_to_fav' 				=> __( 'Adding question to your favorites', 'ap' ),
			'voting_on_post' 				=> __( 'Sending your vote', 'ap' ),
			'requesting_for_closing' 		=> __( 'Requesting for closing this question', 'ap' ),
			'sending_request' 				=> __( 'Submitting request', 'ap' ),
			'loading_comment_form' 			=> __( 'Loading comment form', 'ap' ),
			'submitting_your_answer' 		=> __( 'Sending your answer', 'ap' ),
			'submitting_your_comment' 		=> __( 'Sending your comment', 'ap' ),
			'deleting_comment' 				=> __( 'Deleting comment', 'ap' ),
			'updating_comment' 				=> __( 'Updating comment', 'ap' ),
			'loading_form' 					=> __( 'Loading form', 'ap' ),
			'saving_labels' 				=> __( 'Saving labels', 'ap' ),
			'loading_suggestions' 			=> __( 'Loading suggestions', 'ap' ),
			'uploading_cover' 				=> __( 'Uploading cover', 'ap' ),
			'saving_profile' 				=> __( 'Saving profile', 'ap' ),
		) );
	}
}


if ( ! function_exists( 'ap_comment' ) ) :
	function ap_comment( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		?>
		<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
			<article id="comment-<?php comment_ID(); ?>" class="comment">
				<div class="ap-avatar">
					<?php echo get_avatar( $comment, ap_opt('comment_avatar_size') ); ?>
				</div>
				<div class="comment-content">
					<?php if ( '0' == $comment->comment_approved ) : ?>
						<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'ap' ); ?></p>
					<?php endif; ?>
										
					<p class="ap-comment-texts">
						<?php echo get_comment_text(); ?>
						<?php printf( ' - <time datetime="%1$s">%2$s %3$s</time>',
								get_comment_time( 'c' ),
								ap_human_time(get_comment_time('U')),
								__('ago', 'ap')
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
?>