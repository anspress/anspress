<?php
/**
 * Display user's followers page
 * @link http://anspress.io
 * @since 2.3
 * @package AnsPress
 */
?>
<div id="ap-users" class="ap-users ap-followers">
	<div class="ap-users-loop clearfix">
		<?php
			while ( $followers->users() ) : $followers->the_user();
				include(ap_get_theme_location('users/loop-item.php'));
			endwhile;
		?>
	</div>
	<?php ap_users_the_pagination(); ?>
</div>

