<div class="wrap ap-install">
	<h2>
		<a href="http://open-wp.com"><img src="<?php echo ANSPRESS_URL.'assets/open-wp-logo.png'; ?>" /></a>
		<span><?php _e('AnsPress Installation', 'ap'); ?></span>
		<p><?php _e('You must complete this install to proceed.', 'ap'); ?></p>
	</h2>
	<div class="ap-install-box">
		<div class="ap-install-indi"><span></span></div>
		<button id="start-install" data-args="<?php echo wp_create_nonce('anspress_install'); ?>" class="ap-start-installation"><?php _e('Start installation', 'ap'); ?></button>
		<ul class="ap-install-steps">
			<li>				
				<div class="install-popup select-base-page" style="display:none">
					<?php wp_dropdown_pages( array('name'=> 'base_page','post_type'=> 'page') ); ?>		
					<p class="description"><?php _e('This page slug is use as base slug, if this page was selected for home page then no base slug will be added', 'ap'); ?></p>
					<p class="description"><?php _e('WARNING: All content of selected page will be removed and replaced with [anspress] shortcode.', 'ap'); ?></p>
					<button id="continue-base-install" class="ap-continue"><?php _e('OK! Continue', 'ap'); ?></button>
				</div>
				<a href="#" class="base-page"><?php _e('Check base page', 'ap'); ?></a>
			</li>
			<li>
				<a href="#" class="data-table"><?php _e('Check data tables', 'ap'); ?></a>
			</li>
			<li>
				<a href="#" class="rewrite-rules"><?php _e('Check rewrite rules', 'ap'); ?></a>
			</li>
			<li class="twitter-button" style="display:none">
				<a href="https://twitter.com/openwp" class="twitter-follow-button" data-show-count="false" data-size="small">Follow @openwp</a>
				<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
				<small><?php _e('Follow us to finish installation.', 'ap'); ?></small>
				
				<button class="ap-continue"><?php _e('Finish', 'ap'); ?></button>
			</li>
		</ul>
		<a class="escape_install" href="<?php echo admin_url('/admin.php?page=anspress_options&escape_install=true&nonce='.wp_create_nonce('anspress_install')); ?>"><?php _e('Escape installation', 'ap'); ?></a>
	</div>

<br class="clear"></div>
</div>