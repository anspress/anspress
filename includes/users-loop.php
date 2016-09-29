<?php
/**
 * AnsPress users loop functions
 *
 * Helper functions for AnsPress users loop
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 */

class AP_user_query
{
	/**
	 * The loop iterator.
	 *
	 * @access public
	 * @var int
	 */
	var $current_user = -1;

	/**
	 * The number of users returned by the paged query.
	 *
	 * @access public
	 * @var int
	 */
	var $user_count;

	/**
	 * Array of users located by the query.
	 *
	 * @access public
	 * @var array
	 */
	var $users;

	/**
	 * The user object currently being iterated on.
	 *
	 * @access public
	 * @var object
	 */
	var $user;

	/**
	 * A flag for whether the loop is currently being iterated.
	 *
	 * @access public
	 * @var bool
	 */
	var $in_the_loop;

	/**
	 * The total number of users matching the query parameters.
	 *
	 * @access public
	 * @var int
	 */
	var $total_user_count;

	/**
	 * Items to show per page
	 *
	 * @access public
	 * @var int
	 */
	var $per_page;
	var $total_pages = 1;
	var $paged;
	var $offset;


	public function __construct($args = '') {

		$this->per_page = ap_opt( 'users_per_page' );

		// grab the current page number and set to 1 if no page number is set
		$this->paged = isset( $args['paged'] ) ? (int) $args['paged'] : (get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1);

		$this->offset = $this->per_page * ($this->paged - 1);

		$args = wp_parse_args( $args, array(
			'number' => $this->per_page,
			'offset' => $this->offset,
			'sortby' => 'reputation',
		));

		if ( isset( $args['ID'] ) ) {
			$this->users = array( get_user_by( 'id', $args['ID'] ) );
			$this->total_user_count = 1;
			$this->total_pages = 1;

			$this->user_count = 1;
		} else {

			if ( isset( $args['sortby'] ) ) {

				switch ( $args['sortby'] ) {
					case 'newest':
						$args['orderby']    = 'registered';
						$args['order']      = 'DESC';
						break;

					case 'active':
						$args['ap_query']    = 'user_sort_by_active';
						$args['orderby']    = 'meta_value date';
						$args['order']      = 'ASC';
						$args['meta_query'] = array(
							array(
								'key' => '__last_active',
							),
						);

						break;

					case 'best_answer':
						$args['ap_query']    = 'user_sort_by_best_answer';
						$args['orderby']    = 'meta_value date';
						$args['order']      = 'ASC';
						$args['meta_query'] = array(
							array(
								'key' => '__best_answers',
							),
						);

						break;

					case 'answer':
						$args['ap_query']    = 'user_sort_by_answer';
						$args['orderby']    = 'meta_value date';
						$args['order']      = 'ASC';
						$args['meta_query'] = array(
							array(
								'key' => '__total_answers',
							),
						);
						break;

					case 'followers':
						$args['ap_query']    = 'user_sort_by_followers';
						break;

					case 'following':
						$args['ap_query']    = 'user_sort_by_following';
						break;

					default:
						$args['ap_query']    = 'user_sort_by_reputation';
						$args['orderby']    = 'meta_value';
						$args['order']      = 'DESC';
						$args['meta_query'] = array(
							'relation' => 'OR',
							array(
								'key' => 'ap_reputation',
							),
							array(
								'key' => 'ap_reputation',
								'compare' => 'NOT EXISTS',
							)
						);

						break;
				}
			}

			$ap_user_query = new WP_User_Query( $args );
			$this->users = $ap_user_query->results;

			// count the number of users found in the query
			$this->total_user_count = $ap_user_query->get_total();
			$this->total_pages = ceil( $this->total_user_count / $this->per_page );

			$this->user_count = count( $this->users );
		}
	}

	public function users() {

		if ( $this->current_user + 1 < $this->user_count ) {
			return true;
		} elseif ( $this->current_user + 1 == $this->user_count ) {

			do_action( 'ap_user_loop_end' );
			// Do some cleaning up after the loop
			$this->rewind_users();
		}

		$this->in_the_loop = false;
		return false;
	}

	/**
	 * Check if there are users in loop
	 *
	 * @return bool
	 */
	public  function has_users() {
		if ( $this->user_count ) {
			return true; }

		return false;
	}

	/**
	 * Set up the next user and iterate index.
	 *
	 * @return object The next user to iterate over.
	 */
	public function next_user() {
		$this->current_user++;
		$this->user = $this->users[$this->current_user];

		return $this->user;
	}

	/**
	 * Rewind the users and reset user index.
	 */
	public function rewind_users() {
		$this->current_user = -1;
		if ( $this->user_count > 0 ) {
			$this->user = $this->users[0];
		}
	}

	/**
	 * Set up the current user inside the loop.
	 */
	public function the_user() {
		global $ap_the_user;

		$this->in_the_loop  = true;
		$this->user         = $this->next_user();
		$ap_the_user        = $this->user;

		// loop has just started
		if ( 0 == $this->current_user ) {

			/**
			 * Fires if the current user is the first in the loop.
			 */
			do_action( 'ap_user_loop_start' );
		}

	}

