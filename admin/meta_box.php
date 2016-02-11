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
	public function answers_meta_box_content( ) {
		global $answers;
		$answers = ap_get_answers( array( 'question_id' => get_the_ID() ) );

		if ( ap_have_answers() ) {
			while ( ap_have_answers() ) : ap_the_answer();
			?>
            <div id="answer_<?php the_ID(); ?>" data-id="<?php the_ID(); ?>" class="ap-ansm clearfix">
                <div class="author">
					<a class="ap-ansm-avatar" href="<?php ap_answer_the_author_link(); ?>"<?php ap_hover_card_attributes( ap_answer_get_author_id() ); ?>>
						<?php ap_answer_the_author_avatar(); ?>
					</a>
					<strong class="ap-ansm-name"><?php echo ap_user_display_name( ap_answer_get_author_id() ); ?></strong>
                </div>

                <div class="ap-ansm-inner">
                    
                    <div class="ap-ansm-meta">
						<?php ap_answer_the_active_time(); ?>
					</div>
					
					<div class="ap-ansm-content"><?php the_content(); ?></div>
					
					<div class="answer-actions">

						<span><a href="<?php echo get_edit_post_link( get_the_ID() ); ?>"><?php _e( 'Edit', 'anspress-question-answer' ); ?></a></span>
						<span class="delete vim-d vim-destructive"> | <a href="<?php echo get_delete_post_link( get_the_ID() ); ?>"><?php _e( 'Trash', 'anspress-question-answer' ); ?></a></span>
					</div>

				</div>
			</div>
			<?php
			endwhile ;

		} else {
			?>
            <div class="inside">
				<a href="#addanswerbtn" class="button"><?php _e( 'Add answer', 'anspress-question-answer' ); ?></a>
				<?php _e( 'No answers yet', 'anspress-question-answer' ); ?>
            </div>
            
			<?php
		}
		wp_reset_postdata();
	}

	public function question_meta_box_content($post) {
		$ans_count = ap_count_answer_meta( $post->ID );
		$vote_count = get_post_meta( $post->ID, ANSPRESS_VOTE_META, true );
		?>
            <ul class="ap-meta-list">
            	<?php if ( 'answer' != $post->post_type ) :   ?>
					<li>
						<i class="apicon-answer"></i>
						<?php printf( _n( '<strong>1</strong> Answer', '<strong>%d</strong> Answers', $ans_count, 'anspress-question-answer' ), $ans_count ); ?>
						<a href="#" data-query="ap_admin_answer_from::<?php echo wp_create_nonce( 'admin_answer_'.$post->ID ) .'::'.$post->ID; ?>" class="ap-ajax-btn add-answer" data-cb="loadAdminAnswerForm"><?php _e('Add an answer', 'anspress-question-answer' ); ?></a>
					</li>
				<?php endif; ?>				
				<li>
					<?php $nonce = wp_create_nonce( 'admin_vote' ); ?>
					<i class="apicon-thumb-up"></i>
					<?php printf( _n( '<strong>1</strong> Vote', '<strong>%d</strong> Votes', $vote_count, 'anspress-question-answer' ), $vote_count ); ?>					
					<a id="ap-vote-down" href="#" class="vote button button-small ap-ajax-btn" data-query="ap_admin_vote::<?php echo $nonce; ?>::<?php echo $post->ID; ?>::down" data-cb="replaceText"><?php _e('-', 'anspress-question-answer' ); ?></a>
					<a id="ap-vote-up" href="#" class="vote button button-small ap-ajax-btn" data-query="ap_admin_vote::<?php echo $nonce; ?>::<?php echo $post->ID; ?>::up" data-cb="replaceText"><?php _e('+', 'anspress-question-answer' ); ?></a>
				</li>
				<li><?php $this->flag_meta_box( $post ); ?> </li>
            </ul>
		<?php
	}

	public function flag_meta_box($post) {
		?>
			<i class="apicon-flag"></i>
			<strong><?php echo ap_flagged_post_meta( $post->ID ); ?></strong> <?php _e('Flag', 'anspress-question-answer' ); ?>
			<a id="ap-clear-flag" href="#" data-query="ap_clear_flag::<?php echo wp_create_nonce( 'clear_flag_'.$post->ID ) .'::'.$post->ID; ?>" class="ap-ajax-btn flag-clear" data-cb="afterFlagClear"><?php _e('Clear flag', 'anspress-question-answer' ); ?></a>
		<?php
	}
}
