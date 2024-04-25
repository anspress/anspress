<?php

namespace AnsPress\Tests\WP\Testcases;

trait Ajax {
	public function _set_post_data( $query ) {
		$args            = wp_parse_args( $query );
		$_POST['action'] = 'ap_ajax';
		foreach ( $args as $key => $value ) {
			$_POST[ $key ] = $value;
		}
	}

	public function ap_ajax_success( $key = false, $return_json = false ) {
		$res = json_decode( $this->_last_response );

		if ( !$res ) {
			return false;
		}

		if ( false !== $return_json) {
			return $res;
		}
		if ( false !== $key ) {
			$this->assertObjectHasProperty( $key, $res );
			if ( ! isset( $res->$key ) ) {
				return false;
			}
			return $res->$key;
		}
	}

	public function getAjaxResponse() {
		return $this->ap_ajax_success( false, true );
	}

	public function handle( $action ) {
		try {
			$this->_handleAjax( $action );
		} catch ( \WPAjaxDieStopException $e ) {
			// Do nothing.
		} catch ( \WPAjaxDieContinueException $e ) {
			// Do nothing.
		}
	}

	public function functionHandle( $function, $args = [] ) {
		ini_set( 'implicit_flush', false );
		ob_start();
		try {
			$function( $args );
		} catch ( \WPAjaxDieStopException $e ) {
			// Do nothing.
		} catch ( \WPAjaxDieContinueException $e ) {
			// Do nothing.
		}
		$buffer = ob_get_clean();
		if ( ! empty( $buffer ) ) {
			$this->_last_response .= $buffer;
		}
	}
}
