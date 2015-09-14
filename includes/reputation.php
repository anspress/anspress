<?php
/**
 * AnsPress reputation controller class
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-3.0+
 * @link      http://wp3.com
 * @copyright 2015 Rahul Aryan
 */

class AP_Reputation {

	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {

		// return if reputation is disabled
		if ( ap_opt( 'disable_reputation' ) ) {
			return; }

		ap_register_user_page( 'reputation', __( 'Reputation', 'ap' ), array( $this, 'reputation_page' ) );
		add_filter( 'ap_user_menu', array( $this, 'sort_reputation_page' ) );

		add_action( 'ap_after_new_question', array( $this, 'new_question' ) );
		add_action( 'ap_untrash_question', array( $this, 'new_question' ) );
		add_action( 'ap_trash_question', array( $this, 'delete_question' ) );

		add_action( 'ap_after_new_answer', array( $this, 'new_answer' ) );
		add_action( 'ap_untrash_answer', array( $this, 'new_answer' ) );
		add_action( 'ap_trash_multi_answer', array( $this, 'delete_answer' ) );

		add_action( 'ap_select_answer', array( $this, 'select_answer' ), 10, 3 );
		add_action( 'ap_unselect_answer', array( $this, 'unselect_answer' ), 10, 3 );

		add_action( 'ap_vote_up', array( $this, 'vote_up' ), 10, 2 );
		add_action( 'ap_vote_down', array( $this, 'vote_down' ), 10, 2 );
		add_action( 'ap_undo_vote_up', array( $this, 'undo_vote_up' ), 10, 2 );
		add_action( 'ap_undo_vote_down', array( $this, 'undo_vote_down' ), 10, 2 );

		add_action( 'ap_publish_comment', array( $this, 'new_comment' ) );
		add_action( 'ap_unpublish_comment', array( $this, 'delete_comment' ) );

		add_filter( 'ap_user_display_meta_array', array( $this, 'display_meta' ), 10, 2 );

		add_action( 'ap_added_reputation', array( $this, 'ap_added_reputation' ), 10, 5 );
	}

	public function reputation_page() {
		ap_get_template_part( 'user/reputation' );
	}

	public function sort_reputation_page($menu) {

		if ( isset( $menu['reputation'] ) ) {
			$menu['reputation']['order'] = 10; }

		return $menu;
	}

	/**
	 * Update reputation of user created question
	 * @param  integer $postid
	 * @return boolean|null
	 */
	public function new_question($postid) {
		$reputation = ap_reputation_by_event( 'new_question', true );
		return ap_reputation( 'question', get_current_user_id(), $reputation, $postid );
	}

	/**
	 * Update point of trashing question
	 * @param  integer $postid
	 * @return boolean
	 */
	public function delete_question($post) {
		$reputation = ap_reputation_by_event( 'new_question', true );
		return ap_reputation_log_delete( 'question', get_current_user_id(), $reputation, $post->ID );
	}

	/**
	 * Update reputation of user created an answer
	 * @param  integer $postid
	 * @return boolean|null
	 */
	public function new_answer($postid) {
		$post = get_post( $postid );
		$reputation = ap_reputation_by_event( 'new_answer', true );
		return ap_reputation( 'answer', get_current_user_id(), $reputation, $postid, $post->post_author );
	}

	/**
	 * Update reputation on trasing answer
	 * @param  integer $postid
	 * @return boolean
	 */
	public function delete_answer($post) {
		$reputation = ap_reputation_by_event( 'new_answer', true );
		return ap_reputation_log_delete( 'answer', get_current_user_id(), $reputation, $post->ID );
	}

	/**
	 * Update reputation of user selecting and author of answer on selecting an answer
	 * @param  integer $userid
	 * @param  integer $question_id
	 * @param  integer $answer_id
	 * @return void
	 */
	public function select_answer($userid, $question_id, $answer_id) {
		$reputation = ap_reputation_by_event( 'select_answer', true );
		$selector_reputation = ap_reputation_by_event( 'selecting_answer', true );
		$answer = get_post( $answer_id );

		if ( $answer->post_author != $userid ) {
			ap_reputation( 'best_answer', $answer->post_author, $reputation, $answer_id, $answer->post_author ); }

		ap_reputation( 'selecting_answer', $userid, $selector_reputation, $answer_id, $answer->post_author );
		return;
	}

