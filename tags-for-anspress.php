<?php
/**
 * Tags extension for AnsPress
 *
 * AnsPress - Question and answer plugin for WordPress
 *
 * @package   Tags for AnsPress
 * @author    Rahul Aryan <wp3@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://wp3.in/categories-for-anspress
 * @copyright 2014 WP3.in & Rahul Aryan
 *
 * @wordpress-plugin
 * Plugin Name:       Tags for AnsPress
 * Plugin URI:        http://wp3.in/categories-for-anspress
 * Description:       Extension for AnsPress. Add tags in AnsPress.
 * Donate link: https://www.paypal.com/cgi-bin/webscr?business=rah12@live.com&cmd=_xclick&item_name=Donation%20to%20AnsPress%20development
 * Version:           1.0
 * Author:            Rahul Aryan
 * Author URI:        http://wp3.in
 * Text Domain:       ap
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}


class Tags_For_AnsPress
{

    /**
     * Class instance
     * @var object
     * @since 1.0
     */
    private static $instance;


    /**
     * Get active object instance
     *
     * @since 1.0
     *
     * @access public
     * @static
     * @return object
     */
    public static function get_instance() {

        if ( ! self::$instance )
            self::$instance = new Categories_For_AnsPress();

        return self::$instance;
    }
    /**
     * Initialize the class
     * @since 2.0
     */
    public function __construct()
    {
        if( ! class_exists( 'AnsPress' ) )
            return; // AnsPress not installed

        if (!defined('TAGS_FOR_ANSPRESS_DIR'))    
            define('TAGS_FOR_ANSPRESS_DIR', plugin_dir_path( __FILE__ ));

        if (!defined('TAGS_FOR_ANSPRESS_URL'))   
                define('TAGS_FOR_ANSPRESS_URL', plugin_dir_url( __FILE__ ));

        $this->includes();

        // internationalization
        add_action( 'init', array( $this, 'textdomain' ) );

        //Register question tag
        add_action('init', array($this, 'register_question_tag'), 1);
        add_filter('ap_default_options', array($this, 'ap_default_options') );
        add_action('ap_admin_menu', array($this, 'admin_tags_menu'));

        add_action('ap_option_navigation', array($this, 'option_navigation' ));
        add_action('ap_option_fields', array($this, 'option_fields' ));
        add_action('ap_display_question_metas', array($this, 'ap_display_question_metas' ), 10, 2);
        add_action('ap_after_question_title', array($this, 'ap_after_question_title' ));
        add_action( 'ap_enqueue', array( $this, 'ap_enqueue' ) );
        add_filter('term_link', array($this, 'term_link_filter'), 10, 3);

        add_shortcode( 'anspress_question_tags', array( 'AnsPress_Tags_Shortcode', 'anspress_tags' ) );
        add_shortcode( 'anspress_question_tag', array( 'AnsPress_Tag_Shortcode', 'anspress_tag' ) );

        add_action('ap_ask_form_fields', array($this, 'ask_from_tag_field'), 10, 2);
        add_action('ap_ask_fields_validation', array($this, 'ap_ask_fields_validation'));
        add_action( 'ap_after_new_question', array($this, 'after_new_question'), 10, 2 );
        add_action( 'ap_after_update_question', array($this, 'after_new_question'), 10, 2 );

    }

    public function includes(){
        require_once( TAGS_FOR_ANSPRESS_DIR . 'shortcode-tags.php' );
        require_once( TAGS_FOR_ANSPRESS_DIR . 'shortcode-tag.php' );
    }

    /**
     * Load plugin text domain
     *
     * @since 1.0
     *
     * @access public
     * @return void
     */
    public static function textdomain() {

        // Set filter for plugin's languages directory
        $lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';

        // Load the translations
        load_plugin_textdomain( 'tags_for_anspress', false, $lang_dir );

    }
    
    /**
     * Register tag taxonomy for question cpt
     * @return void
     * @since 2.0
     */
    public function register_question_tag(){

        /**
         * Labesl for tag taxonomy
         * @var array
         */

        $tag_labels = array(
            'name' => __('Question Tags', 'ap'),
            'singular_name' => _x('Tag', 'ap'),
            'all_items' => __('All Tags', 'ap'),
            'add_new_item' => _x('Add New Tag', 'ap'),
            'edit_item' => __('Edit Tag', 'ap'),
            'new_item' => __('New Tag', 'ap'),
            'view_item' => __('View Tag', 'ap'),
            'search_items' => __('Search Tag', 'ap'),
            'not_found' => __('Nothing Found', 'ap'),
            'not_found_in_trash' => __('Nothing found in Trash', 'ap'),
            'parent_item_colon' => ''
        );

        /**
         * FILTER: ap_question_tag_labels
         * Filter ic called before registering question_tag taxonomy
         */
       $tag_labels = apply_filters( 'ap_question_tag_labels',  $tag_labels);
       

        /**
         * Arguments for tag taxonomy
         * @var array
         * @since 2.0
         */
        $tag_args = array(
            'hierarchical' => true,
            'labels' => $tag_labels,
            'rewrite' => false
        );

        /**
         * FILTER: ap_question_tag_args
         * Filter ic called before registering question_tag taxonomy
         */
        $tag_args = apply_filters( 'ap_question_tag_args',  $tag_args);

        /**
         * Now let WordPress know about our taxonomy
         */
        register_taxonomy('question_tag', array('question'), $tag_args);

    }

    /**
     * Apppend default options
     * @param   array $defaults
     * @return  array           
     * @since   1.0
     */             
    public function ap_default_options($defaults)
    {

        $defaults['max_tags']       = 5;
        $defaults['min_tags']       = 1;
        $defaults['tags_per_page']   = 36;

        return $defaults;
    }

    /**
     * Add tags menu in wp-admin
     * @return void
     * @since 2.0
     */
    public function admin_tags_menu(){
        add_submenu_page('anspress', __('Questions Tags', 'tags_for_anspress'), __('Tags', 'tags_for_anspress'), 'manage_options', 'edit-tags.php?taxonomy=question_tag');
    }

    /**
     * Register tags option tab in AnsPress options
     * @param  array $navs Default navigation array
     * @return array
     * @since 1.0
     */
    public function option_navigation($navs){
        $navs['tags'] =  __('Tags', 'tags_for_anspress');
        return $navs;
    }

    /**
     * Option fields
     * @param  array  $settings
     * @return string
     * @since 1.0
     */
    public function option_fields($settings){
        $active = (isset($_REQUEST['option_page'])) ? $_REQUEST['option_page'] : 'general' ;
        if ($active == 'tags') {
            ?>
                <div class="tab-pane" id="ap-tags">       
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><label for="tags_per_page"><?php _e('Tags per page', 'tags_for_anspress'); ?></label></th>
                            <td>
                                <input type="number" min="1" name="anspress_opt[tags_per_page]" id="tags_per_page" value="<?php echo $settings['tags_per_page'] ; ?>" />                                
                                <p class="description"><?php _e('Tags to show per page', 'tags_for_anspress'); ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="max_tags"><?php _e('Maximum tags', 'ap'); ?></label></th>
                            <td>
                                <input type="number" min="1" id="max_tags" name="anspress_opt[max_tags]" value="<?php echo $settings['max_tags']; ?>" />
                                <p class="description"><?php _e('Maximum numbers of tags that user can add when asking.', 'ap'); ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="min_tags"><?php _e('Minimum tags', 'ap'); ?></label></th>
                            <td>
                                <input type="number" min="1" id="min_tags" name="anspress_opt[min_tags]" value="<?php echo $settings['min_tags']; ?>" />
                                <p class="description"><?php _e('Minimum numbers of tags user need to add when asking.', 'ap'); ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="min_point_new_tag"><?php _e('Minimum points to create new tag', 'ap'); ?></label></th>
                            <td>
                                <input type="number" min="1" id="min_point_new_tag" name="anspress_opt[min_point_new_tag]" value="<?php echo $settings['min_point_new_tag']; ?>" />
                                <p class="description"><?php _e('User must have more or equal to those points to create a new tag.', 'ap'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
            <?php
        }
        
    }

    /**
     * Append meta display
     * @param  array $metas
     * @param array $question_id        
     * @return array
     * @since 2.0
     */
    public function ap_display_question_metas($metas, $question_id)
    {   
        if(ap_question_have_tags($question_id) && !is_singular('question'))
            $metas['tags'] = ap_question_tags_html(array('label' => __('Tagged ', 'tags_for_anspress'), 'show' => 1));

        return $metas;
    }

    /**
     * Hook tags after post
     * @param   object $post 
     * @return  string    
     * @since   1.0   
     */
    public function ap_after_question_title($post)
    {
        if(ap_question_have_tags())
            echo '<div class="ap-post-tags clearfix">'. ap_question_tags_html(array('list' => true, 'label' => '', 'class' => 'ap-ul-inline' )) .'</div>';
    }

    /**
     * Enqueue scripts
     * @since 1.0
     */
    public function ap_enqueue()
    {
        wp_enqueue_script( 'tagsinput', ap_get_theme_url('js/bootstrap-tagsinput.min.js', TAGS_FOR_ANSPRESS_URL));
        wp_enqueue_script( 'tags_js', ap_get_theme_url('js/tags_js.js', TAGS_FOR_ANSPRESS_URL));
        wp_enqueue_style( 'tagsinput_css', ap_get_theme_url('css/bootstrap-tagsinput.css', TAGS_FOR_ANSPRESS_URL));
        wp_enqueue_style( 'tags_css', ap_get_theme_url('css/tags.css', TAGS_FOR_ANSPRESS_URL));
        
    }

    public function term_link_filter( $url, $term, $taxonomy ) {
        if($taxonomy == 'question_tag'){
            $url = add_query_arg(array('question_tag' => $term->term_id), get_permalink(ap_opt('question_tag_page_id')));
        }
        return $url;
       
    }

    /**
     * add tag field in ask form
     * @param  array $validate
     * @return void
     * @since 2.0
     */
    public function ask_from_tag_field($args, $editing){
        global $editing_post;

        if($editing){
            $tags = get_the_terms( $editing_post->ID, 'question_tag' );
            $tags_string = '';
            
            if(is_array($tags))
                foreach($tags as $t)
                    $tags_string .= $t->name.',';
        }

        $args['fields'][] = array(
            'name' => 'tag',
            'label' => __('Tags', 'ap'),
            'type'  => 'text',
            'value' => ( $editing ? $tags_string :  sanitize_text_field(@$_POST['tag'] ))  ,
            'taxonomy' => 'question_tag',
            'desc' => __('Slowly type for suggestions', 'ap'),
            'order' => 11,
            'attr' => 'data-role="ap-tagsinput"'
        );

        return $args;
    }

    /**
     * add tag in validation field
     * @param  array $fields
     * @return array
     * @since  1.0
     */
    public function ap_ask_fields_validation($args){
        $args['tag'] = array(
            'sanitize' => array('sanitize_tags'),
            'validate' => array('required', 'comma_separted_count' => ap_opt('min_tags')),
        );

        return $args;
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
        global $validate;
        $fields = $validate->get_sanitized_fields();
        if(isset($fields['tag'])){
            $tags = explode(',', $fields['tag']);
            wp_set_object_terms( $post_id, $tags, 'question_tag' );
        }
    }

}

