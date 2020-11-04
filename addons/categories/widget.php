<?php
/**
 * AnsPress category widget.
 *
 * @package      AnsPress
 * @subpackage   Categories Addon
 * @author       Rahul Aryan <rah12@live.com>
 * @license      GPL-3.0+
 * @link         https://anspress.net
 * @copyright    2014 Rahul Aryan
 */

namespace Anspress\Widgets;

/**
 * Register AnsPress category widget.
 *
 * @since 4.1.8
 */
class Categories extends \WP_Widget {
	/**
	 * Construct
	 */
	public function __construct() {
		// Instantiate the parent object.
		parent::__construct(
			'AnsPress_Category_Widget',
			'(AnsPress) Categories',
			array( 'description', __( 'Display AnsPress categories', 'anspress-question-answer' ) )
		);
	}

	public function widget( $args, $instance ) {
		/**
		 * This filter is documented in widgets/question_stats.php
		 */
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$cat_args = array(
			'parent'     => $instance['parent'],
			'number'     => $instance['number'],
			'hide_empty' => $instance['hide_empty'],
			'orderby'    => $instance['orderby'],
			'order'      => $instance['order'],
		);

		$icon_width  = ! empty( $instance['icon_width'] ) ? $instance['icon_width'] : 32;
		$icon_height = ! empty( $instance['icon_height'] ) ? $instance['icon_height'] : 32;

		$categories = get_terms( 'question_category', $cat_args );
		?>
	<ul id="ap-categories-widget" class="ap-cat-wid clearfix">
		<?php
		foreach ( (array) $categories as $key => $category ) :
			$ap_category   = get_term_meta( $category->term_id, 'ap_category', true );
			$ap_category   = wp_parse_args(
				$ap_category, [
					'color' => '#333',
					'icon'  => 'apicon-category',
				]
			);
			$sub_cat_count = count( get_term_children( $category->term_id, 'question_category' ) );
			?>
			<li class="clearfix">
			<a class="ap-cat-image" style="height:<?php echo $icon_height; ?>px;width:<?php echo $icon_width; ?>px;background: <?php echo esc_attr( $ap_category['color'] ); ?>"
			  href="<?php echo get_category_link( $category ); ?>">
					<span class="ap-category-icon <?php echo esc_attr( $ap_category['icon'] ); ?>"></span>
				</a>
			<a class="ap-cat-wid-title" href="<?php echo get_category_link( $category ); ?>">
				<?php echo $category->name; ?>
			</a>
			<div class="ap-cat-count">
				<span><?php printf( _n( '%d Question', '%d Questions', $category->count, 'anspress-question-answer' ), $category->count ); ?></span>
				<?php if ( $sub_cat_count > 0 ) : ?>
					<span><?php printf( __( '%d Child', 'anspress-question-answer' ), $sub_cat_count ); ?></span>
					<?php endif; ?>
			</div>
			</li>
			<?php endforeach; ?>
		</ul>
	<?php
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title       = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Categories', 'anspress-question-answer' );
		$hide_empty  = ! empty( $instance['hide_empty'] ) ? $instance['hide_empty'] : false;
		$parent      = ! empty( $instance['parent'] ) ? $instance['parent'] : 0;
		$number      = ! empty( $instance['number'] ) ? $instance['number'] : 10;
		$orderby     = ! empty( $instance['orderby'] ) ? $instance['orderby'] : 'count';
		$order       = ! empty( $instance['order'] ) ? $instance['order'] : 'DESC';
		$icon_height = ! empty( $instance['icon_height'] ) ? $instance['icon_height'] : '32';
		$icon_width  = ! empty( $instance['icon_width'] ) ? $instance['icon_width'] : '32';

		$cat_args = array(
			'hide_empty' => false,
			'orderby'    => 'count',
			'order'      => 'DESC',
		);

		$categories = get_terms( 'question_category', $cat_args );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'anspress-question-answer' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>"
			  type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'hide_empty' ); ?>"><?php _e( 'Hide empty:', 'anspress-question-answer' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'hide_empty' ); ?>" name="<?php echo $this->get_field_name( 'hide_empty' ); ?>"
			  type="checkbox" value="1" <?php checked( true, $hide_empty ); ?>>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'parent' ); ?>"><?php _e( 'Parent:', 'anspress-question-answer' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'parent' ); ?>" name="<?php echo $this->get_field_name( 'parent' ); ?>">
				<option value="0"><?php _e( 'Top level', 'anspress-question-answer' ); ?></option>
				<?php
				if ( $categories ) {
					foreach ( $categories as $c ) {
						echo '<option value="' . $c->term_id . '" ' . selected( $parent, $c->term_id ) . '>' . $c->name . '</option>'; }
				}

				?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number:', 'anspress-question-answer' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>"
			  type="text" value="<?php echo esc_attr( $number ); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'orderby' ); ?>"><?php _e( 'Order By:', 'anspress-question-answer' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'orderby' ); ?>" name="<?php echo $this->get_field_name( 'orderby' ); ?>">
				<option value="none" <?php echo selected( $orderby, 'none' ); ?>><?php _e( 'None', 'anspress-question-answer' ); ?></option>
				<option value="count" <?php echo selected( $orderby, 'count' ); ?>><?php _e( 'Count', 'anspress-question-answer' ); ?></option>
				<option value="id" <?php echo selected( $orderby, 'id' ); ?>><?php _e( 'ID', 'anspress-question-answer' ); ?></option>
				<option value="name" <?php echo selected( $orderby, 'name' ); ?>><?php _e( 'Name', 'anspress-question-answer' ); ?></option>
				<option value="slug" <?php echo selected( $orderby, 'slug' ); ?>><?php _e( 'Slug', 'anspress-question-answer' ); ?></option>
				<option value="term_group" <?php echo selected( $orderby, 'term_group' ); ?>><?php _e( 'Term group', 'anspress-question-answer' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php _e( 'Order:', 'anspress-question-answer' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>">
				<option value="DESC" <?php echo selected( $order, 'DESC' ); ?>><?php _e( 'DESC', 'anspress-question-answer' ); ?></option>
				<option value="ASC" <?php echo selected( $order, 'ASC' ); ?>><?php _e( 'ASC', 'anspress-question-answer' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'icon_width' ); ?>"><?php _e( 'Icon width:', 'anspress-question-answer' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'icon_width' ); ?>" name="<?php echo $this->get_field_name( 'icon_width' ); ?>"
			  type="text" value="<?php echo esc_attr( $icon_width ); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'icon_height' ); ?>"><?php _e( 'Icon height:', 'anspress-question-answer' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'icon_height' ); ?>" name="<?php echo $this->get_field_name( 'icon_height' ); ?>"
			  type="text" value="<?php echo esc_attr( $icon_height ); ?>">
		</p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                = array();
		$instance['title']       = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['hide_empty']  = ( ! empty( $new_instance['hide_empty'] ) ) ? strip_tags( $new_instance['hide_empty'] ) : false;
		$instance['parent']      = ( ! empty( $new_instance['parent'] ) ) ? strip_tags( $new_instance['parent'] ) : '0';
		$instance['number']      = ( ! empty( $new_instance['number'] ) ) ? strip_tags( $new_instance['number'] ) : '5';
		$instance['orderby']     = ( ! empty( $new_instance['orderby'] ) ) ? strip_tags( $new_instance['orderby'] ) : 'count';
		$instance['order']       = ( ! empty( $new_instance['order'] ) ) ? strip_tags( $new_instance['order'] ) : 'DESC';
		$instance['icon_width']  = ( ! empty( $new_instance['icon_width'] ) ) ? (int) strip_tags( $new_instance['icon_width'] ) : 32;
		$instance['icon_height'] = ( ! empty( $new_instance['icon_height'] ) ) ? (int) strip_tags( $new_instance['icon_height'] ) : 32;

		return $instance;
	}
}
