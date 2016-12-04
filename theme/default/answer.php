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
<div id="post-<?php the_ID(); ?>" <?php post_class() ?> ap-id="<?php the_ID(); ?>" ap="answer">
	<div class="ap-content" itemprop="suggestedAnswer<?php echo ap_is_selected() ? ' acceptedAnswer' : ''; ?>" itemscope itemtype="https://schema.org/Answer">
		<div class="ap-single-vote"><?php ap_vote_btn(); ?></div>
		<div class="ap-avatar">
			<a href="<?php ap_profile_link(); ?>"<?php ap_hover_card_attr(); ?>>
				<?php ap_author_avatar( ap_opt( 'avatar_size_qanswer' ) ); ?>
			</a>
		</div>
		<div class="ap-cell clearfix">
			<div class="ap-cell-inner">
				<div class="ap-q-metas">
					<?php echo ap_user_display_name( [ 'html' => true ] ); ?>
					<a href="<?php the_permalink(); ?>" class="ap-posted">
						<time itemprop="datePublished" datetime="<?php echo ap_get_time( get_the_ID(), 'c' ); ?>">
							<?php printf( 'Posted %s', ap_human_time( ap_get_time( get_the_ID(), 'U' ) ) ); ?>
						</time>
					</a>
					<?php ap_recent_post_activity(); ?>
					<?php echo ap_post_status_badge( ); // xss okay.	?>
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

				</div>
				<?php if ( ap_user_can_read_answer( ) ) : ?>
					<div class="ap-post-footer clearfix">
						<?php echo ap_select_answer_btn_html( ); // xss okay ?>
						<?php ap_post_actions_buttons() ?>
						<?php echo ap_comment_btn_html(); // xss okay. ?>
					</div>
				<?php endif; ?>
			</div>
			<?php ap_the_comments(); ?>
		</div>
	</div>
</div>
