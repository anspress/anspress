<?php
/**
 * Template for user notification loop.
 *
 * Render notifications in user's page.
 *
 * @author  Rahul Aryan <support@anspress.io>
 * @link    https://anspress.io/
 * @since   1.0.0
 * @package WordPress/AnsPress-pro
 */

?>

<?php if ( ap_count_unseen_notifications() > 0 ) : ?>
	<?php
		$btn_args = wp_json_encode( array(
			'ap_ajax_action' => 'mark_notifications_seen',
			'__nonce'        => wp_create_nonce( 'mark_notifications_seen' ),
		) );
	?>
	<a href="#" class="ap-btn ap-btn-markall-read ap-btn-small" ap-ajax-btn ap-query="<?php echo esc_js( $btn_args ); ?>">
		<?php _e( 'Mark all as seen', 'anspress-pro' ); // xss okay. ?>
	</a>
<?php endif; ?>

<div class="ap-noti-sub">
	<a href="?tab=notifications&seen=all"><?php _e( 'All', 'anspress-pro' ); ?></a>
	<a href="?tab=notifications&seen=0"><?php _e( 'Unseen', 'anspress-pro' ); ?></a>
	<a href="?tab=notifications&seen=1"><?php _e( 'Seen', 'anspress-pro' ); ?></a>
</div>

<?php if ( $notifications->have() ) : ?>
	<div class="ap-noti">
		<?php while ( $notifications->have() ) : $notifications->the_notification(); ?>
			<?php $notifications->item_template(); ?>
		<?php endwhile; ?>
	</div>
<?php else : ?>
	<h3><?php _e( 'No notification', 'anspress-question-answer' ); // xss ok. ?></h3>
<?php endif; ?>


<?php /*if ( $reputations->total_pages > 1 ) : ?>
	<a href="#" ap-loadmore="<?php echo esc_js( wp_json_encode( array( 'ap_ajax_action' => 'load_more_reputation', '__nonce' => wp_create_nonce( 'load_more_reputation' ), 'current' => 1, 'user_id' => $reputations->args['user_id'] ) ) ); ?>" class="ap-loadmore ap-btn" ><?php esc_attr_e( 'Load More', 'anspress-question-answer' ); ?></a>
<?php endif;*/ ?>
