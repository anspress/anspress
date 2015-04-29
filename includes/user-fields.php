<?php



class AP_User_Fields
{
    /**
     * Store user form option
     * @var array
     */
    var $from = array();
    
    var $form_fields = array();

    /**
     * Store arguments
     * @var array
     */
    var $args;
    
    var $fields_value;
    
    var $fields_name = array();

    function __construct($args = '')
    {

        $defaults = array(
            'user_id' => ap_user_get_the_ID(),
            'form'  => array(),
        );

        $this->args = wp_parse_args( $args, $defaults );

        $this->fields_value = ap_user_get_the_meta();
        //var_dump($this->fields_value);

        $this->form_fields = array(                        
            array(
                'name' => 'first_name',
                'label' => __('First name', 'ap'),
                'type'  => 'text',
                'placeholder'  => __('Your first name', 'ap'),
                'value' => $this->fields_value['first_name'],
                'order' => 5,
                'autocomplete' => false,
                'sanitize' => array('sanitize_text_field'),
            ),
            array(
                'name' => 'last_name',
                'label' => __('Last name', 'ap'),
                'type'  => 'text',
                'placeholder'  => __('Your surname', 'ap'),
                'value' => $this->fields_value['last_name'],
                'order' => 5,
                'autocomplete' => false,
                'sanitize' => array('sanitize_text_field'),
            ),
            array(
                'name' => 'nickname',
                'label' => __('Nickname', 'ap'),
                'type'  => 'text',
                'placeholder'  => __('Your nickname', 'ap'),
                'value' => $this->fields_value['nickname'],
                'order' => 5,
                'autocomplete' => false,
                'sanitize' => array('sanitize_text_field'),
            ),
            array(
                'name' => 'display_name',
                'label' => __('Display name', 'ap'),
                'type'  => 'select',
                'options'  => ap_user_get_display_name_option(),
                'value' => $this->fields_value['display_name'],
                'order' => 5,
                'autocomplete' => false,
                'sanitize' => array('sanitize_text_field'),
            ),
            array(
                'name' => 'description',
                'label' => __('Description', 'ap'),
                'type'  => 'textarea',
                'value' => $this->fields_value['description'],
                'placeholder'  => __('Write something about yourself'),
                'rows' => 5,
                'order' => 5,
                'sanitize' => array('sanitize_text_field'),
            ),
            array(
                'type'          => 'custom',
                'html'          => '<h3>'.__('Account', 'ap').'</h3>',
                'order'         => 5,
            ),       
            array(
                'name' => 'user_login',
                'label' => __('Username', 'ap'),
                'type'  => 'text',
                'placeholder'  => __('Your username', 'ap'),
                'desc'  => __('This cannot be changed.', 'ap'),
                'value' => $this->fields_value['user_login'],
                'order' => 5,                    
                'edit_disabled' => true,                    
                'autocomplete' => false,
                'sanitize' => array('sanitize_text_field'),
                'visibility' => 'me',
            ),
            array(
                'name' => 'user_email',
                'label' => __('Email', 'ap'),
                'type'  => 'text',
                'placeholder'  => __('Your contact email', 'ap'),
                'value' => $this->fields_value['user_email'],
                'order' => 5,
                'autocomplete' => false,
                'edit_disabled' => true,  
                'sanitize' => array('is_email'),
                'validate' => array('is_email'),
                'visibility' => 'me',
            ),
            array(
                'name' => 'password',
                'label' => __('Password', 'ap'),
                'type'  => 'password',
                'placeholder'  => __('Update your password', 'ap'),
                'value' => 'password',
                'visibility' => 'me',
                'order' => 5,
                'autocomplete' => false,
            ),
        );

        $this->field_to_show();
        $this->fields_name();

        $this->from = wp_parse_args($this->args['form'], array(
            'name'              => 'ap_user_profile_field',
            'user_id'           => $this->args['user_id'],
            'field_hidden'      => true,
            'hide_footer'       => true,
            'nonce_name'        => 'nonce_user_profile_'.$this->args['user_id'],
            'fields'            => $this->form_fields
        ));
    }

    public function fields_name(){
        foreach ($this->form_fields as $field) {
            if(!empty($field['name']))
                $this->fields_name[] = $field['name'];
        }
        
    }

    public function field_to_show(){
        if(isset($this->args['show_only']) )
            foreach($this->form_fields as $fields)
                if($fields['name'] == $this->args['show_only']){
                    $this->form_fields = array($fields);
                    break;
                }
    }

    public function get_field_by_name($field_name){
        foreach($this->form_fields as $fields)
            if($fields['name'] == $field_name){
                return $fields;
                break;
            }
    }

    public function get_form(){
        anspress()->form = new AnsPress_Form($this->from);
        return anspress()->form->get_form();
    }

    public function update_field($field_name)
    {

        if(!in_array($field_name, $this->fields_name))
            return;

        $user_id = get_current_user_id();

        $field_name = sanitize_text_field( $field_name );

        $fields = $this->get_field_by_name($field_name);

        $validate = new AnsPress_Validation(array($field_name => $fields));

        if(is_array($validate->get_errors()) && in_array($field_name, $validate->get_errors()))
            return $validate->get_errors();

        $fields = $validate->get_sanitized_fields();

        if($field_name == 'password'){
            wp_set_password( $password, $user_id );
            return true;
        }
        elseif($field_name == 'display_name')
        {
            return is_wp_error(wp_update_user( array( 'ID' => $user_id, 'display_name' => $fields[$field_name] ) ));
        }
        elseif($field_name == 'display_name' || $field_name == 'user_email' || $field_name == 'first_name' || $field_name == 'last_name' || $field_name == 'nickname')
        {
            return is_wp_error(wp_update_user( array( 'ID' => $user_id, $field_name => $fields[$field_name] ) ));
        }
        elseif(isset($fields[$field_name]))
            update_user_meta( $this->args['user_id'], $field_name, $fields[$field_name] );
    }
}

function ap_user_fields($args = ''){
    echo ap_user_get_fields($args)->get_form();
}

    function ap_user_get_fields($args = ''){
        global $ap_user_fields;
        $ap_user_fields = new AP_User_Fields($args);

        return $ap_user_fields;
    }