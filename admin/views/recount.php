<?php
/**
 * AnsPress admin section recount section
 *
 * @link https://anspress.io
 * @since 4.0.5
 * @author Rahul Aryan <support@anspress.io>
 * @package WordPress/AnsPress
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$class = 'is-dismissible';


// if ( ! empty( $message ) ) {
// 	printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
// }
?>
<style>
	#anspress .show_loading .button{
		background-image: url(<?php echo ap_get_theme_url( 'images/loading.gif' ); ?>) !important;
		background-repeat: no-repeat !important;
		padding-right: 35px;
		background-position: 94% 4px !important;
		background-size: 18px !important;
	}
	.btn-container .button{
		transition: padding-right 0.5s;
	}
	.btn-container span{
		height: 28px;
		display: inline-block;
		line-height: 27px;
		font-style: italic;
	}
	.btn-container span.success, .btn-container span.failed{
		background: none;
	}
	.hide{
		display: none !important;
	}
</style>

<div class="wrap">
	<?php do_action( 'ap_before_admin_page_title' ) ?>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row" valign="top">
					<label><?php esc_attr_e( 'Votes', 'anspress-question-answer' ); ?></label>
				</th>
				<td>
					<div class="btn-container">
						<button class="button ap-recount-btn" data-action="votes"><?php esc_attr_e( 'Re-count votes', 'anspress-question-answer' ); ?></button>
						<span class="hide"
							data-start="<?php _e( 'Re-counting all AnsPress post votes...', 'anspress-question-answer' ); ?>"
							data-continue="<?php _e( '{0} out of {1} posts processed', 'anspress-question-answer' ); ?>"
							data-success="<?php _e( 'Successfully counted all votes!', 'anspress-question-answer' ); ?>"
							data-failed="<?php _e( 'Failed to count all votes, please try again or submit a help request', 'anspress-question-answer' ); ?>">
						</span>
					</div>
					<p class="description"><?php esc_attr_e( 'Re-count all votes of question and answers.', 'anspress-question-answer' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label><?php esc_attr_e( 'Answers', 'anspress-question-answer' ); ?></label>
				</th>
				<td>
					<div class="btn-container">
						<button class="button ap-recount-btn" data-action="answers"><?php esc_attr_e( 'Re-count answers', 'anspress-question-answer' ); ?></button>
						<span class="hide"
							data-start="<?php _e( 'Re-counting answers of every question...', 'anspress-question-answer' ); ?>"
							data-continue="<?php _e( '{0} out of {1} question processed', 'anspress-question-answer' ); ?>"
							data-success="<?php _e( 'Successfully updated answers count!', 'anspress-question-answer' ); ?>"
							data-failed="<?php _e( 'Failed to count answers, please try again or submit a help request', 'anspress-question-answer' ); ?>">
						</span>
					</div>
					<p class="description"><?php esc_attr_e( 'Re-count answers of all questions.', 'anspress-question-answer' ); ?></p>
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
		</tbody>
	</table>
</div>

<script type="text/javascript">
	var __nonce = '<?php echo wp_create_nonce('recount'); ?>';
	// First, checks if it isn't implemented yet.
	if (!String.prototype.format) {
		String.prototype.apFormat = function() {
			var args = arguments;
			return this.replace(/{(\d+)}/g, function(match, number) {
				return typeof args[number] != 'undefined'
					? args[number]
					: match
				;
			});
		};
	}
	(function($){

		var apAjaxCountAction = function($el){
			var currentRequest = $el.attr('data-current')||0;
			if(currentRequest > 5)
				return;

			$.ajax({
				url: ajaxurl,
				data: {
					action: 'anspress_recount',
					sub_action: $el.data('action'),
					__nonce: __nonce,
					current: currentRequest
				},
				success: function(data){
					if(typeof data.error !== 'undefined' && data.error)
						return alert('Error!');

					currentRequest++;

					$el.attr('data-current', currentRequest);
					showMessage($el.next(), data);

					if(data.action==='continue')
						apAjaxCountAction($el);

					else if(data.action==='failed' || data.action==='success'){
						$el.prop('disabled', false).parent().removeClass('show_loading');
						$el.attr('data-current', 0);
					}
				}
			});
		}

		var showMessage = function($el, args){
			args = args||{action: 'start', processed: 0, total: 0};
			var msg = $el.data(args.action);
			$el.removeClass('hide').addClass(args.action);
			$el.text(msg.apFormat(args.processed, args.total));

			if(args.action==='failed')
				$el.css('color', '#F44336');
			else if(args.action==='success')
				$el.css('color', '#4CAF50');
		}

		$(document).ready(function(){
			$('.ap-recount-btn').click(function(e){
				e.preventDefault();
				$(this).parent().addClass('show_loading');
				$(this).prop('disabled', true);
				apAjaxCountAction($(this));
				showMessage($(this).next());
			})
		});

	})(jQuery);

</script>
