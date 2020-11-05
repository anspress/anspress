<?php
/**
 * AnsPress common functions.
 *
 * @package AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-3.0+
 * @link      https://anspress.net
 * @copyright 2014 Rahul Aryan
 */

/**
 * Get slug of base page.
 *
 * @return string
 * @since  2.0.0
 * @since  3.0.0 Return `questions` if base page is not selected.
 * @since  4.1.6 Make sure always `questions` is returned if no base page is set.
 */
function ap_base_page_slug() {
	$slug = 'questions';

	if ( ! empty( ap_opt( 'base_page' ) ) ) {
		$base_page = get_post( ap_opt( 'base_page' ) );

		if ( $base_page ) {
			$slug = $base_page->post_name;

			if ( $base_page->post_parent > 0 ) {
				$parent_page = get_post( $base_page->post_parent );
				$slug        = $parent_page->post_name . '/' . $slug;
			}
		}
	}

	return apply_filters( 'ap_base_page_slug', $slug );
}

/**
 * Retrieve permalink to base page.
 *
 * @return  string URL to AnsPress base page
 * @since   2.0.0
 * @since   3.0.0 Return link to questions page if base page not selected.
 */
function ap_base_page_link() {
	if ( empty( ap_opt( 'base_page' ) ) ) {
		return home_url( '/questions/' );
	}
	return get_permalink( ap_opt( 'base_page' ) );
}

/**
 * Get location to a file. First file is being searched in child theme and then active theme
 * and last fall back to AnsPress theme directory.
 *
 * @param   string $file   file name.
 * @param   mixed  $plugin Plugin path. File is search inside AnsPress extension.
 * @return  string
 * @since   0.1
 * @since   2.4.7 Added filter `ap_get_theme_location`
 */
function ap_get_theme_location( $file, $plugin = false ) {

	$child_path  = get_stylesheet_directory() . '/anspress/' . $file;
	$parent_path = get_template_directory() . '/anspress/' . $file;

	// Checks if the file exists in the theme first,
	// Otherwise serve the file from the plugin.
	if ( file_exists( $child_path ) ) {
		$template_path = $child_path;
	} elseif ( file_exists( $parent_path ) ) {
		$template_path = $parent_path;
	} elseif ( false !== $plugin ) {
		$template_path = $plugin . '/templates/' . $file;
	} else {
		$template_path = ANSPRESS_THEME_DIR . '/' . $file;
	}

	/**
	 * Filter AnsPress template file.
	 *
	 * @param string $template_path Path to template file.
	 * @since 2.4.7
	 */
	return apply_filters( 'ap_get_theme_location', $template_path );
}

/**
 * Get url to a file
 * Used for enqueue CSS or JS.
 *
 * @param  string  $file   File name.
 * @param  mixed   $plugin Plugin path, if calling from AnsPress extension.
 * @param  boolean $ver    When true, AnsPress version will be appended to file url.
 * @return string
 * @since  2.0
 */
function ap_get_theme_url( $file, $plugin = false, $ver = true ) {
	$child_path  = get_stylesheet_directory() . '/anspress/' . $file;
	$parent_path = get_template_directory() . '/anspress/' . $file;

	// Checks if the file exists in the theme first.
	// Otherwise serve the file from the plugin.
	if ( file_exists( $child_path ) ) {
		$template_url = get_stylesheet_directory_uri() . '/anspress/' . $file;
	} elseif ( file_exists( $parent_path ) ) {
		$template_url = get_template_directory_uri() . '/anspress/' . $file;
	} elseif ( false !== $plugin ) {
		$template_url = $plugin . 'templates/' . $file;
	} else {
		$template_url = ANSPRESS_THEME_URL . '/' . $file;
	}

	/**
	 * Allows filtering url of a AnsPress file.
	 *
	 * @param string $url Url of a file.
	 * @since 2.0
	 */
	return apply_filters( 'ap_theme_url', $template_url . ( true === $ver ? '?v=' . AP_VERSION : '' ) );
}


/**
 * Check if current page is AnsPress. Also check if showing question or
 * answer page in BuddyPress.
 *
 * @return boolean
 * @since 4.1.0 Improved check. Check for main pages.
 * @since 4.1.1 Check for @see ap_current_page().
 * @since 4.1.8 Added filter `is_anspress`.
 */
function is_anspress() {
	$ret = false;

	// If BuddyPress installed.
	if ( function_exists( 'bp_current_component' ) ) {
		if ( in_array( bp_current_component(),  array( 'qa', 'questions', 'answers' ) ) ) {
			$ret = true;
		}
	}

	$page_slug      = array_keys( ap_main_pages() );
	$queried_object = get_queried_object();

	// Check if main pages.
	if ( $queried_object instanceof WP_Post ) {
		$page_ids = [];
		foreach ( $page_slug as $slug ) {
			$page_ids[] = ap_opt( $slug );
		}

		if ( in_array( $queried_object->ID, $page_ids ) ) {
			$ret = true;
		}
	}

	// Check if ap_page.
	if ( is_search() && 'question' === get_query_var( 'post_type' ) ) {
		$ret = true;
	} elseif ( '' !== ap_current_page() ) {
		$ret = true;
	}

	/**
	 * Filter for overriding is_anspress() return value.
	 *
	 * @param boolean $ret True or false.
	 * @since 4.1.8
	 */
	return apply_filters( 'is_anspress', $ret );
}

/**
 * Check if current page is question page.
 *
 * @return boolean
 * @since 0.0.1
 * @since 4.1.0 Also check and return true if singular question.
 */
function is_question() {
	if ( is_singular( 'question' ) ) {
		return true;
	}

	return false;
}

/**
 * Is if current AnsPress page is ask page.
 *
 * @return boolean
 */
function is_ask() {
	if ( is_anspress() && 'ask' === ap_current_page() ) {
		return true;
	}
	return false;
}

/**
 * Get current question ID in single question page.
 *
 * @return integer|false
 * @since unknown
 * @since 4.1.0 Remove `question_name` query var check. Get question ID from queried object.
 */
function get_question_id() {
	if ( is_question() && get_query_var( 'question_id' ) ) {
		return (int) get_query_var( 'question_id' );
	}

	if ( is_question() ) {
		return get_queried_object_id();
	}

	if ( get_query_var( 'edit_q' ) ) {
		return get_query_var( 'edit_q' );
	}

	if ( ap_answer_the_object() ) {
		return ap_get_post_field( 'post_parent' );
	}

	return false;
}

/**
 * Return human readable time format.
 *
 * @param  string         $time Time.
 * @param  boolean        $unix Is $time is unix.
 * @param  integer        $show_full_date Show full date after some period. Default is 7 days in epoch.
 * @param  boolean|string $format Date format.
 * @return string|null
 * @since  2.4.7 Checks if showing default date format is enabled.
 */
function ap_human_time( $time, $unix = true, $show_full_date = 604800, $format = false ) {
	if ( false === $format ) {
		$format = get_option( 'date_format' );
	}

	if ( ! is_numeric( $time ) && ! $unix ) {
		$time = strtotime( $time );
	}

	// If default date format is enabled then just return date.
	if ( ap_opt( 'default_date_format' ) ) {
		return date_i18n( $format, $time );
	}

	if ( $time ) {
		if ( $show_full_date + $time > current_time( 'timestamp', true ) ) {
			return sprintf(
				/* translators: %s: human-readable time difference */
				__( '%s ago', 'anspress-question-answer' ),
				human_time_diff( $time, current_time( 'timestamp', true ) )
			);
		}

		return date_i18n( $format, $time );
	}
}

/**
 * Check if user answered on a question.
 *
 * @param integer $question_id  Question ID.
 * @param integer $user_id      User ID.
 * @return boolean
 *
 * @since unknown
 * @since 4.1.6 Changed cache group to `counts`.
 * @todo clear cache after answer.
 */
function ap_is_user_answered( $question_id, $user_id ) {
	global $wpdb;

	$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts where post_parent = %d AND ( post_author = %d AND post_type = 'answer')", $question_id, $user_id ) ); // db call ok.

	return $count > 0 ? true : false;
}

/**
 * Return link to answers.
 *
 * @param  boolean|integer $question_id Question ID.
 * @return string
 */
function ap_answers_link( $question_id = false ) {
	if ( ! $question_id ) {
		return get_permalink() . '#answers';
	}
	return get_permalink( $question_id ) . '#answers';
}


/**
 * Return edit link for question and answer.
 *
 * @param mixed $_post Post.
 * @return string
 * @since 2.0.1
 */
function ap_post_edit_link( $_post ) {
	$_post     = ap_get_post( $_post );
	$nonce     = wp_create_nonce( 'edit-post-' . $_post->ID );
	$base_page = 'question' === $_post->post_type ? ap_get_link_to( 'ask' ) : ap_get_link_to( 'edit' );
	$edit_link = add_query_arg(
		array(
			'id'      => $_post->ID,
			'__nonce' => $nonce,
		), $base_page
	);

	/**
	 * Allows filtering post edit link.
	 *
	 * @param string $edit_link Url to edit post.
	 * @since unknown
	 */
	return apply_filters( 'ap_post_edit_link', $edit_link );
}

/**
 * Truncate string but preserve full word.
 *
 * @param string $text String.
 * @param int    $limit Limit string to.
 * @param string $ellipsis Ellipsis.
 * @return string
 *
 * @since 4.1.8 Strip tags.
 */
function ap_truncate_chars( $text, $limit = 40, $ellipsis = '...' ) {
	$text = strip_tags( $text );
	$text = str_replace( array( "\r\n", "\r", "\n", "\t" ), ' ', $text );
	if ( strlen( $text ) > $limit ) {
		$endpos = strpos( $text, ' ', (string) $limit );

		if ( false !== $endpos ) {
			$text = trim( substr( $text, 0, $endpos ) ) . $ellipsis;
		}
	}
	return $text;
}

/**
 * Convert number to 1K, 1M etc.
 *
 * @param  integer $num       Number to convert.
 * @param  integer $precision Precision.
 * @return string
 */
