<?php
/**
 * Tools page
 *
 * @link https://anspress.net
 * @since 2.0.0
 * @author Rahul Aryan <rah12@live.com>
 * @package AnsPress
 * @since 4.2.0 Fixed: CS bugs.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

global $wp_roles;
$ap_roles = new AP_Roles();

$class = 'is-dismissible';

if (
	ap_sanitize_unslash( 'role_name', 'p' ) &&
	anspress_verify_nonce( 'ap_role_' . ap_sanitize_unslash( 'role_name', 'p' ) . '_update' ) &&
	is_super_admin()
	) {
	$caps = ap_sanitize_unslash( 'c', 'p' ) ? ap_sanitize_unslash( 'c', 'p' ) : array();
	$caps = array_map( 'sanitize_text_field', $caps );

	ap_update_caps_for_role( ap_sanitize_unslash( 'role_name', 'p' ), $caps );
} elseif ( ap_sanitize_unslash( 'new_role', 'p' ) && anspress_verify_nonce( 'ap_new_role' ) ) {
	$role_name = ap_sanitize_unslash( 'role_name', 'p' );
	$role_slug = sanitize_title_with_dashes( ap_sanitize_unslash( 'role_slug', 'p' ) );

	if ( ! isset( $wp_roles->roles[ $role_slug ] ) ) {
		$role_caps = ap_sanitize_unslash( 'role_caps', 'p' );
		$caps      = ( 'moderator_caps' === $role_caps ? ap_role_caps( 'moderator' ) : ap_role_caps( 'participant' ) );
		add_role( $role_slug, $role_name, $caps );

		// translators: %s contain role name.
		$message = sprintf( esc_attr__( 'New role %s added successfully .', 'anspress-question-answer' ), $role_name );
		$class  .= ' notice notice-success';
	} else {
		// translators: %s contain role name.
		$message = sprintf( esc_attr__( 'Its look like %s role already exists .', 'anspress-question-answer' ), $role_name );
		$class  .= ' notice notice-error';
	}
}


if ( ! empty( $message ) ) {
	echo wp_kses_post( sprintf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ) );
}
?>

<div class="wrap">
	<div class="white-bg">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" valign="top">
						<label><?php esc_attr_e( 'Add new role', 'anspress-question-answer' ); ?>:</label>
					</th>
					<td>
						<p class="description"><?php esc_attr_e( 'Add a new user role.', 'anspress-question-answer' ); ?></p>
						<br />
						<form action="" method="POST">
							<input type="text" name="role_name" value="" placeholder="<?php esc_attr_e( 'Role name', 'anspress-question-answer' ); ?>" class="regular-text">
							<input type="text" name="role_slug" value="" placeholder="<?php esc_attr_e( 'Role slug, without any space', 'anspress-question-answer' ); ?>" class="regular-text">
							<br />
							<br />
							<label>
								<input type="radio" name="role_caps" value="participant_caps">
								<?php esc_attr_e( 'Basic Capabilities', 'anspress-question-answer' ); ?>
							</label>

							<label>
								<input type="radio" name="role_caps" value="moderator_caps">
								<?php esc_attr_e( 'Moderator Capabilities', 'anspress-question-answer' ); ?>
							</label>
							<br />
							<br />
							<?php wp_nonce_field( 'ap_new_role', '__nonce' ); ?>
							<input name="new_role" type="submit" class="button button-primary" value="<?php esc_attr_e( 'Add role', 'anspress-question-answer' ); ?>" />
						</form>

					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<label><?php esc_attr_e( 'AnsPress capabilities', 'anspress-question-answer' ); ?>:</label>
						<p class="description"><?php esc_attr_e( 'Add AnsPress capabilities to 3rd party roles.', 'anspress-question-answer' ); ?></p>
					</th>

					<td>
						<div class="ap-tools-roles">
							<label for="ap-tools-selectroles">
								<?php esc_attr_e( 'Select user role', 'anspress-question-answer' ); ?>
							</label>

							<select id="ap-tools-selectroles">
								<?php $selected = ap_sanitize_unslash( 'role_name', 'request', 'administrator' ); ?>
								<?php foreach ( $wp_roles->roles as $key => $role ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
									<option value="role_<?php echo esc_attr( $key ); ?>" <?php selected( $selected, $key ); ?>>
										<?php echo esc_attr( $role['name'] ); ?>
									</option>
								<?php } ?>
							</select>

							<?php foreach ( $wp_roles->roles as $key => $role ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
								<form id="role_<?php echo esc_attr( $key ); ?>" class="ap-tools-roleitem" style="display:none" method="POST" action="">
									<strong class="ap-tools-roletitle">
										<?php echo esc_attr( $role['name'] ); ?>
									</strong>
									<div class="ap-tools-basecaps ap-tools-ck">
										<strong><?php esc_attr_e( 'Basic Capabilities', 'anspress-question-answer' ); ?><input type="checkbox" class="checkall" /></strong>

										<?php foreach ( $ap_roles->base_caps as $cap => $val ) { ?>
											<label for="<?php echo esc_attr( $key . '_' . $cap ); ?>">
												<input id="<?php echo esc_attr( $key . '_' . $cap ); ?>" type="checkbox" name="c[<?php echo esc_attr( $cap ); ?>]" <?php echo isset( $role['capabilities'][ $cap ] ) && $role['capabilities'][ $cap ] ? ' checked="checked"' : ''; ?> />
												<?php echo esc_attr( $cap ); ?>
											</label>
										<?php } ?>
									</div>
									<div class="ap-tools-modcaps ap-tools-ck">
										<strong><?php esc_attr_e( 'Moderator Capabilities', 'anspress-question-answer' ); ?><input type="checkbox" class="checkall" /></strong>

										<?php foreach ( $ap_roles->mod_caps as $cap => $val ) { ?>
											<label for="<?php echo esc_attr( $key . '_' . $cap ); ?>">
												<input id="<?php echo esc_attr( $key . '_' . $cap ); ?>" type="checkbox" name="c[<?php echo esc_attr( $cap ); ?>]" <?php echo ( isset( $role['capabilities'][ $cap ] ) && $role['capabilities'][ $cap ] ? ' checked="checked"' : '' ); ?> />
												<?php echo esc_attr( $cap ); ?>
											</label>
										<?php } ?>

									</div>

									<input type="hidden" name="ap_admin_form" value="role_update" />
									<input type="hidden" name="role_name" value="<?php echo esc_attr( $key ); ?>" />
									<?php wp_nonce_field( 'ap_role_' . $key . '_update', '__nonce' ); ?>
									<input id="save-options" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save Role', 'anspress-question-answer' ); ?>" name="save">
								</form>
							<?php } ?>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
