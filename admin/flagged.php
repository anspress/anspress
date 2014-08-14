<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}


class AP_Flagged_Table extends WP_List_Table {


	public $per_page = 20;

	public $total_count;
	public $published_count;
	public $pending_count;
	public $trash_count;
	public $draft_count;
	public $moderate_count;
	
	public $current_status;



	public function __construct() {

		global $status, $page;

		// Set parent defaults
		parent::__construct( array(
			'singular'  => 'ap_moderate_list',    // Singular name of the listed records
			'plural'    => 'ap_moderate_lists',    	// Plural name of the listed records
			'ajax'      => false             			// Does this table support ajax?
		) );
		
		$this->process_bulk_action();
		$this->get_posts_counts();		
		$this->current_status =  isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'publish' ;
	}
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && !$this->has_items() )
			return;

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) )
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		if ( ! empty( $_REQUEST['order'] ) )
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
?>
		<p class="search-box">
			<?php do_action( 'ap_moderate_search' ); ?>
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button( $text, 'button', false, false, array('ID' => 'search-submit') ); ?><br/>
		</p>
<?php
	}
	
	public function advanced_filters() {
		?>
				<div id="ap-moderate-filters">	
					<?php $this->search_box( __( 'Search', 'ap' ), 'ap-moderates' ); ?>
				</div>

		<?php
	}


	/**
	 * Retrieve the view types
	 *
	 * @access public
	 * @since 1.4
	 * @return array $views All the views available
	 */
	public function get_views() {

		$current        = isset( $_GET['status'] ) ? $_GET['status'] : 'publish';
		$total_count    = '&nbsp;<span class="count">(' . $this->total_count    . ')</span>';
		$published_count = '&nbsp;<span class="count">(' . $this->published_count . ')</span>';
		$pending_count  = '&nbsp;<span class="count">(' . $this->pending_count  . ')</span>';
		$draft_count = '&nbsp;<span class="count">(' . $this->draft_count . ')</span>';
		$trash_count   = '&nbsp;<span class="count">(' . $this->trash_count   . ')</span>';
		$moderate_count   = '&nbsp;<span class="count">(' . $this->moderate_count   . ')</span>';
		

		$views = array(
			'publish'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'status' => 'publish', 'paged' => FALSE ) ), $current === 'publish' ? ' class="current"' : '', __('Publish', 'ap') . $published_count ),
			'pending'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'status' => 'pending', 'paged' => FALSE ) ), $current === 'pending' ? ' class="current"' : '', __('Pending', 'ap') . $pending_count ),
			'moderate'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'status' => 'moderate', 'paged' => FALSE ) ), $current === 'moderate' ? ' class="current"' : '', __('Moderate', 'ap') . $moderate_count ),
			'draft'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'status' => 'draft', 'paged' => FALSE ) ), $current === 'draft' ? ' class="current"' : '', __('Draft', 'ap') . $draft_count ),
			'trash'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'status' => 'trash', 'paged' => FALSE ) ), $current === 'trash' ? ' class="current"' : '', __('Trash', 'ap') . $trash_count )
		);

		return apply_filters( 'ap_moderate_table_views', $views );
	}

	/**
	 * Retrieve the table columns
	 */
	public function get_columns() {
		$columns = array(
			'cb'        		=> '<input type="checkbox" />', //Render a checkbox instead of text		
			'post_type'  		=> __( 'Type', 'ap' ),
			'post_title'  		=> __( 'Title', 'ap' ),
			'flag'  			=> __( 'Flag', 'ap' ),
			'category'  		=> __( 'Category', 'ap' )
		);

		return apply_filters( 'ap_moderate_table_columns', $columns );
	}

	/**
	 * Retrieve the table's sortable columns
	 */
	public function get_sortable_columns() {
		$columns = array(
			'post_title' 	=> array( 'post_title', false ),
		);
		return apply_filters( 'ap_moderate_table_sortable_columns', $columns );
	}

	/**
	 * This function renders most of the columns in the list table.
	 */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'post_title':
                return $item->$column_name;
				
            default:
                return print_r( $item, true ) ;
        }
    }


	/**
	 * Render the checkbox column
	 */
	public function column_cb( $post ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			'post',
			$post->ID
		);
	}
	
	public function column_post_type( $post ) {
		if($post->post_type == 'question')
			return __('Question', 'ap');
		
		return __('Answer', 'ap');
	}
	
	public function column_post_title( $post ) {
		if($post->post_type =='question')
			$title ='<a href="'.esc_url(add_query_arg(array('post' => $post->ID, 'action' => 'edit'), 'post.php')).'" class="row-title">'.$post->post_title.'</a>';
		else
			$title =  '<a href="'.esc_url(add_query_arg(array('post' => $post->ID, 'action' => 'edit'
			), 'post.php')).'" class="row-title">'. ap_truncate_chars(strip_tags($post->post_content), 80).'</a>';
		
		if('trash' == $this->current_status){
			$actions = array(
				'untrash' => sprintf('<a href="%s">%s</a>', ap_untrash_post($post->ID), __('Restore', 'ap')),
				'delete' => sprintf('<a href="%s">%s</a>', get_delete_post_link($post->ID, null,  true), __('Delete permanently', 'ap')),
			);
		}else{		
			$actions = array(
				'edit'      => sprintf('<a href="%s">%s</a>', get_edit_post_link($post->ID), __('Edit', 'ap')),
				'trash'      => sprintf('<a href="%s">%s</a>', get_delete_post_link($post->ID), __('Trash', 'ap')),
				'view'      => sprintf('<a href="%s">%s</a>', get_permalink($post->ID), __('View', 'ap'))
			);
		}


		return sprintf('%1$s %2$s', $title, $this->row_actions($actions) );  
		
	}
	
	public function column_flag( $post ) {
		$flag_count = get_post_meta($post->ID, ANSPRESS_FLAG_META, true);
		return '<span class="flag-count' . ($flag_count ? ' flagged' : '') . '">' . $flag_count . '</span>';
	}
	
	public function column_category( $post ) {
		/* Get the genres for the post. */
		$category = get_the_terms($post->ID, ANSPRESS_CAT_TAX);
		
		/* If terms were found. */
		if (!empty($category)) {
			$out = array();
			
			/* Loop through each term, linking to the 'edit posts' page for the specific term. */
			foreach ($category as $cat) {
				$out[] = edit_term_link($cat->name, '', '', $cat, false);
			}
			/* Join the terms, separating them with a comma. */
			return join(', ', $out);
		}
		
		/* If no terms were found, output a default message. */
		else {
			return __('--');
		}
	}


	/**
	 * Retrieve the bulk actions
	 */
	public function get_bulk_actions() {
		$status =  isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'publish' ;
		
		if('trash' == $status){
			$actions = array(
				'restore' => __( 'Restore', 'ap' ), 
				'delete' => __( 'Delete permanently', 'ap' ),
			);
		}else{
			$actions = array(
				'publish'   => __( 'Published', 'ap' ),
				'pending'   => __( 'Pending', 'ap' ),
				'trash'   	=> __( 'Move to trash', 'ap' ),
				
			);
		}

		return apply_filters( 'ap_moderate_table_bulk_actions', $actions );
	}

	/**
	 * Process the bulk actions
	 */
	public function process_bulk_action() {
		$ids    = isset( $_GET['post'] ) ? $_GET['post'] : false;
		$action = $this->current_action();

		if ( ! is_array( $ids ) )
			$ids = array( $ids );


		if( empty( $action ) )
			return;

		foreach ( $ids as $id ) {
			// Detect when a bulk action is being triggered...
			if ( 'trash' === $this->current_action() ) {
				wp_trash_post( $id );
			}

			if ( 'publish' === $this->current_action() ) {
				wp_publish_post( $id );
			}

			if ( 'pending' === $this->current_action() ) {
				// Update post
				$u_post = array();
				$u_post['ID'] = $id;
				$u_post['post_status'] = 'pending';

				// Update the post into the database
				wp_update_post( $u_post );				
			}

			if ( 'delete' === $this->current_action() ) {
				wp_delete_post( $id, true );
			}	
			if ( 'restore' === $this->current_action() ) {
				wp_untrash_post( $id);
			}			

			do_action( 'ap_moderate_table_do_bulk_action', $id, $this->current_action() );
		}

	}

	/**
	 * Retrieve the posts counts
	 */
	public function get_posts_counts() {
		global $wp_query;
		$counts        			= ap_flagged_posts_count();
		$this->published_count 	= $counts->publish;
		$this->pending_count  	= $counts->pending;
		$this->trash_count   	= $counts->trash;
		$this->draft_count  	= $counts->draft;
		$this->moderate_count  	= $counts->moderate;

		foreach( $counts as $count ) {
			$this->total_count += $count;
		}
	}

	public function posts_data() {
		global $wpdb;

		$status =  isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'publish' ;
		$paged = isset( $_GET['paged'] ) ? sanitize_text_field($_GET['paged']) : 1;
		
		// Preparing your query
        $query = "SELECT p.*, v.apmeta_userid as vote_user, v.apmeta_value as vote_value, v.apmeta_param as vote_note, v.apmeta_date FROM $wpdb->posts p INNER JOIN ".$wpdb->prefix."ap_meta v ON v.apmeta_actionid = p.ID AND v.apmeta_type='flag' WHERE (p.post_type = 'answer' OR p.post_type = 'question') AND p.post_status = '$status' ";
		
		//adjust the query to take pagination
		if(!empty($paged) && !empty($this->per_page)){
			$offset=($paged-1)*$this->per_page;
			$query.=' LIMIT '.(int)$offset.','.$this->per_page;
		}		
		
		return $wpdb->get_results($query);

	}


	/**
	 * Setup the final data for the table
	 * @return void
	 */
	public function prepare_items() {

		wp_reset_vars( array( 'action', 'post', 'orderby', 'order', 's' ) );

		$columns  = $this->get_columns();
		$hidden   = array(); // No hidden columns
		$sortable = $this->get_sortable_columns();
		$data     = $this->posts_data();
		$status   = isset( $_GET['status'] ) ? $_GET['status'] : 'publish';

		$this->_column_headers = array( $columns, $hidden, $sortable );

		switch ( $status ) {
			case 'publish':
				$total_items = $this->published_count;
				break;
			case 'pending':
				$total_items = $this->pending_count;
				break;
			case 'draft':
				$total_items = $this->draft_count;
				break;
			case 'trash':
				$total_items = $this->trash_count;
				break;
		}

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page, 
				'total_pages' => ceil( $total_items / $this->per_page ) 
			)
		);
	}
}
