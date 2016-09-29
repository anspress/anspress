<?php
/**
 * Control the output of AnsPress dashboard
 *
 * @link https://anspress.io
 * @since 2.0.0-alpha2
 * @author Rahul Aryan <support@anspress.io>
 * @package AnsPress
 */

// If this file is called directly, abort.
if ( ! defined('WPINC' ) ) {
	die;
}

class AnsPress_Dashboard{
	function __construct() {
		add_action('admin_footer', array( __CLASS__, 'admin_footer' ) );

		add_meta_box('ap-mb-aboutauthor', '<i class="apicon-user"></i>'.__('About Author', 'anspress-question-answer' ), array( __CLASS__, 'anspress_aboutauthor' ), 'anspress', 'column1', 'core' );

		add_meta_box('ap-mb-attn', '<i class="apicon-alert"></i>'.__('Require Attention', 'anspress-question-answer' ), array( __CLASS__, 'anspress_attn' ), 'anspress', 'column1', 'core' );

		add_meta_box('anspress_feed', 'AnsPress Feed', array( __CLASS__, 'anspress_feed' ), 'anspress', 'column1', 'core' );

		add_meta_box('ap-mb-qstats', '<i class="apicon-question"></i>'.__('Questions', 'anspress-question-answer' ), array( __CLASS__, 'anspress_stats' ), 'anspress', 'column2', 'core' );

		add_meta_box('ap-mb-latestq',__('Latest Questions', 'anspress-question-answer' ), array( __CLASS__, 'anspress_latestq' ), 'anspress', 'column2', 'core' );

		add_meta_box('ap-mb-astats', '<i class="apicon-answer"></i>'.__('Answer', 'anspress-question-answer' ), array( __CLASS__, 'anspress_astats' ), 'anspress', 'column3', 'core' );

		add_meta_box('ap-mb-latesta', __('Latest Answers', 'anspress-question-answer' ), array( __CLASS__, 'anspress_latesta' ), 'anspress', 'column3', 'core' );

		add_meta_box('ap-mb-topusers', 'Top Users', array( __CLASS__, 'topusers' ), 'anspress', 'column4', 'core' );
	}

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

	public static function anspress_aboutauthor() {
		?>
			<div class="main about-author clearfix">
				<img src="https://www.gravatar.com/avatar/0c8cfd3bc56d97fe6bebc035fe9b8c80.jpg?s=50&r=g" />
				<div class="no-overflow">
					<a class="name" href="https://anspress.io/hire/">Rahul Aryan</a>
					<p>Passionate WordPress developer. With over 7+ years’ experience, able to develop interactive plugins and themes. Also possess great familiarity of WP-Cli, APIs and JSON.</p>
					<strong>Skills and Experiences</strong>
					<div class="skills">
						<span>WordPress</span>
						<span>Plugin Dev</span>
						<span>Theme Dev</span>
						<span>Node.js</span>
						<span>jQuery</span>
						<span>Angular.js</span>
						<span>RESTful</span>
						<span>WP-Cli</span>
						<span>RESTful</span>
						<span>LEMP Stack</span>
						<span>AWS</span>
					</div>
					<a href="mailto:rah12@live.com" class="button button-primary">Hire Me (Full time or for a project)</a>
				</div>
			</div>
		<?php
	}

	public static function anspress_stats() {
		$question_count = ap_total_posts_count('question' );
		?>
		<script>
			var questionChartData = {
			    labels: ["Published","Private","Closed","Moderate"],
			    datasets: [{
		            data: [<?php echo $question_count->publish.','.$question_count->private_post.','.$question_count->closed.','.$question_count->moderate; ?>],
		            backgroundColor: [
		                "#4d97fe",
		                "#929292",
		                "#ff6262",
		                "#f9a341"
		            ],
		            hoverBackgroundColor: [
		                "#ddd",
		                "#ddd",
		                "#ddd"
		            ]
		        }]
			};
		</script>
		<div class="main">
			<canvas id="question-chart"></canvas>
			<ul>
				<li class="post-count">
					<a href="<?php echo admin_url('edit.php?post_type=question' ); ?>" class="publish"><?php printf( __('%d Published', 'anspress-question-answer' ), $question_count->publish ); ?></a>
				</li>
				<li class="post-count">
					<a href="<?php echo admin_url('edit.php?post_type=question&post_status=private_post' ); ?>" class="private"><?php printf( __('%d Private', 'anspress-question-answer' ), $question_count->private_post ); ?></a>
				</li>
				<li class="post-count">
					<a href="<?php echo admin_url('edit.php?post_type=question&post_status=closed' ); ?>" class="closed"><?php printf( __('%d Closed', 'anspress-question-answer' ), $question_count->closed ); ?></a>
				</li>
				<li class="post-count">
					<a href="<?php echo admin_url('edit.php?post_type=question&post_status=moderate' ); ?>" class="moderate"><?php printf( __('%d Moderate', 'anspress-question-answer' ), $question_count->moderate ); ?></a>
				</li>
			</ul>
		</div>
		<?php
	}

