<div class="ap-uw clearfix">
	<?php 
		while ( ap_users() ) : ap_the_user();
			?>
			<div class="ap-uw-summary clearfix" data-id="<?php ap_user_the_ID(); ?>">
				<a class="ap-users-avatar" href="<?php ap_user_the_link(); ?>">
					<?php ap_user_the_avatar( $avatar_size )  ?>
				</a>
				<div class="no-overflow clearfix">
					<a class="ap-uw-name" href="<?php ap_user_the_link(); ?>"><?php ap_user_the_display_name(); ?></a>
					<div class="ap-uw-status">
						<span><?php
							printf(
								/* translators: %s: user reputation */
								__( '%s Rep.', 'anspress-question-answer' ),
								ap_user_get_the_reputation()
							);
						?></span>
						<span><?php
							printf(
								/* translators: %d: total best answers */
								__( '%d Best', 'anspress-question-answer' ),
								ap_user_get_the_meta('__best_answers')
							);
						?></span>
						<span><?php
							printf(
								/* translators: %d: total answers */
								__( '%d Answers', 'anspress-question-answer' ),
								ap_user_get_the_meta('__total_answers')
							);
						?></span>
						<span><?php
							printf(
								/* translators: %d: total questions */
								__( '%d Questions', 'anspress-question-answer' ),
								ap_user_get_the_meta('__total_questions')
							);
						?></span>
						<?php 
				            /**
				             * ACTION: ap_users_loop_meta
				             * Used to hook into loop item meta
				             * @since 2.1.0
				             */
				            do_action('ap_users_loop_meta'); 
				        ?>
			        </div>
			        <div class="ap-users-buttons clearfix">
						<?php ap_follow_button(ap_user_get_the_ID()); ?>
					</div>
				</div>
			</div>
			<?php
		endwhile;

		/*} else {
			_e('No users found.', 'anspress-question-answer');
		} */
	?>	
</div>