function ap_short_num( $num, $precision = 2 ) {
	if ( $num >= 1000 && $num < 1000000 ) {
		$n_format = number_format( $num / 1000, $precision ) . 'K';
	} elseif ( $num >= 1000000 && $num < 1000000000 ) {
		$n_format = number_format( $num / 1000000, $precision ) . 'M';
	} elseif ( $num >= 1000000000 ) {
		$n_format = number_format( $num / 1000000000, $precision ) . 'B';
	} else {
		$n_format = $num;
	}

	return $n_format;
}

/**
 * Sanitize comma delimited strings.
 *
 * @param  string|array $str Comma delimited string.
 * @param  string       $pieces_type Type of piece, string or number.
 * @return string
 */
function sanitize_comma_delimited( $str, $pieces_type = 'int' ) {
	$str = ! is_array( $str ) ? explode( ',', $str ) : $str;

	if ( ! empty( $str ) ) {
		$str       = wp_unslash( $str );
		$glue      = 'int' !== $pieces_type ? '","' : ',';
		$sanitized = [];
		foreach ( $str as $s ) {
			if ( '0' == $s || ! empty( $s ) ) {
				$sanitized[] = 'int' === $pieces_type ? intval( $s ) : str_replace( [ "'", '"', ',' ], '', sanitize_text_field( $s ) );
			}
		}

		$new_str = implode( $glue, esc_sql( $sanitized ) );

		if ( 'int' !== $pieces_type ) {
			return '"' . $new_str . '"';
		}

		return $new_str;
	}
}

/**
 * Check if doing ajax request.
 *
 * @return boolean
 * @since 2.0.1
 * @since  3.0.0 Check if `ap_ajax_action` is set.
 */
function ap_is_ajax() {
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_REQUEST['ap_ajax_action'] ) ) { // input var ok.
		return true;
	}

	return false;
}

/**
 * Allow HTML tags.
 *
 * @return array
 * @since 0.9
 */
function ap_form_allowed_tags() {
	global $ap_kses_check;
	$ap_kses_check = true;

	$allowed_style = array(
		'align' => true,
	);

	$allowed_tags = array(
		'p'          => array(
			'style' => $allowed_style,
			'title' => true,
		),
		'span'       => array(
			'style' => $allowed_style,
		),
		'a'          => array(
			'href'  => true,
			'title' => true,
		),
		'br'         => array(),
		'em'         => array(),
		'strong'     => array(
			'style' => $allowed_style,
		),
		'pre'        => array(),
		'code'       => array(),
		'blockquote' => array(),
		'img'        => array(
			'src'   => true,
			'style' => $allowed_style,
		),
		'ul'         => array(),
		'ol'         => array(),
		'li'         => array(),
		'del'        => array(),
	);

	/**
	 * Filter allowed HTML KSES tags.
	 *
	 * @param array $allowed_tags Allowed tags.
	 */
	return apply_filters( 'ap_allowed_tags', $allowed_tags );
}

/**
 * Send a array as a JSON.
 *
 * @param array $result Results.
 */
function ap_send_json( $result = array() ) {
	header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
	$result['is_ap_ajax'] = true;
	$json                 = '<div id="ap-response">' . wp_json_encode( $result, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP ) . '</div>';

	wp_die( $json ); // xss ok.
}

/**
 * Highlight matching words.
 *
 * @param string $text  String.
 * @param string $words Words need to highlight.
 * @return string
 * @since   2.0
 */
function ap_highlight_words( $text, $words ) {
	$words = explode( ' ', $words );
	foreach ( $words as $word ) {
		// Quote the text for regex.
		$word = preg_quote( $word );
		// Highlight the words.
		$text = preg_replace( "/\b($word)\b/i", '<span class="highlight_word">\1</span>', $text );
	}
	return $text;
}

/**
 * Return response with type and message.
 *
 * @param string $id           messge id.
 * @param bool   $only_message return message string instead of array.
 * @return string
 * @since 2.0.0
 */
function ap_responce_message( $id, $only_message = false ) {
	$msg = array(
		'success'                       => array(
			'type'    => 'success',
			'message' => __( 'Success', 'anspress-question-answer' ),
		),

		'something_wrong'               => array(
			'type'    => 'error',
			'message' => __( 'Something went wrong, last action failed.', 'anspress-question-answer' ),
		),

		'comment_edit_success'          => array(
			'type'    => 'success',
			'message' => __( 'Comment updated successfully.', 'anspress-question-answer' ),
		),
		'cannot_vote_own_post'          => array(
			'type'    => 'warning',
			'message' => __( 'You cannot vote on your own question or answer.', 'anspress-question-answer' ),
		),
		'no_permission_to_view_private' => array(
			'type'    => 'warning',
			'message' => __( 'You do not have permission to view private posts.', 'anspress-question-answer' ),
		),
		'captcha_error'                 => array(
			'type'    => 'error',
			'message' => __( 'Please check captcha field and resubmit it again.', 'anspress-question-answer' ),
		),
		'post_image_uploaded'           => array(
			'type'    => 'success',
			'message' => __( 'Image uploaded successfully', 'anspress-question-answer' ),
		),
		'answer_deleted_permanently'    => array(
			'type'    => 'success',
			'message' => __( 'Answer has been deleted permanently', 'anspress-question-answer' ),
		),
		'upload_limit_crossed'          => array(
			'type'    => 'warning',
			'message' => __( 'You have already attached maximum numbers of allowed uploads.', 'anspress-question-answer' ),
		),
		'profile_updated_successfully'  => array(
			'type'    => 'success',
			'message' => __( 'Your profile has been updated successfully.', 'anspress-question-answer' ),
		),
		'voting_down_disabled'          => array(
			'type'    => 'warning',
			'message' => __( 'Voting down is disabled.', 'anspress-question-answer' ),
		),
		'you_cannot_vote_on_restricted' => array(
			'type'    => 'warning',
			'message' => __( 'You cannot vote on restricted posts', 'anspress-question-answer' ),
		),
	);

	/**
	 * Filter ajax response message.
	 *
	 * @param array $msg Messages.
	 * @since 2.0.1
	 */
	$msg = apply_filters( 'ap_responce_message', $msg );

	if ( isset( $msg[ $id ] ) && $only_message ) {
		return $msg[ $id ]['message'];
	}

	if ( isset( $msg[ $id ] ) ) {
		return $msg[ $id ];
	}

	return false;
}

/**
 * Format an array as valid AnsPress ajax response.
 *
 * @param  array|string $results Response to send.
 * @return array
 * @since  unknown
 * @since  4.1.0 Removed `template` variable. Send `snackbar` for default message.
 */
function ap_ajax_responce( $results ) {
	if ( ! is_array( $results ) ) {
		$message_id         = $results;
		$results            = array();
		$results['message'] = $message_id;
	}

	$results['ap_responce'] = true;

	if ( isset( $results['message'] ) ) {
		$error_message = ap_responce_message( $results['message'] );

		if ( false !== $error_message ) {
			$results['snackbar'] = array(
				'message'      => $error_message['message'],
				'message_type' => $error_message['type'],
			);

			$results['success'] = 'error' === $error_message['type'] ? false : true;
		}
	}

	/**
	 * Filter AnsPress ajax response body.
	 *
	 * @param array $results Results.
	 * @since 2.0.1
	 */
	$results = apply_filters( 'ap_ajax_responce', $results );

	return $results;
}

/**
 * Array map callback.
 *
 * @param  array $a Array.
 * @return mixed
 */
function ap_meta_array_map( $a ) {
	return $a[0];
}

/**
 * Return the current page url.
 *
 * @param array $args Arguments.
 * @return string
 * @since 2.0.0
 */
function ap_current_page_url( $args ) {
	$base = rtrim( get_permalink(), '/' );
	if ( get_option( 'permalink_structure' ) !== '' ) {
		$link = $base . '/';
		if ( ! empty( $args ) ) {
			foreach ( $args as $k => $s ) {
				$link .= $k . '/' . $s . '/';
			}
		}
	} else {
		$link = add_query_arg( $args, $base );
	}

	return $link;
}

/**
 * Sort array by order value. Group array which have same order number and then sort them.
 *
 * @param array $array Array to order.
 * @return array
 * @since 2.0.0
 * @since 4.1.0 Use `WP_List_Util` class for sorting.
 */
function ap_sort_array_by_order( $array ) {
	$new_array = [];

	if ( ! empty( $array ) && is_array( $array ) ) {
		$i = 1;
		foreach ( $array as $k => $a ) {
			if ( is_array( $a ) ) {
				$array[ $k ]['order'] = isset( $a['order'] ) ? $a['order'] : $i;
			}

			$i += 2;
		}

		$util = new WP_List_Util( $array );
		return $util->sort( 'order', 'ASC', true );
	}
}

/**
 * Echo anspress links.
 *
 * @param string|array $sub Sub page.
 * @since 2.1
 */
function ap_link_to( $sub ) {
	echo ap_get_link_to( $sub ); // xss ok.
}

	/**
	 * Return link to AnsPress pages.
	 *
	 * @param string|array $sub Sub pages/s.
	 * @return string
	 */
