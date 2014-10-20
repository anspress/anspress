<?php
/*
* @package libSSE-php
* @author Licson Lee <licson0729@gmail.com>
* @description A PHP library for handling Server-Sent Events (SSE)
*/

/*
* @class SSEData
* @description A class for store data and access them between scripts using different mechnism.
*/

class SSEData {
	private $mech;//the mechnism to use
	private static $registers = array();//all mechnisms avaliable
	/*
	* @method SSEData::__construct
	* @param $method the mechnism to use
	* @param $credinals extra configuration for the mechnism
	* @description init a data instance for access data between scripts
	*/
	public function __construct($method,$credinals=null){
		if(isset(self::$registers[$method]) && self::$registers[$method]){
			$mech = self::$registers[$method];
			$this->mech = new $mech($credinals);
		}
		else {
			throw new Exception('No mechnism with the name found.');
		}
	}
	/*
	* @method SSEData::register
	* @param $method the mechnism's name
	* @param $class the name the the class that does the mechnism
	* @description register a mechnism
	*/
	static public function register($method,$class){
		self::$registers[$method] = $class;
	}
	/*
	* @method SSEDataLLget
	* @param $key the key of the corresponding value
	* @description get the specfic data with the key
	*/
	public function get($key){
		return $this->mech->get($key);
	}
	/*
	* @method SSEData::set
	* @param $key the key
	* @param $val the value to store
	* @description store a value using the given key and name pair
	*/
	public function set($key,$val){
		$this->mech->set($key,$val);
	}
	/*
	* @method SSEData::delete
	* @param $key the key
	* @description delete the data with the corresponding key
	*/
	public function delete($key){
		$this->mech->delete($key);
	}
};
