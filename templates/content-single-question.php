<?php
/**
 * This file is responsible for displaying single question page.
 * This file can be overridden by creating a anspress directory in active theme folder.
 *
 * @package    AnsPress
 * @subpackage Templates
 * @license    https://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 * @author     Rahul Aryan <rah12@live.com>
 *
 * @since      4.2.0
 */

namespace AnsPress\Post;

?>
<div id="ap-single" class="ap-q clearfix">

	<div class="ap-question-lr ap-row" itemtype="https://schema.org/Question" itemscope="">
		<div class="ap-q-left <?php echo ( is_active_sidebar( 'ap-qsidebar' ) ) ? 'ap-col-8' : 'ap-col-12'; ?>">
			<?php
			ap_get_template_part( 'loop-single-question' );

			if ( ap_has_answers() ) {
				ap_get_template_part( 'loop-answers' );
			}

			// Get answers.
			//ap_answers();

			// Get answer form.
			ap_get_template_part( 'answer-form' );
			?>
		</div>

		<?php if ( is_active_sidebar( 'ap-qsidebar' ) ) : ?>
			<div class="ap-question-right ap-col-4">
				<div class="ap-question-info">
					<?php dynamic_sidebar( 'ap-qsidebar' ); ?>
				</div>
			</div>
		<?php endif; ?>

	</div>
</div>
