<?php

/**
 * Output tags html
 * @param  array $args
 * @return string
 * @since 1.0
 */
function ap_question_tags_html($args = array()) {

	$defaults  = array(
		'question_id'   => get_the_ID(),
		'list'           => false,
		'tag'           => 'span',
		'class'         => 'question-tags',
		'label'         => __( 'Tagged', 'tags-for-anspress' ),
		'echo'          => false,
		'show'          => 0,
	);

	if ( ! is_array( $args ) ) {
		$defaults['question_id '] = $args;
		$args = $defaults;
	} else {
		$args = wp_parse_args( $args, $defaults );
	}

	$tags = get_the_terms( $args['question_id'], 'question_tag' );

	if ( $tags && count( $tags ) > 0 ) {
		$o = '';
		if ( $args['list'] ) {
			$o = '<ul class="'.$args['class'].'">';
			foreach ( $tags as $t ) {
				$o .= '<li><a href="'.esc_url( get_term_link( $t ) ).'" title="'.$t->description.'">'. $t->name .' &times; <i class="tax-count">'.$t->count.'</i></a></li>';
			}
			$o .= '</ul>';
		} else {
			$o = $args['label'];
			$o .= '<'.$args['tag'].' class="'.$args['class'].'">';
			$i = 1;
			foreach ( $tags as $t ) {
				$o .= '<a href="'.esc_url( get_term_link( $t ) ).'" title="'.$t->description.'">'. $t->name .'</a> ';
				/*
                if($args['show'] > 0 && $i == $args['show']){
                    $o_n = '';
                    foreach($tags as $tag_n)
                        $o_n .= '<a href="'.esc_url( get_term_link($tag_n)).'" title="'.$tag_n->description.'">'. $tag_n->name .'</a> ';

                    $o .= '<a class="ap-tip" data-tipclass="tags-list" title="'.esc_html($o_n).'" href="#">'. sprintf(__('%d More', 'tags-for-anspress'), count($tags)) .'</a>';
                    break;
                }*/
				$i++;
			}
			$o .= '</'.$args['tag'].'>';
		}

		if ( $args['echo'] ) {
			echo $o; }

		return $o;
	}
}


function ap_tag_details() {

	$var = get_query_var( 'question_tag' );

	$tag = get_term_by( 'slug', $var, 'question_tag' );
	echo '<div class="clearfix">';
	echo '<h3><a href="'.get_tag_link( $tag ).'">'. $tag->name .'</a></h3>';
	echo '<div class="ap-taxo-meta">';
	echo '<span class="count">'. $tag->count .' '.__( 'Questions', 'tags-for-anspress' ).'</span>';
	echo '<a class="aicon-rss feed-link" href="' . get_term_feed_link( $tag->term_id, 'question_tag' ) . '" title="Subscribe to '. $tag->name .'" rel="nofollow"></a>';
	echo '</div>';
	echo '</div>';

	echo '<p class="desc clearfix">'. $tag->description .'</p>';
}

function ap_question_have_tags($question_id = false) {
	if ( ! $question_id ) {
		$question_id = get_the_ID(); }

	$tags = wp_get_post_terms( $question_id, 'question_tag' );

	if ( ! empty( $tags ) ) {
		return true; }

	return false;
}


if ( ! function_exists( 'is_question_tag' )) {
	function is_question_tag() {

		if ( ap_get_tag_slug() == get_query_var( 'ap_page' ) ) {
			return true;
		}

		return false;
	}
}

if ( ! function_exists( 'is_question_tags' ) ) {
	function is_question_tags() {

		if ( ap_get_tags_slug() == get_query_var( 'ap_page' ) ) {
			return true;
		}

		return false;
	}
}

function ap_tag_sorting() {
	$args = array(
		'hierarchical'      => true,
		'hide_if_empty'     => true,
		'number'            => 10,
	);

	$terms = get_terms( 'question_tag', $args );

	$selected = isset( $_GET['ap_tag_sort'] ) ? sanitize_text_field( $_GET['ap_tag_sort'] ) : '';

	if ( $terms ) {
		echo '<div class="ap-dropdown">';
			echo '<a id="ap-sort-anchor" class="ap-dropdown-toggle'.($selected != '' ? ' active' : '').'" href="#">'.__( 'Tags', 'tags-for-anspress' ).'</a>';
			echo '<div class="ap-dropdown-menu">';
		foreach ( $terms as $t ) {
			echo '<li '.($selected == $t->term_id ? 'class="active" ' : '').'><a href="#" data-value="'.$t->term_id.'">'. $t->name .'</a></li>';
		}
			echo '<input name="ap_tag_sort" type="hidden" value="'.$selected.'" />';
			echo '</div>';
		echo '</div>';
	}
}

function ap_tags_tab() {
	$active = isset( $_GET['ap_sort'] ) ? $_GET['ap_sort'] : 'popular';

	$link = ap_get_link_to( 'tags' ).'?ap_sort=';

	?>
    <ul class="ap-questions-tab ap-ul-inline clearfix" role="tablist">
        <li class="<?php echo $active == 'popular' ? ' active' : ''; ?>"><a href="<?php echo $link.'popular'; ?>"><?php _e( 'Popular', 'tags-for-anspress' ); ?></a></li>
        <li class="<?php echo $active == 'new' ? ' active' : ''; ?>"><a href="<?php echo $link.'new'; ?>"><?php _e( 'New', 'tags-for-anspress' ); ?></a></li>
        <li class="<?php echo $active == 'name' ? ' active' : ''; ?>"><a href="<?php echo $link.'name'; ?>"><?php _e( 'Name', 'tags-for-anspress' ); ?></a></li>
        <?php
			/**
			 * ACTION: ap_tags_tab
			 * Used to hook into tags page tab
			 */
			do_action( 'ap_tags_tab', $active );
		?>
    </ul>
    <?php
}

/**
 * Slug for tag page
 * @return string
 */
function ap_get_tag_slug() {
	$slug = ap_opt('tag_page_slug');
	$slug = sanitize_title( $slug );

	if(empty($slug)){
		$slug = 'tag';
	}
	/**
	 * FILTER: ap_tag_slug
	 */
	return apply_filters( 'ap_tag_slug', $slug );
}

/**
 * Slug for tag page
 * @return string
 */
function ap_get_tags_slug() {
	$slug = ap_opt('tags_page_slug');
	$slug = sanitize_title( $slug );

	if(empty($slug)){
		$slug = 'tags';
	}
	/**
	 * FILTER: ap_tag_slug
	 */
	return apply_filters( 'ap_tags_slug', $slug );
}
