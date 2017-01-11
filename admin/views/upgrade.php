<?php
/**
 * Control the output of AnsPress migrate page
 *
 * @link    https://anspress.io
 * @since   4.0.0
 * @author  Rahul Aryan <support@anspress.io>
 * @package AnsPress
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$done = get_option( 'anspress_updates', [] );

$tasks = array(
	'post_data'     => [ 'label' => __( 'Questions and Answers', 'anspress-question-answer' ), 'icon' => 'apicon-question' ],
	'reputations'   => [ 'label' => __( 'Migrate reputation data', 'anspress-question-answer' ), 'icon' => 'apicon-reputation' ],
	'category'   => [ 'label' => __( 'Migrate category data', 'anspress-question-answer' ), 'icon' => 'apicon-category' ],
);

?>
<div class="wrap anspress-upgrade">
	<div class="anspress-upgrade-in">
		<h2><?php _e( 'Thank you for choosing AnsPress', 'anspress-question-answer' ); ?></h2>
		<p class="upgrade-desc"><?php _e( 'This page will help you with automatically migrating AnsPress 3.x data to 4.x. Please make sure to create a backup of site before starting upgrade.', 'anspress-question-answer' ); ?></p>
		<a href="#" id="do-tasks" class="button start-process"><?php _e( 'Start Upgrade', 'anspress-question-answer' ); ?></a>
	</div>
		<div class="error-happen"><?php _e( 'Aaiyaa! Something went wrong. Please click above button again or report issue to us.', 'anspress-question-answer' ); ?></div>
		<div class="ap-tasks">
			<?php foreach ( (array) $tasks as $slug => $task ) : ?>
				<div class="ap-task<?php echo isset( $done[ $slug ] ) && $done[ $slug ] ? ' done' : ''; ?>" data-task="<?php echo $slug; ?>">
					<i class="<?php echo esc_attr( $task['icon'] ); ?>"></i>
					<div class="ap-task-name"><?php echo esc_attr( $task['label'] ); ?></div>
					<span class="<?php echo isset( $done[ $slug ] ) && $done[ $slug ] ? 'apicon-check' : 'apicon-clock'; ?>"></span>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="support-link">
			<div><?php _e( 'Having trouble upgrading? We are available 12x7', 'anspress-question-answer' ); ?></div>
			<a href="https://anspress.io/questions/" target="_blank" class="button"><?php _e( 'Get help now', 'anspress-question-answer' ); ?></a>
		</div>
		<div class="copyright">Coded with &hearts; by Rahul Aryan and <a href="https://anspress.io/contributors/" target="_blank">Contributors</a></div>

</div>

<script type="text/javascript">
	jQuery(document).ready(function($){
		var running = false;
		var count = 0;
		var doTask = function(init){
			init = init||false;
			running = true;
			count++;
			AnsPress.ajax({
				url: '<?php echo admin_url( "admin-ajax.php" ); ?>',
				data: {action: 'ap_migrator_4x', '__nonce' : "<?php echo wp_create_nonce( 'ap_migration' ); ?>", init: init},
				success: function(data){
					running = false;
					if(data.success){
						if(data.active){
							$('[data-task="'+data.active+'"]').addClass('working');
							if(data.message !== '')
								$('[data-task="'+data.active+'"] .ap-task-name').text(data.message);
						}

						if(data.status){
							$.each(data.status, function(k, st){
								if(st){
									$('[data-task="'+k+'"]').addClass('done').removeClass('working');
									$('[data-task="'+k+'"] span').attr('class', 'apicon-check');
								}

								if(!st && !running && data.continue && count < 20){
									doTask();
								}

								if(count === 20){
									$('.error-happen').css('display', 'table');
								}
							});
						}
					}
				},
				error: function(){
					console.log('failed');
				}
			});
		};

		$('#do-tasks').click(function(e){
			e.preventDefault();
			count = 0;
			$('.error-happen').hide();
			doTask(true);
		})
	});
</script>
