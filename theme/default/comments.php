<?php
if ( post_password_required() || (ap_opt('logged_in_can_see_comment') && !is_user_logged_in()))
	return;
	
?>
<div id="ap-comment-area-<?php echo get_the_ID(); ?>">
	<?php if ( have_comments() ) : ?>
	<div id="comments-<?php echo get_the_ID(); ?>" class="ap-comments comment-container have-comments">
		<div class="ap_commnt_title clearfix">
			<span class="ap-icon-status ap-tlicon"></span>
			<strong class="ap-coomentcount"><?php printf(_n('1 Comment', '%d Comments', get_comment_pages_count(), 'ap', get_comment_pages_count())); ?></strong>
		</div>
		<ul class="commentlist clearfix">
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
	<?php else: ?>
		<!-- do not remove this, its needed for ajax submission -->
		<div id="comments-<?php echo get_the_ID(); ?>" class="ap-comments comment-container no-comments">		
			<ul class="commentlist">
			</ul>
		</div>
	<?php endif; ?>	
</div>