	/**
	 * Update reputation of user selecting and author of answer on unselecting answer
	 * @param  integer $userid
	 * @param  integer $question_id
	 * @param  integer $answer_id
	 * @return void
	 */
	public function unselect_answer($userid, $question_id, $answer_id) {
		$reputation = ap_reputation_by_event( 'select_answer', true );
		$selector_reputation = ap_reputation_by_event( 'selecting_answer', true );
		$answer = get_post( $answer_id );

		if ( $answer->post_author != $userid ) {
			ap_reputation_log_delete( 'best_answer', $answer->post_author, $reputation, $answer_id );
		}
		ap_reputation_log_delete( 'selecting_answer', $userid, $selector_reputation, $answer_id );
		return;
	}

	/**
	 * Update reputation of post author when received an up vote
	 * @param  integer $postid
	 * @param  array   $counts
	 * @return null|false
	 */
	public function vote_up($postid, $counts) {
		$post = get_post( $postid );

		// give reputation to post author
		if ( $post->post_type == 'question' ) {
			$reputation = ap_reputation_by_event( 'question_upvote', true ); } elseif ($post->post_type == 'answer')
			$reputation = ap_reputation_by_event( 'answer_upvote', true );

		$uid = $post->post_author;

		if ( ! empty( $reputation ) ) {
			ap_reputation( 'vote_up', $uid, $reputation, $postid ); }

		if ( $post->post_type == 'question' ) {
			$reputation = ap_reputation_by_event( 'question_upvoted', true ); } elseif ($post->post_type == 'answer')
			$reputation = ap_reputation_by_event( 'answer_upvoted', true );

		$userid = get_current_user_id();

		if ( ! empty( $reputation ) ) {
			return ap_reputation( 'vote_up', $userid, $reputation, $postid ); }

		return false;
	}

	/**
	 * Update reputation of post author when received a down vote
	 * @param  integer $postid
	 * @param  array   $counts
	 * @return boolean
	 */
	public function vote_down($postid, $counts) {
		$post = get_post( $postid );

		// give reputation to post author
		if ( $post->post_type == 'question' ) {
			$reputation = ap_reputation_by_event( 'question_downvote', true ); } elseif ($post->post_type == 'answer')
			$reputation = ap_reputation_by_event( 'answer_downvote', true );

		$uid = $post->post_author;

		if ( empty( $reputation ) ) {
			return false; }

		ap_reputation( 'vote_down', $uid, $reputation, $postid );

		// give reputation to user casting vote
		$userid = get_current_user_id();
		if ( $post->post_type == 'question' ) {
			$reputation = ap_reputation_by_event( 'question_downvoted', true ); } elseif ($post->post_type == 'answer')
			$reputation = ap_reputation_by_event( 'answer_downvoted', true );

		ap_reputation( 'voted_down', $userid, $reputation, $postid );

		return true;
	}

	/**
	 * Reverse reputation of post author when up vote is undone
	 * @param  integer $postid
	 * @param  array   $counts
	 * @return boolean
	 */
	public function undo_vote_up($postid, $counts) {
		$post = get_post( $postid );

		// give reputation to post author
		if ( $post->post_type == 'question' ) {
			$reputation = ap_reputation_by_event( 'question_upvote', true ); } elseif ($post->post_type == 'answer')
			$reputation = ap_reputation_by_event( 'answer_upvote', true );

		$uid = $post->post_author;

		if ( empty( $reputation ) ) {
			return false; }

		ap_reputation_log_delete( 'vote_up', $uid, $reputation, $postid );

		if ( $post->post_type == 'question' ) {
			$reputation = ap_reputation_by_event( 'question_upvoted', true ); } elseif ($post->post_type == 'answer')
			$reputation = ap_reputation_by_event( 'answer_upvoted', true );

		$userid = get_current_user_id();

		ap_reputation_log_delete( 'vote_up', $userid, $reputation, $postid );

		return true;
	}