	public static function anspress_latestq() {
		global $questions, $wpdb;

		$results = $wpdb->get_results("SELECT date_format(post_date, '%d %a') as post_day, post_date, count(ID) as post_count from {$wpdb->posts} WHERE post_status IN('publish', 'closed', 'private_post', 'moderate') AND post_type = 'question' AND post_date > (NOW() - INTERVAL 1 MONTH) GROUP BY post_day ORDER BY post_date ASC" );

		$days = array();
		$counts = array();

		foreach ( (array) $results as $r ) {
			$days[] = $r->post_day;
			$counts[] = $r->post_count;
		}
		?>
		<?php if ( $results ) :   ?>
		<script>
			var latestquestionChartData = {
				labels: [ <?php echo "'".implode("','", $days )."'"; ?> ],				
				datasets: [{
					backgroundColor: 'rgba(53, 209, 252, 0.3)',
					borderColor: 'rgba(53, 209, 252, 0.7)',
					data: [ <?php echo implode(',', $counts ); ?> ]
				}]
			};
		</script>
		<?php endif; ?>
		<div class="main">
		<canvas id="latestquestion-chart" height="80"></canvas>
		<?php $questions = ap_get_questions(array( 'sortby' => 'newest', 'showposts' => 5 ) ); ?>
			<?php if ( ap_have_questions() ) :   ?>
				<ul class="post-list">
					<?php while ( ap_questions() ) : ap_the_question(); ?>
						<li>
							<a target="_blank" href="<?php the_permalink(); ?>"><?php the_title( ); ?></a> - 
							<span class="posted"><?php the_date( ); ?></span>
						</li>
					<?php endwhile; ?>
				</ul>
			<?php endif;?>
			<?php wp_reset_postdata();?>
		</div>
		<?php
	}

	public static function anspress_latesta() {
		global $answers, $wpdb;

		$results = $wpdb->get_results("SELECT date_format(post_date, '%d %a') as post_day, post_date, count(ID) as post_count from {$wpdb->posts} WHERE post_status IN('publish', 'closed', 'private_post', 'moderate') AND post_type = 'answer' AND post_date > (NOW() - INTERVAL 1 MONTH) GROUP BY post_day ORDER BY post_date ASC" );

		$days = array();
		$counts = array();

		foreach ( (array) $results as $r ) {
			$days[] = $r->post_day;
			$counts[] = $r->post_count;
		}
		?>
		<?php if ( $results ) :   ?>
		<script>
			var latestanswerChartData = {
				labels: [ <?php echo "'".implode("','", $days )."'"; ?> ],				
				datasets: [{
					backgroundColor: 'rgba(78, 207, 158, 0.3)',
					borderColor: 'rgba(78, 207, 158, 0.7)',
					data: [ <?php echo implode(',', $counts ); ?> ]
				}]
			};
		</script>
		<?php endif; ?>
		<div class="main">
			<canvas id="latestanswer-chart" height="80"></canvas>
			<?php $answers = ap_get_answers(array( 'sortby' => 'newest', 'showposts' => 5 ) ); ?>
			<?php if ( ap_have_answers() ) :   ?>
				<ul class="post-list">
					<?php while ( ap_answers() ) : ap_the_answer(); ?>
						<li>
							<a target="_blank" href="<?php the_permalink(); ?>"><?php the_title( ); ?></a> - 
							<span class="posted"><?php the_date( ); ?></span>
						</li>
					<?php endwhile; ?>
				</ul>
			<?php endif;?>
			<?php wp_reset_postdata();?>
		</div>
		<?php
	}

