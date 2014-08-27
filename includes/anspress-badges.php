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
		add_action( 'ap_save_profile', array($this, 'completed_profile'), 10, 2);
		


    }
	
	public function completed_profile($user, $fields){
		
		//check if user completed all required fields
		if(ap_check_user_profile_complete($user->ID)){
			
			ap_award_badges($user->ID, 'completed_profile');			
		}

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

function ap_set_badge($user_id, $badge_id, $badge_type){
	return ap_add_meta($user_id, 'badge', $badge_id, $badge_type);
}

function ap_get_badges($user_id, $badge_type = false){
	return ap_get_meta(array('apmeta_type' => 'badge', 'apmeta_userid' => $user_id, 'apmeta_value' => $badge_type));
}

function ap_get_user_badge($user_id, $badge_id){
	return ap_get_meta(array('apmeta_type' => 'badge', 'apmeta_userid' => $user_id, 'apmeta_actionid' => $badge_id));
}

function ap_user_have_badge_type($user_id, $type){
	$badges = ap_get_badges($user_id, $type);	
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
			'id'       		=> 1,
			'title'       	=> __('Autobiographer', 'ap'),
			'description' 	=> __('Completed all user profile fields', 'ap'),
			'min_points'    => 0,
			'type'    		=> 'bronze',
			'event'    		=> 'completed_profile',
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
function ap_badge_option_update($id, $title, $desc, $type, $min_points, $event, $multiple = false){
	$opt 	= ap_badges_option();
	foreach($opt as $k => $p){
		if($p['id'] == $id){
			$opt[$k]['title'] 		= $title;
			$opt[$k]['description'] = $desc;
			$opt[$k]['type'] 		= $type;
			$opt[$k]['min_points'] 	= $min_points;
			$opt[$k]['event'] 		= $event;
			$opt[$k]['multiple'] 	= $multiple;
		}
	}
	$update = update_option('ap_badges', $opt);
	wp_cache_delete('ap_badges', 'ap');
	return $update;
}
function ap_badge_option_new($id, $title, $desc, $type, $min_points, $event, $multiple = false){
	$opt 	= ap_badges_option();
	$opt[] = array(
		'id' 			=> count($opt),
		'title' 		=> $title,
		'description' 	=> $desc,
		'type' 			=> $type,
		'min_points' 	=> $min_points,
		'event' 		=> $event,
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

function ap_badges_by_event($event){
	$opt = ap_badges_option();
	$badges = array();
	foreach( $opt as $badge)
		if($badge['event'] == $event){
			$badges[] = $badge;
		}
	return $badges;
}

function ap_award_badges($user_id, $event){
	$badges = ap_badges_by_event('completed_profile');
	
	if(!empty($badges))
		foreach($badges as $b){
			if(!$b['multiple']){
				$received = ap_get_user_badge($user_id, $b['id']);
				if(!$received)
					ap_set_badge($user_id, $b['id'], $b['type']);
			}else{
				ap_set_badge($user_id, $b['id'], $b['type']);
			}
		}
		
		
}