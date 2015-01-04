<?php
/**
 * All actions of AnsPress
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://wp3.in
 * @copyright 2014 Rahul Aryan
 */

class AnsPress_Actions
{
	/**
	 * Initialize the class
	 * @return void
	 * @since 2.0
	 */
	public function __construct()
	{
		new AnsPress_Post_Status;

		add_action( 'ap_after_new_question', array($this, 'after_new_question'), 10, 2 );
		add_action( 'ap_after_new_answer', array($this, 'after_new_answer'), 10, 2 );

		add_action( 'ap_after_update_question', array($this, 'ap_after_update_question'), 10, 2 );
	}

	/**
	 * Things to do after creating a question
	 * @param  int $post_id
	 * @param  object $post
	 * @return void
	 * @since 1.0
	 */
	public function after_new_question($post_id, $post)
	{

		$user_id = get_current_user_id();
		update_post_meta($post_id, ANSPRESS_VOTE_META, '0');
		update_post_meta($post_id, ANSPRESS_FAV_META, '0');
		update_post_meta($post_id, ANSPRESS_CLOSE_META, '0');
		update_post_meta($post_id, ANSPRESS_FLAG_META, '0');
		update_post_meta($post_id, ANSPRESS_VIEW_META, '0');
		update_post_meta($post_id, ANSPRESS_UPDATED_META, current_time( 'mysql' ));
		update_post_meta($post_id, ANSPRESS_SELECTED_META, false);
		
		//ap_add_history($user_id, $post_id, 'asked');
		ap_add_parti($post_id, $user_id, 'question');
		
		//update answer count
		update_post_meta($post_id, ANSPRESS_ANS_META, '0');

		do_action('ap_after_inserting_question', $post_id);
		ap_do_event('new_question', $post_id, $user_id);

	}	
	
	/**
	 * Things to do after creating an answer
	 * @param  int $post_id
	 * @param  object $post
	 * @return void
	 * @since 2.0
	 */
	public function after_new_answer($post_id, $post)
	{

		$user_id = get_current_user_id();	
		$question = get_post($post->post_parent);
		// set default value for meta
		update_post_meta($post_id, ANSPRESS_VOTE_META, '0');
		
		// set updated meta for sorting purpose
		update_post_meta($question->ID, ANSPRESS_UPDATED_META, current_time( 'mysql' ));
		update_post_meta($post_id, ANSPRESS_UPDATED_META, current_time( 'mysql' ));
		
		ap_add_parti($question->ID, $user_id, 'answer');			
		
		// get existing answer count
		$current_ans = ap_count_ans($question->ID);
		
		//update answer count
		update_post_meta($question->ID, ANSPRESS_ANS_META, $current_ans);
		
		update_post_meta($post_id, ANSPRESS_BEST_META, 0);
		
		do_action('ap_after_inserting_answer', $post_id);			
	}

	public function ap_after_update_question($post_id, $post){
		// set updated meta for sorting purpose
		update_post_meta($post_id, ANSPRESS_UPDATED_META, current_time( 'mysql' ));

		ap_do_event('edit_question', $post_id, get_current_user_id());
	}

}