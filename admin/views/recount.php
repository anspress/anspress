<?php
/**
 * AnsPress admin section recount section
 *
 * @link https://anspress.io
 * @since 4.0.5
 * @author Rahul Aryan <support@anspress.io>
 * @package AnsPress
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$class = 'is-dismissible';

?>
<style>

	.btn-container .button{
		transition: padding-right 0.5s;
	}

	.btn-container span.success, .btn-container span.failed{
		background: none;
	}
	.hide{
		display: none !important;
	}
</style>

<div class="wrap">
	<?php do_action( 'ap_before_admin_page_title' ); ?>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row" valign="top">
					<label><?php esc_attr_e( 'Votes', 'anspress-question-answer' ); ?></label>
				</th>
				<td>
					<?php
						$btn_args = wp_json_encode( array(
							'action'  => 'ap_recount_votes',
							'__nonce' => wp_create_nonce( 'recount_votes' ),
						) );
					?>
					<div class="btn-container ap-recount-votes">
						<button class="button ap-recount-btn" data-query="<?php echo esc_js( $btn_args ); ?>"><?php esc_attr_e( 'Recount votes', 'anspress-question-answer' ); ?></button>
						<span class="recount-msg"></span>
					</div>
					<p class="description"><?php esc_attr_e( 'Recount all votes of question and answers.', 'anspress-question-answer' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label><?php esc_attr_e( 'Answers', 'anspress-question-answer' ); ?></label>
				</th>
				<td>
					<?php
						$btn_args = wp_json_encode( array(
							'action'  => 'ap_recount_answers',
							'__nonce' => wp_create_nonce( 'recount_answers' ),
						) );
					?>
					<div class="btn-container ap-recount-answers">
						<button class="button ap-recount-btn" data-query="<?php echo esc_js( $btn_args ); ?>"><?php esc_attr_e( 'Recount answers', 'anspress-question-answer' ); ?></button>
						<span class="recount-msg"></span>
					</div>
					<p class="description"><?php esc_attr_e( 'Recount answers of all questions.', 'anspress-question-answer' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label><?php esc_attr_e( 'Flagged posts', 'anspress-question-answer' ); ?></label>
				</th>
				<td>
					<div class="btn-container">
						<button class="button ap-recount-btn" data-action="flags"><?php esc_attr_e( 'Re-count flagged posts', 'anspress-question-answer' ); ?></button>
						<span class="hide"
							data-start="<?php _e( 'Re-counting flagged posts...', 'anspress-question-answer' ); ?>"
							data-continue="<?php _e( '{0} out of {1} posts processed', 'anspress-question-answer' ); ?>"
							data-success="<?php _e( 'Successfully updated flagged posts count!', 'anspress-question-answer' ); ?>"
							data-failed="<?php _e( 'Failed to count flagged posts, please try again or submit a help request', 'anspress-question-answer' ); ?>">
						</span>
					</div>
					<p class="description"><?php esc_attr_e( 'Re-count flagged posts.', 'anspress-question-answer' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label><?php esc_attr_e( 'Subscribers', 'anspress-question-answer' ); ?></label>
				</th>
				<td>
					<div class="btn-container">
						<button class="button ap-recount-btn" data-action="subscribers"><?php esc_attr_e( 'Re-count question subscribers', 'anspress-question-answer' ); ?></button>
						<span class="hide"
							data-start="<?php _e( 'Re-counting question subscribers...', 'anspress-question-answer' ); ?>"
							data-continue="<?php _e( '{0} out of {1} questions processed', 'anspress-question-answer' ); ?>"
							data-success="<?php _e( 'Successfully updated subscribers count!', 'anspress-question-answer' ); ?>"
							data-failed="<?php _e( 'Failed to count question subscribers, please try again or submit a help request', 'anspress-question-answer' ); ?>">
						</span>
					</div>
					<p class="description"><?php esc_attr_e( 'Re-count question subscribers.', 'anspress-question-answer' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label><?php esc_attr_e( 'Reputations', 'anspress-question-answer' ); ?></label>
				</th>
				<td>
					<div class="btn-container">
						<button class="button ap-recount-btn" data-action="reputation"><?php esc_attr_e( 'Re-count user reputation', 'anspress-question-answer' ); ?></button>
						<span class="hide"
							data-start="<?php _e( 'Re-counting user reputation...', 'anspress-question-answer' ); ?>"
							data-continue="<?php _e( '{0} out of {1} user processed', 'anspress-question-answer' ); ?>"
							data-success="<?php _e( 'Successfully updated users reputation!', 'anspress-question-answer' ); ?>"
							data-failed="<?php _e( 'Failed to count users reputation, please try again or submit a help request', 'anspress-question-answer' ); ?>">
						</span>
					</div>
					<p class="description"><?php esc_attr_e( 'Re-count all users reputation points.', 'anspress-question-answer' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row" valign="top">
					<label><?php esc_attr_e( 'Views', 'anspress-question-answer' ); ?></label>
				</th>
				<td>
					<div class="btn-container">
						<button class="button ap-recount-btn" data-action="views"><?php esc_attr_e( 'Re-count question views', 'anspress-question-answer' ); ?></button>

						<span class="hide"
							data-start="<?php _e( 'Re-counting post views...', 'anspress-question-answer' ); ?>"
							data-continue="<?php _e( '{0} out of {1} question processed', 'anspress-question-answer' ); ?>"
							data-success="<?php _e( 'Successfully updated question views!', 'anspress-question-answer' ); ?>"
							data-failed="<?php _e( 'Failed to count views, please try again or submit a help request', 'anspress-question-answer' ); ?>">
						</span>
					</div>
					<p class="description"><?php esc_attr_e( 'Re-count all questions views.', 'anspress-question-answer' ); ?></p>
					<br />
					<br />
					<form class="counter-args">
						<p><strong><?php _e( 'Add fake views if views table is empty', 'anspress-question-answer' ); ?></strong></p>
						<label>
							<?php _e( 'Add fake views', 'anspress-question-answer' ); ?>
							<input type="checkbox" name="fake_views" value="1" />
						</label>
						<br />
						<br />
						<label><?php _e( 'Minimum and maximum views', 'anspress-question-answer' ); ?></label>
						<input type="text" value="500" name="min_views" placeholder="<?php _e( 'Min. views', 'anspress-question-answer' ); ?>" />
						<input type="text" value="1000" name="max_views" placeholder="<?php _e( 'Max. views', 'anspress-question-answer' ); ?>" />
					</form>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<script type="text/javascript">
	(function($){
		function apRecount(query){
			AnsPress.ajax({
				data: query,
				success: function(data){
					AnsPress.hideLoading($(data.el).find('.button'));
					$(data.el).find('.recount-msg').text(data.msg);
					if(typeof data.q !== 'undefined'){
						AnsPress.showLoading($(data.el).find('.button'));
						apRecount(data.q);
					}
				}
			});
		}

		$('.ap-recount-btn').click(function(e){
			e.preventDefault();
			var query = $(this).data('query');
			AnsPress.showLoading($(this));
			apRecount(query);
		})
	})(jQuery);

</script>
