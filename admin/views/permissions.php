<?php
/**
 * Tools page
 *
 * @link https://anspress.io
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

$class = 'is-dismissible';

if ( isset($_POST['role_name'] ) && wp_verify_nonce( $_POST['__nonce'], 'ap_role_'.$_POST['role_name'].'_update' ) && is_super_admin( ) ) {

	$caps = isset($_POST['c'] ) ? $_POST['c'] : array();
	$caps = array_map( 'sanitize_text_field', $caps );

	ap_update_caps_for_role( $_POST['role_name'], $caps );

} elseif ( isset( $_POST['new_role'] ) && wp_verify_nonce( $_POST['__nonce'], 'ap_new_role' ) ) {
	$role_name = sanitize_text_field( $_POST['role_name'] );
	$role_slug = sanitize_title_with_dashes( $_POST['role_slug'] );

	if ( ! isset( $wp_roles->roles[ $role_slug ] ) ) {
		$role_caps = wp_unslash( $_POST['role_caps'] );
		$caps = ($role_caps == 'moderator_caps' ? ap_role_caps('moderator' ) : 	ap_role_caps('participant' ));
		add_role( $role_slug, $role_name, $caps );

		$message = sprintf( __( 'New role %s added successfully .', 'anspress-question-answer' ), $role_name );
		$class .= ' notice notice-success';
	} else {
		$message = sprintf( __( 'Its look like %s role already exists .', 'anspress-question-answer' ), $role_name );
		$class .= ' notice notice-error';
	}
}


if ( ! empty( $message ) ) {
	printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
}
?>

<div class="wrap">
	<?php do_action('ap_before_admin_page_title' ) ?>
	<h2><?php _e('Permission and role', 'anspress-question-answer' ) ?></h2>
	<div class="white-bg">
	    <table class="form-table">
	        <tbody>
	       		<tr>
					<th scope="row" valign="top">
						<label><?php _e('Add new role', 'anspress-question-answer' ); ?>:</label>					
					</th>
					<td>
						<p class="description"><?php _e('Add a new user role.', 'anspress-question-answer' );?></p>
						<br />
						<form action="" method="POST">
							<input type="text" name="role_name" value="" placeholder="<?php _e('Role name', 'anspress-question-answer' ); ?>" class="regular-text">
							<input type="text" name="role_slug" value="" placeholder="<?php _e('Role slug, without any space', 'anspress-question-answer' ); ?>" class="regular-text">
							<br />
							<br />
							<label>
								<input type="radio" name="role_caps" value="participant_caps">
								<?php _e('Basic Capabilities', 'anspress-question-answer' ); ?>
							</label>

							<label>
								<input type="radio" name="role_caps" value="moderator_caps">
								<?php _e('Moderator Capabilities', 'anspress-question-answer' ); ?>
							</label>
							<br />
							<br />
							<?php wp_nonce_field( 'ap_new_role', '__nonce' ); ?>
							<input name="new_role" type="submit" class="button button-primary" value="<?php _e('Add role', 'anspress-question-answer' ); ?>" />
						</form>
						
					</td>
				</tr>
	            <tr>
					<th scope="row" valign="top">
						<label><?php _e('AnsPress capabilities', 'anspress-question-answer' );?>:</label>
						<p class="description"><?php _e('Add AnsPress capabilities to 3rd party roles.', 'anspress-question-answer' );?></p>
					</th>
						
	                <td>
	                	<div class="ap-tools-roles">
							<label for="ap-tools-selectroles"><?php _e('Select user role', 'anspress-question-answer' ); ?></label>
							<select id="ap-tools-selectroles">
							<?php $selected = ap_sanitize_unslash( 'role_name', 'request', 'administrator' ); ?>
								<?php foreach ( $wp_roles->roles as $key => $role ) { ?>
									<option value="role_<?php echo $key; ?>" <?php selected( $selected, $key ); ?>><?php echo $role['name']; ?></option>
								<?php } ?>
							</select>

							<?php foreach ( $wp_roles->roles as $key => $role ) { ?>
								<form id="role_<?php echo $key; ?>" class="ap-tools-roleitem" style="display:none" method="POST" action="">
									<strong class="ap-tools-roletitle">
										<?php echo $role['name']; ?>
									</strong>
									<div class="ap-tools-basecaps ap-tools-ck">
										<strong><?php _e( 'Basic Capabilities', 'anspress-question-answer' ); ?><input type="checkbox" class="checkall" /></strong>
										<?php foreach ( $ap_roles->base_caps as $cap => $val ) {   ?>
											<label for="<?php echo $key.'_'.$cap; ?>">
												<input id="<?php echo $key.'_'.$cap; ?>" type="checkbox" name="c[<?php echo $cap; ?>]" <?php echo isset( $role['capabilities'][$cap] ) && $role['capabilities'][$cap] ? ' checked="checked"' : ''; ?> />
												<?php echo $cap; ?>
											</label>
										<?php } ?>
									</div>
									<div class="ap-tools-modcaps ap-tools-ck">
										<strong><?php _e( 'Moderator Capabilities', 'anspress-question-answer' ); ?><input type="checkbox" class="checkall" /></strong>
										<?php foreach ( $ap_roles->mod_caps as $cap => $val ) {   ?>
											<label for="<?php echo $key.'_'.$cap; ?>">
												<input id="<?php echo $key.'_'.$cap; ?>" type="checkbox" name="c[<?php echo $cap; ?>]" <?php echo isset( $role['capabilities'][$cap] ) && $role['capabilities'][$cap] ? ' checked="checked"' : ''; ?> />
												<?php echo $cap; ?>
											</label>
										<?php } ?>

									</div>
									<input type="hidden" name="ap_admin_form" value="role_update" />
									<input type="hidden" name="role_name" value="<?php echo $key; ?>" />
									<?php wp_nonce_field('ap_role_'.$key.'_update', '__nonce' ); ?>
									<input id="save-options" class="button button-primary" type="submit" value="Save Role" name="save">
								</form>
							<?php } ?>
						</div>
	                </td>
	            </tr>
	        </tbody>
	    </table>
    </div>
</div>

