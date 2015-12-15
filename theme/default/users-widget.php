<div class="ap-uw clearfix">
	<?php 
		if ( ! empty( $users->results ) ) {
			foreach ( $users->results as $f ) {
				$data = $f->data;
				$current_user_meta = array_map( function( $a ){ return $a[0]; }, get_user_meta($f->ID));
				?>
				<div class="apw-user-summary clearfix">
					<?php ap_follow_btn_html($f->ID, true); ?>
					<a class="ap-user-avatar" href="<?php echo ap_user_link($f->ID); ?>">
						<?php echo get_avatar( $f->ID, 40 ); ?>
					</a>
					<div class="no-overflow">
						<a class="user-name" href="<?php echo ap_user_link($f->ID); ?>"><?php echo 	$data->display_name; ?></a>
						<?php echo ap_get_rank_title($f->ID); ?>
					</div>
				</div>
				<?php
			}
		} else {
			_e('No users found.', 'anspress-question-answer');
		} 
	?>	
</div>


