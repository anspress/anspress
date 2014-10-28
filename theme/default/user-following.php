<div id="ap-following" class="ap-users-lists clearfix">
	<?php

	if ( ! empty( $following ) ) {
		foreach ( $following as $f ) {
			$data = $f->data;
			$current_user_meta = array_map( function( $a ){ return $a[0]; }, get_user_meta($f->ID));
			include(ap_get_theme_location('content-user.php'));
		}		
	} else {
		_e('No users found.', 'ap');
	}
	?>
</div>

<?php if ( ! empty( $following ) ) {
	ap_pagi($base, $total_pages, $paged);}
?>