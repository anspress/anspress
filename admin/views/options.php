<?php
/**
 * AnsPress options page.
 *
 * @link       https://anspress.net
 * @author     Rahul Aryan <rah12@live.com>
 * @package    AnsPress
 * @subpackage Admin Pages
 * @since      4.1.0
 * @since      4.2.0 Changed title of page from to `AnsPress Settings`.
 * @since      4.2.0 Fixed: CS bugs. Added: new settings "Features".
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Check if user have proper rights.
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_attr__( 'Trying to cheat, huh!', 'anspress-question-answer' ) );
}

/**
 * Action triggered before outputting AnsPress options page.
 *
 * @since 4.1.0
 */
do_action( 'ap_before_options_page' );

$features_groups = array(
	'toggle_features' => array(
		'label'    => __( 'Toggle Features', 'anspress-question-answer' ),
		'template' => 'toggle-features.php',
		'info'     => __( 'Some features have additional settings which will be visible in the settings sidebar after they are enabled.', 'anspress-question-answer' ),
	),
);

$features_groups = apply_filters( 'ap_settings_menu_features_groups', $features_groups );

$all_options = array(
	'features'      => array(
		'label'  => __( '⭐ Features', 'anspress-question-answer' ),
		'groups' => $features_groups,
	),
	'general'       => array(
		'label'  => __( '⚙ General', 'anspress-question-answer' ),
		'groups' => array(
			'pages'      => array(
				'label' => __( 'Pages', 'anspress-question-answer' ),
			),
			'permalinks' => array(
				'label' => __( 'Permalinks', 'anspress-question-answer' ),
			),
			'layout'     => array(
				'label' => __( 'Layout', 'anspress-question-answer' ),
			),
		),
	),

	'postscomments' => array(
		'label' => __( '📃 Posts & Comments', 'anspress-question-answer' ),
	),
	'user'          => array(
		'label'  => __( '👨‍💼 User', 'anspress-question-answer' ),
		'groups' => array(
			'activity' => array(
				'label' => __( 'Activity', 'anspress-question-answer' ),
			),
		),
	),
	'uac'           => array(
		'label'  => __( '🔑 User Access Control', 'anspress-question-answer' ),
		'groups' => array(
			'reading' => array(
				'label' => __( 'Reading Permissions', 'anspress-question-answer' ),
			),
			'posting' => array(
				'label' => __( 'Posting Permissions', 'anspress-question-answer' ),
			),
			'other'   => array(
				'label' => __( 'Other Permissions', 'anspress-question-answer' ),
			),
			'roles'   => array(
				'label'    => __( 'Role Editor', 'anspress-question-answer' ),
				'template' => 'roles.php',
			),
		),
	),
	'tools'         => array(
		'label'  => __( '🔨 Tools', 'anspress-question-answer' ),
		'groups' => array(
			're-count'  => array(
				'label'    => __( 'Re-count', 'anspress-question-answer' ),
				'template' => 'recount.php',
			),
			'uninstall' => array(
				'label'    => __( 'Uninstall', 'anspress-question-answer' ),
				'template' => 'uninstall.php',
			),
		),
	),
);

$all_options = apply_filters( 'ap_all_options', $all_options );

/**
 * Action used to register AnsPress options.
 *
 * @since 4.1.0
 * @since Fixed: rewrite rules  not getting flushed on changing permalinks.
 */
do_action( 'ap_register_options' );

$form_name = ap_sanitize_unslash( 'ap_form_name', 'r' );
$updated   = false;

// Process submit form.
if ( ! empty( $form_name ) && anspress()->get_form( $form_name )->is_submitted() ) {
	$form = anspress()->get_form( $form_name );

	if ( ! $form->have_errors() ) {
		$values  = $form->get_values();
		$options = get_option( 'anspress_opt', array() );

		foreach ( $values as $key => $opt ) {
			// Modify the max_upload_size options data to not
			// exceed the value set in php.ini config.
			if ( 'max_upload_size' === $key ) {
				$max_upload = wp_max_upload_size();
				if ( $opt['value'] > $max_upload ) {
					$opt['value'] = (int) $max_upload;
				} else {
					$opt['value'] = (int) $opt['value'];
				}
			}
			$options[ $key ] = $opt['value'];
		}

		update_option( 'anspress_opt', $options );
		wp_cache_delete( 'anspress_opt', 'ap' );
		wp_cache_delete( 'anspress_opt', 'ap' );

		// Flush rewrite rules.
		if ( 'form_options_general_pages' === $form_name || 'form_options_general_permalinks' === $form_name ) {
			$main_pages = array_keys( ap_main_pages() );

			foreach ( $main_pages as $slug ) {
				if ( isset( $values[ $slug ] ) ) {
					$_post = get_post( $values[ $slug ]['value'] );

					// Proceed only if there is post available.
					if ( $_post ) {
						ap_opt( $slug . '_id', $_post->post_name );
					}
				}
			}

			ap_opt( 'ap_flush', 'true' );
			flush_rewrite_rules();
		}

		$updated = true;
	}
}

?>

