<?php
/**
 * Control the output of AnsPress dashboard
 *
 * @link https://anspress.net
 * @since 2.0.0
 * @author Rahul Aryan <rah12@live.com>
 * @package AnsPress
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Dashboard class.
 */
class AnsPress_Dashboard {
	/**
	 * Init class.
	 */
	public static function init() {
		add_action( 'admin_footer', array( __CLASS__, 'admin_footer' ) );

		add_meta_box( 'ap-mb-attn', '<i class="apicon-alert"></i>' . __( 'Require Attention', 'anspress-question-answer' ), array( __CLASS__, 'anspress_attn' ), 'anspress', 'column1', 'core' );

		add_meta_box( 'ap-mb-qstats', '<i class="apicon-question"></i>' . __( 'Questions', 'anspress-question-answer' ), array( __CLASS__, 'anspress_stats' ), 'anspress', 'column2', 'core' );

		add_meta_box( 'ap-mb-latestq', __( 'Latest Questions', 'anspress-question-answer' ), array( __CLASS__, 'anspress_latestq' ), 'anspress', 'column2', 'core' );

		add_meta_box( 'ap-mb-astats', '<i class="apicon-answer"></i>' . __( 'Answer', 'anspress-question-answer' ), array( __CLASS__, 'anspress_astats' ), 'anspress', 'column3', 'core' );

		add_meta_box( 'ap-mb-latesta', __( 'Latest Answers', 'anspress-question-answer' ), array( __CLASS__, 'anspress_latesta' ), 'anspress', 'column3', 'core' );
	}

	/**
	 * Add javascript in dashboard footer.
	 */
	public static function admin_footer() {
		?>
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready( function($) {
				// close postboxes that should be closed
				$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
				// postboxes setup
				postboxes.add_postbox_toggles('anspress');
			});
			//]]>
		</script>
		<?php
	}

	/**
	 * Full AnsPress stats.
	 */
	public static function anspress_stats() {
		$question_count = ap_total_posts_count( 'question' );
		?>
		<div class="main">
			<ul>
				<li class="post-count">
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=question' ) ); ?>" class="publish">
						<?php printf( esc_attr__( '%d Published', 'anspress-question-answer' ), esc_attr( $question_count->publish ) ); ?>
					</a>
				</li>
				<li class="post-count">
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=question&post_status=private_post' ) ); ?>" class="private">
						<?php printf( esc_attr__( '%d Private', 'anspress-question-answer' ), esc_attr( $question_count->private_post ) ); ?>
					</a>
				</li>
				<li class="post-count">
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=question&post_status=moderate' ) ); ?>" class="moderate"><?php printf( esc_attr__( '%d Moderate', 'anspress-question-answer' ), esc_attr( $question_count->moderate ) ); ?></a>
				</li>
			</ul>
		</div>
		<?php
	}

	/**
	 * Show latest questions.
	 */
	public static function anspress_latestq() {
		global $wpdb;

		$results = $wpdb->get_results( "SELECT date_format(post_date, '%d %a') as post_day, post_date, count(ID) as post_count from {$wpdb->posts} WHERE post_status IN('publish', 'private_post', 'moderate') AND post_type = 'question' AND post_date > (NOW() - INTERVAL 1 MONTH) GROUP BY post_day ORDER BY post_date ASC" ); // db call okay, cache okay.

		$days   = array();
		$counts = array();

		foreach ( (array) $results as $r ) {
			$days[]   = $r->post_day;
			$counts[] = $r->post_count;
		}
		?>
		<?php if ( $results ) : ?>

		<?php endif; ?>
		<div class="main">

			<?php
			anspress()->questions = ap_get_questions(
				array(
					'ap_order_by' => 'newest',
					'showposts'   => 5,
				)
			);
?>

			<?php if ( ap_have_questions() ) : ?>
				<ul class="post-list">
					<?php
					while ( ap_have_questions() ) :
						ap_the_question();
?>
						<li>
							<a target="_blank" href="<?php the_permalink(); ?>"><?php the_title(); ?></a> -
							<span class="posted"><?php the_date(); ?></span>
						</li>
					<?php endwhile; ?>
				</ul>
			<?php endif; ?>

			<?php wp_reset_postdata(); ?>
		</div>
		<?php
	}

