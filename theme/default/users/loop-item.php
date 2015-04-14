<?php
/**
 * User item inside loop
 *
 * Template is used to display user inside a loop
 *
 * @package AnsPress
 */
	
?>
<div class="ap-users-item" data-id="<?php ap_user_the_ID(); ?>">
	<div class="ap-users-inner clearfix">
		<div class="ap-users-summary clearfix">
			<a class="ap-users-avatar" href="<?php ap_user_the_link(); ?>">
				<?php ap_user_the_avatar()  ?>
			</a>
			<div class="no-overflow">
				<a class="ap-users-name" href="<?php ap_user_the_link(); ?>"><?php ap_user_the_display_name(); ?></a>
			</div>
		</div>
		<ul class="ap-users-meta ap-inline-list clearfix">
			<li><a href="<?php echo ap_user_link(ap_user_get_the_ID(), 'points'); ?>"><b data-view="ap-points"><?php ap_user_the_reputation(); ?></b><span><?php _e('Reputation', 'ap') ?></span></a></li>
		</ul>
	</div>
</div>