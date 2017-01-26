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
<a href="#" class="ap-btn ap-btn-markall-read ap-btn-small"><?php _e( 'Mark all as read', 'anspress-pro' ); ?></a>
<div class="ap-noti-sub">
	<a href="#"><?php _e( 'Read', 'anspress-pro' ); ?></a>
	<a href="#"><?php _e( 'Unread', 'anspress-pro' ); ?></a>
</div>
<div class="ap-noti">

		<?php //while ( $notifications->have() ) : $notifications->the_notification(); ?>
			<div class="ap-noti-item clearfix">
				<div class="ap-noti-avatar"><?php echo get_avatar( 33, 40 ); ?></div>
				<div class="ap-noti-inner">
					<strong>Rahul Aryan</strong> answered to your question
					<strong>How to fix node.js issue?</strong>
					<time class="ap-noti-date">12 Hours ago</time>
				</div>
			</div>

			<div class="ap-noti-item clearfix">
				<div class="ap-noti-icon apicon-flag"></div>
				<div class="ap-noti-inner">
					You received a flag on your question
					<strong>How to fix node.js issue?</strong>
					<time class="ap-noti-date">12 Hours ago</time>
				</div>
			</div>

			<div class="ap-noti-item clearfix">
				<div class="ap-noti-icon apicon-reputation"></div>
				<div class="ap-noti-inner">
					You gained <strong>10</strong> reputation points
					<time class="ap-noti-date">12 Hours ago</time>
				</div>
			</div>

			<div class="ap-noti-item clearfix">
				<div class="ap-noti-avatar"><?php echo get_avatar( 2, 40 ); ?></div>
				<div class="ap-noti-inner">
					You have received an up vote on question <strong>Maecenas in diam nisi</strong>
					<time class="ap-noti-date">12 Hours ago</time>
				</div>
			</div><div class="ap-noti-item clearfix">
				<div class="ap-noti-avatar"><?php echo get_avatar( 33, 40 ); ?></div>
				<div class="ap-noti-inner">
					<strong>Rahul Aryan</strong> answered to your question
					<strong>How to fix node.js issue?</strong>
					<time class="ap-noti-date">12 Hours ago</time>
				</div>
			</div>

			<div class="ap-noti-item clearfix">
				<div class="ap-noti-icon apicon-flag"></div>
				<div class="ap-noti-inner">
					You received a flag on your question
					<strong>How to fix node.js issue?</strong>
					<time class="ap-noti-date">12 Hours ago</time>
				</div>
			</div>

			<div class="ap-noti-item clearfix">
				<div class="ap-noti-icon apicon-reputation"></div>
				<div class="ap-noti-inner">
					You gained <strong>10</strong> reputation points
					<time class="ap-noti-date">12 Hours ago</time>
				</div>
			</div>

			<div class="ap-noti-item clearfix">
				<div class="ap-noti-avatar"><?php echo get_avatar( 2, 40 ); ?></div>
				<div class="ap-noti-inner">
					You have received an up vote on question <strong>Maecenas in diam nisi</strong>
					<time class="ap-noti-date">12 Hours ago</time>
				</div>
			</div>


		<?php //endwhile; ?>
</div>

<?php /*if ( $reputations->total_pages > 1 ) : ?>
	<a href="#" ap-loadmore="<?php echo esc_js( wp_json_encode( array( 'ap_ajax_action' => 'load_more_reputation', '__nonce' => wp_create_nonce( 'load_more_reputation' ), 'current' => 1, 'user_id' => $reputations->args['user_id'] ) ) ); ?>" class="ap-loadmore ap-btn" ><?php esc_attr_e( 'Load More', 'anspress-question-answer' ); ?></a>
<?php endif;*/ ?>
