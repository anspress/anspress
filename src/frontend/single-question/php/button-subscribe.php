<?php
/**
 * Post subscriber button.
 *
 * @package AnsPress
 * @since   5.0.0
 */

use AnsPress\Classes\Auth;
use AnsPress\Classes\Plugin;
use AnsPress\Classes\PostHelper;
use AnsPress\Classes\Router;
use AnsPress\Modules\Subscriber\SubscriberService;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

// Check that post argument is set.
if ( ! isset( $post ) ) {
	throw new InvalidArgumentException( 'Post argument is required.' );
}

if ( ! isset( $attributes ) ) {
	throw new InvalidArgumentException( 'Attributes argument is required.' );
}

if ( ! PostHelper::isQuestion( $post ) ) {
	return;
}

$href = Router::route(
	'v1.questions.actions',
	array(
		'question_id' => $post->ID,
		'action'      => 'subscribe',
	)
);

$totalSubscriberCount = Plugin::get( SubscriberService::class )->getSubscriberCountByEventRef( 'question', $post->ID );

$isSubscribed = Plugin::get( SubscriberService::class )->isSubscribedToQuestion( $post->ID );

$subscribers = Plugin::get( SubscriberService::class )->getQuestionSubscribers( $post->ID, array( 'limit' => 8 ) );
?>
<div class="anspress-subscribes">
	<anspress-link
		data-href="<?php echo esc_attr( $href ); ?>"
		data-method="POST"
		data-anspress-id="button:subscribe:<?php echo (int) $post->ID; ?>"
		class="anspress-button anspress-button-subscribe<?php echo $isSubscribed ? ' anspress-button-active' : ''; ?>"><?php echo ! $isSubscribed ? esc_attr__( 'Subscribe', 'anspress-question-answer' ) : esc_attr__( 'Unsubscribe', 'anspress-question-answer' ); ?></anspress-link>

	<?php if ( $totalSubscriberCount > 0 ) : ?>
		<div class="anspress-subscriber-list">
			<?php if ( $subscribers ) : ?>

					<?php foreach ( $subscribers as $subscriber ) : ?>
						<?php echo get_avatar( $subscriber->subs_user_id, 30 ); ?>
					<?php endforeach; ?>

			<?php endif; ?>
			<?php if ( $totalSubscriberCount > 8 ) : ?>
				<div class="anspress-subscriber-count">+<?php echo esc_attr( number_format_i18n( $totalSubscriberCount - 8 ) ); ?></div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
