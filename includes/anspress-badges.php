<?php
/**
 * AnsPress.
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://rahularyan.com
 * @copyright 2014 Rahul Aryan
 */

class AP_Badges
{
    /**
     * Instance of this class.
     */
    protected static $instance = null;
    /**
     * Return an instance of this class.
     * @return    object    A single instance of this class.
     */
    public static function get_instance()
    {
        
        // If the single instance hasn't been set, set it now.
        if (null == self::$instance) {
            self::$instance = new self;
        }
        
        return self::$instance;
    }
    /**
     * Initialize the plugin by setting localization and loading public scripts
     * and styles.
     */
    public function __construct()
    {
		add_action( 'ap_save_profile', array($this, 'save_profile'), 10, 2);
		add_action('ap_after_avatar_upload', array($this, 'after_upload'));
		
		add_action('ap_event_vote_up', array($this, 'question_vote'), 10, 2);
		
		add_action('ap_added_favorite', array($this, 'favorite_question'), 10, 2);
		
		add_action('after_insert_views', array($this, 'question_view'), 10, 2);
		
		add_action('ap_event_select_answer', array($this, 'select_answer'), 10, 3);

    }
	
	public function save_profile($user, $fields){
		
		//check if user completed all required fields
		if(ap_check_user_profile_complete($user->ID))			
			ap_award_badge($user->ID, 'autobiographer');			

		if(ap_check_if_photogenic($user->ID))
			ap_award_badge($user->ID, 'photogenic');
		

	}
	
	public function after_upload($user_id){
		if(ap_check_if_photogenic($user_id))
			ap_award_badge($user_id, 'photogenic');
	}
	
	public function question_vote($post_id, $counts) {
		$post = get_post($post_id);
		
		// return if not question
		if($post->post_type != 'question')
			return;
		
		$great_question = ap_badge_by_id('great_question');
		$great_question = $great_question['value'];
		
		$good_question 	= ap_badge_by_id('good_question');
		$good_question 	= $good_question['value'];
		
		$nice_question 	= ap_badge_by_id('nice_question');
		$nice_question 	= $nice_question['value'];
		
		$student 		= ap_badge_by_id('student');
		$student 		= $student['value'];


		if(ap_received_point_post($post_id) > $great_question && !ap_received_badge_on_post('great_question', $post_id)){
		
			ap_award_badge($post->post_author, 'great_question', $post_id);
			
		}elseif(ap_received_point_post($post_id) > $good_question && !ap_received_badge_on_post('good_question', $post_id)){
		
			ap_award_badge($post->post_author, 'good_question', $post_id);
			
		}elseif(ap_received_point_post($post_id) > $nice_question && !ap_received_badge_on_post('nice_question', $post_id) ){
		
			ap_award_badge($post->post_author, 'nice_question', $post_id);
			
		}
		
		if(ap_received_point_post($post_id) > $student && !ap_received_badge_on_post('student', $post_id) ){		
			ap_award_badge( $post->post_author, 'student', $post_id );			
		}

	}
	
	public function favorite_question($post_id, $count){
		$post = get_post($post_id);
		
		// return if not question
		if($post->post_type != 'question')
			return;
			
		$favorite_question = ap_badge_by_id('favorite_question');
		$favorite_question = $favorite_question['value'];
		
		$stellar_question = ap_badge_by_id('stellar_question');
		$stellar_question = $stellar_question['value'];
		
		if($count > $favorite_question && !ap_received_badge_on_post('favorite_question', $post_id)){
		
			ap_award_badge($post->post_author, 'favorite_question', $post_id);
			
		}elseif($count > $stellar_question && !ap_received_badge_on_post('stellar_question', $post_id)){
		
			ap_award_badge($post->post_author, 'stellar_question', $post_id);
			
		}
	}
	
	public function question_view($post_id, $count){
		$post = get_post($post_id);
		
		// return if not question
		if($post->post_type != 'question')
			return;
			
		$popular_question = ap_badge_by_id('popular_question');
		$popular_question = $popular_question['value'];
		
		$notable_question = ap_badge_by_id('notable_question');
		$notable_question = $notable_question['value'];
		
		$famous_question = ap_badge_by_id('famous_question');
		$famous_question = $famous_question['value'];
		
		if($count > $popular_question && !ap_received_badge_on_post('popular_question', $post_id)){
		
			ap_award_badge($post->post_author, 'popular_question', $post_id);
			
		}elseif($count > $notable_question && !ap_received_badge_on_post('notable_question', $post_id)){
		
			ap_award_badge($post->post_author, 'notable_question', $post_id);
			
		}elseif($count > $famous_question && !ap_received_badge_on_post('famous_question', $post_id)){
		
			ap_award_badge($post->post_author, 'famous_question', $post_id);
			
		}
		
		$limit = 7 * 86400; //days * seconds per day
		$post_age = current_time('timestamp') - mysql2date('U', $post->post_date_gmt);
		
		if($post_age > $limit){
			if($count < 10 && ap_count_ans_meta($post_id) == 0) 
				ap_award_badge($post->post_author, 'tumbleweed', $post_id);
		}
	}
	
