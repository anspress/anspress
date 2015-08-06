<?php

/**
 * Register all ajax hooks
 *
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 *
 * @link      http://anspress.io
 * @copyright 2014 Rahul Aryan
 */
class AnsPress_Ajax
{
    protected $ap;
    /**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 */
	public function __construct($ap)
	{
	    $ap->add_action('ap_ajax_suggest_similar_questions', $this, 'suggest_similar_questions');
	    $ap->add_action('ap_ajax_load_comment_form', $this, 'load_comment_form');
	    $ap->add_action('ap_ajax_delete_comment', $this, 'delete_comment');
	    $ap->add_action('ap_ajax_select_best_answer', $this, 'select_best_answer');
	    $ap->add_action('ap_ajax_delete_post', $this, 'delete_post');
	    $ap->add_action('ap_ajax_permanent_delete_post', $this, 'permanent_delete_post');
	    $ap->add_action('ap_ajax_change_post_status', $this, 'change_post_status');
	    $ap->add_action('ap_ajax_load_user_field_form', $this, 'load_user_field_form');
	    $ap->add_action('ap_ajax_set_featured', $this, 'set_featured');
	    $ap->add_action('ap_ajax_follow', $this, 'follow');
	    $ap->add_action('wp_ajax_ap_suggest_tags', $this, 'ap_suggest_tags');
	    $ap->add_action('wp_ajax_nopriv_ap_suggest_tags', $this, 'ap_suggest_tags');
	    $ap->add_action('ap_ajax_user_cover', $this, 'ap_user_cover');
	    $ap->add_action('ap_ajax_delete_notification', $this, 'delete_notification');
	    $ap->add_action('ap_ajax_markread_notification', $this, 'markread_notification');
	    $ap->add_action('ap_ajax_flag_post', $this, 'flag_post');
	    $ap->add_action('ap_ajax_subscribe', $this, 'subscribe');
	    $ap->add_action('ap_ajax_vote', $this, 'vote');
	    $ap->add_action('wp_ajax_ap_vote_for_close', $this, 'ap_vote_for_close');
		$ap->add_action('wp_ajax_nopriv_ap_vote_for_close', $this, 'ap_nopriv_vote_for_close');
	}

	/**
	 * Show similar questions when asking a question.
	 *
	 * @since 2.0.1
	 */
	public function suggest_similar_questions()
	{
	    if (empty($_POST['value'])) {
	        return;
	    }

	    $keyword = sanitize_text_field($_POST['value']);
	    $questions = get_posts(array(
			'post_type' => 'question',
			'showposts' => 10,
			's' => $keyword,
		));

	    if ($questions) {
	        $items = '<div class="ap-similar-questions-head">';
	        $items .= '<h3>'.ap_icon('check', true).sprintf(__('%d similar questions found', 'ap'), count($questions)).'</h3>';
	        $items .= '<p>'.__('We found similar questions that have already been asked, click to read them. Avoid creating duplicate questions, it will be deleted.').'</p>';
	        $items .= '</div>';
	        $items .= '<div class="ap-similar-questions">';
	        foreach ($questions as $p) {
	            $count = ap_count_answer_meta($p->ID);
	            $p->post_title = ap_highlight_words($p->post_title, $keyword);

	            if (!isset($_POST['is_admin'])) {
	                $items .= '<a class="ap-sqitem clearfix" href="'.get_permalink($p->ID).'"><span class="acount">'.sprintf(_n('1 Answer', '%d Answers', $count, 'ap'), $count).'</span><span class="ap-title">'.$p->post_title.'</span></a>';
	            } else {
	                $items .= '<div class="ap-q-suggestion-item clearfix"><a class="select-question-button button button-primary button-small" href="'.add_query_arg(array('post_type' => 'answer', 'post_parent' => $p->ID), admin_url('post-new.php')).'">'.__('Select', 'ap').'</a><span class="question-title">'.$p->post_title.'</span><span class="acount">'.sprintf(_n('1 Answer', '%d Answers', $count, 'ap'), $count).'</span></div>';
	            }
	        }
	        $items .= '</div>';
	        $result = array('status' => true, 'html' => $items);
	    } else {
	        $result = array('status' => false, 'message' => __('No related questions found', 'ap'));
	    }

	    ap_send_json($result);
	}

