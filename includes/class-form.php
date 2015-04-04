<?php
/**
 * Form class
 *
 * @package  	AnsPress
 * @license  	http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     	http://wp3.in
 * @since 		2.0
 */

class AnsPress_Form {

    private $name;

    private $args = array();

    private $output = '';
    
    private $field;

    private $errors;

    /**
     * Initiate the class
     * @param array $args
     */
    public function __construct($args = array())
    {
        // Default args
        $defaults = array(
                //'name'         => '',
                'method'            => 'POST',
                'action'            => '',
                'is_ajaxified'      => false,
                'class'             => 'ap-form',
                'submit_button'     => __('Submit', 'ap'),
            );

        // Merge defaults args
        $this->args = wp_parse_args( $args, $defaults );

        // set the name of the form
        $this->name = $this->args['name'];

        global $ap_errors;
        $this->errors = $ap_errors;

        $this->add_default_in_field();

        $this->order_fields();
    }

    private function add_default_in_field()
    {
        if(!isset($this->args['fields']))
            return;
        
        foreach($this->args['fields'] as $k => $field){
            if(!isset($field['order']))
                $this->args['fields'][$k]['order'] = 10;

            if(!isset($field['show_desc_tip']))
                $this->args['fields'][$k]['show_desc_tip'] = true;
        }
    }

    /**
     * Order fields
     * @return void
     * @since 2.0.1
     */
    private function order_fields()
    {
        if(!isset($this->args['fields']))
            return;

        $this->args['fields'] = ap_sort_array_by_order($this->args['fields']);        
    }

    /**
     * Build the form 
     * @return void
     * @since 2.0.1
     */
    public function build()
    {
        $this->form_head();
        $this->form_fields();
        $this->hidden_fields();
        $this->form_footer();
    }

    /**
     * FORM element
     * @return void
     * @since 2.0.1
     */
    private function form_head()
    {
        $attr = '';

        if($this->args['is_ajaxified'])
            $attr .= ' data-action="ap_ajax_form"';

        if(!empty($this->args['class']))
            $attr .= ' class="'.$this->args['class'].'"';

        ob_start();
        /**
         * ACTION: ap_form_before_[form_name]
         * action for hooking before form
         * @since 2.0.1
         */
        do_action('ap_form_before_'. $this->name);
        $this->output .= ob_get_clean();

        $this->output .= '<form id="'.$this->args['name'].'" method="'.$this->args['method'].'" action="'.$this->args['action'].'"'.$attr.'>';
    }

    /**
     * FORM footer
     * @return void
     * @since 2.0.1
     */
    private function form_footer()
    { 
        ob_start();
        /**
         * ACTION: ap_form_bottom_[form_name]
         * action for hooking captcha and extar fields
         * @since 2.0.1
         */
        do_action('ap_form_bottom_'. $this->name);
        $this->output .= ob_get_clean();

        $this->output .= '<button type="submit" class="ap-btn ap-submit-btn">'.$this->args['submit_button'].'</button>';
        $this->output .= '</form>';
    }

    private function nonce()
    {
        $nonce_name = isset($this->args['nonce_name']) ? $this->args['nonce_name'] : $this->name;
        $this->output .=  wp_nonce_field( $nonce_name, '__nonce', true, false) ;
    }

    /**
     * Form hidden fields
     * @return void
     * @since 2.0.1
     */
    private function hidden_fields()
    {
        if($this->args['is_ajaxified'])
            $this->output .= '<input type="hidden" name="ap_ajax_action" value="'.$this->name.'">';
           
            $this->output .= '<input type="hidden" name="ap_form_action" value="'.$this->name.'">';

        $this->nonce();
    }

    /**
     * form field label
     * @return void
     * @since 2.0.1
     */
    private function label()
    {
        if($this->field['label'] && !$this->field['show_desc_tip']){
            $this->output .= '<label class="ap-form-label" for="'. @$this->field['name'] .'">'. @$this->field['label'].'</label>';
        }elseif($this->field['label']){
            $this->output .= '<label class="ap-form-label" for="'. @$this->field['name'] .'">'. @$this->field['label'];
            $this->desc();
            $this->output .= '</label>';
        }
    }

    /**
     * Output placeholder attribute of current field
     * @return string
     * @since 2.0.1
     */
    private function placeholder(){        
        return !empty($this->field['placeholder']) ? ' placeholder="'.$this->field['placeholder'].'"' : '';
    }

