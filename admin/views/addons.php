<?php
/**
 * Tools page
 *
 * @link https://anspress.io
 * @since 4.0
 * @author Rahul Aryan <support@anspress.io>
 * @package WordPress/AnsPress
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
global $wpdb;

?>

<div class="wrap">
	<?php do_action( 'ap_before_admin_page_title' ) ?>

	<form class="ap-addons" method="POST">
		<?php foreach ( (array) ap_get_addons() as $file => $data ) { ?>
			<div class="ap-addon">
				<div class="ap-addon-cb">
					<input type="checkbox" id="<?php echo esc_attr( $file ); ?>" value="<?php echo esc_attr( $file ); ?>" name="addon[]" <?php checked( ap_is_addon_active( $file ) , true ); ?>>
					<span class="ap-addon-toggle"></span>
				</div>
				<div class="no-overflow">
					<strong class="ap-addon-name"><?php echo esc_attr( $data['name'] ); ?>
					<?php echo $data['pro'] ? '<i>pro</i>' : ''; ?></strong>
					<p class="description"><?php echo esc_attr( $data['description'] ); ?></p>
				</div>
			</div>
		<?php } ?>
		<input name="action" type="hidden" value="ap_toggle_addons" />
		<input name="__nonce" type="hidden" value="<?php echo wp_create_nonce( 'ap-toggle-addons' ); ?>" />
	</form>

</div>

<script type="text/javascript">
	function submitAddonForm(data, el){
		jQuery.ajax({
			url: ajaxurl,
			data: data,
			success: function(data){
				window.location.reload();
			}
		});
	}

	jQuery(document).ready(function($){
		$('input[name="addon[]"]').change(function(){
			var data = $(this).closest('form').serialize();
			submitAddonForm(data, this);
		});
	});
</script>