	/**
	 * Return comment form.
	 *
	 * @since 2.0.1
	 */
	public function load_comment_form()
	{
	    $result = array(
			'ap_responce' => true,
			'action' => 'load_comment_form',
		);

	    if ((wp_verify_nonce($_REQUEST['__nonce'], 'comment_form_nonce')) || (wp_verify_nonce($_REQUEST['__nonce'], 'edit_comment_'.(int) $_REQUEST['comment_ID']))) {
	        $comment_args = array();
	        $content = '';
	        $commentid = '';
	        if (isset($_REQUEST['comment_ID'])) {
	            $comment = get_comment($_REQUEST['comment_ID']);
	            $comment_post_ID = $comment->comment_post_ID;
	            $nonce = wp_create_nonce('comment_'.$comment->comment_ID);
	            $comment_args['label_submit'] = __('Update comment', 'ap');

	            $content = $comment->comment_content;
	            $commentid = '<input type="hidden" name="comment_ID" value="'.$comment->comment_ID.'"/>';
	        } else {
	            $comment_post_ID = (int) $_REQUEST['post'];
	            $nonce = wp_create_nonce('comment_'.(int) $_REQUEST['post']);
	        }

	        $comment_args = array(
				'id_form' => 'ap-commentform',
				'title_reply' => '',
				'logged_in_as' => '',
				'comment_field' => '<div class="ap-comment-submit"><input type="submit" value="'.__('Comment', 'ap').'" name="submit"></div><div class="ap-comment-textarea"><textarea name="comment" rows="3" aria-required="true" id="ap-comment-textarea" class="ap-form-control autogrow" placeholder="'.__('Respond to the post.', 'ap').'">'.$content.'</textarea></div><input type="hidden" name="ap_form_action" value="comment_form"/><input type="hidden" name="ap_ajax_action" value="comment_form"/><input type="hidden" name="__nonce" value="'.$nonce.'"/>'.$commentid,
				'comment_notes_after' => '',
			);

	        if (isset($_REQUEST['comment_ID'])) {
	            $comment_args['label_submit'] = __('Update comment', 'ap');
	        }

			//$current_user = get_userdata( get_current_user_id() );
			global $withcomments;
	        $withcomments = true;

	        $post = new WP_Query(array('p' => $comment_post_ID, 'post_type' => array('question', 'answer')));
	        $count = get_comment_count($comment_post_ID);
	        ob_start();
	        if (!ap_opt('show_comments_by_default')) {
	            echo '<div class="ap-comment-block clearfix">';
	        }

	        echo '<div class="ap-comment-form clearfix">';
	        echo '<div class="ap-comment-inner">';
	        comment_form($comment_args, $comment_post_ID);
	        echo '</div>';
	        echo '</div>';

	        if (!ap_opt('show_comments_by_default')) {
	            while ($post->have_posts()) {
	                $post->the_post();
	                comments_template();
	            }

	            wp_reset_postdata();
	        }

	        if (!ap_opt('show_comments_by_default')) {
	            echo '</div>';
	        }

	        $result['html'] = ob_get_clean();
	        $result['container'] = '#comments-'.$comment_post_ID;
			//$result['message'] = 'success';
			$result['view_default'] = ap_opt('show_comments_by_default');
	        $result['view'] = array('comments_count_'.$comment_post_ID => '('.$count['approved'].')', 'comment_count_label_'.$comment_post_ID => sprintf(_n('One comment', '%d comments', $count['approved'], 'ap'), $count['approved']));
	    } else {
	        $result['message'] = 'no_permission';
	    }

	    ap_send_json(ap_ajax_responce($result));
	}