function ap_get_link_to( $sub ) {
	$url = false;

	if ( 'ask' === $sub ) {
		$url = get_permalink( ap_opt( 'ask_page' ) );
	}

	if ( false === $url ) {
		/**
		 * Define default AnsPress page slugs.
		 *
		 * @var array
		 */
		$default_pages = array(
			'question' => ap_opt( 'question_page_slug' ),
			'users'    => ap_opt( 'users_page_slug' ),
			'user'     => ap_opt( 'user_page_slug' ),
		);

		$default_pages = apply_filters( 'ap_default_page_slugs', $default_pages );

		if ( is_array( $sub ) && isset( $sub['ap_page'] ) && isset( $default_pages[ $sub['ap_page'] ] ) ) {
			$sub['ap_page'] = $default_pages[ $sub['ap_page'] ];
		} elseif ( ! is_array( $sub ) && ! empty( $sub ) && isset( $default_pages[ $sub ] ) ) {
			$sub = $default_pages[ $sub ];
		}

		$base = rtrim( ap_base_page_link(), '/' );
		$args = '';

		if ( get_option( 'permalink_structure' ) !== '' ) {
			if ( ! is_array( $sub ) && 'base' !== $sub ) {
				$args = $sub ? '/' . $sub : '';
			} elseif ( is_array( $sub ) ) {
				$args = '/';

				if ( ! empty( $sub ) ) {
					foreach ( (array) $sub as $s ) {
						$args .= $s . '/';
					}
				}
			}

			$args = user_trailingslashit( rtrim( $args, '/' ) );
		} else {
			if ( ! is_array( $sub ) ) {
				$args = $sub ? '&ap_page=' . $sub : '';
			} elseif ( is_array( $sub ) ) {
				$args = '';

				if ( ! empty( $sub ) ) {
					foreach ( $sub as $k => $s ) {
						$args .= '&' . $k . '=' . $s;
					}
				}
			}
		}

		$url = $base . $args;
	} // End if().

	/**
	 * Allows filtering anspress links.
	 *
	 * @param string       $url Generated url.
	 * @param string|array $sub AnsPress sub pages.
	 *
	 * @since unknown
	 */
	return apply_filters( 'ap_link_to', $url, $sub );
}

/**
 * Return the total numbers of post.
 *
 * @param string         $post_type Post type.
 * @param boolean|string $ap_type ap_meta type.
 * @return array
 * @since  2.0.0
 * @TODO use new qameta table.
 */
function ap_total_posts_count( $post_type = 'question', $ap_type = false, $user_id = false ) {
	global $wpdb;

	if ( 'question' === $post_type ) {
		$type = "p.post_type = 'question'";
	} elseif ( 'answer' === $post_type ) {
		$type = "p.post_type = 'answer'";
	} else {
		$type = "(p.post_type = 'question' OR p.post_type = 'answer')";
	}

	$meta = '';
	$join = '';

	if ( 'flag' === $ap_type ) {
		$meta = 'AND qameta.flags > 0';
		$join = "INNER JOIN {$wpdb->ap_qameta} qameta ON p.ID = qameta.post_id";
	} elseif ( 'unanswered' === $ap_type ) {
		$meta = 'AND qameta.answers = 0';
		$join = "INNER JOIN {$wpdb->ap_qameta} qameta ON p.ID = qameta.post_id";
	} elseif ( 'best_answer' === $ap_type ) {
		$meta = 'AND qameta.selected > 0';
		$join = "INNER JOIN {$wpdb->ap_qameta} qameta ON p.ID = qameta.post_id";
	}

	$where = "WHERE p.post_status NOT IN ('trash', 'draft') AND $type $meta";

	if ( false !== $user_id && (int) $user_id > 0 ) {
		$where .= ' AND p.post_author = ' . (int) $user_id;
	}

	$where     = apply_filters( 'ap_total_posts_count', $where );
	$query     = "SELECT count(*) as count, p.post_status FROM $wpdb->posts p $join $where GROUP BY p.post_status";

	$count = $wpdb->get_results( $query, ARRAY_A ); // @codingStandardsIgnoreLine
	$counts = array();

	foreach ( (array) get_post_stati() as $state ) {
		$counts[ $state ] = 0;
	}

	$counts['total'] = 0;


	if ( ! empty( $count ) ) {
		foreach ( $count as $row ) {
			$counts[ $row['post_status'] ] = (int) $row['count'];
			$counts['total']              += (int) $row['count'];
		}
	}

	return (object) $counts;
}

/**
 * Return total numbers of published questions.
 *
 * @return integer
 */
function ap_total_published_questions() {
	$posts = ap_total_posts_count();
	return $posts->publish;
}

/**
 * Get total numbers of solved question.
 *
 * @param string $type Valid values are int or object.
 * @return int|object
 */
function ap_total_solved_questions( $type = 'int' ) {
	global $wpdb;
	$query     = "SELECT count(*) as count, p.post_status FROM $wpdb->posts p INNER JOIN $wpdb->ap_qameta qameta ON p.ID = qameta.post_id WHERE p.post_type = 'question' AND qameta.selected_id IS NOT NULL AND qameta.selected_id > 0 GROUP BY p.post_status";

	$count  = $wpdb->get_results( $query, ARRAY_A ); // unprepared SQL ok, db call ok.
	$counts = array( 'total' => 0 );

	foreach ( get_post_stati() as $state ) {
		$counts[ $state ] = 0;
	}

	foreach ( (array) $count as $row ) {
		$counts[ $row['post_status'] ] = (int) $row['count'];
		$counts['total']              += (int) $row['count'];
	}

	$counts = (object) $counts;
	if ( 'int' === $type ) {
		return $counts->publish + $counts->private_post;
	}

	return $counts;
}

/**
 * Get current sorting type.
 *
 * @return string
 * @since 2.1
 */
function ap_get_sort() {
	return ap_sanitize_unslash( 'ap_sort', 'p', null );
}

/**
 * Remove white space from string.
 *
 * @param string $contents String.
 * @return string
 */
function ap_trim_traling_space( $contents ) {
	$contents = preg_replace( '#(^(&nbsp;|\s)+|(&nbsp;|\s)+$)#', '', $contents );
	return $contents;
}

/**
 * Replace square brackets in a string.
 *
 * @param string $contents String.
 */
function ap_replace_square_bracket( $contents ) {
	$contents = str_replace( '[', '&#91;', $contents );
	$contents = str_replace( ']', '&#93;', $contents );
	return $contents;
}

/**
 * Create base page for AnsPress.
 *
 * This function is called in plugin activation. This function checks if base page already exists,
 * if not then it create a new one and update the option.
 *
 * @see anspress_activate
 * @since 2.3
 * @since 4.1.0 Creates all other AnsPress pages if not exists.
 */
function ap_create_base_page() {
	$opt = ap_opt();

	$pages = ap_main_pages();

	foreach ( $pages as $slug => $page ) {
		// Check if page already exists.
		$_post = get_page( ap_opt( $slug ) );

		if ( ! $_post || 'trash' === $_post->post_status ) {
			$args = wp_parse_args(
				$page, array(
					'post_type'      => 'page',
					'post_content'   => '[anspress]',
					'post_status'    => 'publish',
					'comment_status' => 'closed',
				)
			);

			if ( 'base_page' !== $slug ) {
				$args['post_parent'] = ap_opt( 'base_page' );
			}

			// Now create post.
			$new_page_id = wp_insert_post( $args );

			if ( $new_page_id ) {
				$page = get_page( $new_page_id );

				ap_opt( $slug, $page->ID );
				ap_opt( $slug . '_id', $page->post_name );
			}
		}
	} // End foreach().
}

/**
 * Return question title with solved prefix if answer is accepted.
 *
 * @param boolean|integer $question_id Question ID.
 * @return string
 *
 * @since   2.3 @see `ap_page_title`
 */
function ap_question_title_with_solved_prefix( $question_id = false ) {
	if ( false === $question_id ) {
		$question_id = get_question_id();
	}

	$solved = ap_have_answer_selected( $question_id );

	if ( ap_opt( 'show_solved_prefix' ) ) {
		return get_the_title( $question_id ) . ' ' . ( $solved ? __( '[Solved] ', 'anspress-question-answer' ) : '' );
	}

	return get_the_title( $question_id );
}

/**
 * Verify the __nonce field.
 *
 * @param string $action Action.
 * @return bool
 * @since  2.4
 */
function ap_verify_nonce( $action ) {
	return wp_verify_nonce( ap_sanitize_unslash( '__nonce', 'p' ), $action );
}

/**
 * Verify default ajax nonce field.
 *
 * @return boolean
 */
function ap_verify_default_nonce() {
	$nonce_name = isset( $_REQUEST['ap_ajax_nonce'] ) ? 'ap_ajax_nonce' : '__nonce'; // input var okay.

	if ( ! isset( $_REQUEST[ $nonce_name ] ) ) { // input var okay.
		return false;
	}

	return wp_verify_nonce( ap_sanitize_unslash( $nonce_name, 'p' ), 'ap_ajax_nonce' );
}

/**
 * Parse search string to array.
 *
 * @param  string $str search string.
 * @return array
 */
function ap_parse_search_string( $str ) {
	$output = array();

	// Split by space.
	$bits = explode( ' ', $str );

	// Process pairs.
	foreach ( $bits as $id => $pair ) {
		// Split the pair.
		$pair_bits = explode( ':', $pair );

		// This was actually a pair.
		if ( count( $pair_bits ) === 2 ) {

			$values    = explode( ',', $pair_bits[1] );
			$sanitized = array();

			if ( is_array( $values ) && ! empty( $values ) ) {
				foreach ( $values as $value ) {
					if ( ! empty( $value ) ) {
						$sanitized[] = sanitize_text_field( $value );
					}
				}
			}

			if ( count( $sanitized ) > 0 ) {
				// Use left part of pair as index and push right part to array.
				if ( ! empty( $pair_bits[0] ) ) {
					$output[ sanitize_text_field( $pair_bits[0] ) ] = $sanitized;
				}
			}

			// Remove this pair from $bits.
			unset( $bits[ $id ] );
		} // Not a pair, presumably reached the query.
		else {

			// Exit the loop.
			break;
		}
	}

	// Rebuild query with remains of $bits.
	$output['q'] = sanitize_text_field( implode( ' ', $bits ) );

	return $output;
}

/**
 * Send properly formatted AnsPress json string.
 *
 * @param  array|string $response Response array or string.
 */
function ap_ajax_json( $response ) {
	ap_send_json( ap_ajax_responce( $response ) );
}

/**
 * Check if object is profile menu item.
 *
 * @param  object $menu Menu Object.
 * @return boolean
 */
function ap_is_profile_menu( $menu ) {
	return in_array( 'anspress-page-profile', $menu->classes, true );
}

/**
 * Get the IDs of answer by question ID.
 *
 * @param  integer $question_id Question post ID.
 * @return object
 * @since  2.4
 */
