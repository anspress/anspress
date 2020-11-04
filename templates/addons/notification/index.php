<?php
/**
 * Template for user notification loop.
 *
 * Render notifications in user's page.
 *
 * @author  Rahul Aryan <rah12@live.com>
 * @link    https://anspress.net/
 * @since   1.0.0
 * @package AnsPress-pro
 */

$user_id = get_query_var( 'ap_user_id' );
?>

<?php if ( ap_count_unseen_notifications() > 0 ) : ?>
	<?php
		$btn_args = wp_json_encode(
			array(
				'ap_ajax_action' => 'mark_notifications_seen',
				'__nonce'        => wp_create_nonce( 'mark_notifications_seen' ),
			)
		);
	?>
	<a href="#" class="ap-btn ap-btn-markall-read ap-btn-small" apajaxbtn apquery="<?php echo esc_js( $btn_args ); ?>">
		<?php _e( 'Mark all as seen', 'anspress-question-answer' ); // xss okay. ?>
	</a>
<?php endif; ?>

<div class="ap-noti-sub">
	<a href="<?php echo ap_user_link( $user_id, 'notifications' ); ?>?seen=all"><?php _e( 'All', 'anspress-question-answer' ); ?></a>
	<a href="<?php echo ap_user_link( $user_id, 'notifications' ); ?>?seen=0"><?php _e( 'Unseen', 'anspress-question-answer' ); ?></a>
	<a href="<?php echo ap_user_link( $user_id, 'notifications' ); ?>?seen=1"><?php _e( 'Seen', 'anspress-question-answer' ); ?></a>
</div>

<?php if ( $notifications->have() ) : ?>
	<div class="ap-noti">
		<?php
		while ( $notifications->have() ) :
			$notifications->the_notification();
?>
			<?php $notifications->item_template(); ?>
		<?php endwhile; ?>
	</div>
<?php else : ?>
	<h3><?php _e( 'No notification', 'anspress-question-answer' ); // xss ok. ?></h3>
<?php endif; ?>


<?php if ( $notifications->total_pages > 1 ) : ?>
	<a href="#" ap-loadmore="
	<?php
	echo esc_js(
		wp_json_encode(
			array(
				'ap_ajax_action' => 'load_more_notifications',
				'__nonce'        => wp_create_nonce( 'load_more_notifications' ),
				'current'        => 1,
				'user_id'        => $notifications->args['user_id'],
			)
		)
	);
?>
" class="ap-loadmore ap-btn" ><?php esc_attr_e( 'Load More', 'anspress-question-answer' ); ?></a>
<?php endif; ?>
