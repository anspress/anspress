<?php
/**
 * Question class
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://rahularyan.com
 * @copyright 2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

if(!class_exists('Question_Query')):

/**
 * Question
 *
 * This class is for retriving questions based on $args
 */
class Question_Query extends WP_Query {

    public $args = array();

    /**
     * Initialize class
     * @param array $args
     * @access public
     * @since  2.0
     */
    public function __construct( $args = array() ) {

        global $questions;

        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

        if(isset($args['post_parent']))
            $post_parent = $args['post_parent'];
        else
            $post_parent = (get_query_var('parent')) ? get_query_var('parent') : false;

        if(isset($args['orderby']))
            $orderby = $args['orderby'];
        else
            $orderby = (get_query_var('orderby')) ? get_query_var('orderby') : 'active';

        $defaults = array(
           // 'ap_query'      => 'main_questions',
            'post_status'   => array('publish', 'moderate', 'private_question', 'closed'),
            'showposts'     => 20,
            'orderby'       => $orderby,
            'paged'         => $paged,
        );

        if($post_parent)
            $this->args['post_parent'] = $post_parent;

        $this->args = wp_parse_args( $args, $defaults );        

        $this->pre_questions();

        do_action('ap_pre_get_questions', $this);

        $this->args['post_type'] = 'question';        

        $args = $this->args;

        add_action( 'the_post', array($this, 'append_post_var') );
        /**
         * Initialize parent class
         */
        parent::__construct( $args );

        remove_action('ap_pre_get_questions', $this);
        remove_action('the_post', $this);

    }

    public function pre_questions(){
        add_action('ap_pre_get_questions', array($this, 'orderby_questions'));
    }

    /**
     * Modify orderby args
     * @return void
     */
    public function orderby_questions(){
        switch ( $this->args[ 'orderby' ] ) {
            case 'answers' :
                $this->args[ 'orderby' ] = 'meta_value_num';
                $this->args[ 'meta_key' ] = ANSPRESS_ANS_META;
            break;
            case 'unanswered' :
                $this->args[ 'orderby' ] = 'meta_value_num';
                $this->args[ 'meta_key' ] = ANSPRESS_ANS_META ;
                $this->args[ 'meta_value' ] = 0 ;
            break;
            case 'voted' :
                $this->args[ 'orderby'] = 'meta_value_num' ;
                $this->args[ 'meta_key'] = ANSPRESS_VOTE_META;
            break;
            case 'unsolved' :
                $this->args['orderby'] = 'meta_value';
                $this->args['meta_key'] = ANSPRESS_SELECTED_META;
                $this->args['meta_compare'] = 'NOT EXISTS';
 
            break;
            case 'oldest' :
                $this->args['orderby'] = 'date';
                $this->args['order'] = 'ASC';
            break;
            case 'active' :
                $this->args['orderby'] = 'meta_value';
                $this->args['meta_key'] = ANSPRESS_UPDATED_META;
                $this->args['meta_query']  = array(
                    'relation' => 'OR',
                    array(
                        'key' => ANSPRESS_UPDATED_META
                    ),
                );
            break;

            //TOOD: Add more orderby like most viewed, and user order like 'answered by user_id', 'asked_by_user_id'
        }
         
    }

    /**
     * Append meta as a post variable.
     * @param  object $post Wordpress post object
     * @return void
     */
    public function append_post_var( $post ){
        if(is_object($post) && ($post->post_type == 'question' || $post->post_type == 'answer')) {

            $post->net_vote     = ap_net_vote_meta($post->ID);
            $post->selected     = get_post_meta($post->ID, ANSPRESS_SELECTED_META, true);
            $post->closed       = get_post_meta($post->ID, ANSPRESS_CLOSE_META, true);
            

            $votes = ap_post_votes($post->ID);              
            // net vote
            $post->voted_up     = $votes['voted_up'];
            $post->voted_down   = $votes['voted_down'];
            $post->net_vote     = $votes['voted_up'] - $votes['voted_down'];
            
            //closed count
            $post->closed       = get_post_meta($post->ID, ANSPRESS_CLOSE_META, true);
            $post->selected     = get_post_meta($post->ID, ANSPRESS_SELECTED_META, true);
            
            //flagged count
            $post->flag = get_post_meta($post->ID, ANSPRESS_FLAG_META, true);
            
            //favorite count
            $post->favorite     = get_post_meta($post->ID, ANSPRESS_FAV_META, true);
            
            $post->voted_closed = ap_is_user_voted_closed();                    
            $post->flagged = ap_is_user_flagged();

            //if current logged in user voted
            if(is_user_logged_in()){
                $post->favorited    = ap_is_user_favorite($post->ID);
                $userid = get_current_user_id();
                $post->user_voted_up = ap_is_user_voted($post->ID, 'vote_up', $userid); 
                $post->user_voted_down = ap_is_user_voted($post->ID, 'vote_down', $userid); 
            }
        }
    }

}

endif;