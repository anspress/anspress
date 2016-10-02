<?php
/**
 * Display user's followers page
 * @link https://anspress.io
 * @since 2.3
 * @package AnsPress
 */
?>
<div id="ap-users" class="ap-users ap-followers">
	<h3 class="ap-user-page-title clearfix">
		<?php echo ap_page_title() ?>
	</h3>
	<div class="ap-users-loop clearfix">
		<?php
			while ( $followers->users() ) : $followers->the_user();
				include(ap_get_theme_location('users/loop-item.php'));
			endwhile;
		?>
	</div>
	<?php $followers->the_pagination(); ?>
</div>

