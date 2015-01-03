<?php
/**
 * AnsPress form validation class
 * @link http://wp3.in
 * @since 2.0
 * @license GPL 2+
 * @package AnsPress
 */

class AnsPress_Validation
{
    private $args = array();

    private $errors = array();

    private $fields = array();

    /**
     * Initialize the class
     * @param array $args
     */
    public function __construct($args = array())
    {
        if(empty($args))
            return;

        $this->args = $args;

        $this->fields_to_include();

        $this->actions();
    }

    /**
     * Check fields to process
     * @return void
     * @since 2.0
     */
    private function fields_to_include()
    {
        foreach($this->args as $field => $actions){
            //if(isset($_REQUEST[$field]))
            $this->fields[$field] = @$_REQUEST[$field];
        }
    }

    /**
     * Check if field is empty or not set
     * @param  string $field
     * @return void
     * @since 2.0
     */
    public function required($field)
    {
        if(!isset($this->fields[$field]) || mb_strlen($this->fields[$field]) == 0 )
            $this->errors[$field][] = __('This field is required', 'ap');
    }

    /**
     * Sanitize text fields
     * @param  string $field
     * @return void
     * @since 2.0
     */
    private function sanitize_text_field($field)
    {
        if(isset($this->fields[$field]))
            $this->fields[$field] = sanitize_text_field($this->fields[$field]);
    }

    /**
     * Check length of a string, if less then specified then return error
     * @param  string $field
     * @param  string $param
     * @return void
     * @since  2.0
     */
    private function length_check($field, $param)
    {
        if(!isset($this->fields[$field]) || mb_strlen($this->fields[$field]) < $param )
            $this->errors[$field][] = sprintf(__('Its too short, it must be minimum %d characters', 'ap'), $param);
    }

    /**
     * Sanitize as a boolean value
     * @param  array $field
     * @return void
     * @since 2.0
     */
    private function only_boolean($field)
    {

        $this->fields[$field] = (bool) $this->fields[$field];

    }

    /**
     * Sanitize as a integer value
     * @param  array $field
     * @return void
     * @since 2.0
     */
    private function only_int($field)
    {

        $this->fields[$field] = (int) $this->fields[$field];

    }

    /**
     * Sanitize field based on actions passed
     * @param  string $field
     * @param  array $actions
     * @return void
     * @since 2.0
     */
    private function sanitize($field, $actions)
    {
        foreach($actions as $type ){
            switch ($type) {
                case 'sanitize_text_field':
                    $this->sanitize_text_field($field);
                    break;

                case 'only_boolean':                    
                    $this->only_boolean($field);
                    break;
                
                default:
                    $this->fields[$field] = apply_filters('ap_validation_sanitize_field', $field, $actions );
                    break;
            }
        }
    }

    /**
     * Validate a field based on actions passed
     * @param  string $field   
     * @param  array $actions
     * @return void          
     * @since 2.0
     */
    private function validate($field, $actions)
    {

        foreach($actions as $type => $param){
            if(isset($this->errors[$field]))
                return;
            
            switch ($type) {
                case 'required':
                    $this->required($field);
                    break;

                case 'length_check':
                    $this->length_check($field, $param);
                    break;
                
                default:
                    $this->errors[$field] = apply_filters('ap_validation_validate_field', $field, $actions );
                    break;
            }
        }
    }

    /**
     * Field is being checked and sanitized
     * @return void
     * @since 2.0
     */
    private function actions()
    {
        foreach($this->args as $field => $actions){
            if(isset($actions['sanitize']))
                $this->sanitize($field, $actions['sanitize']);

            if(isset($actions['validate']))
                $this->validate($field, $actions['validate']);
        }
            
    }

    /**
     * Check if fields have any error
     * @return boolean
     * @since 2.0
     */
    public function have_error(){
        if(count($this->errors) > 0)
            return true;

        return false;
    }

    /**
     * Get all errors
     * @return array | boolean
     */
    public function get_errors(){
        if(count($this->errors) > 0)
            return $this->errors;

        return false;
    }

    /**
     * Return all sanitized fields
     * @return array
     * @since 2.0
     */
    public function get_sanitized_fields()
    {
        return $this->fields;
    }
}
    
?>