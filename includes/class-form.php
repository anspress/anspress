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

    }

    /**
     * Build the form 
     * @return void
     * @since 2.0
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
     * @since 2.0
     */
    private function form_head()
    {
        $attr = '';

        if($this->args['is_ajaxified'])
            $attr .= ' data-type="ap_ajax_form"';

        if(!empty($this->args['class']))
            $attr .= ' class="'.$this->args['class'].'"';

        $this->output .= '<form name="'.$this->args['name'].'" method="'.$this->args['method'].'" action="'.$this->args['action'].'"'.$attr.'>';
    }

    /**
     * FORM footer
     * @return void
     * @since 2.0
     */
    private function form_footer()
    {        
        $this->output .= '<button type="submit" class="ap-btn ap-submit-btn">'.$this->args['submit_button'].'</button>';
        $this->output .= '</form>';
    }

    private function nonce()
    {
        $this->output .=  wp_nonce_field( $this->name, '__nonce', true, false) ;
    }

    /**
     * Form hidden fields
     * @return void
     * @since 2.0
     */
    private function hidden_fields()
    {
        if($this->args['is_ajaxified'])
            $this->output .= '<input type="hidden" name="action" value="ap_submit_form">';

        $this->nonce();
    }

    /**
     * form field label
     * @return void
     * @since 2.0
     */
    private function label()
    {
        if($this->field['label'])
            $this->output .= '<label class="ap-form-label" for="'. @$this->field['name'] .'">'. @$this->field['label'] .'</label>';
    }

    /**
     * Output placeholder attribute of current field
     * @return string
     * @since 2.0
     */
    private function placeholder(){        
        return !empty($this->field['placeholder']) ? ' placeholder="'.$this->field['placeholder'].'"' : '';
    }

    /**
     * Output description of a form fields
     * @return void
     * @since 2.0
     */
    private function desc(){
        $this->output .= (!empty($this->field['desc']) ? '<p class="ap-field-desc">'.$this->field['desc'].'</p>' : '');
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
        $this->output .= '<input type="text" class="ap-form-control" value="'. @$field['value'] .'" name="'. @$field['name'] .'"'.$placeholder.' />';

        $this->desc();
    }

    /**
     * Checkbox field
     * @param  array  $field
     * @return void
     * @since 2.0
     */
    private function checkbox_field($field = array())
    {
        if(isset($field['label']))
            $this->label();
        
        if(!empty($field['desc']))
            $this->output .= '<div class="ap-checkbox-withdesc">';

        $this->output .= '<input type="checkbox" class="ap-form-control" value="'. @$field['value'] .'" name="'. @$field['name'] .'" '.checked( $field['value'], 1, false ).' />';
        $this->desc();

        if(!empty($field['desc']))
            $this->output .= '</div>';
    }

    /**
     * output select field options
     * @param  array  $field
     * @return void
     * @since 2.0
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
     * @since 2.0
     */
    private function select_field($field = array())
    {
        if(isset($field['label']))
            $this->label();
        
        $this->output .= '<select class="ap-form-control" value="'. @$field['value'] .'" name="'. @$field['name'] .'" >';
        $this->select_options($field);
        $this->output .= '</select>';
        $this->desc();
    }

    /**
     * output select field options
     * @param  array  $field
     * @return void
     * @since 2.0
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
     * @since 2.0
     */
    private function taxonomy_select_field($field = array())
    {
        if(isset($field['label']))
            $this->label();
        
        $this->output .= '<select class="ap-form-control" value="'. @$field['value'] .'" name="'. @$field['name'] .'" >';
        $this->taxonomy_select_options($field);
        $this->output .= '</select>';
        $this->desc();
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

        $placeholder = $this->placeholder();
        $this->output .= '<textarea rows="'. @$field['rows'] .'" class="ap-form-control" name="'. @$field['name'] .'"'.$placeholder.'>'. @$field['value'] .'</textarea>';

        $this->desc();
    }

    /**
     * Create wp_editor field
     * @param  array  $field
     * @return void      
     * @since 2.0
     */
    private function editor_field($field = array())
    {
        if(isset($field['label']))
            $this->label();

        /**
         * FILTER: ap_pre_editor_settings
         * Can be used to mody wp_editor settings
         * @var array
         * @since 2.0
         */
        $settings = apply_filters('ap_pre_editor_settings', $field['settings'] );

        // Turn on the output buffer
        ob_start();
        wp_editor( $field['value'], $field['name'], $field['settings'] );
        $this->output .= ob_get_clean();

        $this->desc();
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
         * @since 2.0
         */
        $this->args['fields'] =  apply_filters('ap_pre_form_fields', $this->args['fields'] );
        
        foreach($this->args['fields'] as $field){

            $this->field = $field;

            $this->output .= '<div class="ap-form-fields">';
           
            switch ($field['type']) {

                case 'text':
                    $this->text_field($field);
                    break;

                case 'checkbox':
                    $this->checkbox_field($field);
                    break;

                case 'select':
                    $this->select_field($field);
                    break;

                case 'taxonomy_select':
                    $this->taxonomy_select_field($field);
                    break;

                case 'textarea':
                    $this->textarea_field($field);
                    break;

                case 'editor':
                    $this->editor_field($field);
                    break;
                
                default:
                    /**
                     * FILTER: ap_form_fields_[type]
                     * filter for custom form field type
                     */
                    $this->output .= apply_filters( 'ap_form_fields_'.$field['type'],  $field);
                    break;
            }

            $this->output .= '</div>';
        }
    }

    /**
     * Output form
     * @return string
     * @since 2.0
     */
    public function get_form()
    {
        if(empty($this->args['fields']))
            return __('No fields found', 'ap');

        $this->build();

        return $this->output;
    }

}


function ap_ask_form(){
    $args = array(
        'name'              => 'ask_form',
        'is_ajaxified'      => true,
        'submit_button'     => __('Post question', 'ap'),
        'fields'            => array(
            array(
                'name' => 'title',
                'label' => __('Title', 'ap'),
                'type'  => 'text',
                'placeholder'  => __('Question in once sentence', 'ap'),
                'value' => sanitize_text_field(@$_POST['title'] )
            ),
            array(
                'name' => 'category',
                'label' => __('Category', 'ap'),
                'type'  => 'taxonomy_select',
                'value' => sanitize_text_field(@$_POST['category'] ),
                'taxonomy' => 'question_category'
            ),
            array(
                'name' => 'is_private',
                'label' => __('Private', 'ap'),
                'type'  => 'checkbox',
                'desc'  => __('This question ment to be private, only visible to admin and moderator.', 'ap'),
                'value' => sanitize_text_field(@$_POST['is_private'] )
            ),
            array(
                'name' => 'description',
                'label' => __('Description', 'ap'),
                'type'  => 'editor',
                'desc'  => __('Write question in detail', 'ap'),
                'rows'  => 5,
                'value' => @$_POST['description'],
                'settings' => array(
                    'editor_class' => 'ap-editor',
                ),
            ),
        ),
    );

    $form = new AnsPress_Form($args);

    echo $form->get_form();
}