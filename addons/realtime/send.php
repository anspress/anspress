<?php

//@ini_set('display_errors', 0);
require_once('libSSE/libsse.php');

function get_latest_time($data){
	$data = (array)$data;

	if(count($data) == 1)
		foreach ($data as $k => $v)
			return $k;
		
	return max(array_keys($data));
}

function get_updates($data, $time){
	$new_updates = array();
	if(!empty($data))
		foreach((array)$data as $k => $update){
			if($k > $time)
				$new_updates[$k] = $update;
		}
	
	return (object)$new_updates;
}

$GLOBALS['data'] = new SSEData('file', array('path'=>'./data'));
$sse = new SSE();

class questionPage extends SSEEvent {
	private $cache = 0;
	private $data;
	public function update(){
		$load_time 	= $_GET['load_time'];
		$this->data->max_time = get_latest_time($this->data);		
		return json_encode(get_updates($this->data, $load_time));
	}
	
	/* This method will check when to send update */
	public function check(){
			/* Do some sanitization */
			$load_time 	= intval ($_GET['load_time']);
			$page 		= $_GET['page'];
			$qid 		= intval ($_GET['qid']);
			
			if(!preg_match("/^[A-Za-z0-9_-]+$/", $page))
				return false;
				
			$this->data = json_decode($GLOBALS['data']->get('question_'.$qid));		
			
			$event_time = 0;
			if(isset($this->data)){
				$event_time = get_latest_time($this->data);
			}

			if($this->cache == 0)
				$this->cache = $load_time;

			if(isset($this->data) && $event_time > $this->cache){

				if($event_time == 0)
					$this->cache = $load_time;
				else
					$this->cache = $event_time;
					
				return true;
			}
		//}
		return false;
	}
};

$event = filter_var($_GET['event'], FILTER_SANITIZE_STRING);

if ($event == 'question') 
	$event = 'question';

$sse->exec_limit = 30;
$sse->addEventListener($event, new questionPage());
$sse->start();