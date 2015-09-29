<?php
/**
 * AnsPress post types
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://rahularyan.com
 * @copyright 2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class AnsPress_PostTypes
{

	/**
	 * Initialize the class
	 */
	public function __construct() {

		// Register Custom Post types and taxonomy
		add_action( 'init', array( $this, 'register_question_cpt' ), 0 );
		add_action( 'init', array( $this, 'register_answer_cpt' ), 0 );
		add_action( 'post_type_link',array( $this, 'post_type_link' ),10,2 );
		add_filter( 'manage_edit-question_columns', array( $this, 'cpt_question_columns' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'custom_columns_value' ) );
		add_filter( 'manage_edit-answer_columns', array( $this, 'cpt_answer_columns' ) );
		add_action( 'manage_answer_posts_custom_column', array( $this, 'answer_row_actions' ), 10, 2 );
		add_filter( 'manage_edit-question_sortable_columns', array( $this, 'admin_column_sort_flag' ) );
		add_filter( 'manage_edit-answer_sortable_columns', array( $this, 'admin_column_sort_flag' ) );
		add_action( 'pre_get_posts', array( $this, 'admin_column_sort_flag_by' ) );
	}

	/**
	 * Register question CPT
	 * @return void
	 * @since 2.0.1
	 */
	public function register_question_cpt() {

		// question CPT labels
		$labels = array(
			'name'              => _x( 'Questions', 'Post Type General Name', 'ap' ),
			'singular_name'     => _x( 'Question', 'Post Type Singular Name', 'ap' ),
			'menu_name'         => __( 'Questions', 'ap' ),
			'parent_item_colon' => __( 'Parent Question:', 'ap' ),
			'all_items'         => __( 'All Questions', 'ap' ),
			'view_item'         => __( 'View Question', 'ap' ),
			'add_new_item'      => __( 'Add New Question', 'ap' ),
			'add_new'           => __( 'New Question', 'ap' ),
			'edit_item'         => __( 'Edit Question', 'ap' ),
			'update_item'       => __( 'Update Question', 'ap' ),
			'search_items'      => __( 'Search question', 'ap' ),
			'not_found'         => __( 'No question found', 'ap' ),
			'not_found_in_trash' => __( 'No questions found in Trash', 'ap' ),
		);

		/**
		 * FILTER: ap_question_cpt_labels
		 * filter is called before registering question CPT
		 */
		$labels = apply_filters( 'ap_question_cpt_labels', $labels );

		// question CPT arguments
		$args   = array(
			'label' => __( 'question', 'ap' ),
			'description' => __( 'Question', 'ap' ),
			'labels' => $labels,
			'supports' => array(
				'title',
				'editor',
				'author',
				'comments',
				'trackbacks',
				'revisions',
				'custom-fields',
				'buddypress-activity',
			),
			'hierarchical' => false,
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => false,
			'show_in_nav_menus' => false,
			'show_in_admin_bar' => true,
			'menu_icon' => ANSPRESS_URL . '/assets/question.png',
			'can_export' => true,
			'has_archive' => true,
			'exclude_from_search' => true,
			'publicly_queryable' => true,
			'capability_type' => 'post',
			'rewrite' => false,
			'query_var' => 'apq',
		);

		/**
		 * FILTER: ap_question_cpt_args
		 * filter is called before registering question CPT
		 */
		$args = apply_filters( 'ap_question_cpt_args', $args );

		// register CPT question
		register_post_type( 'question', $args );
	}

	/**
	 * Register answer custom post type
	 * @return void
	 * @since  2.0
	 */
	public function register_answer_cpt() {
		// Answer CPT labels
		$labels = array(
			'name'          => _x( 'Answers', 'Post Type General Name', 'ap' ),
			'singular_name' => _x( 'Answer', 'Post Type Singular Name', 'ap' ),
			'menu_name' => __( 'Answers', 'ap' ),
			'parent_item_colon' => __( 'Parent Answer:', 'ap' ),
			'all_items' => __( 'All Answers', 'ap' ),
			'view_item' => __( 'View Answer', 'ap' ),
			'add_new_item' => __( 'Add New Answer', 'ap' ),
			'add_new' => __( 'New answer', 'ap' ),
			'edit_item' => __( 'Edit answer', 'ap' ),
			'update_item' => __( 'Update answer', 'ap' ),
			'search_items' => __( 'Search answer', 'ap' ),
			'not_found' => __( 'No answer found', 'ap' ),
			'not_found_in_trash' => __( 'No answer found in Trash', 'ap' ),
		);

		/**
		 * FILTER: ap_answer_cpt_label
		 * filter is called before registering answer CPT
		 */
		$labels = apply_filters( 'ap_answer_cpt_label', $labels );

		// Answers CPT arguments
		$args   = array(
			'label' => __( 'answer', 'ap' ),
			'description' => __( 'Answer', 'ap' ),
			'labels' => $labels,
			'supports' => array(
				'editor',
				'author',
				'comments',
				'revisions',
				'custom-fields',
			),
			'hierarchical' => false,
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => false,
			'show_in_nav_menus' => false,
			'show_in_admin_bar' => false,
			'menu_icon' => ANSPRESS_URL . '/assets/answer.png',
			// 'show_in_menu' => 'anspress',
			'can_export' => true,
			'has_archive' => true,
			'exclude_from_search' => true,
			'publicly_queryable' => true,
			'capability_type' => 'post',
			'rewrite' => false,
		);

		/**
		 * FILTER: ap_answer_cpt_args
		 * filter is called before registering answer CPT
		 */
		$args = apply_filters( 'ap_answer_cpt_args', $args );

		// Register CPT answer.
		register_post_type( 'answer', $args );
	}

	/**
	 * Alter question and answer CPT permalink
	 * @param  string $link
	 * @param  object $post
	 * @return string
	 * @since 2.0.0-alpha2
	 */
	public function post_type_link($link, $post) {
		if ( $post->post_type == 'question' ) {
			$question_slug = ap_opt( 'question_page_slug' );

			if ( empty( $question_slug ) ) {
				$question_slug = 'question'; }

			if ( get_option( 'permalink_structure' ) ) {
				if ( ap_opt( 'question_permalink_follow' ) ) {
					$link = rtrim( ap_base_page_link(), '/' ).'/'.$question_slug.'/'.$post->post_name.'/'; } else {
					$link = home_url( '/'.$question_slug.'/'.$post->post_name.'/' ); }
			} else {
				$link = add_query_arg( array( 'apq' => false, 'question_id' => $post->ID ), ap_base_page_link() );
			}
			/**
			 * FILTER: ap_question_post_type_link
			 * Allow overriding of question post type permalink
			 */
			return apply_filters( 'ap_question_post_type_link', $link, $post );

		} elseif ( $post->post_type == 'answer' && $post->post_parent != 0 ) {
			$link = get_permalink( $post->post_parent ) ."?show_answer=$post->ID#answer_{$post->ID}";

			/**
			* FILTER: ap_answer_post_type_link
			* Allow overriding of answer post type permalink
			*/
			return apply_filters( 'ap_answer_post_type_link', $link, $post );
		}
		return $link;
	}

	/**
	 * Alter columns in question cpt
	 * @param  array $columns
	 * @return array
	 * @since  2.0.0-alpha
	 */
	public function cpt_question_columns($columns) {

		$columns = array();
		$columns['cb']          = '<input type="checkbox" />';
		$columns['ap_author']      = __( 'Author', 'ap' );
		$columns['title']       = __( 'Title', 'ap' );

		if ( taxonomy_exists( 'question_category' ) ) {
			$columns['question_category']       = __( 'Category', 'ap' ); }

		if ( taxonomy_exists( 'question_tag' ) ) {
			$columns['question_tag']       = __( 'Tag', 'ap' ); }

		$columns['status']      = __( 'Status', 'ap' );
		$columns['answers']     = __( 'Ans', 'ap' );
		$columns['comments']    = __( 'Comments', 'ap' );
		$columns['vote']        = __( 'Vote', 'ap' );
		$columns['flag']        = __( 'Flag', 'ap' );
		$columns['date']        = __( 'Date', 'ap' );

		return $columns;
	}

	public function custom_columns_value($column) {

		global $post;

		if ( ! ($post->post_type != 'question' || $post->post_type != 'answer') ) {
			return $column; }

		if ( 'ap_author' == $column ) {
			echo '<a class="ap-author-col" href="'.ap_user_link( $post->post_author ).'" class="">';
			echo get_avatar( get_the_author_meta( 'user_email' ), 28 );
			echo get_the_author_meta( 'display_name' ) .'<span class="user-login">'.get_the_author_meta( 'user_login' ).'</span>';
			echo '</a>';

		} elseif ( 'status' == $column ) {

			echo '<span class="post-status">';

			if ( 'private_post' == $post->post_status ) {
				echo __( 'Private', 'ap' ); } elseif ('closed' == $post->post_status)
					echo __( 'Closed', 'ap' );

				elseif ('moderate' == $post->post_status)
					echo __( 'Moderate', 'ap' );

				elseif ('private' == $post->post_status)
					echo __( 'Private', 'ap' );

				elseif ('darft' == $post->post_status)
					echo __( 'Draft', 'ap' );

				elseif ('pending' == $post->post_status)
					echo __( 'Pending', 'ap' );

				elseif ('trash' == $post->post_status)
					echo __( 'Trash', 'ap' );

			else {
				echo __( 'Open', 'ap' ); }

			echo '</span>';

		} elseif ( 'question_category' == $column && taxonomy_exists( 'question_category' ) ) {

			$category = get_the_terms( $post->ID, 'question_category' );

			if ( ! empty( $category ) ) {
				$out = array();

				foreach ( $category as $cat ) {
					$out[] = edit_term_link( $cat->name, '', '', $cat, false );
				}
				echo join( ', ', $out );
			} else {
				_e( '--' );
			}
		} elseif ( 'question_tag' == $column && taxonomy_exists( 'question_tag' ) ) {

			$terms = get_the_terms( $post->ID, 'question_tag' );

			if ( ! empty( $terms ) ) {
				$out = array();

				foreach ( $terms as $term ) {
					$out[] = sprintf('<a href="%s">%s</a>', esc_url(add_query_arg(array(
						'post_type' => $post->post_type,
						'question_tag' => $term->slug,
					), 'edit.php')), esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, 'question_tag', 'display' ) ));
				}

				echo join( ', ', $out );
			} else {
				_e( '--', 'ap' );
			}
		} elseif ( 'answers' == $column ) {
			$a_count = ap_count_answer_meta();

			/* If terms were found. */
			if ( ! empty( $a_count ) ) {

				echo '<a class="ans-count" title="' . $a_count . __( 'answers', 'ap' ) . '" href="' . esc_url(add_query_arg(array(
					'post_type' => 'answer',
					'post_parent' => $post->ID,
				), 'edit.php')) . '">' . $a_count . '</a>';
			} /* If no terms were found, output a default message. */
			else {
				echo '<a class="ans-count" title="0' . __( 'answers', 'ap' ) . '">0</a>';
			}
		} elseif ( 'parent_question' == $column ) {
			echo '<a class="parent_question" href="' . esc_url(add_query_arg(array(
				'post' => $post->post_parent,
				'action' => 'edit',
			), 'post.php')) . '"><strong>' . get_the_title( $post->post_parent ) . '</strong></a>';
		} elseif ( 'vote' == $column ) {
			$vote = get_post_meta( $post->ID, ANSPRESS_VOTE_META, true );
			echo '<span class="vote-count' . ($vote ? ' zero' : '') . '">' .$vote . '</span>';
		} elseif ( 'flag' == $column ) {
			$total_flag = ap_post_flag_count();
			echo '<span class="flag-count' . ($total_flag ? ' flagged' : '') . '">'. $total_flag . '</span>';
		}
	}
	/**
	 * Answer CPT columns
	 * @param  array $columns
	 * @return array
	 * @since 2.0.0-alpha2
	 */
	public function cpt_answer_columns($columns) {

		$columns = array(
			'cb'                => '<input type="checkbox" />',
			'ap_author'         => __( 'Author', 'ap' ),
			'answer_content'    => __( 'Content', 'ap' ),
			'status'            => __( 'Status', 'ap' ),
			'comments'          => __( 'Comments', 'ap' ),
			'vote'              => __( 'Vote', 'ap' ),
			'flag'              => __( 'Flag', 'ap' ),
			'date'              => __( 'Date', 'ap' ),
		);
		return $columns;
	}

	public function answer_row_actions($column, $post_id) {

		global $post, $mode;

		if ( 'answer_content' != $column ) {
			return; }

		$question = get_post( $post->post_parent );
		echo '<a href="'.get_permalink( $post->post_parent ).'" class="row-title">'.$question->post_title.'</a>';

		$content = get_the_excerpt();
		// get the first 80 words from the content and added to the $abstract variable
		preg_match( '/^([^.!?\s]*[\.!?\s]+){0,40}/', strip_tags( $content ), $abstract );
		// pregmatch will return an array and the first 80 chars will be in the first element
		echo $abstract[0] . '...';

		// First set up some variables
		$actions          = array();
		$post_type_object = get_post_type_object( $post->post_type );
		$can_edit_post    = current_user_can( $post_type_object->cap->edit_post, $post->ID );

		// Actions to delete/trash
		if ( current_user_can( $post_type_object->cap->delete_post, $post->ID ) ) {
			if ( 'trash' == $post->post_status ) {
				$_wpnonce           = wp_create_nonce( 'untrash-post_' . $post_id );
				$url                = admin_url( 'post.php?post=' . $post_id . '&action=untrash&_wpnonce=' . $_wpnonce );
				$actions['untrash'] = "<a title='" . esc_attr( __( 'Restore this item from the Trash' ) ) . "' href='" . $url . "'>" . __( 'Restore' ) . '</a>';

			} elseif ( EMPTY_TRASH_DAYS ) {
				$actions['trash'] = "<a class='submitdelete' title='" . esc_attr( __( 'Move this item to the Trash' ) ) . "' href='" . get_delete_post_link( $post->ID ) . "'>" . __( 'Trash' ) . '</a>';
			}
			if ( 'trash' == $post->post_status || ! EMPTY_TRASH_DAYS ) {
				$actions['delete'] = "<a class='submitdelete' title='" . esc_attr( __( 'Delete this item permanently' ) ) . "' href='" . get_delete_post_link( $post->ID, '', true ) . "'>" . __( 'Delete Permanently' ) . '</a>'; }
		}
		if ( $can_edit_post ) {
			$actions['edit'] = '<a href="' . get_edit_post_link( $post->ID, '', true ) . '" title="' . esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;' ),$post->title ) ) . '" rel="permalink">' . __( 'Edit' ) . '</a>'; }

		// Actions to view/preview
		if ( in_array($post->post_status, array(
			'pending',
			'draft',
			'future',
		)) ) {
			if ( $can_edit_post ) {
				$actions['view'] = '<a href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) . '" title="' . esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;' ),$post->title ) ) . '" rel="permalink">' . __( 'Preview' ) . '</a>'; }
		} elseif ( 'trash' != $post->post_status ) {
			$actions['view'] = '<a href="' . get_permalink( $post->ID ) . '" title="' . esc_attr( __( 'View &#8220;%s&#8221; question' ) ) . '" rel="permalink">' . __( 'View' ) . '</a>';
		}

		// ***** END  -- Our actions  *******//
		// Echo the 'actions' HTML, let WP_List_Table do the hard work
		$WP_List_Table = new WP_List_Table();
		echo $WP_List_Table->row_actions( $actions );
	}

	public function admin_column_sort_flag($columns) {

		$columns['flag'] = 'flag';
		return $columns;
	}

	public function admin_column_sort_flag_by($query) {

		if ( ! is_admin() ) {
			return; }

		$orderby = $query->get( 'orderby' );

		if ( 'flag' == $orderby ) {
			$query->set( 'meta_key', ANSPRESS_FLAG_META );
			$query->set( 'orderby', 'meta_value_num' );
		}
	}

}
