<?php
/**
 * Display AnsPress user page
 *
 * @link http://anspress.io
 * @since 2.0.1
 *
 * @package AnsPress
 */

?>
<?php while ( ap_users() ) : ap_the_user(); ?>

	<div id="ap-user" class="ap-user" data-id="<?php ap_displayed_user_id(); ?>">
		<div class="ap-user-navigation clearfix">
			<?php if(ap_active_user_page() != 'about'): ?>
				<div class="ap-user-info ">
					<div class="ap-user-avatar">
						<?php ap_user_the_avatar(40); ?>
						<?php ap_avatar_upload_form(); ?>
					</div>
					<div class="ap-user-data">
						<a class="ap-user-name" href="<?php ap_user_the_link(); ?>"><?php ap_user_the_display_name(); ?></a>
						<?php
							if(!ap_opt('disable_reputation')){
								echo '<span class="ap-user-reputation">';
								printf(__('%s Rep.', 'ap'), ap_user_get_the_reputation());
								echo '</span>';
							}
						?>
					</div>
					<div class="ap-user-info-btns">
						<?php ap_follow_button(ap_get_displayed_user_id()); ?>
					</div>
				</div>
			<?php endif; ?>
			<?php ap_user_menu(); ?>
		</div>

		<?php if(ap_active_user_page() == 'about'): ?>
			<div class="ap-user-cover clearfix">
				<?php ap_cover_upload_form(); ?>
				<div class="ap-user-cover-img" style="background-image:url(<?php echo ap_get_cover_src(); ?>)" data-view="user_cover_<?php ap_displayed_user_id(); ?>"></div>
			</div>
			<div class="ap-user-head clearfix">
				<div class="ap-user-avatar">
					<?php ap_user_the_avatar(150); ?>
					<?php ap_avatar_upload_form(); ?>
				</div>
				<a class="ap-user-name" href="<?php ap_user_the_link(); ?>"><?php ap_user_the_display_name(); ?></a>
				<div class="ap-user-mini-status">
					<span><?php printf(__('%s Rep.', 'ap'), ap_user_get_the_reputation()); ?></span>
					<span><?php printf(__('%d Answers', 'ap'), ap_user_get_the_meta('__total_answers')); ?></span>
					<span><?php printf(__('%d Questions', 'ap'), ap_user_get_the_meta('__total_questions')); ?></span>
					<span><?php printf(__('%d Followers', 'ap'), ap_user_get_the_meta('__total_followers')); ?></span>
					<span><?php printf(__('%d Following', 'ap'), ap_user_get_the_meta('__total_following')); ?></span>
				</div>
				<?php if(ap_user_meta_exists('description')): ?>
					<div class="ap-user-dscription">
						<div class="ap-user-description-in">
							<div id="user-summery">
								<?php ap_user_the_meta('description'); ?>
							</div>
						</div>
						<a href="#" data-action="ap_expand" data-expand="#user-summery"><?php echo ap_icon('ellipsis', true); ?></a>
					</div>
				<?php endif; ?>
				<div class="ap-user-buttons clearfix">
					<?php ap_follow_button(ap_get_displayed_user_id()); ?>
				</div>
			</div>
		<?php endif; ?>
		<!-- Start  ap-profile-lr -->
		<div class="ap-user-lr row">
			<div class="<?php echo is_active_sidebar( 'ap-user' ) ? 'col-md-9' : 'col-md-12' ?>">
				<?php ap_user_page(); ?>
			</div>

			<?php if ( is_active_sidebar( 'ap-user' ) && is_anspress()){ ?>
				<div class="ap-user-right col-md-3">
					<?php dynamic_sidebar( 'ap-user' ); ?>
				</div>
			<?php } ?>
		</div>
		<!-- End ap-profile-lr -->
	</div>

<?php endwhile; ?>
