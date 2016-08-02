<div id="ap-user-widget" class="ap-user-widget ap-widget clearfix">
	<div class="ap-user-cover clearfix">
		<div class="ap-user-cover-img" style="background-image:url(<?php echo ap_get_cover_src(false, true); ?>)" data-view="user_cover_<?php ap_displayed_user_id(); ?>"></div>
	</div>
	<div class="ap-user-head clearfix">
		<div class="ap-user-avatar">
			<?php ap_user_the_avatar(60); ?>
		</div>
		<a class="ap-user-name" href="<?php ap_user_the_link(); ?>"><?php ap_user_the_display_name(); ?></a>
		<?php
			if(!ap_opt('disable_reputation')){
				echo '<span class="ap-user-reputation">';
				printf(
					/* translators: %s: user reputation */
					__('%s Rep.', 'anspress-question-answer'),
					ap_user_get_the_reputation()
				);
				echo '</span>';
			}
		?>
	</div>
	<?php 
		$menus = ap_get_user_menu(get_current_user_id());
		$active_user_page   = get_query_var('user_page');
	    $active_user_page   = $active_user_page ? $active_user_page : 'about';
	    $item_output = '<a id="ap-user-menu-anchor" href="#">'.get_avatar(get_current_user_id(), 20).ap_user_display_name(get_current_user_id()).ap_icon('chevron-down', true).'</a>';
			
		echo '<ul class="ap-user-widget-links">';
		
		foreach($menus as $m){
			
			$class = !empty($m['class']) ? ' '.$m['class'] : '';

            echo '<li'.($active_user_page == $m['slug'] ? ' class="active"' : '').'><a href="'.$m['link'].'" class="ap-user-link-'.$m['slug'].$class.'">'.$m['title'].'</a></li>';
        }

		echo '</ul>';
	?>
</div>