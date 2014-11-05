<div id="ap-profile" class="clearfix" data-id="<?php echo $userid; ?>">
	<?php if(is_ap_profile()): ?>
	<div class="ap-profile-cover clearfix">
		<div data-view="cover" class="ap-cover-bg" <?php ap_user_cover_style($userid); ?>><?php ap_cover_upload_form(); ?></div>
		<div class="ap-profile-head clearfix">
			<div class="ap-user-image">
				<?php ap_avatar_upload_form(); ?>
				<div class="ap-useravin" data-view="avatar-main">					
					<?php echo get_avatar( $user->user_email, 105 ); ?>
				</div>
			</div>
			<div class="ap-user-summery">
				<?php ap_follow_btn_html($userid); ?>
				<?php //ap_message_btn_html($userid, $display_name); ?>				
				<h1 class="ap-user-name"><?php echo $display_name; ?></h1>
				<span class="ap-user-rank"><?php echo ap_get_rank_title($userid); ?></span>			
			</div>
			<div class="ap-cover-bottom">
				<ul class="ap-user-ffp ap-inline-list clearfix">
					<li><a href="<?php echo ap_user_link($userid, 'followers'); ?>"><b data-view="ap-followers"><?php echo ap_get_current_user_meta('followers') ?></b><span><?php _e('Followers', 'ap') ?></span></a></li>
					<li><a href="<?php echo ap_user_link($userid, 'following'); ?>"><b data-view="ap-following"><?php echo ap_get_current_user_meta('following') ?></b><span><?php _e('Following', 'ap') ?></span></a></li>
					<li><a href="<?php echo ap_user_link($userid, 'points'); ?>"><b data-view="ap-points"><?php echo ap_get_points($userid, true); ?></b><span><?php _e('Points', 'ap') ?></span></a></li>
				</ul>
			</div>
		</div>		
	</div>
	<?php endif; ?>
	<div class="ap-profile-lr">
		<div class="ap-profile-nav clearfix">
			<?php ap_user_menu(); ?>
		</div>
		<div class="ap-profile-right clearfix">
			<div class="ap-user-tm clearfix">
				<h2 class="entry-title">
				<?php if (!ap_opt('double_titles'))
				echo ap_page_title();?>
				</h2>
				<?php ap_user_page_menu(); ?>
			</div>
			<?php 
				/* include proper user template */
				ap_user_template(); 
			?>
		</div>
	</div>
</div>