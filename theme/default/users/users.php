<div class="row">
	<div id="ap-users" class="ap-users <?php echo is_active_sidebar( 'ap-sidebar' ) && is_anspress() ? 'col-md-9' : 'col-md-12' ?>">
		<div class="ap-list-head clearfix">
			<form id="ap-search-form" class="ap-search-form pull-left" action="<?php echo ap_get_link_to('search'); ?>?type=user">
			    <input name="ap_s" type="text" class="ap-form-control" placeholder="<?php _e('Search users...', 'anspress-question-answer'); ?>" value="<?php echo sanitize_text_field( get_query_var('ap_s') ); ?>" />
			    <input name="type" type="hidden" value="user" />
			</form>
			<?php ap_users_tab(); ?>
		</div>

		<div class="ap-users-loop clearfix">
			<?php
				while ( ap_users() ) : ap_the_user();
					include(ap_get_theme_location('users/loop-item.php'));
				endwhile;
			?>
		</div>
		<?php ap_users_the_pagination(); ?>
	</div>
	<?php if ( is_active_sidebar( 'ap-sidebar' ) && is_anspress()){ ?>
		<div class="ap-question-right col-md-3">
			<div class="ap-question-info">
				<?php dynamic_sidebar( 'ap-sidebar' ); ?>
			</div>
		</div>
	<?php } ?>
</div>


