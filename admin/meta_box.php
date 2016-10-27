<?php

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
	 */
	public function add_meta_box( $post_type ) {
		if ( 'question' == $post_type ) {
			add_meta_box( 'ap_answers_meta_box' ,__( 'Answers', 'anspress-question-answer' ), array( $this, 'answers_meta_box_content' ), $post_type, 'normal', 'high' );
		}
		if ( 'question' == $post_type || 'answer' == $post_type ) {
			add_meta_box( 'ap_question_meta_box' ,__( 'Question', 'anspress-question-answer' ), array( $this, 'question_meta_box_content' ), $post_type, 'side', 'high' );
		}
	}

	/**
	 * Render Meta Box content.
	 */
	public function answers_meta_box_content() {
		?>
		<div id="answers-list" data-questionid="<?php the_ID(); ?>">
			<div class="ap-ansm clearfix" v-for="post in items" v-cloak :class="{[statusCase(post.status)]: true, selected: post.selected}">
				<div class="author">
					<a href="#" class="ap-ansm-avatar" v-html="post.avatar"></a>
					<strong class="ap-ansm-name">{{post.author}}</strong>
				</div>
				<div class="ap-ansm-inner">
					<div class="ap-ansm-meta">
						<span class="post-status" :class="statusCase(post.status)" v-if="post.status != 'Published'">{{post.status}}</span>
						<span v-html="post.activity"></span>
					</div>
					<div class="ap-ansm-content" v-html="post.content"></div>
					<div class="answer-actions">
						<span><a :href="post.edit_link"><?php esc_attr_e( 'Edit', 'anspress-question-answer' ); ?></a></span>
						<span class="delete vim-d vim-destructive"> | <a :href="post.trash_link"><?php esc_attr_e( 'Trash', 'anspress-question-answer' ); ?></a></span>
					</div>
				</div>
			</div>
			<br />
			<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=answer&post_parent=' . get_the_ID() ) ); ?>" class="button add-answer"><?php esc_html_e( 'Add an answer', 'anspress-question-answer' ); ?></a>
		</div>
		<?php
	}

	/**
	 * Question meta box.
	 */
	public function question_meta_box_content( $post ) {
		$ans_count = ap_get_answers_count( $post->ID );
		$vote_count = ap_get_votes_net( $post );
		?>
			<ul class="ap-meta-list">

				<?php if ( 'answer' != $post->post_type ) :   ?>
					<li>
						<i class="apicon-answer"></i>
						<?php printf( _n( '<strong>1</strong> Answer', '<strong>%d</strong> Answers', $ans_count, 'anspress-question-answer' ), $ans_count ); ?>
						<a href="<?php echo admin_url( 'post-new.php?post_type=answer&post_parent=' . get_the_ID() ) ?>" class="add-answer"><?php _e('Add an answer', 'anspress-question-answer' ); ?></a>
					</li>
				<?php endif; ?>

				<li>
					<?php $nonce = wp_create_nonce( 'admin_vote' ); ?>
					<i class="apicon-thumb-up"></i>
					<?php printf( _n( '<strong>%d</strong> Vote', '<strong>%d</strong> Votes', $vote_count, 'anspress-question-answer' ), $vote_count ); // xss okay. ?>

					<a id="ap-vote-down" href="#" class="vote button button-small ap-ajax-btn" data-query="ap_admin_vote::<?php echo $nonce; ?>::<?php echo $post->ID; ?>::down" data-cb="replaceText">
						<?php esc_html_e( '-', 'anspress-question-answer' ); ?>
					</a>

					<a id="ap-vote-up" href="#" class="vote button button-small ap-ajax-btn" data-query="ap_admin_vote::<?php echo $nonce; ?>::<?php echo $post->ID; ?>::up" data-cb="replaceText">
						<?php esc_attr_e( '+', 'anspress-question-answer' ); ?>
					</a>

				</li>
				<li><?php $this->flag_meta_box( $post ); ?> </li>
			</ul>
		<?php
	}

	public function flag_meta_box($post) {
		?>
			<i class="apicon-flag"></i>
			<strong><?php ap_post_field( 'flags', $post); ?></strong> <?php _e('Flag', 'anspress-question-answer' ); ?>
			<a id="ap-clear-flag" href="#" data-query="ap_clear_flag::<?php echo wp_create_nonce( 'clear_flag_' . $post->ID ) .'::' . $post->ID; ?>" class="ap-ajax-btn flag-clear" data-cb="afterFlagClear"><?php _e( 'Clear flag', 'anspress-question-answer' ); ?></a>
		<?php
	}
}
