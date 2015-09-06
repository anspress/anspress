<?php
/**
 * Tools page
 *
 * @link http://anspress.io
 * @since 2.0.0-alpha2
 * @author Rahul Aryan <support@anspress.io>
 * @package AnsPress
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

global $wp_roles;
$ap_roles = new AP_Roles;
?>
<div class="ap-tools clearfix">
	<div class="ap-tools-roles">
		<h2><?php _e( 'AnsPress capabilities', 'ap' ); ?></h3>
		<span><?php _e( 'Add AnsPress capabilities to 3rd party roles', 'ap' ); ?></span>
		<br />
		<br />
		<label for="ap-tools-selectroles"><?php _e('Select user role', 'ap'); ?></label>
		<select id="ap-tools-selectroles">
			<?php foreach ( $wp_roles->roles as $key => $role ) : ?>
				<option value="role_<?php echo $key; ?>" <?php selected( sanitize_text_field( $_POST['role_name'] ), $key ); ?>><?php echo $role['name']; ?></option>
			<?php endforeach; ?>
		</select>

		<?php foreach ( $wp_roles->roles as $key => $role ) : ?>
			<form id="role_<?php echo $key; ?>" class="ap-tools-roleitem" style="display:none" method="POST" action="">
				<strong class="ap-tools-roletitle">
					<?php echo $role['name']; ?>
				</strong>
				<div class="ap-tools-basecaps ap-tools-ck">
					<strong><?php _e( 'Basic Capabilities', 'ap' ); ?><input type="checkbox" class="checkall" /></strong>
					<?php foreach ( $ap_roles->base_caps as $cap => $val ) :   ?>
						<label for="<?php echo $key.'_'.$cap; ?>">
							<input id="<?php echo $key.'_'.$cap; ?>" type="checkbox" name="c[<?php echo $cap; ?>]" <?php echo isset( $role['capabilities'][$cap] ) && $role['capabilities'][$cap] ? ' checked="checked"' : ''; ?> />
							<?php echo $cap; ?>
						</label>
					<?php endforeach; ?>
				</div>
				<div class="ap-tools-modcaps ap-tools-ck">
					<strong><?php _e( 'Moderator Capabilities', 'ap' ); ?><input type="checkbox" class="checkall" /></strong>
					<?php foreach ( $ap_roles->mod_caps as $cap => $val ) :   ?>
						<label for="<?php echo $key.'_'.$cap; ?>">
							<input id="<?php echo $key.'_'.$cap; ?>" type="checkbox" name="c[<?php echo $cap; ?>]" <?php echo isset( $role['capabilities'][$cap] ) && $role['capabilities'][$cap] ? ' checked="checked"' : ''; ?> />
							<?php echo $cap; ?>
						</label>
					<?php endforeach; ?>

				</div>
				<input type="hidden" name="ap_admin_form" value="role_update" />
				<input type="hidden" name="role_name" value="<?php echo $key; ?>" />
				<?php wp_nonce_field('ap_role_'.$key.'_update', '__nonce' ); ?>
				<input id="save-post" class="button button-primary" type="submit" value="Save Role" name="save">
			</form>
		<?php endforeach; ?>
	</div>
</div>
