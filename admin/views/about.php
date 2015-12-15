<?php
/**
 * Control the output of AnsPress about page
 *
 * @link http://anspress.io
 * @since 2.2
 * @author Rahul Aryan <support@anspress.io>
 * @package AnsPress
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
	die;
}

?>
<div id="anspress" class="wrap ap-about">
	<h2>
		<i class="apicon-anspress"></i>
		<span><?php echo AP_VERSION; ?></span>
		
	</h2>

	<div class="container">
		<div class="row ap-about-feature">
			<div class="col-md-8">
				<h3>Changelog</h3>
				<div class="ap-changelog">
				
				<span class="done"> <span class="bullet-done">✔</span> Form error message below input field</span>
				<span class="done"> <span class="bullet-done">✔</span> DB error in Answer sorting</span>
				<span class="done"> <span class="bullet-done">✔</span> While try to edit answer format get cleared </span>
				<span class="done"> <span class="bullet-done">✔</span> BuddyPress reputation count shows 1</span>
				<span class="done"> <span class="bullet-done">✔</span> Load minified assets</span>
				<span class="done"> <span class="bullet-done">✔</span> Improved admin dashboard</span>
				<span class="done"> <span class="bullet-done">✔</span> popup add link box in tinymce cuts off content.</span>
				<span class="done"> <span class="bullet-done">✔</span> Add breadcrumbs widget</span>
				<span class="done"> <span class="bullet-done">✔</span> Add breadcrumbs function</span>
				<span class="done"> <span class="bullet-done">✔</span> Improve popup notification style</span>
				<span class="done"> <span class="bullet-done">✔</span> Dont show delete button if post already trashed</span>
				<span class="done"> <span class="bullet-done">✔</span> Add permanenet delete button from frontend</span>
				<span class="done"> <span class="bullet-done">✔</span> error 404 when no trailing slash</span>
				<span class="done"> <span class="bullet-done">✔</span> highlight trashed post</span>
				<span class="done"> <span class="bullet-done">✔</span> Changes author when editing a question</span>
				<span class="done"> <span class="bullet-done">✔</span> Private answers are not visible to its author</span>
				<span class="done"> <span class="bullet-done">✔</span> Improve editor mode toggle buttons</span>
				<span class="done"> <span class="bullet-done">✔</span> Add loading animation in post/edit comment button</span>
				<span class="done"> <span class="bullet-done">✔</span> Improve subscribe button</span>
				<span class="done"> <span class="bullet-done">✔</span> disable view count ap_meta entry</span>
				<span class="done"> <span class="bullet-done">✔</span> Add icons in stats widget</span>
				<span class="done"> <span class="bullet-done">✔</span> Check comments as general user</span>
				<span class="done"> <span class="bullet-done">✔</span> Show question filter tab on Category and Tag pages</span>
				<span class="done"> <span class="bullet-done">✔</span> BuddyPress not working</span>
				<span class="done"> <span class="bullet-done">✔</span> Short category dropdown in ask form as defined</span>
				<span class="done"> <span class="bullet-done">✔</span> Rename "voted" tab to vote</span>
				<span class="done"> <span class="bullet-done">✔</span> Unsolved also shows questions with selected answers</span>
				<span class="done"> <span class="bullet-done">✔</span> On Single Question page  , Active | Voted | Newest | Oldest should be ajax based</span>
				<span class="done"> <span class="bullet-done">✔</span> Ask page title option not working</span>
				<span class="done"> <span class="bullet-done">✔</span> Dont let reputation to be negative</span>
				<span class="done"> <span class="bullet-done">✔</span> Improve UI of button</span>
				<span class="done"> <span class="bullet-done">✔</span> Add sticky answer navigation</span>
				<span class="done"> <span class="bullet-done">✔</span> Comment loading shows success</span>
				<span class="done"> <span class="bullet-done">✔</span> Add answer form help content</span>
				<span class="done"> <span class="bullet-done">✔</span> Improve answer form UI</span>
				<span class="done"> <span class="bullet-done">✔</span> Added fullscreen toggle in Answer form</span>
				<span class="done"> <span class="bullet-done">✔</span> Improve UI of question page</span>
				<span class="done"> <span class="bullet-done">✔</span> Check UI of comments when comments are shown by default</span>
				<span class="done"> <span class="bullet-done">✔</span> Change font weight of question and answer action links</span>
				<span class="done"> <span class="bullet-done">✔</span> Improve UI of comments</span>
				<span class="done"> <span class="bullet-done">✔</span> Pending comment is highlighted</span>
				<span class="done"> <span class="bullet-done">✔</span> Add comment author name and time</span>
				<span class="done"> <span class="bullet-done">✔</span> Commentrs are not subscribed by default</span>
				<span class="done"> <span class="bullet-done">✔</span> User is not unsubscribed when his comment or answer is deleted</span>
				<span class="done"> <span class="bullet-done">✔</span> Anonymous cant answer if question is created by anonymous</span>
				<span class="done"> <span class="bullet-done">✔</span> Reputation showing wrong count</span>
				<span class="done"> <span class="bullet-done">✔</span> white space when user don't have permission to view answers</span>
				<span class="done"> <span class="bullet-done">✔</span> Improve admin answer table</span>
				<span class="done"> <span class="bullet-done">✔</span> Improve admin question table</span>
				<span class="done"> <span class="bullet-done">✔</span> Added image from link in question and answer</span>
				<span class="done"> <span class="bullet-done">✔</span> Add image uploader in question and answer</span>
				</div>
			</div>
			<div class="col-md-4">
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
				<div class="ap-dash-tile clearfix">
					<div class="anspress-links">
						<a href="http://anspress.io/" target="_blank"><i class="apicon-anspress-icon"></i><?php _e('About AnsPress', 'anspress-question-answer'); ?></a>
						<a href="http://anspress.io/questions/" target="_blank"><i class="apicon-question"></i><?php _e('Support Q&A', 'anspress-question-answer'); ?></a>
						<a href="http://anspress.io/themes/" target="_blank"><i class="apicon-info"></i><?php _e('AnsPress Themes', 'anspress-question-answer'); ?></a>
						<a href="http://github.com/anspress/anspress" target="_blank"><i class="apicon-mark-github"></i><?php _e('Github Repo', 'anspress-question-answer'); ?></a>
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
</div>