/**
 * Get everything running
 *
 * @since 1.0
 *
 * @access private
 * @return void
 */

function tags_for_anspress() {
    $discounts = new Tags_For_AnsPress();
}
add_action( 'plugins_loaded', 'tags_for_anspress' );

/**
 * Register activatin hook
 * @return void
 * @since  1.0
 */
function activate_tags_for_anspress(){
    // create and check for tags base page
    
    $page_to_create = array('question_tags' => __('Tags', 'tags_for_anspress'), 'question_tag' => __('Tag', 'tags_for_anspress'));

    foreach($page_to_create as $k => $page_title){
        // create page
        
        // check if page already exists
        $page_id = ap_opt("{$k}_page_id");
        
        $post = get_post($page_id);

        if(!$post){
            
            $args['post_type']          = "page";
            $args['post_content']       = "[anspress_{$k}]";
            $args['post_status']        = "publish";
            $args['post_title']         = $page_title;
            $args['comment_status']     = 'closed';
            $args['post_parent']        = ap_opt('questions_page_id');
            
            // now create post
            $new_page_id = wp_insert_post ($args);
        
            if($new_page_id){
                $page = get_post($new_page_id);
                ap_opt("{$k}_page_slug", $page->post_name);
                ap_opt("{$k}_page_id", $page->ID);
            }
        }
    }
}
register_activation_hook( __FILE__, 'activate_tags_for_anspress'  );

