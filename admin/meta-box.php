<?php
/**
 * AnsPresss admin meta boxes.
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-3.0+
 * @link      https://anspress.net
 * @copyright 2014 Rahul Aryan
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Meta box class.
 * Registers meta box for admin post edit screen.
 */
class AP_Question_Meta_Box {
	/**
	 * Initialize the class.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
	}

	/**
	 * Hook meta boxes in post edit screen.
	 *
	 * @param string $post_type Post type.
	 */
	public function add_meta_box( $post_type ) {

		if ( 'question' === $post_type ) {
			add_meta_box( 'ap_answers_meta_box', sprintf( __( ' %d Answers', 'anspress-question-answer' ), ap_get_answers_count() ), array( $this, 'answers_meta_box_content' ), $post_type, 'normal', 'high' );
		}

		if ( 'question' === $post_type || 'answer' === $post_type ) {
			add_meta_box( 'ap_question_meta_box', __( 'Question', 'anspress-question-answer' ), array( $this, 'question_meta_box_content' ), $post_type, 'side', 'high' );
		}
	}

	/**
	 * Render Meta Box content.
	 */
	public function answers_meta_box_content() {
		?>
		<div id="answers-list" data-questionid="<?php the_ID(); ?>">


		</div>
		<br />
		<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=answer&post_parent=' . get_the_ID() ) ); ?>" class="button add-answer"><?php esc_html_e( 'Add an answer', 'anspress-question-answer' ); ?></a>

		<script type="text/html" id="ap-answer-template">
			<div class="author">
				<a href="#" class="ap-ansm-avatar">{{{avatar}}}</a>
				<strong class="ap-ansm-name">{{author}}</strong>
			</div>
			<div class="ap-ansm-inner">
				<div class="ap-ansm-meta">
					<span class="post-status">{{status}}</span>
					{{{activity}}}
				</div>
				<div class="ap-ansm-content">{{{content}}}</div>
				<div class="answer-actions">
					<span><a href="{{{editLink}}}"><?php esc_attr_e( 'Edit', 'anspress-question-answer' ); ?></a></span>
					<span class="delete vim-d vim-destructive"> | <a href="{{{trashLink}}}"><?php esc_attr_e( 'Trash', 'anspress-question-answer' ); ?></a></span>
				</div>
			</div>
		</script>
		<?php
	}

	/**
	 * Question meta box.
	 *
	 * @param object|integer|null $_post Post.
	 */
	public function question_meta_box_content( $_post ) {
		$ans_count  = ap_get_answers_count( $_post->ID );
		$vote_count = ap_get_votes_net( $_post );
		?>
			<ul class="ap-meta-list">

				<?php if ( 'answer' !== $_post->post_type ) : ?>
					<li>
						<i class="apicon-answer"></i>
						<?php printf( _n( '<strong>%d</strong> Answer', '<strong>%d</strong> Answers', $ans_count, 'anspress-question-answer' ), $ans_count ); // xss okay. ?>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=answer&post_parent=' . get_the_ID() ) ); ?>" class="add-answer"><?php esc_attr_e( 'Add an answer', 'anspress-question-answer' ); ?></a>
					</li>
				<?php endif; ?>

				<li>
					<?php $nonce = wp_create_nonce( 'admin_vote' ); ?>
					<i class="apicon-thumb-up"></i>
					<?php printf( _n( '<strong>%d</strong> Vote', '<strong>%d</strong> Votes', $vote_count, 'anspress-question-answer' ), $vote_count ); // xss okay. ?>

					<a id="ap-vote-down" href="#" class="vote button button-small ap-ajax-btn" data-query="ap_admin_vote::<?php echo esc_attr( $nonce ); ?>::<?php echo esc_attr( $_post->ID ); ?>::down" data-cb="replaceText">
						<?php esc_html_e( '-', 'anspress-question-answer' ); ?>
					</a>

					<a id="ap-vote-up" href="#" class="vote button button-small ap-ajax-btn" data-query="ap_admin_vote::<?php echo esc_attr( $nonce ); ?>::<?php echo esc_attr( $_post->ID ); ?>::up" data-cb="replaceText">
						<?php esc_attr_e( '+', 'anspress-question-answer' ); ?>
					</a>
				</li>
				<li><?php $this->flag_meta_box( $_post ); ?> </li>
			</ul>
		<?php
	}

	/**
	 * Show flags and clear flag button in post edit screen.
	 *
	 * @param object $post Post.
	 */
	public function flag_meta_box( $post ) {
		$args = array(
			'action'         => 'ap_ajax',
			'ap_ajax_action' => 'ap_clear_flag',
			'__nonce'        => wp_create_nonce( 'clear_flag_' . $post->ID ),
			'post_id'        => $post->ID,
		);

		?>
			<i class="apicon-flag"></i>
			<strong class="ap-question-flag-count"><?php ap_post_field( 'flags', $post ); ?></strong> <?php esc_attr_e( 'Flag', 'anspress-question-answer' ); ?>
			<a id="ap-clear-flag" href="#" data-query="<?php echo esc_js( wp_json_encode( $args ) ); ?>" class="flag-clear" data-cb="afterFlagClear"><?php esc_attr_e( 'Clear flag', 'anspress-question-answer' ); ?></a>

			<script type="text/javascript">
				jQuery(document).ready(function($){
					$('#ap-clear-flag').click(function(e){
						e.preventDefault();
						var self = this;
						var q = JSON.parse($(self).attr('data-query'));

						$.ajax({
							url: ajaxurl,
							data: q,
							type: 'POST',
							success: function(data){
								$('.ap-question-flag-count').text('0');
								$('.column-flag .flag-count').removeClass('flagged');
								$(self).remove();
							}
						});
					})
				});
			</script>
		<?php
	}
}
