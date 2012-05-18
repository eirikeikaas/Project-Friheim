<?php

/**
 * undocumented class
 *
 * @package default
 * @implements Module_Definition
 * @author 
 **/

class Manager_Def extends Controller{

	public function __construct(){
		$this->info("Manager", "manager", "0.1a");
		$this->loadHelper();

		// ACTIONS ////////////////////////////////////

		$this->defineAction("list", "list.html", array(
			"pre" => "preList"
		));
		
		$this->defineAction("new", "new.html", array(
			"pre" => "preList"
		));

		///////////////////////////////////////////////

		$this->defineTab("Superadmin", "superadmin", "admin/manager/list", 16, true, 9999);
	}

	public static function install(){
		/*$table = System::getConfig('usersTable');
		$table = (empty($table)) ? 'users' : $table;
		DB::set("CREATE TABLE IF NOT EXISTS $table (id INT NOT NULL AUTO_INCREMENT ,title VARCHAR(60) NULL ,author INT NULL , timestamp DATETIME NULL , body TEXT NULL , PRIMARY KEY (id) ,UNIQUE INDEX id_UNIQUE (id ASC) )");
		*/
	}

	public function preList($app, $params){
		$settings = ORM::for_table(System::getConfig('settingstable'))->find_many();
		System::addVars(array('settings' => $settings));
	}
	
	public function preNew($app, $params){
		$settings = ORM::for_table(System::getConfig('settingstable'))->find_many();
		System::addVars(array('settings' => $settings));
	}

	/*public function saveEdit($app, $params){
		$usersorm = $this->users->find_many();
		$users = array();
		foreach($usersorm as $user){
			$users[] = $user->as_array();
		}
		System::addVars(array('users' => $users));
	}*/
}