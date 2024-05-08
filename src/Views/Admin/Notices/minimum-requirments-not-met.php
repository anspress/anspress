<?php
/**
 * Shows notice if minimum requirements are not met.
 *
 * @package AnsPress\Core
 * @since 5.0.0
 * @codeCoverageIgnore
 */

use AnsPress\Core\Classes\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$requirmentErrors = Plugin::getRequirmentErrors()

?>
<div class="notice notice-error">
	<p>
		<?php
		if ( $requirmentErrors ) {
			foreach ( $requirmentErrors as $err ) {
				echo esc_html( $err ) . '<br>';
			}
		}
		?>
	</p>
</div>
<?php
