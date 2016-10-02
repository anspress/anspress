<?php
/**
 * AnsPress bad words filter
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 * @since     2.4.5
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Bad words filter hooks.
 */
class AP_Bad_words
{
	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 * @since 2.4.8 Removed `$ap` args.
	 */
	public function __construct( ) {

		// Return if reputation is disabled.
		if ( ! ap_check_for_bad_words() ) {
			return;
		}

		anspress()->add_action( 'ap_before_inserting_question', $this, 'before_inserting_question', 10, 2 );
		anspress()->add_action( 'ap_before_updating_question', $this, 'before_inserting_question', 10, 2 );
		anspress()->add_action( 'ap_before_inserting_answer', $this, 'before_inserting_question', 10, 2 );
		anspress()->add_action( 'ap_before_updating_answer', $this, 'before_inserting_question', 10, 2 );
		anspress()->add_action( 'ap_before_inserting_comment', $this, 'before_inserting_question', 10, 2 );
		anspress()->add_action( 'ap_before_updating_comment', $this, 'before_inserting_question', 10, 2 );
	}

	/**
	 * Hook to check bad words before inserting question into database.
	 * @param  boolean $return Return ajax response or boolean.
	 * @param  string  $str    Question form fields.
	 * @return array|boolean
	 * @since 2.4.5
	 */
	public function before_inserting_question( $return, $str ) {
		$bad_words = ap_find_bad_words( $str );

		if ( false === $bad_words || empty($bad_words ) ) {
			return $return;
		}

		return array(
			'form' 			=> esc_attr( $_POST['ap_form_action'] ),
			'message_type' 	=> 'error',
			'message'		=> __( 'Don’t use foul or abusive language. Let everything you say be good and helpful.', 'anspress-question-answer' ),
			'action'		=> 'bad_word_detected',
		);

	}

}

/**
 * Check if checking for bad word is enabled.
 * @return boolean
 * @since  2.4.5
 */
function ap_check_for_bad_words() {
	$bad_word_file = ap_get_theme_location( 'badwords.txt' );

	// Return if badwords.txt file does not exists.
	if ( ! file_exists( $bad_word_file ) ) {
		return false;
	}

	if ( ap_opt('check_bad_words' ) ) {
		return true;
	}
	return false;
}

/**
 * Find bad words in a string.
 * @param  string $str String need to be checked.
 * @return array|boolean
 * @since  2.4.5
 */
function ap_find_bad_words( $str ) {
	$bad_word_file = ap_get_theme_location( 'badwords.txt' );

	// Return if badwords.txt file does not exists.
	if ( ! file_exists( $bad_word_file ) ) {
		return false;
	}

	$words = file( $bad_word_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );

	$found = array();

	foreach ( $words as $w ) {
		$count = preg_match_all('/\b'.preg_quote($w,'/' ).'\b/i', $str );

		if ( $count > 0 ) {
			$found[$w] = $count;
		}
	}

	if ( ! empty($found ) ) {
		return $found;
	}

	return false;
}

