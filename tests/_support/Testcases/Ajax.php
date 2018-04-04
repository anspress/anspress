<?php

namespace AnsPress\Tests\Testcases;

include_once str_replace( 'includes/../data', '', DIR_TESTDATA ) . '/includes/exceptions.php';

trait Ajax {
	public function ap_ajax_success( $key = false, $return_json = false ) {
		preg_match( '#<div[^>]*>(.*?)</div>#', $this->_last_response, $match );
		if ( ! isset( $match[1] ) ) {
			return false;
		}
		$res = json_decode( $match[1] );
		if ( false !== $return_json ) {
			return $res;
		}
		if ( false !== $key ) {
			$this->assertObjectHasAttribute( $key, $res );
			if ( ! isset( $res->$key ) ) {
				return false;
			}
			return $res->$key;
		}
	}

	public function getAjaxResponse() {
		return $this->ap_ajax_success( false, true );
	}
}