	/**
	 * Reverse reputation of post author when down vote is undone
	 * @param  integer $postid
	 * @param  array   $counts
	 * @return false|null
	 */
	public function undo_vote_down($postid, $counts) {
		$post = get_post( $postid );

		// give reputation to post author
		if ( $post->post_type == 'question' ) {
			$reputation = ap_reputation_by_event( 'question_downvote', true ); } elseif ($post->post_type == 'answer')
			$reputation = ap_reputation_by_event( 'answer_downvote', true );

		$uid = $post->post_author;

		if ( empty( $reputation ) ) {
			return false; }

		ap_reputation_log_delete( 'vote_up', $uid, $reputation, $postid );

		// give reputation to user casting vote
		$userid = get_current_user_id();
		if ( $post->post_type == 'question' ) {
			$reputation = ap_reputation_by_event( 'question_downvoted', true ); } elseif ($post->post_type == 'answer')
			$reputation = ap_reputation_by_event( 'answer_downvoted', true );

		ap_reputation_log_delete( 'voted_down', $userid, $reputation, $postid );
	}

	/**
	 * Award reputation on new comment
	 * @param  object $comment WordPress comment object
	 * @return void
	 */
	public function new_comment($comment) {
		$reputation = ap_reputation_by_event( 'new_comment', true );
		ap_reputation( 'comment', $comment->user_id, $reputation, $comment->comment_ID );
	}

	/**
	 * Reverse reputation on deleting comment
	 * @param  object $comment
	 * @return void
	 */
	public function delete_comment($comment) {
		$reputation = ap_reputation_by_event( 'new_comment', true );
		ap_reputation_log_delete( 'comment', $comment->user_id, $reputation, $comment->comment_ID );
	}


	public function display_meta($metas, $user_id) {
		if ( $user_id > 0 ) {
			$metas['reputation'] = '<span class="ap-user-meta ap-user-meta-reputation" title="'.__( 'Reputation', 'ap' ).'">'. sprintf( __( '%s Rep.', 'ap' ), ap_get_reputation( $user_id, true ) ) .'</span>'; }

		return $metas;
	}

	public function ap_added_reputation($user_id, $action_id, $reputation, $type, $current_user_id) {
		ap_insert_notification( $current_user_id, $user_id, 'received_reputation', array( 'reputation' => $reputation, 'type' => $type ) );
	}

}

/**
 * User reputations loop
 *
 * Query wrapper for fetching reputations of a specific user by ID
 *
 * @param array|string $args arguments passed to class.
 *                           @param string $user_id WordPress user_id, default is current user_id
 *                           @param integer $number Numbers of rows to fetch from database, default is 20
 *                           @param integer $offset Rows to offset
 * @since 2.3
 */
class AnsPress_Reputation
{
	/**
	 * The loop iterator.
	 *
	 * @access public
	 * @var int
	 */
	var $current = -1;

	/**
	 * The number of rows returned by the paged query.
	 *
	 * @access public
	 * @var int
	 */
	var $count;

	/**
	 * Array of users located by the query.
	 *
	 * @access public
	 * @var array
	 */
	var $reputations;

	/**
	 * The reputation object currently being iterated on.
	 *
	 * @access public
	 * @var object
	 */
	var $reputation;

	/**
	 * A flag for whether the loop is currently being iterated.
	 *
	 * @access public
	 * @var bool
	 */
	var $in_the_loop;

	/**
	 * The total number of rows matching the query parameters.
	 *
	 * @access public
	 * @var int
	 */
	var $total_count;

	/**
	 * Items to show per page
	 *
	 * @access public
	 * @var int
	 */
	var $per_page = 20;

	var $total_pages = 1;

	var $paged;

	var $offset;

	public function __construct($args = '') {

		// grab the current page number and set to 1 if no page number is set
		$this->paged = (get_query_var( 'paged' )) ? get_query_var( 'paged' ) : 1;

		$this->offset = $this->per_page * ($this->paged - 1);

		$this->args = wp_parse_args( $args, array(
			'user_id' 	=> ap_get_displayed_user_id(),
			'number' 	=> $this->per_page,
			'offset' 	=> $this->offset,
		));

		$this->per_page = $this->args['number'];

		$this->query();
	}

