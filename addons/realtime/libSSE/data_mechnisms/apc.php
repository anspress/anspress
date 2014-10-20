<?php
/*
* @package libSSE-php
* @author Licson Lee <licson0729@gmail.com>
* @description A PHP library for handling Server-Sent Events (SSE)
*/

/*
* @class SSEData_APC
* @description The APC key-value storage
*/

class SSEData_APC {
	/*
	* @method SSEData_APC::__construct
	* @param $args The parameters needed.
	*/
	public function __construct($args){
	}
	/*
	* @method SSEData_APC::get
	* @param $key the ID of the data
	* @description get the data by the key
	*/
	public function get($key){
		return apc_fetch($key);
	}
	/*
	* @method SSEData_APC::set
	* @param $key the ID of the data
	* @param $value the data
	* @description add data to the cache
	*/
	public function set($key,$value){
		return apc_store($key,$value);
	}
	/*
	* @method SSEData_APC::delete
	* @param $key the ID of the data
	* @description delete the data with the same ID specified
	*/
	public function delete($key){
		return apc_delete($key);
	}
};

//register the module
SSEData::register('apc','SSEData_APC');