	/**
	 * Show latest answers.
	 */
	public static function anspress_latesta() {
		global $answers, $wpdb;

		$results = $wpdb->get_results( "SELECT date_format(post_date, '%d %a') as post_day, post_date, count(ID) as post_count from {$wpdb->posts} WHERE post_status IN('publish', 'private_post', 'moderate') AND post_type = 'answer' AND post_date > (NOW() - INTERVAL 1 MONTH) GROUP BY post_day ORDER BY post_date ASC" ); // db call okay, cache ok.

		$days   = array();
		$counts = array();

		foreach ( (array) $results as $r ) {
			$days[]   = $r->post_day;
			$counts[] = $r->post_count;
		}
		?>
		<?php if ( $results ) : ?>
		<?php endif; ?>
		<div class="main">
			<?php
			$answers = ap_get_answers(
				array(
					'ap_order_by' => 'newest',
					'showposts'   => 5,
				)
			);
?>

			<?php if ( ap_have_answers() ) : ?>
				<ul class="post-list">
					<?php
					while ( ap_have_answers() ) :
						ap_the_answer();
?>
						<li>
							<a target="_blank" href="<?php the_permalink(); ?>"><?php the_title(); ?></a> -
							<span class="posted"><?php the_date(); ?></span>
						</li>
					<?php endwhile; ?>
				</ul>
			<?php endif; ?>

			<?php wp_reset_postdata(); ?>
		</div>
		<?php
	}

	/**
	 * Show items which need attention.
	 */
	public static function anspress_attn() {
		$q_flagged_count = ap_total_posts_count( 'question', 'flag' );
		$a_flagged_count = ap_total_posts_count( 'answer', 'flag' );
		$question_count  = wp_count_posts( 'question', 'readable' );
		$answer_count    = wp_count_posts( 'answer', 'readable' );
		?>
		<div class="main attn">
			<?php if ( $q_flagged_count->total || $question_count->moderate ) : ?>
				<strong><?php esc_attr_e( 'Questions', 'anspress-question-answer' ); ?></strong>
				<ul>
					<?php if ( $q_flagged_count->total ) : ?>
					<li><a href=""><i class="apicon-flag"></i><?php printf( __( '%d Flagged questions', 'anspress-question-answer' ), $q_flagged_count->total ); ?></a></li>
					<?php endif; ?>
					<?php if ( $question_count->moderate ) : ?>
					<li><a href=""><i class="apicon-stop"></i><?php printf( __( '%d questions awaiting moderation', 'anspress-question-answer' ), $question_count->moderate ); ?></a></li>
					<?php endif; ?>
				</ul>
			<?php else : ?>
				<?php esc_attr_e( 'All looks fine', 'anspress-question-answer' ); ?>
			<?php endif; ?>

			<?php if ( $a_flagged_count->total || $answer_count->moderate ) : ?>
				<strong><?php _e( 'Answers', 'anspress-question-answer' ); ?></strong>
				<ul>
					<?php if ( $a_flagged_count->total ) : ?>
						<li>
							<a href=""><i class="apicon-flag"></i>
							<?php printf( __( '%d Flagged answers', 'anspress-question-answer' ), $a_flagged_count->total ); ?>
							</a>
						</li>
					<?php endif; ?>

					<?php if ( $answer_count->moderate ) : ?>
						<li><a href=""><i class="apicon-stop"></i><?php printf( __( '%d answers awaiting moderation', 'anspress-question-answer' ), $answer_count->moderate ); ?></a></li>
					<?php endif; ?>

				</ul>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Total Answer stats.
	 */
	public static function anspress_astats() {
		global $answers;
		$answer_count = ap_total_posts_count( 'answer' );
		?>
		<div class="main">
			<ul>
				<li class="post-count">
					<a href="<?php echo admin_url( 'edit.php?post_type=answer' ); ?>" class="publish"><?php printf( __( '%d Published', 'anspress-question-answer' ), $answer_count->publish ); ?></a>
				</li>
				<li class="post-count">
					<a href="<?php echo admin_url( 'edit.php?post_type=answer&post_status=private_post' ); ?>" class="private"><?php printf( __( '%d Private', 'anspress-question-answer' ), $answer_count->private_post ); ?></a>
				</li>
				<li class="post-count">
					<a href="<?php echo admin_url( 'edit.php?post_type=answer&post_status=moderate' ); ?>" class="moderate"><?php printf( __( '%d Moderate', 'anspress-question-answer' ), $answer_count->moderate ); ?></a>
				</li>
			</ul>
		</div>
		<?php
	}

}

AnsPress_Dashboard::init();

// We need the global screen column value to beable to have a sidebar in WordPress 2.8.
global $screen_layout_columns;

$screen      = get_current_screen();
$columns     = absint( $screen->get_columns() );
$columns_css = '';

if ( $columns ) {
	$columns_css = " columns-$columns";
}

?>

<div id="anspress-metaboxes" class="wrap">
	<h1>AnsPress</h1>
	<div class="welcome-panel" id="welcome-panel">
		<div class="welcome-panel-content">
			<h2><?php _e( 'Welcome to AnsPress!', 'anspress-question-answer' ); ?></h2>
			<p class="about-description">
				<?php _e( 'We’ve assembled some links to get you started:', 'anspress-question-answer' ); ?>
			</p>
			<div class="welcome-panel-column-container">
				<div class="welcome-panel-column">
					<h3><?php _e( 'Get Started', 'anspress-question-answer' ); ?></h3>
					<a href="<?php echo admin_url( 'admin.php?page=anspress_options' ); ?>" class="button button-primary button-hero">
						<?php _e( 'AnsPress Options', 'anspress-question-answer' ); ?>
					</a>

