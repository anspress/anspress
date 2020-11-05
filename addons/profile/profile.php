<?php
/**
 * An AnsPress add-on to for displaying user profile.
 *
 * @author     Rahul Aryan <rah12@live.com>
 * @copyright  2014 anspress.net & Rahul Aryan
 * @license    GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link       https://anspress.net
 * @package    AnsPress
 * @subpackage User Profile Addon
 *
 * @anspress-addon
 * Addon Name:    User Profile
 * Addon URI:     https://anspress.net
 * Description:   Display user profile.
 * Author:        Rahul Aryan
 * Author URI:    https://anspress.net
 */

namespace AnsPress\Addons;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * User profile hooks.
 */
class Profile extends \AnsPress\Singleton {
	/**
	 * Instance of this class.
	 *
	 * @var     object
	 * @since 4.1.8
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since 4.0.0
	 */
	protected function __construct() {
		ap_add_default_options(
			[
				'user_page_slug_questions'  => 'questions',
				'user_page_slug_answers'    => 'answers',
				'user_page_title_questions' => __( 'Questions', 'anspress-question-answer' ),
				'user_page_title_answers'   => __( 'Answers', 'anspress-question-answer' ),
			]
		);

		anspress()->add_action( 'ap_form_addon-profile', $this, 'options' );
		ap_register_page( 'user', __( 'User profile', 'anspress-question-answer' ), [ $this, 'user_page' ], true, true );

		anspress()->add_action( 'ap_rewrites', $this, 'rewrite_rules', 10, 3 );
		anspress()->add_action( 'ap_ajax_user_more_answers', $this, 'load_more_answers', 10, 2 );
		anspress()->add_filter( 'wp_title', $this, 'page_title' );
		anspress()->add_action( 'the_post', $this, 'filter_page_title' );
		anspress()->add_filter( 'ap_current_page', $this, 'ap_current_page' );
		anspress()->add_filter( 'posts_pre_query', $this, 'modify_query_archive', 999, 2 );
	}

	/**
	 * Register profile options
	 */
	public function options() {
		$opt = ap_opt();

		$form = array(
			'fields' => array(
				'user_page_title_questions' => array(
					'label' => __( 'Questions page title', 'anspress-question-answer' ),
					'desc'  => __( 'Custom title for user profile questions page', 'anspress-question-answer' ),
					'value' => $opt['user_page_title_questions'],
				),
				'user_page_slug_questions'  => array(
					'label' => __( 'Questions page slug', 'anspress-question-answer' ),
					'desc'  => __( 'Custom slug for user profile questions page', 'anspress-question-answer' ),
					'value' => $opt['user_page_slug_questions'],
				),
				'user_page_title_answers'   => array(
					'label' => __( 'Answers page title', 'anspress-question-answer' ),
					'desc'  => __( 'Custom title for user profile answers page', 'anspress-question-answer' ),
					'value' => $opt['user_page_title_answers'],
				),
				'user_page_slug_answers'    => array(
					'label' => __( 'Answers page slug', 'anspress-question-answer' ),
					'desc'  => __( 'Custom slug for user profile answers page', 'anspress-question-answer' ),
					'value' => $opt['user_page_slug_answers'],
				),
			),
		);

		return $form;
	}

