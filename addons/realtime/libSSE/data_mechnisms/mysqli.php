<?php
/*
* @package libSSE-php
* @author Licson Lee <licson0729@gmail.com>
* @description A PHP library for handling Server-Sent Events (SSE)
*/

/*
* @class SSEData_MySQLi
* @description The MySQLi data mechnism
*/

class SSEData_MySQLi {
	private $conn;
	private $credinals;
	/*
	* @method SSEData_MySQLi::__construct
	* @param $credinals the data needed to connect to a database
	*/
	public function __construct($credinals){
		if($credinals !== null){
			$this->credinals = $credinals;
			if(!$this->connect()){
				throw new Exception('Error establishing connection.');
			}
			$this->prepare();
		}
		else
		{
			throw new Exception('No credinals specified.');
		}
	}
	/*
	* @method SSEData_MySQLi::connect
	* @description connect to the MySQL server
	*/
	private function connect(){
		$host = $this->credinals['host'];
		$user = $this->credinals['user'];
		$pass = $this->credinals['password'];
		$db = $this->credinals['db'];
		
		$this->conn = mysqli_connect($host,$user,$pass,$db);
		return (bool)$this->conn;
	}
	/*
	* @method SSEData_MySQLi::check_reconnect
	* @description check the connection is valid, if not then reconnect
	*/
	private function check_reconnect(){
		if(!mysqli_ping($this->conn)){
			if(!$this->connect()){
				throw new Exception('Error reconnect.');
			}
		}
	}
	/*
	* @method SSEData_MySQLi::escape
	* @param $str the string to escape
	* @description escape string to prevent SQL Injection
	*/
	private function escape($str){
		return mysqli_real_escape_string($this->conn,$str);
	}
	/*
	* @method SSEData_MySQLi::prepare
	* @description prepare the table to store data
	*/
	private function prepare(){
		return (bool)(mysqli_query($this->conn,'CREATE TABLE IF NOT EXISTS `sse_data_table` (`key` varchar(50) NOT NULL, `value` text, PRIMARY KEY (`key`) ) ENGINE=MEMORY DEFAULT CHARSET=utf8;'));
	}
	/*
	* @method SSEData_MySQLi::get
	* @param $key the ID of the data
	* @description get the data by the key
	*/
	public function get($key){
		$this->check_reconnect();
		$query = mysqli_query($this->conn,sprintf('SELECT * FROM `sse_data_table` WHERE `key` = \'%s\'',$this->escape($key)));
		$res = mysqli_fetch_assoc($query);
		return $res['value'];
	}
	/*
	* @method SSEData_MySQLi::set
	* @param $key the ID of the data
	* @param $value the data
	* @description add data to the table
	*/
	public function set($key,$value){
		if($this->get($key)){
			return mysqli_query($this->conn,sprintf("UPDATE `sse_data_table` SET `value` = '%s' WHERE `key` = '%s'",$this->escape($value),$this->escape($key)));
		}
		else {
			return mysqli_query($this->conn,sprintf("INSERT INTO `sse_data_table` SET `key` = '%s', `value` = '%s'",$this->escape($key),$this->escape($value)));
		}
	}
	/*
	* @method SSEData_MySQLi::delete
	* @param $key the ID of the data
	* @description delete the data with the same ID specified
	*/
	public function delete($key){
		return mysqli_query($this->conn,sprintf('DELETE FROM `sse_data_table` WHERE `key` == \'%s\'',$this->escape($key)));
	}
};
//register the module
SSEData::register('mysqli','SSEData_MySQLi');