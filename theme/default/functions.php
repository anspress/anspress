<?php
//include pagination function
require_once( ap_get_theme_location('pagination.php') );


add_action('wp_enqueue_scripts', 'init_scripts_front');
function init_scripts_front(){
	if(is_anspress()){
		wp_enqueue_script( 'jquery');				
		wp_enqueue_script( 'ap-js', ap_get_theme_url('js/ap.js'), 'jquery', AP_VERSION);	
		wp_enqueue_script( 'ap-site-js', ANSPRESS_URL.'assets/ap-site.js', 'jquery', AP_VERSION);	
		wp_enqueue_script( 'bootstrap-js', ap_get_theme_url('js/bootstrap.min.js'), array(), AP_VERSION);	
		
		if(ap_opt('bootstrap'))
			wp_enqueue_style( 'bootstrap', ANSPRESS_URL.'assets/bootstrap.min.css', array(), AP_VERSION);
			
		wp_enqueue_style( 'ap-style', ap_get_theme_url('css/ap.css'), array(), AP_VERSION);
		wp_enqueue_style( 'ap-fonts', ap_get_theme_url('fonts/style.css'), array(), AP_VERSION);
		?>
			<script type="text/javascript">
				var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
			</script>
		<?php

		wp_localize_script( 'ap-site-js', 'aplang', array(
			'password_field_not_macthing' => __( 'Password not matching', 'ap' ),
			'password_length_less' => __( 'Password length must be 6 or higher', 'ap' ),
			'not_valid_email' => __( 'Not a valid email', 'ap' ),
			'username_less' => __( 'Username length must be 4 or higher', 'ap' ),
			'username_not_avilable' => __( 'Username not available', 'ap' ),
			'email_already_in_use' => __( 'Email already in use', 'ap' ),
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
					<?php echo get_avatar( $comment, 30 ); ?>
				</div>
				<div class="comment-content">
					<?php if ( '0' == $comment->comment_approved ) : ?>
						<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'ap' ); ?></p>
					<?php endif; ?>
					<div class="comment-meta">
						<?php
							
							if(ap_user_can_edit_comment(get_comment_ID()))
								echo '<a class="comment-edit-btn" href="#" data-action="edit-comment" data-args="'.get_comment_ID().'-'.wp_create_nonce( 'comment-'.get_comment_ID() ).'"><i class="aicon-pencil"></i> '.__('Edit', 'ap').'</a>';
							
							if(ap_user_can_delete_comment(get_comment_ID()))
								echo '<a class="comment-delete-btn" href="#" data-action="delete-comment" data-confirm="'.__('Are you sure? It cannot be undone!', 'ap').'" data-args="'.get_comment_ID().'-'.wp_create_nonce( 'delete-comment-'.get_comment_ID() ).'"><i class="aicon-close"></i> '.__('Delete', 'ap').'</a>';
						?>
					</div>					
					<p class="ap-comment-texts">
						<?php echo get_comment_text(); ?>
						<?php printf( ' - <time datetime="%1$s">%2$s %3$s</time>',
								get_comment_time( 'c' ),
								ap_human_time(get_comment_time('U')),
								__('ago', 'ap')
							);
						?>
					</p>						
				</div>
			</article>
		<?php
	}
endif;
?>