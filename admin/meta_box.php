<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/** 
 * The Class.
 */
class AP_Question_Meta_Box {

	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
	}


	public function add_meta_box( $post_type ) {
        $post_types = array('question');     //limit meta box to certain post types
        if ( in_array( $post_type, $post_types )) {
			add_meta_box('ap_answers_meta_box' ,__( 'Answers', 'ap' ), array( $this,'answers_meta_box_content' ), $post_type, 'normal', 'high' );			
			add_meta_box('ap_question_meta_box' ,__( 'Question', 'ap' ), array( $this,'question_meta_box_content' ), $post_type, 'side', 'high' );			
        }
		
		if ( in_array( $post_type, array('question', 'answer') )) {
			add_meta_box('ap_flag_meta_box' ,__( 'Flag & report', 'ap' ), array( $this,'flag_meta_box_content' ), $post_type, 'normal', 'high' );		
        }
	}


	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function answers_meta_box_content( $post ) {
		$ans_args=array(
			'post_type' => 'answer',
			'post_status' => 'publish',
			'post_parent' => get_the_ID(),
			'showposts' => 10,
			'orderby' => 'date',
			'order' => 'DESC'
		);

	
		$ans_args = apply_filters('ap_meta_box_answers_query_args', $ans_args);
	
		$answers = get_posts($ans_args);	
		
		if(!empty($answers)){
		foreach ($answers as $ans){
		?>
			<div class="answer clearfix">
				<div class="author">
					<span><?php echo get_avatar($ans->post_author, 30); ?></span>
					<strong><?php echo ap_user_display_name($ans->post_author); ?></strong>
				</div>
				<div class="answer-content">
					<div class="submitted-on">
						<?php 
							printf( __( '<span class="when">Answered about %s ago</span>', 'ap' ), ap_human_time( get_the_time('U', $ans)));
						?>
					</div>
					<p><?php echo $ans->post_content; ?></p>
					<div class="row-actions">
						<span><a href="<?php echo get_edit_post_link($ans->ID); ?>"><?php _e('Edit', 'ap'); ?></a></span>
						<span> | <a href="#"><?php _e('Hide', 'ap'); ?></a></span>
						<span class="delete vim-d vim-destructive"> | <a href="<?php echo get_delete_post_link($ans->ID); ?>"><?php _e('Trash', 'ap'); ?></a></span>
					</div>
				</div>
			</div>
		<?php
		}
		}else{
			 _e('No answers yet', 'ap');
		}
		wp_reset_postdata();
	}
	
	public function flag_meta_box_content( $post ) {

		
		// get all flag message
		$flag_note = ap_opt('flag_note');
		
		$flags = ap_get_all_meta(
			array(
			'where' => array(
				'apmeta_type' => array('value' => 'flag', 'compare' => '=', 'relation' => 'AND'), 
				'apmeta_actionid' => array('value' => $post->ID, 'compare' => '=', 'relation' => 'AND'), 
				)
			), 10);

		if(!empty($flags)){
		foreach ($flags as $r){
		?>
			<div class="flag-item clearfix">
				<div class="flagger">
					<?php echo get_avatar($r->apmeta_userid, 30); ?>
					<strong><?php echo ap_user_display_name($r->apmeta_userid); ?></strong>
				</div>
				<div class="flag-message">
					<div class="submitted-on">
						<?php 
							printf( __( '<span class="when">Flagged about %s ago</span>', 'ap' ), ap_human_time( $r->unix_date));
						?>
					</div>
					<strong><?php echo $r->apmeta_value !== NULL ? $flag_note[$r->apmeta_value]['title'] : __('Flagged with custom message', 'ap'); ?></strong>
					<span><?php echo $r->apmeta_value !== NULL ? $flag_note[$r->apmeta_value]['description'] : $r->apmeta_param; ?></span>
					<div class="row-actions">
						<span class="delete vim-d vim-destructive"><a id="ap-delete-flag" data-id="<?php echo $r->apmeta_id; ?>" data-nonce="<?php echo wp_create_nonce('flag_delete'.$r->apmeta_id) ?>" href="#"><?php _e('Delete', 'ap') ?></a></span>
					</div>
				</div>
			</div>
		<?php
		}
		}else{
			 _e('No flag yet', 'ap');
		}
	}
	
	function question_meta_box_content($post){
		$ans_count = ap_count_ans_meta($post->ID);
		$vote_count = get_post_meta($post->ID, ANSPRESS_VOTE_META, true);
		?>
			<ul>
				<li> <?php printf( _n('<strong>1</strong> Answer', '<strong>%d</strong> Answers', $ans_count, 'ap'), $ans_count); ?> </li>
				<li> <?php printf( _n('<strong>1</strong> Vote', '<strong>%d</strong> Votes', $vote_count, 'ap'), $vote_count); ?> </li>
			</ul>
		<?php
	}
}