    /**
     * Output description of a form fields
     * @return void
     * @since 2.0.1
     */
    private function desc(){

        if(!$this->field['show_desc_tip'])
            $this->output .= (!empty($this->field['desc']) ? '<p class="ap-field-desc">'.$this->field['desc'].'</p>' : '');
        else
            $this->output .= (!empty($this->field['desc']) ? '<span class="ap-tip ap-field-desc" data-tipposition="right" title="'.esc_html($this->field['desc']).'">?</span>' : '');
    }

    /**
     * Output text fields
     * @param       array  $field
     * @return      void
     * @since       2.0
     */
    private function text_field($field = array())
    {
        if(isset($field['label']))
            $this->label();

        $placeholder = $this->placeholder();
        $autocomplete = isset($field['autocomplete'])  ? ' autocomplete="off"' : '';

        $this->output .= '<div class="ap-form-fields-in">';

        if(!isset($field['repeatable']) || !$field['repeatable'] ){
            
            $this->output .= '<input id="'. @$field['name'] .'" type="text" class="ap-form-control" value="'. @$field['value'] .'" name="'. @$field['name'] .'"'.$placeholder.' '. @$field['attr'] .$autocomplete.' />';
        }else{
            if(!empty($field['value']) && is_array($field['value'])){
                $this->output .= '<div id="ap-repeat-c-'. @$field['name'] .'" class="ap-repeatbable-field">';
                foreach($field['value'] as $k => $rep_f){                    
                    $this->output .= '<div id="ap_text_rep_'. @$field['name'] .'_'.$k.'" class="ap-repeatbable-field"><input id="'. @$field['name'] .'_'.$k.'" type="text" class="ap-form-control ap-repeatable-text" value="'. @$rep_f .'" name="'. @$field['name'] .'['.$k.']"'.$placeholder.' '. @$field['attr'] .$autocomplete.' />';
                    $this->output .= '<button data-action="ap_delete_field" type="button" data-toggle="'. @$field['name'] .'_'.$k.'">'.__('Delete').'</button>';
                    $this->output .= '</div>';
                }
                $this->output .= '</div>';

                $this->output .= '<div id="ap-repeatbable-field-'. @$field['name'] .'" class="ap-reapt-field-copy">';
                $this->output .= '<div id="ap_text_rep_'. @$field['name'] .'_#" class="ap-repeatbable-field"><input id="'. @$field['name'] .'_#" type="text" class="ap-form-control ap-repeatable-text" value="" name="'. @$field['name'] .'[#]"'.$placeholder.' '. @$field['attr'] .$autocomplete.' />';
                $this->output .= '<button data-action="ap_delete_field" type="button" data-toggle="'. @$field['name'] .'_#">'.__('Delete').'</button>';
                $this->output .= '</div></div>';
                $this->output .= '<button data-action="ap_add_field" type="button" data-field="ap-repeat-c-'. @$field['name'] .'" data-copy="ap-repeatbable-field-'. @$field['name'] .'">'.__('Add more').'</button>';
            }
        }

        $this->error_messages();

        if(!$this->field['show_desc_tip'])
            $this->desc();

        $this->output .= '</div>';
    }

    /**
     * Output text type="number"
     * @param       array  $field
     * @return      void
     * @since       2.0.0-alpha2
     */
    private function number_field($field = array())
    {
        if(isset($field['label']))
            $this->label();

        $placeholder = $this->placeholder();
        $autocomplete = isset($field['autocomplete'])  ? ' autocomplete="off"' : '';
        $this->output .= '<div class="ap-form-fields-in">';
        $this->output .= '<input id="'. @$field['name'] .'" type="number" class="ap-form-control" value="'. @$field['value'] .'" name="'. @$field['name'] .'"'.$placeholder.' '. @$field['attr'] .$autocomplete.' />';
        $this->error_messages();

        if(!$this->field['show_desc_tip'])
            $this->desc();

        $this->output .= '</div>';
    }

