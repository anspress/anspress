<?php
/**
 * Display AnsPress user page
 *
 * @link http://wp3.in
 * @since 2.0.1
 *
 * @package AnsPress
 */

///$ap_user 		= ap_user();
//$ap_user_data 	= ap_user_data();

?>

<?php while ( ap_users() ) : ap_the_user(); ?>
	<div id="ap-user" class="ap-user clearfix" data-id="<?php ap_displayed_user_id(); ?>">
		<?php

			/*<div class="ap-profile-cover clearfix">
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
				</div>*/
			?>

			<!-- Start  ap-profile-lr -->
			<div class="ap-user-lr row">
				<div class="col-md-3 ap-user-left">
					<div class="ap-user-info clearfix">
						<div class="ap-user-avatar ap-pull-left">
							<?php ap_user_the_avatar(50); ?>						
						</div>
						<div class="ap-user-data no-overflow">
							<a class="ap-user-name" href="<?php ap_user_the_link(); ?>"><?php ap_user_the_display_name(); ?></a>
							<?php
								/**
								 * ACTION: ap_user_left_after_name
								 */
								do_action('ap_user_left_after_name');
							?>
						</div>					
					</div>
					<?php ap_user_menu(); ?>
				</div>
				<div class="col-md-9 ap-user-right">					
					<?php 
						/* include proper user template */
						ap_user_page();
					?>
				</div>
			</div>
			<!-- End ap-profile-lr -->
	</div>
<?php endwhile; ?>
