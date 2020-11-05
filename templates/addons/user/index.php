<?php
/**
 * User profile template.
 * User profile index template.
 *
 * @license    https://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 * @author     Rahul Aryan <rah12@live.com>
 *
 * @link       https://anspress.net
 * @since      4.0.0
 * @package    AnsPress
 * @subpackage Templates
 */

$user_id     = ap_current_user_id();
$current_tab = ap_sanitize_unslash( 'tab', 'r', 'questions' );
?>

<div id="ap-user" class="ap-user <?php echo is_active_sidebar( 'ap-user' ) && is_anspress() ? 'ap-col-9' : 'ap-col-12'; ?>">

	<?php if ( '0' == $user_id && ! is_user_logged_in() ) : ?>

		<h1><?php _e( 'Please login to view your profile', 'anspress-question-answer' ); ?></h1>

	<?php else : ?>

		<div class="ap-user-bio">
			<div class="ap-user-avatar ap-pull-left">
				<?php echo get_avatar( $user_id, 80 ); ?>
			</div>
			<div class="no-overflow">
				<div class="ap-user-name">
					<?php
					echo ap_user_display_name(
						[
							'user_id' => $user_id,
							'html'    => true,
						]
					);
?>
				</div>
				<div class="ap-user-about">
					<?php echo get_user_meta( $user_id, 'description', true ); ?>
				</div>
			</div>
		</div>
		<?php self::user_menu(); ?>
		<?php self::sub_page_template(); ?>

	<?php endif; ?>

</div>

<?php if ( is_active_sidebar( 'ap-user' ) && is_anspress() ) : ?>
	<div class="ap-question-right ap-col-3">
		<?php dynamic_sidebar( 'ap-user' ); ?>
	</div>
<?php endif; ?>