function ap_questions_answer_ids( $question_id ) {
	global $wpdb;

	$ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = 'answer' AND post_parent=%d", $question_id ) ); // db call ok.

	return $ids;
}

/**
 * Whitelist array items.
 *
 * @param  array $master_keys Master keys.
 * @param  array $array       Array to filter.
 * @return array
 */
function ap_whitelist_array( $master_keys, $array ) {
	return array_intersect_key( $array, array_flip( $master_keys ) );
}

/**
 * Append table name in $wpdb.
 *
 * @since unknown
 * @since 4.1.2 Added `ap_activity` table.
 */
function ap_append_table_names() {
	global $wpdb;

	$wpdb->ap_qameta      = $wpdb->prefix . 'ap_qameta';
	$wpdb->ap_votes       = $wpdb->prefix . 'ap_votes';
	$wpdb->ap_views       = $wpdb->prefix . 'ap_views';
	$wpdb->ap_reputations = $wpdb->prefix . 'ap_reputations';
	$wpdb->ap_subscribers = $wpdb->prefix . 'ap_subscribers';
	$wpdb->ap_activity    = $wpdb->prefix . 'ap_activity';

}
ap_append_table_names();


/**
 * Check if $_REQUEST var exists and get value. If not return default.
 *
 * @param  string $var     Variable name.
 * @param  mixed  $default Default value.
 * @return mixed
 * @since  3.0.0
 */
function ap_isset_post_value( $var, $default = '' ) {
	if ( isset( $_REQUEST[ $var ] ) ) { // input var okay.
		return wp_unslash( $_REQUEST[ $var ] ); // input var okay, xss ok, sanitization ok.
	}

	return $default;
}

/**
 * Get active list filter by filter key.
 *
 * @param  string|null $filter  Filter key.
 * @return false|string|array
 * @since  4.0.0
 */
function ap_get_current_list_filters( $filter = null ) {
	$get_filters = [];
	$filters     = array_keys( ap_get_list_filters() );

	if ( in_array( 'order_by', $filters, true ) ) {
		$get_filters['order_by'] = ap_opt( 'question_order_by' );
	}

	if ( empty( $filters ) || ! is_array( $filters ) ) {
		$filters = [];
	}

	foreach ( (array) $filters as $k ) {
		$val = ap_isset_post_value( $k );

		if ( ! empty( $val ) ) {
			$get_filters[ $k ] = $val;
		}
	}

	if ( null !== $filter ) {
		return ! isset( $get_filters[ $filter ] ) ? null : $get_filters[ $filter ];
	}

	return $get_filters;
}

/**
 * Sanitize and unslash string or array or post/get value at the same time.
 *
 * @param  string|array   $str    String or array to sanitize. Or post/get key name.
 * @param  boolean|string $from   Get value from `$_REQUEST` or `query_var`. Valid values: request, query_var.
 * @param  mixed          $default   Default value if variable not found.
 * @return array|string
 * @since  3.0.0
 */
function ap_sanitize_unslash( $str, $from = false, $default = '' ) {
	// If not false then get from $_REQUEST or query_var.
	if ( false !== $from ) {
		if ( in_array( strtolower( $from ), [ 'request', 'post', 'get', 'p', 'g', 'r' ], true ) ) {
			$str = ap_isset_post_value( $str, $default );
		} elseif ( 'query_var' === $from ) {
			$str = get_query_var( $str );
		}
	}

	// Return default if empty.
	if ( empty( $str ) ) {
		return $default;
	}

	if ( is_array( $str ) ) {
		$str = wp_unslash( $str );
		return array_map( 'sanitize_text_field', $str );
	}

	return sanitize_text_field( wp_unslash( $str ) );
}

/**
 * Return post status based on AnsPress options.
 *
 * @param  boolean|integer $user_id    ID of user creating question.
 * @param  string          $post_type  Post type, question or answer.
 * @param  boolean         $edit       Is editing post.
 * @return string
 * @since  3.0.0
 */
function ap_new_edit_post_status( $user_id = false, $post_type = 'question', $edit = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$new_edit   = $edit ? 'edit' : 'new';
	$option_key = $new_edit . '_' . $post_type . '_status';
	$status     = 'publish';

	// If super admin or user have no_moderation cap.
	if ( is_super_admin( $user_id ) || user_can( $user_id, 'ap_no_moderation' ) ) {
		return $status;
	}

	if ( ap_opt( $option_key ) === 'moderate' && ! ( user_can( $user_id, 'ap_moderator' ) || is_super_admin( $user_id ) ) ) {
		$status = 'moderate';
	}

	// If anonymous post status is set to moderate.
	if ( empty( $user_id ) && ap_opt( 'anonymous_post_status' ) === 'moderate' ) {
		$status = 'moderate';
	}

	return $status;
}

/**
 * Find duplicate post by content.
 *
 * @param  string        $content   Post content.
 * @param  string        $post_type Post type.
 * @param  integer|false $question_id Question ID.
 * @return boolean|false
 * @since  3.0.0
 * @since  4.1.0 Removed option check. Removed `ap_sanitize_description_field` sanitization.
 */
function ap_find_duplicate_post( $content, $post_type = 'question', $question_id = false ) {
	global $wpdb;

	// Return if content is empty. But blank content will be checked.
	if ( empty( $content ) ) {
		return false;
	}

	$question_q = false !== $question_id ? $wpdb->prepare( ' AND post_parent= %d', $question_id ) : '';

	$var = (int) $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_content = %s AND post_type = %s {$question_q} LIMIT 1", $content, $post_type ) ); // @codingStandardsIgnoreLine

	if ( $var > 0 ) {
		return $var;
	}

	return false;
}
/**
 * Check if question suggestion is disabled.
 *
 * @return boolean
 * @since  3.0.0
 */
function ap_disable_question_suggestion() {
	/**
	 * Modify ap_disable_question_suggestion.
	 *
	 * @param boolean $enable Default is false.
	 * @since  3.0.0
	 */
	return (bool) apply_filters( 'ap_disable_question_suggestion', false );
}

/**
 * Pre fetch users and update cache.
 *
 * @param  array $ids User ids.
 * @since 4.0.0
 */
function ap_post_author_pre_fetch( $ids ) {
	$users = get_users(
		[
			'include' => $ids,
			'fields'  => array( 'ID', 'user_login', 'user_nicename', 'user_email', 'display_name' ),
		]
	);

	foreach ( (array) $users as $user ) {
		update_user_caches( $user );
	}

	update_meta_cache( 'user', $ids );
}


/**
 * Activity type to human readable title.
 *
 * @param  string $type Activity type.
 * @return string
 */
function ap_activity_short_title( $type ) {
	$title = array(
		'new_question'           => __( 'asked', 'anspress-question-answer' ),
		'approved_question'      => __( 'approved', 'anspress-question-answer' ),
		'approved_answer'        => __( 'approved', 'anspress-question-answer' ),
		'new_answer'             => __( 'answered', 'anspress-question-answer' ),
		'delete_answer'          => __( 'deleted answer', 'anspress-question-answer' ),
		'restore_question'       => __( 'restored question', 'anspress-question-answer' ),
		'restore_answer'         => __( 'restored answer', 'anspress-question-answer' ),
		'new_comment'            => __( 'commented', 'anspress-question-answer' ),
		'delete_comment'         => __( 'deleted comment', 'anspress-question-answer' ),
		'new_comment_answer'     => __( 'commented on answer', 'anspress-question-answer' ),
		'edit_question'          => __( 'edited question', 'anspress-question-answer' ),
		'edit_answer'            => __( 'edited answer', 'anspress-question-answer' ),
		'edit_comment'           => __( 'edited comment', 'anspress-question-answer' ),
		'edit_comment_answer'    => __( 'edited comment on answer', 'anspress-question-answer' ),
		'answer_selected'        => __( 'selected answer', 'anspress-question-answer' ),
		'answer_unselected'      => __( 'unselected answer', 'anspress-question-answer' ),
		'status_updated'         => __( 'updated status', 'anspress-question-answer' ),
		'best_answer'            => __( 'selected as best answer', 'anspress-question-answer' ),
		'unselected_best_answer' => __( 'unselected as best answer', 'anspress-question-answer' ),
		'changed_status'         => __( 'changed status', 'anspress-question-answer' ),
	);

	$title = apply_filters( 'ap_activity_short_title', $title );

	if ( isset( $title[ $type ] ) ) {
		return $title[ $type ];
	}

	return $type;
}

/**
 * Return canonical URL of current page.
 *
 * @return string
 * @since  3.0.0
 */
function ap_canonical_url() {
	$canonical_url = ap_get_link_to( get_query_var( 'ap_page' ) );

	if ( is_question() ) {
		$canonical_url = get_permalink( get_question_id() );
	}

	/**
	 * Filter AnsPress canonical URL.
	 *
	 * @param string $canonical_url Current URL.
	 * @return string
	 * @since  3.0.0
	 */
	$canonical_url = apply_filters( 'ap_canonical_url', $canonical_url );

	return esc_url( $canonical_url );
}

/**
 * Return or echo user display name.
 *
 * Get display name from comments if WP_Comment object is passed. Else
 * fetch name form user profile. If anonymous user then fetch name from
 * current question, answer or comment.
 *
 * @param  WP_Comment|array|integer $args {
 *      Arguments or `WP_Comment` or user ID.
 *
 *      @type integer $user_id User ID.
 *      @type boolean $html    Shall return just text name or name with html markup.
 *      @type boolean $echo    Return or echo.
 *      @type string  $anonymous_label A placeholder name for anonymous user if no name found in post or comment.
 * }
 *
 * @return string|void If `$echo` argument is tru then it will echo name.
 * @since 0.1
 * @since 4.1.2 Improved args and PHPDoc.
 */
