<?php
/**
 * Control the output of post flag page
 *
 * @link https://anspress.io
 * @since 2.0.0-alpha2
 * @author Rahul Aryan <support@anspress.io>
 * @package AnsPress
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$post_id = (int) $_GET['post_id'];
global $post;

// Assign your post details to $post (& not any other variable name!!!!)
$post = get_post( $post_id );

setup_postdata( $post );

$flags = ap_get_all_meta(
	array(
	'where' => array(
		'apmeta_type' => array( 'value' => 'flag', 'compare' => '=', 'relation' => 'AND' ),
		'apmeta_actionid' => array( 'value' => $post->ID, 'compare' => '=', 'relation' => 'AND' ),
		),
	));

?>
<div id="ap-admin-dashboard" class="wrap">
	<?php do_action( 'ap_before_admin_page_title' ) ?>
	<h2><?php _e( 'Post flag', 'anspress-question-answer' ) ?></h2>

    <div class="ap-admin-container">
        <div class="ap-flag-post-content">
			<h1><?php the_title() ?></h1>
            <div class="ap-admin-sub">
				<span><?php printf( __( 'Total <b>%d flag</b>', 'anspress-question-answer' ), ap_post_flag_count() ) ?></span>
				<span> | <a href="<?php echo get_edit_post_link( get_the_ID() ) ?>"><?php _e( 'Edit post', 'anspress-question-answer' ) ?></a></span>
				<span> | <a href="<?php echo get_delete_post_link( get_the_ID() ) ?>"><?php _e( 'Trash post', 'anspress-question-answer' ) ?></a></span>
            </div>
            <div class="post-content">
				<?php the_content() ?>
            </div>
        </div>  
		<?php
		if ( ! empty( $flags ) && is_array( $flags ) ) {
			echo '<table class="ap-flagger-table"><tbody>';
			foreach ( $flags as $flag ) {
				echo '<tr class="flag-item">';
				echo '<td class="ap-user-avatar">'. get_avatar( $flag->apmeta_userid, 30 ) .'</td>';
				echo '<td class="ap-when-flagged">'. sprintf( __( '%s flagged this post %s', 'anspress-question-answer' ), '<a href="'.get_edit_user_link( $flag->apmeta_userid ).'">'.ap_user_display_name( $flag->apmeta_userid ).'</a>', ap_human_time( $flag->unix_date ) ) .'</td>';
				echo '<td class="ap-remove-flag"><a href="__nonce='. wp_create_nonce( 'flag_delete'.$flag->apmeta_id ). '&action=ap_delete_flag&id=' .$flag->apmeta_id.'" data-action="ap-delete-flag">'.__( 'Remove', 'anspress-question-answer' ).'</a></td>';
				echo '</tr>';
			}
			echo '</tbody></table>';
		} else {
			_e( 'No one flagged this post yet', 'anspress-question-answer' );
		}
		?>      
    </div>

</div>
