<?php
/**
 * Class for AnsPress base page shortcode
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      http://anspress.io
 * @copyright 2014 Rahul Aryan
 */

/**
 * Class for AnsPress base page shortcode
 */
class AnsPress_BasePage_Shortcode {

	protected static $instance = null;

	public static function get_instance() {

		// create an object
		null === self::$instance && self::$instance = new self;

		return self::$instance; // return the object
	}

	/**
	 * Control the output of [anspress] shortcode
	 *
	 * @param  array  $atts  {
	 *     Attributes of the shortcode.
	 *
	 *     $categories slug of question_category
	 *     $tags slug of question_tag
	 *     $tax_relation taxonomy relation, see here http://codex.wordpress.org/Taxonomies
	 *     $tags_operator operator for question_tag taxnomomy
	 *     $categories_operator operator for question_category taxnomomy
	 *  }
	 * @param  string $content
	 * @return string
	 * @since 2.0.0-beta
	 */
	public function anspress_sc( $atts, $content='' ) {
		global $questions, $wp;

		if ( isset( $atts['categories'] ) ) {
			$categories = explode( ',', str_replace( ', ', ',', $atts['categories'] ) );
			// append $atts in global $wp so that we can use it later
			$wp->set_query_var( 'ap_sc_atts_categories', $categories );
		}

		if ( isset( $atts['tags'] ) ) {
			$tags = explode( ',', str_replace( ', ', ',', $atts['tags'] ) );
			$wp->set_query_var( 'ap_sc_atts_tags', $tags );
		}

		if ( isset( $atts['tax_relation'] ) ) {
			$tax_relation = $atts['tax_relation'];
			$wp->set_query_var( 'ap_sc_atts_tax_relation', $tax_relation );
		}

		if ( isset( $atts['tags_operator'] ) ) {
			$tags_operator = $atts['tags_operator'];
			$wp->set_query_var( 'ap_sc_atts_tags_operator', $tags_operator );
		}

		if ( isset( $atts['categories_operator'] ) ) {
			$categories_operator = $atts['categories_operator'];
			$wp->set_query_var( 'ap_sc_atts_categories_operator', $categories_operator );
		}

		// Load specefic AnsPress page.
		if ( isset( $atts['page'] ) ) {
			set_query_var( 'ap_page', $atts['page'] );
			$_GET['ap_page'] = $atts['page'];
		}

		ob_start();
		echo '<div id="anspress">';

			/**
			 * ACTION: ap_before
			 * Action is fired before loading AnsPress body.
			 */
			do_action( 'ap_before' );

			// Include theme file.
			ap_page();

		if ( ! ap_opt( 'author_credits' ) ) {
			echo '<div class="ap-cradit">' . __( 'Question and answer is powered by <a href="http://anspress.io" traget="_blank">AnsPress</a>', 'anspress-question-answer' ) . '</div>'; }
		echo '</div>';
		wp_reset_postdata();

		return ob_get_clean();
	}

}

