<?php

/**
 * Simple MySQL-wrapper
 */

class DB{
	/**
	 * Connection-object
	 *
	 * @access private
	 * @static
	 * @var object
	 **/
	private static $conn = false;

	/**
	 * Connection-object
	 *
	 * @access private
	 * @static
	 * @var string
	 **/
	private static $settingstable = "hem_settings";
	
	/**
	 * Runs initiate()
	 * 
	 * @see initiate()
	 */
	function __construct(){
		self::initiate();
	}
	
	/**
	 * Opens connection to MySQL DB, if not already exists
	 * 
	 * @access private
	 * @static
	 * @return void
	 */
	private static function initiate(){
		if(!self::$conn){
			self::$conn = new MySQLi(System::getConfig('mysqlHost'), System::getConfig('mysqlUser'), System::getConfig('mysqlPswd'), System::getConfig('mysqlDB'));
			self::$conn->set_charset('utf8');
		}
	}
	
	/**
	 * Returns MySQLi-instance
	 * 
	 * @access public
	 * @static
	 * @return MySQLi
	 */
	public static function &getInstance(){
		self::initiate();
		return self::$conn;
	}
	
	/**
	 * Performs a SELECT, SHOW, DESCRIBE or EXPLAIN query
	 * and returns the result as an assocative array
	 *
	 * @access public
	 * @static
	 * @param $query string
	 * @return array
	 */
	public static function get($query){
		self::initiate();
		$q = self::$conn->query($query);
		
		if($q !== false){
			
			if($q->num_rows > 1){
				$result = array();
				while($r = $q->fetch_assoc()){
					$result[] = $r;
				}
			
				return $result;
			}else{
				return $q->fetch_assoc();
			}
		}else{
			#$bt = debug_backtrace();
			#if(self::$conn->error !== ""){trigger_error(self::$conn->error." :: ".debug_print_backtrace(),E_USER_ERROR);}
			return false;
		}
	}
	
	/**
	 * Performs a UPDATE, DELETE, CREATE, ALTER, DROP, etc. query
	 * and returns the MySQLi_Result object
	 *
	 * @access public
	 * @static
	 * @param $query string
	 * @return MySQLi_Result
	 */
	public static function set($query){
		self::initiate();
		if(preg_match("/^(SELECT|SHOW|DESCRIBE|EXPLAIN).*$/",$query) > 0){
			die("DB::set() will not handle SELECT, SHOW, DESCRIBE or EXPLAIN queries");
		}
		$q = self::$conn->query($query);
		return $q;
	}
	
	/**
	 * Returns the last insert ID
	 * 
	 * @access public
	 * @static
	 * @return int
	 */
	public static function id(){
		return self::$conn->insert_id;
	}
	

	/**
	 * Returns the last error
	 * 
	 * @access public
	 * @static
	 * @return string
	 */
	public static function error(){
		return self::$conn->error;
	}
	
	/**
	 * Escapes data with the MySQLi escape function
	 *
	 * @access public
	 * @static
	 * @param $str string
	 * @return string
	 */
	public static function escape($str){
		return self::$conn->escape_string($str);
	}
	
	/**
	 * 
	 */
	public static function _get($name){
		self::initiate();
		$c_name = self::$conn->escape_string($name);
		$get = self::get("SELECT value FROM {self::$settingstable} WHERE name = '{$c_name}'");
		return $get['value'];
	}
	
	/**
	 * 
	 */
	public static function _set($name, $value){
		self::initiate();
		$c_name = self::$conn->escape_string($name);
		$c_value = self::$conn->escape_string($value);
		if(self::get("SELECT id FROM {self::$settingstable} WHERE name = '{$c_name}'") !== false){
			return self::set("UPDATE {self::$settingstable} SET value = '{$c_value}' WHERE name = '{$c_name}'");
		}
	}

	/**
	 * 
	 */
	public static function report(){
		return self::$conn->stat();
	}
}

?>