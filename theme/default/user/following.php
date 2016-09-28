<?php
/**
 * Display user's following page
 * @link https://anspress.io
 * @since 2.3
 * @package AnsPress
 */
?>
<div id="ap-users" class="ap-users ap-following">
	<h3 class="ap-user-page-title clearfix">
		<?php echo ap_page_title() ?>
	</h3>
	<div class="ap-users-loop clearfix">
		<?php
			while ( $following->users() ) : $following->the_user();
				include(ap_get_theme_location('users/loop-item.php'));
			endwhile;
		?>
	</div>
	<?php $following->the_pagination(); ?>
</div>

