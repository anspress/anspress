<?php
/**
 * AnsPress question object.
 *
 * @package    AnsPress
 * @subpackage Question Class
 * @since      4.1.0
 * @author     Rahul Aryan<support@anspress.io>
 * @copyright  Copyright (c) 2017, Rahul Aryan
 * @license    http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The AP_QA class.
 */
abstract class AP_QA {

	/**
	 * The question ID.
	 *
	 * @var integer
	 */
	public $ID = 0;

	/**
	 * Declare the default properties in WP_Post as we can't extend it
	 * Anything we've declared above has been removed.
	 *
	 * @codingStandardsIgnoreStart
	 */
	public $post_author = 0;
	public $post_date = '0000-00-00 00:00:00';
	public $post_date_gmt = '0000-00-00 00:00:00';
	public $post_content = '';
	public $post_title = '';
	public $post_excerpt = '';
	public $post_status = 'publish';
	public $comment_status = 'open';
	public $ping_status = 'open';
	public $post_password = '';
	public $post_name = '';
	public $to_ping = '';
	public $pinged = '';
	public $post_modified = '0000-00-00 00:00:00';
	public $post_modified_gmt = '0000-00-00 00:00:00';
	public $post_content_filtered = '';
	public $post_parent = 0;
	public $guid = '';
	public $menu_order = 0;
	public $post_mime_type = '';
	public $comment_count = 0;
	public $filter;

	/**
	 * AnsPress properties.
	 */
	public $answers = 0;
	public $flags = 0;
	public $closed = false;
	public $featured = false;
	public $fields = [];
	public $last_updated = '0000-00-00 00:00:00';
	public $roles = '';
	public $selected = false;
	public $selected_id = null;
	public $subscribers = 0;
	public $terms = '';
	public $views = 0;
	public $votes_down = 0;
	public $votes_net = 0;
	public $votes_up = 0;
	// @codingStandardsIgnoreEnd

	/**
	 * All valid post columns names.
	 *
	 * @var array
	 */
	public $post_fields = [];

	/**
	 * Unsaved post columns.
	 *
	 * @var array
	 */
	public $unsaved_post_fields = [];

	/**
	 * All valid qameta columns.
	 *
	 * @var array
	 */
	public $qameta_fields = [];

	/**
	 * Unsaved qameta columns.
	 *
	 * @var array
	 */
	public $unsaved_qameta = [];

	public $unsaved_terms = [];

	public $unsaved_unset_terms = [];


	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @param string $key Property name.
	 */
	public function __get( $key ) {
		if ( method_exists( $this, 'get_' . $key ) ) {
			return call_user_func( array( $this, 'get_' . $key ) );
		} else {
			// Translators: Placeholder is name of property.
			return new WP_Error( 'ap-invalid-property', sprintf( __( 'Can\'t get property %s', 'anspress-question-answer' ), $key ) );
		}
	}

	public function __call( $method, $args ) {
		$new_method = str_replace( 'the_', 'get_', $method );

		$echo_methods = [ 'the_ID', 'the_author_link', 'the_author_avatar', 'the_votes_net', 'the_answers_count' ];

		if ( in_array( $method, $echo_methods, true ) && method_exists( $this, $new_method ) ) {
			echo call_user_func_array( [ $this, $new_method ], $args );
		} else {
			// Translators: Placeholder is name of property.
			throw new Exception( sprintf( __( 'Can\'t get method %s', 'anspress-question-answer' ), $method ) );
		}
	}

	/**
	 * Set post field value for a question or answer.
	 *
	 * This function does not actually save data to database. Everything is
	 * just stored in object. For saving fields to database `save()` method
	 * must be called after this method. This is useful when there are multiple
	 * qameta fields to update and you just want to call one mysql update statement.
	 *
	 * @param string $key   Post or qameta field name.
	 * @param mixed  $value post or qameta Qameta value.
	 *
	 * @return WP_Error|null Return `WP_Error` if not a valid field else null.
	 */
	public function set( $key, $value ) {
		if ( empty( $this->post_fields ) ) {
			$this->post_fields = [ 'post_author', 'post_date', 'post_content', 'post_title', 'post_excerpt', 'post_status', 'comment_status', 'post_password', 'post_name', 'post_parent', 'comment_count' ];
		}

		if ( empty( $this->qameta_fields ) ) {
			$this->qameta_fields = array_keys( ap_qameta_fields() );
		}

		/**
		 * Allow filtering post or qameta column value.
		 *
		 * @param mixed  $value column value.
		 * @param string $key Name of post or qameta column.
		 * @param object $object Current qa object passed by reference.
		 */
		$value = apply_filters_ref_array( 'ap_set_qa_unsaved_value',  [ $value, $key, &$this ] );

		// Check if post field.
		if ( in_array( $key, $this->post_fields, true ) ) {
			$this->unsaved_post_fields[ $key ] = $value;
		}

		// Check if qameta field.
		if ( in_array( $key, $this->qameta_fields, true ) ) {
			$this->unsaved_qameta[ $key ] = $value;
		}

		return new WP_Error(
			'ap_not_valid_post_field',
			// Translators: placeholder contain name of post or qameta field.
			sprintf( __( '%s is not a valid post field', 'anspress-question-answer' ), $key )
		);
	}

