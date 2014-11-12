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

class AP_labels
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
		global $current_screen;
		
		//Register Custom Post types and taxonomy
        add_action('init', array($this, 'create_cpt_tax'), 1);
		
		add_action ( 'question_label_edit_form_fields', array($this, 'question_label_edit_fields'));
		add_action( 'question_label_add_form_fields', array($this, 'question_label_fields') );
		
		// save extra category extra fields hook
		add_action ( 'edited_question_label', array($this, 'save_question_label'));
		add_action( 'created_question_label', array($this, 'save_question_label') );
		
		add_action('admin_enqueue_scripts', array($this, 'colorpicker'));
		
		add_filter('manage_edit-question_label_columns', array($this, 'add_question_label_columns'));
		add_filter('manage_question_label_custom_column', array($this, 'add_question_label_column_content'), 10, 3);
		
		add_filter('wp_ajax_ap_save_labels', array($this, 'ap_save_labels'));
		
		add_filter('ap_after_inserting_question', array($this, 'default_label'));
		
    }

	
	public function colorpicker(){
		$screen = get_current_screen();
		if($screen->taxonomy == 'question_label'){
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker');
			wp_enqueue_script( 'ap-colorpicker-handle', ANSPRESS_URL.'assets/colorpicker.js', array( 'wp-color-picker' ), false, true );
		}
	}
	
    // Register Custom Post Type    
    public function create_cpt_tax()
    {
		$question_labels = array(
            'name' => __('Question Label', 'ap'),
            'singular_name' => _x('Label', 'ap'),
            'all_items' => __('All Label', 'ap'),
            'add_new_item' => _x('Add New Label', 'ap'),
            'edit_item' => __('Edit Label', 'ap'),
            'new_item' => __('New Label', 'ap'),
            'view_item' => __('View Label', 'ap'),
            'search_items' => __('Search Label', 'ap'),
            'not_found' => __('Nothing Found', 'ap'),
            'not_found_in_trash' => __('Nothing found in Trash', 'ap'),
            'parent_item_colon' => ''
        );

		
		register_taxonomy('question_label', array('question'), array(
            'hierarchical' => true,
            'labels' => $question_labels,
            'rewrite' => false
        ));
        
    }
		
	public function question_label_edit_fields( $tax ) {
		$t_id = $tax->term_id;
		$tax_meta = get_option( "question_label_$t_id");
	?>
			
		<tr class="form-field ap-label-color-field">
			<th>
				<label for="question_label_field"><?php _e('Label color', 'ap'); ?></label>
			</th>
			<td>
				<input type="text" name="question_label[color]" id="question_label_field" value="<?php echo $tax_meta['color'] ? $tax_meta['color'] : ''; ?>"><br />
				<br />
			</td>
		</tr>
			
		
	<?php
	}

	public function question_label_fields( $tax ) {
		$t_id = $tax->term_id;
		$tax_meta = get_option( "question_label_$t_id");

	?>
			
		<tr class="form-field ap-label-color-field">
			<th>
				<label for="question_label_field"><?php _e('Label color', 'ap'); ?></label>
			</th>
			<td>
				<input type="text" name="question_label[color]" id="question_label_field" value="<?php echo $tax_meta['color'] ? $tax_meta['color'] : ''; ?>"><br />
				<br />
			</td>
		</tr>			
		
	<?php
	}

	// save extra category extra fields callback function
	public function save_question_label( $term_id ) {
		if ( isset( $_POST['question_label'] ) ) {
			$t_id = $term_id;
			$tax_meta = get_option( "question_label_$t_id");
			$tax_keys = array_keys($_POST['question_label']);
				foreach ($tax_keys as $key){
				if (isset($_POST['question_label'][$key])){
					$tax_meta[$key] = $_POST['question_label'][$key];
				}
			}
			//save the option array
			update_option( "question_label_$t_id", $tax_meta );
		}
	}

	function add_question_label_columns($columns){
		$columns['color'] = __('Color', 'ap');
		return $columns;
	}
	
	 
	function add_question_label_column_content($content, $column_name, $label_id){
		$color = ap_get_label_color($label_id);
		$content .= '<span style="background:'.$color.'; height:20px; width:50px; display:block;"> </span>';
		return $content;
	}
	
	public function ap_save_labels (){
		if(ap_user_can_change_label() && wp_verify_nonce($_POST['nonce'], 'label-'.$_POST['id'])){
			if(isset($_POST['args'])){
				$ids = array();
				foreach($_POST['args'] as $id){					
					$ids[] = (int)$id;					
				}

				$post_id = (int)sanitize_text_field( $_POST['id']);
				$existing = get_the_terms( $post_id, 'question_label' );
				
				wp_set_post_terms($post_id, $ids, 'question_label' );
				update_post_meta($post_id, ANSPRESS_UPDATED_META, current_time( 'mysql' ));

				$taxo = get_the_terms( $post_id, 'question_label' );
				
				$result = array('status' => true, 'message' => __('Question label updated.', 'ap'), 'html' => ap_get_question_label($post_id, true));
			}else{
				$result = array('status' => false, 'message' => __('No label selected', 'ap'));
			}
		}else{
			$result = array('status' => false, 'message' => __('Unable to set labels, please try again', 'ap'));
		}
		die(json_encode($result));
	}
	
	public function default_label($post_id){
		if(ap_opt('default_label'))
			wp_set_post_terms($post_id, ap_opt('default_label'), 'question_label' );
	}

}

