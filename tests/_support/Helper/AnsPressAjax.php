<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class AnsPressAjax extends \Codeception\Module
{
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

	public function handle( $action ) {
		try {
			$this->_handleAjax( $action );
		} catch ( \WPAjaxDieStopException $e ) {
			$this->_last_response = $e->getMessage();
		}
	}
}
