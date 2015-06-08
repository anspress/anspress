<?php
/**
 * Display user profile page
 *
 * @link http://wp3.in
 * @since unknown
 * @package AnsPress
 */
?>
<div class="ap-profile-box clearfix">
	<h3 class="ap-user-page-title clearfix">
		<?php echo ap_page_title() ?>
	</h3>
	<div class="ap-profile-tab-c clearfix">
		<?php ap_user_profile_tab(); ?>
	</div>
	<div class="ap-profile-form clearfix">
		<?php ap_user_fields(); ?>
	</div>
</div>

