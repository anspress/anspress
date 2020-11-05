<?php
/**
 * Custom abstract class for performing query.
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-3.0+
 * @link      https://anspress.net/
 * @copyright 2017 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Base class for query.
 */
abstract class AnsPress_Query {
	/**
	 * The loop iterator.
	 *
	 * @access public
	 * @var int
	 */
	var $current = -1;

	/**
	 * The number of rows returned by the paged query.
	 *
	 * @access public
	 * @var int
	 */
	var $count;

	/**
	 * Array of items located by the query.
	 *
	 * @access public
	 * @var array
	 */
	var $objects;

	/**
	 * The object currently being iterated on.
	 *
	 * @access public
	 * @var object
	 */
	var $object;

	/**
	 * A flag for whether the loop is currently being iterated.
	 *
	 * @access public
	 * @var bool
	 */
	var $in_the_loop;

	/**
	 * The total number of rows matching the query parameters.
	 *
	 * @access public
	 * @var int
	 */
	var $total_count;

	/**
	 * Items to show per page
	 *
	 * @access public
	 * @var int
	 */
	var $per_page = 20;

	/**
	 * Total numbers of pages based on query.
	 *
	 * @var int
	 */
	var $total_pages = 1;

	/**
	 * Current page.
	 *
	 * @var int
	 */
	var $paged = 1;

	/**
	 * Database query offset.
	 *
	 * @var int
	 */
	var $offset;

	/**
	 * Arguments.
	 *
	 * @var array
	 */
	var $args;

	/**
	 * Ids to be prefetched.
	 *
	 * @var array
	 */
	var $ids = [
		'post'     => [],
		'comment'  => [],
		'question' => [],
		'answer'   => [],
		'user'     => [],
	];
	var $pos = [
		'post'     => [],
		'comment'  => [],
		'question' => [],
		'answer'   => [],
		'user'     => [],
	];

	/**
	 * Initialize the class.
	 *
	 * @param array $args Arguments.
	 */
	public function __construct( $args = [] ) {
		$this->paged  = isset( $args['paged'] ) ? (int) $args['paged'] : 1;
		$this->offset = $this->per_page * ( $this->paged - 1 );

		$this->args = wp_parse_args(
			$args, array(
				'user_id' => get_current_user_id(),
				'number'  => $this->per_page,
				'offset'  => $this->offset,
				'order'   => 'DESC',
			)
		);

		$this->per_page = $this->args['number'];
		$this->query();
	}

	/**
	 * Count total numbers of rows found.
	 *
	 * @param string $key MD5 hashed key.
	 */
	public function total_count( $key ) {
		global $wpdb;
		$this->total_count = $wpdb->get_var( apply_filters( 'ap_found_rows', 'SELECT FOUND_ROWS()', $this ) ); // WPCS: db call
	}

	/**
	 * Fetch results from database.
	 */
	public function query() {
		$this->total_pages = ceil( $this->total_count / $this->per_page );
		$this->count       = count( $this->objects );
	}

	/**
	 * Check if loop has objects.
	 *
	 * @return boolean
	 */
	public function have() {
		if ( $this->current + 1 < $this->count ) {
			return true;
		} elseif ( $this->current + 1 === $this->count ) {
			do_action( 'ap_loop_end' );
			// Do some cleaning up after the loop.
			$this->rewind();
		}

		$this->in_the_loop = false;
		return false;
	}

	/**
	 * Rewind the object and reset index.
	 */
	public function rewind() {
		$this->current = -1;

		if ( $this->count > 0 ) {
			$this->object = $this->objects[0];
		}
	}

	/**
	 * Check if there are objects.
	 *
	 * @return bool
	 */
	public function has() {
		if ( $this->count ) {
			return true;
		}

		return false;
	}

	/**
	 * Set up the next object and iterate index.
	 *
	 * @return object The next object to iterate over.
	 */
	public function next() {
		$this->current++;
		$this->object = $this->objects[ $this->current ];
		return $this->object;
	}

	/**
	 * Set up the current object inside the loop.
	 */
	public function the_object() {
		$this->in_the_loop = true;
		$this->object      = $this->next();

		// Loop has just started.
		if ( 0 === $this->current ) {
			/**
			 * Fires if the current object is the first in the loop.
			 */
			do_action( 'ap_loop_start' );
		}
	}

	/**
	 * Add pre fetch ids.
	 *
	 * @param string        $type ids type.
	 * @param integer       $id id.
	 * @param false|integer $key Object key.
	 */
	public function add_prefetch_id( $type, $id, $key = false ) {
		if ( empty( $id ) ) {
			return;
		}

		if ( ! isset( $this->ids[ $type ] ) ) {
			$this->ids[ $type ] = [];
		}

		if ( ! in_array( $id, $this->ids[ $type ], true ) ) {
			$this->ids[ $type ][] = (int) $id;
		}

		if ( false !== $key ) {
			$this->add_pos( $type, $id, $key );
		}
	}

	/**
	 * Add position of reference in objects.
	 *
	 * @param string  $type ids type.
	 * @param integer $ref_id Reference ID.
	 * @param mixed   $key Object key.
	 */
	public function add_pos( $type, $ref_id, $key ) {
		if ( ! isset( $this->pos[ $type ] ) ) {
			$this->pos[ $type ] = [];
		}

		if ( ! isset( $this->pos[ $type ][ $ref_id ] ) ) {
			$this->pos[ $type ][ $ref_id ] = $key;
			return;
		}

		$prev_val = $this->pos[ $type ][ $ref_id ];

		if ( ! is_array( $prev_val ) ) {
			$this->pos[ $type ][ $ref_id ] = [ $prev_val, $key ];
			return;
		}

		if ( is_array( $prev_val ) ) {
			$this->pos[ $type ][ $ref_id ][] = $key;
		}
	}

	/**
	 * Add reference data to obejcts.
	 *
	 * @param string  $type ids type.
	 * @param integer $ref_id Reference ID.
	 * @param mixed   $data Reference data.
	 */
	public function append_ref_data( $type, $ref_id, $data ) {
		if ( isset( $this->pos[ $type ] ) && ( 0 == $this->pos[ $type ][ $ref_id ] || ! empty( $this->pos[ $type ][ $ref_id ] ) ) ) {
			$pos = $this->pos[ $type ][ $ref_id ];
			if ( is_array( $pos ) ) {
				foreach ( (array) $pos as $key ) {
					$this->objects[ $key ]->ref = $data;
				}
			} else {
				$this->objects[ $pos ]->ref = $data;
			}
		}
	}

	/**
	 * Include a template file.
	 *
	 * @param string $template Path to template file without .php extension.
	 */
	public function template( $template ) {
		include ap_get_theme_location( $template . '.php' );
	}

	/**
	 * Check if loop has more pages.
	 *
	 * @return boolean
	 * @since 4.1.2
	 */
	public function have_pages() {
		return $this->total_pages > $this->paged;
	}
}
