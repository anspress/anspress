<?php
/**
 * AnsPress post types
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-3.0+
 * @link      https://anspress.net
 * @copyright 2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Custom post type.
 */
class AnsPress_PostTypes {

	/**
	 * Initialize the class
	 */
	public static function init() {
		// Register Custom Post types and taxonomy.
		anspress()->add_action( 'init', __CLASS__, 'register_question_cpt', 0 );
		anspress()->add_action( 'init', __CLASS__, 'register_answer_cpt', 0 );
		anspress()->add_action( 'post_type_link', __CLASS__, 'post_type_link', 10, 4 );
		anspress()->add_filter( 'post_type_archive_link', __CLASS__, 'post_type_archive_link', 10, 2 );
		anspress()->add_filter( 'post_updated_messages', __CLASS__, 'post_updated_messages', 10 );
		anspress()->add_filter( 'bulk_post_updated_messages', __CLASS__, 'bulk_post_updated_messages', 10, 2 );
	}

	/**
	 * Return question permalink structure.
	 *
	 * @return object
	 * @since 4.1.0
	 */
	public static function question_perm_structure() {
		$question_permalink = ap_opt( 'question_page_permalink' );
		$question_slug      = ap_get_page_slug( 'question' );

		$rewrites = array();
		if ( 'question_perma_2' === $question_permalink ) {
			$rewrites['rule'] = $question_slug . '/%question%';
		} elseif ( 'question_perma_3' === $question_permalink ) {
			$rewrites['rule'] = $question_slug . '/%question_id%';
		} elseif ( 'question_perma_4' === $question_permalink ) {
			$rewrites['rule'] = $question_slug . '/%question_id%/%question%';
		} elseif ( 'question_perma_5' === $question_permalink ) {
			$rewrites['rule'] = $question_slug . '/%question%/%question_id%';
		} elseif ( 'question_perma_6' === $question_permalink ) {
			$rewrites['rule'] = $question_slug . '/%question_id%-%question%';
		} elseif ( 'question_perma_7' === $question_permalink ) {
			$rewrites['rule'] = $question_slug . '/%question%-%question_id%';
		} else {
			$rewrites['rule'] = ap_base_page_slug() . '/' . $question_slug . '/%question%';
		}

		/**
		 * Allows filtering question permalink structure.
		 *
		 * @param array $rewrite Question permalink structure.
		 * @since 4.1.0
		 */
		return (object) apply_filters( 'ap_question_perm_structure', $rewrites );
	}

	/**
	 * Register question CPT.
	 *
	 * @since 2.0.1
	 */
	public static function register_question_cpt() {
		add_rewrite_tag( '%question_id%', '([0-9]+)', 'post_type=question&p=' );
		add_rewrite_tag( '%question%', '([^/]+)' );

		// Question CPT labels.
		$labels = array(
			'name'               => _x( 'Questions', 'Post Type General Name', 'anspress-question-answer' ),
			'singular_name'      => _x( 'Question', 'Post Type Singular Name', 'anspress-question-answer' ),
			'menu_name'          => __( 'Questions', 'anspress-question-answer' ),
			'parent_item_colon'  => __( 'Parent question:', 'anspress-question-answer' ),
			'all_items'          => __( 'All questions', 'anspress-question-answer' ),
			'view_item'          => __( 'View question', 'anspress-question-answer' ),
			'add_new_item'       => __( 'Add new question', 'anspress-question-answer' ),
			'add_new'            => __( 'New question', 'anspress-question-answer' ),
			'edit_item'          => __( 'Edit question', 'anspress-question-answer' ),
			'update_item'        => __( 'Update question', 'anspress-question-answer' ),
			'search_items'       => __( 'Search questions', 'anspress-question-answer' ),
			'not_found'          => __( 'No question found', 'anspress-question-answer' ),
			'not_found_in_trash' => __( 'No questions found in trash', 'anspress-question-answer' ),
		);

		/**
		 * Override default question CPT labels.
		 *
		 * @param array $labels Default question labels.
		 */
		$labels = apply_filters( 'ap_question_cpt_labels', $labels );

		// Question CPT arguments.
		$args = array(
			'label'               => __( 'question', 'anspress-question-answer' ),
			'description'         => __( 'Question', 'anspress-question-answer' ),
			'labels'              => $labels,
			'supports'            => array(
				'title',
				'editor',
				'author',
				'comments',
				'excerpt',
				'trackbacks',
				'revisions',
				'custom-fields',
				'buddypress-activity',
			),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => true,
			'menu_icon'           => ANSPRESS_URL . 'assets/question.png',
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'rewrite'             => false,
			'query_var'           => 'question',
			'delete_with_user'    => true,
		);

		/**
		 * Filter default question CPT arguments.
		 *
		 * @param array $args CPT arguments.
		 */
		$args = apply_filters( 'ap_question_cpt_args', $args );

		// Call it before registering cpt.
		AnsPress_Rewrite::rewrite_rules();

		// Register CPT question.
		register_post_type( 'question', $args );
	}

