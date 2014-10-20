<?php
/*
* @package libSSE-php
* @author Licson Lee <licson0729@gmail.com>
* @description A PHP library for handling Server-Sent Events (SSE)
*/

/*
* @class SSEData_MySQL
* @description The MySQL data mechnism
*/

class SSEData_MySQL {
	private $conn;//the mysql connection
	private $credinals;//the credinals needed to connect to a MySQL database
	/*
	* @method SSEData_MySQL::__construct
	* @param $credinals the data needed to connect to a database
	*/
	public function __construct($credinals){
		if($credinals !== null){
			$this->credinals = $credinals;//save it for reconnetion
			if(!$this->connect()){//connect to the database
				//fail to connect
				throw new Exception('Error establishing connection.');
			}
			//prepare the table to use
			$this->prepare();
		}
		else
		{
			//no credinals specified
			throw new Exception('No credinals specified.');
		}
	}
	/*
	* @method SSEData_MySQL::connect
	* @description connect to the MySQL server
	*/
	private function connect(){
		$host = $this->credinals['host'];//MySQL host
		$user = $this->credinals['user'];//the login username
		$pass = $this->credinals['password'];//the login password
		$db = $this->credinals['db'];//the database to use
		
		//connect to the database
		$this->conn = mysql_pconnect($host,$user,$pass);
		mysql_select_db($db,$this->conn);//select the database to use
		return (bool)$this->conn;
	}
	/*
	* @method SSEData_MySQL::check_reconnect
	* @description check the connection is valid, if not then reconnect
	*/
	private function check_reconnect(){
		if(!mysql_ping($this->conn)){
			if(!$this->connect()){
				throw new Exception('Error reconnect.');
			}
		}
	}
	/*
	* @method SSEData_MySQL::escape
	* @param $str the string to escape
	* @description escape string to prevent SQL Injection
	*/
	private function escape($str){
		return mysql_real_escape_string($str,$this->conn);
	}
	/*
	* @method SSEData_MySQL::prepare
	* @description prepare the table to store data
	*/
	private function prepare(){
		return (bool)(mysql_query('CREATE TABLE IF NOT EXISTS `sse_data_table` (`key` varchar(50) NOT NULL, `value` text, PRIMARY KEY (`key`) ) ENGINE=MEMORY DEFAULT CHARSET=utf8;',$this->conn));
	}
	/*
	* @method SSEData_MySQL::get
	* @param $key the ID of the data
	* @description get the data by the key
	*/
	public function get($key){
		$this->check_reconnect();
		$query = mysql_query(sprintf('SELECT * FROM `sse_data_table` WHERE `key` = \'%s\'',$this->escape($key)),$this->conn);
		$res = mysql_fetch_assoc($query);
		return $res['value'];
	}
	/*
	* @method SSEData_MySQL::set
	* @param $key the ID of the data
	* @param $value the data
	* @description add data to the table
	*/
	public function set($key,$value){
		if($this->get($key)){
			return mysql_query(sprintf("UPDATE `sse_data_table` SET `value` = '%s' WHERE `key` = '%s'",$this->escape($value),$this->escape($key)),$this->conn);
		}
		else {
			return mysql_query(sprintf("INSERT INTO `sse_data_table` SET `key` = '%s', `value` = '%s'",$this->escape($key),$this->escape($value)),$this->conn);
		}
	}
	/*
	* @method SSEData_MySQL::delete
	* @param $key the ID of the data
	* @description delete the data with the same ID specified
	*/
	public function delete($key){
		return mysql_query(sprintf('DELETE FROM `sse_data_table` WHERE `key` == \'%s\'',$this->escape($key)),$this->conn);
	}
};

//register the module
SSEData::register('mysql','SSEData_MySQL');