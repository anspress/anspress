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
	 *
	 * @since 4.0.0.
	 */
	public static function init() {
		anspress()->add_action( 'ap_before_inserting_question', __CLASS__, 'before_inserting_question', 10, 2 );
		anspress()->add_action( 'ap_before_updating_question', __CLASS__, 'before_inserting_question', 10, 2 );
		anspress()->add_action( 'ap_before_inserting_answer', __CLASS__, 'before_inserting_question', 10, 2 );
		anspress()->add_action( 'ap_before_updating_answer', __CLASS__, 'before_inserting_question', 10, 2 );
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
		$bad_words = SELF::find_bad_words( $str );

		if ( false === $bad_words || empty( $bad_words ) ) {
			return $return;
		}

		return array(
			'form' 		 => ap_sanitize_unslash( 'ap_form_action' ),
			'success'  => false,
			'snackbar' => [ 'message' => __( 'Don’t use foul or abusive language. Let everything you say be good and helpful.', 'anspress-question-answer' ) ],
			'action'	 => 'bad_word_detected',
		);

	}

	/**
	 * Register Categories options
	 */
	public static function options() {
		ap_register_option_section( 'addons', basename( __FILE__ ) , __( 'Bad Words', 'anspress-question-answer' ), [
			array(
				'name'  => 'bad_words',
				'label' => __( 'Add comma separted bad words', 'anspress-question-answer' ),
				'desc'  => __( 'Enter words separted by comma. This option can be overriden by placing a <small>badwords.txt</small> in AnsPress override folder. While using bad word from file, add one word per line rather then comma separted.' ),
				'type'  => 'textarea',
			),
		]);
	}

	/**
	 * Check if checking for bad word is enabled.
	 *
	 * @return array
	 * @since  4.0.0
	 */
	public static function get_bad_words() {
		$bad_word_file = ap_get_theme_location( 'badwords.txt' );

		// Return if badwords.txt file does not exists.
		if ( file_exists( $bad_word_file ) ) {
			return  file( $bad_word_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
		}

		$option = ap_opt( 'bad_words' );

		if ( ! empty( $option ) ) {
			return explode( ',', $option );
		}

		return [];
	}

	/**
	 * Find bad words in a string.
	 *
	 * @param  string $str String need to be checked.
	 * @return array|boolean
	 * @since  4.0.0
	 */
	public static function find_bad_words( $str ) {
		$found = array();

		foreach ( (array) SELF::get_bad_words() as $w ) {
			$w = trim( $w );
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

}

// Init addon.
AnsPress_Bad_words::init();
