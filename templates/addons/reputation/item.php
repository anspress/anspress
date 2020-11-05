<?php
/**
 * Template for user reputation item.
 *
 * Render reputation item in authors page.
 *
 * @author  Rahul Aryan <rah12@live.com>
 * @link    https://anspress.net/
 * @since   4.0.0
 * @package AnsPress
 */

?>

<tr class="ap-reputation-item">
	<td class="col-icon"><i class="<?php $reputations->the_icon(); ?> <?php $reputations->the_event(); ?>"></i></td>
	<td class="col-event ap-reputation-event">
		<div class="ap-reputation-activity"><?php $reputations->the_activity(); ?></div>
		<?php $reputations->the_ref_content(); ?>
	</td>
	<td class="col-date ap-reputation-date"><?php $reputations->the_date(); ?></td>
	<td class="col-points ap-reputation-points"><span><?php $reputations->the_points(); ?></span></td>
</tr>

