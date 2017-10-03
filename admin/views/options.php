<?php
/**
 * AnsPress options page.
 *
 * @link       https://anspress.io
 * @since      4.1.0
 * @author     Rahul Aryan <support@anspress.io>
 * @package    AnsPress
 * @subpackage Admin Pages
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

/**
 * Action used to register AnsPress options.
 *
 * @since 4.1.0
 */
do_action( 'ap_register_options' );

$form_name = ap_sanitize_unslash( 'ap_form_name', 'r' );
$updated = false;

// Process submit form.
if ( ! empty( $form_name ) && anspress()->get_form( $form_name )->is_submitted() ) {
	$form = anspress()->get_form( $form_name );

	if ( ! $form->have_errors() ) {
		$values = anspress()->get_form( $form_name )->get_values();

		$options = get_option( 'anspress_opt', [] );

		foreach ( $values as $key => $opt ) {
			$options[ $key ] = $opt['value'];
		}

		update_option( 'anspress_opt', $options );
		wp_cache_delete( 'anspress_opt', 'ap' );
		wp_cache_delete( 'anspress_opt', 'ap' );

		// Flush rewrite rules.
		if ( 'form_options_general_pages' === $form_name ) {
			ap_opt( 'ap_flush', 'true' );
			flush_rewrite_rules();
		}

		$updated = true;
	}
}

?>

<?php if ( true === $updated ) :   ?>
	<div class="notice notice-success is-dismissible">
		<p><?php esc_html_e( 'AnsPress option updated successfully!', 'anspress-question-answer' ); ?></p>
	</div>
<?php endif; ?>

