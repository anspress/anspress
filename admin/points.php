<?php

 
// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
 
/**
 * Create a new table class that will extend the WP_List_Table
 */
class AP_Points_Table extends WP_List_Table
{
    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
 
        $data = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );
 
        $perPage = 20;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);
 
        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );
 
        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);
 
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }
 
    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        $columns = array(
            'title'       	=> 'Title',
            'points'        => 'Points',
            'event'    		=> 'Event'
        );
 
        return $columns;
    }
 
    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }
 
    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        return array(
			'title' => array('title', false),
			'points' => array('points', false),
		);
    }
 
    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data()
    {
		$data = ap_point_option();
		
        return $data;
    }
 
    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'title':
            case 'event':
                return $item[ $column_name ];
 
            default:
                return print_r( $item, true ) ;
        }
    }
 
    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'id';
        $order = 'asc';
 
        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }
 
        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }
 
 
        $result = strnatcmp( $a[$orderby], $b[$orderby] );
 
        if($order === 'asc')
        {
            return $result;
        }
 
        return -$result;
    }
	
	public function column_title($point){
		$nonce = wp_create_nonce( 'delete_point' );
		return '<a class="row-title" href="#">'.$point['title'].'</a><div>'.$point['description'].'<div>
		<div class="row-actions">
			<span class="edit"><a title="'.__('Delete this point', 'ap').'" href="'.$point['id'].'" data-action="ap-edit-point">'.__('Edit', 'ap').'</a> | </span>
			<span class="edit"><a title="'.__('Delete this point', 'ap').'" href="'.$point['id'].'" data-button="ap-delete-point" data-args="'.$point['id'].'-'.$nonce.'">'.__('Delete', 'ap').'</a></span>
		</div>';
	}
	
	public function column_points($point){
		if($point['points'] < 0)
			return '<span class="point negative">'.$point['points'].'</span>';
		return '<span class="point">'.$point['points'].'</span>';
	}
}
?>