<div class="ap-user-card" data-id="<?php echo $f->ID; ?>">
	<div class="ap-user-inner clearfix">
		<div class="ap-cover" <?php ap_user_cover_style($f->ID); ?>><span class="ap-cover-layer"></span></div>
		<div class="ap-user-summary clearfix">
			<?php ap_follow_btn_html($f->ID); ?>
			<a class="ap-user-avatar" href="<?php echo ap_user_link($f->ID); ?>">
				<?php echo get_avatar( $f->ID, 40 ); ?>
			</a>
			<div class="no-overflow">
				<a class="user-name" href="<?php echo ap_user_link($f->ID); ?>"><?php echo 	$data->display_name; ?></a>
				<?php echo ap_get_rank_title($f->ID); ?>
			</div>
		</div>
		<ul class="ap-point-ff ap-inline-list clearfix">
			<li><a href="<?php echo ap_user_link($f->ID, 'followers'); ?>"><b data-view="ap-followers"><?php echo ap_get_current_user_meta('followers') ?></b><span><?php _e('Followers', 'anspress-question-answer') ?></span></a></li>
			<li><a href="<?php echo ap_user_link($f->ID, 'following'); ?>"><b data-view="ap-following"><?php echo ap_get_current_user_meta('following') ?></b><span><?php _e('Following', 'anspress-question-answer') ?></span></a></li>
			<li><a href="<?php echo ap_user_link($f->ID, 'points'); ?>"><b data-view="ap-points"><?php echo ap_get_points($f->ID, true); ?></b><span><?php _e('Points', 'anspress-question-answer') ?></span></a></li>
		</ul>
	</div>
</div>
