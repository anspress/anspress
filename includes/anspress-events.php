<?php
/**
 * AnsPress.
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://rahularyan.com
 * @copyright 2014 Rahul Aryan
 */

function ap_do_event(){
	$args = func_get_args ();
	do_action('ap_event', $args);
	//do_action('ap_event_'.$args[0], $args);
	$action = 'ap_event_'.$args[0];
	$args[0] = $action;
	call_user_func_array('do_action', $args);
}