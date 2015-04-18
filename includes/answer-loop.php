<?php
/**
 * AnsPress answer loop related functions and classes
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      http://anspress.io
 * @copyright 2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

if(!class_exists('Answers_Query')):

/**
 * Question
 *
 * This class is for retriving answers based on $args
 */
class Answers_Query extends WP_Query {

    public $args = array();

    /**
     * Initialize class
     * @param array $args
     * @access public
     * @since  2.0
     */
    public function __construct( $args = array() ) {

        global $answers;

        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

        if(isset($args['question_id']))
            $question_id = $args['question_id'];

        if(isset($args['orderby']))
            $orderby = $args['orderby'];
        else
            $orderby = (get_query_var('ap_sort')) ? get_query_var('ap_sort') : 'active';

        $defaults = array(
            'post_status'       => array('publish', 'moderate', 'private_post'),
            'showposts'         => ap_opt('answers_per_page'),
            'orderby'           => $orderby,
            'paged'             => $paged,
            'only_best_answer'  => false,
            'include_best_answer'  => false,
        );

        $this->args = wp_parse_args( $args, $defaults );
        
        if(!empty($question_id))
            $this->args['post_parent'] = $question_id;

        $this->pre_answers();

        do_action('ap_pre_get_answers', $this);

        $this->args['post_type'] = 'answer';        

        $args = $this->args;

        /**
         * Initialize parent class
         */
        parent::__construct( $args );

        remove_action('ap_pre_get_answers', $this);
    }

    public function pre_answers(){
        add_action('ap_pre_get_answers', array($this, 'orderby_answers'));
    }

    /**
     * Modify orderby args
     * @return void
     */
    public function orderby_answers(){
        $this->args['meta_query'] = array();
        switch ( $this->args[ 'orderby' ] ) {
            case 'voted' :
                $this->args[ 'orderby'] = 'meta_value_num' ;
                $this->args['meta_query']  = array(
                    'relation' => 'AND',
                    array(
                        'key'       => ANSPRESS_VOTE_META,
                    )
                );
            break;
            case 'oldest' :
                $this->args['orderby'] = 'meta_value date';
                $this->args['order'] = 'ASC';
            break;
            case 'newest' :
                $this->args['orderby'] = 'meta_value date';
                $this->args['order'] = 'DESC';
            break;
            default :
                $this->args['orderby'] = 'meta_value';
                $this->args['meta_key'] = ANSPRESS_UPDATED_META;
                $this->args['meta_query']  = array(
                    'relation' => 'AND',
                    array(
                        'key' => ANSPRESS_UPDATED_META
                    )
                );
            break;
        }

        if(!$this->args['include_best_answer'])
            $this->args['meta_query'][] = array(
                'key'           => ANSPRESS_BEST_META,
                'type'          => 'BOOLEAN',
                'compare'       => '!=',
                'value'         => '1'
            );
         
    }

}

endif;

/**
 * Display answers of a question
 * @param  array  $args
 * @return void
 * @since  2.0
 */
function ap_get_answers($args = array()){

    if(empty($args['question_id']))
        $args['question_id'] = get_question_id();

    $sort = get_query_var('ap_sort');

    if(empty($sort ))
        $sort = ap_opt('answers_sort');

    $args['orderby'] = $sort;

    anspress()->answers = new Answers_Query($args);   
    
    include(ap_get_theme_location('answers.php'));

    wp_reset_postdata();
}

/** 
 * Get select answer object
 * @since   2.0
 */
function ap_get_best_answer($question_id = false){
    global $answers;
    
    if(!$question_id) 
        $question_id = get_question_id();

    $answer_id = ap_selected_answer($question_id);

    anspress()->answers = new WP_Query(array('p' => $answer_id, 'post_type' => 'answer'));    
    
    while ( anspress()->answers->have_posts() ) : anspress()->answers->the_post(); 
        include(ap_get_theme_location('answer.php'));
    endwhile;

    wp_reset_postdata();
}


function ap_have_answers(){
    return anspress()->answers->have_posts();
}

function ap_answers(){  
   return anspress()->answers->have_posts();
}

function ap_the_answers(){
    return anspress()->answers->the_post(); 
}

function ap_answer_the_object(){
    return anspress()->answers->post;
}

/**
 * Echo active answer id
 * @return void
 * @since 2.1
 */
function ap_answer_the_answer_id(){
    echo ap_answer_get_the_answer_id();
}
    
    /**
     * Get the active answer id
     * @return integer
     * @since 2.1
     */
    function ap_answer_get_the_answer_id(){
        return ap_answer_the_object()->ID;
    }

/**
 * Echo active answer question id
 * @return void
 * @since 2.1
 */
function ap_answer_the_question_id(){
    echo ap_answer_get_the_question_id();
}
    
    /**
     * Get the active answer question id
     * @return integer
     * @since 2.1
     */
    function ap_answer_get_the_question_id(){
        return ap_answer_the_object()->post_parent;
    }

/**
 * Check if user can view current answer
 * @return boolean
 * @since 2.1
 */
function ap_answer_user_can_view(){
    return ap_user_can_view_post(ap_answer_get_the_question_id());
}

/**
 * Check if current answer is selected as a best
 * @return boolean
 * @since 2.1
 */
function ap_answer_is_best($answer_id = false){
    $answer_id = ap_parameter_empty($answer_id, ap_answer_get_the_answer_id());

    $meta = get_post_meta($answer_id, ANSPRESS_BEST_META, true);
    
    if($meta) return true;
    
    return false;
}

/**
 * Get current answer author id
 * @return integer
 * @since 2.1
 */
function ap_answer_get_author_id(){
    return ap_answer_the_object()->post_author;
}

/**
 * echo user profile link
 * @return 2.1
 */
function ap_answer_the_author_link(){
    echo ap_answer_get_the_author_link();
}
    /**
     * Return the author profile link
     * @return string
     * @since 2.1
     */
    function ap_answer_get_the_author_link(){
        return ap_user_link(ap_question_get_author_id());
    }

function ap_answer_the_author_avatar($size = false){
    $size = ap_parameter_empty(ap_opt('avatar_size_qanswer'), 45);
    echo ap_answer_get_the_author_avatar( $size );
}
    /**
     * Return question author avatar
     * @param  integer $size
     * @return string
     * @since 2.1
     */
    function ap_answer_get_the_author_avatar($size = 45){
        return get_avatar( ap_question_get_author_id(), $size );
    }

/**
 * Output active answer vote button
 * @return 2.1
 */
function ap_answer_the_vote_button(){
    ap_vote_btn(ap_answer_the_object());
}

/**
 * Output comment template if enabled.
 * @return void
 * @since 2.1
 */
function ap_answer_the_comments(){
    if(ap_opt('show_comments_by_default') && !ap_opt('disable_comments_on_answer')) 
        comments_template();
}