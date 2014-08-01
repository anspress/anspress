<div id="ap-followers" class="ap-users-lists clearfix">
	<?php
	if ( ! empty( $followers ) ) {
		foreach ( $followers as $f ) {
			$data = $f->data;
		?>
			<div class="ap-user">
				<div class="ap-user-inner clearfix">
					<div class="ap-cover"></div>
					<div class="ap-user-summary">
						<a class="ap-user-avatar" href="<?php echo ap_user_link($f->ID); ?>">
							<?php echo get_avatar( $f->ID, 50 ); ?>
						</a>
						<a class="user-name" href="<?php echo ap_user_link($f->ID); ?>"><?php echo $data->display_name; ?></a>
						<?php echo ap_get_rank_title($f->ID); ?>
					</div>
				</div>
			</div>
		<?php
		}
	} else {
		echo 'No followers yet.';
	}
	?>
</div>