function ap_user_display_name( $args = [] ) {
	global $post;

	$defaults = array(
		'user_id'         => get_the_author_meta( 'ID' ),
		'html'            => false,
		'echo'            => false,
		'anonymous_label' => __( 'Anonymous', 'anspress-question-answer' ),
	);

	// When only user id passed.
	if ( is_numeric( $args ) ) {
		$defaults['user_id'] = $args;
		$args                = $defaults;
	} elseif ( $args instanceof WP_Comment ) {
		$defaults['user_id']         = $args->user_id;
		$defaults['anonymous_label'] = $args->comment_author;
		$args                        = $defaults;
	} else {
		$args = wp_parse_args( $args, $defaults );
	}

	extract( $args ); // @codingStandardsIgnoreLine

	$user = get_userdata( $user_id );

	if ( $user ) {
		$return = ! $html ? $user->display_name : '<a href="' . ap_user_link( $user_id ) . '" itemprop="url"><span itemprop="name">' . $user->display_name . '</span></a>';
	} elseif ( $post && in_array( $post->post_type, [ 'question', 'answer' ], true ) ) {
		$post_fields = ap_get_post_field( 'fields' );

		if ( ! $html ) {
			if ( is_array( $post_fields ) && ! empty( $post_fields['anonymous_name'] ) ) {
				$return = $post_fields['anonymous_name'];
			} else {
				$return = $anonymous_label;
			}
		} else {
			if ( is_array( $post_fields ) && ! empty( $post_fields['anonymous_name'] ) ) {
				$return = $post_fields['anonymous_name'] . __( ' (anonymous)', 'anspress-question-answer' );
			} else {
				$return = $anonymous_label;
			}
		}
	} else {
		if ( ! $html ) {
			$return = $anonymous_label;
		} else {
			$return = $anonymous_label;
		}
	}

	/**
	 * Filter AnsPress user display name.
	 *
	 * Filter can be used to alter user display name or
	 * appending some extra information of user, like: rank, reputation etc.
	 * Make sure to return plain text when `$args['html']` is true.
	 *
	 * @param string $return Name of user to return.
	 * @param array  $args   Arguments.
	 *
	 * @since 2.0.1
	 */
	$return = apply_filters( 'ap_user_display_name', $return, $args );

	if ( ! $args['echo'] ) {
		return $return;
	}

	echo $return; // xss okay.
}

/**
 * Return Link to user pages.
 *
 * @param  boolean|integer $user_id    user id.
 * @param  string|array    $sub        page slug.
 * @return string
 * @since  unknown
 * @since  4.1.1 Profile link not linking to BuddyPress when active.
 * @since  4.1.2 User user nicename in url as author_name query var gets user by nicename.
 */
function ap_user_link( $user_id = false, $sub = false ) {
	$link = '';

	if ( false === $user_id ) {
		$user_id = get_the_author_meta( 'ID' );
	}

	if ( empty( $user_id ) && is_author() ) {
		$user_id = get_queried_object_id();
	}

	if ( $user_id < 1 && empty( $user_id ) ) {
		$link = '#/user/anonymous';
	} else {
		$user = get_user_by( 'id', $user_id );

		if ( ! $user ) {
			$link = '#/user/anonymous';
		} elseif ( function_exists( 'bp_core_get_userlink' ) ) {
			$link = bp_core_get_userlink( $user_id, false, true );
		} elseif ( ap_is_addon_active( 'profile.php' ) ) {
			$slug = get_option( 'ap_user_path' );
			$link = home_url( $slug ) . '/' . $user->user_nicename . '/';
		} else {
			$link = get_author_posts_url( $user_id );
		}
	}

	// Append sub.
	if ( ! empty( $sub ) ) {
		if ( is_array( $sub ) ) {
			$link = rtrim( $link, '/' ) . implode( '/', $sub ) . '/';
		} else {
			$link = $link . rtrim( $sub, '/' ) . '/';
		}
	}

	$link = user_trailingslashit( $link );

	return apply_filters( 'ap_user_link', $link, $user_id, $sub );
}

/**
 * Return current page in user profile.
 *
 * @since 2.0.1
 * @return string
 * @since 2.4.7 Added new filter `ap_active_user_page`.
 */
function ap_active_user_page() {
	$user_page = sanitize_text_field( get_query_var( 'user_page' ) );

	if ( ! empty( $user_page ) ) {
		return $user_page;
	}

	$page = 'about';

	return apply_filters( 'ap_active_user_page', $page );
}

/**
 * User name and link with anchor tag.
 *
 * @param string  $user_id User ID.
 * @param boolean $echo Echo or return.
 */
function ap_user_link_anchor( $user_id, $echo = true ) {

	$name = ap_user_display_name( $user_id );

	if ( $user_id < 1 ) {
		if ( $echo ) {
			echo $name; // xss okay.
		} else {
			return $name;
		}
	}

	$html  = '<a href="' . ap_user_link( $user_id ) . '">';
	$html .= $name;
	$html .= '</a>';

	if ( $echo ) {
		echo $html; // xss okay.
	}

	return $html;
}

/**
 * Remove stop words from a string.
 *
 * @param  string $str String from need to be filtered.
 * @return string
 */
function ap_remove_stop_words( $str ) {
	// EEEEEEK Stop words.
	$common_words = array( 'a', 'able', 'about', 'above', 'abroad', 'according', 'accordingly', 'across', 'actually', 'adj', 'after', 'afterwards', 'again', 'against', 'ago', 'ahead', 'ain\'t', 'all', 'allow', 'allows', 'almost', 'alone', 'along', 'alongside', 'already', 'also', 'although', 'always', 'am', 'amid', 'amidst', 'among', 'amongst', 'an', 'and', 'another', 'any', 'anybody', 'anyhow', 'anyone', 'anything', 'anyway', 'anyways', 'anywhere', 'apart', 'appear', 'appreciate', 'appropriate', 'are', 'aren\'t', 'around', 'as', 'a\'s', 'aside', 'ask', 'asking', 'associated', 'at', 'available', 'away', 'awfully', 'b', 'back', 'backward', 'backwards', 'be', 'became', 'because', 'become', 'becomes', 'becoming', 'been', 'before', 'beforehand', 'begin', 'behind', 'being', 'believe', 'below', 'beside', 'besides', 'best', 'better', 'between', 'beyond', 'both', 'brief', 'but', 'by', 'c', 'came', 'can', 'cannot', 'cant', 'can\'t', 'caption', 'cause', 'causes', 'certain', 'certainly', 'changes', 'clearly', 'c\'mon', 'co', 'co.', 'com', 'come', 'comes', 'concerning', 'consequently', 'consider', 'considering', 'contain', 'containing', 'contains', 'corresponding', 'could', 'couldn\'t', 'course', 'c\'s', 'currently', 'd', 'dare', 'daren\'t', 'definitely', 'described', 'despite', 'did', 'didn\'t', 'different', 'directly', 'do', 'does', 'doesn\'t', 'doing', 'done', 'don\'t', 'down', 'downwards', 'during', 'e', 'each', 'edu', 'eg', 'eight', 'eighty', 'either', 'else', 'elsewhere', 'end', 'ending', 'enough', 'entirely', 'especially', 'et', 'etc', 'even', 'ever', 'evermore', 'every', 'everybody', 'everyone', 'everything', 'everywhere', 'ex', 'exactly', 'example', 'except', 'f', 'fairly', 'far', 'farther', 'few', 'fewer', 'fifth', 'first', 'five', 'followed', 'following', 'follows', 'for', 'forever', 'former', 'formerly', 'forth', 'forward', 'found', 'four', 'from', 'further', 'furthermore', 'g', 'get', 'gets', 'getting', 'given', 'gives', 'go', 'goes', 'going', 'gone', 'got', 'gotten', 'greetings', 'h', 'had', 'hadn\'t', 'half', 'happens', 'hardly', 'has', 'hasn\'t', 'have', 'haven\'t', 'having', 'he', 'he\'d', 'he\'ll', 'hello', 'help', 'hence', 'her', 'here', 'hereafter', 'hereby', 'herein', 'here\'s', 'hereupon', 'hers', 'herself', 'he\'s', 'hi', 'him', 'himself', 'his', 'hither', 'hopefully', 'how', 'howbeit', 'however', 'hundred', 'i', 'i\'d', 'ie', 'if', 'ignored', 'i\'ll', 'i\'m', 'immediate', 'in', 'inasmuch', 'inc', 'inc.', 'indeed', 'indicate', 'indicated', 'indicates', 'inner', 'inside', 'insofar', 'instead', 'into', 'inward', 'is', 'isn\'t', 'it', 'it\'d', 'it\'ll', 'its', 'it\'s', 'itself', 'i\'ve', 'j', 'just', 'k', 'keep', 'keeps', 'kept', 'know', 'known', 'knows', 'l', 'last', 'lately', 'later', 'latter', 'latterly', 'least', 'less', 'lest', 'let', 'let\'s', 'like', 'liked', 'likely', 'likewise', 'little', 'look', 'looking', 'looks', 'low', 'lower', 'ltd', 'm', 'made', 'mainly', 'make', 'makes', 'many', 'may', 'maybe', 'mayn\'t', 'me', 'mean', 'meantime', 'meanwhile', 'merely', 'might', 'mightn\'t', 'mine', 'minus', 'miss', 'more', 'moreover', 'most', 'mostly', 'mr', 'mrs', 'much', 'must', 'mustn\'t', 'my', 'myself', 'n', 'name', 'namely', 'nd', 'near', 'nearly', 'necessary', 'need', 'needn\'t', 'needs', 'neither', 'never', 'neverf', 'neverless', 'nevertheless', 'new', 'next', 'nine', 'ninety', 'no', 'nobody', 'non', 'none', 'nonetheless', 'noone', 'no-one', 'nor', 'normally', 'not', 'nothing', 'notwithstanding', 'novel', 'now', 'nowhere', 'o', 'obviously', 'of', 'off', 'often', 'oh', 'ok', 'okay', 'old', 'on', 'once', 'one', 'ones', 'one\'s', 'only', 'onto', 'opposite', 'or', 'other', 'others', 'otherwise', 'ought', 'oughtn\'t', 'our', 'ours', 'ourselves', 'out', 'outside', 'over', 'overall', 'own', 'p', 'particular', 'particularly', 'past', 'per', 'perhaps', 'placed', 'please', 'plus', 'possible', 'presumably', 'probably', 'provided', 'provides', 'q', 'que', 'quite', 'qv', 'r', 'rather', 'rd', 're', 'really', 'reasonably', 'recent', 'recently', 'regarding', 'regardless', 'regards', 'relatively', 'respectively', 'right', 'round', 's', 'said', 'same', 'saw', 'say', 'saying', 'says', 'second', 'secondly', 'see', 'seeing', 'seem', 'seemed', 'seeming', 'seems', 'seen', 'self', 'selves', 'sensible', 'sent', 'serious', 'seriously', 'seven', 'several', 'shall', 'shan\'t', 'she', 'she\'d', 'she\'ll', 'she\'s', 'should', 'shouldn\'t', 'since', 'six', 'so', 'some', 'somebody', 'someday', 'somehow', 'someone', 'something', 'sometime', 'sometimes', 'somewhat', 'somewhere', 'soon', 'sorry', 'specified', 'specify', 'specifying', 'still', 'sub', 'such', 'sup', 'sure', 't', 'take', 'taken', 'taking', 'tell', 'tends', 'th', 'than', 'thank', 'thanks', 'thanx', 'that', 'that\'ll', 'thats', 'that\'s', 'that\'ve', 'the', 'their', 'theirs', 'them', 'themselves', 'then', 'thence', 'there', 'thereafter', 'thereby', 'there\'d', 'therefore', 'therein', 'there\'ll', 'there\'re', 'theres', 'there\'s', 'thereupon', 'there\'ve', 'these', 'they', 'they\'d', 'they\'ll', 'they\'re', 'they\'ve', 'thing', 'things', 'think', 'third', 'thirty', 'this', 'thorough', 'thoroughly', 'those', 'though', 'three', 'through', 'throughout', 'thru', 'thus', 'till', 'to', 'together', 'too', 'took', 'toward', 'towards', 'tried', 'tries', 'truly', 'try', 'trying', 't\'s', 'twice', 'two', 'u', 'un', 'under', 'underneath', 'undoing', 'unfortunately', 'unless', 'unlike', 'unlikely', 'until', 'unto', 'up', 'upon', 'upwards', 'us', 'use', 'used', 'useful', 'uses', 'using', 'usually', 'v', 'value', 'various', 'versus', 'very', 'via', 'viz', 'vs', 'w', 'want', 'wants', 'was', 'wasn\'t', 'way', 'we', 'we\'d', 'welcome', 'well', 'we\'ll', 'went', 'were', 'we\'re', 'weren\'t', 'we\'ve', 'what', 'whatever', 'what\'ll', 'what\'s', 'what\'ve', 'when', 'whence', 'whenever', 'where', 'whereafter', 'whereas', 'whereby', 'wherein', 'where\'s', 'whereupon', 'wherever', 'whether', 'which', 'whichever', 'while', 'whilst', 'whither', 'who', 'who\'d', 'whoever', 'whole', 'who\'ll', 'whom', 'whomever', 'who\'s', 'whose', 'why', 'will', 'willing', 'wish', 'with', 'within', 'without', 'wonder', 'won\'t', 'would', 'wouldn\'t', 'x', 'y', 'yes', 'yet', 'you', 'you\'d', 'you\'ll', 'your', 'you\'re', 'yours', 'yourself', 'yourselves', 'you\'ve', 'z', 'zero' );

	return preg_replace( '/\b(' . implode( '|', $common_words ) . ')\b/', '', $str );
}

