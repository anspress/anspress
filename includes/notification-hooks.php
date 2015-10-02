<?php
/**
 * Notification hooks.
 *
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      http://anspress.io
 * @copyright 2014 Rahul Aryan
 * @package   AnsPress/AnsPress_Notifications_Hooks
 */

/**
 * Register notification hooks
 */
class AnsPress_Notifications_Hooks
{
	/**
	 * AnsPress main class
	 * @var object
	 */
	protected $ap;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 * @param AnsPress $ap Parent class object.
	 */
	public function __construct($ap) {
		$ap->add_action('ap_select_answer', $this, 'select_answer_notification', 10, 3);
	}

	public function select_answer_notification($user_id, $question_id, $answer_id){
		ap_insert_notification( $user_id, $post->post_author, 'answer_selected', array( 'post_id' => $post->ID ) );
		ap_new_notification($user_id);
	}
}
