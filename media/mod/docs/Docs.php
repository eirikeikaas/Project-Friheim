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

class Docs extends Model{
	private static $base = "";

	public function __construct($table){
		$this->table($table);
	}

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

	public static function insert(){
		list($id, $author, $body, $title) = func_get_args();
		$c_title = DB::escape($title);
		$c_author = DB::escape($author);
		$c_body = DB::escape($body);
		DB::set("INSERT INTO ".System::getConfig('blogTable')."(title, author, created, changed, body) VALUES ('$c_title', $c_author, NOW(), NOW(), '$c_body')");
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

	public static function update(){
		list($id, $body, $title) = func_get_args();
		$c_id = DB::escape($id);
		$c_body = DB::escape($body);
		$c_title = DB::escape($title);
		return DB::set("UPDATE ".System::getConfig('blogTable')." SET changed = NOW(), body = '$c_body', title = '$c_title' WHERE id = $c_id");
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

	public static function delete(){
		list($id) = func_get_args();
		$c_id = DB::escape($id);
		DB::set("DELETE FROM ".System::getConfig('blogTable')." WHERE id = $c_id");
	}
}