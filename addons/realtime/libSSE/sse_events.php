<?php
/*
* @package libSSE-php
* @author Licson Lee <licson0729@gmail.com>
* @description A PHP library for handling Server-Sent Events (SSE)
*/

/*
* @class SSEEvent
* @description The event placeholder class
*/

class SSEEvent {	
	public function check(){
		//data always updates
		return true;
	}
	
	public function update(){
		//returns nothing
		return '';
	}
};

/*
* @class SSETimedEvent
* @extends SSEEvent
* @description Event class for regular updates
*/

class SSETimedEvent extends SSEEvent {
	public $period = 1;
	private $start = 0;
	
	public function check(){
		if($this->start === 0) $this->start = time();
		if(SSEUtils::time_mod($this->start,$this->period) == 0) return true;
		else return false;
	}
};
