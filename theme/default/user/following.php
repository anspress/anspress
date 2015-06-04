<?php
/**
 * Display user's following page
 * @link http://anspress.io
 * @since 2.3
 * @package AnsPress
 */
?>
<div id="ap-users" class="ap-users ap-following">
	<div class="ap-users-loop clearfix">
		<?php
			while ( $following->users() ) : $following->the_user();
				include(ap_get_theme_location('users/loop-item.php'));
			endwhile;
		?>
	</div>
	<?php $following->the_pagination(); ?>
</div>

