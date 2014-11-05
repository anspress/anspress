<h1 class="entry-title">
		<?php if (!ap_opt('double_titles'))
		the_title(); 
		?>
	<a class="ap-btn ap-ask-btn-head pull-right" href="<?php echo ap_get_link_to('ask') ?>"><?php _e('Ask Question'); ?></a>
</h1>
<div id="ap-users" class="clearfix">
	<?php
	ap_users_tab();
	
	if ( ! empty( $users->results ) ) {
		foreach ( $users->results as $f ) {
			$data = $f->data;
			$current_user_meta = array_map( function( $a ){ return $a[0]; }, get_user_meta($f->ID));
			include(ap_get_theme_location('content-user.php'));
		}
	} else {
		_e('No users found.', 'ap');
	}
	?>
</div>

<?php 
if ( ! ( $users->results ) )  {
	ap_pagi($base, $total_pages, $paged);
}
