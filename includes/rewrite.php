<?php
	/**
	 * Plugin rewrite rules and query variables
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

/**
 * This class handle all rewrite rules and define query varibale of anspress
 * @since 2.0.0
 */
class AnsPress_Rewrite {
	private static $counter = 1;
	/**
	 * Register query vars
	 * @param  array $query_vars Registered query variables.
	 * @return array
	 */
	public static function query_var( $query_vars ) {
		$query_vars[] = 'edit_post_id';
		$query_vars[] = 'ap_nonce';
		$query_vars[] = 'question_id';
		$query_vars[] = 'answer_id';
		$query_vars[] = 'question';
		$query_vars[] = 'question_name';
		$query_vars[] = 'answer_id';
		$query_vars[] = 'answer';
		$query_vars[] = 'ask';
		$query_vars[] = 'ap_page';
		$query_vars[] = 'qcat_id';
		$query_vars[] = 'qcat';
		$query_vars[] = 'qtag_id';
		$query_vars[] = 'q_tag';
		$query_vars[] = 'q_cat';
		$query_vars[] = 'ap_s';
		$query_vars[] = 'parent';
		$query_vars[] = 'ap_user';
		$query_vars[] = 'user_page';
		//$query_vars[] = 'ap_paged';

		return $query_vars;
	}

	/**
	 * Rewrite rules.
	 *
	 * @return array
	 */
	public static function rewrites() {
		global $wp_rewrite;
		global $ap_rules;

		unset( $wp_rewrite->extra_permastructs['question'] );
		unset( $wp_rewrite->extra_permastructs['answer'] );

		$base_page_id = ap_opt( 'base_page' );
		$slug = ap_base_page_slug() . '/';
		$lang = '';
		$lang_rule = '';
		$lang_index = 0;

		// Support polylang permalink.
		if ( function_exists( 'pll_languages_list' ) ) {
			if ( ! empty( pll_languages_list() ) ) {
				$lang = '(' . implode( '|', pll_languages_list() ) . ')/';
				$lang_rule = '&lang=$matches[#]';
				$lang_index = 1;
			}
		}

		$question_permalink = ap_opt( 'question_page_permalink' );
		$question_slug = ap_get_page_slug( 'question' );

		if ( 'question_perma_2' === $question_permalink ) {
			$question_placeholder = $lang . $question_slug . '/([^/]+)';
			$question_perma = '&question_name=$matches[#]';
		} elseif ( 'question_perma_3' === $question_permalink ) {
			$question_placeholder = $lang . $question_slug . '/([^/]+)';
			$question_perma = '&question_id=$matches[#]';
		} elseif ( 'question_perma_4' === $question_permalink ) {
			$question_placeholder = $lang . $question_slug . '/([^/]+)/([^/]+)';
			$question_perma = '&question_id=$matches[#]&question_name=$matches[#]';
		} else {
			$question_placeholder = $lang . ap_base_page_slug() . '/' . $question_slug . '/([^/]+)';
			$question_perma = '&question_name=$matches[#]';
		}

		$slug = $lang . $slug;
		$base_page_id = $base_page_id . $lang_rule;

		$new_rules = array(
			$slug . 'parent/([^/]+)/?' => 'index.php?page_id=' . $base_page_id . '&parent=$matches[#]',

			$slug . 'page/?([0-9]{1,})/?$' => 'index.php?page_id=' . $base_page_id . '&paged=$matches[#]',

			$slug . '([^/]+)/page/?([0-9]{1,})/?$' => 'index.php?page_id=' . $base_page_id . '&ap_page=$matches[#]&paged=$matches[#]',
		);


		$new_rules[ $question_placeholder . '/([^/]+)/?$' ] = 'index.php?page_id=' . $base_page_id . '&ap_page=question' . $question_perma . '&answer_id=$matches[#]';

		$new_rules[ $question_placeholder . '/?$' ]  = 'index.php?page_id=' . $base_page_id . '&ap_page=question' . $question_perma;

		$new_rules[ $slug . ap_get_page_slug( 'search' ) . '/([^/]+)/?' ] = 'index.php?page_id=' . $base_page_id . '&ap_page=search&ap_s=$matches[#]';

		$new_rules[ $slug . ap_get_page_slug( 'ask' ) . '/([^/]+)/?' ] = 'index.php?page_id=' . $base_page_id . '&ap_page=ask&parent=$matches[#]';

		$new_rules[ $slug . ap_get_page_slug( 'ask' ) . '/?' ] = 'index.php?page_id=' . $base_page_id . '&ap_page=ask';

		$new_rules[ $slug . '([^/]+)/?' ] = 'index.php?page_id=' . $base_page_id . '&ap_page=$matches[#]';

		$ap_rules = apply_filters( 'ap_rewrite_rules', $new_rules, $slug, $base_page_id );

		foreach( $ap_rules as $k => $r ) {
			$ap_rules[ $k] = preg_replace_callback('/\#/', [ __CLASS__, 'incr_hash' ], $r );
			self::$counter = 1;
		}

		return $wp_rewrite->rules = $ap_rules + $wp_rewrite->rules;
	}