/**
 * Search array by key and value.
 *
 * @param  array  $array Array to search.
 * @param  string $key   Array key to search.
 * @param  mixed  $value Value of key supplied.
 * @return array
 * @since  4.0.0
 */
function ap_search_array( $array, $key, $value ) {
	$results = array();

	if ( is_array( $array ) ) {
		if ( isset( $array[ $key ] ) && $array[ $key ] == $value ) {
			$results[] = $array;
		}

		foreach ( $array as $subarray ) {
			$results = array_merge( $results, ap_search_array( $subarray, $key, $value ) );
		}
	}

	return $results;
}

/**
 * Get all AnsPress add-ons data.
 *
 * @return array
 * @since 4.0.0
 * @since 4.1.8 Do not fetch addon meta data from files, instead defined it in array.
 */
function ap_get_addons() {
	$cache  = wp_cache_get( 'addons', 'anspress' );
	$option = get_option( 'anspress_addons', [] );

	if ( false !== $cache ) {
		return $cache;
	}

	$all_files = [];
	foreach ( [ 'pro', 'free' ] as $folder ) {
		$path = ANSPRESS_ADDONS_DIR . DS . $folder;

		if ( file_exists( $path ) ) {
			$files = scandir( $path );

			foreach ( $files as $file ) {
				$ext = pathinfo( $file, PATHINFO_EXTENSION );

				if ( 'php' === $ext ) {
					$all_files[] = $folder . DS . $file;
				}
			}
		}
	}

	$addons = array(
		'email.php'             => array(
			'name'        => __( 'Emails', 'anspress-question-answer' ),
			'description' => __( 'Notifies users and admins by email for various events and activities.', 'anspress-question-answer' ),
		),
		'notifications.php'     => array(
			'name'        => __( 'Notifications', 'anspress-question-answer' ),
			'description' => __( 'Adds a fancy user notification dropdown like Facebook and Stackoverflow.', 'anspress-question-answer' ),
		),
		'categories.php'        => array(
			'name'        => __( 'Categories', 'anspress-question-answer' ),
			'description' => __( 'Add category support in AnsPress questions.', 'anspress-question-answer' ),
		),
		'tags.php'              => array(
			'name'        => __( 'Tags', 'anspress-question-answer' ),
			'description' => __( 'Add tag support in AnsPress questions.', 'anspress-question-answer' ),
		),
		'reputation.php'        => array(
			'name'        => __( 'Reputation', 'anspress-question-answer' ),
			'description' => __( 'Award points to user based on activities.', 'anspress-question-answer' ),
		),
		'avatar.php'            => array(
			'name'        => __( 'Dynamic Avatar', 'anspress-question-answer' ),
			'description' => __( 'Generate user avatar based on display name initials.', 'anspress-question-answer' ),
		),
		'buddypress.php'        => array(
			'name'        => __( 'BuddyPress', 'anspress-question-answer' ),
			'description' => __( 'Integrate AnsPress with BuddyPress.', 'anspress-question-answer' ),
		),
		'profile.php'           => array(
			'name'        => __( 'User Profile', 'anspress-question-answer' ),
			'description' => __( 'User profile for users.', 'anspress-question-answer' ),
		),
		'recaptcha.php'         => array(
			'name'        => __( 'reCaptcha', 'anspress-question-answer' ),
			'description' => __( 'Add reCaptcha verification in question, answer and comment forms.', 'anspress-question-answer' ),
		),
		'syntaxhighlighter.php' => array(
			'name'        => __( 'Syntax Highlighter', 'anspress-question-answer' ),
			'description' => __( 'Add syntax highlighter support.', 'anspress-question-answer' ),
		),
		'akismet.php' => array(
			'name'        => __( 'Akismet Check', 'anspress-question-answer' ),
			'description' => __( 'Check for spam in post content.', 'anspress-question-answer' ),
		),
	);

	/**
	 * This hooks can be used to filter existing addons or for adding new addons.
	 *
	 * @since 4.1.8
	 */
	$addons = apply_filters( 'ap_addons', $addons );

	$valid_addons = [];
	foreach ( (array) $addons as $k => $addon ) {
		$path  = ANSPRESS_ADDONS_DIR . DS . basename( $k, '.php' ) . DS . $k;
		$path2 = ANSPRESS_ADDONS_DIR . DS . $k;

		$addons[ $k ]['path'] = '';

		if ( isset( $addon['path'] ) && file_exists( $addon['path'] ) ) {
			$addons[ $k ]['path'] = wp_normalize_path( $addon['path'] );
		} elseif ( file_exists( $path ) ) {
			$addons[ $k ]['path'] = wp_normalize_path( $path );
		} elseif ( file_exists( $path2 ) ) {
			$addons[ $k ]['path'] = wp_normalize_path( $path2 );
		}

		$addons[ $k ]['pro']    = isset( $addon['pro'] ) ? $addon['pro'] : false;
		$addons[ $k ]['active'] = isset( $option[ $k ] ) ? true : false;
		$addons[ $k ]['id']     = $k;
		$addons[ $k ]['class']  = sanitize_html_class( sanitize_title( str_replace( [ '/', '.php' ], [ '-', '' ], 'addon-' . $k ) ) );

		if ( ! empty( $addons[ $k ]['path'] ) && file_exists( $addons[ $k ]['path'] ) ) {
			$valid_addons[ $k ] = $addons[ $k ];
		}
	}

	wp_cache_set( 'addons', $valid_addons, 'anspress' );
	return $valid_addons;
}

/**
 * Return all active addons.
 *
 * @return array
 * @since 4.0.0
 */
function ap_get_active_addons() {
	$active_addons = [];

	foreach ( ap_get_addons() as $addon ) {
		if ( $addon['active'] ) {
			$active_addons[ $addon['id'] ] = $addon;
		}
	}

	return $active_addons;
}

/**
 * Return a single addon by file path.
 *
 * @param string $file Main file name of addon.
 * @return array
 * @since 4.1.0
 */
function ap_get_addon( $file ) {
	$search = false;

	foreach ( ap_get_addons() as $f => $addon ) {
		if ( $f === $file ) {
			$search = $addon;
			break;
		}
	}

	return $search;
}

/**
 * Activate an addon and trigger addon activation hook.
 *
 * @param string $addon_name Addon file name.
 * @return boolean
 *
 * @since 4.1.8 Fixed fatal error if addon does not exists.
 */
