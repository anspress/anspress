<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * The Class.
 */
class AP_Question_Meta_Box {

	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
	}


	public function add_meta_box( $post_type ) {
		$post_types = array( 'question' );     // limit meta box to certain post types
		if ( in_array( $post_type, $post_types ) ) {
			add_meta_box( 'ap_answers_meta_box' ,__( 'Answers', 'ap' ), array( $this, 'answers_meta_box_content' ), $post_type, 'normal', 'high' );
			add_meta_box( 'ap_question_meta_box' ,__( 'Question', 'ap' ), array( $this, 'question_meta_box_content' ), $post_type, 'side', 'high' );
		}

		/*
		if ( in_array( $post_type, array('question', 'answer') )) {
            add_meta_box('ap_flag_meta_box' ,__( 'Flag & report', 'ap' ), array( $this,'flag_meta_box_content' ), $post_type, 'normal', 'high' );
        }*/
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

						<span><a href="<?php echo get_edit_post_link( get_the_ID() ); ?>"><?php _e( 'Edit', 'ap' ); ?></a></span>
						<span class="delete vim-d vim-destructive"> | <a href="<?php echo get_delete_post_link( get_the_ID() ); ?>"><?php _e( 'Trash', 'ap' ); ?></a></span>
					</div>

				</div>
			</div>
			<?php
			endwhile ;

		} else {
			?>
            <div class="inside">
				<a href="#addanswerbtn" class="button"><?php _e( 'Add answer', 'ap' ); ?></a>
				<?php _e( 'No answers yet', 'ap' ); ?>
            </div>
            
			<?php
		}
		wp_reset_postdata();
	}

	public function question_meta_box_content($post) {
		$ans_count = ap_count_answer_meta( $post->ID );
		$vote_count = get_post_meta( $post->ID, ANSPRESS_VOTE_META, true );
		?>
            <ul>
				<li> <?php printf( _n( '<strong>1</strong> Answer', '<strong>%d</strong> Answers', $ans_count, 'ap' ), $ans_count ); ?> </li>
				<li> <?php printf( _n( '<strong>1</strong> Vote', '<strong>%d</strong> Votes', $vote_count, 'ap' ), $vote_count ); ?> </li>
            </ul>
		<?php
	}
}
