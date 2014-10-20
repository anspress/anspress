<?php
/*
* @package libSSE-php
* @author Licson Lee <licson0729@gmail.com>
* @description A PHP library for handling Server-Sent Events (SSE)
*/

/*
* @class SSEData_File
* @description The file data mechnism
*/

class SSEData_File {
	private $path; //the path to save the keys as files
	private $gc_lifetime = 600; //How long should the garbage collector run in seconds
	
	/*
	* @method SSEData_File::__construct
	* @param $args The parameters needed.
	*/
	public function __construct($args){
		if(isset($args['path'])){
			$this->path = $args['path'];
			if(!is_dir($this->path)){
				mkdir($this->path,0777);
			}
		}
		else {
			throw new Exception('Save path must be present.');
		}
		
		if(isset($args['gc_lifetime'])){
			$this->gc_lifetime = $args['gc_lifetime'];
		}
	}
	/*
	* @method SSEData_File::get
	* @param $key the ID of the data
	* @description get the data by the key
	*/
	public function get($key){
		$content = (string)@file_get_contents($this->path.'/sess_'.sha1($key));
		$this->gc();
		return $content;
	}
	/*
	* @method SSEData_File::set
	* @param $key the ID of the data
	* @param $value the data
	* @description add data to the file
	*/
	public function set($key,$value){
		$result = file_put_contents($this->path.'/sess_'.sha1($key),$value) === false ? false : true;
		$this->gc();
		return $result;
	}
	/*
	* @method SSEData_File::delete
	* @param $key the ID of the data
	* @description delete the data with the same ID specified
	*/
	public function delete($key){
		$path = $this->path.'/sess_'.sha1($key);
		if(file_exists($path)){
			unlink($path);
		}
		return true;
	}
	/*
	* @method SSEData_File::gc
	* @description remove keys that are unused for a specified period
	*/
	private function gc(){
		foreach(glob($this->path.'/sess_*') as $file){
			if(filemtime($file)+$this->gc_lifetime < time() && file_exists($file)){
				unlink($file);
			}
		}
		return true;
	}
};

//register the module
SSEData::register('file','SSEData_File');