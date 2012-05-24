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

class Manager_Helper extends Helper{

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

	public static function add($key, $type, $default){
		System::addOption($key, $type, $default);
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

	public static function set($key, $value){
		System::updateOption($key, $value);
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

	public static function delete($key){
		System::deleteOption($key);
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
		System::getOption($key);
	}
}