<?php

/**
 * AnsPress options page
 *
 * 
 * @link http://anspress.io/anspress
 * @since 2.0.1
 * @package AnsPress
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$settings = ap_opt();


/**
 * Anspress option navigation
 * @var array
 */
?>

<div id="anspress" class="wrap">
	<?php screen_icon(); echo '<h2>' . __( 'AnsPress Options' ) . '</h2>';
	// This shows the page's name and an icon if one has been provided ?>
			
	<?php if ( @$_POST['anspress_opt_updated'] === true ) : ?>
		<div class="updated fade"><p><strong><?php _e( 'AnsPress options updated', 'ap' ); ?></strong></p></div>
	<?php endif; // If the form has just been submitted, this shows the notification ?>
	
	<div class="ap-wrap">
		<div class="anspress-options ap-wrap-left">
			<div class="option-nav-tab clearfix">
				<?php ap_options_nav(); ?>
			</div>
			<div class="ap-group-options">
				<?php ap_option_group_fields(); ?>
			</div>
		</div>
		<div class="ap-wrap-side">
			<div class="ap-dash-tile clearfix">
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