	/**
	 * Ajax action for deleting comment.
	 *
	 * @since 2.0.0
	 */
	public function delete_comment()
	{
	    $comment_id = (int) $_POST['comment_ID'];

	    if (isset($_POST['comment_ID']) && ap_user_can_delete_comment($comment_id) && wp_verify_nonce($_POST['__nonce'], 'delete_comment')) {
	        $comment = get_comment($comment_id);

	        if (time() > (get_comment_date('U', (int) $_POST['comment_ID']) + (int) ap_opt('disable_delete_after')) && !is_super_admin()) {
	            ap_send_json(ap_ajax_responce(array('message_type' => 'warning', 'message' => sprintf(__('This post was created %s ago, its locked hence you cannot delete it.', 'ap'), ap_human_time(get_comment_date('U', (int) $_POST['comment_ID']))))));

	            return;
	        }

	        $delete = wp_delete_comment((int) $_POST['comment_ID'], true);

	        if ($delete) {
	            do_action('ap_after_deleting_comment', $comment);
	            $count = get_comment_count($comment->comment_post_ID);
	            ap_send_json(ap_ajax_responce(array('action' => 'delete_comment', 'comment_ID' => (int) $_POST['comment_ID'], 'message' => 'comment_delete_success', 'view' => array('comments_count_'.$comment->comment_post_ID => '('.$count['approved'].')', 'comment_count_label_'.$comment->comment_post_ID => sprintf(_n('One comment', '%d comments', $count['approved'], 'ap'), $count['approved'])))));
	        } else {
	            ap_send_json(ap_ajax_responce('something_wrong'));
	        }

	        return;
	    }
	    ap_send_json(ap_ajax_responce('no_permission'));
	}

	/**
	 * Ajax action for selecting a best answer.
	 *
	 * @since 2.0.0
	 */
	public function select_best_answer()
	{
	    $answer_id = (int) $_POST['answer_id'];

	    if (!is_user_logged_in()) {
	        ap_send_json(ap_ajax_responce('no_permission'));

	        return;
	    }

	    if (!wp_verify_nonce($_POST['__nonce'], 'answer-'.$answer_id)) {
	        ap_send_json(ap_ajax_responce('something_wrong'));

	        return;
	    }

	    $post = get_post($answer_id);
	    $user_id = get_current_user_id();

	    if (ap_question_best_answer_selected($post->post_parent)) {
	        do_action('ap_unselect_answer', $user_id, $post->post_parent, $post->ID);

	        update_post_meta($post->ID, ANSPRESS_BEST_META, 0);

	        update_post_meta($post->post_parent, ANSPRESS_SELECTED_META, false);

	        update_post_meta($post->post_parent, ANSPRESS_UPDATED_META, current_time('mysql'));

	        if (ap_opt('close_after_selecting')) {
	            wp_update_post(array('ID' => $post->post_parent, 'post_status' => 'publish'));
	        }

	        ap_update_user_best_answers_count_meta($user_id);
	        ap_update_user_solved_answers_count_meta($user_id);

	        ap_send_json(ap_ajax_responce(array('message' => 'unselected_the_answer', 'action' => 'unselected_answer', 'do' => 'reload')));
	    } else {
	        do_action('ap_select_answer', $user_id, $post->post_parent, $post->ID);
	        update_post_meta($post->ID, ANSPRESS_BEST_META, 1);
	        update_post_meta($post->post_parent, ANSPRESS_SELECTED_META, $post->ID);
	        update_post_meta($post->post_parent, ANSPRESS_UPDATED_META, current_time('mysql'));

	        if (ap_opt('close_after_selecting')) {
	            wp_update_post(array('ID' => $post->post_parent, 'post_status' => 'closed'));
	        }

	        ap_update_user_best_answers_count_meta($user_id);
	        ap_update_user_solved_answers_count_meta($user_id);

	        ap_insert_notification($user_id, $post->post_author, 'answer_selected', array('post_id' => $post->ID));

	        $html = ap_select_answer_btn_html($answer_id);
	        ap_send_json(ap_ajax_responce(array('message' => 'selected_the_answer', 'action' => 'selected_answer', 'do' => 'reload', 'html' => $html)));
	    }
	}

