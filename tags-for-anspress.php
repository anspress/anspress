<?php
/**
 * Tags extension for AnsPress
 *
 * AnsPress - Question and answer plugin for WordPress
 *
 * @package   Tags for AnsPress
 * @author    Rahul Aryan <wp3@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://anspress.io/tags-for-anspress
 * @copyright 2014 anspress.io & Rahul Aryan
 *
 * @wordpress-plugin
 * Plugin Name:       Tags for AnsPress
 * Plugin URI:        http://anspress.io/tags-for-anspress
 * Description:       Extension for AnsPress. Add tags in AnsPress.
 * Donate link: https://www.paypal.com/cgi-bin/webscr?business=rah12@live.com&cmd=_xclick&item_name=Donation%20to%20AnsPress%20development
 * Version:           1.4
 * Author:            Rahul Aryan
 * Author URI:        http://anspress.io
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



        ap_register_page('tag', __('Tag', 'ap'), array($this, 'tag_page'), false);
        ap_register_page('tags', __('Tags', 'ap'), array($this, 'tags_page'));

        // internationalization
        add_action( 'admin_init', array( $this, 'load_options' ) );
        add_action( 'init', array( $this, 'textdomain' ) );

        //Register question tag
        add_action('init', array($this, 'register_question_tag'), 1);
        add_filter('ap_default_options', array($this, 'ap_default_options') );
        add_action('ap_admin_menu', array($this, 'admin_tags_menu'));
        add_action('ap_display_question_metas', array($this, 'ap_display_question_metas' ), 10, 2);
        add_action('ap_question_info', array($this, 'ap_question_info' ));
        add_action( 'ap_enqueue', array( $this, 'ap_enqueue' ) );
        add_filter('term_link', array($this, 'term_link_filter'), 10, 3);
        add_action('ap_ask_form_fields', array($this, 'ask_from_tag_field'), 10, 2);
        add_action('ap_ask_fields_validation', array($this, 'ap_ask_fields_validation'));
        add_action( 'ap_processed_new_question', array($this, 'after_new_question'), 0, 2 );
        add_action( 'ap_processed_update_question', array($this, 'after_new_question'), 0, 2 );
        add_filter('ap_page_title', array($this, 'page_title'));
        add_filter('ap_breadcrumbs', array($this, 'ap_breadcrumbs'));
        add_action('ap_list_head', array($this, 'ap_list_head'));
        add_filter( 'terms_clauses', array($this, 'terms_clauses'), 10, 3);
        add_filter( 'get_terms', array($this, 'get_terms'), 10, 3);
        add_action('ap_user_subscription_tab', array($this, 'subscription_tab'));
        add_action('ap_user_subscription_page', array($this, 'subscription_page'));
    }

    public function tag_page()
    {
        global $questions, $question_tag;

        $tag_id = sanitize_text_field(get_query_var( 'q_tag'));

        $question_args= array('tax_query' => array(
            array(
                'taxonomy' => 'question_tag',
                'field' => is_numeric($tag_id) ? 'id' : 'slug',
                'terms' => array( $tag_id )
            )
        ));

        $question_tag       = get_term_by( is_numeric($tag_id) ? 'id' : 'slug', $tag_id, 'question_tag');

        $questions = ap_get_questions($question_args);
        include(ap_get_theme_location('tag.php', TAGS_FOR_ANSPRESS_DIR));
    }

    public function tags_page()
    {
        global $question_tags, $ap_max_num_pages, $ap_per_page, $tags_rows_found;

        $paged              = get_query_var('paged') ? get_query_var('paged') : 1;
        $per_page           = ap_opt('tags_per_page');
        $total_terms        = $tags_rows_found;
        $offset             = $per_page * ( $paged - 1) ;
        $ap_max_num_pages   = ceil($total_terms / $per_page) ;

        $tag_args = array(
            'ap_tags_query' => 'num_rows',
            'parent'        => 0,
            'number'        => $per_page,
            'offset'        => $offset,
            'hide_empty'    => false,
            'order'         => 'DESC',
        );

        if(@$_GET['ap_sort'] == 'new'){
            $tag_args['orderby'] = 'id';
            $tag_args['order']      = 'ASC';
        }
        elseif(@$_GET['ap_sort'] == 'name'){
            $tag_args['orderby']    = 'name';
            $tag_args['order']      = 'ASC';
        }
        else{
            $tag_args['orderby'] = 'count';
        }

        if(isset($_GET['ap_s']))
            $tag_args['search'] = sanitize_text_field( $_GET['ap_s'] );

        /**
         * FILTER: ap_tags_shortcode_args
         * Filter applied before getting categories.
         * @var array
         */
        $tag_args = apply_filters('ap_tags_shortcode_args', $tag_args );

        $question_tags = get_terms( 'question_tag' , $tag_args);

        include ap_get_theme_location('tags.php', TAGS_FOR_ANSPRESS_DIR);
    }

    public function load_options()
    {
        $this->option_fields();
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
        load_plugin_textdomain( 'ap', false, $lang_dir );

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
        $defaults['tags_per_page']   = 20;

        return $defaults;
    }

    /**
     * Add tags menu in wp-admin
     * @return void
     * @since 2.0
     */
    public function admin_tags_menu(){
        add_submenu_page('anspress', __('Questions Tags', 'ap'), __('Tags', 'ap'), 'manage_options', 'edit-tags.php?taxonomy=question_tag');
    }
    /**
     * Register option fields
     * @return void
     * @since 1.2.1
     */
    public function option_fields()
    {
        if(!is_admin())
            return;

        $settings = ap_opt();
        ap_register_option_group( 'tags', __('Tags', 'ap'), array(
            array(
                'name'              => 'anspress_opt[tags_per_page]',
                'label'             => __('Tags to show', 'ap'),
                'description'       => __('Numbers of tags to show in tags page.', 'ap'),
                'type'              => 'number',
                'value'             => $settings['tags_per_page'],
            ),
            array(
                'name'              => 'anspress_opt[max_tags]',
                'label'             => __('Maximum tags', 'ap'),
                'description'       => __('Maximum numbers of tags that user can add when asking.', 'ap'),
                'type'              => 'number',
                'value'             => $settings['max_tags'],
            ),
            array(
                'name'              => 'anspress_opt[min_tags]',
                'label'             => __('Minimum tags', 'ap'),
                'description'       => __('minimum numbers of tags that user must add when asking.', 'ap'),
                'type'              => 'number',
                'value'             => $settings['min_tags'],
            ),
        ));
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
            $metas['tags'] = ap_question_tags_html(array('label' => ap_icon('tag', true), 'show' => 1));

        return $metas;
    }

    /**
     * Hook tags after post
     * @param   object $post
     * @return  string
     * @since   1.0
     */
    public function ap_question_info($post)
    {
        if(ap_question_have_tags()){
            echo '<div class="widget"><span class="ap-widget-title">'.__('Tags').'</span>';
            echo '<div class="ap-post-tags clearfix">'. ap_question_tags_html(array('list' => true, 'label' => '')) .'</div></div>';
        }
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
            if(get_option('permalink_structure') != '')
                 return ap_get_link_to(array('ap_page' => 'tag', 'q_tag' => $term->slug));
            else
                return ap_get_link_to(array('ap_page' => 'tag', 'q_tag' => $term->term_id));
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

        if(empty($validate))
            return;

        $fields = $validate->get_sanitized_fields();
        if(isset($fields['tag'])){
            $tags = explode(',', $fields['tag']);
            wp_set_object_terms( $post_id, $tags, 'question_tag' );
        }
    }

    /**
     * Tags page title
     * @param  string $title
     * @return string
     */
    public function page_title($title){
        if(is_question_tag()){
            $tag_id = sanitize_text_field( get_query_var( 'q_tag'));
            $tag = get_term_by(is_numeric($tag_id) ? 'id' : 'slug', $tag_id, 'question_tag');
            $title = $tag->name;
        }

        return $title;
    }

    public function ap_breadcrumbs($navs){
        if( is_question_tag()){
            $tag_id = sanitize_text_field( get_query_var( 'q_tag'));
            $tag = get_term_by(is_numeric($tag_id) ? 'id' : 'slug', $tag_id, 'question_tag');
            $navs['page'] = array( );
            $navs['tag'] = array( 'title' => $tag->name, 'link' => get_term_link( $tag, 'question_tag' ), 'order' => 8 );
        }elseif( is_question_categories()){
            $navs['page'] = array( 'title' => __('Categories', 'ap'), 'link' => ap_get_link_to('categories'), 'order' => 8 );

        }

        return $navs;
    }

    public function ap_list_head()
    {
        global $wp;

        if(!isset($wp->query_vars['ap_sc_atts_tags']))
            ap_tag_sorting();
    }

    public function terms_clauses($query, $taxonomies, $args){
        if(isset($args['ap_tags_query']) && $args['ap_tags_query'] == 'num_rows'){
            $query['fields'] = "SQL_CALC_FOUND_ROWS ". $query['fields'];
        }

        if(in_array('question_tag', $taxonomies) && isset($args['ap_query']) && $args['ap_query'] == 'tags_subscription'){
            global $wpdb;

            $query['join']     = $query['join']." INNER JOIN ".$wpdb->prefix."ap_meta apmeta ON t.term_id = apmeta.apmeta_actionid";
            $query['where']    = $query['where']." AND apmeta.apmeta_type='subscriber' AND apmeta.apmeta_param='tag' AND apmeta.apmeta_userid='".$args['user_id']."'";
        }

        return $query;
    }

    public function get_terms($terms, $taxonomies, $args){
        if(isset($args['ap_tags_query']) && $args['ap_tags_query'] == 'num_rows'){
            global $tags_rows_found,  $wpdb;

            $tags_rows_found = $wpdb->get_var( apply_filters( 'ap_get_terms_found_rows', "SELECT FOUND_ROWS()", $terms, $taxonomies, $args ) );
            //wp_cache_set( $this->cache_key.'_count', $this->total_count, 'ap' );
        }
        return $terms;
    }

    public function subscription_tab($active)
    {
        echo '<li class="'.($active == 'tag' ? 'active' : '').'"><a href="?tab=tag">'.__('Tag', 'ap').'</a></li>';
    }

    public function subscription_page($active)
    {
        $active = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'question';

        if($active != 'tag')
            return;

        global $question_tags, $ap_max_num_pages, $ap_per_page, $tags_rows_found;

        $paged              = get_query_var('paged') ? get_query_var('paged') : 1;
        $per_page           = ap_opt('tags_per_page');
        $total_terms        = $tags_rows_found;
        $offset             = $per_page * ( $paged - 1) ;
        $ap_max_num_pages   = ceil($total_terms / $per_page) ;

        $tag_args = array(
            'ap_tags_query' => 'num_rows',
            'ap_query'      => 'tags_subscription',
            'parent'        => 0,
            'number'        => $per_page,
            'offset'        => $offset,
            'hide_empty'    => false,
            'order'         => 'DESC',
            'user_id'       => get_current_user_id(),
        );

        if(@$_GET['ap_sort'] == 'new'){
            $tag_args['orderby'] = 'id';
            $tag_args['order']      = 'ASC';
        }
        elseif(@$_GET['ap_sort'] == 'name'){
            $tag_args['orderby']    = 'name';
            $tag_args['order']      = 'ASC';
        }
        else{
            $tag_args['orderby'] = 'count';
        }

        if(isset($_GET['ap_s']))
            $tag_args['search'] = sanitize_text_field( $_GET['ap_s'] );

        $question_tags = get_terms( 'question_tag' , $tag_args);

        include ap_get_theme_location('tags.php', TAGS_FOR_ANSPRESS_DIR);
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

    $defaults  = array(
        'question_id'   => get_the_ID(),
        'list'           => false,
        'tag'           => 'span',
        'class'         => 'question-tags',
        'label'         => __('Tagged', 'ap'),
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
                /*if($args['show'] > 0 && $i == $args['show']){
                    $o_n = '';
                    foreach($tags as $tag_n)
                        $o_n .= '<a href="'.esc_url( get_term_link($tag_n)).'" title="'.$tag_n->description.'">'. $tag_n->name .'</a> ';

                    $o .= '<a class="ap-tip" data-tipclass="tags-list" title="'.esc_html($o_n).'" href="#">'. sprintf(__('%d More', 'ap'), count($tags)) .'</a>';
                    break;
                }*/
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

if(!function_exists('is_question_tag')){
    function is_question_tag(){
        if('tag' == get_query_var( 'ap_page' ))
            return true;

        return false;
    }
}

function ap_tag_sorting(){
    $args = array(
        'hierarchical'      => true,
        'hide_if_empty'     => true,
        'number'            => 10,
    );

    $terms = get_terms('question_tag', $args);

    $selected = isset($_GET['ap_tag_sort']) ? sanitize_text_field( $_GET['ap_tag_sort'] ) : '';

    if($terms){
        echo '<div class="ap-dropdown">';
            echo '<a id="ap-sort-anchor" class="ap-dropdown-toggle'.($selected != '' ? ' active' : '').'" href="#">'.__('Tags', 'ap').'</a>';
            echo '<div class="ap-dropdown-menu">';
            foreach ($terms as $t) {
                echo '<li '.($selected == $t->term_id ? 'class="active" ' : '').'><a href="#" data-value="'.$t->term_id.'">'. $t->name .'</a></li>';
            }
            echo '<input name="ap_tag_sort" type="hidden" value="'.$selected.'" />';
            echo '</div>';
        echo '</div>';
    }
}

function ap_tags_tab(){
    $active = isset($_GET['ap_sort']) ? $_GET['ap_sort'] : 'popular';

    $link = ap_get_link_to('tags').'?ap_sort=';

    ?>
    <ul class="ap-questions-tab ap-ul-inline clearfix" role="tablist">
        <li class="<?php echo $active == 'popular' ? ' active' : ''; ?>"><a href="<?php echo $link.'popular'; ?>"><?php _e('Popular', 'ap'); ?></a></li>
        <li class="<?php echo $active == 'new' ? ' active' : ''; ?>"><a href="<?php echo $link.'new'; ?>"><?php _e('New', 'ap'); ?></a></li>
        <li class="<?php echo $active == 'name' ? ' active' : ''; ?>"><a href="<?php echo $link.'name'; ?>"><?php _e('Name', 'ap'); ?></a></li>
        <?php
            /**
             * ACTION: ap_tags_tab
             * Used to hook into tags page tab
             */
            do_action('ap_tags_tab', $active);
        ?>
    </ul>
    <?php
}