	public function is_main_query() {
		global $ap_user_query;
		return $ap_user_query === $this;
	}

	public function the_pagination() {

		$base = ap_get_link_to( 'users' ) . '/%_%';
		ap_pagination( $this->paged, $this->total_pages, $base );
	}
}

/**
 * Setup users loop
 * @param  string|array $args
 * @return AP_user_query
 */
function ap_has_users($args = '') {
	$sortby = ap_get_sort() != '' ? ap_get_sort() : 'reputation';

	$args = wp_parse_args( $args, array( 'sortby' => $sortby ) );

	return new AP_user_query( $args );
}

function ap_users() {
	global $ap_user_query;
	return $ap_user_query->users();
}

function ap_the_user() {
	global $ap_user_query;
	return $ap_user_query->the_user();
}

function ap_user_the_object($user_id = false) {
	global $ap_the_user;

	if ( ! isset( $ap_the_user ) && $user_id ) {
		return get_user_by( 'id', $user_id );
	}

	return $ap_the_user;
}

/**
 * Echo active user ID
 */
function ap_user_the_ID() {
	echo ap_user_get_the_ID();
}

	/**
	 * Return memeber ID active in loop
	 * @return integer
	 */
function ap_user_get_the_ID() {
	$user = ap_user_the_object();

	if ( ! isset( $user ) ) {
		return ap_get_displayed_user_id();
	}

	return $user->data->ID;
}

/**
 * Echo active user display name
 */
function ap_user_the_display_name() {
	echo ap_user_get_the_display_name();
}

/**
 * Return active user ID
 * @return string
 */
function ap_user_get_the_display_name() {
	return ap_user_display_name( array( 'user_id' => ap_user_get_the_ID() ) );
}

/**
 * Echo user login name
 * @since 3.0.0
 */
function ap_user_the_user_login() {
	echo ap_user_get_the_user_login();
}

/**
 * Return active user ID
 * @return string
 */
function ap_user_get_the_user_login() {
	$user = ap_user_the_object();
	if ( $user ) {
		return $user->data->user_login;
	}
}

	/**
	 * echo active user link
	 */
function ap_user_the_link() {
	echo ap_user_get_the_link();
}
	/**
	 * Retrive active user link
	 * @return string Link to user profile
	 */
function ap_user_get_the_link() {
	return ap_user_link( ap_user_get_the_ID() );
}

	/**
	 * Echo active user avatar
	 */
function ap_user_the_avatar($size = 40) {
	echo ap_user_get_the_avatar( $size );
}

	/**
	 * Retrive active user avatar
	 * @param  integer $size       height and width of avatar
	 * @return string       return avatar <img> tag
	 */
function ap_user_get_the_avatar($size = 40) {
	if ( is_ap_users() ) {
		$size = ap_opt( 'users_page_avatar_size' ); }

	return get_avatar( ap_user_get_the_ID(), $size );
}

	/**
	 * Echo active user reputation
	 * @param  boolean $short      Shorten count like 2.8k
	 */
function ap_user_the_reputation($short = true) {
	echo ap_user_get_the_reputation( $short );
}

	/**
	 * Get active user reputation
	 * @param  boolean $short Shorten count like 2.8k
	 * @return string
	 */
function ap_user_get_the_reputation($short = true) {
	return ap_get_reputation( ap_user_get_the_ID(), $short );
}

	/**
	 * output users page pagination
	 * @return string pagination html tag
	 */
function ap_users_the_pagination() {
	global $ap_user_query;
	$ap_user_query->the_pagination();
}

	/**
	 * Echo user meta
	 * @param  string          $key        user meta key
	 * @param  boolean|integer $user_id    user id
	 */
function ap_user_the_meta($key, $user_id = false) {
	$meta = ap_user_get_the_meta( $key, $user_id );

	if ( ! is_array( $meta ) ) {
		echo $meta; }
}

	/**
	 * Get the user meta by key
	 * if key is false then all metas of user will be returned.
	 *
	 * @param  boolean|string  $key        meta key
	 * @param  boolean|integer $user_id    user id
	 * @return array|string
	 */
function ap_user_get_the_meta($key = false, $user_id = false) {
	if ( ! $user_id ) {
		$user_id = ap_user_get_the_ID(); }

	$meta = get_user_meta( $user_id );

	if ( is_array( $meta ) ) {
		$meta = array_map( 'ap_meta_array_map', $meta ); }

	$obj = ap_user_the_object( $user_id );

	$meta['user_login']         = $obj->user_login;
	$meta['user_nicename']      = $obj->user_nicename;
	$meta['user_email']         = $obj->user_email;
	$meta['user_registered']    = $obj->user_registered;
	$meta['display_name']       = $obj->display_name;

	$ap_metas = array(
		'__total_questions',
		'__total_answers',
		'__profile_views',
		'__last_active',
		'__total_followers',
		'__total_following',
		'__up_vote_casted',
		'__down_vote_casted',
		'__up_vote_received',
		'__down_vote_received',
		'__best_answers',
	);

	// Set the default value for anspress meta
	foreach ( $ap_metas as $ap_meta ) {
		if ( ! isset( $meta[$ap_meta] ) ) {
			$meta[$ap_meta] = 0; }
	}

	if ( $key !== false && isset( $meta[$key] ) ) {
		return $meta[$key]; }

	if ( $key === false ) {
		return $meta; }
}

