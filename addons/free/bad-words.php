<?php
/**
 * An AnsPress add-on to check and filter bad words in
 * question, answer and comments. Add restricted words
 * after activating addon.
 *
 * @author    Rahul Aryan <support@rahularyan.com>
 * @copyright 2014 AnsPress.io & Rahul Aryan
 * @license   GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link      https://anspress.io
 * @package   WordPress/AnsPress/BadWords
 *
 * Addon Name:    Bad Words
 * Addon URI:     https://anspress.io
 * Description:   Check and filter bad words in AnsPress content.
 * Author:        Rahul Aryan
 * Author URI:    https://anspress.io
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Bad words filter hooks.
 */
class AnsPress_Bad_words {
	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 * @since 2.4.8 Removed `$ap` args.
	 */
	public static function init( ) {

		anspress()->add_action( 'ap_before_inserting_question', __CLASS__, 'before_inserting_question', 10, 2 );
		anspress()->add_action( 'ap_before_updating_question', __CLASS__, 'before_inserting_question', 10, 2 );
		anspress()->add_action( 'ap_before_inserting_answer', __CLASS__, 'before_inserting_question', 10, 2 );
		anspress()->add_action( 'ap_before_updating_answer', __CLASS__, 'before_inserting_question', 10, 2 );
		anspress()->add_action( 'ap_before_inserting_comment', __CLASS__, 'before_inserting_question', 10, 2 );
		anspress()->add_action( 'ap_before_updating_comment', __CLASS__, 'before_inserting_question', 10, 2 );
		anspress()->add_action( 'ap_option_groups', __CLASS__, 'options' );

	}

	/**
	 * Hook to check bad words before inserting question into database.
	 *
	 * @param  boolean $return Return ajax response or boolean.
	 * @param  string  $str    Question form fields.
	 * @return array|boolean
	 * @since 2.4.5
	 */
	public static function before_inserting_question( $return, $str ) {
		$bad_words = ap_find_bad_words( $str );

		if ( false === $bad_words || empty( $bad_words ) ) {
			return $return;
		}

		return array(
			'form' 			       => ap_sanitize_unslash( 'ap_form_action' ),
			'message_type' 	   => 'error',
			'message'		       => __( 'Don’t use foul or abusive language. Let everything you say be good and helpful.', 'anspress-question-answer' ),
			'action'		       => 'bad_word_detected',
		);

	}

	/**
	 * Register Categories options
	 */
	public static function options() {
		ap_register_option_section( 'addons', basename( __FILE__ ) , __( 'Bad Words', 'anspress-question-answer' ), [
			array(
				'name'  => 'check_question',
				'label' => __( 'Check bad words in question', 'anspress-question-answer' ),
				'desc'  => __( 'Enable this to check for bad words in question content.' ),
				'type'  => 'checkbox',
			),
			array(
				'name'  => 'check_answer',
				'label' => __( 'Check bad words in answer', 'anspress-question-answer' ),
				'desc'  => __( 'Enable this to check for bad words in answer content.' ),
				'type'  => 'checkbox',
			),
			array(
				'name'  => 'check_comment',
				'label' => __( 'Check bad words in comments', 'anspress-question-answer' ),
				'desc'  => __( 'Enable this to check for bad words in AnsPress comments.' ),
				'type'  => 'checkbox',
			),
			array(
				'name'  => 'action',
				'label' => __( 'What to do?', 'anspress-question-answer' ),
				'desc'  => __( 'Select what to do when AnsPress detects any bad words in contents.' ),
				'type'  => 'select',
				'options'  => array(
					'no_submission' => __( 'Prevent form to be submitted', 'anspress-question-answer' ),
					'replace' => __( 'Replace with defined character', 'anspress-question-answer' ),
					'strip' => __( 'Strip characters', 'anspress-question-answer' ),
				),
			),
			array(
				'name'  => 'words',
				'label' => __( 'Add comma separted bad words', 'anspress-question-answer' ),
				'type'  => 'textarea',
			),
		]);
	}

}

/**
 * Check if checking for bad word is enabled.
 *
 * @return boolean
 * @since  2.4.5
 */
function ap_check_for_bad_words() {
	$bad_word_file = ap_get_theme_location( 'badwords.txt' );

	// Return if badwords.txt file does not exists.
	if ( ! file_exists( $bad_word_file ) ) {
		return false;
	}

	if ( ap_opt( 'check_bad_words' ) ) {
		return true;
	}

	return false;
}

/**
 * Find bad words in a string.
 *
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
		$count = preg_match_all( '/\b' . preg_quote( $w,'/' ) . '\b/i', $str );

		if ( $count > 0 ) {
			$found[ $w ] = $count;
		}
	}

	if ( ! empty( $found ) ) {
		return $found;
	}

	return false;
}

// Init addon.
AnsPress_Bad_words::init();