	public function select_answer($userid, $question_id, $answer_id){
		$question = get_post($question_id);
		$scholar = ap_badge_by_id('scholar');
		
		if($question->post_author == $userid && !ap_received_badge_on_post('scholar', $question_id))
			ap_award_badge($question->post_author, 'scholar', $question_id);

	}

}

/* Badge types */
function ap_badge_types(){
	$types = array(
		'gold' 		=> __('Gold', 'ap'),
		'silver' 	=> __('Silver', 'ap'),
		'bronze' 	=> __('Bronze', 'ap'),
	);
	
	return apply_filters('ap_badge_types', $types);
}

function ap_get_badge_type($badge_id){
	$tax_meta = get_option( "badge_$badge_id");
	return $tax_meta['type'];
}

function ap_get_badge_action($badge_id){
	$tax_meta = get_option( "badge_$badge_id");
	return $tax_meta['action'];
}

function ap_get_badge_points($badge_id){
	$tax_meta = get_option( "badge_$badge_id");
	return $tax_meta['points'];
}

function ap_set_badge($user_id, $badge_id, $badge_type, $action_id = 0){
	return ap_add_meta($user_id, 'badge', $action_id, $badge_id, $badge_type);
}

function ap_get_users_all_badges($user_id){
	global $wpdb;
		
	return $wpdb->get_results( $wpdb->prepare('SELECT apmeta_id as meta_id, apmeta_userid as userid, apmeta_value as badge_id, apmeta_param as type, apmeta_date as date FROM '.$wpdb->prefix.'ap_meta WHERE apmeta_userid = %d AND apmeta_type = "badge" GROUP BY apmeta_value', $user_id));

}

function ap_user_badge_count_by_badge($user_id){
	global $wpdb;
		
	$results = $wpdb->get_results( $wpdb->prepare('SELECT count(*) as count, apmeta_value as badge_id FROM '.$wpdb->prefix.'ap_meta WHERE apmeta_userid = %d AND apmeta_type = "badge" GROUP BY apmeta_value', $user_id));
	
	if($results){
		$counts = array();
		foreach($results as $r)
			$counts[$r->badge_id] = $r->count;
		
		return $counts;
	}
	
	return false;
}

function ap_get_user_badge($user_id, $badge_id){
	return ap_get_meta(array('apmeta_type' => 'badge', 'apmeta_userid' => $user_id, 'apmeta_value' => $badge_id));
}

function ap_user_have_badge_type($user_id, $type){
	$badges = ap_get_users_all_badges($user_id, $type);	
}

function ap_badges_option(){
	$data  	= wp_cache_get('ap_badges', 'ap');
	if($data === false){
		$opt 	= get_option('ap_badges');
		$data 	= (is_array($opt) ? $opt : array()) + ap_default_badges();
		$data 	= apply_filters('ap_badges_option', $data);
		wp_cache_set('ap_badges', $data, 'ap');
	}
	return $data;
}

