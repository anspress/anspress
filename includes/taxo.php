<?php
/**
 * AnsPress taxonomies and terms functions.
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
 * Output question categories
 *
 * @param  array $args Arguments.
 * @return string|void
 */
function ap_question_categories_html( $args = array() ) {
	$defaults = array(
		'question_id' => get_the_ID(),
		'list'        => false,
		'tag'         => 'span',
		'class'       => 'question-categories',
		'label'       => __( 'Categories', 'anspress-question-answer' ),
		'echo'        => false,
	);

	if ( ! is_array( $args ) ) {
		$defaults['question_id'] = $args;
		$args                    = $defaults;
	} else {
		$args = wp_parse_args( $args, $defaults );
	}

	$cats = get_the_terms( $args['question_id'], 'question_category' );

	if ( $cats ) {
		$o = '';
		if ( $args['list'] ) {
			$o .= '<ul class="' . $args['class'] . '">';
			foreach ( $cats as $c ) {
				$o .= '<li><a href="' . esc_url( get_term_link( $c ) ) . '" data-catid="' . $c->term_id . '" title="' . $c->description . '">' . $c->name . '</a></li>';
			}
			$o .= '</ul>';
		} else {
			$o .= $args['label'];
			$o .= '<' . $args['tag'] . ' class="' . $args['class'] . '">';
			foreach ( $cats as $c ) {
				$o .= '<a data-catid="' . $c->term_id . '" href="' . esc_url( get_term_link( $c ) ) . '" title="' . $c->description . '">' . $c->name . '</a>';
			}
			$o .= '</' . $args['tag'] . '>';
		}

		if ( $args['echo'] ) {
			echo $o; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $o;
	}
}

/**
 * Get category details.
 */
function ap_category_details() {
	$var      = get_query_var( 'question_category' );
	$category = get_term_by( 'slug', $var, 'question_category' );

	echo '<div class="clearfix">';
	echo '<h3><a href="' . esc_url( get_category_link( $category ) ) . '">' . esc_html( $category->name ) . '</a></h3>';
	echo '<div class="ap-taxo-meta">';
	echo '<span class="count">' . (int) $category->count . ' ' . esc_attr__( 'Questions', 'anspress-question-answer' ) . '</span>';
	echo '<a class="aicon-rss feed-link" href="' . esc_url( get_term_feed_link( $category->term_id, 'question_category' ) ) . '" title="Subscribe to ' . esc_attr( $category->name ) . '" rel="nofollow"></a>';
	echo '</div>';
	echo '</div>';

	echo '<p class="desc clearfix">' . wp_kses_post( $category->description ) . '</p>';

	$child = get_terms(
		array(
			'taxonomy'     => 'question_category',
			'parent'       => $category->term_id,
			'hierarchical' => false,
			'hide_empty'   => false,
		)
	);

	if ( $child ) :
		echo '<ul class="ap-child-list clearfix">';
		foreach ( $child as $key => $c ) :
			echo '<li><a class="taxo-title" href="' . esc_url( get_category_link( $c ) ) . '">' . esc_html( $c->name ) . '<span>' . (int) $c->count . '</span></a>';
			echo '</li>';
		endforeach;
		echo '</ul>';
	endif;
}

/**
 * Display sub categories list.
 *
 * @param int $parent_id Parent id.
 */
function ap_sub_category_list( $parent_id ) {
	$categories = get_terms(
		array(
			'taxonomy'   => 'question_category',
			'parent'     => $parent_id,
			'hide_empty' => false,
		)
	);

	if ( $categories ) {
		echo '<ul class="ap-category-subitems ap-ul-inline clearfix">';
		foreach ( $categories as $cat ) {
			echo '<li><a href="' . esc_url( get_category_link( $cat ) ) . '">' . esc_html( $cat->name ) . '<span>(' . (int) $cat->count . ')</span></a></li>';
		}
		echo '</ul>';
	}
}

/**
 * Check if question have category.
 *
 * @param false|int $post_id Question id.
 * @return bool
 */
function ap_question_have_category( $post_id = false ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$categories = get_the_terms( $post_id, 'question_category' );
	if ( ! empty( $categories ) ) {
		return true;
	}

	return false;
}


if ( ! function_exists( 'is_question_categories' ) ) {
	/**
	 * Check if anspress categories page.
	 *
	 * @return boolean
	 * @since  1.0
	 */
	function is_question_categories() {
		if ( 'categories' === ap_current_page() ) {
			return true;
		}

		return false;
	}
}

/**
 * Check if current page is question category.
 *
 * @return boolean
 * @since 4.0.0
 */
function is_question_category() {
	if ( 'category' === ap_current_page() ) {
		return true;
	}

	return false;
}


/**
 * Return category for sorting dropdown.
 *
 * @param string|boolean $search Search value.
 * @return array|boolean
 */
function ap_get_category_filter( $search = false ) {
	$args = array(
		'taxonomy'      => 'question_category',
		'hierarchical'  => true,
		'hide_if_empty' => true,
		'number'        => 10,
	);

	if ( false !== $search ) {
		$args['search'] = $search;
	}

	$terms    = get_terms( $args );
	$selected = ap_get_current_list_filters( 'category' );

	if ( ! $terms ) {
		return false;
	}

	$items = array();

	foreach ( (array) $terms as $t ) {
		$item = array(
			'key'   => 'category',
			'value' => (string) $t->term_id,
			'label' => $t->name,
		);
		// Check if active.
		if ( $selected && in_array( $t->term_id, $selected, true ) ) {
			$item['active'] = true;
		}

		$items[] = $item;
	}

	return $items;
}

/**
 * Output category filter dropdown.
 */
function ap_category_sorting() {
	$filters  = ap_get_category_filter();
	$selected = (int) ap_sanitize_unslash( 'ap_cat_sort', 'g', 0 );
	if ( $filters ) {
		echo '<div class="ap-dropdown">';
		echo '<a id="ap-sort-anchor" class="ap-dropdown-toggle' . ( '' !== $selected ? ' active' : '' ) . '" href="#">' . esc_attr__( 'Category', 'anspress-question-answer' ) . '</a>';
		echo '<div class="ap-dropdown-menu">';

		foreach ( $filters as $category_id => $category_name ) {
			echo '<li ' . ( $selected === (int) $category_id ? 'class="active" ' : '' ) . '><a href="#" data-value="' . (int) $category_id . '">' . esc_html( $category_name ) . '</a></li>';
		}
		echo '<input name="ap_cat_sort" type="hidden" value="' . esc_attr( $selected ) . '" />';
		echo '</div>';
		echo '</div>';
	}
}

/**
 * Return category image.
 *
 * @param  integer $term_id Category ID.
 * @param  integer $height  image height, without PX.
 */
function ap_get_category_image( $term_id, $height = 32 ) {
	$option = get_term_meta( $term_id, 'ap_category', true );
	$color  = ! empty( $option['color'] ) ? ' background:' . $option['color'] . ';' : 'background:#333;';

	$style = 'style="' . $color . 'height:' . $height . 'px;"';

	if ( ! empty( $option['image']['id'] ) ) {
		$image = wp_get_attachment_image( $option['image']['id'], array( 900, $height ) );
		return $image;
	}

	return '<div class="ap-category-defimage" ' . $style . '></div>';
}

/**
 * Output category image.
 *
 * @param  integer $term_id Category ID.
 * @param  integer $height  image height, without PX.
 */
function ap_category_image( $term_id, $height = 32 ) {
	echo ap_get_category_image( $term_id, $height ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Return category icon.
 *
 * @param  integer $term_id     Term ID.
 * @param  string  $attributes  Custom attributes.
 */
function ap_get_category_icon( $term_id, $attributes = '' ) {
	$option = get_term_meta( $term_id, 'ap_category', true );
	$color  = ! empty( $option['color'] ) ? ' background:' . $option['color'] . ';' : '';

	$style = 'style="' . $color . '"' . $attributes;

	if ( ! empty( $option['icon'] ) ) {
		return '<span class="ap-category-icon ' . $option['icon'] . '"' . $style . '></span>';
	}
}

/**
 * Output category icon.
 *
 * @param  integer $term_id     Term ID.
 * @param  string  $attributes  Custom attributes.
 */
function ap_category_icon( $term_id, $attributes = '' ) {
	echo ap_get_category_icon( $term_id, $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Slug for categories page.
 *
 * @return string
 * @since 4.1.0 Use new option categories_page_id.
 */
function ap_get_categories_slug() {
	return ap_opt( 'categories_page_id' );
}

/**
 * Slug for category page.
 *
 * @return string
 */
function ap_get_category_slug() {
	return apply_filters( 'ap_category_slug', ap_get_page_slug( 'category' ) );
}

/**
 * Check if category have featured image.
 *
 * @param  integer $term_id Term ID.
 * @return boolean
 * @since  2.0.2
 */
function ap_category_have_image( $term_id ) {
	$option = get_term_meta( $term_id, 'ap_category', true );
	if ( ! empty( $option['image']['id'] ) ) {
		return true;
	}

	return false;
}


/**
 * Output tags html.
 *
 * @param  array $args Arguments.
 * @return string|void
 *
 * @since  1.0
 */
function ap_question_tags_html( $args = array() ) {
	$defaults = array(
		'question_id' => get_the_ID(),
		'list'        => false,
		'tag'         => 'span',
		'class'       => 'question-tags',
		'label'       => __( 'Tagged', 'anspress-question-answer' ),
		'echo'        => false,
		'show'        => 0,
	);

	if ( ! is_array( $args ) ) {
		$defaults['question_id '] = $args;
		$args                     = $defaults;
	} else {
		$args = wp_parse_args( $args, $defaults );
	}

	$tags = get_the_terms( $args['question_id'], 'question_tag' );

	if ( $tags && count( $tags ) > 0 ) {
		$o = '';
		if ( $args['list'] ) {
			$o .= '<ul class="' . $args['class'] . '" itemprop="keywords">';
			foreach ( $tags as $t ) {
				$o .= '<li><a href="' . esc_url( get_term_link( $t ) ) . '" title="' . $t->description . '">' . $t->name . ' &times; <i class="tax-count">' . $t->count . '</i></a></li>';
			}
			$o .= '</ul>';
		} else {
			$o .= $args['label'];
			$o .= '<' . $args['tag'] . ' class="' . $args['class'] . '" itemprop="keywords">';
			$i  = 1;
			foreach ( $tags as $t ) {
				$o .= '<a href="' . esc_url( get_term_link( $t ) ) . '" title="' . $t->description . '">' . $t->name . '</a> ';
				++$i;
			}
			$o .= '</' . $args['tag'] . '>';
		}

		if ( $args['echo'] ) {
			echo $o; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $o;
	}
}

/**
 * Display tag details.
 *
 * @return void
 */
function ap_tag_details() {
	$var = get_query_var( 'question_tag' );

	$tag = get_term_by( 'slug', $var, 'question_tag' );

	echo '<div class="clearfix">';
	echo '<h3><a href="' . esc_url( get_tag_link( $tag ) ) . '">' . esc_html( $tag->name ) . '</a></h3>';
	echo '<div class="ap-taxo-meta">';
	echo '<span class="count">' . (int) $tag->count . ' ' . esc_attr__( 'Questions', 'anspress-question-answer' ) . '</span>';
	echo '<a class="aicon-rss feed-link" href="' . esc_url( get_term_feed_link( $tag->term_id, 'question_tag' ) ) . '" title="Subscribe to ' . esc_attr( $tag->name ) . '" rel="nofollow"></a>';
	echo '</div>';
	echo '</div>';

	echo '<p class="desc clearfix">' . wp_kses_post( $tag->description ) . '</p>';
}

/**
 * Check if question have tags.
 *
 * @param false|int $question_id Question id.
 * @return bool
 */
function ap_question_have_tags( $question_id = false ) {
	if ( ! $question_id ) {
		$question_id = get_the_ID(); }

	$tags = wp_get_post_terms( $question_id, 'question_tag' );

	if ( ! empty( $tags ) ) {
		return true; }

	return false;
}

/**
 * Check if question tag page.
 *
 * @return bool
 */
function is_question_tag() {
	if ( 'tag' === ap_current_page() ) {
		return true;
	}

	return false;
}

/**
 * Check if question tags page.
 *
 * @return bool
 */
function is_question_tags() {
	if ( 'tags' === ap_current_page() ) {
		return true;
	}

	return false;
}

/**
 * Return category for sorting dropdown.
 *
 * @param string|false $search Search query.
 * @return array|boolean
 */
function ap_get_tag_filter( $search = false ) {
	$args = array(
		'taxonomy'      => 'question_tag',
		'hierarchical'  => true,
		'hide_if_empty' => true,
		'number'        => 10,
	);

	if ( false !== $search ) {
		$args['search'] = $search;
	}

	$terms    = get_terms( $args );
	$selected = ap_get_current_list_filters( 'qtag' );

	if ( ! $terms ) {
		return false;
	}

	$items = array();

	foreach ( (array) $terms as $t ) {
		$item = array(
			'key'   => 'qtag',
			'value' => (string) $t->term_id,
			'label' => $t->name,
		);

		// Check if active.
		if ( $selected && in_array( $t->term_id, $selected, true ) ) {
			$item['active'] = true;
		}

		$items[] = $item;
	}

	return $items;
}

/**
 * Slug for tag page.
 *
 * @return string
 */
function ap_get_tag_slug() {
	return apply_filters( 'ap_tag_slug', ap_get_page_slug( 'tag' ) );
}

/**
 * Slug for tag page.
 *
 * @return string
 */
function ap_get_tags_slug() {
	return apply_filters( 'ap_tags_slug', ap_get_page_slug( 'tags' ) );
}
