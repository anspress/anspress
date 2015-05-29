<?php
/**
 * Reputation loop item template
 * 
 */
?>

<?php //Do not change id attribute ?>
<div id="ap-reputation-<?php ap_reputation_get_the_id(); ?>" class="ap-reputation-item <?php ap_reputation_get_the_class(); ?>">
	<span class="point"><?php ap_reputation_get_the_reputation() ?></span>
	<div class="no-overflow">
		<span class="time"><?php ap_reputation_get_the_date(); ?></span>
		<span class="info"><?php ap_reputation_get_the_info(); ?></span>							
	</div>
</div>