function ap_user_meta_exists($key, $user_id = false) {
	$meta = ap_user_get_the_meta( $key, $user_id );

	if ( ! empty( $meta ) ) {
		return true; }

	return false;
}

	/**
	 * Count total numbers of vote received by current user
	 * @return integer
	 */
function ap_user_total_votes_received() {
	return ap_user_get_the_meta( '__up_vote_received' ) + ap_user_get_the_meta( '__down_vote_received' );
}

	/**
	 * Count total numbers of votes casted by current user
	 * @return integer
	 */
function ap_user_total_votes_casted() {
	return ap_user_get_the_meta( '__up_vote_casted' ) + ap_user_get_the_meta( '__down_vote_casted' );
}

	/**
	 * Return array of user name, to be used in display name user field
	 * @param  integer $user_id
	 * @return array
	 * @since 2.1
	 */
function ap_user_get_display_name_option($user_id = false) {
	$user_id = ap_parameter_empty( @$user_id, @ap_user_get_the_ID() );
	$user = ap_user_get_the_meta( false, $user_id );

	$public_display = array();

	if ( ! empty( $user['nickname'] ) ) {
		$public_display[$user['nickname']] = $user['nickname']; }

	if ( ! empty( $user['user_login'] ) ) {
		$public_display[$user['user_login']] = $user['user_login']; }

	if ( ! empty( $user['first_name'] ) ) {
		$public_display[$user['first_name']] = $user['first_name']; }

	if ( ! empty( $user['last_name'] ) ) {
		$public_display[$user['last_name']] = $user['last_name']; }

	if ( ! empty( $user['first_name'] ) && ! empty( $user['last_name'] ) ) {
		$public_display[$user['first_name'] . ' ' . $user['last_name']] = $user['first_name'] . ' ' . $user['last_name'];
		$public_display[$user['last_name'] . ' ' . $user['first_name']] = $user['last_name'] . ' ' . $user['first_name'];
	}

	return $public_display;
}

function ap_user_get_member_for() {
	$registered = new DateTime( ap_user_get_registered_date() );
	$now = new DateTime( current_time( 'mysql' ) );
	$diff = date_diff( $registered, $now );

	$time = '';

	if ( $diff->y > 0 ) {
		$time .= sprintf( __( '%d years, ', 'anspress-question-answer' ), $diff->y ); }

	if ( $diff->m > 0 ) {
		$time .= sprintf( __( '%d months, ', 'anspress-question-answer' ), $diff->m ); }

	if ( $diff->d > 0 ) {
		$time .= sprintf( __( '%d days', 'anspress-question-answer' ), $diff->d ); }

	if ($time == '') {
		if ($diff->h > 0) {
			$time .= sprintf( __( '%d hours', 'anspress-question-answer' ), $diff->h ); }
		else {
			$time .= sprintf( __( '%d minutes', 'anspress-question-answer' ), $diff->i ); }
	}

	return $time;
}

	/**
	 * Get users registartion date
	 * @return string   Date
	 */
function ap_user_get_registered_date() {
	global $ap_user_query;

	if ( ! isset( $ap_user_query->user ) ) {
		return ap_get_displayed_user_id(); }

	$user = $ap_user_query->user;
	return $user->data->user_registered;
}

	/**
	 * Count user votes received in percentage
	 * @return integer
	 */
function ap_user_votes_received_percent() {
	$meta       = (int) ap_user_get_the_meta( '__up_vote_received' );
	$total_vote = (int) ap_user_total_votes_received();

	if ( $total_vote == 0 || $meta == 0 ) {
		return 0; } else {
		return ceil( ($meta / $total_vote) * 100 ); }
}

function ap_user_votes_casted_percent() {
	$meta       = (int) ap_user_get_the_meta( '__up_vote_casted' );
	$total_vote = (int) ap_user_total_votes_casted();

	if ( $total_vote == 0 || $meta == 0 ) {
		return 0; } else {
		return ceil( ($meta / $total_vote) * 100 ); }
}

/**
 * Get user signature. If no signature then return user role.
 * @param  boolean $user_id User ID.
 * @return string
 * @since  3.0.0
 */
function ap_get_user_signature( $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = ap_user_get_the_ID();
	}

	$signature = get_user_meta( $user_id, 'signature', true );

	$user = get_user_by( 'id', $user_id );

	if ( empty( $signature ) ) {
		$signature = str_replace( 'ap_', '', $user->roles[0] );
	}

	/**
	 * Filter user signature.
	 * @param  string $signature User signature.
	 * @return string
	 * @since  3.0.0
	 */
	return apply_filters( 'ap_user_signature', $signature );
}
