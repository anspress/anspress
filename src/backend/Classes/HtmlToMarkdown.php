<?php
/**
 * Html to markdown converter.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Classes;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

/**
 * Html to markdown converter.
 */
class HtmlToMarkdown {
	/**
	 * Conversion rules.
	 *
	 * @var array
	 */
	private $rules = array(
		'h1'     => array(
			'pattern'     => '/<h1>(.*?)<\/h1>/',
			'replacement' => '# $1',
		),
		'h2'     => array(
			'pattern'     => '/<h2>(.*?)<\/h2>/',
			'replacement' => '## $1',
		),
		'h3'     => array(
			'pattern'     => '/<h3>(.*?)<\/h3>/',
			'replacement' => '### $1',
		),
		'h4'     => array(
			'pattern'     => '/<h4>(.*?)<\/h4>/',
			'replacement' => '#### $1',
		),
		'h5'     => array(
			'pattern'     => '/<h5>(.*?)<\/h5>/',
			'replacement' => '##### $1',
		),
		'h6'     => array(
			'pattern'     => '/<h6>(.*?)<\/h6>/',
			'replacement' => '###### $1',
		),
		'strong' => array(
			'pattern'     => '/<strong>(.*?)<\/strong>/',
			'replacement' => '**$1**',
		),
		'b'      => array(
			'pattern'     => '/<b>(.*?)<\/b>/',
			'replacement' => '**$1**',
		),
		'em'     => array(
			'pattern'     => '/<em>(.*?)<\/em>/',
			'replacement' => '_$1_',
		),
		'i'      => array(
			'pattern'     => '/<i>(.*?)<\/i>/',
			'replacement' => '_$1_',
		),
		'p'      => array(
			'pattern'     => '/<p>(.*?)<\/p>/',
			'replacement' => '$1' . "\n\n",
		),
		'br'     => array(
			'pattern'     => '/<br\s*\/?>/',
			'replacement' => "\n",
		),
		'li'     => array(
			'pattern'     => '/<li>(.*?)<\/li>/',
			'replacement' => '* $1' . "\n",
		),
		'a'      => array(
			'pattern'     => '/<a href="(.*?)">(.*?)<\/a>/',
			'replacement' => '[$2]($1)',
		),
		'img'    => array(
			'pattern'     => '/<img src="(.*?)" alt="(.*?)"\s*\/?>/',
			'replacement' => '![$2]($1)',
		),
	);

	/**
	 * Convert HTML to markdown.
	 *
	 * @param string $html HTML content.
	 * @return string Markdown content.
	 */
	public function htmlToMarkdown( $html ) {
		foreach ( $this->rules as $rule ) {
			$html = preg_replace( $rule['pattern'], $rule['replacement'], $html );
		}

		// Convert lists separately to handle nested tags correctly.
		$html = $this->convertLists( $html );

		// Strip any remaining HTML tags.
		return wp_strip_all_tags( $html );
	}

	/**
	 * Helper methods for list conversion.
	 *
	 * @param string $html HTML content.
	 * @return string Markdown content.
	 */
	private function convertLists( $html ) {
		// Convert unordered lists.
		$html = preg_replace_callback(
			'/<ul>(.*?)<\/ul>/s',
			function ( $matches ) {
				return $this->convertUnorderedList( $matches[1] );
			},
			$html
		);

		// Convert ordered lists.
		$html = preg_replace_callback(
			'/<ol>(.*?)<\/ol>/s',
			function ( $matches ) {
				return $this->convertOrderedList( $matches[1] );
			},
			$html
		);

		return $html;
	}

	/**
	 * Convert unordered list.
	 *
	 * @param string $content Content.
	 * @return string
	 */
	private function convertUnorderedList( $content ) {
		return preg_replace( '/<li>(.*?)<\/li>/', '* $1', $content );
	}

	/**
	 * Convert ordered list.
	 *
	 * @param string $content Content.
	 * @return string
	 */
	private function convertOrderedList( $content ) {
		return preg_replace( '/<li>(.*?)<\/li>/', '1. $1', $content );
	}

	/**
	 * Add a new rule.
	 *
	 * @param string $tag         Tag name.
	 * @param string $pattern     Pattern.
	 * @param string $replacement Replacement.
	 * @return void
	 */
	public function addRule( $tag, $pattern, $replacement ) {
		$this->rules[ $tag ] = array(
			'pattern'     => $pattern,
			'replacement' => $replacement,
		);
	}
}