    public function delete_post()
    {
        $post_id = (int) $_POST['post_id'];

        $action = 'delete_post_'.$post_id;

        if (!wp_verify_nonce($_POST['__nonce'], $action) || !ap_user_can_delete($post_id)) {
            ap_send_json(ap_ajax_responce('something_wrong'));

            return;
        }

        $post = get_post($post_id);

        if ((time() > (get_the_time('U', $post->ID) + (int) ap_opt('disable_delete_after'))) && !is_super_admin()) {
            ap_send_json(ap_ajax_responce(array('message_type' => 'warning', 'message' => sprintf(__('This post was created %s ago, its locked hence you cannot delete it.', 'ap'), ap_human_time(get_the_time('U', $post->ID))))));

            return;
        }

        wp_trash_post($post_id);
        if ($post->post_type == 'question') {
            do_action('ap_wp_trash_question', $post_id);
            ap_send_json(ap_ajax_responce(array('action' => 'delete_question', 'do' => 'redirect', 'redirect_to' => ap_base_page_link(), 'message' => 'question_moved_to_trash')));
        } else {
            do_action('ap_wp_trash_answer', $post_id);
            $current_ans = ap_count_published_answers($post->post_parent);
            $count_label = sprintf(_n('1 Answer', '%d Answers', $current_ans, 'ap'), $current_ans);
            $remove = (!$current_ans ? true : false);
            ap_send_json(ap_ajax_responce(array(
				'action' => 'delete_answer',
				'div_id' => '#answer_'.$post_id,
				'count' => $current_ans,
				'count_label' => $count_label,
				'remove' => $remove,
				'message' => 'answer_moved_to_trash',
				'view' => array('answer_count' => $current_ans, 'answer_count_label' => $count_label), )));
        }
    }

    public function permanent_delete_post()
    {
        $post_id = (int) $_POST['post_id'];

        $action = 'delete_post_'.$post_id;

        if (!wp_verify_nonce($_POST['__nonce'], $action) || !ap_user_can_permanent_delete()) {
            ap_send_json(ap_ajax_responce('something_wrong'));

            return;
        }

        $post = get_post($post_id);

        wp_trash_post($post_id);

        if ($post->post_type == 'question') {
            do_action('ap_wp_trash_question', $post_id);
        } else {
            do_action('ap_wp_trash_answer', $post_id);
        }

        wp_delete_post($post_id, true);

        if ($post->post_type == 'question') {
            ap_send_json(ap_ajax_responce(array('action' => 'delete_question', 'do' => 'redirect', 'redirect_to' => ap_base_page_link(), 'message' => 'question_deleted_permanently')));
        } else {
            $current_ans = ap_count_published_answers($post->post_parent);
            $count_label = sprintf(_n('1 Answer', '%d Answers', $current_ans, 'ap'), $current_ans);
            $remove = (!$current_ans ? true : false);
            ap_send_json(ap_ajax_responce(array(
				'action' => 'delete_answer',
				'div_id' => '#answer_'.$post_id,
				'count' => $current_ans,
				'count_label' => $count_label,
				'remove' => $remove,
				'message' => 'answer_deleted_permanently',
				'view' => array('answer_count' => $current_ans, 'answer_count_label' => $count_label), )));
        }
    }

	/**
	 * Handle change post status request.
	 *
	 * @since 2.1
	 */
	public function change_post_status()
	{
	    $post_id = (int) $_POST['post_id'];
	    $status = $_POST['status'];

	    if (!is_user_logged_in() || !wp_verify_nonce($_POST['__nonce'], 'change_post_status_'.$post_id) || !ap_user_can_change_status($post_id)) {
	        ap_send_json(ap_ajax_responce('no_permission'));
	        die();
	    } else {
	        $post = get_post($post_id);
	        if (($post->post_type == 'question' || $post->post_type == 'answer') && $post->post_status != $status) {
	            $update_data = array();
	            if ($status == 'publish') {
	                $update_data['post_status'] = 'publish';
	            } elseif ($status == 'moderate') {
	                $update_data['post_status'] = 'moderate';
	            } elseif ($status == 'private_post') {
	                $update_data['post_status'] = 'private_post';
	            } elseif ($status == 'closed') {
	                $update_data['post_status'] = 'closed';
	            }

				// unregister history action for edit
				remove_action('ap_after_new_answer', array('AP_History', 'new_answer'));
	            remove_action('ap_after_new_question', array('AP_History', 'new_question'));

	            $update_data['ID'] = $post->ID;
	            wp_update_post($update_data);

	            ap_add_history(get_current_user_id(), $post_id, '', 'status_updated');

	            add_action('ap_post_status_updated', $post->ID);

	            ob_start();
	            ap_post_status_description($post->ID);
	            $html = ob_get_clean();

	            ap_send_json(ap_ajax_responce(array(
					'action' => 'status_updated',
					'message' => 'status_updated',
					'do' => array('remove_if_exists', 'toggle_active_class', 'append_before'),
					'append_before_container' => '#ap_post_actions_'.$post->ID,
					'toggle_active_class_container' => '#ap_post_status_toggle_'.$post->ID,
					'remove_if_exists_container' => '#ap_post_status_desc_'.$post->ID,
					'active' => '.'.$status,
					'html' => $html,
				)));
	            die();
	        }
	    }
	    ap_send_json(ap_ajax_responce('something_wrong'));
	    die();
	}

