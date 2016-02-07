<?php
// Die if access directly.
if ( ! defined('ABSPATH' ) ) {
	die();
}

class AP_List_Table_Hooks{

	/**
	 * Initialize the class
	 */
	public function __construct() {
		add_filter( 'views_edit-question', array( $this, 'flag_view' ) );
		add_filter( 'views_edit-answer', array( $this, 'flag_view' ) );
		add_action( 'parse_query', array( $this, 'filter_query_by_flagged' ) );
		add_action( 'manage_answer_posts_custom_column', array( $this, 'answer_row_actions' ), 10, 2 );
		add_filter( 'post_row_actions', array( $this, 'add_question_flag_link' ), 10, 2 );
	}

	/**
	 * Add flagged post view
	 * @param  array $views Views array.
	 * @return array
	 */
	public function flag_view( $views ) {
		global $post_type_object;
		$flagged_count = ap_total_posts_count($post_type_object->name, 'flag' );
		$class = isset( $_GET['flagged'] ) ? 'class="current" ' : '';
	    $views['flagged'] = '<a '.$class.'href="edit.php?flagged=true&#038;post_type='.$post_type_object->name.'">'.__('Flagged', 'anspress-question-answer' ).' <span class="count">('.$flagged_count->total.')</span></a>';

	    return $views;
	}

	public function filter_query_by_flagged($query) {
		global $pagenow;
		$vars = $query->query_vars;

		if ( $pagenow == 'edit.php' && isset( $vars['post_type'] ) && ($vars['post_type'] == 'question' || $vars['post_type'] == 'answer') && isset( $_GET['flagged'] ) ) {
			$query->set( 'meta_query', array( array( 'key' => ANSPRESS_FLAG_META, 'compare' => '>', 'value' => 0 ) ) );
			$query->set( 'orderby', 'meta_value' );
		}
	}

	/**
	 * Add action links below question/answer content in wp post list.
	 * @param  string  $column  Current column name.
	 * @param  integer $post_id Current post id.
	 */
	public function answer_row_actions($column, $post_id) {

		global $post, $mode;

		if ( 'answer_content' != $column ) {
			return;
		}

		$question = get_post( $post->post_parent );
		echo '<a href="'.get_permalink( $post->post_parent ).'" class="row-title">'.$question->post_title.'</a>';

		$content = get_the_excerpt();

		// Get the first 80 words from the content and added to the $abstract variable.
		preg_match( '/^([^.!?\s]*[\.!?\s]+){0,40}/', strip_tags( $content ), $abstract );

		// Pregmatch will return an array and the first 80 chars will be in the first element
		echo $abstract[0] . '...';

		// First set up some variables
		$actions          = array();
		$post_type_object = get_post_type_object( $post->post_type );
		$can_edit_post    = current_user_can( $post_type_object->cap->edit_post, $post->ID );

		// Actions to delete/trash.
		if ( current_user_can( $post_type_object->cap->delete_post, $post->ID ) ) {
			if ( 'trash' == $post->post_status ) {
				$_wpnonce           = wp_create_nonce( 'untrash-post_' . $post_id );
				$url                = admin_url( 'post.php?post=' . $post_id . '&action=untrash&_wpnonce=' . $_wpnonce );
				$actions['untrash'] = "<a title='" . esc_attr( __( 'Restore this item from the Trash', 'anspress-question-answer' ) ) . "' href='" . $url . "'>" . __( 'Restore', 'anspress-question-answer' ) . '</a>';

			} elseif ( EMPTY_TRASH_DAYS ) {
				$actions['trash'] = "<a class='submitdelete' title='" . esc_attr( __( 'Move this item to the Trash', 'anspress-question-answer' ) ) . "' href='" . get_delete_post_link( $post->ID ) . "'>" . __( 'Trash', 'anspress-question-answer' ) . '</a>';
			}

			if ( 'trash' == $post->post_status || ! EMPTY_TRASH_DAYS ) {
				$actions['delete'] = "<a class='submitdelete' title='" . esc_attr( __( 'Delete this item permanently', 'anspress-question-answer' ) ) . "' href='" . get_delete_post_link( $post->ID, '', true ) . "'>" . __( 'Delete Permanently', 'anspress-question-answer' ) . '</a>';
			}
		}

		if ( $can_edit_post ) {
			$actions['edit'] = '<a href="' . get_edit_post_link( $post->ID, '', true ) . '" title="' . esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;', 'anspress-question-answer' ),$post->title ) ) . '" rel="permalink">' . __( 'Edit', 'anspress-question-answer' ) . '</a>';
		}

		// Actions to view/preview.
		if ( in_array($post->post_status, array( 'pending', 'draft', 'future' ) ) && $can_edit_post ) {

			$actions['view'] = '<a href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) . '" title="' . esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;', 'anspress-question-answer' ),$post->title ) ) . '" rel="permalink">' . __( 'Preview', 'anspress-question-answer' ) . '</a>';

		} elseif ( 'trash' != $post->post_status ) {
			$actions['view'] = '<a href="' . get_permalink( $post->ID ) . '" title="' . esc_attr( __( 'View &#8220;%s&#8221; question', 'anspress-question-answer' ) ) . '" rel="permalink">' . __( 'View', 'anspress-question-answer' ) . '</a>';
		}

		if ( ap_flagged_post_meta( $post->ID ) ) {
			$actions['flag'] = '<a href="#" data-query="ap_clear_flag::'. wp_create_nonce( 'clear_flag_'.$post->ID ) .'::'.$post->ID.'" class="ap-ajax-btn flag-clear" data-cb="afterFlagClear">'.__('Clear flag', 'anspress-question-answer' ).'</a>';
		}

		// Echo the 'actions' HTML, let WP_List_Table do the hard work.
		$WP_List_Table = new WP_List_Table();
		echo $WP_List_Table->row_actions( $actions );
	}

	/**
	 * Add clear flag action button in question list.
	 * @param array  $actions Actions array.
	 * @param object $post    Post object.
	 */
	public function add_question_flag_link($actions, $post) {

		if ( ap_flagged_post_meta( $post->ID ) ) {
			$actions['flag'] = '<a href="#" data-query="ap_clear_flag::'. wp_create_nonce( 'clear_flag_'.$post->ID ) .'::'.$post->ID.'" class="ap-ajax-btn flag-clear" data-cb="afterFlagClear">'.__('Clear flag', 'anspress-question-answer' ).'</a>';
		}

		return $actions;
	}
}
