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
					'question_id' => 'all',
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
	 * Show items which need attention.
	 */
	public static function anspress_attn() {
		$q_flagged_count = ap_total_posts_count( 'question', 'flag' );
		$a_flagged_count = ap_total_posts_count( 'answer', 'flag' );
		$question_count  = wp_count_posts( 'question', 'readable' );
		$answer_count    = wp_count_posts( 'answer', 'readable' );
		?>
		<div class="main attn">
			<?php
			if ( ! $q_flagged_count->total && ! $question_count->moderate && ! $a_flagged_count->total && ! $answer_count->moderate ) :
				esc_attr_e( 'All looks fine', 'anspress-question-answer' );
			endif;
			?>

			<?php if ( $q_flagged_count->total || $question_count->moderate ) : ?>
				<strong><?php esc_attr_e( 'Questions', 'anspress-question-answer' ); ?></strong>
				<ul>
					<?php if ( $q_flagged_count->total ) : ?>
						<li>
							<a href="<?php echo esc_url( admin_url( 'edit.php?flagged=true&post_type=question' ) ); ?>"><i class="apicon-flag"></i>
							<?php
								// translators: Placeholder contains total flagged question count.
								echo esc_attr( sprintf( __( '%d Flagged questions', 'anspress-question-answer' ), $q_flagged_count->total ) );
							?>
							</a>
						</li>
					<?php endif; ?>

					<?php if ( $question_count->moderate ) : ?>
						<li>
							<a href="<?php echo esc_url( admin_url( 'edit.php?post_status=moderate&post_type=question' ) ); ?>"><i class="apicon-stop"></i>
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
			<?php endif; ?>

			<?php if ( $a_flagged_count->total || $answer_count->moderate ) : ?>
				<strong><?php esc_attr_e( 'Answers', 'anspress-question-answer' ); ?></strong>
				<ul>
					<?php if ( $a_flagged_count->total ) : ?>
						<li>
							<a href="<?php echo esc_url( admin_url( 'edit.php?flagged=true&post_type=answer' ) ); ?>">
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
							<a href="<?php echo esc_url( admin_url( 'edit.php?post_status=moderate&post_type=answer' ) ); ?>">
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
					<svg preserveAspectRatio="xMidYMin slice" fill="none" viewBox="0 0 1232 240" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
						<g clip-path="url(#a)">
							<path fill="#151515" d="M0 0h1232v240H0z"></path>
							<ellipse cx="616" cy="232" fill="url(#b)" opacity=".05" rx="1497" ry="249"></ellipse>
							<mask id="d" width="1000" height="400" x="232" y="20" maskUnits="userSpaceOnUse" style="mask-type:alpha">
							<path fill="url(#c)" d="M0 0h1000v400H0z" transform="translate(232 20)"></path>
							</mask>
							<g stroke-width="2" mask="url(#d)">
							<path stroke="url(#e)" d="M387 20v1635"></path>
							<path stroke="url(#f)" d="M559.5 20v1635"></path>
							<path stroke="url(#g)" d="M732 20v1635"></path>
							<path stroke="url(#h)" d="M904.5 20v1635"></path>
							<path stroke="url(#i)" d="M1077 20v1635"></path>
							</g>
						</g>
						<defs>
							<linearGradient id="e" x1="387.5" x2="387.5" y1="20" y2="1655" gradientUnits="userSpaceOnUse">
							<stop stop-color="#3858E9" stop-opacity="0"></stop>
							<stop offset=".297" stop-color="#3858E9"></stop>
							<stop offset=".734" stop-color="#3858E9"></stop>
							<stop offset="1" stop-color="#3858E9" stop-opacity="0"></stop>
							<stop offset="1" stop-color="#3858E9" stop-opacity="0"></stop>
							</linearGradient>
							<linearGradient id="f" x1="560" x2="560" y1="20" y2="1655" gradientUnits="userSpaceOnUse">
							<stop stop-color="#FFFCB5" stop-opacity="0"></stop>
							<stop offset="0" stop-color="#FFFCB5" stop-opacity="0"></stop>
							<stop offset=".297" stop-color="#FFFCB5"></stop>
							<stop offset=".734" stop-color="#FFFCB5"></stop>
							<stop offset="1" stop-color="#FFFCB5" stop-opacity="0"></stop>
							</linearGradient>
							<linearGradient id="g" x1="732.5" x2="732.5" y1="20" y2="1655" gradientUnits="userSpaceOnUse">
							<stop stop-color="#C7FFDB" stop-opacity="0"></stop>
							<stop offset=".297" stop-color="#C7FFDB"></stop>
							<stop offset=".693" stop-color="#C7FFDB"></stop>
							<stop offset="1" stop-color="#C7FFDB" stop-opacity="0"></stop>
							</linearGradient>
							<linearGradient id="h" x1="905" x2="905" y1="20" y2="1655" gradientUnits="userSpaceOnUse">
							<stop stop-color="#FFB7A7" stop-opacity="0"></stop>
							<stop offset=".297" stop-color="#FFB7A7"></stop>
							<stop offset=".734" stop-color="#FFB7A7"></stop>
							<stop offset="1" stop-color="#3858E9" stop-opacity="0"></stop>
							<stop offset="1" stop-color="#FFB7A7" stop-opacity="0"></stop>
							</linearGradient>
							<linearGradient id="i" x1="1077.5" x2="1077.5" y1="20" y2="1655" gradientUnits="userSpaceOnUse">
							<stop stop-color="#7B90FF" stop-opacity="0"></stop>
							<stop offset=".297" stop-color="#7B90FF"></stop>
							<stop offset=".734" stop-color="#7B90FF"></stop>
							<stop offset="1" stop-color="#3858E9" stop-opacity="0"></stop>
							<stop offset="1" stop-color="#7B90FF" stop-opacity="0"></stop>
							</linearGradient>
							<radialGradient id="b" cx="0" cy="0" r="1" gradientTransform="matrix(0 249 -1497 0 616 232)" gradientUnits="userSpaceOnUse">
							<stop stop-color="#3858E9"></stop>
							<stop offset="1" stop-color="#151515" stop-opacity="0"></stop>
							</radialGradient>
							<radialGradient id="c" cx="0" cy="0" r="1" gradientTransform="matrix(0 765 -1912.5 0 500 -110)" gradientUnits="userSpaceOnUse">
							<stop offset=".161" stop-color="#151515" stop-opacity="0"></stop>
							<stop offset=".682"></stop>
							</radialGradient>
							<clipPath id="a">
							<path fill="#fff" d="M0 0h1232v240H0z"></path>
							</clipPath>
						</defs>
					</svg>
				</div>
				<h2><?php esc_html_e( 'Welcome to AnsPress!', 'anspress-question-answer' ); ?></h2>
				<p><?php esc_html_e( 'We\'ve assembled some links to get you started:', 'anspress-question-answer' ); ?></p>
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
								<a class="welcome-icon welcome-learn-more" href="https://anspress.net/docs/" target="_blank">
									<?php esc_attr_e( 'Documentations and FAQ', 'anspress-question-answer' ); ?>
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