/**
 * Output tags html
 * @param  array  $args 
 * @return string
 * @since 1.0
 */
function ap_question_tags_html($args = array()){
    
    $defaults  = array(
        'question_id'   => get_the_ID(),
        'list'           => false,
        'tag'           => 'span',
        'class'         => 'question-tags',
        'label'         => __('Tagged', 'tags_for_anspress'),
        'echo'          => false,
        'show'          => 0
    );

    if(!is_array($args)){
        $defaults['question_id '] = $args;
        $args = $defaults;
    }else{
        $args = wp_parse_args( $args, $defaults );
    }
        
    $tags = get_the_terms($args['question_id'], 'question_tag' );
    
    if($tags && count($tags) >0){
        $o = '';
        if($args['list']){
            $o = '<ul class="'.$args['class'].'">';
            foreach($tags as $t){
                $o .= '<li><a href="'.esc_url( get_term_link($t)).'" title="'.$t->description.'">'. $t->name .' &times; <i class="tax-count">'.$t->count.'</i></a></li>';
            }
            $o .= '</ul>';
        }else{
            $o = $args['label'];
            $o .= '<'.$args['tag'].' class="'.$args['class'].'">';
            $i = 1;
            foreach($tags as $t){
                $o .= '<a href="'.esc_url( get_term_link($t)).'" title="'.$t->description.'">'. $t->name .'</a> ';
                if($args['show'] > 0 && $i == $args['show']){
                    $o_n = '';
                    foreach($tags as $tag_n)
                        $o_n .= '<a href="'.esc_url( get_term_link($tag_n)).'" title="'.$tag_n->description.'">'. $tag_n->name .'</a> ';

                    $o .= '<a class="ap-tip" data-tipclass="tags-list" title="'.esc_html($o_n).'" href="#">'. sprintf(__('%d More', 'ap'), count($tags)) .'</a>';
                    break;
                }
                $i++;
            }
            $o .= '</'.$args['tag'].'>';
        }

        if($args['echo'])
            echo $o;

        return $o;
    }

    
}


