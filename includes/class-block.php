<?php
/**
 * Block related functions.
 *
 * @package   AnsPress
 * @since     5.0.0
 */

namespace AnsPress;

/**
 * Class for handling block related operations.
 *
 * @package AnsPress
 */
class Block {
	/**
	 * Initialize block related hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'block_categories_all', array( $this, 'block_categories_all' ), 10 );

		add_action( 'init', array( $this, 'register_blocks' ) );
	}

	/**
	 * Hooks for block related operations.
	 *
	 * @return void
	 */
	public function register_blocks() {
		register_block_type( ANSPRESS_DIR . '/build/questions' );
	}

	/**
	 * Register AnsPress as a block category.
	 *
	 * @param array $categories Categories.
	 * @return array
	 */
	public function block_categories_all( $categories ) {
		return array_merge(
			$categories,
			array(
				array(
					'slug'  => 'anspress',
					'title' => __( 'AnsPress', 'anspress-question-answer' ),
					'icon'  => 'anspress-icon',
				),
			)
		);
	}
}
