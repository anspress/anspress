<div id="ap-followers" class="ap-users-lists clearfix">
	<?php

	if ( ! empty( $followers ) ) {
		foreach ( $followers as $f ) {
			$data = $f->data;
			$current_user_meta = array_map( function( $a ){ return $a[0]; }, get_user_meta($f->ID));
			include(ap_get_theme_location('content-user.php'));
		}
	} else {
		_e('No users found.', 'ap');
	}
	?>
</div>

<?php if ( ! empty( $followers ) ) {
	ap_pagi($base, $total_pages, $paged);}
?>