	private function query() {

		global $wpdb;

		$query = $wpdb->prepare( 'SELECT SQL_CALC_FOUND_ROWS v.apmeta_id as id,v.apmeta_userid as user_id, v.apmeta_actionid as action_id, v.apmeta_value as reputation, v.apmeta_param as event, v.apmeta_date as rep_date FROM '.$wpdb->prefix."ap_meta v WHERE v.apmeta_type='reputation' AND v.apmeta_userid = %d order by rep_date DESC LIMIT %d,%d", $this->args['user_id'], $this->offset, $this->per_page );

		$key = md5( $query );

		$result = wp_cache_get( $key, 'ap' );
		$this->total_count = wp_cache_get( $key.'_count', 'ap' );

		if ( $result === false ) {
			$result = $wpdb->get_results( $query );
			$this->total_count = $wpdb->get_var( apply_filters( 'ap_reputations_found_rows', 'SELECT FOUND_ROWS()', $this ) );
			wp_cache_set( $key.'_count', $this->total_count, 'ap' );
			wp_cache_set( $key, $result, 'ap' );
		}

		$this->reputations 	= $result;
		$this->total_pages 	= ceil( $this->total_count / $this->per_page );
		$this->count 		= count( $result );

	}

	public function reputations() {

		if ( $this->current + 1 < $this->count ) {
			return true;
		} elseif ( $this->current + 1 == $this->count ) {

			do_action( 'ap_reputations_loop_end' );

			// Do some cleaning up after the loop
			$this->rewind_reputation();
		}

		$this->in_the_loop = false;
		return false;
	}

	/**
	 * Rewind the reputations and reset index.
	 */
	public function rewind_reputation() {
		$this->current = -1;
		if ( $this->count > 0 ) {
			$this->reputation = $this->reputations[0];
		}
	}

	/**
	 * Check if there are reputation in loop
	 *
	 * @return bool
	 */
	public  function has_reputations() {
		if ( $this->count ) {
			return true; }

		return false;
	}

	/**
	 * Set up the next reputation and iterate index.
	 *
	 * @return object The next reputation to iterate over.
	 */
	public function next_reputation() {
		$this->current++;
		$this->reputation = $this->reputations[$this->current];

		return $this->reputation;
	}

	/**
	 * Set up the current reputation inside the loop.
	 */
	public function the_reputation() {

		$this->in_the_loop 		= true;
		$this->reputation      	= $this->next_reputation();

		// loop has just started
		if ( 0 == $this->current ) {

			/**
			 * Fires if the current reputation is the first in the loop.
			 */
			do_action( 'ap_reputation_loop_start' );
		}

	}
}

/**
 * Setup reputation loop
 * @param  string|array $args
 * @return object
 */
function ap_has_reputations($args = '') {

	$sortby = ap_get_sort() != '' ? ap_get_sort() : 'date';

	$args = wp_parse_args( $args, array( 'sortby' => $sortby ) );

	anspress()->reputations = new AnsPress_Reputation( $args );

	return anspress()->reputations->has_reputations();
}

function ap_reputations() {
	return anspress()->reputations->reputations();
}

function ap_the_reputation() {
	return anspress()->reputations->the_reputation();
}

/**
 * Return the current reputation obejct
 * @return object
 */
function ap_reputation_the_object() {
	$rep = anspress()->reputations->reputation;
	return $rep;
}

function ap_reputation_get_the_id() {
	echo ap_reputation_get_id();
}

function ap_reputation_get_id() {
	return ap_reputation_the_object()->id;
}

function ap_reputation_get_the_action_id() {
	echo ap_reputation_get_action_id();
}
function ap_reputation_get_action_id() {
	return ap_reputation_the_object()->action_id;
}

function ap_reputation_get_the_event() {
	echo ap_reputation_get_event();
}

function ap_reputation_get_event() {
	return ap_reputation_the_object()->event;
}

function ap_reputation_get_the_reputation() {
	$rep = ap_reputation_get_reputation();

	if ( $rep > 0 ) {
		printf( __( '+%d', 'ap' ), $rep ); } else {
		echo $rep; }
}

function ap_reputation_get_reputation() {
	return ap_reputation_the_object()->reputation;
}

function ap_reputation_get_the_class() {
	echo ap_reputation_get_class();
}

function ap_reputation_get_class() {
	$rep = ap_reputation_get_reputation();

	if ( $rep > 0 ) {
		return 'positive'; } elseif ($rep < 0)
		return 'negative';
	else {
		return 'neutral'; }
}

function ap_reputation_get_the_date() {
	printf( __( '%s ago', 'ap' ), ap_human_time( ap_reputation_get_date(), false ) );
}
function ap_reputation_get_date() {
	return ap_reputation_the_object()->rep_date;
}