    public function load_user_field_form()
    {
        $user_id = get_current_user_id();
        $field_name = sanitize_text_field($_POST['field']);

        if (!is_user_logged_in() || !wp_verify_nonce($_POST['__nonce'], 'user_field_form_'.$field_name.'_'.$user_id)) {
            ap_send_json(ap_ajax_responce('no_permission'));
        } else {
            if (ap_has_users(array('ID' => $user_id))) {
                while (ap_users()) {
                    ap_the_user();
                    $form = ap_user_get_fields(array(
					'show_only' => $field_name,
					'form' => array(
						'field_hidden' => false,
						'hide_footer' => false,
						'show_cancel' => true,
						'is_ajaxified' => true,
						'submit_button' => __('Update', 'ap'), ),
					));

                    ap_send_json(ap_ajax_responce(array(
						'action' => 'user_field_form_loaded',
						'do' => 'updateHtml',
						'container' => '#user_field_form_'.$field_name,
						'html' => $form->get_form(),
					)));
                }
            }
        }
        ap_send_json(ap_ajax_responce('something_wrong'));
        die();
    }

    public function ap_suggest_tags()
    {
        $keyword = sanitize_text_field($_POST['q']);
        $tags = get_terms('question_tag', array(
			'orderby' => 'count',
			'order' => 'DESC',
			'hide_empty' => false,
			'search' => $keyword,
			'number' => 8,
		));

        if ($tags) {
            $items = array();
            foreach ($tags as $k => $t) {
                $items[$k] = $t->name;
            }

            $result = array('status' => true, 'items' => $items);
            die(json_encode($result));
        }

        die(json_encode(array('status' => false)));
    }

    public function set_featured()
    {
        $post_id = (int) $_POST['post_id'];

        if (!is_super_admin() || !wp_verify_nonce($_POST['__nonce'], 'set_featured_'.$post_id)) {
            ap_send_json(ap_ajax_responce('no_permission'));
            die();
        } else {
            $post = get_post($post_id);
            $featured_questions = get_option('featured_questions');

            if (($post->post_type == 'question')) {
                if (!empty($featured_questions) && is_array($featured_questions) && in_array($post->ID, $featured_questions)) {
                    foreach ($featured_questions as $key => $q) {
                        if ($q == $post->ID) {
                            unset($featured_questions[$key]);
                        }
                    }

                    update_option('featured_questions', $featured_questions);

                    ap_send_json(ap_ajax_responce(array(
						'action' => 'unset_featured_question',
						'message' => 'unset_featured_question',
						'do' => array('updateHtml'),
						'container' => '#set_featured_'.$post->ID,
						'html' => __('Set as featured', 'ap'),
					)));
                } else {
                    if (empty($featured_questions) || !is_array($featured_questions) || !$featured_questions) {
                        $featured_questions = array($post->ID);
                    } else {
                        $featured_questions[] = $post->ID;
                    }

                    update_option('featured_questions', $featured_questions);

                    ap_send_json(ap_ajax_responce(array(
						'action' => 'set_featured_question',
						'message' => 'set_featured_question',
						'do' => array('updateHtml'),
						'container' => '#set_featured_'.$post->ID,
						'html' => __('Unset as featured', 'ap'),
					)));
                }
            }
        }
        ap_send_json(ap_ajax_responce('something_wrong'));
        die();
    }

