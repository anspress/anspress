<?php
/**
 * Form and controls of ask form
 *
 * @link http://wp3.in
 * @since 2.0.1
 * @license GPL2+
 * @package AnsPress
 */

class AnsPress_Answer_Form
{
    public function __construct()
    {
       // add_filter('ap_ask_form_fields', array($this, 'ask_form_name_field'));
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

new AnsPress_Answer_Form;

/**
 * Generate answer form
 * @param  boolean $editing
 * @return void
 */
function ap_answer_form($question_id, $editing = false){
    global $editing_post;

    if($editing){
        $is_private = $editing_post->post_status == 'private_post' ? true : false;
    }

    $args = array(
        'name'              => 'answer_form',
        'is_ajaxified'      => true,
        'submit_button'     => __('Post answer', 'ap'),
        'nonce_name'        => 'nonce_answer_'.$question_id,
        'fields'            => array(
            array(
                'name' => 'description',
                'type'  => 'editor',
                'value' => ( $editing ? $editing_post->post_content : @$_POST['description']  ),
                'settings' => array(
                    'textarea_rows' => 8,
                ),
            ),
            array(
                'name' => 'is_private',
                'label' => __('Private', 'ap'),
                'type'  => 'checkbox',
                'desc'  => __('This answer ment to be private, only visible to admin and moderator.', 'ap'),
                'value' => ( $editing ? $is_private : sanitize_text_field( @$_POST['is_private'] ) ),
                'order' => 12,
                'show_desc_tip' => false
            ),            
            array(
                'name' => 'form_question_id',
                'type'  => 'hidden',
                'value' => ( $editing ? $editing_post->post_parent : $question_id  ),
                'order' => 20
            ),
        ),
    );
    
    /**
     * FILTER: ap_ask_form_fields
     * Filter for modifying $args
     * @var array
     * @since  2.0
     */
    $args = apply_filters( 'ap_answer_form_fields', $args, $editing );

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
function ap_edit_answer_form($question_id)
{
    ap_answer_form($question_id, true);
}