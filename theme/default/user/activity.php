<?php
/**
 * Display user profile page
 *
 * @link http://wp3.in
 * @since unknown
 * @package AnsPress
 */
?>

<?php //ap_profile_user_stats_counts() ?>

<!-- start about me -->		
<?php /*if($description != '') : ?>
	<div class="ap-profile-box ap-about-me">
		<h3 class="ap-box-title"><?php _e('About Me', 'ap'); ?></h3>
		<p class="about-me">
			<?php echo $description; ?>
		</p>
	</div>			
<?php endif; */?>
<!-- End about me -->

<div class="ap-profile-box clearfix">
	<?php
		ap_get_template_part('user/posts');
	?>
</div>