	/**
	 * Set term(s) for current post.
	 *
	 * @param  array|integer $terms Term ids or single term id.
	 * @param  string        $taxonomy Taxonomy slug.
	 * @return array|boolean|WP_Error|string
	 */
	public function set_terms( $terms, $taxonomy = 'question_category' ) {
		if ( ! isset( $this->unsaved_terms[ $taxonomy ] ) ) {
			$this->unsaved_terms[ $taxonomy ] = [];
		}

		if ( is_array( $terms ) ) {
			$this->unsaved_terms[ $taxonomy ] = array_merge( $this->unsaved_terms[ $taxonomy ], $terms );
		} else {
			$this->unsaved_terms[ $taxonomy ][] = $terms;
		}
	}

	public function unset_terms( $terms, $taxonomy = 'question_category' ) {
		if ( ! isset( $this->unsaved_unset_terms[ $taxonomy ] ) ) {
			$this->unsaved_unset_terms[ $taxonomy ] = [];
		}

		if ( is_array( $terms ) ) {
			$this->unsaved_unset_terms[ $taxonomy ] = array_merge( $this->unsaved_unset_terms[ $taxonomy ], $terms );
		} else {
			$this->unsaved_unset_terms[ $taxonomy ][] = $terms;
		}
	}

	/**
	 * Save qameta data stored in current object.
	 *
	 * @return null|WP_Error|boolean Returns null if no data to save. WP_Error object if
	 *                               something went wrong saving qameta field. Returns true
	 *                               if fields get saved successfully.
	 */
	public function save() {
		$id = $this->save_post();
		$post = ap_get_post( $id );
		$this->setup_post( $post );
		$this->save_qameta();
		$this->save_terms();

		return $id;
	}

	/**
	 * Save post.
	 *
	 * @return integer|WP_Error
	 */
	public function save_post() {
		if ( empty( $this->unsaved_post_fields ) ) {
			return;
		}

		// Default arguments.
		$defaults = array(
			'post_status'      => 'draft',
			'post_title'       => __( 'New Question', 'anspress-question-answer' ),
			'comment_status' 	 => 'open',
			'attach_uploads' 	 => false,
			'post_author' 		 => 0,
			'is_private' 		   => false,
			'post_name' 		   => '',
			'post_type' 		   => 'question',
		);

		$args = wp_parse_args( $this->unsaved_post_fields, $defaults );
		$args['ID'] = $this->ID;
		$post_type = $args['post_type'];

		if ( $this->ID ) {
			/**
			 * Can be used to modify `$args` before updating question or answer.
			 *
			 * @param array $args Post arguments.
			 * @since 2.0.1
			 * @since 4.1.0 Moved from includes/ask-form.php
			 */
			$args = apply_filters( "ap_pre_update_{$post_type}", $args );
		} else {
			/**
			 * Can be used to modify args before saving question or answer.
			 *
			 * @param array $args Post arguments.
			 * @since 2.0.1
			 * @since 4.1.0 Moved from includes/ask-form.php
			 */
			$args = apply_filters( "ap_pre_insert_{$post_type}", $args );
		}

		/**
		 * Fired before a question or answer is saved.
		 *
		 * @param array  $args   The post object arguments used for creation.
		 * @param object $object Current object passed by reference.
		 * @since 4.1.0
		 */
		do_action_ref_array( "ap_pre_save_{$post_type}", [ $args, &$this ] );

		$id = wp_insert_post( $args, true );

		// Return if error.
		if ( is_wp_error( $id ) ) {
			return $id;
		}

		// Set post parent for uploaded media.
		if ( ap_isset_post_value( 'ap-medias' ) ) {
			ap_set_media_post_parent( ap_sanitize_unslash( 'ap-medias', 'r' ), $id );
			ap_clear_unattached_media();
		}

		// Clear unsaved post fields.
		$this->unsaved_post_fields = [];

		return $id;
	}

	/**
	 * Save post qameta.
	 *
	 * @return void
	 */
	public function save_qameta() {
		if ( $this->ID && ! empty( $this->unsaved_qameta ) ) {
			ap_insert_qameta( $this->ID, $this->unsaved_qameta, true );
			$this->unsaved_qameta = [];
		}
	}

	/**
	 * Save terms.
	 *
	 * @return void
	 */
	public function save_terms() {
		// Unset terms.
		if ( $this->ID && ! empty( $this->unsaved_unset_terms ) ) {
			foreach ( $this->unsaved_unset_terms as $taxonomy => $terms ) {
				wp_remove_object_terms( $this->ID, $terms, $taxonomy );
			}
		}

		// Set terms.
		if ( $this->ID && ! empty( $this->unsaved_terms ) ) {
			foreach ( $this->unsaved_terms as $taxonomy => $terms ) {
				wp_set_object_terms( $this->ID, $terms, $taxonomy );
			}
		}
	}