/* retrieve the color of label */
function ap_get_label_color($label_id){
	$tax_meta = get_option( "question_label_$label_id");
	return $tax_meta['color'];
}

function ap_get_question_label($post_id = NULL, $bg = false){	
	if(!$post_id) $post_id = get_the_ID();
	$terms = get_the_terms( $post_id, 'question_label' );

	if($terms){
		$o = '<ul class="question-labels">';
		foreach($terms as $t){
			$color = ap_get_label_color($t->term_id);
			$o .= '<li title="'.$t->name. ($t->description ? ' - '.$t->description : '').'"'.($bg ?' style="background:'.$color.';"' : '').' class="ap-label-name ap-tip">';
			if(!$bg)
				$o .= '<span class="question-label-color '.$t->slug.'" style="background:'.$color.';"></span>';
				
			$o .= '<i>'.$t->name.'</i>
				</li>';
		}
		$o .= '</ul>';
		return $o;
	}

}

function ap_label_html($term){
	if(!$term)
		return;
		
	$color = ap_get_label_color($term->term_id);
	return '<span class="ap-label-name '.$term->slug.'" style="background:'.$color.';">'.$term->name.'</span>';
}

function ap_change_label_html($post_id){
	if(ap_user_can_change_label()):
	?>
	<div class="ap-change-label-from ap-dropdown pull-right">
		<a class="ap-icon ap-icon-cog ap-dropdown-toggle" data-action="ap-change-status"></a>
		<div class="ap-dropdown-menu">
			<?php
				$labels = get_terms('question_label', array('orderby'=> 'name', 'hide_empty' => false));
				$taxo = get_the_terms( $post_id, 'question_label' );
				
				$taxo_array = array();
				if($taxo)
				foreach ($taxo as $t)
					$taxo_array[] = $t->slug;
					
				
				if($labels){
					echo '<ul id="ap-label-select" data-action="ap-label-select">';
					echo '<li class="ap-select-header">'.__('Label', 'ap').'</li>';
					foreach($labels as $l){
						$color = ap_get_label_color($l->term_id);
						echo '<li data-args="'.$l->term_id.'" class="ap-select-item '.ap_icon('checked').' '.(in_array($l->slug, $taxo_array) ? ' selected': '').'" data-color="'.$color.'"><span class="question-label-color" style="background:'.$color.';"></span>'.$l->name.'<span class="unselect ap-icon-cross"></span></li>';
					}
					$nonce = wp_create_nonce( 'label-'.get_question_id() );
					echo '<li class="ap-select-footer"><a href="#" data-button="ap-save-label" data-id="'.get_question_id().'" data-nonce="'.$nonce.'" class="ap-btn ap-small">'.__('Done', 'ap').'</a>';
					echo '<a href="#" class="ap-btn ap-small" data-toggle="ap-dropdown">'.__('Close', 'ap').'</a>';
					echo '</li></ul>';
				}
			?>			
		</div>
	</div>
	<?php
	endif;
}

function ap_question_have_labels($post_id = false){
	if(!$post_id)
		$post_id = get_the_ID();
		
	/* if(!ap_opt('enable_categories'))
		return false; */
	
	$labels = wp_get_post_terms( $post_id, 'question_label');
	if(!empty($labels))
		return true;
	
	return false;
}