	/**
	 * Layout of base page
	 */
	public function user_page() {
		$this->user_pages();
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
	public function rewrite_rules( $rules, $slug, $base_page_id ) {
		$base_slug = get_page_uri( ap_opt( 'user_page' ) );
		update_option( 'ap_user_path', $base_slug, true );

		$new_rules = [];
		$new_rules = array(
			$base_slug . '/([^/]+)/([^/]+)/page/?([0-9]{1,})/?' => 'index.php?author_name=$matches[#]&ap_page=user&user_page=$matches[#]&ap_paged=$matches[#]',
			$base_slug . '/([^/]+)/([^/]+)/?' => 'index.php?author_name=$matches[#]&ap_page=user&user_page=$matches[#]',
			$base_slug . '/([^/]+)/?'         => 'index.php?author_name=$matches[#]&ap_page=user',
			$base_slug . '/?'                 => 'index.php?ap_page=user',
		);

		return $new_rules + $rules;
	}

	/**
	 * Register user profile pages.
	 */
	public function user_pages() {
		if ( ! empty( anspress()->user_pages ) ) {
			return;
		}

		anspress()->user_pages = array(
			array(
				'slug'  => 'questions',
				'label' => __( 'Questions', 'anspress-question-answer' ),
				'icon'  => 'apicon-question',
				'cb'    => [ $this, 'question_page' ],
				'order' => 2,
			),
			array(
				'slug'  => 'answers',
				'label' => __( 'Answers', 'anspress-question-answer' ),
				'icon'  => 'apicon-answer',
				'cb'    => [ $this, 'answer_page' ],
				'order' => 2,
			),
		);

		do_action( 'ap_user_pages' );

		foreach ( (array) anspress()->user_pages as $key => $args ) {
			$rewrite = ap_opt( 'user_page_slug_' . $args['slug'] );
			$title   = ap_opt( 'user_page_title_' . $args['slug'] );

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
	public function user_menu( $user_id = false, $class = '' ) {
		$user_id     = false !== $user_id ? $user_id : ap_current_user_id();
		$current_tab = get_query_var( 'user_page', ap_opt( 'user_page_slug_questions' ) );
		$ap_menu     = apply_filters( 'ap_user_menu_items', anspress()->user_pages, $user_id );

		echo '<ul class="ap-tab-nav clearfix ' . esc_attr( $class ) . '">';

		foreach ( (array) $ap_menu as $args ) {

			if ( empty( $args['private'] ) || ( true === $args['private'] && get_current_user_id() === $user_id ) ) {
				echo '<li class="ap-menu-' . esc_attr( $args['slug'] ) . ( $args['rewrite'] === $current_tab ? ' active' : '' ) . '">';

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

	public function user_page_title() {
		$this->user_pages();
		$title       = ap_user_display_name( ap_current_user_id() );
		$current_tab = sanitize_title( get_query_var( 'user_page', ap_opt( 'user_page_slug_questions' ) ) );
		$page        = ap_search_array( anspress()->user_pages, 'rewrite', $current_tab );

		if ( ! empty( $page ) ) {
			return $title . ' | ' . $page[0]['label'];
		}
	}

	/**
	 * Add user page title.
	 *
	 * @param  string $title AnsPress page title.
	 * @return string
	 */
	public function page_title( $title ) {
		if ( 'user' === ap_current_page() ) {
			return $this->user_page_title() . ' | ';
		}

		return $title;
	}

	/**
	 * Filter user page title.
	 *
	 * @param object $_post WP post object.
	 * @return void
	 */
	public function filter_page_title( $_post ) {
		if ( 'user' === ap_current_page() && ap_opt( 'user_page' ) == $_post->ID && ! is_admin() ) {
			$_post->post_title = $this->user_page_title();
		}
	}

	/**
	 * Render sub page template.
	 */
	public function sub_page_template() {
		$current      = get_query_var( 'user_page', ap_opt( 'user_page_slug_questions' ) );
		$current_page = ap_search_array( anspress()->user_pages, 'rewrite', $current );

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
	public function question_page() {
		$user_id                        = ap_current_user_id();
		$args['ap_current_user_ignore'] = true;
		$args['author']                 = $user_id;

		/**
		* Filter authors question list args
		*
		* @var array
		*/
		$args = apply_filters( 'ap_authors_questions_args', $args );

		anspress()->questions = new \Question_Query( $args );

		include ap_get_theme_location( 'addons/user/questions.php' );
	}

	/**
	 * Display user questions page.
	 */
	public function answer_page() {
		global $answers;

		$user_id = ap_current_user_id();

		$args['ap_current_user_ignore'] = true;
		$args['ignore_selected_answer'] = true;
		$args['showposts']              = 10;
		$args['author']                 = $user_id;

		/*
		if ( false !== $paged ) {
			$args['paged'] = $paged;
		}*/

		/**
		 * Filter authors question list args
		 *
		 * @var array
		 */
		$args               = apply_filters( 'ap_user_answers_args', $args );
		anspress()->answers = $answers = new \Answers_Query( $args );

		ap_get_template_part( 'addons/user/answers' );
	}

	/**
	 * Ajax callback for loading more answers.
	 *
	 * @return void
	 */
	public function load_more_answers() {
		global $answers;

		$user_id = ap_sanitize_unslash( 'user_id', 'r' );
		$paged   = ap_sanitize_unslash( 'current', 'r', 1 ) + 1;

		$args['ap_current_user_ignore'] = true;
		$args['ignore_selected_answer'] = true;
		$args['showposts']              = 10;
		$args['author']                 = (int) $user_id;

		if ( false !== $paged ) {
			$args['paged'] = $paged;
		}

		/**
		 * Filter authors question list args
		 *
		 * @param array $args WP_Query arguments.
		 */
		$args               = apply_filters( 'ap_user_answers_args', $args );
		anspress()->answers = $answers = new \Answers_Query( $args );

		ob_start();
		if ( ap_have_answers() ) {
			/* Start the Loop */
			while ( ap_have_answers() ) :
				ap_the_answer();
				ap_get_template_part( 'addons/user/answer-item' );
			endwhile;
		}
		$html = ob_get_clean();

		ap_ajax_json(
			array(
				'success' => true,
				'element' => '#ap-bp-answers',
				'args'    => array(
					'ap_ajax_action' => 'user_more_answers',
					'__nonce'        => wp_create_nonce( 'loadmore-answers' ),
					'type'           => 'answers',
					'current'        => $paged,
					'user_id'        => $user_id,
				),
				'html'    => $html,
			)
		);
	}

	/**
	 * Override current page of AnsPress.
	 *
	 * @param string $query_var Current page name.
	 * @return string
	 * @since 4.1.0
	 */
	public function ap_current_page( $query_var ) {
		if ( is_author() && 'user' === get_query_var( 'ap_page' ) ) {
			$query_var = 'user';
		}

		return $query_var;
	}

	/**
	 * Modify main query.
	 *
	 * @param  array  $posts  Array of post object.
	 * @param  object $query Wp_Query object.
	 * @return void|array
	 * @since 4.1.0
	 * @since 4.1.1 Redirect to current user profile if no author set.
	 * @since 4.1.2 Check for 404 error.
	 */
	public function modify_query_archive( $posts, $query ) {
		if ( $query->is_main_query() && ! $query->is_404 && 'user' === get_query_var( 'ap_page' ) ) {
			$query_object = get_queried_object();

			if ( ! $query_object && ! get_query_var( 'author_name' ) && is_user_logged_in() ) {
				wp_safe_redirect( ap_user_link( get_current_user_id() ) );
				exit;
			} elseif ( $query_object && $query_object instanceof \WP_User ) {
				return [ get_post( ap_opt( 'user_page' ) ) ];
			} else {
				$query->set_404();
				status_header( 404 );
			}
		}

		return $posts;
	}

	/**
	 * Override user page template.
	 *
	 * @param string $template Template file.
	 * @return string
	 * @since 4.1.0
	 */
	public function page_template( $template ) {
		if ( is_author() && 'user' === get_query_var( 'ap_page' ) ) {
			$user_slug = ap_opt( 'user_page_id' );
			return locate_template( [ 'page-' . $user_slug . '.php', 'page.php' ] );
		}

		return $template;
	}

	/**
	 * Get current user id for AnsPress profile.
	 *
	 * @return integer
	 * @since 4.1.0
	 */
	public function current_user_id() {
		$query_object = get_queried_object();
		$user_id      = get_queried_object_id();

		// Current user id if queried object is not set.
		if ( ! $query_object instanceof \WP_User || empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		return (int) $user_id;
	}

}

// Init addon.
Profile::init();