	public static function anspress_attn() {
		$q_flagged_count = ap_total_posts_count( 'question', 'flag' );
		$a_flagged_count = ap_total_posts_count( 'answer', 'flag' );
		$question_count = wp_count_posts( 'question', 'readable' );
		$answer_count = wp_count_posts( 'answer', 'readable' );
		?>
		<div class="main attn">
			<?php if ( $q_flagged_count->total || $question_count->moderate ) :   ?>
			<strong><?php _e('Questions', 'anspress-question-answer' ); ?></strong>
			<ul>
				<?php if ( $q_flagged_count->total ) :   ?>
				<li><a href=""><i class="apicon-flag"></i><?php printf(__('%d Flagged questions', 'anspress-question-answer' ), $q_flagged_count->total ); ?></a></li>
				<?php endif; ?>
				<?php if ( $question_count->moderate ) :   ?>
				<li><a href=""><i class="apicon-stop"></i><?php printf(__('%d questions awaiting moderation', 'anspress-question-answer' ), $question_count->moderate ); ?></a></li>
				<?php endif; ?>
			</ul>
			<?php else : ?>
				<?php _e('All looks fine', 'anspress-question-answer' ); ?>
			<?php endif; ?>
			
			<?php if ( $a_flagged_count->total || $answer_count->moderate ) :   ?>
			<strong><?php _e('Answers', 'anspress-question-answer' ); ?></strong>
			<ul>
				<?php if ( $a_flagged_count->total ) :   ?>
				<li><a href=""><i class="apicon-flag"></i><?php printf(__('%d Flagged answers', 'anspress-question-answer' ), $a_flagged_count->total ); ?></a></li>
				<?php endif; ?>
				<?php if ( $answer_count->moderate ) :   ?>
				<li><a href=""><i class="apicon-stop"></i><?php printf(__('%d answers awaiting moderation', 'anspress-question-answer' ), $answer_count->moderate ); ?></a></li>
				<?php endif; ?>
			</ul>
			<?php endif; ?>
		</div>
		<?php
	}

	public static function anspress_astats() {
		global $answers;
		$answer_count = ap_total_posts_count('answer' );
		?>
		<script>
			var answerChartData = {
			    labels: ["Published","Private","Closed","Moderate"],
			    datasets: [{
		            data: [<?php echo $answer_count->publish.','.$answer_count->private_post.','.$answer_count->closed.','.$answer_count->moderate; ?>],
		            backgroundColor: [
		                "#4d97fe",
		                "#929292",
		                "#ff6262",
		                "#f9a341"
		            ],
		            hoverBackgroundColor: [
		                "#ddd",
		                "#ddd",
		                "#ddd"
		            ]
		        }]
			};
		</script>
		<div class="main">
			<canvas id="answer-chart"></canvas>
			<ul>
				<li class="post-count">
					<a href="<?php echo admin_url('edit.php?post_type=answer' ); ?>" class="publish"><?php printf( __('%d Published', 'anspress-question-answer' ), $answer_count->publish ); ?></a>
				</li>
				<li class="post-count">
					<a href="<?php echo admin_url('edit.php?post_type=answer&post_status=private_post' ); ?>" class="private"><?php printf( __('%d Private', 'anspress-question-answer' ), $answer_count->private_post ); ?></a>
				</li>
				<li class="post-count">
					<a href="<?php echo admin_url('edit.php?post_type=answer&post_status=closed' ); ?>" class="closed"><?php printf( __('%d Closed', 'anspress-question-answer' ), $answer_count->closed ); ?></a>
				</li>
				<li class="post-count">
					<a href="<?php echo admin_url('edit.php?post_type=answer&post_status=moderate' ); ?>" class="moderate"><?php printf( __('%d Moderate', 'anspress-question-answer' ), $answer_count->moderate ); ?></a>
				</li>
			</ul>
		</div>
		<?php
	}


