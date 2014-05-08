<?php
if ( post_password_required() )
	return;
	
?>
<div id="comments-<?php echo $post->ID; ?>" class="ap-comments comment-container">	
	<?php if ( have_comments() ) : ?>
		<div class="ap-comments-inner">
			<ul class="commentlist">
				<?php wp_list_comments( array( 'max_depth' => 0, 'callback' => 'ap_comment', 'style' => 'ul' ) ); ?>
			</ul><!-- .commentlist -->

			<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // are there comments to navigate through ?>
			<nav id="comment-nav-below" class="navigation" role="navigation">
				<h1 class="assistive-text section-heading"><?php _e( 'Comment navigation', 'ap' ); ?></h1>
				<div class="nav-previous"><?php previous_comments_link( __( '&larr; Older Comments', 'ap' ) ); ?></div>
				<div class="nav-next"><?php next_comments_link( __( 'Newer Comments &rarr;', 'ap' ) ); ?></div>
			</nav>
			<?php endif;  ?>
		</div>
	<?php endif; ?>		
</div>