<?php
/**
 * All deprecated classes.
 *
 * @since 5.0.0
 * @package AnsPress
 * @codingStandardsIgnoreFile
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

/**
 * All flag methods.
 *
 * @deprecated 5.0.0
 */
class AnsPress_Flag {
	/**
	 * Ajax callback to process post flag button
	 *
	 * @since 2.0.0
	 * @deprecated 5.0.0
	 */
	public static function action_flag() {
		_deprecated_function( __FUNCTION__, '5.0.0' );
	}
}

/**
 * Comments class
 *
 * @deprecated 5.0.0
 */
class AnsPress_Comment_Hooks {

}

/**
 * The `comment_modal` ajax callback.
 *
 * @since 4.1.8
 * @deprecated 5.0.0
 */
class Comment_Modal {
}

/**
 * AnsPress vote related class.
 *
 * @deprecated 5.0.0
 */
class AnsPress_Vote {
}