function ap_activate_addon( $addon_name ) {
	if ( ap_is_addon_active( $addon_name ) ) {
		return false;
	}

	global $ap_addons_activation;

	$opt        = get_option( 'anspress_addons', [] );
	$all_addons = ap_get_addons();
	$addon_name = wp_normalize_path( $addon_name );

	if ( isset( $all_addons[ $addon_name ] ) ) {
		$opt[ $addon_name ] = true;
		update_option( 'anspress_addons', $opt );

		$file = $all_addons[ $addon_name ]['path'];

		// Check file exists before requiring.
		if ( ! file_exists( $file ) ) {
			return false;
		}

		require_once $file;

		if ( isset( $ap_addons_activation[ $addon_name ] ) ) {
			call_user_func( $ap_addons_activation[ $addon_name ] );
		}

		do_action( 'ap_addon_activated', $addon_name );

		// Fix to drop wpengine cache.
		if ( class_exists( 'WpeCommon' ) ) {
			WpeCommon::purge_memcached();
			WpeCommon::clear_maxcdn_cache();
			WpeCommon::purge_varnish_cache();
		}

		// Delete cache.
		wp_cache_delete( 'addons', 'anspress' );

		// Flush rewrite rules.
		ap_opt( 'ap_flush', 'true' );

		return true;
	}

	return false;
}

/**
 * Deactivate addons.
 *
 * @param string $addon_name Addons file name.
 * @return boolean
 */
function ap_deactivate_addon( $addon_name ) {
	if ( ! ap_is_addon_active( $addon_name ) ) {
		return false;
	}

	$opt        = get_option( 'anspress_addons', [] );
	$all_addons = ap_get_addons();
	$addon_name = wp_normalize_path( $addon_name );

	if ( isset( $all_addons[ $addon_name ] ) ) {
		unset( $opt[ $addon_name ] );
		update_option( 'anspress_addons', $opt );
		do_action( 'ap_addon_deactivated', $addon_name );

		// Delete cache.
		wp_cache_delete( 'addons', 'anspress' );

		return true;
	}

	return false;
}

/**
 * Check if addon is active.
 *
 * @param string $addon Addon file name without path.
 * @return boolean
 * @since 4.0.0
 */
function ap_is_addon_active( $addon ) {
	$addons = ap_get_active_addons();

	if ( isset( $addons[ $addon ] ) ) {
		return true;
	}

	return false;
}

/**
 * Get addon image.
 *
 * @param string $file Addon main file name. Example: `avatar.php` or `category.php`.
 * @return void|string Return image if exists else null.
 * @since 4.1.8
 */
function ap_get_addon_image( $file ) {
	$addon = ap_get_addon( $file );

	if ( isset( $addon['path'] ) ) {
		$path_parts = pathinfo( $addon['path'] );
		if ( isset( $path_parts['dirname'] ) && file_exists( $path_parts['dirname'] . '/image.png' ) ) {
			return plugin_dir_url( $addon['path'] ) . '/image.png';
		}
	}
}

/**
 * Check if addon has options.
 *
 * @param string $file Addon main file.
 * @return boolean
 * @since 4.1.8
 */
function ap_addon_has_options( $file ) {
	$addon = ap_get_addon( $file );

	$form_name = str_replace( '.php', '', $addon['id'] );
	$form_name = str_replace( '/', '_', $form_name );

	if ( anspress()->form_exists( 'addon-' . $form_name ) ) {
		return true;
	}

	return false;
}

/**
 * Trigger question and answer update hooks.
 *
 * @param object $_post Post object.
 * @param string $event Event name.
 * @since 4.0.0
 */
function ap_trigger_qa_update_hook( $_post, $event ) {
	$_post = ap_get_post( $_post );

	// Check if post type is question or answer.
	if ( ! in_array( $_post->post_type, [ 'question', 'answer' ], true ) ) {
		return;
	}

	/**
		* Triggered right after updating question/answer.
		*
		* @param object $_post      Inserted post object.
		* @since 0.9
		*/
	do_action( 'ap_after_update_' . $_post->post_type, $_post, $event );
}

/**
 * Find item in in child array.
 *
 * @param  mixed   $needle Needle to find.
 * @param  mixed   $haystack Haystack.
 * @param  boolean $strict Strict match.
 * @return boolean
 */
