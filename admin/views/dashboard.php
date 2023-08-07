<?php
/**
 * Control the output of AnsPress dashboard
 *
 * @link https://anspress.net
 * @since 2.0.0
 * @author Rahul Aryan <rah12@live.com>
 * @package AnsPress
 * @since 4.2.0 Fixed: CS bugs.
 *
 * @todo Improve this page.
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
						<?php
							// translators: placeholder contain count.
							echo esc_attr( sprintf( __( '%d Published', 'anspress-question-answer' ), $question_count->publish ) );
						?>
					</a>
				</li>
				<li class="post-count">
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=question&post_status=private_post' ) ); ?>" class="private">
						<?php
							// translators: placeholder contain count.
							echo esc_attr( sprintf( __( '%d Private', 'anspress-question-answer' ), $question_count->private_post ) );
						?>
					</a>
				</li>
				<li class="post-count">
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=question&post_status=moderate' ) ); ?>" class="moderate">
						<?php
							// translators: placeholder contain count.
							echo esc_attr( sprintf( __( '%d Moderate', 'anspress-question-answer' ), $question_count->moderate ) );
						?>
					</a>
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

		$results = $wpdb->get_results( "SELECT date_format(post_date, '%d %a') as post_day, post_date, count(ID) as post_count from {$wpdb->posts} WHERE post_status IN('publish', 'private_post', 'moderate') AND post_type = 'question' AND post_date > (NOW() - INTERVAL 1 MONTH) GROUP BY post_day ORDER BY post_date ASC" ); // phpcs:ignore

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
							<span class="posted"><?php echo esc_html( get_the_date() ); ?></span>
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
						<li>
							<a href=""><i class="apicon-flag"></i>
							<?php
								// translators: Placeholder contains total flagged question count.
								echo esc_attr( sprintf( __( '%d Flagged questions', 'anspress-question-answer' ), $q_flagged_count->total ) );
							?>
							</a>
						</li>
					<?php endif; ?>

					<?php if ( $question_count->moderate ) : ?>
						<li>
							<a href=""><i class="apicon-stop"></i>
								<?php
									echo esc_attr(
										// translators: placeholder contains total question awaiting moderation.
										sprintf( __( '%d questions awaiting moderation', 'anspress-question-answer' ), $question_count->moderate )
									);
								?>
							</a>
						</li>
					<?php endif; ?>
				</ul>
			<?php else : ?>
				<?php esc_attr_e( 'All looks fine', 'anspress-question-answer' ); ?>
			<?php endif; ?>

			<?php if ( $a_flagged_count->total || $answer_count->moderate ) : ?>
				<strong><?php esc_attr_e( 'Answers', 'anspress-question-answer' ); ?></strong>
				<ul>
					<?php if ( $a_flagged_count->total ) : ?>
						<li>
							<a href="">
								<i class="apicon-flag"></i>
								<?php
									echo esc_attr(
										sprintf(
											// translators: placeholder contains total flagged answers count.
											__( '%d Flagged answers', 'anspress-question-answer' ),
											$a_flagged_count->total
										)
									);
								?>
							</a>
						</li>
					<?php endif; ?>

					<?php if ( $answer_count->moderate ) : ?>
						<li>
							<a href="">
								<i class="apicon-stop"></i>
								<?php
									echo esc_attr(
										sprintf(
											// translators: placeholder contains total awaiting moderation question/answer.
											__( '%d answers awaiting moderation', 'anspress-question-answer' ),
											$answer_count->moderate
										)
									);
								?>
							</a>
							</li>
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
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=answer' ) ); ?>" class="publish">
						<?php
							// translators: placeholder contains total number of published answer count.
							echo esc_attr( sprintf( __( '%d Published', 'anspress-question-answer' ), $answer_count->publish ) );
						?>
					</a>
				</li>
				<li class="post-count">
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=answer&post_status=private_post' ) ); ?>" class="private">
						<?php
							// translators: placeholder contains total numbers of private posts.
							echo esc_attr( sprintf( __( '%d Private', 'anspress-question-answer' ), $answer_count->private_post ) );
						?>
					</a>
				</li>
				<li class="post-count">
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=answer&post_status=moderate' ) ); ?>" class="moderate">
						<?php
							// translators: Placeholder contain total awaiting moderation answers count.
							echo esc_attr( sprintf( __( '%d Moderate', 'anspress-question-answer' ), $answer_count->moderate ) );
						?>
					</a>
				</li>
			</ul>
		</div>
		<?php
	}

}

AnsPress_Dashboard::init();

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
			<div class="welcome-panel-header">
				<div class="welcome-panel-header-image">
					<svg width="780" height="550" viewBox="0 0 780 550" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><g opacity=".5" fill="#273FCC" stroke="#627EFF" stroke-width="2" stroke-miterlimit="10"><circle cx="434.757" cy="71.524" r="66.1"></circle><circle cx="432.587" cy="43.138" r="66.1"></circle><circle cx="426.277" cy="16.14" r="66.1"></circle><circle cx="416.143" cy="-9.165" r="66.1"></circle><circle cx="402.53" cy="-32.447" r="66.1"></circle><circle cx="385.755" cy="-53.376" r="66.1"></circle><circle cx="116.864" cy="-53.072" r="66.1"></circle><circle cx="99.984" cy="-32.183" r="66.1"></circle><circle cx="86.278" cy="-8.953" r="66.1"></circle><circle cx="76.078" cy="16.3" r="66.1"></circle><circle cx="69.714" cy="43.23" r="66.1"></circle><circle cx="67.518" cy="71.524" r="66.1"></circle><circle cx="67.518" cy="71.524" r="66.1"></circle><circle cx="67.518" cy="99.05" r="66.1"></circle><circle cx="67.518" cy="126.577" r="66.1"></circle><circle cx="67.518" cy="154.09" r="66.1"></circle><circle cx="67.518" cy="181.617" r="66.1"></circle><circle cx="67.518" cy="209.143" r="66.1"></circle><circle cx="67.518" cy="236.67" r="66.1"></circle><circle cx="67.518" cy="264.196" r="66.1"></circle><circle cx="67.518" cy="291.722" r="66.1"></circle><circle cx="67.518" cy="319.236" r="66.1"></circle><circle cx="67.518" cy="346.762" r="66.1"></circle><circle cx="67.518" cy="374.289" r="66.1"></circle><circle cx="67.518" cy="374.831" r="66.1"></circle><circle cx="68.471" cy="393.565" r="66.1"></circle><circle cx="71.249" cy="411.757" r="66.1"></circle><circle cx="75.76" cy="429.315" r="66.1"></circle><circle cx="81.925" cy="446.146" r="66.1"></circle><circle cx="89.651" cy="462.17" r="66.1"></circle><circle cx="411.579" cy="464.073" r="66.1"></circle><circle cx="423.208" cy="438.98" r="66.1"></circle><circle cx="430.986" cy="412.008" r="66.1"></circle><circle cx="434.558" cy="383.517" r="66.1"></circle><circle cx="433.831" cy="354.43" r="66.1"></circle><circle cx="428.777" cy="326.428" r="66.1"></circle><circle cx="419.635" cy="300.078" r="66.1"></circle><circle cx="406.763" cy="275.725" r="66.1"></circle><circle cx="390.491" cy="253.698" r="66.1"></circle><circle cx="371.189" cy="234.369" r="66.1"></circle><circle cx="349.188" cy="218.054" r="66.1"></circle><circle cx="324.846" cy="205.124" r="66.1"></circle><circle cx="298.506" cy="195.896" r="66.1"></circle><circle cx="270.512" cy="190.739" r="66.1"></circle><circle cx="241.368" cy="189.986" r="66.1"></circle><circle cx="213.003" cy="193.754" r="66.1"></circle><circle cx="186.147" cy="201.739" r="66.1"></circle><circle cx="161.157" cy="213.559" r="66.1"></circle><circle cx="138.389" cy="228.882" r="66.1"></circle><circle cx="118.174" cy="247.352" r="66.1"></circle><circle cx="100.857" cy="268.599" r="66.1"></circle><circle cx="86.794" cy="292.264" r="66.1"></circle><circle cx="76.316" cy="318.019" r="66.1"></circle><circle cx="69.781" cy="345.466" r="66.1"></circle><circle cx="67.518" cy="374.289" r="66.1"></circle><circle cx="712.577" cy="449.729" r="66.1"></circle><circle cx="712.577" cy="428.072" r="66.1"></circle><circle cx="712.577" cy="406.403" r="66.1"></circle><circle cx="712.577" cy="384.733" r="66.1"></circle><circle cx="712.577" cy="363.077" r="66.1"></circle><circle cx="712.577" cy="341.408" r="66.1"></circle><circle cx="712.577" cy="319.738" r="66.1"></circle><circle cx="712.577" cy="298.069" r="66.1"></circle><circle cx="712.577" cy="276.412" r="66.1"></circle><circle cx="712.577" cy="254.743" r="66.1"></circle><circle cx="712.577" cy="233.073" r="66.1"></circle><circle cx="712.577" cy="211.417" r="66.1"></circle><circle cx="712.577" cy="189.748" r="66.1"></circle><circle cx="712.577" cy="168.078" r="66.1"></circle><circle cx="712.577" cy="146.422" r="66.1"></circle><circle cx="712.577" cy="124.753" r="66.1"></circle><circle cx="712.577" cy="103.083" r="66.1"></circle><circle cx="712.577" cy="81.413" r="66.1"></circle><circle cx="712.577" cy="59.757" r="66.1"></circle><circle cx="712.577" cy="38.088" r="66.1"></circle><circle cx="712.577" cy="16.418" r="66.1"></circle><circle cx="712.577" cy="-5.238" r="66.1"></circle><circle cx="712.577" cy="-26.907" r="66.1"></circle><circle cx="712.577" cy="-48.577" r="66.1"></circle><circle cx="662.966" cy="-44.161" r="66.1"></circle><circle cx="646.429" cy="-21.024" r="66.1"></circle><circle cx="629.893" cy="2.113" r="66.1"></circle><circle cx="613.356" cy="25.25" r="66.1"></circle><circle cx="596.819" cy="48.387" r="66.1"></circle><circle cx="580.282" cy="71.524" r="66.1"></circle><circle cx="580.282" cy="465.515" r="66.1"></circle></g></svg>
				</div>
				<h2><?php esc_html_e( 'Welcome to AnsPress!', 'anspress-question-answer' ); ?></h2>
				<p><?php esc_html_e( 'We’ve assembled some links to get you started:', 'anspress-question-answer' ); ?></p>
			</div>

			<div class="welcome-panel-column-container">
				<div class="welcome-panel-column">
					<i class="apicon-anspress-icon"></i>
					<div class="welcome-panel-column-content">
						<h3><?php esc_html_e( 'Get Started', 'anspress-question-answer' ); ?></h3>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=anspress_options' ) ); ?>" class="button button-primary button-hero">
							<?php esc_html_e( 'AnsPress Options', 'anspress-question-answer' ); ?>
						</a>
					</div>
				</div>
				<div class="welcome-panel-column">
					<i class="apicon-pencil"></i>
					<div class="welcome-panel-column-content">
						<h3><?php esc_html_e( 'Next Steps', 'anspress-question-answer' ); ?></h3>
						<ul>
							<li>
								<a class="welcome-icon welcome-write-blog" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=question' ) ); ?>">
									<?php esc_html_e( 'Write your first question', 'anspress-question-answer' ); ?>
								</a>
							</li>
							<li>
								<a class="welcome-icon welcome-add-page" href="<?php echo esc_url( admin_url( 'admin.php?page=ap_select_question' ) ); ?>">
									<?php esc_html_e( 'Post an answer', 'anspress-question-answer' ); ?>
								</a>
							</li>
							<li>
								<a class="welcome-icon welcome-view-site" href="<?php echo esc_url( ap_get_link_to( '/' ) ); ?>">
									<?php esc_html_e( 'View questions', 'anspress-question-answer' ); ?>
								</a>
							</li>
						</ul>
					</div>
				</div>

				<div class="welcome-panel-column">
					<i class="apicon-home"></i>
					<div class="welcome-panel-column-content">
						<h3><?php esc_html_e( 'More actions', 'anspress-question-answer' ); ?></h3>
						<ul>
							<li>
								<div class="welcome-icon welcome-widgets-menus">
									<?php
									printf(
										// translators: %1 is link to themes %2 is link to addons.
										wp_kses_post( 'Get %1$s or %2$s', 'anspress-question-answer' ),
										'<a href="' . esc_url( 'https://anspress.net/themes/' ) . '" target="_blank">' . esc_html__( 'Themes', 'anspress-question-answer' ) . '</a>',
										'<a href="' . esc_url( 'https://anspress.net/extensions/' ) . '" target="_blank">' . esc_html__( 'Extensions', 'anspress-question-answer' ) . '</a>'
									);
									?>
								</div>
							</li>
							<li>
								<a class="welcome-icon welcome-comments" href="https://anspress.net/questions/" target="_blank">
									<?php esc_html_e( 'Help and Support!', 'anspress-question-answer' ); ?>
								</a>
							</li>
							<li>
								<a class="welcome-icon welcome-learn-more" href="https://anspress.net/docs/">
									<?php esc_html_e( 'Documents and FAQ', 'anspress-question-answer' ); ?>
								</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="dashboard-widgets-wrap">
		<div id="dashboard-widgets" class="metabox-holder<?php echo esc_attr( $columns_css ); ?>">
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
