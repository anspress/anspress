<?php
/**
 * AnsPress options page
 *
 * @link https://anspress.io/anspress
 * @since 2.0.1
 * @author Rahul Aryan <support@anspress.io>
 * @package WordPress/AnsPress
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$updated = ap_sanitize_unslash( 'updated', 'r' );
?>

<?php if ( 'true' === $updated ) :   ?>
	<div class="notice notice-success is-dismissible">
		<p><?php esc_html_e( 'AnsPress option updated!', 'anspress-question-answer' ); ?></p>
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

	<div class="anspress-imglinks">
		<a href="https://anspress.io/extensions/" target="_blank">
			<img src="<?php echo ANSPRESS_URL; ?>assets/images/more_functions.svg" />
		</a>
	</div>

	<div class="ap-optionpage-wrap no-overflow">

		<div class="ap-wrap">
			<div class="anspress-options ap-wrap-left clearfix">
				<div class="option-nav-tab clearfix">
					<?php ap_options_nav(); ?>
				</div>
				<div class="metabox-holder">

					<div class="ap-group-options">
						<?php ap_option_group_fields(); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('.postbox > h3').click(function(){
			$(this).closest('.postbox').toggleClass('closed');
		});
		$('#question_page_slug').on('keyup', function(){
			$('.ap-base-slug').text($(this).val());
		})
	});
</script>