function ap_reputation_get_the_info($event = false, $action_id = false) {
	if ( ! $event ) {
		$event = ap_reputation_get_event(); }

	if ( ! $action_id ) {
		$action_id = ap_reputation_get_action_id(); }

	echo ap_reputation_get_info( $event, $action_id );
}

function ap_reputation_get_info($event, $action_id) {

	switch ( $event ) {
		case 'question':
			$info = sprintf( __( '%sAsked %s', 'ap' ), '<span class="ap-reputation-event">', '</span><a href="'.get_permalink( $action_id ).'">'.get_the_title( $action_id ).'</a>' );
			break;

		case 'answer':
			$info = sprintf( __( '%sAnswered %s', 'ap' ), '<span class="ap-reputation-event">','</span><a href="'.get_permalink( $action_id ).'">'. get_the_title( $action_id ).'</a>' );
			break;

		case 'comment':
			$info = sprintf( __( '%sCommented %s', 'ap' ), '<span class="ap-reputation-event">', '</span><a href="'.get_comment_link( $action_id ).'">'. get_comment_text( $action_id ).'</a>' );
			break;

		case 'selecting_answer':
			$info = sprintf( __( '%sSelected answer %s','ap' ), '<span class="ap-reputation-event">', '</span><a href="'.get_permalink( $action_id ).'">'. get_the_title( $action_id ).'</a>' );
			break;

		case 'vote_up':
			$info = sprintf( __( '%sUp vote %s %s','ap' ), '<span class="ap-reputation-event">', '</span>'.get_post_type( $action_id ), '<a href="'.get_permalink( $action_id ).'">'.get_the_title( $action_id ).'</a>' );
			break;

		case 'vote_down':
			$info = sprintf( __( '%sDown vote %s %s','ap' ), '<span class="ap-reputation-event">', '</span>'.get_post_type( $action_id ), '<a href="'.get_permalink( $action_id ).'">'.get_the_title( $action_id ).'</a>' );
			break;

		case 'voted_down':
			$info = sprintf( __( '%sDown voted %s','ap' ), '<span class="ap-reputation-event">', '</span>'.get_post_type( $action_id ) );
			break;

		case 'best_answer':
			$info = sprintf( __( '%sBest answer %s','ap' ), '<span class="ap-reputation-event">', '</span>'.get_post_type( $action_id ) );
			break;

		default:
			$info = apply_filters( 'ap_reputation_info_event',  $event, $action_id );
			break;
	}

	return apply_filters( 'ap_reputation_info',  $info, $event, $action_id );
}

function ap_reputation_option() {
	$data  	= wp_cache_get( 'ap_reputation', 'ap' );
	if ( $data === false ) {
		$opt 	= get_option( 'ap_reputation' );
		$data 	= (is_array( $opt ) ? $opt : array()) + ap_default_reputation();
		$data 	= apply_filters( 'ap_reputation_option', $data );
		wp_cache_set( 'ap_reputation', $data, 'ap' );
	}
	return $data;
}

function ap_reputation_by_id($id) {
	$opt = ap_reputation_option();
	foreach ( $opt as $reputation ) {
		if ( $reputation['id'] == $id ) {
			return $reputation; }
	}

	return false;
}

/**
 * @param string $event
 */
function ap_reputation_by_event($event, $only_reputation = false) {
	$opt = ap_reputation_option();
	foreach ( $opt as $reputation ) {
		if ( $reputation['event'] == $event ) {
			if ( $only_reputation ) {
				return $reputation['reputation']; } else {
				return $reputation; }
		}
	}
	return false;
}


function ap_reputation_option_new($title, $desc, $reputation, $event) {
	$opt 	= ap_reputation_option();
	$opt[] = array(
		'id' => count( $opt ),
		'title' => $title,
		'description' => $desc,
		'reputation' => $reputation,
		'event' => $event,
	);
	return update_option( 'ap_reputation', $opt );
}

function ap_reputation_option_update($id, $title, $desc, $reputation, $event) {
	$opt 	= ap_reputation_option();
	foreach ( $opt as $k => $p ) {
		if ( $p['id'] == $id ) {
			$opt[$k]['title'] 		= $title;
			$opt[$k]['description'] = $desc;
			$opt[$k]['reputation'] 		= $reputation;
			$opt[$k]['event'] 		= $event;
		}
	}
	wp_cache_delete( 'ap_reputation', 'ap' );
	return update_option( 'ap_reputation', $opt );
}

