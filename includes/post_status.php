<?php

/**
 * Post status related codes
 *
 * @link https://anspress.io
 * @since 2.0.1
 * @license GPL2+
 * @package AnsPress
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class AnsPress_Post_Status {

	/**
	 * Register post status for question and answer CPT
	 */
	public static function register_post_status() {
		 register_post_status( 'moderate', array(
			  'label'                     => __( 'Moderate', 'anspress-question-answer' ),
			  'public'                    => true,
			  'show_in_admin_all_list'    => false,
			  'show_in_admin_status_list' => true,
			  'label_count'               => _n_noop( 'Moderate <span class="count">(%s)</span>', 'Moderate <span class="count">(%s)</span>', 'anspress-question-answer' ),
		 ) );

		 register_post_status( 'private_post', array(
			  'label'                     => __( 'Private', 'anspress-question-answer' ),
			  'public'                    => true,
			  'show_in_admin_all_list'    => false,
			  'show_in_admin_status_list' => true,
			  'label_count'               => _n_noop( 'Private Post <span class="count">(%s)</span>', 'Private Post <span class="count">(%s)</span>', 'anspress-question-answer' ),
		 ) );
	}

	/**
	 * Handle change post status ajax request.
	 *
	 * @since 2.1
	 */
	public static function change_post_status() {
		$args = ap_sanitize_unslash( 'args', 'request' );

		if ( empty( $args ) ) {
			ap_ajax_json('something_wrong' );
		}

	    $post_id = (int) $args[0];
	    $status = $args[1];

	    // Die if not a defined post status.
	   	if ( ! in_array( $status, [ 'publish', 'moderate', 'private_post'], true ) ) {
	   		ap_ajax_json( 'something_wrong' );
	   	}

	    // Check if user has permission else die.
	    if ( ! is_user_logged_in() || ! ap_verify_nonce( 'change_post_status_'.$post_id ) || ! ap_user_can_change_status( $post_id ) ) {
	        ap_ajax_json('no_permission' );
	    }

		$post = ap_get_post( $post_id );

	   	// Check if post is question or answer and new post status is not same as old.
	   	if ( ! in_array( $post->post_type, [ 'question', 'answer' ] ) || $post->post_status == $status ) {
			ap_ajax_json('something_wrong' );
		}

	   	$update_data = array();
	   	$update_data['post_status'] = $status;
		$update_data['ID'] = $post->ID;
		wp_update_post( $update_data );

		// Unselect as best answer if moderate.
		if ( 'answer' == $post->post_type && 'moderate' == $status && ap_have_answer_selected( $post->post_parent ) ) {
			ap_unselect_answer( $post->ID );
		}

		do_action( 'ap_post_status_updated', $post->ID );

		$activity_type = 'moderate' === $post->post_status ? 'approved_' . $post->post_type : 'changed_status';
		ap_update_post_activity_meta( $post_id, $activity_type, get_current_user_id() );

		ob_start();
		ap_post_status_description( $post->ID );
		$html = ob_get_clean();

		ap_ajax_json( array(
			'action' 		=> 'status_updated',
			'message' 		=> 'status_updated',
			'do' 			=> array(
				'remove_if_exists' => '#ap_post_status_desc_'.$post->ID,
				'toggle_active_class' => array( '#ap_post_status_toggle_'.$post->ID, '.'.$status ),
				'append_before' => '#ap_post_actions_'.$post->ID,
			),
			'html' 			=> $html,
		));
	}
}

/**
 * Output chnage post status button.
 *
 * @param 	boolean|integer $post_id Post ID.
 * @return 	null|string
 */
function ap_post_change_status_btn_html( $post_id = false ) {
	$post = ap_get_post( $post_id );

	if ( ap_user_can_change_status( $post_id ) ) {
		$action = 'change_post_status_' . $post_id;
		$nonce = wp_create_nonce( $action );

		$status = apply_filters( 'ap_change_status_dropdown', array(
			'publish' 		=> __( 'Publish', 'anspress-question-answer' ),
			'moderate' 		=> __( 'Moderate', 'anspress-question-answer' ),
			'private_post' 	=> __( 'Private', 'anspress-question-answer' ),
		) );

		$output = '<div class="ap-dropdown">
			<a class="ap-tip ap-dropdown-toggle" title="' . __( 'Change status of post', 'anspress-question-answer' ) . '" href="#">
				' . __( 'Status', 'anspress-question-answer' ) . ' <i class="caret"></i>
            </a>
			<ul id="ap_post_status_toggle_' . $post_id . '" class="ap-dropdown-menu" role="menu">';

		foreach ( (array) $status as $k => $title ) {
			$can = true;

			if ( 'moderate' === $k  && ! ap_user_can_change_status_to_moderate() ) {
				$can = false;
			}

			if ( $can ) {
				$output .= '<li class="' . $k . ( $k === $post->post_status ? ' active' : '' ) . '">
						<a href="#" data-action="ajax_btn" data-query="change_post_status::' . $nonce . '::' . $post_id . '::' . $k . '">' . esc_attr( $title ) . '</a>
					</li>';
			}
		}
		$output .= '</ul>
		</div>';

		return $output;
	}
}

/**
 * Return description of a post status.
 *
 * @param  boolean|integer $post_id Post ID.
 */
function ap_post_status_description( $post_id = false ) {
	$post = ap_get_post( $post_id );
	$post_type = 'question' === $post->post_type ? __( 'Question', 'anspress-question-answer' ) : __( 'Answer', 'anspress-question-answer' );

	if ( ap_have_parent_post( $post_id ) && 'answer' !== $post->post_type  ) : ?>
		<div id="ap_post_status_desc_<?php echo esc_attr( $post_id ); ?>" class="ap-notice blue clearfix">
			<?php echo ap_icon( 'link', true ) ?>
			<span><?php printf( __( 'Question is asked for %s.', 'anspress-question-answer' ), '<a href="' . get_permalink( ap_get_post_field('post_parent') ) . '">' . get_the_title( ap_get_post_field('post_parent') ) . '</a>' ); ?></span>
		</div>
	<?php endif;

	if ( is_private_post( $post_id ) ) : ?>
        <div id="ap_post_status_desc_<?php echo $post_id; ?>" class="ap-notice gray clearfix">
            <i class="apicon-lock"></i>
            <span><?php printf( __( '%s is marked as a private, only admin and post author can see.', 'anspress-question-answer' ), $post_type ); ?></span>
        </div>
    <?php endif;

	if ( is_post_waiting_moderation( $post_id ) ) : ?>
        <div id="ap_post_status_desc_<?php echo $post_id; ?>" class="ap-notice yellow clearfix">
            <i class="apicon-info"></i><span><?php printf( __( '%s is waiting for approval by moderator.', 'anspress-question-answer' ), $post_type ); ?></span>
        </div>
    <?php endif;

	if ( is_post_closed( $post_id ) && $post->post_type != 'answer' ) : ?>
        <div id="ap_post_status_desc_<?php echo $post_id; ?>" class="ap-notice red clearfix">
            <?php echo ap_icon( 'cross', true ) ?>
            <span><?php printf( __( '%s is closed for new answers', 'anspress-question-answer' ), $post_type ); ?></span>
        </div>
    <?php endif;

	if ( $post->post_status == 'trash' ) : ?>
        <div id="ap_post_status_desc_<?php echo $post_id; ?>" class="ap-notice red clearfix">
            <?php echo ap_icon( 'cross', true ) ?>
            <span><?php printf( __( '%s has been trashed, you can delete it permanently from wp-admin.', 'anspress-question-answer' ), $post_type ); ?></span>
        </div>
    <?php endif;
}
