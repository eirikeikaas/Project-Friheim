<?php

class Settings_Endpoint extends Endpoint{
	private $helper = false;
	
	public function __construct(){
		$this->info("Settings", "settings", "0.1a");
		$this->loadHelper();
		$this->helper = new Settings_Helper();
	}
	
	public function add($key, $type, $human, $default){
		return $this->helper->add($key, $type, $human, $default);
	}
	
	public function get($key){
		return $this->helper->get($key);
	}
	
	public function set($key, $value, $human = ""){
		return $this->helper->set($key, $value, $human);
	}
	
	public function del($key){
		return $this->helper->del($key);
	}
}

?>