	/**
	 * Given the post data, let's set the variables.
	 *
	 * @param  object|WP_Post $post Post object.
	 * @return bool           If the setup was successful or not
	 */
	public function setup_post( $post ) {
		if ( ! is_object( $post ) ) {
			return false;
		}

		foreach ( $post as $key => $value ) {
			switch ( $key ) {
				default:
					$this->$key = $value;
					break;
			}
		}

		return true;
	}

	/**
	 * Remove stop words from string if option is enabled.
	 *
	 * @param  string $str String to filter.
	 * @return string
	 */
	public function remove_stop_words( $str ) {
		$str = sanitize_title( $str );

		if ( ap_opt( 'keep_stop_words' ) ) {
			return $str;
		}

		$post_name = ap_remove_stop_words( $str );

		// Check if post name is not empty.
		if ( ! empty( $post_name ) ) {
			return $post_name;
		}

		// If empty then return original without stripping stop words.
		return sanitize_title( $str );
	}

	/**
	 * Return current post ID.
	 *
	 * @return integer
	 */
	public function get_ID() {
		return (int) $this->ID;
	}

	public function get_author_link() {
		return esc_url( ap_user_link( $this->post_author ) );
	}

	public function get_author_avatar( $size = 40 ) {
		$author = $this->post_author;
		if ( '0' === $author && is_array( $this->fields ) && ! empty( $this->fields['anonymous_name'] ) ) {
			$author = $this->fields['anonymous_name'];
		}

		return get_avatar( $author, $size );
	}

	public function get_votes_net() {
		return (int) $this->votes_net;
	}

	public function get_status_object( $key = null ) {
		$status_obj = get_post_status_object( $this->post_status );

		if ( $key && $status_obj->$key ) {
			return $status_obj->$key;
		}

		return $status_obj;
	}

	public function get_time( $format = '' ) {
		return get_post_time( $format, true, $this->ID, true );
	}

	/**
	 * Get latest activity of question or answer.
	 *
	 * @param  boolean $answer_activities Show answers activities as well.
	 * @return array|null
	 */
	public function get_recent_activity( $answer_activities = false ) {
		$activity = $this->activities;
		if ( false !== $answer_activities && ! empty( $this->activities['child'] ) ) {
			$activity = $this->activities['child'];
		}

		if ( ! empty( $activity ) && ! empty( $activity['date'] ) ) {
			$activity['date'] = get_gmt_from_date( $activity['date'] );
		}

		if ( false === $answer_activities && ( ! isset( $activity['type'] ) || in_array( $activity['type'], [ 'new_answer', 'new_question' ], true ) ) ) {
			return;
		}

		if ( $activity ) {
			$formatted = [];

			$user_id = ! empty( $activity['user_id'] ) ? $activity['user_id'] : 0;
			$activity_type = ! empty( $activity['type'] ) ? $activity['type'] : '';

			$formatted['actor']['id']   = $user_id;
			$formatted['actor']['name'] = ap_user_display_name( $user_id );
			$formatted['actor']['url']  = ap_user_link( $user_id );
			$formatted['verb']          = $activity_type;
			$formatted['summary']       = ap_activity_short_title( $activity_type );
			$formatted['url']           = get_permalink();
			$formatted['published']     = $activity['date'];
			$formatted['human_time']    = ap_human_time( $activity['date'], false );

			$formatted = (object) $formatted;

			/**
			 * Filter recent activity of post.
			 *
			 * @param object $formatted Formatted activity object.
			 * @param object $apqa      Current AnsPress post object passed by reference.
			 * @since 4.1.0
			 */
			return apply_filters_ref_array( 'ap_get_recent_activity', [ $formatted, &$this ] );
		}
	}

	public function the_recent_activity( $answer_activities = false ) {
		$activity = $this->get_recent_activity( $answer_activities );

		if ( $activity ) {
			echo '<span class="ap-post-activity">';
			echo '<a href="' . esc_url( $activity->actor['url'] ) . '">' . wp_kses_post( $activity->actor['name'] ) . '</a> ';
			echo '<a href="' . esc_url( $activity->url ) . '">' . esc_html( $activity->summary ) . '</a> ';
			echo '<time datetime="' . esc_attr( mysql2date( 'c', $activity->published ) ) . '">' . esc_html( $activity->human_time ) . '</time>';
			echo '</span>';
		}
	}

	/**
	 * Check if post have terms.
	 *
	 * @param string $taxonomy Taxonomy name.
	 * @return boolean
	 */
	public function have_terms( $taxonomy = 'question_category' ) {
		$terms = get_the_terms( $this->ID, $taxonomy );

		if ( ! empty( $terms ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get all terms of current question.
	 *
	 * @return array
	 */
	public function get_all_terms() {
		$taxonomies = [ 'question_category', 'question_tag' ];
		$taxonomies = apply_filters_ref_array( 'ap_object_taxonomies', [ $taxonomies, &$this ] );

		$taxonomies_data = [];

		foreach ( $taxonomies as $taxonomy ) {
			$taxonomies_data[ $taxonomy ] = get_the_terms( $this->ID, $taxonomy );
		}

		return $taxonomies_data;
	}

}
