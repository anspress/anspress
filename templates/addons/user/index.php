<?php
/**
 * User profile template
 * User profile index template.
 *
 * @link https://anspress.io
 * @since 4.0.0
 * @package AnsPress
 */

	$user_id = get_query_var( 'ap_user_id' );
	$current_tab = ap_sanitize_unslash( 'tab', 'r', 'questions' );
?>
<div id="ap-user" class="ap-user <?php echo is_active_sidebar( 'ap-user' ) && is_anspress() ? 'ap-col-9' : 'ap-col-12' ?>">
	<div class="ap-user-bio">
		<div class="ap-user-avatar ap-pull-left">
			<?php echo get_avatar( $user_id, 80 ); ?>
		</div>
		<div class="no-overflow">
			<div class="ap-user-name">
				<?php echo ap_user_display_name( [ 'user_id' => $user_id, 'html' => true ] ); ?>
			</div>
			<div class="ap-user-about">
				<?php echo get_user_meta( $user_id, 'description', true ); ?>
			</div>
		</div>
	</div>

	<?php SELF::user_menu(); ?>
	<?php SELF::sub_page_template(); ?>

	<?php //do_action( 'ap_user_content' ); ?>

</div>
<?php if ( is_active_sidebar( 'ap-user' ) && is_anspress()){ ?>
	<div class="ap-question-right ap-col-3">
		<?php dynamic_sidebar( 'ap-user' ); ?>
	</div>
<?php } ?>