<div id="anspress" class="wrap">
	<h2 class="admin-title">
		<?php esc_html_e( 'AnsPress Options', 'anspress-question-answer' ); ?>
		<div class="social-links clearfix">
			<a href="https://github.com/anspress/anspress" target="_blank">GitHub</a>
			<a href="https://wordpress.org/plugins/anspress-question-answer/" target="_blank">WordPress.org</a>
			<a href="https://twitter.com/anspress_io" target="_blank">@anspress_io</a>
			<a href="https://www.facebook.com/wp.anspress" target="_blank">Facebook</a>
		</div>
	</h2>
	<div class="clear"></div>

	<!-- <div class="anspress-imglinks">
		<a href="https://anspress.io/extensions/" target="_blank">
			<img src="<?php echo ANSPRESS_URL; ?>assets/images/more_functions.svg" />
		</a>
	</div> -->

	<div class="ap-optionpage-wrap no-overflow">
		<div class="ap-wrap">
			<div class="anspress-options ap-wrap-left clearfix">
				<div class="option-nav-tab clearfix">
					<div class="option-nav-tab clearfix">
						<h2 class="nav-tab-wrapper">
							<?php
								$active_tab = ap_sanitize_unslash( 'active_tab', 'r', 'general' );

								$tab_links = array(
									'general'       => __( 'General', 'anspress-question-answer' ),
									'postscomments' => __( 'Posts & Comments', 'anspress-question-answer' ),
									'uac'           => __( 'User Access Control', 'anspress-question-answer' ),
									'tools'         => __( 'Tools', 'anspress-question-answer' ),
								);

								/**
								 * Hook for modifying AnsPress options tab links.
								 *
								 * @param array $tab_links Tab links.
								 * @since 4.1.0
								 */
								$tab_links = apply_filters( 'ap_options_tab_links', $tab_links );

								foreach ( $tab_links as $key => $name ) {
									echo '<a href="' . esc_url( admin_url( 'admin.php?page=anspress_options' ) ) . '&active_tab=' . esc_attr( $key ) . '" class="nav-tab ap-user-menu-' . esc_attr( $key ) . ( $key === $active_tab ? ' nav-tab-active' : '' ) . '">' . esc_html( $name ) . '</a>';
								}

								/**
								 * Action triggered right after AnsPress options tab links.
								 * Can be used to show custom tab links.
								 *
								 * @since 4.1.0
								 */
								do_action( 'ap_options_tab_links' );
							?>
						</h2>
					</div>
				</div>
				<div class="metabox-holder">
					<?php
						$active_tab = ap_sanitize_unslash( 'active_tab', 'r', 'general' );
						$form = ap_sanitize_unslash( 'ap_form_name', 'r' );
						$action_url = admin_url( 'admin.php?page=anspress_options&active_tab=' . $active_tab );
					?>
					<div class="ap-group-options">
						<?php if ( 'general' === $active_tab ) : ?>
							<p class="ap-tab-subs">
								<a href="#pages-options"><?php esc_attr_e( 'Pages &amp; Permalinks', 'anspress-question-answer' ); ?></a>
								<a href="#layout-options"><?php esc_attr_e( 'Layout Options', 'anspress-question-answer' ); ?></a>
							</p>
							<div class="postbox">
								<h3 id="pages-options"><?php esc_attr_e( 'Pages &amp; Permalinks', 'anspress-question-answer' ); ?></h3>
								<div class="inside">
									<?php
										anspress()->get_form( 'options_general_pages' )->generate( array(
											'form_action' => $action_url . '#form_options_general_pages',
											'ajax_submit' => false,
										) );
									?>
								</div>
							</div>
							<div class="postbox">
								<h3 id="layout-options"><?php esc_attr_e( 'Layout Options', 'anspress-question-answer' ); ?></h3>
								<div class="inside">
									<?php
										anspress()->get_form( 'options_general_layout' )->generate( array(
											'form_action' => $action_url . '#form_options_general_layout',
											'ajax_submit' => false,
										) );
									?>
								</div>
							</div>
						<?php elseif ( 'postscomments' === $active_tab ) : ?>
							<div class="postbox">
								<h3><?php esc_attr_e( 'Posts', 'anspress-question-answer' ); ?></h3>
								<div class="inside">
									<?php
										anspress()->get_form( 'options_postscomments_posts' )->generate( array(
											'form_action' => $action_url . '#form_options_postscomments_posts',
											'ajax_submit' => false,
										) );
									?>
								</div>
							</div>
						<?php elseif ( 'uac' === $active_tab ) : ?>
							<p class="ap-tab-subs">
								<a href="#uac"><?php esc_attr_e( 'User Access Control', 'anspress-question-answer' ); ?></a>
								<a href="#user-roles"><?php esc_attr_e( 'User roles', 'anspress-question-answer' ); ?></a>
							</p>
							<div class="postbox">
								<h3 id="uac"><?php esc_attr_e( 'User Access Control', 'anspress-question-answer' ); ?></h3>
								<div class="inside">
									<?php
										anspress()->get_form( 'options_uac' )->generate( array(
											'form_action' => $action_url . '#form_options_uac',
											'ajax_submit' => false,
										) );
									?>
								</div>
							</div>

							<div class="postbox">
								<h3 id="user-roles"><?php esc_attr_e( 'User roles', 'anspress-question-answer' ); ?></h3>
								<div class="inside">
									<?php include ANSPRESS_DIR . '/admin/views/roles.php'; ?>
								</div>
							</div>

						<?php elseif( 'tools' === $active_tab ): ?>
							<p class="ap-tab-subs">
								<a href="#re-count"><?php esc_attr_e( 'Re-count', 'anspress-question-answer' ); ?></a>
								<a href="#uninstall"><?php esc_attr_e( 'Uninstall', 'anspress-question-answer' ); ?></a>
							</p>
							<?php global $wpdb; ?>

							<div class="postbox">
								<h3 id="re-count"><?php esc_attr_e( 'Re-count', 'anspress-question-answer' ); ?></h3>
								<div class="inside">
									<?php include ANSPRESS_DIR . '/admin/views/recount.php'; ?>
								</div>
							</div>

							<div class="postbox">
								<h3 id="uninstall"><?php esc_attr_e( 'Uninstall - clear all AnsPress data', 'anspress-question-answer' ); ?></h3>
								<div class="inside">
									<?php include ANSPRESS_DIR . '/admin/views/uninstall.php'; ?>
								</div>
							</div>
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
		$('.postbox > h3').click(function(){
			$(this).closest('.postbox').toggleClass('closed');
		});
		$('#form_options_general_pages-question_page_slug').on('keyup', function(){
			$('.ap-base-slug').text($(this).val());
		})
	});
</script>
