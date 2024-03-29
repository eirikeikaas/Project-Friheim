<?php

/**
 * ======= Blog Class ======
 * 
 * Info
 * 
 * @version 1.0
 * @author Eirik Eikaas, Blest AS
 * @copyright Copyright (c) 2012, Blest AS
 * @uses DB
 * @uses Auth
 * @uses System
 */

class Settings_Helper extends Helper{

	/**
	 * Inserts a new article
	 *
	 * @access public
	 * @static
	 * @param $title string
	 * @param $author int
	 * @param $body string
	 * @return void
	 */

	public static function add($key, $type, $human, $default){
		return System::addOption($key, $type, $human, $default);
	}

	/**
	 * Updates an article
	 *
	 * @access public
	 * @static
	 * @param $id int
	 * @param $body string
	 * @param $title string
	 * @return void
	 */

	public static function set($key, $value, $human = ""){
		return System::updateOption($key, $value, $human);
	}

	/**
	 * Deletes an article
	 *
	 * @access public
	 * @param $title string
	 * @param $author int
	 * @param $body string
	 * @return void
	 */

	public static function del($key){
		return System::deleteOption($key);
	}
	
	/**
	 * Deletes an article
	 *
	 * @access public
	 * @param $title string
	 * @param $author int
	 * @param $body string
	 * @return void
	 */

	public static function get($key){
		return System::getOption($key);
	}
}