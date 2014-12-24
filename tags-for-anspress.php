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

        // internationalization
        add_action( 'init', array( $this, 'textdomain' ) );

        //Register question tag
        add_action('init', array($this, 'register_question_tag'), 1);
        add_filter('ap_default_options', array($this, 'ap_default_options') );
        add_action('ap_admin_menu', array($this, 'admin_tags_menu'));

        add_action('ap_option_navigation', array($this, 'option_navigation' ));
        add_action('ap_option_fields', array($this, 'option_fields' ));
        add_action('ap_display_question_metas', array($this, 'ap_display_question_metas' ), 10, 2);
        add_action('ap_after_post_content', array($this, 'ap_after_post_content' ));

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
        if(ap_opt('enable_tags')){

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
    }

    /**
     * Apppend default options
     * @param   array $defaults
     * @return  array           
     * @since   1.0
     */             
    public function ap_default_options($defaults)
    {
        $defaults['enable_tags']    = true;
        $defaults['max_tags']       = 5;
        $defaults['min_tags']       = 1;

        return $defaults;
    }

    /**
     * Add tags menu in wp-admin
     * @return void
     * @since 2.0
     */
    public function admin_tags_menu(){
        if(ap_opt('enable_tags'))
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
                            <th scope="row"><label for="enable_tags"><?php _e('Enable Tags', 'ap'); ?></label></th>
                            <td>
                                <input type="checkbox" id="enable_tags" name="anspress_opt[enable_tags]" value="1" <?php checked( true, $settings['enable_tags'] ); ?> />
                                <p class="description"><?php _e('Enable or disable tags system', 'tags_for_anspress'); ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="tags_per_page"><?php _e('Tags per page', 'tags_for_anspress'); ?></label></th>
                            <td>
                                <input type="number" min="1" name="anspress_opt[tags_per_page]" id="tags_per_page" value="<?php echo $settings['tags_per_page'] ; ?>" />                                
                                <p class="description"><?php _e('Tags to show per page', 'tags_for_anspress'); ?></p>
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
        if(ap_opt('enable_tags') &&  ap_question_have_tags($question_id))
            $metas['tags'] = ap_question_tags_html(array('label' => __('Tagged ', 'tags_for_anspress')));

        return $metas;
    }

    /**
     * Hook tags after post
     * @param   object $post 
     * @return  string    
     * @since   1.0   
     */
    public function ap_after_post_content($post)
    {
        if(ap_question_have_tags())
            echo '<div class="ap-post-tags">'. ap_question_tags_html(array('label' => '' )) .'</div>';
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
 * Output tags html
 * @param  array  $args 
 * @return string
 * @since 1.0
 */
function ap_question_tags_html($args = array()){
    /* return if tags is disabled */
    if(!ap_opt('enable_tags'))
        return;
    
    $defaults  = array(
        'question_id'   => get_the_ID(),
        'list'           => false,
        'tag'           => 'span',
        'class'         => 'question-tags',
        'label'         => __('Tagged', 'tags_for_anspress'),
        'echo'          => false
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
                $o .= '<li><a href="'.esc_url( get_term_link($t)).'" title="'.$t->description.'">'. $t->name .'</a> &times; '.$t->count.'</li>';
            }
            $o .= '</ul>';
            echo $o;
        }else{
            $o = $args['label'];
            $o .= '<'.$args['tag'].' class="'.$args['class'].'">';
            foreach($tags as $t){
                $o .= '<a href="'.esc_url( get_term_link($t)).'" title="'.$t->description.'">'. $t->name .'</a> ';
            }
            $o .= '</'.$args['tag'].'>';
        }

        if($args['echo'])
            echo $o;

        return $o;
    }
}


function ap_tag_details(){
    /* return if tags is disabled */
    if(!ap_opt('enable_tags'))
        return;
        
    $var = get_query_var('question_tag');

    $tag = get_term_by('slug', $var, 'question_tag');
    echo '<div class="clearfix">';
    echo '<h3><a href="'.get_category_link( $tag ).'">'. $tag->name .'</a></h3>';
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
        
    if(!ap_opt('enable_tags'))
        return false;
    
    $tags = wp_get_post_terms( $question_id, 'question_tag');
    
    if(!empty($tags))
        return true;
    
    return false;
}