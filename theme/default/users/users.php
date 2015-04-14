<?php dynamic_sidebar( 'ap-top' ); ?>
<div class="row">
	<div id="ap-users" class="ap-users <?php echo is_active_sidebar( 'ap-sidebar' ) && is_anspress() ? 'col-md-8' : 'col-md-12' ?>">
		<?php ap_users_tab(); ?>

		<div class="ap-users-loop clearfix">
			<?php		
				if(ap_has_users()){

					while ( ap_users() ) : ap_the_user();
						global $users_query;
						include(ap_get_theme_location('users/loop-item.php'));
					endwhile;
				}else{
					_e('No users found');
				}
			?>
		</div>
		<?php ap_users_the_pagination(); ?>
	</div>
	<?php if ( is_active_sidebar( 'ap-sidebar' ) && is_anspress()){ ?>
		<div class="ap-question-right col-md-4">
			<div class="ap-question-info">
				<?php dynamic_sidebar( 'ap-sidebar' ); ?>
			</div>
		</div>
	<?php } ?>
</div>


