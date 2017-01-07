<?php
/**
 * Template for user reputations item.
 *
 * Render reputation item in authors page.
 *
 * @author  Rahul Aryan <support@anspress.io>
 * @link    https://anspress.io/
 * @since   4.0.0
 * @package WordPress/AnsPress
 */

?>
<table class="ap-reputations">
	<tbody>
		<?php while ( $reputations->have() ) : $reputations->the_reputation(); ?>
			<tr class="ap-reputation-item">
				<td class="col-icon"><i class="<?php $reputations->the_icon(); ?> <?php $reputations->the_event(); ?>"></i></td>
				<td class="col-event ap-reputation-event">
					<div class="ap-reputation-activity"><?php $reputations->the_activity(); ?></div>
					<?php $reputations->the_ref_content(); ?>
				</td>
				<td class="col-date ap-reputation-date"><?php $reputations->the_date(); ?></td>
				<td class="col-points ap-reputation-points"><span><?php $reputations->the_points(); ?></span></td>
			</tr>
		<?php endwhile; ?>
	</tbody>
</table>

<a href="#" ap-loadmore="<?php echo esc_js( wp_json_encode( [ 'ap_ajax_action' => 'load_more_reputation', '__nonce' => wp_create_nonce( 'load_more_reputation' ), 'current' => 1 ] ) ); ?>" class="ap-loadmore ap-btn" ><?php esc_attr_e( 'Load More', 'anspress-question-answer' ); ?></a>
