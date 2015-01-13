<?php
/**
 * Display AnsPress user page
 *
 * @link http://wp3.in
 * @since 2.0.1
 *
 * @package AnsPress
 */
$user_id 		= ap_user_page_user_id();
$ap_user 		= ap_user();
$ap_user_data 	= ap_user_data();
?>
<div id="ap-profile" class="clearfix" data-id="<?php echo ap_user_page_user_id(); ?>">
	<!-- 	<div class="ap-profile-cover clearfix">
			<div data-view="cover" class="ap-cover-bg" <?php ap_user_cover_style($user_id); ?>></div>
			<div class="ap-profile-head clearfix">
				
				<div class="ap-user-summery">
					<?php ap_follow_btn_html($user_id); ?>
					<?php //ap_message_btn_html($user_id, $ap_user_data->display_name); ?>				
					
					<span class="ap-user-rank"><?php //echo ap_get_rank_title($user_id); ?></span>			
				</div>
				<div class="ap-cover-bottom">
					
				</div>
			</div>		
		</div> -->

		<!-- Start  ap-profile-lr -->
		<div class="ap-profile-lr clearfix">
			<div class="ap-profile-nav clearfix">
				<div class="ap-avatar-meta-c">
					<div class="ap-big-avatar">
						<?php ap_avatar_upload_form(); ?>
						<!-- TODO: OPTION avatar size to option -->
						<?php echo get_avatar( $ap_user->user_email, 200 ); ?>						
					</div>
					<?php ap_user_profile_meta(); ?>
				</div>
				<?php ap_user_menu(); ?>
			</div>
			<div class="ap-profile-right clearfix">

				<?php 
					/* include proper user template */
					ap_user_page();
				?>
			</div>
		</div>
		<!-- End ap-profile-lr -->
</div>