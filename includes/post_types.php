<?php
/**
 * AnsPress post types
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      https://anspress.io
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
			'name'              => _x( 'Questions', 'Post Type General Name', 'anspress-question-answer' ),
			'singular_name'     => _x( 'Question', 'Post Type Singular Name', 'anspress-question-answer' ),
			'menu_name'         => __( 'Questions', 'anspress-question-answer' ),
			'parent_item_colon' => __( 'Parent Question:', 'anspress-question-answer' ),
			'all_items'         => __( 'All Questions', 'anspress-question-answer' ),
			'view_item'         => __( 'View Question', 'anspress-question-answer' ),
			'add_new_item'      => __( 'Add New Question', 'anspress-question-answer' ),
			'add_new'           => __( 'New Question', 'anspress-question-answer' ),
			'edit_item'         => __( 'Edit Question', 'anspress-question-answer' ),
			'update_item'       => __( 'Update Question', 'anspress-question-answer' ),
			'search_items'      => __( 'Search Questions', 'anspress-question-answer' ),
			'not_found'         => __( 'No question found', 'anspress-question-answer' ),
			'not_found_in_trash' => __( 'No questions found in Trash', 'anspress-question-answer' ),
		);

		/**
		 * FILTER: ap_question_cpt_labels
		 * filter is called before registering question CPT
		 */
		$labels = apply_filters( 'ap_question_cpt_labels', $labels );

		// question CPT arguments
		$args   = array(
			'label' => __( 'question', 'anspress-question-answer' ),
			'description' => __( 'Question', 'anspress-question-answer' ),
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
			'name'          => _x( 'Answers', 'Post Type General Name', 'anspress-question-answer' ),
			'singular_name' => _x( 'Answer', 'Post Type Singular Name', 'anspress-question-answer' ),
			'menu_name' => __( 'Answers', 'anspress-question-answer' ),
			'parent_item_colon' => __( 'Parent Answer:', 'anspress-question-answer' ),
			'all_items' => __( 'All Answers', 'anspress-question-answer' ),
			'view_item' => __( 'View Answer', 'anspress-question-answer' ),
			'add_new_item' => __( 'Add New Answer', 'anspress-question-answer' ),
			'add_new' => __( 'New Answer', 'anspress-question-answer' ),
			'edit_item' => __( 'Edit Answer', 'anspress-question-answer' ),
			'update_item' => __( 'Update Answer', 'anspress-question-answer' ),
			'search_items' => __( 'Search Answers', 'anspress-question-answer' ),
			'not_found' => __( 'No answer found', 'anspress-question-answer' ),
			'not_found_in_trash' => __( 'No answer found in Trash', 'anspress-question-answer' ),
		);

		/**
		 * FILTER: ap_answer_cpt_label
		 * filter is called before registering answer CPT
		 */
		$labels = apply_filters( 'ap_answer_cpt_label', $labels );

		// Answers CPT arguments
		$args   = array(
			'label' => __( 'answer', 'anspress-question-answer' ),
			'description' => __( 'Answer', 'anspress-question-answer' ),
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
		$columns['ap_author']      = __( 'Author', 'anspress-question-answer' );
		$columns['title']       = __( 'Title', 'anspress-question-answer' );

		if ( taxonomy_exists( 'question_category' ) ) {
			$columns['question_category']       = __( 'Category', 'anspress-question-answer' ); }

		if ( taxonomy_exists( 'question_tag' ) ) {
			$columns['question_tag']       = __( 'Tag', 'anspress-question-answer' ); }

		$columns['status']      = __( 'Status', 'anspress-question-answer' );
		$columns['answers']     = __( 'Ans', 'anspress-question-answer' );
		$columns['comments']    = __( 'Comments', 'anspress-question-answer' );
		$columns['vote']        = __( 'Vote', 'anspress-question-answer' );
		$columns['flag']        = __( 'Flag', 'anspress-question-answer' );
		$columns['date']        = __( 'Date', 'anspress-question-answer' );

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
				echo __( 'Private', 'anspress-question-answer' ); } elseif ('closed' == $post->post_status)
					echo __( 'Closed', 'anspress-question-answer' );

				elseif ('moderate' == $post->post_status)
					echo __( 'Moderate', 'anspress-question-answer' );

				elseif ('private' == $post->post_status)
					echo __( 'Private', 'anspress-question-answer' );

				elseif ('darft' == $post->post_status)
					echo __( 'Draft', 'anspress-question-answer' );

				elseif ('pending' == $post->post_status)
					echo __( 'Pending', 'anspress-question-answer' );

				elseif ('trash' == $post->post_status)
					echo __( 'Trash', 'anspress-question-answer' );

			else {
				echo __( 'Open', 'anspress-question-answer' ); }

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
				_e( '--', 'anspress-question-answer' );
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
				_e( '--', 'anspress-question-answer' );
			}
		} elseif ( 'answers' == $column ) {
			$a_count = ap_count_answer_meta();

			/* If terms were found. */
			if ( ! empty( $a_count ) ) {

				echo '<a class="ans-count" title="' . $a_count . __( 'answers', 'anspress-question-answer' ) . '" href="' . esc_url(add_query_arg(array(
					'post_type' => 'answer',
					'post_parent' => $post->ID,
				), 'edit.php')) . '">' . $a_count . '</a>';
			} /* If no terms were found, output a default message. */
			else {
				echo '<a class="ans-count" title="0' . __( 'answers', 'anspress-question-answer' ) . '">0</a>';
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
			'ap_author'         => __( 'Author', 'anspress-question-answer' ),
			'answer_content'    => __( 'Content', 'anspress-question-answer' ),
			'status'            => __( 'Status', 'anspress-question-answer' ),
			'comments'          => __( 'Comments', 'anspress-question-answer' ),
			'vote'              => __( 'Vote', 'anspress-question-answer' ),
			'flag'              => __( 'Flag', 'anspress-question-answer' ),
			'date'              => __( 'Date', 'anspress-question-answer' ),
		);
		return $columns;
	}

	public function admin_column_sort_flag($columns) {

		$columns['flag'] = 'flag';
		return $columns;
	}

	public function admin_column_sort_flag_by($query) {

		if ( ! is_admin() ) {
			return;
		}

		$orderby = $query->get( 'orderby' );

		if ( 'flag' == $orderby ) {
			$query->set( 'meta_key', ANSPRESS_FLAG_META );
			$query->set( 'orderby', 'meta_value_num' );
		}
	}

}