    /**
     * Checkbox field
     * @param  array  $field
     * @return void
     * @since 2.0.1
     */
    private function checkbox_field($field = array())
    {
        if(isset($field['label']))
            $this->label();
        
        $this->output .= '<div class="ap-form-fields-in">';

        if(!empty($field['desc']))
            $this->output .= '<label for="'. @$field['name'] .'">';

        $this->output .= '<input id="'. @$field['name'] .'" type="checkbox" class="ap-form-control" value="1" name="'. @$field['name'] .'" '.checked( (bool)$field['value'], true, false ).' '. @$field['attr'] .' />';

        // hack for getting value of unchecked checkbox
        $this->output .= '<input type="hidden" value="0" name="_hidden_'. @$field['name'] .'" />';

        if(!empty($field['desc']))
            $this->output .= @$field['desc'].'</label>';

        $this->error_messages();

        //if(!$this->field['show_desc_tip'])
            //$this->desc();

        $this->output .= '</div>';
    }

    /**
     * output select field options
     * @param  array  $field
     * @return void
     * @since 2.0.1
     */
    private function select_options($field = array())
    {
        foreach($field['options'] as $k => $opt )
            $this->output .= '<option value="'.$k.'" '.selected( $k, $field['value'], false).'>'.$opt.'</option>';
    }

    /**
     * Select fields
     * @param  array  $field
     * @return void
     * @since 2.0.1
     */
    private function select_field($field = array())
    {
        if(isset($field['label']))
            $this->label();
        $this->output .= '<div class="ap-form-fields-in">';
        $this->output .= '<select id="'. @$field['name'] .'" class="ap-form-control" value="'. @$field['value'] .'" name="'. @$field['name'] .'" '. @$field['attr'] .'>';
        $this->output .= '<option value=""></option>';
        $this->select_options($field);
        $this->output .= '</select>';
        $this->error_messages();
        if(!$this->field['show_desc_tip'])
            $this->desc();
        $this->output .= '</div>';
    }

    /**
     * output select field options
     * @param  array  $field
     * @return void
     * @since 2.0.1
     */
    private function taxonomy_select_options($field = array())
    {
        $taxonomies = get_terms( $field['taxonomy'], 'orderby=count&hide_empty=0&hierarchical=0' );
        
        if($taxonomies){
            foreach($taxonomies as $tax )
                $this->output .= '<option value="'.$tax->term_id.'" '.selected( $tax->term_id, $field['value'], false).'>'.$tax->name.'</option>';
        }
    }

    /**
     * Taxonomy select field
     * @param  array  $field
     * @return void
     * @since 2.0.1
     */
    private function taxonomy_select_field($field = array())
    {
        if(isset($field['label']))
            $this->label();
        $this->output .= '<div class="ap-form-fields-in">';
        $this->output .= '<select id="'. @$field['name'] .'" class="ap-form-control" value="'. @$field['value'] .'" name="'. @$field['name'] .'" '. @$field['attr'] .'>';
        $this->output .= '<option value=""></option>';
        $this->taxonomy_select_options($field);
        $this->output .= '</select>';
        $this->error_messages();
        if(!$this->field['show_desc_tip'])
            $this->desc();
        $this->output .= '</div>';
    }

    /**
     * Page select field
     * @param  array  $field
     * @return void
     * @since 2.0.0-alpha2
     */
    private function page_select_field($field = array())
    {
        if(isset($field['label']))
            $this->label();
        $this->output .= '<div class="ap-form-fields-in">';
        $this->output .= wp_dropdown_pages( array('selected'=> @$field['value'],'name'=> @$field['name'],'post_type'=> 'page', 'echo' => false) );
        $this->error_messages();
        if(!$this->field['show_desc_tip'])
            $this->desc();
        $this->output .= '</div>';
    }

    /**
     * textarea fields
     * @param       array  $field
     * @return      void
     * @since       2.0
     */
    private function textarea_field($field = array())
    {
        if(isset($field['label']))
            $this->label();
        $this->output .= '<div class="ap-form-fields-in">';
        $placeholder = $this->placeholder();
        $this->output .= '<textarea id="'. @$field['name'] .'" rows="'. @$field['rows'] .'" class="ap-form-control" name="'. @$field['name'] .'"'.$placeholder.' '. @$field['attr'] .'>'. @$field['value'] .'</textarea>';
        $this->error_messages();
        if(!$this->field['show_desc_tip'])
            $this->desc();
        $this->output .= '</div>';
    }

