<?php
/**
 * Form and controls of ask form
 *
 * @link http://wp3.in
 * @since 2.0.1
 * @license GPL2+
 * @package AnsPress
 */

class AnsPress_Ask_Form
{
    public function __construct()
    {
        add_filter('ap_ask_form_fields', array($this, 'ask_form_name_field'));
    }

    public function ask_form_name_field($args){
        if(!is_user_logged_in() && ap_opt('allow_anonymous'))
            $args['fields'][] = array(
                'name' => 'name',
                'label' => __('Name', 'ap'),
                'type'  => 'text',
                'placeholder'  => __('Enter your name to display', 'ap'),
                'value' => sanitize_text_field(@$_POST['name'] ),
                'order' => 12
            );

        return $args;
    }
}

new AnsPress_Ask_Form;

/**
 * Generate ask form
 * @param  boolean $editing
 * @return void
 */
function ap_ask_form($editing = false){
    global $editing_post;

    $is_private = false;
    if($editing){
        $is_private = $editing_post->post_status == 'private_post' ? true : false;
    }

    $args = array(
        'name'              => 'ask_form',
        'is_ajaxified'      => true,
        'submit_button'     => __('Post question', 'ap'),
        'fields'            => array(
            array(
                'name' => 'title',
                'label' => __('Title', 'ap'),
                'type'  => 'text',
                'placeholder'  => __('Question in one sentence', 'ap'),
                'desc'  => __('Write a meaningful title for the question.', 'ap'),
                'value' => ( $editing ? $editing_post->post_title : sanitize_text_field( @$_POST['title'] ) ),
                'order' => 5,
                'attr' => 'data-action="suggest_similar_questions"',
                'autocomplete' => false,
            ),
            array(
                'name' => 'title',
                'type'  => 'custom',
                'order' => 5,
                'html' => '<div id="similar_suggestions"></div>'
            ),            
            array(
                'name' => 'description',
                'label' => __('Description', 'ap'),
                'type'  => 'editor',
                'desc'  => __('Write description for the question.', 'ap'),
                'value' => ( $editing ? $editing_post->post_content : @$_POST['description']  ),
                'settings' => array(
                    'textarea_rows' => 8,
                    'tinymce' => ap_opt('question_text_editor') ? false : true,
                    'quicktags' => false,
                ),
            ),
            array(
                'name' => 'parent_id',
                'type'  => 'hidden',
                'value' => ( $editing ? $editing_post->post_parent : get_query_var('parent')  ),
                'order' => 20
            ),
        ),
    );

    if(ap_opt('allow_private_posts'))
        $args['fields'][] = array(
            'name' => 'is_private',
            'type'  => 'checkbox',
            'desc'  => __('Only visible to admin and moderator.', 'ap'),
            'value' => $is_private,
            'order' => 12,
            'show_desc_tip' => false
        );

    if(ap_opt('enable_recaptcha'))
        $args['fields'][] = array(
            'name' => 'captcha',
            'type'  => 'custom',
            'order' => 100,
            'html' => '<div class="g-recaptcha" id="recaptcha" data-sitekey="'.ap_opt('recaptcha_site_key').'"></div><script type="text/javascript"
src="https://www.google.com/recaptcha/api.js?hl='.get_locale().'&onload=onloadCallback&render=explicit"  async defer></script><script type="text/javascript">var onloadCallback = function() {
        widgetId1 = grecaptcha.render("recaptcha", {
          "sitekey" : "'.ap_opt('recaptcha_site_key').'"
        });
      };</script>'
        );
    
    /**
     * FILTER: ap_ask_form_fields
     * Filter for modifying $args
     * @var array
     * @since  2.0
     */
    $args = apply_filters( 'ap_ask_form_fields', $args, $editing );

    if($editing){
        $args['fields'][] = array(
            'name'  => 'edit_post_id',
            'type'  => 'hidden',
            'value' => $editing_post->ID,
            'order' => 20
        );
    }

    $form = new AnsPress_Form($args);

    echo $form->get_form();
}

/**
 * Generate edit question form, this is a wrapper of ap_ask_form()
 * @return void
 * @since 2.0.1
 */
function ap_edit_question_form()
{
    ap_ask_form(true);
}