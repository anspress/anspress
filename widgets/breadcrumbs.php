<?php
/**
 * AnsPress breadcrumbs widget
 *
 * @package AnsPress
 * @author  Rahul Aryan <rah12@live.com>
 * @license GPL 2+ GNU GPL licence above 2+
 * @link    https://anspress.net
 * @since   2.0.0
 */

// phpcs:disable Universal.Files.SeparateFunctionsFromOO.Mixed

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * AnsPress breadcrumbs widget.
 */
class AnsPress_Breadcrumbs_Widget extends WP_Widget {

	/**
	 * Initialize the class
	 */
	public function __construct() {
		parent::__construct(
			'ap_breadcrumbs_widget',
			__( '(AnsPress) Breadcrumbs', 'anspress-question-answer' ),
			array( 'description' => __( 'Show current anspress page navigation', 'anspress-question-answer' ) )
		);
	}


	/**
	 * Get breadcrumbs array
	 *
	 * @return array
	 */
	public static function get_breadcrumbs() {
		$current_page = ap_current_page();
		$title        = ap_page_title();
		$a            = array();

		$a['base'] = array(
			'title' => ap_opt( 'base_page_title' ),
			'link'  => ap_base_page_link(),
			'order' => 0,
		);

		$current_page = $current_page ? $current_page : '';

		if ( is_question() ) {
			$a['page'] = array(
				'title' => get_the_title(),
				'link'  => get_permalink( get_question_id() ),
				'order' => 10,
			);
		} elseif ( 'ask' === $current_page ) {
			$a['page'] = array(
				'title' => get_the_title(),
				'link'  => ap_get_link_to( 'ask' ),
				'order' => 10,
			);
		} elseif ( 'activities' === $current_page ) {
			$a['page'] = array(
				'title' => get_the_title(),
				'link'  => ap_get_link_to( 'activities' ),
				'order' => 10,
			);
		} elseif ( 'base' !== $current_page && '' !== $current_page ) {
			$a['page'] = array(
				'title' => $title,
				'link'  => $current_page,
				'order' => 10,
			);
		}

		$a = apply_filters( 'ap_breadcrumbs', $a );

		return is_array( $a ) ? ap_sort_array_by_order( $a ) : array();
	}

	/**
	 * Output AnsPress breadcrumbs.
	 *
	 * @return void
	 */
	public static function breadcrumbs() {
		$navs = self::get_breadcrumbs();

		echo '<ul class="ap-breadcrumbs clearfix">';
		echo '<li class="ap-breadcrumbs-home"><a href="' . esc_url( home_url( '/' ) ) . '" class="apicon-home"></a></li>';
		echo '<li><i class="apicon-chevron-right"></i></li>';

		$i         = 1;
		$total_nav = count( $navs );

		foreach ( $navs as $k => $nav ) {
			if ( ! empty( $nav ) ) {
				echo '<li>';
				echo '<a href="' . esc_url( $nav['link'] ) . '">' . esc_attr( $nav['title'] ) . '</a>';
				echo '</li>';

				if ( $total_nav !== $i ) {
					echo '<li><i class="apicon-chevron-right"></i></li>';
				}
			}
			++$i;
		}

		echo '</ul>';
	}

	/**
	 * Output widget.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Widget instance.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		echo wp_kses_post( $args['before_widget'] );
		self::breadcrumbs();
		echo wp_kses_post( $args['after_widget'] );
	}
}

/**
 * Register breadcrumbs widget.
 *
 * @return void
 */
function register_anspress_breadcrumbs() {
	register_widget( 'AnsPress_Breadcrumbs_Widget' );
}
add_action( 'widgets_init', 'register_anspress_breadcrumbs' );
