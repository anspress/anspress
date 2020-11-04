<?php
/**
 * Reputation events.
 *
 * @link    https://anspress.net
 * @since   4.0
 * @author  Rahul Aryan <rah12@live.com>
 * @package AnsPress
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

global $wpdb;
$i = 1;
?>

<form id="reputation_events" method="POST">
	<table class="ap-events">
		<tbody>
			<?php foreach ( (array) ap_get_reputation_events() as $slug => $event ) { ?>
				<tr class="ap-event">
					<td class="col-id"><span><?php echo esc_attr( $i ); ?></span></td>
					<td class="col-label"><?php echo esc_attr( $event['label'] ); ?></td>
					<td class="col-description"><?php echo esc_attr( $event['description'] ); ?></td>
					<td class="col-points"><input type="number" value="<?php echo esc_attr( $event['points'] ); ?>" name="events[<?php echo esc_attr( $slug ); ?>]"/></td>
				</tr>
				<?php $i++; ?>
			<?php } ?>
		</tbody>
	</table>
	<button class="button button-primary"><?php esc_attr_e( 'Save Events Points', 'anspress-question-answer' ); ?></button>
	<input name="action" type="hidden" value="ap_save_events" />
	<input name="__nonce" type="hidden" value="<?php echo wp_create_nonce( 'ap-save-events' ); ?>" />
</form>

<script type="text/javascript">
	jQuery(document).ready(function($){
		$('#reputation_events').on('submit', function(){
			$.ajax({
				url: ajaxurl,
				data: $(this).serialize(),
				success: function(data){
					if('' !== data){
						$('.postbox.events').before(data);
					}
				}
			})
			return false;
		});
	});
</script>

