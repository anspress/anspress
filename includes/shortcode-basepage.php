<?php
/**
 * Class for AnsPress base page shortcode
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-2.0+
 * @link      https://anspress.net
 * @copyright 2014 Rahul Aryan
 */

/**
 * Class for AnsPress base page shortcode
 */
class AnsPress_BasePage_Shortcode {
	/**
	 * Instance.
	 *
	 * @var Instance
	 */
	protected static $instance = null;

	/**
	 * Get current instance.
	 */
	public static function get_instance() {

		// Create an object.
		null === self::$instance && self::$instance = new self();
		return self::$instance; // Return the object.
	}

	/**
	 * Current page
	 *
	 * @var string
	 * @since 4.1.9
	 */
	public $current_page = '';

	/**
	 * Control the output of [anspress] shortcode.
	 *
	 * @param array  $atts {
	 *     Attributes of the shortcode.
	 *
	 *     @type string  $categories                 Slug of question_category
	 *     @type string  $tags                       Slug of question_tag
	 *     @type string  $tax_relation           Taxonomy relation, see here @link https://codex.wordpress.org/Taxonomies
	 *     @type string  $tags_operator            Operator for question_tag taxonomy
	 *     @type string  $categories_operator  Operator for question_category taxonomy
	 *     @type string  $page                       Select a page to display.
	 *     @type boolean $hide_list_head           Hide list head?
	 *     @type string  $order_by                   Sort by.
	 *  }
	 * @param string $content Shortcode content.
	 * @return string
	 * @since 2.0.0
	 * @since 3.0.0 Added new attribute `hide_list_head` and `attr_order_by`.
	 */
	public function anspress_sc( $atts, $content = '' ) {
		global $ap_shortcode_loaded;

		// Drop current page variable cache. As we are allowing to change page from shortcode.
		wp_cache_delete( 'current_page', 'anspress' );

		// Check if AnsPress shortcode already loaded.
		if ( true === $ap_shortcode_loaded ) {
			return __( 'AnsPress shortcode cannot be nested.', 'anspress-question-answer' );
		}

		wp_enqueue_script( 'anspress-main' );
		wp_enqueue_script( 'anspress-theme' );
		wp_enqueue_style( 'anspress-main' );
		wp_enqueue_style( 'anspress-fonts' );

		$ap_shortcode_loaded = true;

		$this->attributes( $atts, $content );

		ob_start();
		echo '<div id="anspress" class="anspress">';

			/**
			 * Action is fired before loading AnsPress body.
			 */
			do_action( 'ap_before' );

			// Include theme file.
			ap_page( $this->current_page );

		echo '</div>';
		// Linkback to author.
		if ( ! ap_opt( 'author_credits' ) ) {
			echo '<div class="ap-cradit">' . esc_attr__( 'Question and answer is powered by', 'anspress-question-answer' ) . ' <a href="https://anspress.net" target="_blank">anspress.net</a></div>';
		}

		wp_reset_postdata();
		$ap_shortcode_loaded = false;
		return ob_get_clean();
	}

	/**
	 * Get attributes from shortcode and set it as query var.
	 *
	 * @since 3.0.0
	 * @since 4.1.8 Added `post_parent` attribute.
	 */
	public function attributes( $atts, $content ) {
		global $wp;

		if ( isset( $atts['categories'] ) ) {
			$categories = explode( ',', str_replace( ', ', ',', $atts['categories'] ) );
			$wp->set_query_var( 'ap_categories', $categories );
		}

		if ( isset( $atts['tags'] ) ) {
			$tags = explode( ',', str_replace( ', ', ',', $atts['tags'] ) );
			$wp->set_query_var( 'ap_tags', $tags );
		}

		if ( isset( $atts['tax_relation'] ) ) {
			$tax_relation = $atts['tax_relation'];
			$wp->set_query_var( 'ap_tax_relation', $tax_relation );
		}

		if ( isset( $atts['tags_operator'] ) ) {
			$tags_operator = $atts['tags_operator'];
			$wp->set_query_var( 'ap_tags_operator', $tags_operator );
		}

		if ( isset( $atts['categories_operator'] ) ) {
			$categories_operator = $atts['categories_operator'];
			$wp->set_query_var( 'ap_categories_operator', $categories_operator );
		}

		// Load specefic AnsPress page.
		if ( isset( $atts['page'] ) ) {
			$this->current_page = $atts['page'];
			set_query_var( 'ap_page', $atts['page'] );
			$_GET['ap_page'] = $atts['page'];
		}

		if ( isset( $atts['hide_list_head'] ) ) {
			set_query_var( 'ap_hide_list_head', (bool) $atts['hide_list_head'] );
			$_GET['ap_hide_list_head'] = $atts['hide_list_head'];
		}

		// Sort by.
		if ( isset( $atts['order_by'] ) ) {
			$_GET['filters'] = [ 'order_by' => $atts['order_by'] ];
		}

		// parent post.
		if ( isset( $atts['post_parent'] ) ) {
			set_query_var( 'post_parent', $atts['post_parent'] );
		}
	}
}