	/**
	 * Register answer custom post type.
	 *
	 * @since  2.0
	 */
	public static function register_answer_cpt() {
		// Answer CPT labels.
		$labels = array(
			'name'               => _x( 'Answers', 'Post Type General Name', 'anspress-question-answer' ),
			'singular_name'      => _x( 'Answer', 'Post Type Singular Name', 'anspress-question-answer' ),
			'menu_name'          => __( 'Answers', 'anspress-question-answer' ),
			'parent_item_colon'  => __( 'Parent answer:', 'anspress-question-answer' ),
			'all_items'          => __( 'All answers', 'anspress-question-answer' ),
			'view_item'          => __( 'View answer', 'anspress-question-answer' ),
			'add_new_item'       => __( 'Add new answer', 'anspress-question-answer' ),
			'add_new'            => __( 'New answer', 'anspress-question-answer' ),
			'edit_item'          => __( 'Edit answer', 'anspress-question-answer' ),
			'update_item'        => __( 'Update answer', 'anspress-question-answer' ),
			'search_items'       => __( 'Search answers', 'anspress-question-answer' ),
			'not_found'          => __( 'No answer found', 'anspress-question-answer' ),
			'not_found_in_trash' => __( 'No answer found in trash', 'anspress-question-answer' ),
		);

		/**
		 * Filter default answer labels.
		 *
		 * @param array $labels Default answer labels.
		 */
		$labels = apply_filters( 'ap_answer_cpt_label', $labels );

		// Answers CPT arguments.
		$args = array(
			'label'               => __( 'answer', 'anspress-question-answer' ),
			'description'         => __( 'Answer', 'anspress-question-answer' ),
			'labels'              => $labels,
			'supports'            => array(
				'editor',
				'author',
				'comments',
				'excerpt',
				'revisions',
				'custom-fields',
			),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => false,
			'menu_icon'           => ANSPRESS_URL . 'assets/answer.png',
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'rewrite'             => false,
			'query_var'           => 'answer',
		);

		/**
		 * Filter default answer arguments.
		 *
		 * @param array $args Arguments.
		 */
		$args = apply_filters( 'ap_answer_cpt_args', $args );

		// Register CPT answer.
		register_post_type( 'answer', $args );
	}

	/**
	 * Alter question and answer CPT permalink.
	 *
	 * @param  string $link      Link.
	 * @param  object $post      Post object.
	 * @param  bool   $leavename Whether to keep the post name.
	 * @param  bool   $sample    Is it a sample permalink.
	 * @return string
	 * @since 2.0.0
	 */
	public static function post_type_link( $link, $post, $leavename, $sample ) {
		if ( 'question' === $post->post_type ) {
			$question_slug = ap_opt( 'question_page_permalink' );

			if ( empty( $question_slug ) ) {
				$question_slug = 'question_perma_1';
			}

			$default_lang = '';

			// Support polylang permalink.
			if ( function_exists( 'pll_default_language' ) ) {
				$default_lang = pll_get_post_language( $post->ID ) ? pll_get_post_language( $post->ID ) : pll_default_language();
			}

			if ( get_option( 'permalink_structure' ) ) {
				$structure = self::question_perm_structure();
				$rule      = str_replace( '%question_id%', $post->ID, $structure->rule );
				$rule      = str_replace( '%question%', ( $leavename ? '%question%' : $post->post_name ), $rule );
				$link      = home_url( $default_lang . '/' . $rule . '/' );
			} else {
				$link = add_query_arg( array( 'question' => $post->ID ), ap_base_page_link() );
			}

			/**
			 * Allow overriding of question post type permalink
			 *
			 * @param string $link Question link.
			 * @param object $post Post object.
			 */
			$link = apply_filters_deprecated( 'ap_question_post_type_link', array( $link, $post ), '4.4.0', 'ap_question_post_type_link_structure' );

			/**
			 * Allow overriding of question post type permalink
			 *
			 * @param string $link      Question link.
			 * @param object $post      Post object.
			 * @param bool   $leavename Whether to keep the post name.
			 * @param bool   $sample    Is it a sample permalink.
			 */
			$link = apply_filters( 'ap_question_post_type_link_structure', $link, $post, $leavename, $sample );

			return $link;
		} elseif ( 'answer' === $post->post_type && 0 !== (int) $post->post_parent ) {
			$link = get_permalink( $post->post_parent ) . "answer/{$post->ID}/";

			/**
			 * Allow overriding of answer post type permalink.
			 *
			 * @param string $link Answer link.
			 * @param object $post Post object.
			 */
			$link = apply_filters_deprecated( 'ap_answer_post_type_link', array( $link, $post ), '4.4.0', 'ap_answer_post_type_link_structure' );

			/**
			 * Allow overriding of answer post type permalink
			 *
			 * @param string $link      Answer link.
			 * @param object $post      Post object.
			 * @param bool   $leavename Whether to keep the post name.
			 * @param bool   $sample    Is it a sample permalink.
			 */
			$link = apply_filters( 'ap_answer_post_type_link_structure', $link, $post, $leavename, $sample );

			return $link;
		}

		return $link;
	}

