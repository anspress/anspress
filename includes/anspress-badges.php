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
		add_action('init', array($this, 'user_taxonomy'), 0);
		add_action( 'ap_admin_menu', array($this, 'badges_admin_page') );
		
		/* Create custom columns for the manage badge page. */
		add_filter( 'manage_edit-badge_columns', array($this, 'edit_badge_column') );
		
		/* Customize the output of the custom column on the manage badge page. */
		add_action( 'manage_badge_custom_column', array($this, 'manage_badge_column'), 10, 3 );
		
		add_action ( 'badge_edit_form_fields', array($this, 'badge_fields'));
		add_action( 'badge_add_form_fields', array($this, 'badge_fields') );
		
		// save extra badge fields
		add_action ( 'edited_badge', array($this, 'save_badge_fields'));
		add_action( 'created_badge', array($this, 'save_badge_fields') );

    }
	
	public function user_taxonomy() {
		 register_taxonomy(
			'badge',
			'user',
			array(
				'public' => true,
				'labels' => array(
					'name' => __( 'Badges', 'ap' ),
					'singular_name' => __( 'badge', 'ap'),
					'menu_name' => __( 'badges', 'ap' ),
					'search_items' => __( 'Search badges', 'ap' ),
					'popular_items' => __( 'Popular badges', 'ap' ),
					'all_items' => __( 'All badges', 'ap' ),
					'edit_item' => __( 'Edit badge', 'ap' ),
					'update_item' => __( 'Update badge', 'ap' ),
					'add_new_item' => __( 'Add new badge', 'ap' ),
					'new_item_name' => __( 'New badge name', 'ap' ),
					'separate_items_with_commas' => __( 'Separate badge with commas', 'ap' ),
					'add_or_remove_items' => __( 'Add or remove badges', 'ap' ),
					'choose_from_most_used' => __( 'Choose from the most popular badges', 'ap' ),
				),
				'hierarchical' => false,
				'rewrite' => false,
				/* 'capabilities' => array(
					'manage_terms' => 'edit_users', // Using 'edit_users' cap to keep this simple.
					'edit_terms'   => 'edit_users',
					'delete_terms' => 'edit_users',
					'assign_terms' => 'read',
				), */
				'update_count_callback' => array($this, 'badge_count') // Use a custom function to update the count.
			)
		);
	}
	

	public function badge_count( $terms, $taxonomy ) {
		global $wpdb;

		foreach ( (array) $terms as $term ) {

			$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships WHERE term_taxonomy_id = %d", $term ) );

			do_action( 'edit_term_taxonomy', $term, $taxonomy );
			$wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );
			do_action( 'edited_term_taxonomy', $term, $taxonomy );
		}
	}
	
	public function badges_admin_page() {
		add_submenu_page('anspress', 'User Badges', 'User Badges', 'manage_options', 'edit-tags.php?taxonomy=badge');
	}
	
	public function edit_badge_column( $columns ) {

		unset( $columns['posts'] );
		$columns['type'] = __('Type', 'ap');
		$columns['points'] = __('Min. Points', 'ap');
		$columns['action'] = __('Action', 'ap');
		$columns['users'] = __( 'Users', 'ap' );
		
		return $columns;
	}
	
	public function manage_badge_column( $display, $column, $term_id ) {

		if ( 'users' === $column ) {
			$term = get_term( $term_id, 'badge' );
			echo $term->count ? $term->count : 0;
		}
		if ( 'type' === $column ) {
			echo ap_get_badge_type($term_id);
		}
		
		if ( 'action' === $column ) {
			echo ap_get_badge_action($term_id);
		}
		
		if ( 'points' === $column ) {
			echo ap_get_badge_points($term_id);
		}
	}
		
	public function badge_fields( $tax ) {
		$t_id = @$tax->term_id;
		$tax_meta = @get_option( "badge_$t_id");
	?>
			
		<tr class="form-field ap-badge-type-field">
			<th>
				<label for="badge_type_field"><?php _e('Badge type', 'ap'); ?></label>
			</th>
			<td>
				<select name="badge[type]" id="badge_type_field" value="<?php echo $tax_meta['type'] ? $tax_meta['type'] : ''; ?>">
					<?php 
						$current_type = @ap_get_badge_type($tax->term_id);
						foreach (ap_badge_types() as $k => $type)
							echo '<option value="'.$k.'"'.($k == $current_type ? ' selected="selected"': '').'>' .$type. '</option>';
					?>
				</select>
				<br />
				<br />
			</td>
		</tr>
		<tr class="form-field ap-badge-action-field">
			<th>
				<label for="badge_action_field"><?php _e('Action', 'ap'); ?></label>
			</th>
			<td>
				<input type="text" class="widefat" name="badge[action]" id="badge_action_field" value="<?php echo $tax_meta['action'] ? $tax_meta['action'] : ''; ?>" />
				<br />
			</td>
		</tr>
		<tr class="form-field ap-badge-points-field">
			<th>
				<label for="badge_points_field"><?php _e('Minimum points', 'ap'); ?></label>
			</th>
			<td>
				<input type="text" class="widefat" name="badge[points]" id="badge_points_field" value="<?php echo $tax_meta['points'] ? $tax_meta['points'] : ''; ?>" />
				<br />
			</td>
		</tr>
			
		
	<?php
	}
	
	// save badge extra fields
	public function save_badge_fields( $term_id ) {
		if ( isset( $_POST['badge'] ) ) {
			$t_id = $term_id;
			$tax_meta = get_option( "badge_$t_id");
			$tax_keys = array_keys($_POST['badge']);
				foreach ($tax_keys as $key){
				if (isset($_POST['badge'][$key])){
					$tax_meta[$key] = sanitize_text_field($_POST['badge'][$key]);
				}
			}
			//save the option array
			update_option( "badge_$t_id", $tax_meta );
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