    /**
     * Create wp_editor field
     * @param  array  $field
     * @return void      
     * @since 2.0.1
     */
    private function editor_field($field = array())
    {
        if(isset($field['label']))
            $this->label();

        /**
         * FILTER: ap_pre_editor_settings
         * Can be used to mody wp_editor settings
         * @var array
         * @since 2.0.1
         */
        $field['settings']['tinymce'] = array( 
            'content_css' => ap_get_theme_url('css/editor.css') 
       );
        $settings = apply_filters('ap_pre_editor_settings', $field['settings'] );
        $this->output .= '<div class="ap-form-fields-in">';
        // Turn on the output buffer
        ob_start();
        echo '<div class="ap-editor">';
        wp_editor( $field['value'], $field['name'], $settings );
        echo '</div>';
        $this->output .= ob_get_clean();
        $this->error_messages();
        if(!$this->field['show_desc_tip'])
            $this->desc();
        $this->output .= '</div>';
    }
    /**
     * For creating hidden input fields
     * @param  array  $field
     * @return void
     * @since 2.0.1
     */
    private function hidden_field($field = array()){
        $this->output .= '<input type="hidden" value="'. @$field['value'] .'" name="'. @$field['name'] .'" '. @$field['attr'] .' />';
    }

    private function custom_field($field = array()){
        $this->output .= $field['html'];
    }

    /**
     * Check if current field have any error
     * @return boolean
     * @since 2.0.1
     */
    private function have_error(){
        if(isset($this->errors[$this->field['name']]))
            return true;

        return false;
    }
    private function error_messages(){
        if(isset($this->errors[$this->field['name']])){
            $this->output .= '<div class="ap-form-error-messages">';
            
            foreach($this->errors[$this->field['name']] as $error)
                $this->output .= '<p class="ap-form-error-message">'. $error .'</p>';

            $this->output .= '</div>';
        }
    }

    /**
     * Out put all form fields based on on their type
     * @return void
     * @since  2.0
     */
    private function form_fields()
    {
        /**
         * FILTER: ap_pre_form_fields
         * Provide filter to add or override form fields before output.
         * @var array
         * @since 2.0.1
         */
        $this->args['fields'] =  apply_filters('ap_pre_form_fields', $this->args['fields'] );
        
        foreach($this->args['fields'] as $field){

            $this->field = $field;

            $error_class = $this->have_error() ? ' ap-have-error' : '';
           
            switch ($field['type']) {

                case 'text':
                    $this->output .= '<div class="ap-form-fields'.$error_class.'">';
                    $this->text_field($field);
                    $this->output .= '</div>';
                    break;

                case 'number':
                    $this->output .= '<div class="ap-form-fields'.$error_class.'">';
                    $this->number_field($field);
                    $this->output .= '</div>';
                    break;

                case 'checkbox':
                    $this->output .= '<div class="ap-form-fields'.$error_class.'">';
                    $this->checkbox_field($field);
                    $this->output .= '</div>';
                    break;

                case 'select':
                    $this->output .= '<div class="ap-form-fields'.$error_class.'">';
                    $this->select_field($field);
                    $this->output .= '</div>';
                    break;

                case 'taxonomy_select':
                    $this->output .= '<div class="ap-form-fields'.$error_class.'">';
                    $this->taxonomy_select_field($field);
                    $this->output .= '</div>';
                    break;

                case 'page_select':
                    $this->output .= '<div class="ap-form-fields'.$error_class.'">';
                    $this->page_select_field($field);
                    $this->output .= '</div>';
                    break;

                case 'textarea':
                    $this->output .= '<div class="ap-form-fields'.$error_class.'">';
                    $this->textarea_field($field);
                    $this->output .= '</div>';
                    break;

                case 'editor':
                    $this->output .= '<div class="ap-form-fields'.$error_class.'">';
                    $this->editor_field($field);
                    $this->output .= '</div>';
                    break;

                case 'hidden':
                    $this->hidden_field($field);
                    break;

                case 'custom':
                    $this->custom_field($field);
                    break;
                
                default:
                    /**
                     * FILTER: ap_form_fields_[type]
                     * filter for custom form field type
                     */
                    $this->output .= apply_filters( 'ap_form_fields_'.$field['type'],  $field);
                    break;
            }            
        }
    }

    /**
     * Output form
     * @return string
     * @since 2.0.1
     */
    public function get_form()
    {
        if(empty($this->args['fields']))
            return __('No fields found', 'ap');

        $this->build();

        return $this->output;
    }

}


