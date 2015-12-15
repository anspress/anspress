<?php

/*
 * All functions and classes related to flagging
 *
 * This file keep all function required by flagging system.
 *
 * @link http://anspress.io
 * @since 2.3.4
 *
 * @package AnsPress
 */

/**
 * Add flag vote data to ap_meta table.
 *
 * @param int        $userid
 * @param int        $actionid
 * @param null|mixed $value
 * @param null|mixed $param
 *
 * @return integer
 */
function ap_add_flag($userid, $actionid, $value = null, $param = null)
{
    return ap_add_meta($userid, 'flag', $actionid, $value, $param);
}

/**
 * Count post flag votes.
 *
 * @param integer $postid
 *
 * @return int
 */
function ap_post_flag_count($postid = false)
{
    global $post;

    $postid = $postid ? $postid : $post->ID;    

    return apply_filters('ap_post_flag_count', ap_meta_total_count('flag', $postid));
}

/**
 * Return flag count of question and answer from meta.
 * @param  integer $post_id Question/Answer Id.
 * @return integer
 */
function ap_flagged_post_meta( $post_id ){
    return (int)get_post_meta( $post_id, ANSPRESS_FLAG_META, true );
}

/**
 * check if user flagged on post.
 *
 * @param bool $postid
 *
 * @return bool
 */
function ap_is_user_flagged($postid = false)
{
    if (is_user_logged_in()) {
        global $post;
        $postid = $postid ? $postid : $post->ID;
        $userid = get_current_user_id();
        $done = ap_meta_user_done('flag', $userid, $postid);

        return $done > 0 ? true : false;
    }

    return false;
}

/**
 * Flag button html.
 *
 * @return string
 *
 * @since 0.9
 */
function ap_flag_btn_html($echo = false)
{
    if (!is_user_logged_in()) {
        return;
    }

    global $post;
    $flagged = ap_is_user_flagged();
    $total_flag = ap_flagged_post_meta( $post->ID );
    $nonce = wp_create_nonce('flag_'.$post->ID);
    $title = (!$flagged) ? (__('Flag this post', 'anspress-question-answer')) : (__('You have flagged this post', 'anspress-question-answer'));

    $output = '<a id="flag_'.$post->ID.'" data-action="ajax_btn" data-query="flag_post::'.$nonce.'::'.$post->ID.'" class="flag-btn'.(!$flagged ? ' can-flagged' : '').'" href="#" title="'.$title.'">'.__('Flag ', 'anspress-question-answer').'<span class="ap-data-view ap-view-count-'.$total_flag.'" data-view="'.$post->ID.'_flag_count">'.$total_flag.'</span></a>';

    if ($echo) {
        echo $output;
    } else {
        return $output;
    }
}

/**
 * Insert flag vote for comment.
 *
 * @param int   $user_id
 * @param int   $action_id
 * @param mixed $value
 * @param mixed $param
 *
 * @return integer
 */
function ap_insert_comment_flag($user_id, $action_id, $value = null, $param = null)
{
    return ap_add_meta($user_id, 'comment_flag', $action_id, $value, $param);
}

/**
 * Output flag button for the comment.
 *
 * @param bool|int $comment_id
 *
 * @since  2.4
 *
 * @return string
 */
function ap_comment_flag_btn($comment_id = false, $label = false)
{
    echo ap_get_comment_flag_btn($comment_id, $label);
}

/**
 * Return flag button for the comment.
 *
 * @param bool|int $comment_id
 *
 * @since  2.4
 *
 * @return string
 */
function ap_get_comment_flag_btn($comment_id = false, $label = false)
{
    if (!is_user_logged_in()) {
        return;
    }

    if (false === $label) {
        $label = __('Flag', 'anspress-question-answer');
    }

    if (false === $comment_id) {
        $comment_id = get_comment_ID();
    }

    $flagged = ap_is_user_flagged_comment($comment_id);
    $total_flag = ap_comment_flag_count($comment_id);

    $nonce = wp_create_nonce('flag_'.$comment_id);

    $output = '<a id="flag_'.$comment_id.'" data-query="ap_ajax_action=flag_comment&comment_id='.$comment_id.'&__nonce='.$nonce.'"
    	data-action="ap_subscribe" class="flag-btn'.(!$flagged ? ' can-flag' : '').'" href="#" title="'.__('Report this comment to moderaor', 'anspress-question-answer').'">
    	'.$label.'<span class="ap-data-view ap-view-count-'.$total_flag.'" data-view="'.$comment_id.'_comment_flag">'.$total_flag.'</span>
    </a>';

    return $output;
}

/**
 * Count comment flag votes.
 *
 *
 * @return int
 */
function ap_comment_flag_count($comment_id = false)
{
    if (false === $comment_id) {
        $comment_id = get_comment_ID();
    }

    return apply_filters('ap_comment_flag_count', ap_meta_total_count('comment_flag', $comment_id));
}

/**
 * Check if user flagged comment.
 *
 * @param bool|int $comment_id
 * @param bool|int $user_id
 *
 * @since  2.4
 *
 * @return bool
 */
function ap_is_user_flagged_comment($comment_id = false, $user_id = false)
{
    if (!is_user_logged_in()) {
        return false;
    }

    if (false === $comment_id) {
        $comment_id = get_comment_ID();
    }

    if (false === $user_id) {
        $user_id = get_current_user_id();
    }

    $done = ap_meta_user_done('comment_flag', $user_id, $comment_id);

    return $done > 0 ? true : false;
}