function ap_reputation_option_delete($id) {
	$opt 	= ap_reputation_option();
	foreach ( $opt as $k => $p ) {
		if ( $p['id'] == $id ) {
			unset( $opt[$k] );
		}
	}
	return update_option( 'ap_reputation', $opt );
}

/**
 * Get the reputation of a user
 * @param  false|integere $uid    WordPress user id
 * @param  boolean        $short  set it to true for formatted output like 1.2K
 * @return string
 */
function ap_get_reputation($uid = false, $short = false) {
	if ( ! $uid ) {
		$uid = get_current_user_id(); }

	$reputation = get_user_meta( $uid, 'ap_reputation', true );

	if ( $reputation == '' ) {
		return 0;
	} else {
		if ( false !== $short ) {
			return ap_short_num( $reputation ); }

		return $reputation;
	}
}

function ap_get_all_reputation($user_id, $limit = 10) {
	global $wpdb;
	$query = $wpdb->prepare( 'SELECT v.* FROM '.$wpdb->prefix."ap_meta v WHERE v.apmeta_type='reputation' AND v.apmeta_userid = %d order by v.apmeta_date DESC LIMIT %d", $user_id, $limit );

	$key = md5( $query );

	$result = wp_cache_get( $key, 'ap' );

	if ( $result === false ) {
		$result = $wpdb->get_results( $query );
		wp_cache_set( $key, $result, 'ap' );
	}

	return $result;
}

/**
 * @param string $type
 */
function ap_reputation($type, $uid, $reputation, $data, $current_user_id = false) {

	if ( $uid == 0 ) {
		return; }

	if ( $current_user_id === false ) {
		$current_user_id = get_current_user_id(); }

	$reputation = apply_filters( 'ap_reputation',$reputation, $type, $uid, $data );
	ap_alter_reputation( $uid, $reputation );
	ap_reputation_log( $type, $uid, $reputation, $data, $current_user_id );
}

// update reputation
function ap_update_reputation($uid, $reputation) {
	// no negative reputation
	if ( $reputation < 1 ) {
		$reputation = 0;
	}

	update_user_meta( $uid, 'ap_reputation', $reputation );
}

function ap_alter_reputation($uid, $reputation) {
	ap_update_reputation( $uid, ap_get_reputation( $uid ) + $reputation );
}

// add reputation logs to DB
function ap_reputation_log($type, $uid, $reputation, $action_id, $current_user_id) {
	$userinfo = get_userdata( $uid );

	if ( $userinfo->user_login == '' ) {
		return false; }

	if ( $reputation == 0 ) {
		return false; }

	$row = ap_add_meta( $uid, 'reputation', $action_id, $reputation, $type );

	if ( $row !== false ) {
		do_action( 'ap_added_reputation', $uid, $action_id, $reputation, $type, $current_user_id ); }

	return $row;
}

/**
 * @param string $type
 */
function ap_reputation_log_delete($type, $uid, $reputation =null, $data =null) {
	$new_reputation = ap_get_reputation( $uid ) - $reputation;

	$row = ap_delete_meta( array( 'apmeta_type' => 'reputation', 'apmeta_userid' => $uid, 'apmeta_actionid' => $data, 'apmeta_value' => $reputation, 'apmeta_param' => $type ) );
	update_user_meta( $uid, 'ap_reputation', $new_reputation );

	return $row;
}