    public function follow()
    {
        $user_to_follow = (int) $_POST['user_id'];
        $current_user_id = get_current_user_id();

        if (!wp_verify_nonce($_POST['__nonce'], 'follow_'.$user_to_follow.'_'.$current_user_id)) {
            ap_send_json(ap_ajax_responce('something_wrong'));

            return;
        }

        if (!is_user_logged_in()) {
            ap_send_json(ap_ajax_responce('please_login'));

            return;
        }

        if ($user_to_follow == $current_user_id) {
            ap_send_json(ap_ajax_responce('cannot_follow_yourself'));

            return;
        }

        $is_following = ap_is_user_following($user_to_follow, $current_user_id);

        if ($is_following) {
            ap_remove_follower($current_user_id, $user_to_follow);

            ap_send_json(ap_ajax_responce(array('message' => 'unfollow', 'action' => 'unfollow', 'container' => '#follow_'.$user_to_follow, 'do' => 'updateText', 'text' => __('Follow', 'ap'))));

            return;
        } else {
            ap_add_follower($current_user_id, $user_to_follow);

            ap_send_json(ap_ajax_responce(array('message' => 'follow', 'action' => 'follow', 'container' => '#follow_'.$user_to_follow, 'do' => 'updateText', 'text' => __('Unfollow', 'ap'))));
        }
    }

    public function ap_user_cover()
    {
        if (ap_opt('disable_hover_card')) {
            ap_send_json(ap_ajax_responce('something_wrong'));
        }

        $user_id = (int) $_POST['user_id'];

        if (!wp_verify_nonce($_POST['ap_ajax_nonce'], 'ap_ajax_nonce')) {
            ap_send_json(ap_ajax_responce('something_wrong'));

            return;
        }

        global $ap_user_query;
        $ap_user_query = ap_has_users(array('ID' => $user_id));

        if ($ap_user_query->has_users()) {
            while (ap_users()) :
            	ap_the_user();
            	ap_get_template_part('user/user-card');
            endwhile;
        } else {
            ap_send_json(ap_ajax_responce('something_wrong'));
        }

        die();
    }

    public function delete_notification()
    {
        if (!wp_verify_nonce($_POST['__nonce'], 'delete_notification') && !is_user_logged_in()) {
            ap_send_json(ap_ajax_responce('something_wrong'));

            return;
        }

        $notification = ap_get_notification_by_id((int) $_POST['id']);

        if ($notification && ($notification['apmeta_userid'] == get_current_user_id() || is_super_admin())) {
            $row = ap_delete_notification($notification['apmeta_id']);

            if ($row !== false) {
                ap_send_json(ap_ajax_responce(
                	array(
                		'message' => 'delete_notification',
                		'action' => 'delete_notification',
                		'container' => '#ap-notification-'.$notification['apmeta_id']
                	)
                ));
            }
        }

		//if process reached here then there must be something wrong
		ap_send_json(ap_ajax_responce('something_wrong'));
    }

    public function markread_notification()
    {
        $id = (int) $_POST['id'];

        if (isset($_POST['id']) && !wp_verify_nonce($_POST['__nonce'], 'ap_markread_notification_'.$id) && !is_user_logged_in()) {
            ap_send_json(ap_ajax_responce('something_wrong'));

            return;
        } elseif (!wp_verify_nonce($_POST['__nonce'], 'ap_markread_notification_'.get_current_user_id()) && !is_user_logged_in()) {
            ap_send_json(ap_ajax_responce('something_wrong'));

            return;
        }

        if (isset($_POST['id'])) {
            $notification = ap_get_notification_by_id($id);

            if ($notification && ($notification['apmeta_actionid'] == get_current_user_id() || is_super_admin())) {
                $row = ap_update_meta(array('apmeta_type' => 'notification'), array('apmeta_id' => $notification['apmeta_id']));

                if ($row !== false) {
                    ap_send_json(ap_ajax_responce(array('message' => 'mark_read_notification', 'action' => 'mark_read_notification', 'container' => '.ap-notification-'.$notification['apmeta_id'], 'view' => array('notification_count' => ap_get_total_unread_notification()))));
                }
            }
        } else {
            $row = ap_notification_mark_all_read(get_current_user_id());

            if ($row !== false) {
                ap_send_json(ap_ajax_responce(array('message' => 'mark_read_notification', 'action' => 'mark_all_read', 'container' => '#ap-notification-dropdown', 'view' => array('notification_count' => '0'))));
            }
        }

		//if process reached here then there must be something wrong
		ap_send_json(ap_ajax_responce('something_wrong'));
    }

