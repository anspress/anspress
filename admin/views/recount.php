<?php
/**
 * AnsPress admin section recount section
 *
 * @link https://anspress.net
 * @since 4.0.5
 * @author Rahul Aryan <rah12@live.com>
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
	'reputation' => array(
		'label' => __( 'Recount Reputation', 'anspress-question-answer' ),
		'desc'  => __( 'Recount reputation of all users.', 'anspress-question-answer' ),
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
					<label><?php esc_attr_e( 'Views', 'anspress-question-answer' ); ?></label>
				</th>
				<td>
					<?php
						$btn_args = wp_json_encode( array(
							'action'  => 'ap_recount_views',
							'__nonce' => wp_create_nonce( 'recount_views' ),
						) );
					?>
					<div class="btn-container ap-recount-views">
						<button class="button ap-recount-btn" data-query="<?php echo esc_js( $btn_args ); ?>"><?php esc_attr_e( 'Recount question views', 'anspress-question-answer' ); ?></button>

						<span class="recount-msg"></span>
					</div>
					<p class="description"><?php esc_attr_e( 'Recount views count of all questions.', 'anspress-question-answer' ); ?></p>
					<br />
					<form class="counter-args">
						<p><strong><?php esc_attr_e( 'Add fake views if views table is empty', 'anspress-question-answer' ); ?></strong></p>
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
		var viewsArgs;

		function apRecount(query){
			query.args = viewsArgs;
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
			viewsArgs = $(this).closest('td').find('form').serialize();
			apRecount(query);
		})
	})(jQuery);

</script>
