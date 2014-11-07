<?php
/// add default labels
if(!get_terms( 'question_label', 'hide_empty=0')){
$labels = array(
	'Open' => array('description' => __('Question marked as open for discussion', 'ap'), 'slug' => 'open'),
	'Closed' => array('description' => __('Question marked as closed', 'ap'), 'slug' => 'closed'),
	'Solved' => array('description' => __('Question marked as solved', 'ap'), 'slug' => 'solved'),
	'Duplicate' => array('description' => __('Question marked as duplicate', 'ap'), 'slug' => 'duplicate'),
);
$label_c = array(
	'Open' 		=> '#70cc3f',
	'Closed' 	=> '#dd4b4b',
	'Solved' 	=> '#23a1e0',
	'Duplicate' => '#f7b022',
);

foreach($labels as $k => $label){
	$id = wp_insert_term(
	  $k, // the term 
	  'question_label', // the taxonomy
	  array(
		'description'=> $label['description'],
		'slug' => $label['slug']
	  )
	);
	
	$t_id = $id['term_id'];
	$tax_meta = get_option( "question_label_$t_id");

	$tax_meta['color'] = $label_c[$k];
	
	//save the option array
	update_option( "question_label_$t_id", $tax_meta );
}
}
if(!get_terms( 'rank', 'hide_empty=0')){
$ranks = array(
	'Grand Master' => array('slug' => 'grand-master'),
	'New Comer' => array('slug' => 'new-comer'),
	'Regular' => array('slug' => 'regular'),									
	'Trainee' => array('slug' => 'trainee'),								
);
foreach($ranks as $k => $rank){
	$id = wp_insert_term(
	  $k, // the term 
	  'rank', // the taxonomy
	  array(
		'slug' => $rank['slug']
	  )
	);
}
}
?>
<div class="wrap">
	<div class="ap-install">
	<h2>
		<a href="http://open-wp.com"><img src="<?php echo ANSPRESS_URL.'assets/open-wp-logo.png'; ?>" /></a>
		<span><?php _e('AnsPress Installation', 'ap'); ?></span>		
	</h2>
	<div class="ap-install-box">
		<ul class="ap-install-steps">
			<li class="start-install">
				<button id="start-install" data-args="<?php echo wp_create_nonce('anspress_install'); ?>" class="ap-start-installation"><?php _e('Start installation', 'ap'); ?></button>
				<p><?php _e('For your convenience, we have created this auto installer. You must complete this step for proper working of anspress.', 'ap'); ?></p>
			</li>
			<li>
				<a href="#" class="base-page"><?php _e('Check base page', 'ap'); ?></a>
				<div class="install-popup select-base-page">
					<?php wp_dropdown_pages( array('name'=> 'base_page','post_type'=> 'page') ); ?>		
					<p class="description"><?php _e('This page slug is use as base slug, if this page was selected for home page then no base slug will be added', 'ap'); ?></p>
					<p class="description"><?php _e('WARNING: All content of selected page will be removed and replaced with [anspress] shortcode.', 'ap'); ?></p>
					<button id="continue-base-install" class="ap-continue"><?php _e('OK! Continue', 'ap'); ?></button>
				</div>				
			</li>
			<li>
				<a href="#" class="base-page"><?php _e('Default Values', 'ap'); ?></a>
				<div class="install-popup select-base-page">						
					<p class="description"><?php _e('Assign default values.', 'ap'); ?></p>
					<strong><?php _e('Default label'); ?></strong>
					<select id="default-label">
						<?php
							$taxonomies = get_terms( 'question_label', 'hide_empty=0' );
								foreach($taxonomies as $cat)
										echo '<option value="'.$cat->term_id.'">'.$cat->name.'</option>';
						?>
					</select>
					<strong><?php _e('Default Rank'); ?></strong>
					<select id="default-rank">
						<?php
							$taxonomies = get_terms( 'rank', 'hide_empty=0' );
								foreach($taxonomies as $rank)
									echo '<option value="'.$rank->term_id.'">'.$rank->name.'</option>';
						?>
					</select>
					<p><?php _e('You can later edit and customize this default options', 'ap'); ?></p>
					<button id="continue-dopt-install" class="ap-continue"><?php _e('OK! Continue', 'ap'); ?></button>
				</div>
			</li>
			<li>
				<a href="#" class="base-page"><?php _e('Check data tables', 'ap'); ?></a>
				<div class="install-popup select-base-page">						
					<p class="description"><?php _e('This step will check all data tables of AnsPress.', 'ap'); ?></p>
					<button id="continue-dbcheck-install" class="ap-continue"><?php _e('OK! Continue', 'ap'); ?></button>
				</div>
			</li>
			<li>
				<a href="#" class="rewrite-rules"><?php _e('Check rewrite rules', 'ap'); ?></a>
				<div class="install-popup select-base-page">						
					<p class="description"><?php _e('This step will check rewrite rules of AnsPress.', 'ap'); ?></p>
					<button id="continue-rewrite-install" class="ap-continue"><?php _e('OK! Continue', 'ap'); ?></button>
				</div>
			</li>
			<li class="twitter-button">
				<a href="https://twitter.com/openwp" class="twitter-follow-button" data-show-count="false" data-size="small">Follow @openwp</a>
				<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
				
				<div class="doante-to-anspress">
					<strong>If you wish you can donate to AnsPress developments. In exchange we will give some hours of premium support to you.</strong>
					<a href="https://www.paypal.com/cgi-bin/webscr?business=rah12@live.com&cmd=_xclick&item_name=Donation%20to%20AnsPress%20development" target="_blank"><img src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" alt="" /></a>
				</div>				
				
				<small><?php _e('Click here to finish installation.', 'ap'); ?></small>
				<button class="ap-continue" id="ap-finish-installation"><?php _e('Finish', 'ap'); ?></button>
			</li>
		</ul>		
	</div>
	<a class="escape_install" href="<?php echo admin_url('/admin.php?page=anspress_options&escape_install=true&nonce='.wp_create_nonce('anspress_install')); ?>"><?php _e('Escape installation', 'ap'); ?></a>
	</div>
<br class="clear"></div>
</div>