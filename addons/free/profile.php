<?php
/**
 * An AnsPress add-on to for displaying user profile.
 *
 * @author    Rahul Aryan <support@rahularyan.com>
 * @copyright 2014 AnsPress.io & Rahul Aryan
 * @license   GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link      https://anspress.io
 * @package   WordPress/AnsPress/BadWords
 *
 * Addon Name:    User Profile
 * Addon URI:     https://anspress.io
 * Description:   Dipslay user profile.
 * Author:        Rahul Aryan
 * Author URI:    https://anspress.io
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * User profile hooks.
 */
class AnsPress_Profile_Hooks {
	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since 4.0.0.
	 */
	public static function init() {
		ap_register_page( 'user', __( 'User profile', 'anspress-question-answer' ), [ __CLASS__, 'user_page' ] );
		anspress()->add_action( 'ap_rewrite_rules', __CLASS__, 'rewrite_rules', 10, 3 );
		anspress()->add_filter( 'ap_menu_link', __CLASS__, 'menu_link', 10, 2 );
		anspress()->add_action( 'ap_ajax_user_more_answers', __CLASS__, 'load_more_answers', 10, 2 );
		anspress()->add_filter( 'ap_page_title', __CLASS__, 'page_title' );
	}

	/**
	 * Layout of base page
	 */
	public static function user_page() {
		SELF::user_pages();
		dynamic_sidebar( 'ap-top' );

		echo '<div id="ap-user" class="ap-row">';
		include ap_get_theme_location( 'addons/user/index.php' );
		echo '</div>';
	}

	/**
	 * Add category pages rewrite rule.
	 *
	 * @param  array   $rules AnsPress rules.
	 * @param  string  $slug Slug.
	 * @param  integer $base_page_id Base page ID.
	 * @return array
	 */
	public static function rewrite_rules( $rules, $slug, $base_page_id ) {
		$base = 'index.php?page_id=' . $base_page_id . '&ap_page=' ;

		$new_rules = array(
			$slug . ap_get_page_slug( 'user' ) . '/([^/]+)/([^/]+)/?' => 'index.php?page_id=' . $base_page_id . '&ap_page=user&ap_user=$matches[#]&user_page=$matches[#]',
			$slug . ap_get_page_slug( 'user' ) . '/([^/]+)/?' => 'index.php?page_id=' . $base_page_id . '&ap_page=user&ap_user=$matches[#]',
		);

		return $new_rules + $rules;
	}

	/**
	 * Filter user menu links.
	 *
	 * @param  string $url Menu url.
	 * @param  object $item Menu item object.
	 * @return string
	 */
	public static function menu_link( $url, $item ) {
		if ( 'user-profile' === $item->post_name ) {
			$url = ap_user_link( get_current_user_id() );
		}

		return $url;
	}

	/**
	 * Register user profile pages.
	 */
	public static function user_pages() {
		if ( ! empty( anspress()->user_pages ) ) {
			return;
		}

		anspress()->user_pages = array(
			array(
				'slug'  => 'questions',
				'label' => __( 'Questions', 'anspress-question-answer' ),
				'icon'  => 'apicon-question',
				'cb'    => [ __CLASS__, 'question_page' ],
				'order' => 2,
			),
			array(
				'slug'  => 'answers',
				'label' => __( 'Answers', 'anspress-question-answer' ),
				'icon'  => 'apicon-answer',
				'cb'    => [ __CLASS__, 'answer_page' ],
				'order' => 2,
			),
		);

		do_action( 'ap_user_pages' );

		foreach ( (array) anspress()->user_pages as $key => $args ) {
			if ( ! isset( $args['order'] ) ) {
				anspress()->user_pages[ $key ][ 'order' ] = 10;
			}
		}

		anspress()->user_pages = ap_sort_array_by_order( anspress()->user_pages );
	}

	/**
	 * Output user profile menu.
	 */
	public static function user_menu() {
		$user_id = (int) get_query_var( 'ap_user_id' );
		$current_tab = ap_sanitize_unslash( 'user_page', 'query_var', 'questions' );

		echo '<ul class="ap-tab-nav clearfix">';
		foreach ( (array) anspress()->user_pages as $args ) {

			if ( empty( $args['private'] ) || ( true === $args['private'] && get_current_user_id() === $user_id ) ) {
				echo '<li ' . ( $args['slug'] === $current_tab ? ' class="active"' : '' ) . '>';
				echo '<a href="' . esc_url( ap_user_link( $user_id, $args['slug'] ) ) . '">';

				// Show icon.
				if ( ! empty( $args['icon'] ) ) {
					echo '<i class="' . esc_attr( $args['icon'] ) . '"></i>';
				}

				echo esc_attr( $args['label'] );

				// Show count.
				if ( ! empty( $args['count'] ) ) {
					echo '<span>' . esc_attr( number_format_i18n( $args['count'] ) ) . '</span>';
				}

				echo '</a>';
				echo '</li>';
			}
		}

		echo '</ul>';
	}

