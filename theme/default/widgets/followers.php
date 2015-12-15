<div id="ap-followers-widget" class="ap-followers-widget ap-widget clearfix">
	<ul class="ap-ul-inline clearfix">
		<?php while ( $followers->users() ) : $followers->the_user(); ?>
			<li>
				<a class="ap-users-avatar" href="<?php ap_user_the_link(); ?>" title="<?php ap_user_the_display_name(); ?>">
					<?php ap_user_the_avatar($avatar_size); ?>
				</a>
			</li>
		<?php endwhile;	?>		
	</ul>
	<a class="ap-widget-all-link" href="<?php echo ap_user_link(ap_get_displayed_user_id(), 'followers'); ?>"><?php _e('View all followers &rarr;', 'anspress-question-answer'); ?></a>
</div>