	/**
	 * Flag a post as inappropriate.
	 *
	 * @since 2.0.0-alpha2
	 */
	public function flag_post()
	{
	    $post_id = (int) $_POST['post_id'];
	    if (!wp_verify_nonce($_POST['__nonce'], 'flag_'.$post_id) && is_user_logged_in()) {
	        ap_send_json(ap_ajax_responce('something_wrong'));

	        return;
	    }

	    $userid = get_current_user_id();
	    $is_flagged = ap_is_user_flagged($post_id);

	    if ($is_flagged) {
	        ap_send_json(ap_ajax_responce(array('message' => 'already_flagged')));
	        echo json_encode(array('action' => false, 'message' => __('You already flagged this post', 'ap')));
	    } else {
	        ap_add_flag($userid, $post_id);

	        $count = ap_post_flag_count($post_id);

			//update post meta
			update_post_meta($post_id, ANSPRESS_FLAG_META, $count);
	        ap_send_json(ap_ajax_responce(array('message' => 'flagged', 'action' => 'flagged', 'view' => array($post_id.'_flag_count' => $count),  'count' => $count)));
	    }

		//if process reached here then there must be something wrong
		ap_send_json(ap_ajax_responce('something_wrong'));
	}

	/**
	 * Process ajax subscribe request
	 */
    public function subscribe()
    {
        $action_id = (int) $_POST['action_id'];

        $type = sanitize_text_field($_POST['type']);

        if (!wp_verify_nonce($_POST['__nonce'], 'subscribe_'.$action_id.'_'.$type)) {
            ap_send_json(ap_ajax_responce('something_wrong'));

            return;
        }

        if (!is_user_logged_in()) {
            ap_send_json(ap_ajax_responce('please_login'));

            return;
        }

        if ($type === 'category') {
            $subscribe_type = 'category';
        } elseif ($type === 'tag') {
            $subscribe_type = 'tag';
        } else {
            $subscribe_type = false;
        }

        $user_id = get_current_user_id();

        $is_subscribed = ap_is_user_subscribed($action_id, $user_id, $subscribe_type);

        if ($is_subscribed) {
            if ($subscribe_type === false) {
                $row = ap_remove_question_subscriber($action_id);
            } else {
                $row = ap_remove_subscriber($user_id, $action_id, $subscribe_type);
            }

            if (false !== $row) {
                ap_send_json(ap_ajax_responce(array('message' => 'unsubscribed', 'action' => 'unsubscribed', 'container' => '#subscribe_'.$action_id.' b', 'do' => 'updateHtml', 'html' => __('Follow question', 'ap'))));
            }
        } else {
            if ($subscribe_type === false) {
                ap_add_question_subscriber($action_id);
            } else {
                ap_add_subscriber($user_id, $action_id, $subscribe_type);
            }

            ap_send_json(ap_ajax_responce(array('message' => 'subscribed', 'action' => 'subscribed', 'container' => '#subscribe_'.$action_id.' b', 'do' => 'updateHtml', 'html' => __('Unfollow question', 'ap'))));
        }
    }

