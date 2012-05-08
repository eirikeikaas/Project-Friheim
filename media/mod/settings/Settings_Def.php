<?php

/**
 * undocumented class
 *
 * @package default
 * @implements Module_Definition
 * @author 
 **/

class Settings_Def extends Controller{
	private $users = false;

	public function __construct(){
		$this->info("Users", "users", "0.1a");

		// ACTIONS ////////////////////////////////////

		$this->defineAction("edit", "edit.html", array(
			"pre" => "preEdit",
			"save" => array(
				"callback" => "saveEdit",
				"route" => "admin/settings/edit"
			)
		));

		///////////////////////////////////////////////

		$this->defineTab("Instillinger", "settings", "admin/settings/edit", 4, true);

		//include_once('Users.php');
		$this->users = ORM::for_table('users');
	}

	public static function install(){
		/*$table = System::getConfig('usersTable');
		$table = (empty($table)) ? 'users' : $table;
		DB::set("CREATE TABLE IF NOT EXISTS $table (id INT NOT NULL AUTO_INCREMENT ,title VARCHAR(60) NULL ,author INT NULL , timestamp DATETIME NULL , body TEXT NULL , PRIMARY KEY (id) ,UNIQUE INDEX id_UNIQUE (id ASC) )");
		*/
	}

	public function preList($app, $params){
		$usersorm = $this->users->find_many();
		$users = array();
		foreach($usersorm as $user){
			$users[] = $user->as_array();
		}
		print_r($users);
		System::addVars(array('users' => $users));
	}
}