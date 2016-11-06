<?php
/**
 * Answer content
 *
 * @author Rahul Aryan <support@anspress.io>
 * @link https://anspress.io/anspress
 * @since 0.1
 *
 * @package AnsPress
 */

?>
<div id="post-<?php the_ID(); ?>" <?php post_class() ?> data-id="<?php the_ID(); ?>" ap-id>
	<div class="ap-content" itemprop="suggestedAnswer<?php echo ap_is_selected() ? ' acceptedAnswer' : ''; ?>" itemscope itemtype="https://schema.org/Answer">
		<div class="ap-single-vote"><?php ap_vote_btn(); ?></div>
		<div class="ap-avatar">
			<a href="<?php ap_profile_link(); ?>"<?php ap_hover_card_attr(); ?>>
				<?php ap_author_avatar(); ?>
			</a>
		</div>
		<div class="ap-a-cells clearfix">

			<div class="ap-q-metas">
				<?php echo ap_user_display_name( [ 'html' => true ] ); ?>
				<time itemprop="datePublished" datetime="<?php echo ap_get_time( get_the_ID(), 'c' ); ?>">
					<?php printf( 'Posted %s', ap_human_time( ap_get_time( get_the_ID(), 'U' ) ) ); ?>
				</time>
			</div>

			<div class="ap-q-inner">
				<?php
					/**
					 * ACTION: ap_before_answer_content
					 * @since   3.0.0
					 */
					do_action( 'ap_before_answer_content' );

				?>
				<div class="ap-answer-content ap-q-content" itemprop="text" ap-content>
						<?php the_content(); ?>
				</div>
				<?php
					/**
					 * ACTION: ap_after_answer_content
					 * @since   3.0.0
					 */
					do_action( 'ap_after_answer_content' );

				?>
				<?php if ( ap_user_can_read_answer( ) ) :   ?>
					<?php ap_recent_post_activity(); ?>
					<?php ap_post_status_description( get_the_ID() ) ?>
					<?php ap_post_actions_buttons() ?>
				<?php endif; ?>
			</div>

			<?php ap_answer_the_comments(); ?>
		</div>
	</div>
</div>