function ap_default_badges(){
	$points = array(
		array(
			'id'       		=> 'autobiographer',
			'title'       	=> __('Autobiographer', 'ap'),
			'description' 	=> __('Completed all user profile fields', 'ap'),
			'min_points'    => 0,
			'value'    		=> false,
			'type'    		=> 'bronze',
			'multiple'    	=> false
		),
		array(
			'id'       		=> 'photogenic',
			'title'       	=> __('Photogenic', 'ap'),
			'description' 	=> __('Uploaded an avatar and cover image', 'ap'),
			'min_points'    => 0,
			'value'    		=> false,
			'type'    		=> 'bronze',
			'multiple'    	=> false
		),
		array(
			'id'       		=> 'nice_question',
			'title'       	=> __('Nice Question', 'ap'),
			'description' 	=> __('Question score of %d or more', 'ap'),
			'min_points'    => 0,
			'value'    		=> 10,
			'type'    		=> 'bronze',
			'multiple'    	=> true
		),
		array(
			'id'       		=> 'good_question',
			'title'       	=> __('Good Question', 'ap'),
			'description' 	=> __('Question score of %d or more', 'ap'),
			'min_points'    => 0,
			'value'    		=> 50,
			'type'    		=> 'silver',
			'multiple'    	=> true
		),
		array(
			'id'       		=> 'great_question',
			'title'       	=> __('Great Question', 'ap'),
			'description' 	=> __('Question score of %d or more', 'ap'),
			'min_points'    => 0,
			'value'    		=> 100,
			'type'    		=> 'gold',
			'multiple'    	=> true
		),
		array(
			'id'       		=> 'popular_question',
			'title'       	=> __('Popular Question', 'ap'),
			'description' 	=> __('Asked a question with %d views ', 'ap'),
			'min_points'    => 0,
			'value'    		=> 100,
			'type'    		=> 'bronze',
			'multiple'    	=> true
		),
		array(
			'id'       		=> 'notable_question',
			'title'       	=> __('Notable Question', 'ap'),
			'description' 	=> __('Asked a question with %d views ', 'ap'),
			'min_points'    => 0,
			'value'    		=> 500,
			'type'    		=> 'silver',
			'multiple'    	=> true
		),
		array(
			'id'       		=> 'famous_question',
			'title'       	=> __('Famous Question', 'ap'),
			'description' 	=> __('Asked a question with %d views', 'ap'),
			'min_points'    => 0,
			'value'    		=> 1500,
			'type'    		=> 'gold',
			'multiple'    	=> true
		),
		array(
			'id'       		=> 'favorite_question',
			'title'       	=> __('Favorite Question', 'ap'),
			'description' 	=> __('Question favorited by %d users', 'ap'),
			'min_points'    => 0,
			'value'    		=> 5,
			'type'    		=> 'silver',
			'multiple'    	=> true
		),
		array(
			'id'       		=> 'stellar_question',
			'title'       	=> __('Stellar Question', 'ap'),
			'description' 	=> __('Question favorited by %d users', 'ap'),
			'min_points'    => 0,
			'value'    		=> 20,
			'type'    		=> 'gold',
			'multiple'    	=> true
		),
		array(
			'id'       		=> 'scholar',
			'title'       	=> __('Scholar', 'ap'),
			'description' 	=> __('Asked a question and accepted an answer', 'ap'),
			'min_points'    => 0,
			'value'    		=> false,
			'type'    		=> 'bronze',
			'multiple'    	=> false
		),
		array(
			'id'       		=> 'student',
			'title'       	=> __('Student', 'ap'),
			'description' 	=> __('Asked first question with score of %d or more', 'ap'),
			'min_points'    => 0,
			'value'    		=> 1,
			'type'    		=> 'bronze',
			'multiple'    	=> false
		),
		array(
			'id'       		=> 'tumbleweed',
			'title'       	=> __('Tumbleweed', 'ap'),
			'description' 	=> __('Asked a question with no votes, no answers, no comments, and low views for a week', 'ap'),
			'min_points'    => 0,
			'value'    		=> false,
			'type'    		=> 'bronze',
			'multiple'    	=> false
		),
	);
	return $points;
}

function ap_badge_by_id($id){
	$opt = ap_badges_option();
	foreach( $opt as $badge)
		if($badge['id'] == $id)
			return $badge;
	
	return false;
}
function ap_badge_option_update($id, $title, $desc, $type, $min_points, $multiple = false){
	$opt 	= ap_badges_option();
	foreach($opt as $k => $p){
		if($p['id'] == $id){
			$opt[$k]['title'] 		= $title;
			$opt[$k]['description'] = $desc;
			$opt[$k]['type'] 		= $type;
			$opt[$k]['min_points'] 	= $min_points;
			$opt[$k]['multiple'] 	= $multiple;
		}
	}
	$update = update_option('ap_badges', $opt);
	wp_cache_delete('ap_badges', 'ap');
	return $update;
}
function ap_badge_option_new($id, $title, $desc, $type, $min_points, $multiple = false){
	$opt 	= ap_badges_option();
	$opt[] = array(
		'id' 			=> strtolower(str_replace(' ', '_', $title)),
		'title' 		=> $title,
		'description' 	=> $desc,
		'type' 			=> $type,
		'min_points' 	=> $min_points,
		'multiple' 		=> $multiple,
	);
	$new = update_option('ap_badges', $opt);
	wp_cache_delete('ap_badges', 'ap');
	return $new;
}
function ap_badge_option_delete($id){
	$opt 	= ap_badges_option();
	foreach($opt as $k => $p){
		if($p['id'] == $id){
			unset($opt[$k]);
		}
	}
	return update_option('ap_badges', $opt);
}

function ap_award_badge($user_id, $id, $action_id = 0){
	$badge = ap_badge_by_id($id);

	if(!empty($badge)){
		if(!$badge['multiple']){
			$received = ap_get_user_badge($user_id, $badge['id']);
			if(!is_array($received))
				ap_set_badge($user_id, $badge['id'], $badge['type'], $action_id);
		}else{
			ap_set_badge($user_id, $badge['id'], $badge['type'], $action_id);
		}
	}

}

function ap_received_badge_on_post($badge_id, $post_id){
	return ap_get_meta(array('apmeta_type' => 'badge', 'apmeta_actionid' => $post_id, 'apmeta_value' => $badge_id));
}