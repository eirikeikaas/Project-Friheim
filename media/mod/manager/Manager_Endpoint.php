<?php

class Settings_Endpoint extends Endpoint{
	private $helper = false;
	
	public function __construct(){
		$this->info("Settings", "settings", "0.1a");
		$this->loadHelper();
		$this->helper = new Settings_Helper();
	}
	
	public function add($key, $type, $default){
		$this->helper->add($key, $type, $default);
	}
	
	public function get($key){
		$this->helper->get($key);
	}
	
	public function update($key, $value){
		$this->helper->update($key, $value);
	}
	
	public function delete($key){
		$this->helper->delete($key);
	}
}

?>