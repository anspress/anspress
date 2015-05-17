<?php
/**
 * Control the output of AnsPress dashboard
 *
 * @link http://anspress.io
 * @since 2.0.0-alpha2
 * @author Rahul Aryan <support@anspress.io>
 * @package AnsPress
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

$question_count = ap_total_posts_count('question');
$answer_count = ap_total_posts_count('answer');
$flagged_count = ap_total_posts_count('both', 'flag');

?>
<div id="anspress" class="wrap">
	<?php do_action('ap_before_admin_page_title') ?>
	<h2><?php _e('AnsPress Dashboard', 'ap') ?></h2>

	<div class="row ap-dash-tiles">
		<div class="ap-dash-tile col-md-6">
			<div class="ap-dash-tile-in ap-tile anspress-stats-count">
				<ul class="clearfix">
					<li>
						<a href="<?php echo admin_url( 'edit.php?post_type=question' ); ?>">
							<strong><?php echo $question_count->publish; ?></strong>
							<span><?php _e('Questions', 'ap') ?></span>
						</a>
					</li>
					<li>
						<a href="<?php echo admin_url( 'edit.php?post_type=answer' ); ?>">
							<strong><?php echo $answer_count->publish; ?></strong>
							<span><?php _e('Answers', 'ap') ?></span>
						</a>
					</li>
					<li>
						<a href="<?php echo admin_url( 'admin.php?page=anspress_moderate' ); ?>">
							<strong><?php echo $question_count->moderate + $answer_count->moderate. ($question_count->moderate + $answer_count->moderate > 0 ? '<i class="ap-need-att">i</i>' : ''); ?></strong>
							<span><?php _e('Moderate', 'ap') ?></span>
						</a>
					</li>
					<li>
						<a href="<?php echo admin_url( 'admin.php?page=anspress_flagged' ); ?>">
							<strong><?php echo $flagged_count->total. ($flagged_count->total > 0 ? '<i class="ap-need-att">i</i>' : ''); ?></strong>
							<span><?php _e('Flagged', 'ap') ?></span>
						</a>
					</li>
				</ul>
			</div>
			<div class="ap-dash-tile-in ap-dash-questions">
				<h3 class="ap-dash-title"><?php _e('Latest Questions', 'ap') ?></h3>
				<?php
					ap_get_questions(array('sortby' => 'newest'));
					if ( ap_have_questions() ):
				?>
				<div class="ap-user-posts">
					<?php while ( ap_questions() ) : ap_the_question(); ?>
						<div class="ap-user-posts-item clearfix">
							<a class="ap-user-posts-vcount ap-tip<?php echo ap_question_best_answer_selected() ? ' answer-selected' :''; ?>" href="<?php ap_question_the_permalink(); ?>" title="<?php _e('Answers'); ?>"><?php echo ap_icon('answer', true); ?><?php echo ap_question_get_the_answer_count(); ?></a>
							<span class="ap-user-posts-active"><?php ap_question_the_active_ago(); ?></span>
							<a class="ap-user-posts-ccount ap-tip" href="<?php ap_question_the_permalink(); ?>" title="<?php _e('Comments', 'ap'); ?>"><?php echo ap_icon('comment', true); ?><?php echo get_comments_number(); ?></a>
							<div class="no-overflow"><a href="<?php ap_question_the_permalink(); ?>" class="ap-user-posts-title"><?php the_title(); ?></a></div>				
						</div>	
					
					<?php 
						endwhile;
						wp_reset_postdata();
					?>

					<?php 
						else: 
							_e('There is no question yet.', 'ap');
						endif; 
					?>
				</div>
			</div>
		</div>
		<div class="ap-dash-tile col-md-6 ">
			<div class="ap-dash-tile col-md-4 clearfix">
				<div class="ap-donation-block">
					<h3>Please donate</h3>
					<p>We love open source as you do, so we decided to keep AnsPress free and open source. Your donation will keep us going, so kindly donate. Thanks.</p>
					<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
						<input type="hidden" name="cmd" value="_xclick">
						<input type="hidden" name="business" value="support@anspress.io">
						<input type="hidden" name="lc" value="US">
						<input type="hidden" name="button_subtype" value="services">
						<input type="hidden" name="no_note" value="0">
						<input type="hidden" name="currency_code" value="USD">
						<input type="hidden" name="bn" value="PP-BuyNowBF:btn_paynowCC_LG.gif:NonHostedGuest">
						<table>
						<tr><td><input type="hidden" name="on0" value="Amount">Amount</td></tr><tr><td><select name="os0">
							<option value="I am fan">I am fan $10.00 USD</option>
							<option value="I am big fan">I am big fan $20.00 USD</option>
							<option value="I am biggest fan">I am biggest fan $30.00 USD</option>
						</select> </td></tr>
						<tr><td><input type="hidden" name="on1" value="Feature request ?">Feature request ?</td></tr><tr><td><input type="text" name="os1" maxlength="200"></td></tr>
						</table>
						<input type="hidden" name="currency_code" value="USD">
						<input type="hidden" name="option_select0" value="I am fan">
						<input type="hidden" name="option_amount0" value="10.00">
						<input type="hidden" name="option_select1" value="I am big fan">
						<input type="hidden" name="option_amount1" value="20.00">
						<input type="hidden" name="option_select2" value="I am biggest fan">
						<input type="hidden" name="option_amount2" value="30.00">
						<input type="hidden" name="option_index" value="0">
						<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_paynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
						<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
					</form>
				</div>
				<div class="anspress-links">
					<a href="http://anspress.io/" target="_blank"><i class="apicon-anspress-icon"></i><?php _e('About AnsPress', 'ap'); ?></a>
					<a href="http://anspress.io/questions/" target="_blank"><i class="apicon-question"></i><?php _e('Support Q&A', 'ap'); ?></a>
					<a href="http://anspress.io/themes/" target="_blank"><i class="apicon-info"></i><?php _e('AnsPress Themes', 'ap'); ?></a>
					<a href="http://github.com/anspress/anspress" target="_blank"><i class="apicon-mark-github"></i><?php _e('Github Repo', 'ap'); ?></a>
				</div>
				<div class="ap-ext-notice">
					<i class="apicon-puzzle"></i>
					<h3>AnsPress extensions</h3>
					<p>Extend AnsPress capabilities, get free extensions</p>
					<a href="http://anspress.io/extensions/" target="_blank">Browse</a>
				</div>
			</div>

		</div>
		
	</div>

</div>