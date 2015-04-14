<?php dynamic_sidebar( 'ap-top' ); ?>
<div class="row">
	<div id="ap-users" class="ap-users <?php echo is_active_sidebar( 'ap-sidebar' ) && is_anspress() ? 'col-md-8' : 'col-md-12' ?>">
		<?php
		ap_users_tab();
		
		if(ap_has_users()){

			while ( ap_users() ) : ap_the_user();
				global $users_query;
				include(ap_get_theme_location('users/loop-item.php'));
			endwhile;

			ap_users_the_pagination();

		}else{
			_e('No users found');
		}

		/*if ( ! empty( anspress()->users ) ) {
			foreach ( anspress()->users as $f ) {
				//$data = $f->data;
				//$current_user_meta = array_map( function( $a ){ return $a[0]; }, get_user_meta($f->ID));
				
			}
		} else {
			include(ap_get_theme_location('content-none.php'));
		}

		if ( ! ( anspress()->users ) )  {
			ap_pagi($base, $total_pages, $paged);
		}*/


	
		?>
	</div>
	<?php if ( is_active_sidebar( 'ap-sidebar' ) && is_anspress()){ ?>
		<div class="ap-question-right col-md-4">
			<div class="ap-question-info">
				<?php dynamic_sidebar( 'ap-sidebar' ); ?>
			</div>
		</div>
	<?php } ?>
</div>


