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
							<a target="_blank" href="<?php the_permalink(); ?>"><?php echo esc_html( get_the_title() ); ?></a> -
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

		$results = $wpdb->get_results( "SELECT date_format(post_date, '%d %a') as post_day, post_date, count(ID) as post_count from {$wpdb->posts} WHERE post_status IN('publish', 'private_post', 'moderate') AND post_type = 'answer' AND post_date > (NOW() - INTERVAL 1 MONTH) GROUP BY post_day ORDER BY post_date ASC" ); // phpcs:ignore WordPress.DB

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
							<a target="_blank" href="<?php the_permalink(); ?>"><?php echo esc_html( get_the_title() ); ?></a> -
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
				<svg preserveAspectRatio="xMidYMin slice" viewBox="0 0 1232 240" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
					<g clip-path="url(#a)">
						<path class="curve" d="M1430.91 497.569c63.48-63.482 112.65-137.548 146.13-220.1 32.34-79.71 48.73-163.936 48.73-250.299 0-86.362-16.39-170.588-48.73-250.298-33.48-82.573-82.65-156.618-146.13-220.1-63.48-63.482-137.55-112.651-220.1-146.135-79.71-32.336-163.94-48.725-250.301-48.725-86.363 0-170.589 16.389-250.299 48.725-82.573 33.484-156.618 82.653-220.1 146.135-63.481 63.482-112.65 137.547-146.135 220.1C311.64-143.418 295.25-59.192 295.25 27.19c0 86.383 16.39 170.589 48.725 250.299 33.485 82.573 82.654 156.618 146.135 220.1a683.438 683.438 0 0 0 14.475 14.031l85.576-85.577a560.502 560.502 0 0 1-14.535-13.99C472.814 309.24 416.206 172.56 416.206 27.17c0-145.389 56.608-282.069 159.42-384.882 102.813-102.813 239.494-159.42 384.883-159.42 145.391 0 282.071 56.607 384.881 159.42 102.81 102.813 159.42 239.493 159.42 384.882 0 145.39-56.61 282.07-159.42 384.883L861.587 895.857H747.545l540.815-540.815c87.57-87.572 135.81-204.013 135.81-327.851 0-123.84-48.22-240.28-135.81-327.852-87.57-87.572-204.01-135.814-327.851-135.814-123.839 0-240.28 48.222-327.852 135.814C545.085-213.069 496.844-96.648 496.844 27.19c0 123.839 48.221 240.28 135.813 327.852 4.758 4.758 9.636 9.374 14.575 13.93l85.637-85.637c-5.019-4.475-9.938-9.072-14.696-13.829-133.616-133.616-133.616-351.035 0-484.671 133.616-133.616 351.037-133.616 484.667 0 64.74 64.731 100.38 150.792 100.38 242.335 0 91.544-35.64 177.604-100.38 242.336L576.493 895.857H462.452l683.378-683.362c102.19-102.188 102.19-268.442 0-370.629-49.49-49.492-115.31-76.767-185.301-76.767-69.993 0-135.814 27.255-185.305 76.767-49.491 49.491-76.767 115.311-76.767 185.304 0 69.994 27.256 135.814 76.767 185.305a262.783 262.783 0 0 0 14.797 13.708l86.02-86.02a143.305 143.305 0 0 1-15.281-13.224c-26.65-26.651-41.326-62.09-41.326-99.789 0-37.698 14.676-73.138 41.326-99.789 26.651-26.65 62.091-41.326 99.789-41.326s73.141 14.676 99.791 41.326c55.01 55.015 55.01 144.543 0 199.578L295.29 891.986v124.804h1330.52V895.837h-593.13l398.27-398.268h-.04ZM-1234.11-301.729c-82.74 82.734-146.8 179.217-190.43 286.787-42.11 103.881-63.46 213.608-63.46 326.178s21.35 222.297 63.48 326.158c43.63 107.571 107.69 204.053 190.43 286.787 82.73 82.739 179.21 146.799 286.784 190.429 103.861 42.11 213.608 63.48 326.158 63.48 112.55 0 222.297-21.35 326.158-63.48 107.57-43.63 204.053-107.69 286.787-190.429 82.734-82.734 146.8-179.216 190.425-286.787 42.113-103.861 63.482-213.608 63.482-326.158 0-112.549-21.349-222.297-63.482-326.158C138.597-122.492 74.531-218.975-8.203-301.709c-53.382-53.382-112.711-99.063-177.08-136.519l-88.963 88.963c66.284 34.815 126.883 79.448 180.527 133.092C47.155-75.299 124.748 112.021 124.748 311.256c0 199.235-77.593 386.556-218.467 527.43-140.873 140.873-328.194 218.464-527.429 218.464-199.235 0-386.552-77.591-527.432-218.464-140.87-140.874-218.46-328.195-218.46-527.43 0-199.235 77.59-386.555 218.46-527.429L-484.77-880h-171.052l-578.288 578.271Z" fill="#213FD4"></path>
						<path class="curve" d="M85.415-880H-85.637L-949.02-16.635c-87.569 87.572-135.809 204.012-135.809 327.851s48.22 240.28 135.809 327.852c87.572 87.572 204.012 135.813 327.851 135.813s240.3-48.241 327.852-135.813c87.572-87.572 135.813-204.013 135.813-327.852 0-123.839-48.221-240.28-135.813-327.872-55.701-55.68-123.073-95.434-196.574-117.025L-593.209-30.364c81 6.491 156.275 41.145 214.375 99.245 64.731 64.731 100.373 150.792 100.373 242.335 0 91.544-35.642 177.604-100.373 242.336-64.732 64.731-150.792 100.373-242.336 100.373-91.544 0-177.604-35.642-242.335-100.373-64.732-64.732-100.374-150.792-100.374-242.336 0-91.543 35.642-177.604 100.374-242.335L85.415-880Z" fill="#213FD4"></path>
						<path class="dot" d="M961 40c16.569 0 30-13.431 30-30 0-16.569-13.431-30-30-30-16.569 0-30 13.431-30 30 0 16.569 13.431 30 30 30Z" fill="#213FD4"></path>
					</g>
					<defs>
						<clipPath id="a">
						<path fill="#fff" d="M0 0h1232v240H0z"></path>
						</clipPath>
					</defs>
					</svg>
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
