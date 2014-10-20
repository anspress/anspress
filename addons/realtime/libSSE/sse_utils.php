<?php
/*
* @package libSSE-php
* @author Licson Lee <licson0729@gmail.com>
* @description A PHP library for handling Server-Sent Events (SSE)
*/

/*
* @class SSEUtils
* @description Helper class
*/

class SSEUtils {
	/*
	* @method SSEUtils::sseData
	* @param $str the data to be processed
	* @description Make strings SSE friendly (For internal use only)
	*/
	static public function sseData($str){
		return 'data: '.str_replace("\n","\ndata: ",$str);
	}
	/*
	* @method SSEUtils::sseBlock
	* @param $id the event ID
	* @param $event the event name
	* @param $data the event data
	* @description method for output a SSE data block (For internal use only)
	*/
	static public function sseBlock($id,$event,$data){
		echo 'id: '.$id."\n";
		if($event != '') echo 'event: '.$event."\n";
		echo self::sseData($data)."\n\n";//send the data
	}
	/*
	* @method SSEUtils::time_mod
	* @param $start the start timestamp
	* @param $n the time interval
	* @description Calculate the modulus of time
	*/
	static public function time_mod($start,$n){
		return (time() - $start) % $n;
	}
	/*
	* @method SSEUtils::time_diff
	* @param $start the start timestamp
	* @description Calculate the time difference
	*/
	static public function time_diff($start){
		return time() - $start;
	}
	
}