<div id="anspress" class="wrap">
	<h2 class="admin-title">
		<?php esc_html_e( 'AnsPress Settings', 'anspress-question-answer' ); ?>
		<div class="social-links clearfix">
			<a href="https://github.com/anspress/anspress" target="_blank">GitHub</a>
			<a href="https://wordpress.org/plugins/anspress-question-answer/" target="_blank">WordPress.org</a>
			<a href="https://twitter.com/anspress_net" target="_blank">@anspress_net</a>
			<a href="https://www.facebook.com/anspress.io" target="_blank">Facebook</a>
		</div>
	</h2>
	<div class="clear"></div>

	<div class="ap-optionpage-wrap no-overflow">
		<div class="ap-wrap">
			<?php
				$active_tab = ap_sanitize_unslash( 'active_tab', 'r', 'general' );
				$form       = ap_sanitize_unslash( 'ap_form_name', 'r' );
				$action_url = admin_url( 'admin.php?page=anspress_options&active_tab=' . $active_tab );
			?>

			<div class="anspress-options">
				<div class="anspress-options-tab clearfix">
					<?php
					$active_tab = ap_sanitize_unslash( 'active_tab', 'r', 'general' );

					foreach ( $all_options as $key => $args ) {
						$tab_url = admin_url( 'admin.php?page=anspress_options' ) . '&active_tab=' . esc_attr( $key );

						echo '<div class="anspress-options-menu' . ( $key === $active_tab ? ' anspress-options-menu-active' : '' ) . '">';
						echo '<a href="' . esc_url( $tab_url ) . '" class="anspress-options-menu-' . esc_attr( $key ) . '">' . esc_html( $args['label'] ) . '</a>';

						if ( ! empty( $args['groups'] ) && count( $args['groups'] ) > 1 ) {
							echo '<div class="anspress-options-menu-subs">';
							foreach ( $args['groups'] as $groupkey => $sub_args ) {
								echo '<a href="' . esc_url( $tab_url . '#' . esc_attr( $key . '-' . $groupkey ) ) . '">' . esc_attr( $sub_args['label'] ) . '</a>';
							}
							echo '</div>';
						}

						echo '</div>';

						if ( isset( $args['sep'] ) && $args['sep'] ) {
							echo '<div class="anspress-options-menu-sep"></div>';
						}
					}

					/**
					 * Action triggered right after AnsPress options tab links.
					 * Can be used to show custom tab links.
					 *
					 * @since 4.1.0
					 */
					do_action( 'ap_options_tab_links' );
					?>
				</div>
				<div class="anspress-options-body">
					<div class="ap-group-options">

						<?php if ( isset( $all_options[ $active_tab ] ) ) : ?>

							<?php if ( true === $updated ) : ?>
								<div class="notice notice-success is-dismissible">
									<p><?php esc_html_e( 'AnsPress option updated successfully!', 'anspress-question-answer' ); ?></p>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $all_options[ $active_tab ]['groups'] ) ) : ?>

								<?php foreach ( $all_options[ $active_tab ]['groups'] as $groupkey => $args ) : ?>
									<div class="postbox">
										<h3 id="<?php echo esc_attr( $active_tab . '-' . $groupkey ); ?>"><?php echo esc_attr( $args['label'] ); ?></h3>
										<div class="inside anspress-options-inside-<?php echo esc_attr( $groupkey ); ?>">
											<?php
											if ( ! empty( $args['info'] ) ) {
												echo '<p class="anspress-options-info">💡 ' . wp_kses_post( $args['info'] ) . '</p>';
											}

											if ( isset( $args['template'] ) ) {
												include ANSPRESS_DIR . '/admin/views/' . $args['template'];
											} else {
												anspress()->get_form( 'options_' . $active_tab . '_' . $groupkey )->generate(
													array(
														'form_action' => $action_url . '#form_options_' . $active_tab . '_' . $groupkey,
														'ajax_submit' => false,
													)
												);
											}
											?>
										</div>
									</div>
								<?php endforeach; ?>
							<?php else : ?>
								<?php $active_option = $all_options[ $active_tab ]; ?>
								<div class="postbox">
									<h3 id="pages-options"><?php echo esc_attr( $active_option['label'] ); ?></h3>
									<div class="inside">
										<?php
										if ( isset( $active_option['template'] ) ) {
											include ANSPRESS_DIR . '/admin/views/' . $active_option['template'];
										} else {
											anspress()->get_form( 'options_' . $active_tab )->generate(
												array(
													'form_action' => $action_url . '#form_options_' . $active_tab,
													'ajax_submit' => false,
												)
											);
										}
										?>
									</div>
								</div>

							<?php endif; ?>

						<?php endif; ?>

						<?php
							/**
							 * Action triggered in AnsPress options page content.
							 * This action can be used to show custom options fields.
							 *
							 * @since 4.1.0
							 */
							do_action( 'ap_option_page_content' );
						?>
					</div>
				</div>
				<div class="anspress-options-right">
					<?php if ( 'features' !== $active_tab ) : ?>
						<div class="ap-features-info">
							<div>
								💡
								<p><?php esc_attr_e( 'Functions such as email notifications, categories, tags and many more are disabled by default. Please carefully check and enable them if needed.', 'anspress-question-answer' ); ?></p>
							</div>
							<div>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=anspress_options&active_tab=features' ) ); ?>" class="button"><?php esc_attr_e( 'Enable features', 'anspress-question-answer' ); ?></a>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
/**
 * Action triggered after outputting AnsPress options page.
 *
 * @since 4.1.0
 */
do_action( 'ap_after_options_page' );

?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('.postbox > h3').on('click', function(){
			$(this).closest('.postbox').toggleClass('closed');
		});
		$('#form_options_general_pages-question_page_slug').on('keyup', function(){
			$('.ap-base-slug').text($(this).val());
		})
	});
</script>