	/**
	 * Add user page title.
	 *
	 * @param  string $title AnsPress page title.
	 * @return string
	 */
	public static function page_title( $title ) {
		if ( 'user' === ap_current_page() ) {
			SELF::user_pages();
			$title = sprintf( ap_opt( 'user_page_title' ), ap_user_display_name( get_query_var( 'ap_user_id' ) ) );
			$current_tab = ap_sanitize_unslash( 'user_page', 'query_var', 'questions' );			
			$page = ap_search_array( anspress()->user_pages, 'slug', $current_tab );		
			
			if ( empty( $page ) ) {
				return $title;
			}

			return $title . ' | ' . $page[0]['label'];
		}

		return $title;
	}

	/**
	 * Render sub page template.
	 */
	public static function sub_page_template() {
		$current = ap_sanitize_unslash( 'user_page', 'query_var', 'questions' );
		$current_page = ap_search_array( anspress()->user_pages, 'slug', $current );

		if ( ! empty( $current_page ) ) {
			$current_page = $current_page[0];

			// Callback.
			if ( isset( $current_page['cb'] ) && method_exists( $current_page['cb'][0], $current_page['cb'][1] ) ) {
				call_user_func( $current_page['cb'] );
			} elseif ( ! is_array( $current_page['cb'] ) && function_exists( $current_page['cb'] ) ) {
				call_user_func( $current_page['cb'] );
			} else {
				_e( 'Callback function not found for rendering this page', 'anspress-question-answer' ); // xss okay.
			}
		}
	}

	/**
	 * Display user questions page.
	 */
	public static function question_page() {
		global $questions;
		$args['ap_current_user_ignore'] = true;
		$args['author'] = (int) get_query_var( 'ap_user_id' );

		/**
		* FILTER: ap_authors_questions_args
		* Filter authors question list args
		*
		* @var array
		*/
		$args = apply_filters( 'ap_authors_questions_args', $args );

		anspress()->questions = $questions = new Question_Query( $args );

		include ap_get_theme_location( 'addons/user/questions.php' );
	}

	/**
	 * Display user questions page.
	 */
	public static function answer_page() {
		global $answers;
		$args['ap_current_user_ignore'] = true;
		$args['ignore_selected_answer'] = true;
		$args['showposts'] = 10;
		$args['author'] = (int) get_query_var( 'ap_user_id' );

		/*if ( false !== $paged ) {
			$args['paged'] = $paged;
		}*/

		/**
		 * FILTER: ap_authors_questions_args
		 * Filter authors question list args
		 *
		 * @var array
		 */
		$args = apply_filters( 'ap_user_answers_args', $args );
		anspress()->answers = $answers = new Answers_Query( $args );

		ap_get_template_part( 'addons/user/answers' );
	}

	public static function load_more_answers() {
		global $answers;
		$user_id = ap_sanitize_unslash( 'user_id', 'r' );
		$paged = ap_sanitize_unslash( 'current', 'r', 1 ) + 1;
		$args['ap_current_user_ignore'] = true;
		$args['ignore_selected_answer'] = true;
		$args['showposts'] = 10;
		$args['author'] = (int) $user_id;

		if ( false !== $paged ) {
			$args['paged'] = $paged;
		}

		/**
		 * FILTER: ap_authors_questions_args
		 * Filter authors question list args
		 *
		 * @var array
		 */
		$args = apply_filters( 'ap_user_answers_args', $args );
		anspress()->answers = $answers = new Answers_Query( $args );

		ob_start();
		if ( ap_have_answers() ) {
			/* Start the Loop */
			while ( ap_have_answers() ) : ap_the_answer();
				ap_get_template_part( 'addons/user/answer-item' );
			endwhile;
		}
		$html = ob_get_clean();

		ap_ajax_json(array(
			'success'  => true,
			'element'  => '#ap-bp-answers',
			'args'  => [ 'ap_ajax_action' => 'user_more_answers', '__nonce' => wp_create_nonce( 'loadmore-answers' ), 'type' => 'answers', 'current' => $paged, 'user_id' => $user_id ],
			'html'   	 => $html,
		));
	}

}

// Init addon.
AnsPress_Profile_Hooks::init();