	public static function incr_hash( $matches ) {
		return self::$counter++;
  }

	public static function bp_com_paged($args) {
		if ( function_exists( 'bp_current_component' ) ) {
			$bp_com = bp_current_component();

			if ( 'questions' == $bp_com || 'answers' == $bp_com ) {
				return preg_replace( '/page.([0-9]+)./', '?paged=$1', $args );
			}
		}

		return $args;
	}

	/**
	 * Push custom query args in `$wp`.
	 *
	 * If `question_name` is passed then `question_id` var will be added.
	 * Same for `ap_user`.
	 *
	 * @param object $wp WP query object.
	 */
	public static function add_query_var( $wp ) {
		if ( ! empty( $wp->query_vars['question_name'] ) ) {
			$wp->set_query_var( 'ap_page', 'question' );
			$question = get_page_by_path( sanitize_title( $wp->query_vars['question_name'] ), 'OBJECT', 'question' );

			if ( $question ) {
				$wp->set_query_var( 'question_id', $question->ID );
			} else {
				// Rediret to 404 page if question does not exists.
				global $wp_query;
				$wp_query->set_404();
				status_header( 404 );
				get_template_part( 404 );
				exit();
			}
		}

		if ( ! empty( $wp->query_vars['ap_user'] ) ) {
			$user = get_user_by( 'login', sanitize_text_field( urldecode( $wp->query_vars['ap_user'] ) ) );

			if ( $user ) {
				$wp->set_query_var( 'ap_user_id', (int) $user->ID );
			} else {
				global $wp_query;
				$wp_query->set_404();
				status_header( 404 );
				get_template_part( 404 );
				exit();
			}
		}
	}

	/**
	 * Handles shortlink redirects.
	 */
	public static function shortlink() {
		global $wp_query;
		$page  = get_query_var( 'ap_page' );

		if ( empty( $page ) || 'shortlink' !== $page ) {
			return;
		}

		// Post redirect.
		if ( ap_isset_post_value( 'ap_p', false ) ) {
			$permalink = get_permalink( ap_isset_post_value( 'ap_p' ) );
			exit( wp_redirect( $permalink, 302 ) ); // xss okay.
		}

		// Comment redirect.
		if ( ap_isset_post_value( 'ap_c', false ) ) {
			$permalink = get_comment_link( ap_isset_post_value( 'ap_c' ) );
			exit( wp_redirect( $permalink, 302 ) ); // xss okay.
		}

		// User redirect.
		if ( ap_isset_post_value( 'ap_u', false ) ) {
			$permalink = ap_user_link( ap_isset_post_value( 'ap_u' ), ap_isset_post_value( 'sub' ) );
			exit( wp_redirect( $permalink, 302 ) ); // xss okay.
		}
	}
}
