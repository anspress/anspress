<?php
/**
 * An AnsPress add-on to for displaying user profile.
 *
 * @author    Rahul Aryan <support@anspress.io>
 * @copyright 2014 AnsPress.io & Rahul Aryan
 * @license   GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link      https://anspress.io
 * @package   WordPress/AnsPress/Profile
 *
 * Addon Name:    User Profile
 * Addon URI:     https://anspress.io
 * Description:   Display user profile.
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
		ap_add_default_options([
			'user_page_slug_questions'   => 'questions',
			'user_page_slug_answers'   => 'answers',
			'user_page_title_questions'   => __( 'Questions', 'anspress-question-answer' ),
			'user_page_title_answers'   => __( 'Answers', 'anspress-question-answer' ),
		]);
		anspress()->add_action( 'ap_option_groups', __CLASS__, 'options' );
		ap_register_page( 'user', __( 'User profile', 'anspress-question-answer' ), [ __CLASS__, 'user_page' ], true, true );
		anspress()->add_action( 'ap_rewrite_rules', __CLASS__, 'rewrite_rules', 10, 3 );
		anspress()->add_filter( 'ap_menu_link', __CLASS__, 'menu_link', 10, 2 );
		anspress()->add_action( 'ap_ajax_user_more_answers', __CLASS__, 'load_more_answers', 10, 2 );
		anspress()->add_filter( 'ap_page_title', __CLASS__, 'page_title' );
		anspress()->add_filter( 'ap_user_link', __CLASS__, 'ap_user_link', 10, 3 );
	}

	/**
	 * Register profile options
	 */
	public static function options() {

		ap_register_option_section( 'addons', basename( __FILE__ ),  __( 'User Profile', 'anspress-question-answer' ), [
			array(
				'name'  => 'user_page_title_questions',
				'label' => __( 'Questions page title', 'anspress-question-answer' ),
				'desc'  => __( 'Custom title for user profile questions page', 'anspress-question-answer' ),
			),
			array(
				'name'  => 'user_page_slug_questions',
				'label' => __( 'Questions page slug', 'anspress-question-answer' ),
				'desc'  => __( 'Custom slug for user profile questions page', 'anspress-question-answer' ),
			),
			array(
				'name'  => 'user_page_title_answers',
				'label' => __( 'Answers page title', 'anspress-question-answer' ),
				'desc'  => __( 'Custom title for user profile answers page', 'anspress-question-answer' ),
			),
			array(
				'name'  => 'user_page_slug_answers',
				'label' => __( 'Answers page slug', 'anspress-question-answer' ),
				'desc'  => __( 'Custom slug for user profile answers page', 'anspress-question-answer' ),
			),
		]);
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
		$base = $slug . ap_get_page_slug( 'user' );

		self::user_pages();

		$new_rules = [];


		foreach ( (array) anspress()->user_pages as $page ) {
			$new_rules[ $base . '/([^/]+)/' . $page['rewrite'] . '/page/?([0-9]{1,})/?$' ] = 'index.php?page_id=' . $base_page_id . '&ap_page=user&ap_user=$matches[#]&user_page=' . $page['slug'] . '&paged=$matches[#]';
			$new_rules[ $base . '/([^/]+)/' . $page['rewrite'] . '/?' ] = 'index.php?page_id=' . $base_page_id . '&ap_page=user&ap_user=$matches[#]&user_page=' . $page['slug'];
		}

		$new_rules[ $base . '/([^/]+)/?' ] = 'index.php?page_id=' . $base_page_id . '&ap_page=user&ap_user=$matches[#]';

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
		if ( 'user' === $item->object ) {
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
			$rewrite = ap_opt( 'user_page_slug_' . $args['slug'] );
			$title = ap_opt( 'user_page_title_' . $args['slug'] );

			// Override user page slug.
			if ( empty( $args['rewrite'] ) ) {
				anspress()->user_pages[ $key ]['rewrite'] = ! empty( $rewrite ) ? sanitize_title( $rewrite ) : $args['slug'];
			}

			// Override user page title.
			if ( ! empty( $title ) ) {
				anspress()->user_pages[ $key ]['label'] = $title;
			}

			// Add default order.
			if ( ! isset( $args['order'] ) ) {
				anspress()->user_pages[ $key ]['order'] = 10;
			}
		}

		anspress()->user_pages = ap_sort_array_by_order( anspress()->user_pages );
	}

	/**
	 * Output user profile menu.
	 */
	public static function user_menu( $user_id = false, $class = '' ) {
		$user_id = false !== $user_id ? $user_id : (int) get_query_var( 'ap_user_id' );
		$current_tab = ap_sanitize_unslash( 'user_page', 'query_var', 'questions' );
		$ap_menu = apply_filters( 'ap_user_menu_items', anspress()->user_pages, $user_id );

		echo '<ul class="ap-tab-nav clearfix ' . esc_attr( $class ) . '">';

		foreach ( (array) $ap_menu as $args ) {

			if ( empty( $args['private'] ) || ( true === $args['private'] && get_current_user_id() === $user_id ) ) {
				echo '<li class="ap-menu-' . esc_attr( $args['slug'] ) . ( $args['slug'] === $current_tab ? ' active' : '' ) . '">';

				$url = isset( $args['url'] ) ? $args['url'] : ap_user_link( $user_id, $args['rewrite'] );
				echo '<a href="' . esc_url( $url ) . '">';

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
	 * Filter ap_user_link function.
	 *
	 * @param string       $link Link.
	 * @param integer      $user_id User id.
	 * @param array|string $sub Sub page.
	 * @return string
	 */
	public static function ap_user_link( $link, $user_id, $sub ) {
		$user = get_user_by( 'id', $user_id );

		// If permalink is enabled.
		if ( $user_id > 0 && get_option( 'permalink_structure' ) !== '' ) {
			if ( false === $sub ) {
				$sub = array( 'ap_page' => 'user', 'ap_user' => $user->user_login );
			} elseif ( is_array( $sub ) ) {
				$sub['ap_page']  = 'user';
				$sub['ap_user']  = $user->user_login;
			} elseif ( ! is_array( $sub ) ) {
				$sub = array( 'ap_page' => 'user', 'ap_user' => $user->user_login, 'user_page' => $sub );
			}

			$link = ap_get_link_to( $sub );
		}

		return $link;
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
			if ( isset( $current_page['cb'] ) && is_array( $current_page['cb'] ) && method_exists( $current_page['cb'][0], $current_page['cb'][1] ) ) {
				call_user_func( $current_page['cb'] );
			} elseif ( function_exists( $current_page['cb'] ) ) {
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
