<div id="ap-admin-dashboard" class="wrap">
	<h2>AnsPress Dashboard</h2>
	<div class="doante-to-anspress">
		<h3>Help us keep AnsPress open source, free and full functional without any limitations</h3>
		<a href="https://www.paypal.com/cgi-bin/webscr?business=rah12@live.com&cmd=_xclick&item_name=Donation%20to%20AnsPress%20development" target="_blank"><img src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" alt="" /></a>
	</div>
	<div id="welcome-panel" class="welcome-panel">		
		<div class="welcome-panel-content">
			<h3>Welcome to AnsPress!</h3>
			<p class="about-description">We've assembled some links to get you started:</p>
			<div class="welcome-panel-column-container">
			<div class="welcome-panel-column">
				<h4>Need Help ?</h4>
				<a class="button button-primary button-hero load-customize hide-if-no-customize" href="https://rahularyan.com/support">Ask for help</a>
			</div>
			<div class="welcome-panel-column">
				<h4>Next Steps</h4>
				<ul>
					<li><a href="<?php echo admin_url('post-new.php?post_type=question');?>" class="welcome-icon welcome-write-blog">Write your first question</a></li>
					<li><a href="<?php echo admin_url('edit-tags.php?taxonomy=question_category');?>" class="welcome-icon welcome-add-page">New Categories</a></li>
					<li><a href="<?php echo get_permalink(ap_opt('base_page'));?>" class="welcome-icon welcome-view-site">View Front page</a></li>
				</ul>
			</div>
			<div class="welcome-panel-column welcome-panel-last">
				<h4>More Actions</h4>
				<ul>
					<li><div class="welcome-icon welcome-widgets-menus">Manage <a href="<?php echo admin_url('nav-menus.php');?>">menus</a></div></li>
					<li><a href="http://localhost/anspress/wp-admin/options-discussion.php" class="welcome-icon welcome-comments">Turn comments on or off</a></li>
				</ul>
			</div>
			</div>
		</div>
	</div>
</div>