<?php
	/**
	 * Show lists of available extensions from AnsPress server
	 * @package AnsPress
	 * @since 2.0.0-alpha2
	 * @license GPL 2+
	 * @author Rahul Aryan <rh12@live.com>
	 */
	
	// If this file is called directly, abort.
	if ( ! defined( 'WPINC' ) ) {
		die;
	}
	$extensions = new AnsPress_Extensions;
?>
<div class="wrap">
	<h2>
		<?php _e('AnsPress Extensions', 'anspress-question-answer'); ?></span>
	</h2>
	<form method="post" action="" id="plugin-filter">
		<input type="hidden" value="/anspress/wp-admin/plugin-install.php?tab=search&amp;s=search" name="_wp_http_referer">
			<div class="wp-list-table widefat plugin-install">
				<div id="the-list">
					<?php $extensions->extensions_lists() ?>
				</div>
			</div>
	</form>
</div>