				</div>
				<div class="welcome-panel-column">
					<h3><?php _e( 'Next Steps', 'anspress-question-answer' ); ?></h3>
					<ul>
						<li><a class="welcome-icon welcome-write-blog" href="<?php echo admin_url( 'post-new.php?post_type=question' ); ?>"><?php _e( 'Write your first question', 'anspress-question-answer' ); ?></a></li>
						<li><a class="welcome-icon welcome-add-page" href="<?php echo admin_url( 'admin.php?page=ap_select_question' ); ?>"><?php _e( 'Post an answer', 'anspress-question-answer' ); ?></a></li>
						<li><a class="welcome-icon welcome-view-site" href="<?php echo ap_get_link_to( '/' ); ?>"><?php _e( 'View questions', 'anspress-question-answer' ); ?></a></li>
					</ul>
				</div>
				<div class="welcome-panel-column welcome-panel-last">
					<h3><?php _e( 'More actions', 'anspress-question-answer' ); ?></h3>
					<ul>
						<li>
							<div class="welcome-icon welcome-widgets-menus">
								<?php
								printf(
									__( 'Get %1$s or %2$s', 'anspress-question-answer' ),
									sprintf(
										'<a href="%1$s" target="_blank">%2$s</a>',
										esc_url( 'https://anspress.net/themes/' ),
										__( 'Themes', 'anspress-question-answer' )
									),
									sprintf(
										'<a href="%1$s" target="_blank">%2$s</a>',
										esc_url( 'https://anspress.net/extensions/' ),
										__( 'Extensions', 'anspress-question-answer' )
									)
								);
								?>
							</div>
						</li>
						<li><a class="welcome-icon welcome-comments" href="https://anspress.net/questions/" target="_blank"><?php _e( 'Help and Support!', 'anspress-question-answer' ); ?></a></li>
						<li><a class="welcome-icon welcome-learn-more" href="https://anspress.net/docs/"><?php _e( 'Documents and FAQ', 'anspress-question-answer' ); ?></a></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<div id="dashboard-widgets-wrap">
		<div id="dashboard-widgets" class="metabox-holder<?php echo $columns_css; ?>">
			<div id="postbox-container-1" class="postbox-container">
			<?php do_meta_boxes( 'anspress', 'column1', '' ); ?>
			</div>
			<div id="postbox-container-2" class="postbox-container">
			<?php do_meta_boxes( 'anspress', 'column2', '' ); ?>
			</div>
			<div id="postbox-container-3" class="postbox-container">
			<?php do_meta_boxes( 'anspress', 'column3', '' ); ?>
			</div>
			<div id="postbox-container-4" class="postbox-container">
			<?php do_meta_boxes( 'anspress', 'column4', '' ); ?>
			</div>
		</div>
	</div>

</div>
<?php

wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
