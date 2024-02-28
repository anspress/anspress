<?php

namespace AnsPress\Tests\Testcases;

trait Ajax {
	public function ap_ajax_success( $key = false, $return_json = false ) {
		$res = json_decode( $this->_last_response );
		if ( false !== $return_json ) {
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
}