	/**
	 * Filters the post type archive permalink.
	 *
	 * @param string $link      The post type archive permalink.
	 * @param string $post_type Post type name.
	 * @since 4.1.0
	 */
	public static function post_type_archive_link( $link, $post_type ) {
		if ( 'question' === $post_type ) {
			return get_permalink( ap_opt( 'base_page' ) );
		}

		return $link;
	}

	/**
	 * Filter the post updated messages to add Question and Answer
	 * custom post type post updated messages.
	 *
	 * @param array[] $messages Post updated messages.
	 */
	public static function post_updated_messages( $messages ) {
		global $post;
		$permalink      = get_permalink( $post->ID );
		$scheduled_date = sprintf(
			/* translators: Publish box date string. 1: Date, 2: Time. */
			__( '%1$s at %2$s', 'anspress-question-answer' ),
			/* translators: Publish box date format, see https://www.php.net/manual/datetime.format.php */
			date_i18n( _x( 'M j, Y', 'publish box date format', 'anspress-question-answer' ), strtotime( $post->post_date ) ),
			/* translators: Publish box time format, see https://www.php.net/manual/datetime.format.php */
			date_i18n( _x( 'H:i', 'publish box time format', 'anspress-question-answer' ), strtotime( $post->post_date ) )
		);

		// Post updated message for Question post type.
		$messages['question'] = array(
			0  => '', // Unused. Messages start at index 1.
			/* translators: %s Question view URL. */
			1  => sprintf( __( 'Question updated. <a href="%s">View Question</a>', 'anspress-question-answer' ), esc_url( $permalink ) ),
			2  => __( 'Custom field updated.', 'anspress-question-answer' ),
			3  => __( 'Custom field deleted.', 'anspress-question-answer' ),
			4  => __( 'Question updated.', 'anspress-question-answer' ),
			/* translators: %s: Date and time of the revision. */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Question restored to revision from %s.', 'anspress-question-answer' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false, // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			/* translators: %s: Question url */
			6  => sprintf( __( 'Question published. <a href="%s">View Question</a>', 'anspress-question-answer' ), esc_url( $permalink ) ),
			7  => __( 'Question saved.', 'anspress-question-answer' ),
			/* translators: %s: Question url */
			8  => sprintf( __( 'Question submitted. <a target="_blank" href="%s">Preview question</a>', 'anspress-question-answer' ), esc_url( get_preview_post_link( $post ) ) ),
			9  => sprintf(
				/* translators: 1: Scheduled date for the question 2: Question url */
				__( 'Question scheduled for: %1$s. <a target="_blank" href="%2$s">Preview question</a>', 'anspress-question-answer' ),
				'<strong>' . $scheduled_date . '</strong>',
				esc_url( $permalink )
			),
			/* translators: %s: Question url */
			10 => sprintf( __( 'Question draft updated. <a target="_blank" href="%s">Preview question</a>', 'anspress-question-answer' ), esc_url( get_preview_post_link( $post ) ) ),
		);

		// Post updated message for Answer post type.
		$messages['answer'] = array(
			0  => '', // Unused. Messages start at index 1.
			/* translators: %s Answer view URL. */
			1  => sprintf( __( 'Answer updated. <a href="%s">View Answer</a>', 'anspress-question-answer' ), esc_url( $permalink ) ),
			2  => __( 'Custom field updated.', 'anspress-question-answer' ),
			3  => __( 'Custom field deleted.', 'anspress-question-answer' ),
			4  => __( 'Answer updated.', 'anspress-question-answer' ),
			/* translators: %s: Date and time of the revision. */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Answer restored to revision from %s.', 'anspress-question-answer' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false, // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			/* translators: %s: Answer url */
			6  => sprintf( __( 'Answer published. <a href="%s">View Answer</a>', 'anspress-question-answer' ), esc_url( $permalink ) ),
			7  => __( 'Answer saved.', 'anspress-question-answer' ),
			/* translators: %s: Answer url */
			8  => sprintf( __( 'Answer submitted. <a target="_blank" href="%s">Preview answer</a>', 'anspress-question-answer' ), esc_url( get_preview_post_link( $post ) ) ),
			9  => sprintf(
				/* translators: 1: Scheduled date for the answer 2: Answer url */
				__( 'Answer scheduled for: %1$s. <a target="_blank" href="%2$s">Preview answer</a>', 'anspress-question-answer' ),
				'<strong>' . $scheduled_date . '</strong>',
				esc_url( $permalink )
			),
			/* translators: %s: Answer url */
			10 => sprintf( __( 'Answer draft updated. <a target="_blank" href="%s">Preview answer</a>', 'anspress-question-answer' ), esc_url( get_preview_post_link( $post ) ) ),
		);

		return $messages;
	}