	public static function anspress_feed() {
		$rss = fetch_feed( 'https://anspress.io/feed/' );
		set_transient( 'anspress_feed', $rss );

		?>
			<div class="anspress_feed">
				<?php
				if ( ! $rss->get_item_quantity() ) {
					echo '<p>'.__('Apparently, there are no updates to show!','anspress-question-answer' ).'</p>';
					$rss->__destruct();
					unset($rss );
					return;
				}
				?>
				<ul class="post-list">
					<?php foreach ( $rss->get_items(0, 5 ) as $item ) : ?>
						<li>
							<a target="_blank" href="<?php echo esc_url( strip_tags( $item->get_link() ) ); ?>"><?php echo esc_html( $item->get_title() ); ?></a> - 
							<span class="posted"><?php echo $item->get_date('j F Y | g:i a' ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
				<?php
					$rss->__destruct();
					unset($rss );
				?>
			</div>
		<?php
	}

	public static function topusers() {
		global $ap_user_query;
		$user_a = array(
			'number'    	=> 10,
			'sortby' 		=> 'reputation',
			'paged' 		=> 1,
		);

		// The Query.
		$ap_user_query = ap_has_users( $user_a );
		?>
		<div class="main">
			<?php while ( ap_users() ) : ap_the_user(); ?>
			<div class="ap-uw-summary clearfix" data-id="<?php ap_user_the_ID(); ?>">
				<a class="ap-users-avatar" href="<?php ap_user_the_link(); ?>">
					<?php ap_user_the_avatar( 40 )  ?>
				</a>
				<div class="no-overflow clearfix">
					<a class="ap-uw-name" href="<?php ap_user_the_link(); ?>"><?php ap_user_the_display_name(); ?></a>
					<div class="ap-uw-status">
						<span><?php printf(__('%s Rep.', 'anspress-question-answer' ), ap_user_get_the_reputation() ); ?></span>
						<span><?php printf(__('%d Best', 'anspress-question-answer' ), ap_user_get_the_meta('__best_answers' ) ); ?></span>
						<span><?php printf(__('%d Answers', 'anspress-question-answer' ), ap_user_get_the_meta('__total_answers' ) ); ?></span>
						<span><?php printf(__('%d Questions', 'anspress-question-answer' ), ap_user_get_the_meta('__total_questions' ) ); ?></span>
			        </div>
				</div>
			</div>
			<?php endwhile; ?>
		</div>
		<?php
	}
}

new AnsPress_Dashboard();

// we need the global screen column value to beable to have a sidebar in WordPress 2.8
global $screen_layout_columns;

$screen = get_current_screen();
$columns = absint( $screen->get_columns() );
$columns_css = '';

if ( $columns ) {
	$columns_css = " columns-$columns";
}
?>

<div id="anspress-metaboxes" class="wrap">
	<?php screen_icon('options-general' ); ?>
	<h1>AnsPress</h1>
	<div class="welcome-panel" id="welcome-panel">
		<div class="welcome-panel-content">
			<h2><?php _e('Welcome to AnsPress!', 'anspress-question-answer' ); ?></h2>
			<p class="about-description"><?php _e('We’ve assembled some links to get you started:', 'anspress-question-answer' ); ?></p>
			<div class="welcome-panel-column-container">
				<div class="welcome-panel-column">
					<h3><?php _e('Get Started', 'anspress-question-answer' ); ?></h3>
					<a href="<?php echo admin_url( 'admin.php?page=anspress_options' ); ?>" class="button button-primary button-hero"><?php _e('AnsPress Options', 'anspress-question-answer' ); ?></a>
				</div>
				<div class="welcome-panel-column">
					<h3><?php _e('Next Steps', 'anspress-question-answer' ); ?></h3>
					<ul>
						<li><a class="welcome-icon welcome-write-blog" href="<?php echo admin_url( 'post-new.php?post_type=question' ); ?>"><?php _e('Write your first question', 'anspress-question-answer' ); ?></a></li>
						<li><a class="welcome-icon welcome-add-page" href="<?php echo admin_url( 'admin.php?page=ap_select_question' ); ?>"><?php _e('Post an answer', 'anspress-question-answer' ); ?></a></li>
						<li><a class="welcome-icon welcome-view-site" href="<?php echo ap_get_link_to('/' ); ?>"><?php _e('View questions', 'anspress-question-answer' ); ?></a></li>
					</ul>
				</div>
				<div class="welcome-panel-column welcome-panel-last">
					<h3><?php _e('More actions', 'anspress-question-answer' ); ?></h3>
					<ul>
						<li>
							<div class="welcome-icon welcome-widgets-menus">
								<?php
								printf(
									__( 'Get %1$s or %2$s', 'anspress-question-answer' ),
									sprintf(
										'<a href="%1$s" target="_blank">%2$s</a>',
										esc_url( 'https://anspress.io/themes/' ),
										__( 'Themes', 'anspress-question-answer' )
									),
									sprintf(
										'<a href="%1$s" target="_blank">%2$s</a>',
										esc_url( 'https://anspress.io/extensions/' ),
										__( 'Extensions', 'anspress-question-answer' )
									)
								);
								?>
							</div>
						</li>
						<li><a class="welcome-icon welcome-comments" href="https://anspress.io/questions/" target="_blank"><?php _e('Help and Support!', 'anspress-question-answer' ); ?></a></li>
						<li><a class="welcome-icon welcome-learn-more" href="https://anspress.io/docs/"><?php _e('Documents and FAQ', 'anspress-question-answer' ); ?></a></li>
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

