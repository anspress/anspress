<?php
/**
 * Display AnsPress user page
 *
 * @link http://wp3.in
 * @since 2.0.1
 *
 * @package AnsPress
 */
?>
<h3 class="ap-user-page-title clearfix">
	<?php echo ap_page_title() ?>
</h3>
<div class="ap-user-rep">

	<div class="ap-about-rep clearfix">
		<div class="ap-pull-left">
			<span class="ap-about-rep-label"><?php _e('Total', 'ap'); ?></span>
			<span class="ap-about-rep-count"><?php ap_user_the_reputation(); ?></span>
		</div>
		<div class="ap-about-rep-chart">
			<span data-action="ap_chart" data-type="bar" data-peity='{"fill" : ["#8fc77e"], "height": 45, "width": "100%"}'><?php echo ap_user_get_28_days_reputation(); ?></span>		
		</div>
		<div class="ap-user-rep">
			<?php
				if(ap_has_reputations()){
					while ( ap_reputations() ) : ap_the_reputation();
						ap_get_template_part('user/reputation-content');
					endwhile;
					ap_pagination(false, anspress()->reputations->total_pages);
				}else{
					_e('No reputation earned yet.', 'ap');
				}

			?>
		</div>
	</div>
</div>
