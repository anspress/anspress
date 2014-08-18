<?php 
$addons = ap_read_addons();
?>
<?php add_thickbox(); ?>
<div class="wrap">
	<h2>
		<?php _e('AnsPress Addons'); ?> <span class="addon-count"><?php echo ap_addon_counts(); ?></span>
	</h2>

<div class="theme-browser rendered">
<div class="themes">

	<?php foreach($addons as $k => $addon): ?>
		
		<div class="theme" tabindex="0">
			<div class="theme-screenshot">
				<div class="ap-addon-status"<?php echo !$addon['active'] ? ' style="display:none"' : ''; ?>><?php _e('Active', 'ap'); ?></div>
				
				<img alt="" src="<?php echo ANSPRESS_ADDON_URL.$addon['folder'].'/screenshot.png'; ?>">
			</div>

			<a class="more-details thickbox" href="#TB_inline?width=600&height=550&inlineId=<?php echo str_replace(' ', '_', $k); ?>"><?php _e('Addon Details'); ?></a>
			<div class="theme-author"><?php echo $addon['author']; ?></div>


			<h3 class="theme-name"><?php echo $addon['name']; ?></h3>
			<div class="theme-actions">
				<?php if($addon['active']): ?>
					<a data-action="ap-toggle-addon" data-args="<?php echo $k.'-'.wp_create_nonce('toggle_addon').'-deactivate'; ?>" href="#" class="button button-primary activate"><?php _e('Deactivate', 'ap'); ?></a>
				<?php else: ?>
					<a data-action="ap-toggle-addon" data-args="<?php echo $k.'-'.wp_create_nonce('toggle_addon').'-activate'; ?>" href="#" class="button button-primary activate"><?php _e('Activate', 'ap'); ?></a>
				<?php endif; ?>
			</div>
		</div>
		<div id="<?php echo str_replace(' ', '_', $k); ?>" style="display:none;" class="addon-modal">
			<div class="addon-screenshot">
				<img alt="" src="<?php echo ANSPRESS_ADDON_URL.$addon['folder'].'/screenshot.png'; ?>">
			</div>
			 <p class="addon-description">
				  <?php echo $addon['description']; ?>
			 </p>
		</div>
	<?php endforeach; ?>
</div>

<br class="clear"></div>
<div class="theme-overlay"></div>

</div>