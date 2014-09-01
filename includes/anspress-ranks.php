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

class AP_Ranks
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
		add_action( 'ap_admin_menu', array($this, 'ranks_admin_page') );
		
		/* Create custom columns for the manage rank page. */
		add_filter( 'manage_edit-rank_columns', array($this, 'edit_rank_column') );
		
		/* Customize the output of the custom column on the manage rank page. */
		add_action( 'manage_rank_custom_column', array($this, 'manage_rank_column'), 10, 3 );
		
		/* Add section to the edit user page in the admin to select rank. */
		add_action( 'show_user_profile', array($this, 'profile_edit_rank_section') );
		add_action( 'edit_user_profile', array($this, 'profile_edit_rank_section') );
		
		/* Update the rank terms when the edit user page is updated. */
		add_action( 'personal_options_update', array($this, 'save_user_rank_terms') );
		add_action( 'edit_user_profile_update', array($this, 'save_user_rank_terms') );
		
		add_action( 'user_register', array($this, 'default_user_meta'), 10, 1 );
    }
	
	public function user_taxonomy() {
		 register_taxonomy(
			'rank',
			'user',
			array(
				'public' => true,
				'labels' => array(
					'name' => __( 'Ranks', 'ap' ),
					'singular_name' => __( 'Rank', 'ap'),
					'menu_name' => __( 'Ranks', 'ap' ),
					'search_items' => __( 'Search ranks', 'ap' ),
					'popular_items' => __( 'Popular ranks', 'ap' ),
					'all_items' => __( 'All ranks', 'ap' ),
					'edit_item' => __( 'Edit rank', 'ap' ),
					'update_item' => __( 'Update rank', 'ap' ),
					'add_new_item' => __( 'Add new rank', 'ap' ),
					'new_item_name' => __( 'New rank name', 'ap' ),
					'separate_items_with_commas' => __( 'Separate rank with commas', 'ap' ),
					'add_or_remove_items' => __( 'Add or remove ranks', 'ap' ),
					'choose_from_most_used' => __( 'Choose from the most popular ranks', 'ap' ),
				),
				'rewrite' => false,
				'update_count_callback' => array($this, 'rank_count') // Use a custom function to update the count.
			)
		);

	}
	

	public function rank_count( $terms, $taxonomy ) {
		global $wpdb;

		foreach ( (array) $terms as $term ) {

			$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships WHERE term_taxonomy_id = %d", $term ) );

			do_action( 'edit_term_taxonomy', $term, $taxonomy );
			$wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );
			do_action( 'edited_term_taxonomy', $term, $taxonomy );
		}
	}
	
	public function ranks_admin_page() {
		add_submenu_page('anspress', 'User Ranks', 'User Ranks', 'manage_options', 'edit-tags.php?taxonomy=rank');
	}
	
	public function edit_rank_column( $columns ) {

		unset( $columns['posts'] );

		$columns['users'] = __( 'Users' );

		return $columns;
	}
	
	public function manage_rank_column( $display, $column, $term_id ) {

		if ( 'users' === $column ) {
			$term = get_term( $term_id, 'rank' );
			echo $term->count ? $term->count : 0;
		}
	}
	
	public function profile_edit_rank_section( $user ) {

		$tax = get_taxonomy( 'rank' );

		/* Make sure the user can assign terms of the rank taxonomy before proceeding. */
		if ( !current_user_can( $tax->cap->assign_terms ) )
			return;

		/* Get the terms of the 'rank' taxonomy. */
		$terms = get_terms( 'rank', array( 'hide_empty' => false, 'orderby' => 'id' ) ); ?>

		<h3><?php _e( 'Rank', 'ap' ); ?></h3>

		<table class="form-table">

			<tr>
				<th><label for="rank"><?php _e( 'Select rank' ); ?></label></th>

				<td><?php

				/* If there are any rank terms, loop through them and display checkboxes. */
				if ( !empty( $terms ) ) {

					foreach ( $terms as $term ) { ?>
						<input type="radio" name="rank" id="rank-<?php echo esc_attr( $term->slug ); ?>" value="<?php echo esc_attr( $term->slug ); ?>" <?php checked( true, is_object_in_term( $user->ID, 'rank', $term ) ); ?> /> <label for="rank-<?php echo esc_attr( $term->slug ); ?>"><?php echo $term->name; ?></label> <br />
					<?php }
				}

				/* If there are no rank terms, display a message. */
				else {
					_e( 'There are no ranks available.', 'ap' );
				}

				?></td>
			</tr>

		</table>
	<?php }
	
	public function save_user_rank_terms( $user_id ) {

		$tax = get_taxonomy( 'rank' );

		/* Make sure the current user can edit the user and assign terms before proceeding. */
		if ( !current_user_can( 'edit_user', $user_id ) && current_user_can( $tax->cap->assign_terms ) )
			return false;

		$term = esc_attr( $_POST['rank'] );

		/* Sets the terms (we're just using a single term) for the user. */
		wp_set_object_terms( $user_id, array( $term ), 'rank', false);

		clean_object_term_cache( $user_id, 'rank' );
	}
	public function default_user_meta($user_id){
		if(ap_opt('default_rank') !==false)
			wp_set_object_terms( $user_id, array( ap_opt('default_rank') ), 'rank', false);
	}
}

function ap_get_ranks($userid){
	$ranks = wp_get_object_terms( $userid, 'rank');
	if(empty($ranks))
		$ranks = array(get_term_by( 'id', ap_opt('default_rank'), 'rank'));
	
	return $ranks;
}

function ap_get_rank_title($userid){
	$ranks = ap_get_ranks($userid);
	
	$o = '';
	if($ranks)
	foreach ($ranks as $rank){
		$o .= '<span class="ap-user-rank rank-'.$rank->slug.'">'. $rank->name .'</span>';
	}
	
	return $o;
}