function ap_tag_details(){

    $var = get_query_var('question_tag');

    $tag = get_term_by('slug', $var, 'question_tag');
    echo '<div class="clearfix">';
    echo '<h3><a href="'.get_tag_link( $tag ).'">'. $tag->name .'</a></h3>';
    echo '<div class="ap-taxo-meta">';
    echo '<span class="count">'. $tag->count .' '.__('Questions', 'ap').'</span>';  
    echo '<a class="aicon-rss feed-link" href="' . get_term_feed_link($tag->term_id, 'question_tag') . '" title="Subscribe to '. $tag->name .'" rel="nofollow"></a>';
    echo '</div>';
    echo '</div>';
    
    echo '<p class="desc clearfix">'. $tag->description .'</p>';
}

function ap_question_have_tags($question_id = false){
    if(!$question_id)
        $question_id = get_the_ID();
    
    $tags = wp_get_post_terms( $question_id, 'question_tag');
    
    if(!empty($tags))
        return true;
    
    return false;
}

function is_question_tag(){
    if(get_the_ID() == ap_opt('question_tag_page_id'))
        return true;
        
    return false;
}

/**
 * Check if current page is tags page
 * @return boolean
 * @since 1.0
 */
function is_question_tags(){
    $queried_object = get_queried_object();
    if(isset($queried_object->ID) && $queried_object->ID == ap_opt('question_tags_page_id'))
        return true;
        
    return false;
}