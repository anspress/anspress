<?php
/**
 * This template is used when there is no questions found in question archive.
 *
 * @package    AnsPress
 * @subpackage Templates
 * @license    https://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 * @author     Rahul Aryan <support@anspress.io>
 * @since      4.1.0
 */

?>

<p class="ap-no-questions">
	<?php esc_attr_e( 'There are no questions matching your query or you do not have permission to read them.', 'anspress-question-answer' ); ?>
</p>

<?php ap_get_template_part( 'login-signup' ); ?>