function ap_default_reputation() {
	$reputation = array(
		array(
			'id'       		=> 1,
			'title'       	=> __( 'New Registration', 'ap' ),
			'description' 	=> __( 'Points given to newly registered user.', 'ap' ),
			'reputation'      	=> '1',
			'event'    		=> 'registration',
		),
		array(
			'id'       		=> 2,
			'title'       	=> __( 'Uploading avatar', 'ap' ),
			'description' 	=> __( 'Awarded for uploading an profile picture.', 'ap' ),
			'reputation'      	=> '2',
			'event'    		=> 'uploaded_avatar',
		),
		array(
			'id'       		=> 3,
			'title'       	=> __( 'Completing profile', 'ap' ),
			'description' 	=> __( 'Awarded for completing profile fields.', 'ap' ),
			'reputation'      	=> '2',
			'event'    		=> 'uploaded_avatar',
		),
		array(
			'id'       		=> 4,
			'title'       	=> __( 'Question', 'ap' ),
			'description' 	=> __( 'For asking a question.', 'ap' ),
			'reputation'      	=> '2',
			'event'    		=> 'new_question',
		),
		array(
			'id'       		=> 5,
			'title'       	=> __( 'Answer', 'ap' ),
			'description' 	=> __( 'For answering a question.', 'ap' ),
			'reputation'      	=> '10',
			'event'    		=> 'new_answer',
		),
		array(
			'id'       		=> 6,
			'title'       	=> __( 'Comment', 'ap' ),
			'description' 	=> __( 'For new comment.', 'ap' ),
			'reputation'      	=> '1',
			'event'    		=> 'new_comment',
		),
		array(
			'id'       		=> 7,
			'title'       	=> __( 'Receive upvote on question', 'ap' ),
			'description' 	=> __( 'When user receive an upvote on question', 'ap' ),
			'reputation'      	=> '2',
			'event'    		=> 'question_upvote',
		),
		array(
			'id'       		=> 8,
			'title'       	=> __( 'Receive upvote on answer', 'ap' ),
			'description' 	=> __( 'When user receive an upvote on answer', 'ap' ),
			'reputation'      	=> '5',
			'event'    		=> 'answer_upvote',
		),
		array(
			'id'       		=> 9,
			'title'       	=> __( 'Receive down vote on question', 'ap' ),
			'description' 	=> __( 'When user receive an down vote on question', 'ap' ),
			'reputation'      	=> '-1',
			'event'    		=> 'question_downvote',
		),
		array(
			'id'       		=> 10,
			'title'       	=> __( 'Receive down vote on answer', 'ap' ),
			'description' 	=> __( 'When user receive an down vote on answer', 'ap' ),
			'reputation'      	=> '-3',
			'event'    		=> 'answer_downvote',
		),
		array(
			'id'       		=> 11,
			'title'       	=> __( 'Up voted questions', 'ap' ),
			'description' 	=> __( 'When user upvote others question', 'ap' ),
			'reputation'      	=> '0',
			'event'    		=> 'question_upvoted',
		),
		array(
			'id'       		=> 12,
			'title'       	=> __( 'Up voted answers', 'ap' ),
			'description' 	=> __( 'When user upvote others answer', 'ap' ),
			'reputation'      	=> '0',
			'event'    		=> 'answer_upvoted',
		),
		array(
			'id'       		=> 13,
			'title'       	=> __( 'Down voted questions', 'ap' ),
			'description' 	=> __( 'When user down vote others question', 'ap' ),
			'reputation'      	=> '-1',
			'event'    		=> 'question_downvoted',
		),
		array(
			'id'       		=> 14,
			'title'       	=> __( 'Down voted answers', 'ap' ),
			'description' 	=> __( 'When user down vote others answers', 'ap' ),
			'reputation'      	=> '-1',
			'event'    		=> 'answer_downvoted',
			'negative'    	=> true,
		),
		array(
			'id'       		=> 15,
			'title'       	=> __( 'Best answer', 'ap' ),
			'description' 	=> __( 'When user\'s answer get selected as best', 'ap' ),
			'reputation'      	=> '10',
			'event'    		=> 'select_answer',
		),
		array(
			'id'       		=> 16,
			'title'       	=> __( 'Selecting answer', 'ap' ),
			'description' 	=> __( 'When user user select an answer.', 'ap' ),
			'reputation'      	=> '2',
			'event'    		=> 'selecting_answer',
		),
	);

	return $reputation;
}

function ap_received_reputation_post($post_id) {
	$reputation 		= ap_reputation_by_event( 'question_upvote', true );
	$vote_count = ap_meta_total_count( 'vote_up', $post_id );
	return $vote_count * $reputation;
}

/**
 * Get total reputation of all users
 * @return integer
 */
function ap_total_reputation() {
	global $wpdb;

	$count = wp_cache_get( 'site_total_reputation', 'ap' );

	if ( false === $count ) {
		$count = $wpdb->get_var( 'SELECT sum(apmeta_value) FROM '.$wpdb->prefix.'ap_meta where apmeta_type = "reputation"' );
		wp_cache_add( 'site_total_reputation', $count, 'ap' );
	}

	return (int) $count;
}

function ap_get_user_reputation_share($user_id) {
	$user_points = ap_get_reputation( $user_id );

	return ($user_points * ap_total_reputation()) / 100;
}

