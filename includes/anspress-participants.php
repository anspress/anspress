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

class AP_Participents
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

    }
		
}

/* Insert participant  */
function ap_add_parti($post_id, $user_id, $action, $parm = false){
	$rows = ap_add_meta($user_id, 'parti', $post_id, $action, $parm);
	
	/* Update the meta only if successfully created */
	if($rows !== false){
		$current_parti = ap_get_parti($post_id, true);
		update_post_meta($post_id, ANSPRESS_PARTI_META, $current_parti);
	}
}

/* Remove particpants from db when user delete its post or comment */
function ap_remove_parti($post_id, $user_id = false, $action = false){
	$rows = ap_delete_meta(array('apmeta_type' => 'parti', 'apmeta_actionid' => $post_id, 'apmeta_userid' => $user_id));
	
	/* Update the meta only if successfully deleted */
	if($rows !== false){
		$current_parti = ap_get_parti($post_id, true);
		update_post_meta($post_id, ANSPRESS_PARTI_META, $current_parti );
	}
}

function ap_get_parti($post_id, $count = false){
	global $wpdb;
	if($count)		
		return ap_meta_total_count('parti', $post_id, false, 'apmeta_userid');
	else	
		return ap_get_all_meta(array(
			'where' => array(
				'apmeta_type' => array('value' => 'parti', 'compare' => '=', 'relation' => 'AND'), 
				'apmeta_actionid' => array('value' => $post_id, 'compare' => '=', 'relation' => 'AND'), 
			),
			'group' => array(
				'apmeta_userid' => array('relation' => 'AND'),
			)),
		5);
}

function ap_get_all_parti($avatar_size = 40, $post_id = false){
	if(!$post_id)
		$post_id = get_the_ID();
		
	$parti = ap_get_parti($post_id);
	
	echo '<h3 class="ap-question-side-title">'. sprintf( _n('<span>1</span> Participant', '<span>%d</span> Participants', count($parti), 'ap'), count($parti)) .'</h3>';
	
	echo '<ul class="ap-participants-list ap-inline-list clearfix">';	
	foreach($parti as $p){
		?>
			<li><?php echo get_avatar($p->apmeta_userid, $avatar_size); ?></li>
		<?php
	}	
	echo '</ul>';
	
}
