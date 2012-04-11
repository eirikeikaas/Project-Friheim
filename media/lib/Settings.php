<?php

class Settings{
	public static function get($name){
		$c = DB::getInstance();
		
		$q = $c->query("SELECT value FROM tr_settings WHERE name = '{$c_name}' LIMIT 1");
		print_r($q->fetch_assoc());
	}
	
	public static function get($name, $value){
		$c = DB::getInstance();
		
		$q = $c->query("UPDATE tr_settings SET value = '{$c_value}' WHERE name = '{$c_name}' LIMIT 1");
	}
}

?>