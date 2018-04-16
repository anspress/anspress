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

$recounts = array(
	'votes' => array(
		'label' => __( 'Recount Votes', 'anspress-question-answer' ),
		'desc'  => __( 'Recount votes of questions and answers.', 'anspress-question-answer' ),
	),
	'answers' => array(
		'label' => __( 'Recount Answers', 'anspress-question-answer' ),
		'desc'  => __( 'Recount answers of every question.', 'anspress-question-answer' ),
	),
	'flagged' => array(
		'label' => __( 'Recount Flags', 'anspress-question-answer' ),
		'desc'  => __( 'Recount flags on questions and answers.', 'anspress-question-answer' ),
	),
	'subscribers' => array(
		'label' => __( 'Recount Subscribers', 'anspress-question-answer' ),
		'desc'  => __( 'Recount subscribers of questions.', 'anspress-question-answer' ),
	),
);

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

			<?php foreach ( $recounts as $rc => $args ) : ?>
				<tr>
					<th scope="row" valign="top">
						<label><?php echo esc_attr( $args['label'] ); ?></label>
					</th>
					<td>
						<?php
							$btn_args = wp_json_encode( array(
								'action'  => 'ap_recount_' . $rc,
								'__nonce' => wp_create_nonce( 'recount_' . $rc ),
							) );
						?>
						<div class="btn-container ap-recount-<?php echo esc_attr( $rc ); ?>">
							<button class="button ap-recount-btn" data-query="<?php echo esc_js( $btn_args ); ?>"><?php echo esc_attr( $args['label'] ); ?></button>
							<span class="recount-msg"></span>
						</div>
						<p class="description"><?php echo esc_attr( $args['desc'] ); ?></p>
					</td>
				</tr>
			<?php endforeach; ?>

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