	/**
	 * Filter the bulk action updated messages to add Question and Answer
	 * custom post type bulk post updated messages.
	 *
	 * @param array[] $bulk_messages Arrays of messages, each keyed by the corresponding post type. Messages are
	 *                               keyed with 'updated', 'locked', 'deleted', 'trashed', and 'untrashed'.
	 * @param int[]   $bulk_counts   Array of item counts for each message, used to build internationalized strings.
	 */
	public static function bulk_post_updated_messages( $bulk_messages, $bulk_counts ) {
		$bulk_messages['question'] = array(
			/* translators: %s: Number of questions. */
			'updated'   => _n( '%s question updated.', '%s questions updated.', $bulk_counts['updated'], 'anspress-question-answer' ),
			'locked'    => ( 1 === $bulk_counts['locked'] ) ? __( '1 question not updated, somebody is editing it.', 'anspress-question-answer' ) :
							/* translators: %s: Number of questions. */
							_n( '%s question not updated, somebody is editing it.', '%s questions not updated, somebody is editing them.', $bulk_counts['locked'], 'anspress-question-answer' ),
			/* translators: %s: Number of questions. */
			'deleted'   => _n( '%s question permanently deleted.', '%s questions permanently deleted.', $bulk_counts['deleted'], 'anspress-question-answer' ),
			/* translators: %s: Number of questions. */
			'trashed'   => _n( '%s question moved to the Trash.', '%s questions moved to the Trash.', $bulk_counts['trashed'], 'anspress-question-answer' ),
			/* translators: %s: Number of questions. */
			'untrashed' => _n( '%s question restored from the Trash.', '%s questions restored from the Trash.', $bulk_counts['untrashed'], 'anspress-question-answer' ),
		);

		$bulk_messages['answer'] = array(
			/* translators: %s: Number of answers. */
			'updated'   => _n( '%s answer updated.', '%s answers updated.', $bulk_counts['updated'], 'anspress-question-answer' ),
			'locked'    => ( 1 === $bulk_counts['locked'] ) ? __( '1 answer not updated, somebody is editing it.', 'anspress-question-answer' ) :
							/* translators: %s: Number of answers. */
							_n( '%s answer not updated, somebody is editing it.', '%s answers not updated, somebody is editing them.', $bulk_counts['locked'], 'anspress-question-answer' ),
			/* translators: %s: Number of answers. */
			'deleted'   => _n( '%s answer permanently deleted.', '%s answers permanently deleted.', $bulk_counts['deleted'], 'anspress-question-answer' ),
			/* translators: %s: Number of answers. */
			'trashed'   => _n( '%s answer moved to the Trash.', '%s answers moved to the Trash.', $bulk_counts['trashed'], 'anspress-question-answer' ),
			/* translators: %s: Number of answers. */
			'untrashed' => _n( '%s answer restored from the Trash.', '%s answers restored from the Trash.', $bulk_counts['untrashed'], 'anspress-question-answer' ),
		);

		return $bulk_messages;
	}
}