    /**
	 * Process voting button.
	 *
	 * @since 2.0.1.1
	 */
	public function vote()
	{
		$post_id = (int) $_POST['post_id'];

		if (!wp_verify_nonce($_POST['__nonce'], 'vote_'.$post_id)) {
			ap_send_json(ap_ajax_responce('something_wrong'));

			return;
		}

		if (!is_user_logged_in()) {
			ap_send_json(ap_ajax_responce('please_login'));

			return;
		}

		$post = get_post($post_id);
		if ($post->post_author == get_current_user_id()) {
			ap_send_json(ap_ajax_responce('cannot_vote_own_post'));

			return;
		}

		$type = sanitize_text_field($_POST['type']);

		$type = ($type == 'up' ? 'vote_up' : 'vote_down');

		if ($post->post_type == 'question' && ap_opt('disable_down_vote_on_question') && $type == 'vote_down') {
			ap_send_json(ap_ajax_responce('voting_down_disabled'));

			return;
		} elseif ($post->post_type == 'answer' && ap_opt('disable_down_vote_on_answer') && $type == 'vote_down') {
			ap_send_json(ap_ajax_responce('voting_down_disabled'));

			return;
		}

		$userid = get_current_user_id();

		$is_voted = ap_is_user_voted($post_id, 'vote', $userid);

		if (is_object($is_voted) && $is_voted->count > 0) {
			// if user already voted and click that again then reverse
			if ($is_voted->type == $type) {
				ap_remove_vote($type, $userid, $post_id, $post->post_author);
				$counts = ap_post_votes($post_id);

				//update post meta
				update_post_meta($post_id, ANSPRESS_VOTE_META, $counts['net_vote']);

				do_action('ap_undo_vote', $post_id, $counts);

				$action = 'undo';
				$count = $counts['net_vote'];
				do_action('ap_undo_'.$type, $post_id, $counts);

				ap_send_json(ap_ajax_responce(array('action' => $action, 'type' => $type, 'count' => $count, 'message' => 'undo_vote')));
			} else {
				ap_send_json(ap_ajax_responce('undo_vote_your_vote'));
			}
		} else {
			ap_add_vote($userid, $type, $post_id, $post->post_author);

			$counts = ap_post_votes($post_id);

			//update post meta
			update_post_meta($post_id, ANSPRESS_VOTE_META, $counts['net_vote']);
			do_action('ap_'.$type, $post_id, $counts);

			$action = 'voted';
			$count = $counts['net_vote'];
			ap_send_json(ap_ajax_responce(array('action' => $action, 'type' => $type, 'count' => $count, 'message' => 'voted')));
		}
	}

	public function ap_add_to_subscribe_nopriv()
	{
		echo json_encode(array('action' => false, 'message' => __('Please login for adding question to your subscribe', 'ap')));
		die();
	}

	public function ap_vote_for_close()
	{
		$args = explode('-', sanitize_text_field($_POST['args']));
		if (wp_verify_nonce($args[1], 'close_'.$args[0])) {
			$voted_closed = ap_is_user_voted_closed($args[0]);
			$type = 'close';
			$userid = get_current_user_id();

			if ($voted_closed) {
				// if already in voted for close then remove it
				$row = ap_remove_vote($type, $userid, $args[0]);

				$counts = ap_post_close_vote($args[0]);
				//update post meta
				update_post_meta($args[0], ANSPRESS_CLOSE_META, $counts);

				$result = apply_filters('ap_cast_unclose_result', array('row' => $row, 'action' => 'removed', 'text' => __('Close', 'ap').' ('.$counts.')', 'title' => __('Vote for closing', 'ap'), 'message' => __('Your close request has been removed', 'ap')));
			} else {
				$row = ap_add_vote($userid, $type, $args[0]);

				$counts = ap_post_close_vote($args[0]);
				//update post meta
				update_post_meta($args[0], ANSPRESS_CLOSE_META, $counts);

				$result = apply_filters('ap_cast_close_result', array('row' => $row, 'action' => 'added', 'text' => __('Close', 'ap').' ('.$counts.')', 'title' => __('Undo your vote', 'ap'), 'message' => __('Your close request has been sent', 'ap')));
			}
		} else {
			$result = array('action' => false, 'message' => _('Something went wrong', 'ap'));
		}

		die(json_encode($result));
	}

	public function ap_nopriv_vote_for_close()
	{
		echo json_encode(array('action' => false, 'message' => __('Please login for requesting closing this question.', 'ap')));
		die();
	}
}