function ap_in_array_r( $needle, $haystack, $strict = false ) {
	foreach ( $haystack as $item ) {
		if ( ( $strict ? $item === $needle : $item == $needle ) || ( is_array( $item ) && in_array_r( $needle, $item, $strict ) ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Return short link to a item.
 *
 * @param array $args Arguments.
 * @return string Shortlink to a AnsPress page or item.
 *
 * @category haveTest
 *
 * @since unknown
 * @since 4.1.6 Fixed: trailing slash.
 */
function ap_get_short_link( $args ) {
	array_unshift( $args, [ 'ap_page' => 'shortlink' ] );
	return add_query_arg( $args, home_url( '/' ) );
}

/**
 * Register a callback function which triggred
 * after activating an addon.
 *
 * @param string       $addon Name of addon.
 * @param string|array $cb    Callback function name.
 * @since 4.0.0
 */
function ap_addon_activation_hook( $addon, $cb ) {
	global $ap_addons_activation;
	$addon = wp_normalize_path( $addon );

	$ap_addons_activation[ $addon ] = $cb;
}

/**
 * Insert a value or key/value pair after a specific key in an array.  If key doesn't exist, value is appended
 * to the end of the array.
 *
 * @param array  $array
 * @param string $key
 * @param array  $new
 *
 * @return array
 */
function ap_array_insert_after( $array = [], $key, $new ) {
	$keys  = array_keys( $array );
	$index = array_search( $key, $keys );
	$pos   = false === $index ? count( $array ) : $index + 1;

	return array_merge( array_slice( $array, 0, $pos ), $new, array_slice( $array, $pos ) );
}

/**
 * Utility function for getting random values with weighting.
 *
 * @param integer $min Minimum integer.
 * @param integer $max Maximum integer.
 * @param weight  $weight Weight of random integer.
 * @return integer
 */
function ap_rand( $min, $max, $weight ) {
	$offset = $max - $min + 1;
	return floor( $min + pow( lcg_value(), $weight ) * $offset );
}

/**
 * Convert array notation (string, not real array) to dot notation.
 *
 * @param boolean|string $path Path name.
 * @return string Path separated by dot notation.
 */
function ap_to_dot_notation( $path = false ) {
	$parsed = rtrim( str_replace( '..', '.', str_replace( [ ']', '[' ], '.', $path ) ), '.' );
	return $parsed;
}

/**
 * Set key => value in an array.
 *
 * @param array  $arr  Array in which key value need to set.
 * @param string $path Path of new array item.
 * @param mixed  $val  Value to set.
 *
 * @return array Updated array.
 */
function ap_set_in_array( &$arr, $path, $val ) {
	$path = is_string( $path ) ? explode( '.', $path ) : $path;
	$loc  = &$arr;

	foreach ( (array) $path as $step ) {
		$loc = &$loc[ $step ];
	}

	return $loc = $val;
}

/**
 * Output new/edit question form.
 *
 * @param null $deprecated Deprecated argument.
 * @return void
 *
 * @since unknown
 * @since 4.1.0 Moved from includes\ask-form.php. Deprecated first argument. Using new form class.
 * @since 4.1.5 Don't use ap_ajax as action. Set values here while editing. Get values form session if exists.
 *
 * @category haveTests
 */
function ap_ask_form( $deprecated = null ) {
	if ( ! is_null( $deprecated ) ) {
		_deprecated_argument( __FUNCTION__, '4.1.0', 'Use $_GET[id] for currently editing question ID.' );
	}

	$editing    = false;
	$editing_id = ap_sanitize_unslash( 'id', 'r' );

	// If post_id is empty then its not editing.
	if ( ! empty( $editing_id ) ) {
		$editing = true;
	}

	if ( $editing && ! ap_user_can_edit_question( $editing_id ) ) {
		echo '<p>' . esc_attr__( 'You cannot edit this question.', 'anspress-question-answer' ) . '</p>';
		return;
	}

	if ( ! $editing && ! ap_user_can_ask() ) {
		echo '<p>' . esc_attr__( 'You do not have permission to ask a question.', 'anspress-question-answer' ) . '</p>';
		return;
	}

	$args = array(
		'hidden_fields' => array(
			array(
				'name'  => 'action',
				'value' => 'ap_form_question',
			),
		),
	);

	$values         = [];
	$session_values = anspress()->session->get( 'form_question' );

	// Add value when editing post.
	if ( $editing ) {
		$question = ap_get_post( $editing_id );

		$form['editing']      = true;
		$form['editing_id']   = $editing_id;
		$form['submit_label'] = __( 'Update Question', 'anspress-question-answer' );

		$values['post_title']   = $question->post_title;
		$values['post_content'] = $question->post_content;
		$values['is_private']   = 'private_post' === $question->post_status ? true : false;

		if ( isset( $values['anonymous_name'] ) ) {
			$fields = ap_get_post_field( 'fields', $question );

			$values['anonymous_name'] = ! empty( $fields['anonymous_name'] ) ? $fields['anonymous_name'] : '';
		}
	} elseif ( ! empty( $session_values ) ) {
		// Set last session values if not editing.
		$values = $session_values;
	}

	// Generate form.
	anspress()->get_form( 'question' )->set_values( $values )->generate( $args );
}

/**
 * Remove stop words from post name if option is enabled.
 *
 * @param  string $str Post name to filter.
 * @return string
 *
 * @since  3.0.0
 * @since 4.1.0 Moved from includes\ask-form.php.
 */
function ap_remove_stop_words_post_name( $str ) {
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
 * Send ajax response after posting an answer.
 *
 * @param integer|object $question_id Question ID or object.
 * @param integer|object $answer_id   Answer ID or object.
 * @return void
 * @since 4.0.0
 * @since 4.1.0 Moved from includes\answer-form.php.
 */
function ap_answer_post_ajax_response( $question_id, $answer_id ) {
	$question = ap_get_post( $question_id );
	// Get existing answer count.
	$current_ans = ap_count_published_answers( $question_id );

	global $post;
	$post = ap_get_post( $answer_id );
	setup_postdata( $post );

	ob_start();
	global $withcomments;
	$withcomments = true;

	ap_get_template_part( 'answer' );

	$html        = ob_get_clean();
	$count_label = sprintf( _n( '%d Answer', '%d Answers', $current_ans, 'anspress-question-answer' ), $current_ans );

	$result = array(
		'success'      => true,
		'ID'           => $answer_id,
		'form'         => 'answer',
		'div_id'       => '#post-' . get_the_ID(),
		'can_answer'   => ap_user_can_answer( $post->ID ),
		'html'         => $html,
		'snackbar'     => [ 'message' => __( 'Answer submitted successfully', 'anspress-question-answer' ) ],
		'answersCount' => [
			'text'   => $count_label,
			'number' => $current_ans,
		],
	);

	ap_ajax_json( $result );
}

/**
 * Generate answer form.
 *
 * @param  mixed   $question_id  Question iD.
 * @param  boolean $editing      true if post is being edited.
 * @return void
 * @since unknown
 * @since 4.1.0 Moved from includes\answer-form.php. Using new Form class.
 * @since 4.1.5 Don't use ap_ajax as action.
 * @since 4.1.6 Fixed: editing answer creates new answer.
 */
function ap_answer_form( $question_id, $editing = false ) {
	$editing    = false;
	$editing_id = ap_sanitize_unslash( 'id', 'r' );

	// If post_id is empty then its not editing.
	if ( ! empty( $editing_id ) ) {
		$editing = true;
	}

	if ( $editing && ! ap_user_can_edit_answer( $editing_id ) ) {
		echo '<p>' . esc_attr__( 'You cannot edit this answer.', 'anspress-question-answer' ) . '</p>';
		return;
	}

	if ( ! $editing && ! ap_user_can_answer( $question_id ) ) {
		echo '<p>' . esc_attr__( 'You do not have permission to answer this question.', 'anspress-question-answer' ) . '</p>';
		return;
	}

	$args = array(
		'hidden_fields' => array(
			array(
				'name'  => 'action',
				'value' => 'ap_form_answer',
			),
			array(
				'name'  => 'question_id',
				'value' => (int) $question_id,
			),
		),
	);

	$values         = [];
	$session_values = anspress()->session->get( 'form_answer_' . $question_id );

	// Add value when editing post.
	if ( $editing ) {
		$answer = ap_get_post( $editing_id );

		$form['editing']      = true;
		$form['editing_id']   = $editing_id;
		$form['submit_label'] = __( 'Update Answer', 'anspress-question-answer' );

		$values['post_title']   = $answer->post_title;
		$values['post_content'] = $answer->post_content;
		$values['is_private']   = 'private_post' === $answer->post_status ? true : false;

		if ( isset( $values['anonymous_name'] ) ) {
			$fields = ap_get_post_field( 'fields', $answer );

			$values['anonymous_name'] = ! empty( $fields['anonymous_name'] ) ? $fields['anonymous_name'] : '';
		}

		$args['hidden_fields'][] = array(
			'name'  => 'post_id',
			'value' => (int) $editing_id,
		);

	} elseif ( ! empty( $session_values ) ) {
		// Set last session values if not editing.
		$values = $session_values;
	}

	anspress()->get_form( 'answer' )->set_values( $values )->generate( $args );
}

/**
 * Generate comment form.
 *
 * @param  false|integer $post_id  Question or answer id.
 * @param  false|object  $_comment Comment id or object.
 * @return void
 *
 * @since 4.1.0
 * @since 4.1.5 Don't use ap_ajax.
 */
function ap_comment_form( $post_id = false, $_comment = false ) {
	if ( false === $post_id ) {
		$post_id = get_the_ID();
	}

	if ( ! ap_user_can_comment( $post_id ) ) {
		return;
	}

	$args = array(
		'hidden_fields' => array(
			array(
				'name'  => 'post_id',
				'value' => $post_id,
			),
			array(
				'name'  => 'action',
				'value' => 'ap_form_comment',
			),
		),
	);

	$form = anspress()->get_form( 'comment' );

	// Add value when editing post.
	if ( false !== $_comment && ! empty( $_comment ) ) {
		$_comment = get_comment( $_comment );
		$values   = [];

		$args['hidden_fields'][] = array(
			'name'  => 'comment_id',
			'value' => $_comment->comment_ID,
		);

		$values['content'] = $_comment->comment_content;

		if ( '0' == $_comment->user_id ) {
			$values['author'] = $_comment->comment_author;
			$values['email']  = $_comment->comment_author_email;
			$values['url']    = $_comment->comment_author_url;
		}

		$form->set_values( $values );
	}

	$form->generate( $args );
}

/**
 * Include tinymce assets.
 *
 * @return void
 * @since 4.1.0
 */
function ap_ajax_tinymce_assets() {
	if ( ! class_exists( '_WP_Editors' ) ) {
		require ABSPATH . WPINC . '/class-wp-editor.php';
	}

	\_WP_Editors::enqueue_scripts();

	ob_start();
	print_footer_scripts();
	$scripts = ob_get_clean();

	echo str_replace( 'jquery-core,jquery-migrate,', '', $scripts ); // xss okay.
	\_WP_Editors::editor_js();
}

/**
 * All pages required of AnsPress.
 *
 * @return array
 * @since 4.1.0
 */
function ap_main_pages() {
	$pages = array(
		'base_page'       => array(
			'label'      => __( 'Archives page', 'anspress-question-answer' ),
			'desc'       => __( 'Page used to display question archive (list). Sometimes this page is used for displaying other subpages of AnsPress.<br/>This page is also referred as <b>Base Page</b> in AnsPress documentations and support forum.', 'anspress-question-answer' ),
			'post_title' => __( 'Questions', 'anspress-question-answer' ),
			'post_name'  => 'questions',
		),
		'ask_page'        => array(
			'label'      => __( 'Ask page', 'anspress-question-answer' ),
			'desc'       => __( 'Page used to display ask form.', 'anspress-question-answer' ),
			'post_title' => __( 'Ask a question', 'anspress-question-answer' ),
			'post_name'  => 'ask',
		),
		'user_page'       => array(
			'label'      => __( 'User page', 'anspress-question-answer' ),
			'desc'       => __( 'Page used to display user profile.', 'anspress-question-answer' ),
			'post_title' => __( 'Profile', 'anspress-question-answer' ),
			'post_name'  => 'profile',
		),
		'categories_page' => array(
			'label'      => __( 'Categories page', 'anspress-question-answer' ),
			'desc'       => __( 'Page used to display question categories. NOTE: Categories addon must be enabled to render this page.', 'anspress-question-answer' ),
			'post_title' => __( 'Categories', 'anspress-question-answer' ),
			'post_name'  => 'categories',
		),
		'tags_page'       => array(
			'label'      => __( 'Tags page', 'anspress-question-answer' ),
			'desc'       => __( 'Page used to display question tags. NOTE: Tags addon must be enabled to render this page.', 'anspress-question-answer' ),
			'post_title' => __( 'Tags', 'anspress-question-answer' ),
			'post_name'  => 'tags',
		),
		'activities_page' => array(
			'label'      => __( 'Activities page', 'anspress-question-answer' ),
			'desc'       => __( 'Page used to display all anspress activities.', 'anspress-question-answer' ),
			'post_title' => __( 'Activities', 'anspress-question-answer' ),
			'post_name'  => 'activities',
		),
	);

	/**
	 * Hook for filtering main pages of AnsPress. Custom main pages
	 * can be registered using this hook.
	 *
	 * @param array $pages Array of pages.
	 * @since 4.1.5
	 */
	return apply_filters( 'ap_main_pages', $pages );
}

/**
 * Return post IDs of main pages.
 *
 * @return array
 * @since 4.1.0
 */
function ap_main_pages_id() {
	$main_pages = array_keys( ap_main_pages() );
	$pages_id   = [];

	foreach ( $main_pages as $slug ) {
		$pages_id[ $slug ] = ap_opt( $slug );
	}

	return $pages_id;
}

/**
 * Get current user id for AnsPress profile.
 *
 * This function must be used only in AnsPress profile. This function checks for
 * user ID in queried object, hence if not in user page
 *
 * @return integer Always returns 0 if not in AnsPress profile page.
 * @since 4.1.1
 */
function ap_current_user_id() {
	if ( 'user' === ap_current_page() ) {
		$query_object = get_queried_object();

		if ( $query_object instanceof WP_User ) {
			return $query_object->ID;
		}
	}

	return 0;
}

/**
 * Check if post object is AnsPress CPT i.e. question or answer.
 *
 * @param WP_Post $_post WordPress post object.
 * @return boolean
 * @since 4.1.2
 */
function ap_is_cpt( $_post ) {
	return ( in_array( $_post->post_type, [ 'answer', 'question' ], true ) );
}

/**
 * Removes all filters from a WordPress filter, and stashes them in the anspress()
 * global in the event they need to be restored later.
 * Copied directly from bbPress plugin.
 *
 * @global WP_filter $wp_filter
 * @global array $merged_filters
 * @param string $tag
 * @param int $priority
 * @return bool
 *
 * @since 4.2.0
 */
function ap_remove_all_filters( $tag, $priority = false ) {
	global $wp_filter, $merged_filters;

	$ap = anspress();

	// Filters exist
	if ( isset( $wp_filter[ $tag ] ) ) {

		// Filters exist in this priority
		if ( ! empty( $priority ) && isset( $wp_filter[ $tag ][ $priority ] ) ) {

			// Store filters in a backup
			$ap->new_filters->wp_filter[ $tag ][ $priority ] = $wp_filter[ $tag ][ $priority ];

			// Unset the filters
			unset( $wp_filter[ $tag ][ $priority ] );

		// Priority is empty.
		} else {

			// Store filters in a backup
			$ap->new_filters->wp_filter[ $tag ] = $wp_filter[ $tag ];

			// Unset the filters
			unset( $wp_filter[ $tag ] );
		}
	}

	// Check merged filters
	if ( isset( $merged_filters[ $tag ] ) ) {

		// Store filters in a backup
		$ap->new_filters->merged_filters[ $tag ] = $merged_filters[ $tag ];

		// Unset the filters
		unset( $merged_filters[ $tag ] );
	}

	return true;
}
