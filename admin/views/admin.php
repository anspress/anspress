<?php

/**
 * AnsPress options page
 *
 * 
 * @link http://wp3.in/anspress
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
$navigation = array(
	'general'		=> __('General', 'ap'),
	'questions'		=> __('Questions', 'ap'),
	'answers'		=> __('Answers', 'ap'),
	'layout'		=> __('Layout', 'ap'),
	'user'			=> __('User', 'ap'),
	'permission'	=> __('Permission', 'ap'),
	'pages'			=> __('Pages', 'ap'),
	'spam'			=> __('Spam and Moderate', 'ap'),
);


/**
 * FILTER: ap_option_navigation
 * For filtering AnsPress option navigation
 */
$navigation = apply_filters('ap_option_navigation', $navigation );

if ( ! isset( $_REQUEST['settings-updated'] ) )
	$_REQUEST['settings-updated'] = false; // This checks whether the form has just been submitted. ?>

<div class="wrap">
	<?php screen_icon(); echo '<h2>' . __( 'AnsPress Options' ) . '</h2>';
	// This shows the page's name and an icon if one has been provided ?>
			
	<?php if ( false !== $_REQUEST['settings-updated'] ) : ?>
	<div class="updated fade"><p><strong><?php _e( 'Options saved', 'ap' ); ?></strong></p></div>
	<?php endif; // If the form has just been submitted, this shows the notification ?>
	
	<!-- TODO: Remove this donation section if not needed -->
	<div class="get-support">
		<strong>Need more help ? feel free to ask for support. </strong>
		<a href="http://wp3.in">Support Forum</a>
	</div>
	<div class="doante-to-anspress">
		<h3>Help us keep AnsPress open source, free and full functional without any limitations</h3>
		<a href="https://www.paypal.com/cgi-bin/webscr?business=rah12@live.com&cmd=_xclick&item_name=Donation%20to%20AnsPress%20development" target="_blank"><img src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" alt="" /></a>
	</div>

	<form method="post" action="" id="ap-options">
		<!-- STYLE: Option tab -->
		<ul id="ap_opt_nav" class="nav nav-tabs">
		<?php 
			$active = (isset($_REQUEST['option_page'])) ? $_REQUEST['option_page'] : 'general' ;
			foreach ($navigation as $key => $title) {
				$class = ($active == $key) ? ' class="active"' : '' ;
				echo '<li'.$class.'><a href="'. admin_url( "admin.php?page=anspress_options&option_page={$key}") .'">'. $title .'</a></li>';
			}
		?>
		</ul>

		<!-- Tab panes -->
		<!-- STYLE: Style option pane -->
		<div class="tab-content">
			<?php 
				/**
				 * ACTION: ap_option_fields
				 * action used to show option fields
				 */
				do_action('ap_option_fields', $settings);
			 ?>			
			<!-- TODO: move this to LABEL extension -->
			<!-- <div class="tab-pane" id="ap-labels">
				<table class="form-table">					
					<tr valign="top">
						<th scope="row"><label for="default_label"><?php _e('Default label', 'ap'); ?></label></th>
						<td>
							<?php

								$terms = get_terms( 'question_label', array( 'hide_empty' => false, 'orderby' => 'id' ) );
								if ( !empty( $terms ) ) {
									echo '<select name="anspress_opt[default_label]">';
									foreach ( $terms as $term ) { ?>
										<option value="<?php echo esc_attr( $term->term_id ); ?>" <?php selected(  $settings['default_label'], $term->term_id ); ?>><?php echo esc_attr( $term->name ); ?></option>
									<?php }
									echo '</select>';
								}

								/* If there are no rank terms, display a message. */
								else {
									_e( 'There are no labels available.', 'ap' );
								}
							?>
							<p class="description"><?php _e('Assign a default label for new questions', 'ap'); ?></p>
						</td>
					</tr>
				</table>
			</div>	 -->		
			
			<p class="submit"><input type="submit" class="button-primary" value="Save Options" /></p>
		</div>		
		<input type="hidden" value="ap_save_options" name="action"/>
	</form>
</div>