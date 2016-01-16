<?php
/**
 * Display user card
 *
 * @link http://wp3.in
 * @since unknown
 * @package AnsPress
 */
?>
<div id="user_<?php ap_user_the_ID(); ?>_card" style="display:none">
	<div class="ap-card-content">
		<div class="ap-avatar">
			<a href="<?php ap_user_the_link(); ?>">
				<?php ap_user_the_avatar( 40 ); ?>
			</a>
			<?php // ap_follow_button(ap_user_get_the_ID() ); ?>
		</div>
		<?php do_action('ap_hover_card_before_status', ap_user_get_the_ID() ); ?>

		<div class="ap-card-header">
			<a href="<?php ap_user_the_link(); ?>" class="ap-card-name"><?php ap_user_the_display_name(); ?></a>
			<span class="ap-card-desc"><?php echo ap_truncate_chars(get_user_meta( ap_user_get_the_ID(), 'signature', true ), 50 ); ?></span>
		</div>

		<div class="ap-card-count clearfix">
			<div class="ap-card-reputation">
				<span><?php ap_user_the_reputation(); ?></span>
				<i><?php _e('Total Reputation', 'anspress-question-answer' ); ?></i>
			</div>
			<div class="ap-card-reputation">
				<span><?php ap_user_the_reputation(); ?></span>
				<i><?php _e('Reputation', 'anspress-question-answer' ); ?></i>
			</div>
		</div>

		<div class="ap-card-stats">
			<span>
				<?php echo ap_icon('answer', true ); ?>
				<?php
					printf(__('%s answers, %s selected', 'anspress-question-answer' ),
						'<b>'.ap_user_get_the_meta('__total_answers' ).'</b>',
						'<b>'.ap_user_get_the_meta('__best_answers' ).'</b>'
					);
				?>
				</span>
				<span>
					<?php echo ap_icon('question', true ); ?>
					<?php
						printf(__('%s questions, %s solved', 'anspress-question-answer' ),
							'<b>'.ap_user_get_the_meta('__total_questions' ).'</b>',
							'<b>'.ap_user_get_the_meta('__solved_answers' ).'</b>'
						);
					?>
					</span>
					<span>
						<i class="apicon-users"></i> 
						<?php
							printf(__('%s Followers, %s following', 'anspress-question-answer' ),
								'<b>'.ap_user_get_the_meta('__total_followers' ).'</b>',
								'<b>'.ap_user_get_the_meta('__total_following' ).'</b>'
							);
						?>
						</span>
					</div>
					<?php do_action('ap_hover_card_after_status', ap_user_get_the_ID() ); ?>
				</div>
			</div>
